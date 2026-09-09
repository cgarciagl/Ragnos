<?php

namespace Tests\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport;
use Tests\Ragnos\RagnosTestCase;
use ZipArchive;

use CodeIgniter\Test\FeatureTestTrait;

class RSimpleLevelReportTest extends RagnosTestCase
{
    use FeatureTestTrait;

    private array $tempFiles = [];

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        $this->tempFiles = [];
    }

    private function getTempFilePath(string $suffix = '.xlsx'): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_rsimple_' . uniqid('', true) . $suffix;
        $this->tempFiles[] = $path;
        return $path;
    }

    public function testRenderOutputsReportHtml(): void
    {
        $reporte = new RSimpleLevelReport();
        $datos = [
            ['id' => 1, 'nombre' => 'Producto A', 'total' => 100],
        ];
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Reporte Productos', $datos, ['id', 'nombre', 'total']);

        $html = $reporte->render();
        $this->assertStringContainsString('Reporte Productos', $html);
        $this->assertStringContainsString('Producto A', $html);
        $this->assertStringContainsString('exporttoexcel', $html);
    }

    public function testExportToXlsxGeneratesValidFile(): void
    {
        $reporte = new RSimpleLevelReport();
        $datos = [
            ['Oficina' => 'Madrid', 'empleado' => 'Juan Perez', 'ventas' => '$ 12,500.00'],
            ['Oficina' => 'Barcelona', 'empleado' => 'Maria Lopez', 'ventas' => '$ 18,300.00'],
        ];
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Mejores Empleados Test', $datos, ['empleado', 'ventas'], ['Oficina' => ['label' => 'Oficina']]);

        $outputFile = $this->getTempFilePath('.xlsx');
        $res = $reporte->exportToXLSX($outputFile, false);

        $this->assertTrue($res);
        $this->assertFileExists($outputFile);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($outputFile));
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('Mejores Empleados Test', $sheetXml);
        $this->assertStringContainsString('Oficina: Madrid', $sheetXml);
        $this->assertStringContainsString('Juan Perez', $sheetXml);
        $zip->close();
    }

    public function testExportHtmlToXlsxRejectsEmptyHtml(): void
    {
        $reporte = new RSimpleLevelReport();
        $response = $reporte->exportHtmlToXlsx();

        $this->assertNotNull($response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testExportRouteDispatchesAndReturnsXlsx(): void
    {
        $html = '<table border="1"><thead><tr><th>ID</th><th>Nombre</th></tr></thead><tbody><tr><td>1</td><td>Test</td></tr></tbody></table>';
        $result = $this->call('post', 'ragnos/export-xlsx', [
            'html'     => $html,
            'filename' => 'reporte_test.xlsx',
        ]);

        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $result->assertHeader('Content-Disposition', 'attachment; filename="reporte_test.xlsx"');

        $body = $result->response()->getBody();
        $this->assertNotEmpty($body);

        $tempPath = $this->getTempFilePath('.xlsx');
        file_put_contents($tempPath, $body);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($tempPath));
        $this->assertNotFalse($zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();
    }

    public function testSubtotalsFormatQuantityAsIntegerAndMoneyAsCurrency(): void
    {
        $reporte = new RSimpleLevelReport();
        $datos = [
            ['productName' => 'Producto 1', 'quantityInStock' => 150, 'buyPrice' => 25.50],
            ['productName' => 'Producto 2', 'quantityInStock' => 200, 'buyPrice' => 10.00],
        ];
        $reporte->setShowTotals(true);
        $reporte->setSummableFields(['quantityInStock', 'buyPrice']);
        $reporte->setFieldFormats([
            'quantityInStock' => 'integer',
            'buyPrice'        => 'money',
        ]);
        $reporte->quickSetup('Reporte Stock', $datos, [
            'productName'     => 'Producto',
            'quantityInStock' => 'Cantidad en Stock',
            'buyPrice'        => 'Precio',
        ]);

        $html = $reporte->generate();
        // Check that quantityInStock subtotal is formatted as integer (350), NOT as currency ($ 350.00)
        $this->assertStringContainsString('350', $html);
        $this->assertStringNotContainsString('$ 350', $html);
        $this->assertStringNotContainsString('$350', $html);
        // Check that buyPrice subtotal has currency format
        $this->assertMatchesRegularExpression('/\$ ?35\.50/', $html);
    }

    public function testAutoDetectsQuantityInStockAsIntegerFormat(): void
    {
        $reporte = new RSimpleLevelReport();
        $datos = [
            ['productName' => 'Producto 1', 'quantityInStock' => 100],
            ['productName' => 'Producto 2', 'quantityInStock' => 250],
        ];
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Reporte Auto Stock', $datos, [
            'productName'     => 'Producto',
            'quantityInStock' => 'Cantidad en stock',
        ]);

        $html = $reporte->generate();
        $this->assertStringContainsString('350', $html);
        $this->assertStringNotContainsString('$ 350', $html);
        $this->assertStringNotContainsString('$350', $html);
    }

    public function testFormatTotalValueWithCustomCallable(): void
    {
        $reporte = new RSimpleLevelReport();
        $reporte->setFieldFormat('custom', fn($val) => $val . ' unidades');
        $this->assertSame('50 unidades', $reporte->formatTotalValue('custom', 50));
    }
}


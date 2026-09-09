<?php

namespace Tests\Ragnos\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use DOMDocument;
use ZipArchive;

/**
 * Pruebas unitarias para app/ThirdParty/Ragnos/Helpers/xlsxfiles_helper.php
 * y app/ThirdParty/Ragnos/Helpers/csvfiles_helper.php.
 */
class XlsxHelperTest extends CIUnitTestCase
{
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper([
            'App\ThirdParty\Ragnos\Helpers\xlsxfiles_helper',
            'App\ThirdParty\Ragnos\Helpers\csvfiles_helper',
        ]);
    }

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
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_xlsx_' . uniqid('', true) . $suffix;
        $this->tempFiles[] = $path;
        return $path;
    }

    public function testArrayToXlsxReturnsFalseForEmptyData(): void
    {
        $this->assertFalse(\arrayToXLSXFile([]));
        $this->assertFalse(\arrayToExcelFile([]));
    }

    public function testArrayToXlsxGeneratesValidZipAndOpenXmlFiles(): void
    {
        $data = [
            ['id' => 1, 'nombre' => 'Laptop Gamer', 'precio' => 1250.50, 'activo' => true],
            ['id' => 2, 'nombre' => 'Teclado Mecánico', 'precio' => 89.99, 'activo' => true],
        ];

        $outputFile = $this->getTempFilePath('.xlsx');
        $result = \arrayToXLSXFile($data, $outputFile, false, 'Productos');

        $this->assertTrue($result);
        $this->assertFileExists($outputFile);
        $this->assertGreaterThan(0, filesize($outputFile));

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($outputFile), 'El archivo generado debe ser un archivo ZIP válido');

        $expectedFiles = [
            '[Content_Types].xml',
            '_rels/.rels',
            'xl/_rels/workbook.xml.rels',
            'xl/workbook.xml',
            'xl/styles.xml',
            'xl/worksheets/sheet1.xml',
        ];

        foreach ($expectedFiles as $expectedFile) {
            $content = $zip->getFromName($expectedFile);
            $this->assertNotFalse($content, "El archivo {$expectedFile} debe existir dentro del .xlsx");

            $dom = new DOMDocument();
            $this->assertTrue(@$dom->loadXML($content), "El contenido de {$expectedFile} debe ser XML bien formado");
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $this->assertStringContainsString('name="Productos"', $workbookXml);

        $zip->close();
    }

    public function testArrayToXlsxAppendsExtensionIfMissing(): void
    {
        $data = [['clave' => 'A1', 'valor' => 100]];
        $basePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_no_ext_' . uniqid('', true);
        $expectedPath = $basePath . '.xlsx';
        $this->tempFiles[] = $expectedPath;

        $result = \arrayToXLSXFile($data, $basePath, false);

        $this->assertTrue($result);
        $this->assertFileExists($expectedPath);
    }

    public function testArrayToXlsxHandlesDataTypesAndEscaping(): void
    {
        $data = [
            [
                'id'         => 101,
                'codigo'     => '00123', // Ceros a la izquierda deben conservarse como string
                'texto'      => 'Prueba & <Especial> "Comillas"',
                'decimal'    => 45.95,
                'booleano'   => true,
                'vacio'      => null,
            ],
        ];

        $outputFile = $this->getTempFilePath('.xlsx');
        $result = \arrayToXLSXFile($data, $outputFile, false);

        $this->assertTrue($result);

        $zip = new ZipArchive();
        $zip->open($outputFile);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        // 1. Número 101 debe estar como <v>101</v>
        $this->assertStringContainsString('<v>101</v>', $sheetXml);

        // 2. Cero a la izquierda '00123' debe estar como inlineStr
        $this->assertStringContainsString('00123', $sheetXml);
        $this->assertStringNotContainsString('<v>123</v>', $sheetXml);

        // 3. Caracteres especiales XML deben estar escapados
        $this->assertStringContainsString('Prueba &amp; &lt;Especial&gt; &quot;Comillas&quot;', $sheetXml);

        // 4. Decimal debe estar como <v>45.95</v>
        $this->assertStringContainsString('<v>45.95</v>', $sheetXml);

        // 5. Booleano true debe estar como <c ... t="b"><v>1</v></c>
        $this->assertStringContainsString('t="b"', $sheetXml);
        $this->assertStringContainsString('<v>1</v>', $sheetXml);
    }

    public function testArrayToXlsxCustomOptions(): void
    {
        $data = [
            ['col1' => 'Dato A', 'col2' => 'Dato B'],
        ];

        $outputFile = $this->getTempFilePath('.xlsx');
        $result = \arrayToXLSXFile($data, $outputFile, false, 'ReporteCustom', [
            'header_bg'     => '107C41',
            'header_color'  => 'FFFFFF',
            'auto_filter'   => true,
            'freeze_header' => true,
            'custom_widths' => ['A' => 30, 'B' => 40],
        ]);

        $this->assertTrue($result);

        $zip = new ZipArchive();
        $zip->open($outputFile);
        $stylesXml = $zip->getFromName('xl/styles.xml');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        // Color de encabezado en styles.xml
        $this->assertStringContainsString('FF107C41', $stylesXml);

        // Anchos de columnas en sheet1.xml
        $this->assertStringContainsString('width="30"', $sheetXml);
        $this->assertStringContainsString('width="40"', $sheetXml);

        // Inmovilización de panel (freeze pane)
        $this->assertStringContainsString('state="frozen"', $sheetXml);

        // Filtro automático
        $this->assertStringContainsString('<autoFilter ref="A1:B2"/>', $sheetXml);
    }

    public function testArrayToExcelFileAliasProducesValidFile(): void
    {
        $data = [
            ['id' => 1, 'item' => 'Prueba con alias'],
        ];

        $outputFile = $this->getTempFilePath('.xlsx');
        $result = \arrayToExcelFile($data, $outputFile, false);

        $this->assertTrue($result);
        $this->assertFileExists($outputFile);
    }

    public function testArrayToCSVFileGeneratesValidCsv(): void
    {
        $data = [
            ['id' => 1, 'nombre' => 'Producto 1', 'precio' => 10.5],
            ['id' => 2, 'nombre' => 'Producto 2', 'precio' => 20.0],
        ];

        $outputFile = $this->getTempFilePath('.csv');
        $result = \arrayToCSVFile($data, $outputFile, false);

        $this->assertTrue($result);
        $this->assertFileExists($outputFile);

        $csvContent = file_get_contents($outputFile);
        $this->assertStringContainsString('id,nombre,precio', $csvContent);
        $this->assertStringContainsString('1,"Producto 1",10.5', $csvContent);
    }

    public function testHtmlToXlsxReturnsFalseForEmptyHtml(): void
    {
        $this->assertFalse(\htmlToXLSXFile(''));
        $this->assertFalse(\htmlToXLSXFile('   '));
        $this->assertFalse(\htmlToExcelFile(''));
    }

    public function testHtmlToXlsxGeneratesValidZipAndOpenXmlFromSimpleTable(): void
    {
        $html = '<table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>101</td>
                    <td>Servicio Web</td>
                    <td class="text-end">$ 1,500.00</td>
                </tr>
            </tbody>
        </table>';

        $outputFile = $this->getTempFilePath('.xlsx');
        $result = \htmlToXLSXFile($html, $outputFile, false, 'TablaSimple');

        $this->assertTrue($result);
        $this->assertFileExists($outputFile);
        $this->assertGreaterThan(0, filesize($outputFile));

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($outputFile));

        $expectedFiles = [
            '[Content_Types].xml',
            '_rels/.rels',
            'xl/_rels/workbook.xml.rels',
            'xl/workbook.xml',
            'xl/styles.xml',
            'xl/worksheets/sheet1.xml',
        ];

        foreach ($expectedFiles as $expectedFile) {
            $content = $zip->getFromName($expectedFile);
            $this->assertNotFalse($content, "El archivo {$expectedFile} debe existir");
            $dom = new DOMDocument();
            $this->assertTrue(@$dom->loadXML($content), "{$expectedFile} debe ser XML válido");
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('Servicio Web', $sheetXml);
        $this->assertStringContainsString('101', $sheetXml);
        $this->assertStringContainsString('$ 1,500.00', $sheetXml);

        $zip->close();
    }

    public function testHtmlToXlsxPreservesReportStructureAndFormatting(): void
    {
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');

        $reporte = new \App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport();
        $datos = [
            ['Oficina' => 'Boston', 'employeeNumber' => 1188, 'Empleado' => 'Julie Firrelli', 'TotalVentasTrimestre' => '$ 485,320.00'],
            ['Oficina' => 'Boston', 'employeeNumber' => 1216, 'Empleado' => 'Steve Patterson', 'TotalVentasTrimestre' => '$ 505,875.50'],
            ['Oficina' => 'San Francisco', 'employeeNumber' => 1165, 'Empleado' => 'Leslie Jennings', 'TotalVentasTrimestre' => '$ 1,081,524.80'],
        ];
        $reporte->setShowTotals(true);
        $reporte->quickSetup(
            'Mejores Empleados (Último Trimestre)',
            $datos,
            ['employeeNumber', 'Empleado', 'TotalVentasTrimestre'],
            ['Oficina' => ['label' => 'Oficina']],
            'Trimestre Q3'
        );

        $html = $reporte->generate();

        $outputFile = $this->getTempFilePath('.xlsx');
        $result = \htmlToXLSXFile($html, $outputFile, false, 'ReporteEmpleados', [
            'header_bg'   => '1F4E79',
            'title_color' => '1F4E79',
        ]);

        $this->assertTrue($result);
        $this->assertFileExists($outputFile);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($outputFile));

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $stylesXml = $zip->getFromName('xl/styles.xml');
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $zip->close();

        // 1. Título del reporte presente
        $this->assertStringContainsString('Mejores Empleados (Último Trimestre)', $sheetXml);

        // 2. Filtros activos presentes
        $this->assertStringContainsString('Trimestre Q3', $sheetXml);

        // 3. Encabezados de grupos de corte de control
        $this->assertStringContainsString('Oficina: Boston', $sheetXml);
        $this->assertStringContainsString('Oficina: San Francisco', $sheetXml);

        // 4. Filas de datos
        $this->assertStringContainsString('Julie Firrelli', $sheetXml);
        $this->assertStringContainsString('Leslie Jennings', $sheetXml);
        $this->assertStringContainsString('$ 1,081,524.80', $sheetXml);

        // 5. Subtotales por grupo
        $this->assertStringContainsString('SUBTOTAL', $sheetXml);

        // 6. Resumen General / Totales
        $this->assertStringContainsString('Resumen General', $sheetXml);
        $this->assertStringContainsString('RESUMEN GENERAL', $sheetXml);

        // 7. Nombre de la hoja en workbook.xml
        $this->assertStringContainsString('name="ReporteEmpleados"', $workbookXml);

        // 8. Colores corporativos en styles.xml
        $this->assertStringContainsString('FF1F4E79', $stylesXml);
    }

    public function testRSimpleLevelReportExportToXlsxMethod(): void
    {
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');

        $reporte = new \App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport();
        $datos = [
            ['employeeNumber' => 101, 'Empleado' => 'Test User', 'Total' => 100.50],
        ];
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Reporte Test', $datos, ['employeeNumber', 'Empleado', 'Total']);

        $outputFile = $this->getTempFilePath('.xlsx');
        $res = $reporte->exportToXLSX($outputFile, false);

        $this->assertTrue($res);
        $this->assertFileExists($outputFile);
        $this->assertGreaterThan(0, filesize($outputFile));
    }

    public function testBuildZipPackageCreatesValidZip(): void
    {
        $outputFile = $this->getTempFilePath('.zip');
        $entries = [
            'test.txt' => ['type' => 'string', 'content' => 'Hello World Zip'],
        ];

        $res = \buildZipPackage($outputFile, $entries);
        $this->assertTrue($res);
        $this->assertFileExists($outputFile);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($outputFile));
        $this->assertSame('Hello World Zip', $zip->getFromName('test.txt'));
        $zip->close();
    }
}


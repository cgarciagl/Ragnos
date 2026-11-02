<?php

namespace Tests\Ragnos\Views;

use CodeIgniter\Test\CIUnitTestCase;

final class FrontendDependencyTest extends CIUnitTestCase
{
    public function testFrontendLoadsLocalDependencies(): void
    {
        $scripts = file_get_contents(HOMEPATH . 'src/Views/ragnos/scriptfiles.php');

        $this->assertIsString($scripts);
        $this->assertStringNotContainsString('jquery.min.js', strtolower($scripts));
        $this->assertStringContainsString('/assets/js/datatables.min.js?v=3.0.2', $scripts);
        $this->assertStringContainsString('/assets/js/tom-select.complete.min.js?v=2.6.2', $scripts);
        $this->assertFileExists(FCPATH . 'assets/js/swagger-ui-bundle.js');
        $this->assertFileExists(FCPATH . 'assets/js/swagger-ui-standalone-preset.js');
        $this->assertFileExists(FCPATH . 'assets/css/swagger-ui.css');
    }

    public function testLocalVendorFilesMatchDeclaredVersions(): void
    {
        $dataTables = file_get_contents(FCPATH . 'assets/js/datatables.min.js');
        $tomSelect  = file_get_contents(FCPATH . 'assets/js/tom-select.complete.min.js');

        $this->assertIsString($dataTables);
        $this->assertIsString($tomSelect);
        $this->assertStringContainsString('dt-3.0.2', $dataTables);
        $this->assertStringContainsString('DataTables 3.0.2', $dataTables);
        $this->assertStringContainsString('Responsive 4.0.2', $dataTables);
        $this->assertStringContainsString('Select 4.0.1', $dataTables);
        $this->assertStringContainsString('Tom Select v2.6.2', $tomSelect);
    }

    public function testDataTableConfigurationPreservesCoreGridFeatures(): void
    {
        $template = file_get_contents(
            HOMEPATH . 'src/Views/rdatasetcontroller/datatable_init.php',
        );

        $this->assertIsString($template);
        $this->assertStringContainsString('serverSide:  true', $template);
        $this->assertStringContainsString('responsive:  true', $template);
        $this->assertStringContainsString("style: 'single'", $template);
        $this->assertStringContainsString('order: $aaSorting', $template);
        $this->assertStringContainsString('aplicarDebounceABusqueda(dataTable, 400)', $template);
    }

    public function testObsoleteFrontendAssetsWereRemoved(): void
    {
        $obsoleteAssets = [
            'assets/js/jquery.min.js',
            'assets/js/select2.min.js',
            'assets/js/printThis.min.js',
            'assets/js/bootstrap-datetimepicker.min.js',
            'assets/js/summernote-bs5.min.js',
            'assets/css/select2.min.css',
            'assets/css/bootstrap-datetimepicker.min.css',
            'assets/css/summernote-bs5.css',
        ];

        foreach ($obsoleteAssets as $asset) {
            $this->assertFileDoesNotExist(FCPATH . $asset);
        }
    }

    public function testDataTableInitialSearchIsAlwaysAString(): void
    {
        $template = file_get_contents(
            HOMEPATH . 'src/Views/rdatasetcontroller/datatable_init.php',
        );

        $this->assertIsString($template);
        $this->assertStringContainsString("(string) \$initialSearch : ''", $template);
        $this->assertStringNotContainsString('$sSearch = \'null\';', $template);
    }

    public function testAuthoredFrontendCodeUsesStandardApis(): void
    {
        $directories = [
            HOMEPATH . 'app/Views',
            HOMEPATH . 'src/Views',
            FCPATH . 'assets/js',
        ];

        foreach ($directories as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($iterator as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), ['js', 'php'], true)) {
                    continue;
                }
                if (str_ends_with($file->getFilename(), '.min.js') || str_starts_with($file->getFilename(), 'swagger-ui-')) {
                    continue;
                }

                $source = file_get_contents($file->getPathname());
                $this->assertIsString($source);
                $this->assertDoesNotMatchRegularExpression(
                    '/(?:jQuery|\$\s*\(|\$\.)/',
                    $source,
                    $file->getPathname() . ' still uses a jQuery API.',
                );
            }
        }
    }
}

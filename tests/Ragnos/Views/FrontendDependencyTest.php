<?php

namespace Tests\Ragnos\Views;

use CodeIgniter\Test\CIUnitTestCase;

final class FrontendDependencyTest extends CIUnitTestCase
{
    public function testFrontendLoadsLocalJqueryFreeDependencies(): void
    {
        $scripts = file_get_contents(HOMEPATH . 'app/ThirdParty/Ragnos/Views/ragnos/scriptfiles.php');

        $this->assertIsString($scripts);
        $this->assertStringNotContainsString('jquery.min.js', strtolower($scripts));
        $this->assertStringContainsString('/assets/js/datatables.min.js?v=3.0.2', $scripts);
        $this->assertStringContainsString('/assets/js/tom-select.complete.min.js?v=2.6.2', $scripts);
    }

    public function testLocalVendorFilesMatchDeclaredVersions(): void
    {
        $dataTables = file_get_contents(HOMEPATH . 'content/assets/js/datatables.min.js');
        $tomSelect  = file_get_contents(HOMEPATH . 'content/assets/js/tom-select.complete.min.js');

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
            HOMEPATH . 'app/ThirdParty/Ragnos/Views/rdatasetcontroller/datatable_init.php',
        );

        $this->assertIsString($template);
        $this->assertStringContainsString('serverSide:  true', $template);
        $this->assertStringContainsString('responsive:  true', $template);
        $this->assertStringContainsString("style: 'single'", $template);
        $this->assertStringContainsString('order: $aaSorting', $template);
        $this->assertStringContainsString('aplicarDebounceABusqueda(dataTable, 400)', $template);
    }

    public function testObsoleteJqueryAssetsWereRemoved(): void
    {
        $obsoleteAssets = [
            'content/assets/js/jquery.min.js',
            'content/assets/js/select2.min.js',
            'content/assets/js/printThis.min.js',
            'content/assets/js/bootstrap-datetimepicker.min.js',
            'content/assets/js/summernote-bs5.min.js',
            'content/assets/css/select2.min.css',
            'content/assets/css/bootstrap-datetimepicker.min.css',
            'content/assets/css/summernote-bs5.css',
        ];

        foreach ($obsoleteAssets as $asset) {
            $this->assertFileDoesNotExist(HOMEPATH . $asset);
        }
    }

    public function testDataTableInitialSearchIsAlwaysAString(): void
    {
        $template = file_get_contents(
            HOMEPATH . 'app/ThirdParty/Ragnos/Views/rdatasetcontroller/datatable_init.php',
        );

        $this->assertIsString($template);
        $this->assertStringContainsString("(string) \$initialSearch : ''", $template);
        $this->assertStringNotContainsString('$sSearch = \'null\';', $template);
    }

    public function testAuthoredFrontendCodeDoesNotUseJqueryApis(): void
    {
        $directories = [
            HOMEPATH . 'app/Views',
            HOMEPATH . 'app/ThirdParty/Ragnos/Views',
            HOMEPATH . 'content/assets/js',
        ];

        foreach ($directories as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($iterator as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), ['js', 'php'], true)) {
                    continue;
                }
                if (str_ends_with($file->getFilename(), '.min.js')) {
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

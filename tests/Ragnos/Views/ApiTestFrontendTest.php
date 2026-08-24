<?php

namespace Tests\Ragnos\Views;

use CodeIgniter\Test\CIUnitTestCase;

final class ApiTestFrontendTest extends CIUnitTestCase
{
    public function testSavePayloadSelectsTheCorrectRagnosCrudOperation(): void
    {
        $script = file_get_contents(HOMEPATH . 'apitest/app.js');

        $this->assertIsString($script);
        $this->assertStringContainsString("Ragnos_action: this.formMode === 'edit' ? 'update' : 'insert'", $script);
        $this->assertStringContainsString("body[this.config.idField] = this.editingId", $script);
        $this->assertStringContainsString('result.status === 422', $script);
    }

    public function testDemoUsesLocalDependenciesAndOpenApiDiscovery(): void
    {
        $index = file_get_contents(HOMEPATH . 'apitest/index.html');
        $script = file_get_contents(HOMEPATH . 'apitest/app.js');
        $config = file_get_contents(HOMEPATH . 'apitest/config.js');

        $this->assertIsString($index);
        $this->assertIsString($script);
        $this->assertIsString($config);
        $this->assertStringNotContainsString('cdn.jsdelivr.net', $index);
        $this->assertStringContainsString('vendor/js/alpinejs.min.js', $index);
        $this->assertStringContainsString('vendor/js/bootstrap.bundle.min.js', $index);
        $this->assertStringContainsString('loadOpenApi()', $script);
        $this->assertStringContainsString('discoverResources', $script);
        $this->assertStringContainsString('requestHistory', $script);
        $this->assertStringContainsString('sessionStorage', $script);
        $this->assertStringContainsString('openapi.json', $config);
        $this->assertFileExists(HOMEPATH . 'apitest/vendor/js/alpinejs.min.js');
        $this->assertFileExists(HOMEPATH . 'apitest/vendor/js/bootstrap.bundle.min.js');
        $this->assertFileExists(HOMEPATH . 'apitest/vendor/css/bootstrap.min.css');
        $this->assertFileExists(HOMEPATH . 'apitest/vendor/css/bootstrap-icons.min.css');
    }
}

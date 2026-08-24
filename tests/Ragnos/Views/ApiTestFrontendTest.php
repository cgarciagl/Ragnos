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
}

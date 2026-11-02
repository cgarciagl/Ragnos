<?php

namespace Tests\Ragnos\Views;

use Tests\Ragnos\RagnosTestCase;

class ViewSecurityTest extends RagnosTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper([
            'url',
            'Ragnos\Helpers\ragnos_helper',
            'Ragnos\Helpers\utiles_helper',
        ]);
    }

    public function testHtmlTextareaEscapaCierreDeEtiqueta(): void
    {
        $payload = '</textarea><script>alert(1)</script>';
        $html    = view('Ragnos\Views\rfield\htmltextareafield', [
            'name'  => 'descripcion',
            'label' => 'Descripción',
            'value' => $payload,
        ]);

        $this->assertStringNotContainsString($payload, $html);
        $this->assertStringContainsString('&lt;/textarea&gt;&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function testMasterSeSerializaComoJsonSeguroParaJavaScript(): void
    {
        $payload = "x';</script><script>alert(1)</script>";
        $html    = view('Ragnos\Views\rdatasetcontroller\table_view_js', [
            'controllerUniqueID' => 'testController',
            'controller_name'    => 'TestController',
            'tableController'    => 'TestController',
            'clase'              => 'test/controller',
            'fieldlist'          => [],
            'hasdetails'         => false,
            'master'             => $payload,
            'modelo'             => (object) ['canDelete' => false],
            'sortingField'       => -1,
            'sortingDir'         => 'asc',
        ]);

        $this->assertStringNotContainsString($payload, $html);
        $this->assertStringContainsString('\\u0027', $html);
        $this->assertStringContainsString('\\u003C', $html);
    }
}
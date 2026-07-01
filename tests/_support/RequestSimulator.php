<?php

namespace Tests\Support;

use CodeIgniter\Config\Services;

/**
 * Trait para simular peticiones HTTP en tests de forma limpia.
 *
 * Encapsula la manipulación de $_POST/$_GET y Services::request()
 * para evitar duplicación y garantizar reset entre tests.
 */
trait RequestSimulator
{
    /**
     * Limpia todas las superglobales y resetea Services.
     * Llamar en setUp() de cada test case.
     */
    protected function resetRequest(): void
    {
        Services::reset(true);
        $_POST   = [];
        $_GET    = [];
        $_SERVER = [];
        \setOldRecordCache([]);
    }

    /**
     * Simula datos POST en la request.
     */
    protected function setPost(array $data): void
    {
        $request = service('request');
        $request->setGlobal('post', $data);
        $request->setGlobal('request', $data);
    }

    /**
     * Simula datos GET en la request.
     */
    protected function setGet(array $data): void
    {
        $request = service('request');
        $request->setGlobal('get', $data);
        $request->setGlobal('request', $data);
    }

    /**
     * Simula datos POST + GET combinados.
     */
    protected function setRequest(array $post = [], array $get = []): void
    {
        $this->setPost($post);
        $this->setGet($get);
    }

    /**
     * Helper para cargar helpers de Ragnos.
     */
    protected function loadRagnosHelpers(): void
    {
        helper([
            'App\ThirdParty\Ragnos\Helpers\utiles_helper',
            'App\ThirdParty\Ragnos\Helpers\ragnos_helper',
            'text',
        ]);
    }
}

<?php

namespace Tests\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\BaseController;
use Tests\Ragnos\RagnosTestCase;

class BaseControllerTest extends RagnosTestCase
{
    private BaseController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->query('CREATE TABLE gen_gruposdeusuarios (gru_id INTEGER PRIMARY KEY, gru_nombre TEXT NOT NULL)');
        $this->db->query('CREATE TABLE gen_usuarios (
            usu_id INTEGER PRIMARY KEY,
            usu_token TEXT,
            usu_activo TEXT NOT NULL,
            usu_grupo INTEGER NOT NULL
        )');

        $this->db->table('gen_gruposdeusuarios')->insertBatch([
            ['gru_id' => 1, 'gru_nombre' => 'Administrador'],
            ['gru_id' => 2, 'gru_nombre' => 'Operador'],
        ]);
        $this->db->table('gen_usuarios')->insertBatch([
            ['usu_id' => 1, 'usu_token' => 'token-admin', 'usu_activo' => 'S', 'usu_grupo' => 1],
            ['usu_id' => 2, 'usu_token' => 'token-operador', 'usu_activo' => 'S', 'usu_grupo' => 2],
            ['usu_id' => 3, 'usu_token' => 'token-inactivo', 'usu_activo' => 'N', 'usu_grupo' => 1],
        ]);

        $this->controller = new class extends BaseController {
        };
    }

    public function testTokenAdministradorAutorizaGrupoAdministrador(): void
    {
        $this->assertTrue($this->controller->validarTokenEnGrupos('token-admin', 'Administrador'));
    }

    public function testTokenDeOtroGrupoNoAutorizaAdministrador(): void
    {
        $this->assertFalse($this->controller->validarTokenEnGrupos('token-operador', 'Administrador'));
    }

    public function testGruposSeComparanNormalizados(): void
    {
        $this->assertTrue($this->controller->validarTokenEnGrupos('token-operador', [' administrador ', ' OPERADOR ']));
    }

    public function testTokenInactivoEsRechazado(): void
    {
        $this->assertFalse($this->controller->validarToken('token-inactivo'));
    }
}
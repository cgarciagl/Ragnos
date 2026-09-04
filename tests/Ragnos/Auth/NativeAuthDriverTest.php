<?php

declare(strict_types=1);

namespace Tests\Ragnos\Auth;

use App\ThirdParty\Ragnos\Auth\Drivers\NativeAuthDriver;
use App\ThirdParty\Ragnos\Auth\RagnosAuthInterface;
use Tests\Ragnos\RagnosTestCase;

/**
 * Tests unitarios para NativeAuthDriver.
 */
class NativeAuthDriverTest extends RagnosTestCase
{
    private NativeAuthDriver $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $sessionConfig = config('Session');
        if ($sessionConfig !== null && property_exists($sessionConfig, 'driver')) {
            $sessionConfig->driver   = \CodeIgniter\Session\Handlers\FileHandler::class;
            $sessionConfig->savePath = WRITEPATH . 'session';
        }

        $this->db->query('CREATE TABLE IF NOT EXISTS gen_gruposdeusuarios (gru_id INTEGER PRIMARY KEY, gru_nombre TEXT NOT NULL)');
        $this->db->query('CREATE TABLE IF NOT EXISTS gen_usuarios (
            usu_id INTEGER PRIMARY KEY,
            usu_nombre TEXT,
            usu_login TEXT,
            usu_email TEXT,
            usu_token TEXT,
            usu_activo TEXT NOT NULL,
            usu_grupo INTEGER NOT NULL
        )');

        $this->db->table('gen_gruposdeusuarios')->emptyTable();
        $this->db->table('gen_usuarios')->emptyTable();

        $this->db->table('gen_gruposdeusuarios')->insertBatch([
            ['gru_id' => 1, 'gru_nombre' => 'ADMINISTRADOR'],
            ['gru_id' => 2, 'gru_nombre' => 'OPERADOR'],
        ]);

        $this->db->table('gen_usuarios')->insertBatch([
            [
                'usu_id'     => 1,
                'usu_nombre' => 'Super Admin',
                'usu_login'  => 'admin',
                'usu_email'  => 'admin@ragnos.dev',
                'usu_token'  => 'token-valido-admin',
                'usu_activo' => 'S',
                'usu_grupo'  => 1,
            ],
            [
                'usu_id'     => 2,
                'usu_nombre' => 'Juan Perez',
                'usu_login'  => 'juan@empresa.com',
                'usu_email'  => null,
                'usu_token'  => 'token-valido-operador',
                'usu_activo' => 'S',
                'usu_grupo'  => 2,
            ],
            [
                'usu_id'     => 3,
                'usu_nombre' => 'Usuario Inactivo',
                'usu_login'  => 'inactivo',
                'usu_email'  => null,
                'usu_token'  => 'token-inactivo',
                'usu_activo' => 'N',
                'usu_grupo'  => 2,
            ],
        ]);

        session()->remove(['usu_id', 'usu_nombre', 'gru_nombre']);
        $this->driver = new NativeAuthDriver();
    }

    public function testImplementaRagnosAuthInterface(): void
    {
        $this->assertInstanceOf(RagnosAuthInterface::class, $this->driver);
    }

    public function testSinSesionNoEstaLogueado(): void
    {
        $this->assertFalse($this->driver->checkLogin());
        $this->assertFalse($this->driver->isLoggedIn());
        $this->assertNull($this->driver->getUserId());
        $this->assertNull($this->driver->id());
        $this->assertNull($this->driver->getUserName());
        $this->assertNull($this->driver->name());
        $this->assertNull($this->driver->getUserEmail());
    }

    public function testConSesionRetornaDatosCorrectos(): void
    {
        session()->set('usu_id', 1);
        session()->set('usu_nombre', 'Super Admin');

        $driver = new NativeAuthDriver();

        $this->assertTrue($driver->checkLogin());
        $this->assertTrue($driver->isLoggedIn());
        $this->assertSame(1, $driver->getUserId());
        $this->assertSame(1, $driver->id());
        $this->assertSame('Super Admin', $driver->getUserName());
        $this->assertSame('Super Admin', $driver->name());
        $this->assertSame('admin@ragnos.dev', $driver->getUserEmail());
        $this->assertSame('ADMINISTRADOR', $driver->getField('gru_nombre'));
    }

    public function testIsUserInGroupInsensibleAMayusculas(): void
    {
        session()->set('usu_id', 1);

        $driver = new NativeAuthDriver();

        $this->assertTrue($driver->isUserInGroup('administrador'));
        $this->assertTrue($driver->isUserInGroup('ADMINISTRADOR'));
        $this->assertTrue($driver->isUserInGroup(' Administrador '));
        $this->assertFalse($driver->isUserInGroup('operador'));
    }

    public function testValidarTokenApiExitoso(): void
    {
        $this->assertTrue($this->driver->checkApiToken('token-valido-admin'));
        $this->assertSame(1, $this->driver->getUserId());
        $this->assertSame('Super Admin', $this->driver->getUserName());
        $this->assertTrue($this->driver->isUserInGroup('administrador'));
    }

    public function testValidarTokenInactivoFalla(): void
    {
        $this->assertFalse($this->driver->checkApiToken('token-inactivo'));
        $this->assertFalse($this->driver->checkApiToken('token-inexistente'));
        $this->assertFalse($this->driver->checkApiToken(''));
    }

    public function testEmailFallbackDesdeLogin(): void
    {
        session()->set('usu_id', 2);
        $driver = new NativeAuthDriver();

        // En BD usu_email es null pero usu_login es 'juan@empresa.com'
        $this->assertSame('juan@empresa.com', $driver->getUserEmail());
    }

    public function testLogoutLimpiaSesionYCache(): void
    {
        session()->set('usu_id', 1);
        $driver = new NativeAuthDriver();
        $this->assertTrue($driver->checkLogin());

        $driver->logout();

        $this->assertFalse($driver->checkLogin());
        $this->assertNull($driver->getUserId());
    }
}


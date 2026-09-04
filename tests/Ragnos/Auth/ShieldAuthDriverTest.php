<?php

declare(strict_types=1);

namespace Tests\Ragnos\Auth;

use App\ThirdParty\Ragnos\Auth\Drivers\ShieldAuthDriver;
use App\ThirdParty\Ragnos\Auth\RagnosAuthInterface;
use Tests\Ragnos\RagnosTestCase;

/**
 * Tests unitarios para ShieldAuthDriver.
 */
class ShieldAuthDriverTest extends RagnosTestCase
{
    private ShieldAuthDriver $driver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->driver = new ShieldAuthDriver();
    }

    public function testImplementaRagnosAuthInterface(): void
    {
        $this->assertInstanceOf(RagnosAuthInterface::class, $this->driver);
    }

    public function testComportamientoSeguroCuandoShieldNoEstaPresente(): void
    {
        // En este entorno Shield no está instalado por defecto.
        // El driver debe comportarse de forma segura sin lanzar errores fatales.
        $this->assertFalse($this->driver->checkLogin());
        $this->assertFalse($this->driver->isLoggedIn());
        $this->assertNull($this->driver->getUserId());
        $this->assertNull($this->driver->id());
        $this->assertNull($this->driver->getUserName());
        $this->assertNull($this->driver->name());
        $this->assertNull($this->driver->getUserEmail());
        $this->assertFalse($this->driver->isUserInGroup('admin'));
        $this->assertFalse($this->driver->checkApiToken('cualquier-token'));
        $this->assertSame('', $this->driver->getField('username'));

        // logout no debe disparar excepción
        $this->driver->logout();
        $this->assertTrue(true);
    }
}


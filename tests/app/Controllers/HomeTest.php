<?php

namespace Tests\App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\Home;

/**
 * Pruebas de integración para Home controller.
 *
 * Home redirige al login cuando no hay sesión activa.
 */
class HomeTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testIndexRedirigeALoginSinSesion(): void
    {
        $result = $this->call('get', '/');

        // Sin autenticación, redirige al login
        $result->assertStatus(302);
        $this->assertStringContainsString('login', $result->getRedirectUrl() ?? '');
    }

    public function testRutaHomeRequiereAutenticacion(): void
    {
        $result = $this->call('get', '/');

        // La respuesta debe ser una redirección
        $this->assertTrue($result->isRedirect());
    }
}

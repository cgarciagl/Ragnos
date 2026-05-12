<?php

namespace Tests\Ragnos\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use App\ThirdParty\Ragnos\Controllers\RController;
use App\ThirdParty\Ragnos\Controllers\Ragnos;

/**
 * Pruebas para RController:
 * - getClassName devuelve el FQCN en minúsculas
 * - tras instanciar el primer controlador, queda registrado como "active"
 * - isThisActiveController detecta correctamente el controlador activo
 */
class RControllerTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Resetear el singleton estático Ragnos::$CI para aislamiento entre tests
        Ragnos::$CI = null;
    }

    public function testGetClassNameDevuelveFQCNEnMinusculas(): void
    {
        $controller = new RController();
        $name       = $controller->getClassName();

        // El FQCN en minúsculas debe contener 'rcontroller'
        $this->assertStringContainsString('rcontroller', $name);
        $this->assertSame(strtolower(RController::class), $name);
    }

    public function testGetClassNameUsaLaSubclaseConcreta(): void
    {
        $controller = new class extends RController {
        };
        $name = $controller->getClassName();

        // El nombre debe incluir 'class@anonymous' o similar; al menos debe estar en minúsculas
        $this->assertSame(strtolower(get_class($controller)), $name);
    }

    public function testPrimerControlerSeRegistraComoActivo(): void
    {
        $controller = new RController();
        $CI         = Ragnos::get_CI();

        $this->assertTrue(property_exists($CI, 'activeRagnosController'));
        $this->assertSame($controller->getClassName(), $CI->activeRagnosController);
    }

    public function testIsThisActiveControllerTrueParaPrimerInstanciado(): void
    {
        $controller = new RController();
        $this->assertTrue($controller->isThisActiveController());
    }

    public function testSegundoControlerNoSobreescribeActiveController(): void
    {
        $primero = new RController();
        // Una segunda instanciación NO debe robar el "activo"
        $segundo = new class extends RController {
        };

        $this->assertTrue($primero->isThisActiveController());
        // El segundo tiene distinto getClassName, por lo que no es active
        $this->assertFalse($segundo->isThisActiveController());
    }

    public function testMagicGetDevuelveNullParaAtributosNoExistentes(): void
    {
        $controller = new RController();
        $this->assertNull($controller->atributo_inexistente);
    }

    public function testLoadDefaultsCargaHelpersRagnos(): void
    {
        // RController::__construct -> Ragnos::loadDefaults()
        new RController();
        // Las funciones definidas en ragnos_helper.php deben estar disponibles
        $this->assertTrue(function_exists('moneyToNumber'));
        $this->assertTrue(function_exists('mapClassToURL'));
        // Las definidas en utiles_helper.php también
        $this->assertTrue(function_exists('isJson'));
        $this->assertTrue(function_exists('removeNewLines'));
    }
}

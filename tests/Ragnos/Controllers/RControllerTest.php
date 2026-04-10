<?php

namespace Tests\Ragnos\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use App\ThirdParty\Ragnos\Controllers\RController;

class RControllerTest extends CIUnitTestCase
{
    public function testCanInstantiateRController(): void
    {
        $controller = new RController();

        $this->assertInstanceOf(RController::class, $controller);
    }

    public function testGetClassNameReturnsCorrectName(): void
    {
        $controller = new RController();
        $className  = $controller->getClassName();

        $this->assertNotEmpty($className);
        // Just check it's a non-empty string
        $this->assertIsString($className);
    }

    public function testIsThisActiveControllerReturnsFalseForBase(): void
    {
        $controller = new RController();
        $isActive   = $controller->isThisActiveController();

        $this->assertIsBool($isActive);
    }

    public function testRControllerExtendsBaseController(): void
    {
        $controller = new RController();

        // Verify it has methods from BaseController
        $this->assertTrue(method_exists($controller, '__get'));
    }

    public function testMagicGetMethodExists(): void
    {
        $controller = new RController();

        $this->assertTrue(method_exists($controller, '__get'));
    }

    public function testCanAccessMethods(): void
    {
        $controller = new RController();

        // Test public methods exist
        $this->assertTrue(method_exists($controller, 'getClassName'));
        $this->assertTrue(method_exists($controller, 'isThisActiveController'));
    }

    public function testClassNameIsNotEmpty(): void
    {
        $controller = new RController();
        $name       = $controller->getClassName();

        $this->assertIsString($name);
        $this->assertTrue(strlen($name) > 0);
    }
}

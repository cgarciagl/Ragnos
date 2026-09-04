<?php

declare(strict_types=1);

namespace Tests\Ragnos\Auth;

use App\Services\Admin_aut;
use App\ThirdParty\Ragnos\Auth\Drivers\NativeAuthDriver;
use App\ThirdParty\Ragnos\Auth\Drivers\ShieldAuthDriver;
use App\ThirdParty\Ragnos\Auth\RagnosAuthInterface;
use Config\Services;
use Tests\Ragnos\RagnosTestCase;

/**
 * Tests de integración para el servicio Admin_aut y el Service Container.
 */
class AuthServiceTest extends RagnosTestCase
{
    public function testServiceContainerRetornaRagnosAuthInterface(): void
    {
        $auth = service('Admin_aut');
        $this->assertInstanceOf(RagnosAuthInterface::class, $auth);
        $this->assertInstanceOf(NativeAuthDriver::class, $auth);
    }

    public function testServiceContainerRespetaSharedInstance(): void
    {
        $instance1 = service('Admin_aut');
        $instance2 = service('Admin_aut');
        $this->assertSame($instance1, $instance2);

        $instanceNonShared = Services::Admin_aut(false);
        $this->assertNotSame($instance1, $instanceNonShared);
    }

    public function testAdminAutClaseLegadaFuncionaComoProxy(): void
    {
        $instance = Admin_aut::getInstance();
        $this->assertInstanceOf(RagnosAuthInterface::class, $instance);

        session()->set('usu_id', 42);
        session()->set('usu_nombre', 'Admin Test');

        $this->assertSame(42, Admin_aut::id());

        $adminAutObj = new Admin_aut();
        $this->assertTrue($adminAutObj->checkLogin());
        $this->assertTrue($adminAutObj->isLoggedIn());
        $this->assertSame(42, $adminAutObj->getUserId());
        $this->assertSame(42, $adminAutObj->id());
        $this->assertSame('Admin Test', $adminAutObj->getUserName());
        $this->assertSame('Admin Test', $adminAutObj->name());

        session()->remove(['usu_id', 'usu_nombre']);
    }

    public function testConfiguracionFallbackAShieldSiNoEstaInstalado(): void
    {
        $config             = config('RagnosConfig');
        $config->authDriver = 'shield';

        // Al no existir CodeIgniter\Shield\Auth::class, debe retornar NativeAuthDriver como fallback seguro
        $auth = Services::Admin_aut(false);
        $this->assertInstanceOf(NativeAuthDriver::class, $auth);

        $config->authDriver = 'native';
    }
}


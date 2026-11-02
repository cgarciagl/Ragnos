<?php

namespace Tests\App\Libraries;

use App\Libraries\Passwords;
use CodeIgniter\Test\CIUnitTestCase;

final class PasswordsTest extends CIUnitTestCase
{
    public function testHashModernoSeVerificaYDistingueMayusculas(): void
    {
        $hash = Passwords::hash('Secreta1');

        $this->assertTrue(Passwords::verify('Secreta1', $hash));
        $this->assertFalse(Passwords::verify('secreta1', $hash));
        $this->assertFalse(Passwords::needsRehash($hash));
    }

    public function testHashMd5AnteriorSigueFuncionandoYPideMigrarse(): void
    {
        $legacy = md5(strtoupper('admin'));

        $this->assertTrue(Passwords::isLegacy($legacy));
        $this->assertTrue(Passwords::verify('admin', $legacy));
        $this->assertTrue(Passwords::verify('ADMIN', $legacy), 'El formato anterior no distinguía mayúsculas');
        $this->assertFalse(Passwords::verify('otra', $legacy));
        $this->assertTrue(Passwords::needsRehash($legacy));
    }
}

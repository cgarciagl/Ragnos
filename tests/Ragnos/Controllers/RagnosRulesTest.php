<?php

namespace Tests\Ragnos\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\RequestSimulator;
use App\ThirdParty\Ragnos\Controllers\RagnosRules;

/**
 * Pruebas para RagnosRules::readonly_Ragnos.
 * Simula valores nuevos vía service('request')->setGlobal('post', ...) y
 * valores anteriores vía setOldRecordCache (helper de Ragnos).
 */
class RagnosRulesTest extends CIUnitTestCase
{
    use RequestSimulator;

    protected RagnosRules $rules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadRagnosHelpers();
        $this->resetRequest();
        $this->rules = new RagnosRules();
    }

    protected function tearDown(): void
    {
        $this->resetRequest();
        parent::tearDown();
    }

    public function testReadonlyReturnsTrueCuandoValorNoCambia(): void
    {
        \setOldRecordCache(['estado' => 'ACTIVO']);
        $this->setPost(['estado' => 'ACTIVO']);

        $error  = null;
        $result = $this->rules->readonly_Ragnos('ACTIVO', 'estado', [], $error);
        $this->assertTrue($result);
        $this->assertNull($error);
    }

    public function testReadonlyReturnsFalseCuandoValorCambia(): void
    {
        \setOldRecordCache(['estado' => 'ACTIVO']);
        $this->setPost(['estado' => 'INACTIVO']);

        $error  = null;
        $result = $this->rules->readonly_Ragnos('INACTIVO', 'estado', [], $error);
        $this->assertFalse($result);
        $this->assertNotNull($error, 'Se debe asignar mensaje de error cuando la regla falla');
    }

    public function testReadonlyReturnsTrueSinCacheYSinInput(): void
    {
        // Sin cache (campo no existe) y sin input: oldValue=null, newValue=null => no cambió
        $error  = null;
        $result = $this->rules->readonly_Ragnos(null, 'campo_no_existe', [], $error);
        $this->assertTrue($result);
    }

    public function testReadonlyDetectaCambioContraValorRagnosValueAnt(): void
    {
        // Si el cliente envía Ragnos_value_ant_*, ese tiene prioridad sobre la cache
        \setOldRecordCache(['estado' => 'OTRO']);
        $this->setPost([
            'Ragnos_value_ant_estado' => 'ACTIVO',
            'estado'                  => 'INACTIVO',
        ]);

        $error  = null;
        $result = $this->rules->readonly_Ragnos('INACTIVO', 'estado', [], $error);
        $this->assertFalse($result);
        $this->assertNotNull($error);
    }

    public function testInstanciasMultiplesNoComparteEstado(): void
    {
        $r1 = new RagnosRules();
        $r2 = new RagnosRules();
        $this->assertNotSame($r1, $r2);

        $error = null;
        $this->assertTrue($r1->readonly_Ragnos(null, 'x_nuevo', [], $error));
        $this->assertTrue($r2->readonly_Ragnos(null, 'x_nuevo', [], $error));
    }
}

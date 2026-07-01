<?php

namespace Tests\Ragnos\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas funcionales para app/ThirdParty/Ragnos/Helpers/ragnos_helper.php
 * y app/ThirdParty/Ragnos/Helpers/utiles_helper.php (startsWith/endsWith viven ahí).
 *
 * Las funciones son globales (sin namespace), por eso se invocan con prefijo `\`
 * para no resolverse en el namespace del test cuando se ejecuta en aislamiento.
 */
class RagnosHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper([
            'App\ThirdParty\Ragnos\Helpers\utiles_helper',
            'App\ThirdParty\Ragnos\Helpers\ragnos_helper',
        ]);
        \setOldRecordCache(null);
    }

    public function testStartsWithDetectsPrefix(): void
    {
        $this->assertTrue(\startsWith('Hello World', 'Hello'));
        $this->assertTrue(\startsWith('Hello', 'H'));
    }

    public function testStartsWithReturnsFalseForNonPrefix(): void
    {
        $this->assertFalse(\startsWith('Hello World', 'World'));
        $this->assertFalse(\startsWith('Hello', 'X'));
    }

    public function testStartsWithEmptyNeedleAlwaysTrue(): void
    {
        $this->assertTrue(\startsWith('cualquier cosa', ''));
    }

    public function testEndsWithDetectsSuffix(): void
    {
        $this->assertTrue(\endsWith('Hello World', 'World'));
        $this->assertTrue(\endsWith('file.php', '.php'));
    }

    public function testEndsWithReturnsFalseForNonSuffix(): void
    {
        $this->assertFalse(\endsWith('Hello World', 'Hello'));
        $this->assertFalse(\endsWith('file.php', '.txt'));
    }

    public function testEndsWithEmptyNeedleAlwaysTrue(): void
    {
        $this->assertTrue(\endsWith('cualquier cosa', ''));
    }

    public function testMoneyFormatProducesCurrencyString(): void
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is not loaded');
        }
        $result = \moneyFormat(1234.5);
        $this->assertIsString($result);
        $this->assertStringContainsString('$', $result);
        // Dos decimales presentes en el resultado
        $this->assertMatchesRegularExpression('/[0-9][\.,]?[0-9]{0,3}[\.,]\d{2}/', $result);
    }

    public function testMoneyFormatZero(): void
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is not loaded');
        }
        $result = \moneyFormat(0);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testMoneyToNumberStripsCurrencyAndCommas(): void
    {
        $this->assertSame(1234.56, \moneyToNumber('$ 1,234.56'));
        $this->assertSame(1000.50, \moneyToNumber('1,000.50'));
        $this->assertSame(100.0, \moneyToNumber('$100'));
    }

    public function testMoneyToNumberPreservesNegativeSign(): void
    {
        $this->assertSame(-500.0, \moneyToNumber('-500'));
        $this->assertSame(-1234.56, \moneyToNumber('-$1,234.56'));
    }

    public function testMoneyToNumberHandlesEmptyAndJunk(): void
    {
        $this->assertSame(0.0, \moneyToNumber('abc'));
        $this->assertSame(0.0, \moneyToNumber(''));
    }

    public function testRaiseLanzaExcepcionConMensaje(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('mensaje de error de prueba');
        \raise('mensaje de error de prueba');
    }

    public function testRaiseUsaCodigoYExcepcionAnterior(): void
    {
        $previous = new \RuntimeException('previa');
        try {
            \raise('nueva', 42, $previous);
            $this->fail('raise() debió lanzar excepción');
        } catch (\Exception $e) {
            $this->assertSame('nueva', $e->getMessage());
            $this->assertSame(42, $e->getCode());
            $this->assertSame($previous, $e->getPrevious());
        }
    }

    public function testMapClassToURLConvierteFQCNAPath(): void
    {
        $this->assertSame('Tienda/Clientes', \mapClassToURL('App\\Controllers\\Tienda\\Clientes'));
        $this->assertSame('Usuarios', \mapClassToURL('App\\Controllers\\Usuarios'));
    }

    public function testControllerNameToURLConstruyeRutaCorrecta(): void
    {
        $this->assertSame('Tienda/Productos', \controllerNameToURL('Tienda\\Productos'));
        $this->assertSame('Usuarios', \controllerNameToURL('Usuarios'));
    }

    public function testSetOldRecordCacheAlmacenaYRecupera(): void
    {
        $record = ['id' => 5, 'nombre' => 'Ana'];
        \setOldRecordCache($record);
        $this->assertSame($record, \_oldRecordCache());
    }

    public function testSetOldRecordCacheNullNoResetea(): void
    {
        \setOldRecordCache(['id' => 1]);
        \setOldRecordCache(null);
        $this->assertSame(['id' => 1], \_oldRecordCache(), 'Pasar null al setter NO debe limpiar la caché (sólo lo hace pasar array)');
    }

    public function testOldValueUsaCacheCuandoNoHayInput(): void
    {
        \setOldRecordCache(['estado' => 'ACTIVO', 'precio' => 100]);
        $this->assertSame('ACTIVO', \oldValue('estado'));
        $this->assertSame(100, \oldValue('precio'));
    }

    public function testOldValueRetornaNullSiCacheVaciaYSinInput(): void
    {
        $this->assertNull(\oldValue('campo_inexistente'));
    }

    public function testFieldHasChangedRetornaFalseCuandoIgualEnCache(): void
    {
        // Simular que el input nuevo (newValue) y el cache (oldValue) coinciden
        \setOldRecordCache(['estado' => null]);
        // newValue('estado') retorna null si no hay input; oldValue retorna null del cache
        $this->assertFalse(\fieldHasChanged('estado'));
    }
}

<?php

namespace Tests\Ragnos\Helpers;

use Tests\Ragnos\RagnosTestCase;

/**
 * Pruebas funcionales para app/ThirdParty/Ragnos/Helpers/utiles_helper.php.
 * Las funciones son globales: se invocan con prefijo `\` para evitar
 * la resolución en el namespace local cuando los tests corren aislados.
 */
class UtilesHelperTest extends RagnosTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper([
            'App\ThirdParty\Ragnos\Helpers\utiles_helper',
            'App\ThirdParty\Ragnos\Helpers\ragnos_helper',
        ]);
    }

    public function testIsJsonDetectaJsonValido(): void
    {
        $this->assertTrue(\isJson('{}'));
        $this->assertTrue(\isJson('[]'));
        $this->assertTrue(\isJson('{"name":"John","age":30}'));
        $this->assertTrue(\isJson('["a","b","c"]'));
    }

    public function testIsJsonRechazaEntradasInvalidas(): void
    {
        $this->assertFalse(\isJson('not json at all'));
        $this->assertFalse(\isJson('<root><item>v</item></root>'));
        // No es string -> false directo
        $this->assertFalse(\isJson(123));
        $this->assertFalse(\isJson(null));
        $this->assertFalse(\isJson([]));
    }

    public function testRemoveNewLinesReemplazaTodosLosSaltosDeLineaPorEspacio(): void
    {
        $this->assertSame('a b  c d', \removeNewLines("a\nb\r\nc\rd"));
        $this->assertSame('Hola Mundo', \removeNewLines("Hola\nMundo"));
        $this->assertSame('', \removeNewLines(''));
    }

    public function testRemoveNewLinesNoAlteraTextoSinSaltos(): void
    {
        $this->assertSame('Sin saltos', \removeNewLines('Sin saltos'));
    }

    public function testIfSetDevuelveValorSiNoEstaVacio(): void
    {
        $v = 'real';
        $this->assertSame('real', \ifSet($v, 'default'));
    }

    public function testIfSetDevuelveDefaultParaCadenaVacia(): void
    {
        $v = '';
        $this->assertSame('default', \ifSet($v, 'default'));
    }

    public function testIfSetDevuelveDefaultParaCero(): void
    {
        $v = 0;
        $this->assertSame('default', \ifSet($v, 'default'),
            'ifSet usa empty() internamente; 0 cuenta como "no establecido"');
    }

    public function testIfSetDevuelveDefaultParaNull(): void
    {
        $v = null;
        $this->assertSame('default', \ifSet($v, 'default'));
    }

    public function testArrayToSelectGeneraOpcionesConSeleccionado(): void
    {
        $opts = [
            ['v' => 'A', 't' => 'Alta'],
            ['v' => 'B', 't' => 'Baja'],
        ];
        $html = \arrayToSelect('estado', $opts, 'v', 't', 'A');

        $this->assertStringContainsString('<select name="estado"', $html);
        $this->assertStringContainsString('class="form-control"', $html);
        $this->assertStringContainsString('<option value="A" selected>Alta</option>', $html);
        $this->assertStringContainsString('<option value="B">Baja</option>', $html);
        $this->assertStringEndsWith('</select>', $html);
    }

    public function testArrayToSelectEscapaContenidoHTMLPeligroso(): void
    {
        $opts = [
            ['v' => '1', 't' => '<script>alert(1)</script>'],
        ];
        $html = \arrayToSelect('campo', $opts, 'v', 't');

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testArrayToSelectMultipleAgregaSufijoYAtributo(): void
    {
        $opts = [['v' => '1', 't' => 'Uno']];
        $html = \arrayToSelect('roles', $opts, 'v', 't', null, ['multiple' => true]);

        // Sufijo [] añadido al name
        $this->assertStringContainsString('name="roles[]"', $html);
        // Atributo multiple presente
        $this->assertStringContainsString('multiple', $html);
    }

    public function testArrayToSelectAtributosExtraSeRenderizan(): void
    {
        $opts = [['v' => '1', 't' => 'Uno']];
        $html = \arrayToSelect('campo', $opts, 'v', 't', null, ['id' => 'mi-select', 'class' => 'custom']);

        $this->assertStringContainsString('id="mi-select"', $html);
        $this->assertStringContainsString('class="custom"', $html);
    }

    public function testExecuteQueryConSQLite(): void
    {
        // executeQuery() invoca \Config\Database::connect() (grupo default).
        // Configuramos el grupo default como SQLite en memoria para el test.
        $config                       = config('Database');
        $original                     = $config->default;
        $config->default['DBDriver']  = 'SQLite3';
        $config->default['database']  = ':memory:';
        $config->default['DBDebug']   = false;

        // Reset para que connect() devuelva una conexión fresca con la nueva config
        \Config\Database::reset();
        $defDb = \Config\Database::connect();
        $defDb->query('CREATE TABLE prueba_exec (id INTEGER PRIMARY KEY, nombre TEXT)');
        $defDb->query("INSERT INTO prueba_exec (nombre) VALUES ('Ana'), ('Bea'), ('Cris')");

        $rows = \executeQuery('SELECT nombre FROM prueba_exec ORDER BY id');
        $this->assertCount(3, $rows);
        $this->assertSame('Ana', $rows[0]['nombre']);
        $this->assertSame('Cris', $rows[2]['nombre']);

        // Restaurar
        $config->default = $original;
        \Config\Database::reset();
    }

    public function testQueryToAssocArrayConSQLite(): void
    {
        $config                       = config('Database');
        $original                     = $config->default;
        $config->default['DBDriver']  = 'SQLite3';
        $config->default['database']  = ':memory:';
        $config->default['DBDebug']   = false;

        $defDb = \Config\Database::connect();
        $defDb->query('CREATE TABLE mapa (k TEXT, v TEXT)');
        $defDb->query("INSERT INTO mapa (k, v) VALUES ('a','Alpha'), ('b','Beta')");

        $result = \queryToAssocArray('SELECT k, v FROM mapa', 'k', 'v');
        $this->assertSame(['a' => 'Alpha', 'b' => 'Beta'], $result);

        $config->default = $original;
    }

    public function testQueryToAssocArrayReturnEmptyOnMissingKeys(): void
    {
        $config                       = config('Database');
        $original                     = $config->default;
        $config->default['DBDriver']  = 'SQLite3';
        $config->default['database']  = ':memory:';
        $config->default['DBDebug']   = false;

        $defDb = \Config\Database::connect();
        $defDb->query('CREATE TABLE mapa2 (k TEXT, v TEXT)');
        $defDb->query("INSERT INTO mapa2 (k, v) VALUES ('a','Alpha')");

        // Pedimos un campo que no existe => devuelve array vacío
        $result = \queryToAssocArray('SELECT k, v FROM mapa2', 'no_existe', 'v');
        $this->assertSame([], $result);

        $config->default = $original;
    }

    public function testCurrencyDevuelveStringFormateado(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is not loaded');
        }
        $result = \currency(1234.56);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        // Debe contener algún dígito y separador decimal
        $this->assertMatchesRegularExpression('/\d/', $result);
    }
}

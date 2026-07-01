<?php

namespace Tests\Ragnos\Models\Traits;

use Tests\Ragnos\RagnosTestCase;
use Tests\Support\RequestSimulator;
use App\ThirdParty\Ragnos\Models\RDatasetModel;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;

/**
 * Modelo concreto para JsonResultTrait.
 */
class JsonProductoModel extends RDatasetModel
{
    public $table = 'productos_json';
    public $primaryKey = 'id';
    protected $returnType = 'array';

    public function __construct($db = null)
    {
        parent::__construct();
        if ($db !== null) {
            $this->db = $db;
        }
    }
}

/**
 * Pruebas reales para JsonResultTrait. Verifica:
 * - getTableAjax y getTableForAPI con $table vacío retornan null
 * - getTableAjax produce JSON con estructura draw/recordsTotal/recordsFiltered/data
 * - Paginación (length/start)
 * - Ordenamiento via input order[0][column]
 * - getTableForAPI retorna estructura [data, countAll]
 */
class JsonResultTraitTest extends RagnosTestCase
{
    use RequestSimulator;

    private JsonProductoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadRagnosHelpers();
        $this->resetRequest();

        $this->createTestTable('productos_json', [
            'nombre' => ['type' => 'TEXT'],
            'precio' => ['type' => 'REAL'],
        ]);
        for ($i = 1; $i <= 5; $i++) {
            $this->insertTestData('productos_json', [
                'nombre' => "Item {$i}",
                'precio' => $i * 10,
            ]);
        }

        $this->model              = new JsonProductoModel($this->db);
        $this->model->tablefields = ['nombre', 'precio'];
    }

    protected function tearDown(): void
    {
        $this->dropTestTable('productos_json');
        $this->resetRequest();
        parent::tearDown();
    }

    public function testGetTableAjaxSinTableRetornaNull(): void
    {
        $modelo        = new RConcreteDatasetModel();
        $modelo->table = '';
        $this->assertNull($modelo->getTableAjax());
    }

    public function testGetTableForAPISinTableRetornaNull(): void
    {
        $modelo        = new RConcreteDatasetModel();
        $modelo->table = '';
        $this->assertNull($modelo->getTableForAPI());
    }

    public function testGetTableAjaxRetornaJsonValido(): void
    {
        $json = $this->model->getTableAjax();
        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertIsArray($decoded);
    }

    public function testGetTableAjaxContieneLlavesEstandarDataTables(): void
    {
        $decoded = json_decode($this->model->getTableAjax(), true);

        $this->assertArrayHasKey('draw', $decoded);
        $this->assertArrayHasKey('recordsTotal', $decoded);
        $this->assertArrayHasKey('recordsFiltered', $decoded);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('sSearch', $decoded);
    }

    public function testRecordsTotalCoincideConCountEnBD(): void
    {
        $decoded = json_decode($this->model->getTableAjax(), true);
        $this->assertSame(5, $decoded['recordsTotal']);
        $this->assertSame(5, $decoded['recordsFiltered']);
    }

    public function testDataContieneFilasDeLaTabla(): void
    {
        $decoded = json_decode($this->model->getTableAjax(), true);
        // Default limit es 10, hay 5 filas
        $this->assertCount(5, $decoded['data']);

        // Cada fila tiene: tablefields (2: nombre, precio) + el id como columna extra
        // Total: 3 columnas
        $this->assertCount(3, $decoded['data'][0]);
    }

    public function testPaginacionConLengthYStart(): void
    {
        $this->setGet(['length' => '2', 'start' => '0', 'draw' => '7']);
        $decoded = json_decode($this->model->getTableAjax(), true);

        $this->assertCount(2, $decoded['data']);
        $this->assertSame(7, $decoded['draw']);
        $this->assertSame(5, $decoded['recordsTotal'], 'recordsTotal cuenta TODA la tabla');
    }

    public function testPaginacionDevuelveSegundaPagina(): void
    {
        $this->setGet(['length' => '2', 'start' => '2']);
        $decoded = json_decode($this->model->getTableAjax(), true);

        $this->assertCount(2, $decoded['data']);
    }

    public function testGetTableForAPIRetornaArrayConDataYCountAll(): void
    {
        $result = $this->model->getTableForAPI();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('countAll', $result);
        $this->assertSame(5, $result['countAll']);
    }

    public function testGetTableForAPIDataFilasSonAssocArrays(): void
    {
        $result = $this->model->getTableForAPI();
        $this->assertNotEmpty($result['data']);
        $primero = $result['data'][0];

        // Cada fila tiene los tablefields como llaves
        $this->assertArrayHasKey('nombre', $primero);
        $this->assertArrayHasKey('precio', $primero);
        // Plus la clave del primary key
        $this->assertArrayHasKey('id', $primero);
    }

    public function testGenerateJsonResultEnsamblaEstructura(): void
    {
        $this->model->completeFieldList();
        $query = $this->model->builder()->select('nombre, precio, id')->limit(2)->get();

        $json    = $this->model->generateJsonResult($query, 100);
        $decoded = json_decode($json, true);

        $this->assertSame(100, $decoded['recordsTotal']);
        $this->assertSame(100, $decoded['recordsFiltered']);
        $this->assertCount(2, $decoded['data']);
    }

    public function testOrdenamientoViaOrderColumnDescendente(): void
    {
        $this->setGet([
            'order' => [['column' => '1', 'dir' => 'desc']], // ordenar por precio (col 1) DESC
        ]);
        $decoded = json_decode($this->model->getTableAjax(), true);

        // El primer registro debe ser el de precio más alto (Item 5 = $50)
        $this->assertStringContainsString('Item 5', $decoded['data'][0][0]);
    }

    public function testBusquedaGlobalReduceData(): void
    {
        $this->setGet(['search' => ['value' => 'Item 3']]);
        $decoded = json_decode($this->model->getTableAjax(), true);

        // Solo el item 3 coincide
        $this->assertSame(1, $decoded['recordsFiltered']);
        $this->assertCount(1, $decoded['data']);
        $this->assertStringContainsString('Item 3', $decoded['data'][0][0]);
    }
}

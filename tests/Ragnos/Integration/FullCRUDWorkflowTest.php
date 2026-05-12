<?php

namespace Tests\Ragnos\Integration;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Controllers\RDataset;
use App\ThirdParty\Ragnos\Controllers\Ragnos;
use App\ThirdParty\Ragnos\Models\RDatasetModel;

class FullCRUDProductoModel extends RDatasetModel
{
    public $table         = 'productos_fullcrud';
    public $primaryKey    = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    public function __construct($db = null)
    {
        parent::__construct();
        if ($db !== null) {
            $this->db = $db;
        }
    }
}

/**
 * Tests de integración end-to-end CRUD usando modelo + controller real.
 * Recorre: configuración -> insert (con _beforeInsert) -> read -> update -> delete.
 */
class FullCRUDWorkflowTest extends RagnosTestCase
{
    private RDataset $controller;
    private FullCRUDProductoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        helper([
            'App\ThirdParty\Ragnos\Helpers\utiles_helper',
            'App\ThirdParty\Ragnos\Helpers\ragnos_helper',
            'text',
        ]);

        $this->createTestTable('productos_fullcrud', [
            'nombre'      => ['type' => 'TEXT'],
            'precio'      => ['type' => 'REAL'],
            'stock'       => ['type' => 'INTEGER'],
            'creado_por'  => ['type' => 'INTEGER', 'null' => true],
        ]);

        Ragnos::$CI = null;

        // Controller con _beforeInsert que añade creado_por automáticamente
        $this->controller = new class extends RDataset {
            public int $createdById = 42;

            public function _beforeInsert(&$dataArray): void
            {
                $dataArray['creado_por'] = $this->createdById;
            }
        };

        $this->model = new FullCRUDProductoModel($this->db);
        $this->controller->setModel($this->model);
        $this->model->tablefields = ['nombre', 'precio', 'stock', 'creado_por'];
        $this->model->addFieldFromArray('nombre', ['rules' => 'required']);

        // Desactivar auditoría
        $audit = new \ReflectionProperty($this->model, 'enableAudit');
        $audit->setAccessible(true);
        $audit->setValue($this->model, false);

        \CodeIgniter\Config\Services::reset(true);
        $_POST = [];
        $_GET  = [];
    }

    protected function tearDown(): void
    {
        $this->dropTestTable('productos_fullcrud');
        \CodeIgniter\Config\Services::reset(true);
        $_POST = [];
        $_GET  = [];
        parent::tearDown();
    }

    private function setPost(array $data): void
    {
        service('request')->setGlobal('post', $data);
        service('request')->setGlobal('request', $data);
    }

    public function testFlujoCompletoCreateReadUpdateDelete(): void
    {
        // ----- CREATE -----
        $this->setPost(['nombre' => 'Lapicero', 'precio' => '10.5', 'stock' => '100']);
        $this->model->processFormInput();
        $this->assertNotNull($this->model->insertedId);
        $idLapicero = $this->model->insertedId;
        $this->assertTableCount('productos_fullcrud', 1);

        // Segundo producto
        $this->setPost(['nombre' => 'Cuaderno', 'precio' => '25', 'stock' => '50']);
        // Reset estado del modelo entre operaciones
        $this->model->insertedId = null;
        $this->model->errors     = [];
        $this->model->processFormInput();
        $idCuaderno = $this->model->insertedId;

        // Tercero
        $this->setPost(['nombre' => 'Goma', 'precio' => '5', 'stock' => '200']);
        $this->model->insertedId = null;
        $this->model->errors     = [];
        $this->model->processFormInput();
        $idGoma = $this->model->insertedId;

        $this->assertTableCount('productos_fullcrud', 3);

        // Verificar callback _beforeInsert añadió creado_por
        $rowLapicero = $this->getTableRecord('productos_fullcrud', ['id' => $idLapicero]);
        $this->assertEquals(42, $rowLapicero['creado_por'], '_beforeInsert debió añadir creado_por=42');

        // ----- READ -----
        $todos = $this->model->findAll();
        $this->assertCount(3, $todos);

        $uno = $this->model->find($idCuaderno);
        $this->assertSame('Cuaderno', $uno['nombre']);
        $this->assertEquals(25, $uno['precio']);

        // ----- UPDATE -----
        $this->setPost([
            'id'                       => (string) $idCuaderno,
            'nombre'                   => 'Cuaderno Premium',
            'precio'                   => '30',
            'stock'                    => '50',
            'creado_por'               => '42',
            'Ragnos_value_ant_id'      => (string) $idCuaderno,
            'Ragnos_value_ant_nombre'  => 'Cuaderno',
            'Ragnos_value_ant_precio'  => '25',
            'Ragnos_value_ant_stock'   => '50',
            'Ragnos_value_ant_creado_por' => '42',
        ]);
        $this->model->errors = [];
        $this->model->processFormInput();

        $actualizado = $this->model->find($idCuaderno);
        $this->assertSame('Cuaderno Premium', $actualizado['nombre']);
        $this->assertEquals(30, $actualizado['precio']);

        // ----- DELETE -----
        // performDelete requiere que createInputDataArray() devuelva datos.
        // Por eso enviamos un valor "nuevo" distinto al "ant" en algún campo.
        $this->setPost([
            'id'                       => (string) $idGoma,
            'nombre'                   => 'Goma',
            'Ragnos_value_ant_id'      => (string) $idGoma,
            'Ragnos_value_ant_nombre'  => 'PreviousValue', // distinto => hasChanged=true
        ]);
        $deleted = $this->model->performDelete($idGoma);
        $this->assertTrue((bool) $deleted);
        $this->assertTableCount('productos_fullcrud', 2);
        $this->assertNull($this->model->find($idGoma));
    }

    public function testValidacionRequiredImpideInsertConCamposVacios(): void
    {
        $this->setPost(['nombre' => '', 'precio' => '10']);
        $this->model->processFormInput();

        // No debe crearse
        $this->assertTableCount('productos_fullcrud', 0);
        $this->assertNotEmpty($this->model->errors);
        $this->assertArrayHasKey('nombre', $this->model->errors);
    }

    public function testFindAllDevuelveArraysCuandoReturnTypeEsArray(): void
    {
        $this->setPost(['nombre' => 'A', 'precio' => '1', 'stock' => '1']);
        $this->model->processFormInput();

        $todos = $this->model->findAll();
        $this->assertNotEmpty($todos);
        $this->assertIsArray($todos[0]);
        $this->assertArrayHasKey('nombre', $todos[0]);
    }

    public function testCountAllResultsCuentaTodosLosRegistros(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $this->setPost(['nombre' => "P{$i}", 'precio' => "{$i}", 'stock' => '1']);
            $this->model->insertedId = null;
            $this->model->errors     = [];
            $this->model->processFormInput();
        }

        $this->assertSame(4, $this->model->countAllResults());
    }

    public function testCallbackBeforeInsertPuedeRechazarDatosViaExcepcion(): void
    {
        $controller = new class extends RDataset {
            public function _beforeInsert(&$dataArray): void
            {
                if (($dataArray['precio'] ?? 0) < 0) {
                    throw new \Exception('precio invalido');
                }
            }
        };
        $modelo = new FullCRUDProductoModel($this->db);
        $controller->setModel($modelo);
        $modelo->tablefields = ['nombre', 'precio', 'stock'];

        $audit = new \ReflectionProperty($modelo, 'enableAudit');
        $audit->setAccessible(true);
        $audit->setValue($modelo, false);

        $this->setPost(['nombre' => 'X', 'precio' => '-1', 'stock' => '1']);
        $modelo->processFormInput();

        // La excepción se captura y se anota en errors
        $this->assertArrayHasKey('general_error', $modelo->errors);
        $this->assertSame('precio invalido', $modelo->errors['general_error']);
        $this->assertTableCount('productos_fullcrud', 0);
    }
}

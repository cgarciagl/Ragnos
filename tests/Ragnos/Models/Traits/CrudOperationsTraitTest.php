<?php

namespace Tests\Ragnos\Models\Traits;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Controllers\RDataset;
use App\ThirdParty\Ragnos\Controllers\Ragnos;
use App\ThirdParty\Ragnos\Models\RDatasetModel;

/**
 * Modelo concreto para CrudOperationsTrait sobre tabla 'productos_crud'.
 */
class CrudProductoModel extends RDatasetModel
{
    public $table         = 'productos_crud';
    public $primaryKey    = 'id';
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
 * Pruebas reales para CrudOperationsTrait. Verifica:
 * - createInputDataArray extrae campos del request
 * - performInsert/performUpdate persisten datos (vía processFormInput)
 * - performDelete elimina
 * - flags canInsert/canUpdate/canDelete bloquean operación
 * - hooks _beforeInsert pueden modificar datos
 *
 * enableAudit se desactiva vía reflection para no requerir tabla gen_audit_logs.
 */
class CrudOperationsTraitTest extends RagnosTestCase
{
    private RDataset $controller;
    private CrudProductoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        helper([
            'App\ThirdParty\Ragnos\Helpers\utiles_helper',
            'App\ThirdParty\Ragnos\Helpers\ragnos_helper',
            'text',
        ]);

        $this->createTestTable('productos_crud', [
            'nombre' => ['type' => 'TEXT'],
            'precio' => ['type' => 'REAL', 'null' => true],
            'stock'  => ['type' => 'INTEGER', 'null' => true],
        ]);

        // Reset singleton de Ragnos
        Ragnos::$CI = null;

        $this->controller = new class extends RDataset {
            public array $beforeInsertCalls = [];
            public array $afterInsertCalls  = [];
            public array $beforeUpdateCalls = [];
            public bool $beforeDeleteCalled = false;
            public bool $afterDeleteCalled  = false;

            public function _beforeInsert(&$dataArray): void
            {
                $this->beforeInsertCalls[] = $dataArray;
                // Modificamos los datos para verificar referencia
                $dataArray['nombre'] = strtoupper($dataArray['nombre'] ?? '');
            }
            public function _afterInsert(): void
            {
                $this->afterInsertCalls[] = true;
            }
            public function _beforeUpdate(&$dataArray): void
            {
                $this->beforeUpdateCalls[] = $dataArray;
            }
            public function _beforeDelete(): void
            {
                $this->beforeDeleteCalled = true;
            }
            public function _afterDelete(): void
            {
                $this->afterDeleteCalled = true;
            }
        };

        $this->model = new CrudProductoModel($this->db);
        $this->controller->setModel($this->model);
        $this->model->tablefields = ['nombre', 'precio', 'stock'];

        // Desactivar auditoría (no necesitamos gen_audit_logs aquí)
        $audit = new \ReflectionProperty($this->model, 'enableAudit');
        $audit->setAccessible(true);
        $audit->setValue($this->model, false);

        \CodeIgniter\Config\Services::reset(true);
        $_POST = [];
        $_GET  = [];
        \setOldRecordCache([]);
    }

    protected function tearDown(): void
    {
        $this->dropTestTable('productos_crud');
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

    public function testCreateInputDataArraySinFieldsDevuelveArrayVacio(): void
    {
        // sin ofieldlist, no hay campos para extraer
        $resultado = $this->model->createInputDataArray();
        $this->assertSame([], $resultado);
    }

    public function testCreateInputDataArrayExtraeCamposDelPost(): void
    {
        $this->setPost(['nombre' => 'Lapicero', 'precio' => '15.5', 'stock' => '100']);
        $this->model->completeFieldList();

        $resultado = $this->model->createInputDataArray();
        $this->assertSame('Lapicero', $resultado['nombre']);
        $this->assertSame('15.5', $resultado['precio']);
        $this->assertSame('100', $resultado['stock']);
    }

    public function testCreateInputDataArrayIgnoraCamposConQuery(): void
    {
        $this->model->addFieldFromArray('calculado', [
            'query' => 'SELECT 1',
        ]);
        $this->model->completeFieldList();
        $this->setPost(['nombre' => 'X', 'calculado' => 'ignorado']);

        $resultado = $this->model->createInputDataArray();
        $this->assertArrayNotHasKey('calculado', $resultado);
    }

    public function testProcessFormInputCreaRegistroNuevo(): void
    {
        $this->model->completeFieldList();
        $this->setPost([
            'nombre' => 'lapicero',
            'precio' => '10',
            'stock'  => '50',
        ]);

        $this->model->processFormInput();

        $this->assertTableCount('productos_crud', 1);
        $this->assertNotNull($this->model->insertedId);

        $row = $this->getTableRecord('productos_crud', ['id' => $this->model->insertedId]);
        // El callback _beforeInsert hizo strtoupper:
        $this->assertSame('LAPICERO', $row['nombre']);
    }

    public function testHookBeforeInsertSeInvocaUnaVez(): void
    {
        $this->model->completeFieldList();
        $this->setPost(['nombre' => 'X']);

        $this->model->processFormInput();
        $this->assertCount(1, $this->controller->beforeInsertCalls);
    }

    public function testHookAfterInsertSeInvoca(): void
    {
        $this->model->completeFieldList();
        $this->setPost(['nombre' => 'X']);

        $this->model->processFormInput();
        $this->assertCount(1, $this->controller->afterInsertCalls);
    }

    public function testProcessFormInputActualizaCuandoVienePrimaryKey(): void
    {
        $id = $this->db->table('productos_crud')->insert([
            'nombre' => 'Antes', 'precio' => 1, 'stock' => 1,
        ]);
        $insertId = $this->db->insertID();

        $this->model->completeFieldList();
        $this->setPost([
            'id'                       => (string) $insertId,
            'nombre'                   => 'Despues',
            'Ragnos_value_ant_nombre'  => 'Antes',
            'Ragnos_value_ant_precio'  => '1',
            'Ragnos_value_ant_stock'   => '1',
        ]);

        $this->model->processFormInput();

        $row = $this->getTableRecord('productos_crud', ['id' => $insertId]);
        $this->assertSame('Despues', $row['nombre']);
        $this->assertCount(1, $this->controller->beforeUpdateCalls);
    }

    public function testProcessFormInputRellenaErroresSiValidacionFalla(): void
    {
        $this->model->addFieldFromArray('nombre', [
            'rules' => 'required|min_length[5]',
        ]);
        $this->model->completeFieldList();
        $this->setPost(['nombre' => 'no']); // viola min_length

        $this->model->processFormInput();
        $this->assertNotEmpty($this->model->errors);
        $this->assertArrayHasKey('nombre', $this->model->errors);
    }

    public function testPerformDeleteEliminaRegistroExistente(): void
    {
        $this->db->table('productos_crud')->insert(['nombre' => 'Borrame']);
        $id = $this->db->insertID();
        $this->assertTableCount('productos_crud', 1);

        $this->model->completeFieldList();
        // performDelete necesita inputDataArray con al menos un campo para entrar al flujo
        $this->setPost([
            'id'                      => (string) $id,
            'nombre'                  => 'Borrame',
            'Ragnos_value_ant_nombre' => 'Otro',
        ]);

        $result = $this->model->performDelete($id);
        $this->assertTrue((bool) $result);
        $this->assertTableCount('productos_crud', 0);
        $this->assertTrue($this->controller->beforeDeleteCalled);
        $this->assertTrue($this->controller->afterDeleteCalled);
    }

    public function testPerformDeleteRetornaFalseCuandoCanDeleteFalso(): void
    {
        $this->model->canDelete = false;
        // performDelete sale temprano si canDelete=false (sin tocar BD).
        $result = $this->model->performDelete(1);
        $this->assertFalse($result);
    }

    public function testInsertBloqueadoCuandoCanInsertFalso(): void
    {
        $this->model->canInsert  = false;
        $this->model->completeFieldList();
        $this->setPost(['nombre' => 'NoInsert']);

        $this->model->processFormInput();
        $this->assertTableCount('productos_crud', 0);
        $this->assertNull($this->model->insertedId);
    }
}

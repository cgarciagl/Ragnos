<?php

namespace Tests\Ragnos\Models\Traits;

use Tests\Ragnos\RagnosTestCase;
use Tests\Support\CrudTestSetup;

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
    use CrudTestSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initCrudTest('productos_crud', [
            'nombre' => ['type' => 'TEXT'],
            'precio' => ['type' => 'REAL', 'null' => true],
            'stock'  => ['type' => 'INTEGER', 'null' => true],
        ], ['nombre', 'precio', 'stock']);
    }

    protected function tearDown(): void
    {
        $this->cleanupCrudTest();
        parent::tearDown();
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
        $id       = $this->db->table('productos_crud')->insert([
            'nombre' => 'Antes',
            'precio' => 1,
            'stock'  => 1,
        ]);
        $insertId = $this->db->insertID();

        $this->model->completeFieldList();
        $this->setPost([
            'id'                      => (string) $insertId,
            'Ragnos_action'           => 'update',
            'nombre'                  => 'Despues',
            'Ragnos_value_ant_nombre' => 'Antes',
            'Ragnos_value_ant_precio' => '1',
            'Ragnos_value_ant_stock'  => '1',
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
        $this->assertSame('No tiene permisos para eliminar registros.', $this->model->errors['general_error']);
    }

    public function testInsertBloqueadoCuandoCanInsertFalso(): void
    {
        $this->model->canInsert = false;
        $this->model->completeFieldList();
        $this->setPost(['nombre' => 'NoInsert']);

        $this->model->processFormInput();
        $this->assertTableCount('productos_crud', 0);
        $this->assertNull($this->model->insertedId);
        $this->assertSame('No tiene permisos para crear registros.', $this->model->errors['general_error']);
    }

    public function testInsertaLlavePrimariaNaturalCuandoNoEsAutoincremental(): void
    {
        $this->dropTestTable('productos_crud');
        $this->db->query('CREATE TABLE productos_crud (codigo TEXT PRIMARY KEY, nombre TEXT NOT NULL)');

        $this->model->table      = 'productos_crud';
        $this->model->primaryKey = 'codigo';
        $this->model->setAutoIncrement(false);
        $this->model->tablefields = ['codigo', 'nombre'];
        $this->model->ofieldlist  = [];
        $this->model->completeFieldList();
        $this->setPost([
            'Ragnos_action' => 'insert',
            'codigo'        => 'PRD-001',
            'nombre'        => 'Producto natural',
        ]);

        $this->model->processFormInput();

        $row = $this->getTableRecord('productos_crud', ['codigo' => 'PRD-001']);
        $this->assertSame('PRODUCTO NATURAL', $row['nombre']);
        $this->assertSame('PRD-001', $this->model->insertedId);
    }

    public function testInsertHaceRollbackSiFallaHookPosterior(): void
    {
        $this->controller->onAfterInsert = static function (): void {
            throw new \RuntimeException('Fallo posterior');
        };
        $this->model->completeFieldList();
        $this->setPost(['Ragnos_action' => 'insert', 'nombre' => 'No persistir']);

        $this->model->processFormInput();

        $this->assertTableCount('productos_crud', 0);
        $this->assertNull($this->model->insertedId);
        $this->assertSame('Fallo posterior', $this->model->errors['general_error']);
    }

    public function testPerformDeleteAceptaIdSinDatosDeFormulario(): void
    {
        $this->db->table('productos_crud')->insert(['nombre' => 'Borrado API']);
        $id = $this->db->insertID();
        $this->model->completeFieldList();
        $this->setPost([]);

        $result = $this->model->performDelete($id);

        $this->assertTrue($result);
        $this->assertTableCount('productos_crud', 0);
    }
}

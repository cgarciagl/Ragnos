<?php

namespace Tests\Ragnos\Integration;

use Tests\Ragnos\RagnosTestCase;
use Tests\Support\CrudTestSetup;

/**
 * Tests de integración end-to-end CRUD usando modelo + controller real.
 * Recorre: configuración -> insert (con _beforeInsert) -> read -> update -> delete.
 */
class FullCRUDWorkflowTest extends RagnosTestCase
{
    use CrudTestSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initCrudTest('productos_fullcrud', [
            'nombre'     => ['type' => 'TEXT'],
            'precio'     => ['type' => 'REAL'],
            'stock'      => ['type' => 'INTEGER'],
            'creado_por' => ['type' => 'INTEGER', 'null' => true],
        ], ['nombre', 'precio', 'stock', 'creado_por']);

        // Controller con _beforeInsert que añade creado_por automáticamente
        $this->controller->onBeforeInsert = function (&$dataArray): void {
            $dataArray['creado_por'] = 42;
        };

        $this->model->addFieldFromArray('nombre', ['rules' => 'required']);
    }

    protected function tearDown(): void
    {
        $this->cleanupCrudTest();
        parent::tearDown();
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
            'id'                          => (string) $idCuaderno,
            'nombre'                      => 'Cuaderno Premium',
            'precio'                      => '30',
            'stock'                       => '50',
            'creado_por'                  => '42',
            'Ragnos_value_ant_id'         => (string) $idCuaderno,
            'Ragnos_value_ant_nombre'     => 'Cuaderno',
            'Ragnos_value_ant_precio'     => '25',
            'Ragnos_value_ant_stock'      => '50',
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
            'id'                      => (string) $idGoma,
            'nombre'                  => 'Goma',
            'Ragnos_value_ant_id'     => (string) $idGoma,
            'Ragnos_value_ant_nombre' => 'PreviousValue', // distinto => hasChanged=true
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
        $controller                 = new \Tests\Support\Controllers\TestRDataset();
        $controller->onBeforeInsert = function (&$dataArray): void {
            if (($dataArray['precio'] ?? 0) < 0) {
                throw new \Exception('precio invalido');
            }
        };
        $modelo                     = new \Tests\Support\Models\CrudTestModel($this->db);
        $controller->setModel($modelo);
        $modelo->configure('productos_fullcrud', ['nombre', 'precio', 'stock']);

        $this->setPost(['nombre' => 'X', 'precio' => '-1', 'stock' => '1']);
        $modelo->processFormInput();

        // La excepción se captura y se anota en errors
        $this->assertArrayHasKey('general_error', $modelo->errors);
        $this->assertSame('precio invalido', $modelo->errors['general_error']);
        $this->assertTableCount('productos_fullcrud', 0);
    }
}

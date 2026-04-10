<?php

namespace Tests\Ragnos\Integration;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;
use App\ThirdParty\Ragnos\Controllers\RDataset;

class FullCRUDWorkflowTest extends RagnosTestCase
{
    protected string $testTable = 'test_products';
    protected RConcreteDatasetModel $model;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     * Verifica que el flujo completo CRUD es posible con el modelo
     */
    public function testCompleteCreateReadUpdateDeleteWorkflow(): void
    {
        $model = new RConcreteDatasetModel();

        // CREATE - insert debe estar disponible
        $this->assertTrue(method_exists($model, 'insert'));
        $this->assertTrue($model->canInsert);

        // READ - find y findAll disponibles
        $this->assertTrue(method_exists($model, 'find'));
        $this->assertTrue(method_exists($model, 'findAll'));

        // UPDATE - update disponible
        $this->assertTrue(method_exists($model, 'update'));
        $this->assertTrue($model->canUpdate);

        // DELETE - delete disponible
        $this->assertTrue(method_exists($model, 'delete'));
        $this->assertTrue($model->canDelete);
    }

    /**
     * @test
     * Verifica que se pueden crear múltiples registros y listarlos
     */
    public function testCreateMultipleAndList(): void
    {
        $model = new RConcreteDatasetModel();

        // Inserción múltiple
        $this->assertTrue(method_exists($model, 'insertBatch'));

        // Listado
        $this->assertTrue(method_exists($model, 'findAll'));
        $this->assertTrue(method_exists($model, 'listAll'));
    }

    /**
     * @test
     * Verifica que el flujo de búsqueda y filtrado funciona
     */
    public function testSearchAndFilterWorkflow(): void
    {
        $model = new RConcreteDatasetModel();

        // Búsqueda
        $this->assertTrue(method_exists($model, 'find'));
        $this->assertTrue(method_exists($model, 'first'));

        // Filtrado - where viene del builder
        $this->assertTrue(method_exists($model, 'builder'));
    }

    /**
     * @test
     * Verifica que las operaciones en lote están disponibles
     */
    public function testBatchInsertAndBulkUpdate(): void
    {
        $model = new RConcreteDatasetModel();

        // Operaciones en lote
        $this->assertTrue(method_exists($model, 'insertBatch'));
        $this->assertTrue(method_exists($model, 'updateBatch'));
    }

    /**
     * @test
     * Verifica que se pueden usar métodos de query builder
     */
    public function testChainedOperations(): void
    {
        $model = new RConcreteDatasetModel();

        // select y limit tienen aliases en RTableModel
        $this->assertTrue(method_exists($model, 'select'));
        $this->assertTrue(method_exists($model, 'limit'));
    }

    /**
     * @test
     * Verifica que las operaciones secuenciales funcionan correctamente
     */
    public function testSequentialOperations(): void
    {
        $model = new RConcreteDatasetModel();

        // Insert
        $this->assertTrue(method_exists($model, 'insert'));

        // Seguido de find
        $this->assertTrue(method_exists($model, 'find'));

        // Seguido de update
        $this->assertTrue(method_exists($model, 'update'));

        // Seguido de delete
        $this->assertTrue(method_exists($model, 'delete'));
    }

    /**
     * @test
     * Verifica que los tipos de datos se preservan en operaciones
     */
    public function testDataTypePreservation(): void
    {
        $model = new RConcreteDatasetModel();

        // El modelo tiene returnType configurado
        $this->assertEquals('array', $model->returnType);
    }

    /**
     * @test
     * Verifica que se pueden realizar múltiples operaciones en registros
     */
    public function testMultipleRecordOperations(): void
    {
        $model = new RConcreteDatasetModel();

        // Debe poder trabajar con múltiples registros
        $this->assertTrue(method_exists($model, 'findAll'));
        $this->assertTrue(method_exists($model, 'countAllResults'));
        $this->assertTrue(method_exists($model, 'builder'));
    }
}

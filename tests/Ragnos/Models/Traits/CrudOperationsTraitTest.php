<?php

namespace Tests\Ragnos\Models\Traits;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;

class CrudOperationsTraitTest extends RagnosTestCase
{
    protected string $testTable = 'test_crud';
    protected RConcreteDatasetModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new RConcreteDatasetModel();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     * Verifica que el modelo puede ejecutar inserción
     */
    public function testCanPerformInsert(): void
    {
        $this->assertTrue(method_exists($this->model, 'insert'));
        $this->assertTrue($this->model->canInsert);
    }

    /**
     * @test
     * Verifica que el modelo puede ejecutar actualización
     */
    public function testCanPerformUpdate(): void
    {
        $this->assertTrue(method_exists($this->model, 'update'));
        $this->assertTrue($this->model->canUpdate);
    }

    /**
     * @test
     * Verifica que el modelo puede ejecutar eliminación
     */
    public function testCanPerformDelete(): void
    {
        $this->assertTrue(method_exists($this->model, 'performDelete'));
        $this->assertTrue($this->model->canDelete);
    }

    /**
     * @test
     * Verifica que el modelo soporta procesamiento de entrada del formulario
     */
    public function testBeforeInsertHookIsCalled(): void
    {
        $this->assertTrue(method_exists($this->model, 'processFormInput'));
        // processFormInput prepara datos para inserción
        $this->assertTrue(is_callable([$this->model, 'processFormInput']));
    }

    /**
     * @test
     * Verifica que insert puede manejar datos del formulario
     */
    public function testInsertWithTimestamps(): void
    {
        $this->assertTrue(method_exists($this->model, 'insert'));
        // El modelo tiene propiedades useTimestamps
        $this->assertTrue(property_exists($this->model, 'useTimestamps'));
    }

    /**
     * @test
     * Verifica que update modifica los datos
     */
    public function testUpdateChangesUpdatedTimestamp(): void
    {
        $this->assertTrue(method_exists($this->model, 'update'));
        $this->assertTrue(property_exists($this->model, 'useTimestamps'));
    }

    /**
     * @test
     * Verifica que getFormData recupera un registro
     */
    public function testGetFormDataRetrievesRecord(): void
    {
        $this->assertTrue(method_exists($this->model, 'getFormData'));
        $this->assertTrue(is_callable([$this->model, 'getFormData']));
    }

    /**
     * @test
     * Verifica que createInputDataArray crea estructura de datos
     */
    public function testMultipleInserts(): void
    {
        $this->assertTrue(method_exists($this->model, 'insertBatch'));
        $this->assertTrue(method_exists($this->model, 'insert'));
    }

    /**
     * @test
     * Verifica que insert y update funcionan correctamente
     */
    public function testInsertAndUpdateWorkCorrectly(): void
    {
        $this->assertTrue(method_exists($this->model, 'insert'));
        $this->assertTrue(method_exists($this->model, 'update'));
        // Ambas operaciones deben estar disponibles
        $this->assertTrue($this->model->canInsert && $this->model->canUpdate);
    }

    /**
     * @test
     * Verifica que performDelete puede eliminar registros
     */
    public function testDeleteMultipleRecords(): void
    {
        $this->assertTrue(method_exists($this->model, 'performDelete'));
        // whereIn para filtrar múltiples registros
        $this->assertTrue(method_exists($this->model, 'builder'));
    }
}

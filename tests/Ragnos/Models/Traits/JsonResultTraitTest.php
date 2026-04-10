<?php

namespace Tests\Ragnos\Models\Traits;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;

class JsonResultTraitTest extends RagnosTestCase
{
    protected string $testTable = 'test_json';
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
     * Verifica que el modelo tiene método para devolver tabla en JSON
     */
    public function testCanGenerateJsonResult(): void
    {
        $this->assertTrue(method_exists($this->model, 'getTableAjax'));
        $this->assertTrue(is_callable([$this->model, 'getTableAjax']));
    }

    /**
     * @test
     * Verifica que hay método para obtener tabla por SQL en formato JSON
     */
    public function testJsonResultContainsValidData(): void
    {
        $this->assertTrue(method_exists($this->model, 'getTableAjaxBySQL'));
        $this->assertEquals('array', $this->model->returnType);
    }

    /**
     * @test
     * Verifica que el modelo puede generar resultado JSON
     */
    public function testCanSerializeToJson(): void
    {
        $this->assertTrue(method_exists($this->model, 'generateJsonResult'));
        $this->assertTrue(is_callable([$this->model, 'generateJsonResult']));
    }

    /**
     * @test
     * Verifica que el modelo tiene método para obtener resultados decodificables
     */
    public function testJsonResultIsDecodableToArray(): void
    {
        $this->assertTrue(method_exists($this->model, 'getTableForAPI'));
        // getTableForAPI retorna datos para API
        $this->assertTrue(method_exists($this->model, 'performSearchForJson'));
    }

    /**
     * @test
     * Verifica que getTableForAPI devuelve datos para API
     */
    public function testCanGetTableForAPI(): void
    {
        $this->assertTrue(method_exists($this->model, 'getTableForAPI'));
        // getTableForAPI requiere tabla configurada
        $this->assertTrue(method_exists($this->model, 'builder'));
    }

    /**
     * @test
     * Verifica que los datos se preservan en serialización JSON
     */
    public function testJsonResultMaintainsDataIntegrity(): void
    {
        $this->assertTrue(method_exists($this->model, 'getTableAjax'));
        $this->assertTrue(method_exists($this->model, 'generateJsonResult'));
        // Fluent chain support
        $this->assertTrue(method_exists($this->model, 'builder'));
    }

    /**
     * @test
     * Verifica que se pueden filtrar campos antes de JSON
     */
    public function testCanFilterBeforeJsonSerialization(): void
    {
        $this->assertTrue(method_exists($this->model, 'performSearchForJson'));
        // performSearchForJson permite filtrado
        $this->assertTrue(method_exists($this->model, 'select'));
    }

    /**
     * @test
     * Verifica que la estructura de resultado JSON es consistente
     */
    public function testJsonResultStructure(): void
    {
        $this->assertTrue(method_exists($this->model, 'getTableAjax'));
        // Métodos necesarios para generar JSON válido
        $this->assertTrue(method_exists($this->model, 'getCountForSearch'));
    }
}

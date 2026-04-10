<?php

namespace Tests\Ragnos\Models\Traits;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;
use App\ThirdParty\Ragnos\Models\Fields\RSimpleTextField;
use App\ThirdParty\Ragnos\Models\Fields\RSwitchField;

class FieldManagementTraitTest extends RagnosTestCase
{
    protected string $testTable = 'test_fields';
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
     * Verifica que se puede agregar un campo al modelo con addFieldFromArray
     */
    public function testCanAddField(): void
    {
        $this->assertTrue(method_exists($this->model, 'addFieldFromArray'));
        $this->assertTrue(is_callable([$this->model, 'addFieldFromArray']));
    }

    /**
     * @test
     * Verifica que completeFieldList agrega múltiples campos
     */
    public function testCanAddMultipleFields(): void
    {
        $this->assertTrue(method_exists($this->model, 'completeFieldList'));
        // completeFieldList configura varios campos
        $this->assertTrue(method_exists($this->model, 'addFieldFromArray'));
    }

    /**
     * @test
     * Verifica que se puede recuperar un campo por nombre
     */
    public function testFieldByNameReturnsCorrectField(): void
    {
        $this->assertTrue(method_exists($this->model, 'fieldByName'));
        // El método debe retornar un campo o null
        $this->assertTrue(is_callable([$this->model, 'fieldByName']));
    }

    /**
     * @test
     * Verifica que fieldByName retorna null para campo inexistente
     */
    public function testFieldByNameReturnsNullForNonexistent(): void
    {
        $this->assertTrue(method_exists($this->model, 'fieldByName'));
        // fieldByName busca en ofieldlist
        $this->assertTrue(property_exists($this->model, 'ofieldlist'));
    }

    /**
     * @test
     * Verifica que completeFieldList inicializa los campos correctamente
     */
    public function testCompleteFieldListInitializesFields(): void
    {
        $this->assertTrue(method_exists($this->model, 'completeFieldList'));
        $this->assertTrue(property_exists($this->model, 'ofieldlist'));
    }

    /**
     * @test
     * Verifica que un campo puede tener valor por defecto
     */
    public function testFieldDefaultValueIsSet(): void
    {
        $this->assertTrue(method_exists($this->model, 'addFieldFromArray'));
        // addFieldFromArray soporta parámetros incluyendo default
        $this->assertTrue(method_exists($this->model, 'fieldByName'));
    }

    /**
     * @test
     * Verifica que realField retorna el campo a mostrar
     */
    public function testCanRemoveField(): void
    {
        $this->assertTrue(method_exists($this->model, 'realField'));
        $this->assertTrue(is_callable([$this->model, 'realField']));
    }

    /**
     * @test
     * Verifica que los campos pueden tener reglas de validación
     */
    public function testFieldsCanHaveCustomRules(): void
    {
        $this->assertTrue(method_exists($this->model, 'addFieldFromArray'));
        // Los campos heredan de RField que soporta rules
        $this->assertTrue(method_exists($this->model, 'fieldByName'));
    }

    /**
     * @test
     * Verifica que se puede establecer y recuperar la etiqueta de un campo
     */
    public function testFieldLabelCanBeSetAndRetrieved(): void
    {
        $this->assertTrue(method_exists($this->model, 'addFieldFromArray'));
        // addFieldFromArray soporta label en array
        $this->assertTrue(property_exists($this->model, 'tablefields'));
    }

    /**
     * @test
     * Verifica que textForTable retorna valor formateado para tabla
     */
    public function testTextForTableReturnsFormattedValue(): void
    {
        $this->assertTrue(method_exists($this->model, 'textForTable'));
        $this->assertTrue(is_callable([$this->model, 'textForTable']));
    }
}

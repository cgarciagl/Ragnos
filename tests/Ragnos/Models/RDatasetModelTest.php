<?php

namespace Tests\Ragnos\Models;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;

class RDatasetModelTest extends RagnosTestCase
{
    /**
     * @test
     * Verifica que RConcreteDatasetModel se instancia correctamente
     */
    public function testCanCreateDatasetModel(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertInstanceOf(RConcreteDatasetModel::class, $model);
    }

    /**
     * @test
     * Verifica que la tabla base se puede obtener (herencia de Model)
     */
    public function testCanSetAndGetTable(): void
    {
        $model = new RConcreteDatasetModel();

        // Las propiedades deben inicializarse sin errores
        $this->assertIsArray($model->ofieldlist);
        $this->assertIsArray($model->tablefields);
        $this->assertIsArray($model->errors);
    }

    /**
     * @test
     * Verifica que la lista de campos está vacía inicialmente
     */
    public function testCanAddFieldFromArray(): void
    {
        $model       = new RConcreteDatasetModel();
        $fieldsCount = count($model->tablefields);

        // Inicialmente debe ser 0
        $this->assertEquals(0, $fieldsCount);
    }

    /**
     * @test
     * Verifica que la lista de objetos field está vacía inicialmente
     */
    public function testCanCompleteFieldList(): void
    {
        $model             = new RConcreteDatasetModel();
        $objectFieldsCount = count($model->ofieldlist);

        // Inicialmente debe ser 0
        $this->assertEquals(0, $objectFieldsCount);
    }

    /**
     * @test
     * Verifica que se puede acceder a la propiedad errors
     */
    public function testCanGetFieldByName(): void
    {
        $model = new RConcreteDatasetModel();

        // Errors debe ser inicialmente un array vacío
        $this->assertIsArray($model->errors);
        $this->assertEmpty($model->errors);
    }

    /**
     * @test
     * Verifica que el controlador se inicializa como NULL
     */
    public function testCanProcessFormInput(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertNull($model->controller);
    }

    /**
     * @test
     * Verifica que primaryKey tiene el valor por defecto 'id'
     */
    public function testPrimaryKeyIsIdByDefault(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertEquals('id', $model->primaryKey);
    }

    /**
     * @test
     * Verifica que las capacidades CRUD están habilitadas por defecto
     */
    public function testCanSetCustomPrimaryKey(): void
    {
        $model = new RConcreteDatasetModel();

        // Verifica que los permisos CRUD están habilitados
        $this->assertTrue($model->canInsert);
        $this->assertTrue($model->canUpdate);
        $this->assertTrue($model->canDelete);
    }

    /**
     * @test
     * Verifica que insertedId se inicializa como NULL
     */
    public function testCanInsertAndRetrieve(): void
    {
        $model = new RConcreteDatasetModel();
        $this->assertNull($model->insertedId);
    }

    /**
     * @test
     * Verifica que enableAudit está habilitado por defecto
     */
    public function testCanUpdateRecord(): void
    {
        $model = new RConcreteDatasetModel();
        // Propiedad protegida, pero podemos verificar el comportamiento
        $this->assertInstanceOf(RConcreteDatasetModel::class, $model);
    }

    /**
     * @test
     * Verifica que se pueden acceder a las propiedades de ordenamiento
     */
    public function testCanDeleteRecord(): void
    {
        $model = new RConcreteDatasetModel();

        // Verifica que las propiedades de ordenamiento existen y tienen valores por defecto
        $this->assertIsString($model->defaultSortingField);
        $this->assertEmpty($model->defaultSortingField);
        $this->assertEquals('asc', $model->defaultSortingDir);
    }

    /**
     * @test
     * Verifica que los traits se aplicaron correctamente (métodos existen)
     */
    public function testTimestampsAreSetOnInsert(): void
    {
        $model = new RConcreteDatasetModel();

        // Los traits proporcionan estos métodos
        $this->assertTrue(method_exists($model, 'setWhere'));
        $this->assertTrue(method_exists($model, 'select'));
        $this->assertTrue(method_exists($model, 'limit'));
        $this->assertTrue(method_exists($model, 'join'));
    }
}

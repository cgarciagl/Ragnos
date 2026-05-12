<?php

namespace Tests\Ragnos\Models;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;
use App\ThirdParty\Ragnos\Models\Fields\RSimpleTextField;
use App\ThirdParty\Ragnos\Models\Fields\RIdField;

/**
 * Pruebas para RDatasetModel:
 * - Estado inicial documentado
 * - completeFieldList puebla ofieldlist + RIdField + allowedFields
 * - Comportamiento de propiedades públicas relevantes
 *
 * Para CRUD/búsqueda/JSON ver los tests de los traits correspondientes.
 */
class RDatasetModelTest extends RagnosTestCase
{
    public function testEstadoInicialColeccionesVacias(): void
    {
        $model = new RConcreteDatasetModel();

        $this->assertSame([], $model->ofieldlist);
        $this->assertSame([], $model->tablefields);
        $this->assertSame([], $model->errors);
    }

    public function testPropiedadesPorDefecto(): void
    {
        $model = new RConcreteDatasetModel();

        $this->assertSame('id', $model->primaryKey);
        $this->assertNull($model->insertedId);
        $this->assertNull($model->controller);
        $this->assertNull($model->baseQuerySQL);
        $this->assertSame('', $model->defaultSortingField);
        $this->assertSame('asc', $model->defaultSortingDir);
    }

    public function testFlagsCRUDHabilitadosPorDefecto(): void
    {
        $model = new RConcreteDatasetModel();

        $this->assertTrue($model->canInsert);
        $this->assertTrue($model->canUpdate);
        $this->assertTrue($model->canDelete);
    }

    public function testEnableAuditHabilitadoPorDefecto(): void
    {
        $model = new RConcreteDatasetModel();

        $ref = new \ReflectionProperty($model, 'enableAudit');
        $ref->setAccessible(true);
        $this->assertTrue($ref->getValue($model));
    }

    public function testCompleteFieldListLlenaOfieldlistDesdeTablefields(): void
    {
        $model              = new RConcreteDatasetModel();
        $model->tablefields = ['nombre', 'precio'];
        $model->completeFieldList();

        $this->assertArrayHasKey('nombre', $model->ofieldlist);
        $this->assertArrayHasKey('precio', $model->ofieldlist);
        $this->assertInstanceOf(RSimpleTextField::class, $model->ofieldlist['nombre']);
        $this->assertInstanceOf(RSimpleTextField::class, $model->ofieldlist['precio']);
    }

    public function testCompleteFieldListAgregaRIdFieldParaPK(): void
    {
        $model              = new RConcreteDatasetModel();
        $model->tablefields = ['nombre'];
        $model->completeFieldList();

        // Después de completeFieldList, debe haber un RIdField asociado al primaryKey
        $this->assertArrayHasKey('id', $model->ofieldlist);
        $this->assertInstanceOf(RIdField::class, $model->ofieldlist['id']);
    }

    public function testCompleteFieldListLlenaAllowedFields(): void
    {
        $model              = new RConcreteDatasetModel();
        $model->tablefields = ['nombre', 'precio'];
        $model->completeFieldList();

        $ref = new \ReflectionProperty($model, 'allowedFields');
        $ref->setAccessible(true);
        $allowed = $ref->getValue($model);

        $this->assertContains('nombre', $allowed);
        $this->assertContains('precio', $allowed);
        $this->assertContains('id', $allowed, 'allowedFields debe incluir el primaryKey');
    }

    public function testCompleteFieldListNoDuplicaCamposPreviamenteAgregados(): void
    {
        $model = new RConcreteDatasetModel();
        $model->addFieldFromArray('nombre', ['label' => 'Nombre']);
        $model->tablefields = ['nombre'];
        $model->completeFieldList();

        $this->assertSame('Nombre', $model->ofieldlist['nombre']->getLabel(),
            'El label personalizado se preserva, no se sobreescribe');
    }

    public function testCompleteFieldListRellenaTablefieldsSiVacios(): void
    {
        $model = new RConcreteDatasetModel();
        $model->addFieldFromArray('a', []);
        $model->addFieldFromArray('b', []);
        // tablefields vacía: completeFieldList lo deduce de ofieldlist
        $model->completeFieldList();

        $this->assertContains('a', $model->tablefields);
        $this->assertContains('b', $model->tablefields);
    }

    public function testComposicionDeTraitsExpuestaEnAPIPublica(): void
    {
        // Verificamos que los 4 traits están compuestos: usamos métodos públicos clave
        $model = new RConcreteDatasetModel();

        // FieldManagementTrait
        $this->assertTrue(is_callable([$model, 'addFieldFromArray']));
        $this->assertTrue(is_callable([$model, 'fieldByName']));
        $this->assertTrue(is_callable([$model, 'textForTable']));
        // SearchFilterTrait
        $this->assertTrue(is_callable([$model, 'getCountForSearch']));
        $this->assertTrue(is_callable([$model, 'checkRelations']));
        // CrudOperationsTrait
        $this->assertTrue(is_callable([$model, 'processFormInput']));
        $this->assertTrue(is_callable([$model, 'performDelete']));
        $this->assertTrue(is_callable([$model, 'createInputDataArray']));
        // JsonResultTrait
        $this->assertTrue(is_callable([$model, 'getTableAjax']));
        $this->assertTrue(is_callable([$model, 'getTableForAPI']));
        $this->assertTrue(is_callable([$model, 'generateJsonResult']));
    }
}

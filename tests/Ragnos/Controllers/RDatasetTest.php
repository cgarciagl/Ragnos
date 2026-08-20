<?php

namespace Tests\Ragnos\Controllers;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Controllers\RDataset;
use App\ThirdParty\Ragnos\Controllers\Ragnos;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;
use App\ThirdParty\Ragnos\Models\Fields\RSearchField;
use App\ThirdParty\Ragnos\Models\Fields\RSimpleTextField;

/**
 * Pruebas reales para RDataset (controlador base de datasets).
 * Verifica que cada setter/getter modifica el estado correspondiente
 * en el modelo asociado, y que los hooks de campos funcionan.
 */
class RDatasetTest extends RagnosTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Resetear singleton para aislamiento
        Ragnos::$CI = null;
    }

    /**
     * Crea un controlador concreto (RDataset es abstract).
     */
    private function makeController(): RDataset
    {
        return new class extends RDataset {
        };
    }

    public function testConstructorCreaModeloPorDefecto(): void
    {
        $controller = $this->makeController();
        $this->assertInstanceOf(RConcreteDatasetModel::class, $controller->getModel());
    }

    public function testConstructorAsignaBackReferenceControllerEnModelo(): void
    {
        $controller = $this->makeController();
        $this->assertSame($controller, $controller->getModel()->controller);
    }

    public function testSetTitleYGetTitle(): void
    {
        $controller = $this->makeController();
        $controller->setTitle('Productos');
        $this->assertSame('Productos', $controller->getTitle());
    }

    public function testGetTitleVacioPorDefecto(): void
    {
        $controller = $this->makeController();
        $this->assertSame('', $controller->getTitle());
    }

    public function testSetTableNameModificaModelo(): void
    {
        $controller = $this->makeController();
        $controller->setTableName('mi_tabla');
        $this->assertSame('mi_tabla', $controller->getModel()->table);
    }

    public function testSetIdFieldModificaPrimaryKey(): void
    {
        $controller = $this->makeController();
        $controller->setIdField('uid');
        $this->assertSame('uid', $controller->getModel()->primaryKey);
    }

    public function testSetTableFieldsAsignaListaEnModelo(): void
    {
        $controller = $this->makeController();
        $controller->setTableFields(['nombre', 'precio']);
        $this->assertSame(['nombre', 'precio'], $controller->getModel()->tablefields);
    }

    public function testSetCanInsertYGetCanInsert(): void
    {
        $controller = $this->makeController();
        $this->assertTrue($controller->canInsert(), 'Default true');

        $controller->setCanInsert(false);
        $this->assertFalse($controller->canInsert());

        $controller->setCanInsert(true);
        $this->assertTrue($controller->canInsert());
    }

    public function testSetCanUpdateYGetCanUpdate(): void
    {
        $controller = $this->makeController();
        $this->assertTrue($controller->canUpdate());

        $controller->setCanUpdate(false);
        $this->assertFalse($controller->canUpdate());
    }

    public function testSetCanDeleteYGetCanDelete(): void
    {
        $controller = $this->makeController();
        $this->assertTrue($controller->canDelete());

        $controller->setCanDelete(false);
        $this->assertFalse($controller->canDelete());
    }

    public function testAddFieldAgregaEntradaEnOfieldlist(): void
    {
        $controller = $this->makeController();
        $controller->addField('email', ['label' => 'Correo', 'rules' => 'required|valid_email']);

        $list = $controller->getModel()->ofieldlist;
        $this->assertArrayHasKey('email', $list);
        $this->assertSame('Correo', $list['email']->getLabel());
        $this->assertSame('required|valid_email', $list['email']->getRules());
    }

    public function testRemoveFieldEliminaEntrada(): void
    {
        $controller = $this->makeController();
        $controller->addField('email', ['label' => 'Correo']);
        $this->assertArrayHasKey('email', $controller->getModel()->ofieldlist);

        $controller->removeField('email');
        $this->assertArrayNotHasKey('email', $controller->getModel()->ofieldlist);
    }

    public function testAddLabelModificaCampoExistente(): void
    {
        $controller = $this->makeController();
        $controller->addField('email');
        $controller->addLabel('email', 'Email Address');

        $this->assertSame('Email Address', $controller->getModel()->ofieldlist['email']->getLabel());
    }

    public function testAddRulesModificaCampoExistente(): void
    {
        $controller = $this->makeController();
        $controller->addField('email');
        $controller->addRules('email', 'required|valid_email');

        $this->assertSame('required|valid_email', $controller->getModel()->ofieldlist['email']->getRules());
    }

    public function testAddDefaultModificaCampoExistente(): void
    {
        $controller = $this->makeController();
        $controller->addField('estado');
        $controller->addDefault('estado', 'ACTIVO');

        $this->assertSame('ACTIVO', $controller->getModel()->ofieldlist['estado']->getDefault());
    }

    public function testAddSearchReemplazaCampoConRSearchField(): void
    {
        $controller = $this->makeController();
        $field      = $controller->addSearch('cliente_id', 'Clientes', '', 'cb');

        $this->assertInstanceOf(RSearchField::class, $controller->getModel()->ofieldlist['cliente_id']);
        $this->assertInstanceOf(RSearchField::class, $field);
        $this->assertSame('Clientes', $field->getController());
        $this->assertSame('cb', $field->getCallback());
    }

    public function testAddSearchEnvuelveCampoExistente(): void
    {
        $controller = $this->makeController();
        $controller->addField('cliente_id', ['label' => 'Cliente']);
        $field = $controller->addSearch('cliente_id', 'Clientes');

        $this->assertInstanceOf(RSearchField::class, $field);
        // Verifica que el label viaja del field interno al decorador
        $this->assertSame('Cliente', $field->getLabel());
    }

    public function testSetModelReasignaYActualizaBackReference(): void
    {
        $controller = $this->makeController();
        $nuevoModel = new RConcreteDatasetModel();
        $controller->setModel($nuevoModel);

        $this->assertSame($nuevoModel, $controller->getModel());
        $this->assertSame($controller, $nuevoModel->controller);
    }

    public function testSetAutoIncrementSeAsignaAlModelo(): void
    {
        $controller = $this->makeController();
        $controller->setAutoIncrement(false);
        $this->assertFalse($controller->getModel()->usesAutoIncrement());
    }

    public function testHooksPorDefectoSonNoOpYNoLanzanExcepcion(): void
    {
        $controller = $this->makeController();
        $data       = ['a' => 1];

        // Ninguno debe lanzar
        $controller->_beforeInsert($data);
        $controller->_afterInsert();
        $controller->_beforeUpdate($data);
        $controller->_afterUpdate();
        $controller->_beforeDelete();
        $controller->_afterDelete();
        $controller->_filters();

        // Verificamos llegada hasta aquí (sin excepción)
        $this->assertTrue(true);
    }

    public function testSetUseSoftDeletesPropaga(): void
    {
        $controller = $this->makeController();
        $controller->setUseSoftDeletes(true);

        $ref = new \ReflectionProperty($controller->getModel(), 'useSoftDeletes');
        $ref->setAccessible(true);
        $this->assertTrue($ref->getValue($controller->getModel()));
    }
}

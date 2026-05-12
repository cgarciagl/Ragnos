<?php

namespace Tests\Ragnos\Models\Traits;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;
use App\ThirdParty\Ragnos\Models\Fields\RSimpleTextField;
use App\ThirdParty\Ragnos\Models\Fields\RSwitchField;
use App\ThirdParty\Ragnos\Models\Fields\RPillboxField;

/**
 * Pruebas de comportamiento real para FieldManagementTrait.
 * Verifica addFieldFromArray (mapeo de tipos), fieldByName, realField,
 * completeFieldList y textForTable (formato de salida por tipo).
 */
class FieldManagementTraitTest extends RagnosTestCase
{
    private RConcreteDatasetModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        helper([
            'App\ThirdParty\Ragnos\Helpers\utiles_helper',
            'App\ThirdParty\Ragnos\Helpers\ragnos_helper',
            'text',
        ]);
        $this->model = new RConcreteDatasetModel();
    }

    public function testAddFieldFromArrayConTypeSwitchCreaRSwitchField(): void
    {
        $this->model->addFieldFromArray('activo', ['type' => 'switch']);
        $this->assertInstanceOf(RSwitchField::class, $this->model->ofieldlist['activo']);
    }

    public function testAddFieldFromArrayConTypePillboxCreaRPillboxField(): void
    {
        $this->model->addFieldFromArray('tags', ['type' => 'pillbox']);
        $this->assertInstanceOf(RPillboxField::class, $this->model->ofieldlist['tags']);
    }

    public function testAddFieldFromArrayConTypeTextCreaRSimpleTextField(): void
    {
        $this->model->addFieldFromArray('nombre', ['type' => 'text']);
        $this->assertInstanceOf(RSimpleTextField::class, $this->model->ofieldlist['nombre']);
    }

    public function testAddFieldFromArraySinTypeUsaRSimpleTextField(): void
    {
        $this->model->addFieldFromArray('descripcion', []);
        $this->assertInstanceOf(RSimpleTextField::class, $this->model->ofieldlist['descripcion']);
    }

    public function testAddFieldFromArrayConTypeDateUsaRSimpleTextField(): void
    {
        // type='date' no está en classMap, por lo que cae a RSimpleTextField pero conserva type='date'
        $this->model->addFieldFromArray('fecha', ['type' => 'date']);
        $field = $this->model->ofieldlist['fecha'];
        $this->assertInstanceOf(RSimpleTextField::class, $field);
        $this->assertSame('date', $field->getType());
    }

    public function testAddFieldFromArrayAsignaLabelYRules(): void
    {
        $this->model->addFieldFromArray('email', [
            'label' => 'Correo',
            'rules' => 'required|valid_email',
        ]);
        $f = $this->model->ofieldlist['email'];

        $this->assertSame('Correo', $f->getLabel());
        $this->assertSame('required|valid_email', $f->getRules());
    }

    public function testAddFieldFromArrayAsignaOptions(): void
    {
        $this->model->addFieldFromArray('estado', [
            'type'    => 'dropdown',
            'options' => ['1' => 'Activo', '0' => 'Inactivo'],
        ]);
        $f = $this->model->ofieldlist['estado'];

        $this->assertSame(['1' => 'Activo', '0' => 'Inactivo'], $f->getOptions());
    }

    public function testAddFieldFromArrayRetornaElCampoCreado(): void
    {
        $field = $this->model->addFieldFromArray('nombre', ['label' => 'X']);
        $this->assertInstanceOf(RSimpleTextField::class, $field);
        $this->assertSame($field, $this->model->ofieldlist['nombre']);
    }

    public function testFieldByNameDevuelveCampoExistente(): void
    {
        $this->model->addFieldFromArray('email', ['label' => 'Correo']);
        $f = $this->model->fieldByName('email');

        $this->assertInstanceOf(RSimpleTextField::class, $f);
        $this->assertSame('Correo', $f->getLabel());
    }

    public function testFieldByNameLanzaExcepcionSiNoExiste(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Field 'no_existe' not found");
        $this->model->fieldByName('no_existe');
    }

    public function testRealFieldRetornaFieldName(): void
    {
        $this->model->addFieldFromArray('email', []);
        $this->assertSame('email', $this->model->realField('email'));
    }

    public function testCompleteFieldListAgregaCamposFaltantesYPK(): void
    {
        $this->model->tablefields = ['a', 'b'];
        $this->model->completeFieldList();

        $this->assertArrayHasKey('a', $this->model->ofieldlist);
        $this->assertArrayHasKey('b', $this->model->ofieldlist);
        $this->assertArrayHasKey('id', $this->model->ofieldlist);
    }

    public function testTextForTableDropdownTraduceKeyAOption(): void
    {
        $this->model->addFieldFromArray('estado', [
            'type'    => 'dropdown',
            'options' => ['1' => 'Activo', '0' => 'Inactivo'],
        ]);
        $resultado = $this->model->textForTable(['estado' => '1'], 'estado');
        $this->assertSame('Activo', $resultado);
    }

    public function testTextForTableDropdownConValorNoEnOptionsDevuelveValor(): void
    {
        $this->model->addFieldFromArray('estado', [
            'type'    => 'dropdown',
            'options' => ['1' => 'Activo'],
        ]);
        $resultado = $this->model->textForTable(['estado' => 'X'], 'estado');
        $this->assertSame('X', $resultado);
    }

    public function testTextForTableSwitchOn(): void
    {
        $this->model->addFieldFromArray('activo', [
            'type' => 'switch',
        ]);
        // onValue por defecto = '1'
        $resultado = $this->model->textForTable(['activo' => '1'], 'activo');
        $this->assertStringContainsString('✅', $resultado);
    }

    public function testTextForTableSwitchOff(): void
    {
        $this->model->addFieldFromArray('activo', [
            'type' => 'switch',
        ]);
        $resultado = $this->model->textForTable(['activo' => '0'], 'activo');
        $this->assertStringContainsString('❌', $resultado);
    }

    public function testTextForTableDateFormatea(): void
    {
        $this->model->addFieldFromArray('fecha', ['type' => 'date']);
        $resultado = $this->model->textForTable(['fecha' => '2024-01-15'], 'fecha');
        $this->assertSame('15/01/2024', $resultado);
    }

    public function testTextForTableMoneyAplicaFormato(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('intl extension is not loaded');
        }
        $this->model->addFieldFromArray('precio', [
            'rules' => 'money',
        ]);
        $resultado = $this->model->textForTable(['precio' => 1234.5], 'precio');
        $this->assertStringContainsString('$', $resultado);
    }

    public function testTextForTableTruncaTextosLargos(): void
    {
        $this->model->addFieldFromArray('descripcion', []);
        // character_limiter requiere espacios para truncar
        $textoLargo = 'palabra1 palabra2 palabra3 palabra4 palabra5 palabra6 palabra7';
        $resultado  = $this->model->textForTable(['descripcion' => $textoLargo], 'descripcion');

        // character_limiter trunca y añade '...'
        $this->assertLessThan(strlen($textoLargo), strlen($resultado));
        $this->assertStringEndsWith('...', $resultado);
    }

    public function testTextForTableMultiselectMapeaCadaValor(): void
    {
        $this->model->addFieldFromArray('roles', [
            'type'    => 'multiselect',
            'options' => ['1' => 'Admin', '2' => 'User', '3' => 'Guest'],
        ]);
        $resultado = $this->model->textForTable(['roles' => '1,3'], 'roles');
        $this->assertStringContainsString('Admin', $resultado);
        $this->assertStringContainsString('Guest', $resultado);
    }
}

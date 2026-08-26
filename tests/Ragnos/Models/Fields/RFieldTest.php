<?php

namespace Tests\Ragnos\Models\Fields;

use CodeIgniter\Test\CIUnitTestCase;
use App\ThirdParty\Ragnos\Models\Fields\RSimpleTextField;
use App\ThirdParty\Ragnos\Models\Fields\RSwitchField;
use App\ThirdParty\Ragnos\Models\Fields\RPillboxField;
use App\ThirdParty\Ragnos\Models\Fields\RIdField;

/**
 * Pruebas para RField y sus subclases.
 * Verifica setters/getters reales, loadFromArray, setDefaults, isRequired,
 * loadVars y comportamientos específicos de cada subclase.
 */
class RFieldTest extends CIUnitTestCase
{
    public function testConstructorAsignaSoloFieldname(): void
    {
        $field = new RSimpleTextField('username');
        $this->assertSame('username', $field->getFieldName());
    }

    public function testGetLabelFallbackAFieldname(): void
    {
        $field = new RSimpleTextField('email');
        // Sin setLabel, getLabel devuelve fieldname
        $this->assertSame('email', $field->getLabel());
    }

    public function testSetLabelGuardaYRecupera(): void
    {
        $field = new RSimpleTextField('email');
        $field->setLabel('Correo electrónico');
        $this->assertSame('Correo electrónico', $field->getLabel());
    }

    public function testSetFieldNameModificaNombre(): void
    {
        $field = new RSimpleTextField('viejo');
        $field->setFieldName('nuevo');
        $this->assertSame('nuevo', $field->getFieldName());
    }

    public function testSetValueGuardaYRecupera(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setValue('hola');
        $this->assertSame('hola', $field->getValue());
    }

    public function testGetValueRetornaDefaultSiNoHayValor(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setDefault('valor_por_defecto');
        $this->assertSame('valor_por_defecto', $field->getValue());
    }

    public function testGetValuePrefiereValorSobreDefault(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setDefault('default');
        $field->setValue('asignado');
        $this->assertSame('asignado', $field->getValue());
    }

    public function testSetRulesYGetRules(): void
    {
        $field = new RSimpleTextField('campo');
        $this->assertNull($field->getRules());

        $field->setRules('required|max_length[10]');
        $this->assertSame('required|max_length[10]', $field->getRules());
    }

    public function testSetTypeNormaliza(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setType('  dropdown  ');
        $this->assertSame('dropdown', $field->getType());
    }

    public function testSetTypeConRFieldTypeEnum(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setType(\App\ThirdParty\Ragnos\Models\Fields\RFieldType::SWITCH);
        $this->assertSame('switch', $field->getType());
        $this->assertSame(\App\ThirdParty\Ragnos\Models\Fields\RFieldType::SWITCH , $field->getFieldTypeEnum());
    }

    public function testSetOptionsYGetOptions(): void
    {
        $field = new RSimpleTextField('estado');
        $this->assertSame([], $field->getOptions());

        $opts = ['1' => 'Activo', '0' => 'Inactivo'];
        $field->setOptions($opts);
        $this->assertSame($opts, $field->getOptions());
    }

    public function testSetTabYGetTab(): void
    {
        $field = new RSimpleTextField('campo');
        $this->assertNull($field->getTab());

        $field->setTab('general');
        $this->assertSame('general', $field->getTab());
    }

    public function testSetPlaceholderYGetPlaceholder(): void
    {
        $field = new RSimpleTextField('email');
        $this->assertNull($field->getPlaceHolder());

        $field->setPlaceHolder('escriba su correo');
        $this->assertSame('escriba su correo', $field->getPlaceHolder());
    }

    public function testSetQueryYGetQuery(): void
    {
        $field = new RSimpleTextField('nombre_completo');
        $this->assertNull($field->getQuery());

        $field->setQuery("CONCAT(nombre, ' ', apellido)");
        $this->assertSame("CONCAT(nombre, ' ', apellido)", $field->getQuery());
    }

    public function testLoadFromArrayCargaTodasLasPropiedades(): void
    {
        $field = new RSimpleTextField('producto');
        $field->loadFromArray([
            'label'       => 'Producto',
            'rules'       => 'required',
            'type'        => 'dropdown',
            'default'     => 'Standard',
            'options'     => ['Standard', 'Premium'],
            'placeholder' => 'elija',
            'tab'         => 'detalles',
            'value'       => 'Premium',
            'query'       => 'SELECT 1',
        ]);

        $this->assertSame('Producto', $field->getLabel());
        $this->assertSame('required', $field->getRules());
        $this->assertSame('dropdown', $field->getType());
        $this->assertSame('Standard', $field->getDefault());
        $this->assertSame(['Standard', 'Premium'], $field->getOptions());
        $this->assertSame('elija', $field->getPlaceHolder());
        $this->assertSame('detalles', $field->getTab());
        $this->assertSame('Premium', $field->getValue());
        $this->assertSame('SELECT 1', $field->getQuery());
    }

    public function testLoadFromArrayIgnoraClavesDesconocidas(): void
    {
        $field = new RSimpleTextField('campo');
        // No debe lanzar excepción al recibir clave no mapeada
        $field->loadFromArray(['label' => 'L', 'inexistente' => 'X']);
        $this->assertSame('L', $field->getLabel());
    }

    public function testSetDefaultsRellenaValoresFaltantes(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setDefaults();

        $this->assertSame('campo', $field->getLabel(), 'label cae a fieldname');
        $this->assertSame('', $field->getRules());
        $this->assertSame('', $field->getValue());
        $this->assertSame('text', $field->getType());
        $this->assertSame([], $field->getOptions());
        $this->assertSame('', $field->getPlaceHolder());
    }

    public function testSetDefaultsNoSobrescribeValoresExistentes(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setLabel('Etiqueta');
        $field->setRules('required');
        $field->setDefaults();

        $this->assertSame('Etiqueta', $field->getLabel());
        $this->assertSame('required', $field->getRules());
    }

    public function testIsRequiredDetectaReglaRequired(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setRules('required|max_length[10]');
        $this->assertTrue($field->isRequired());
    }

    public function testIsRequiredFalseSinReglaRequired(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setRules('max_length[10]|valid_email');
        $this->assertFalse($field->isRequired());
    }

    public function testIsRequiredFalseSinReglas(): void
    {
        $field = new RSimpleTextField('campo');
        $this->assertFalse($field->isRequired());
    }

    public function testLoadVarsExponeKeysEsperadas(): void
    {
        $field = new RSimpleTextField('email');
        $field->setLabel('Correo');
        $field->setValue('a@b.com');
        $field->setRules('required');
        $vars = $field->loadVars();

        $expectedKeys = ['name', 'value', 'label', 'type', 'default', 'options', 'placeholder', 'extra_attributes'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $vars, "Falta key '$key' en loadVars()");
        }
        $this->assertSame('email', $vars['name']);
        $this->assertSame('a@b.com', $vars['value']);
        $this->assertSame('Correo', $vars['label']);
    }

    public function testLoadVarsExtraAttributesIncluyeRequired(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setRules('required|max_length[10]');
        $vars = $field->loadVars();

        $this->assertStringContainsString('required', $vars['extra_attributes']);
    }

    public function testLoadVarsExtraAttributesIncluyeReadonly(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setRules('readonly');
        $vars = $field->loadVars();

        $this->assertStringContainsString('readonly', $vars['extra_attributes']);
    }

    public function testLoadVarsExtraAttributesIncluyeDisabledComoDisabledReadonly(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setRules('disabled');
        $vars = $field->loadVars();

        $this->assertStringContainsString('disabled', $vars['extra_attributes']);
        $this->assertStringContainsString('readonly', $vars['extra_attributes']);
    }

    public function testLoadVarsExtraAttributesVacioSinReglasEspeciales(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setRules('max_length[10]');
        $vars = $field->loadVars();

        $this->assertSame('', $vars['extra_attributes']);
    }

    // ---- RSimpleTextField ----

    public function testRSimpleTextFieldTipoDefaultEsText(): void
    {
        $field = new RSimpleTextField('campo');
        $field->setDefaults();
        $this->assertSame('text', $field->getType());
    }

    // ---- RSwitchField ----

    public function testRSwitchFieldTipoEsSwitch(): void
    {
        $field = new RSwitchField('activo');
        $this->assertSame('switch', $field->getType());
    }

    public function testRSwitchFieldDefaultsOnOff(): void
    {
        $field = new RSwitchField('activo');
        $this->assertSame('1', $field->getOnValue());
        $this->assertSame('0', $field->getOffValue());
    }

    public function testRSwitchFieldLoadFromArrayAsignaOnYOff(): void
    {
        $field = new RSwitchField('activo');
        $field->loadFromArray([
            'label'    => 'Activo',
            'onValue'  => 'YES',
            'offValue' => 'NO',
        ]);
        $this->assertSame('Activo', $field->getLabel());
        $this->assertSame('YES', $field->getOnValue());
        $this->assertSame('NO', $field->getOffValue());
    }

    public function testRSwitchFieldLoadVarsIncluyeOnOff(): void
    {
        $field = new RSwitchField('activo');
        $field->setOnValue('SI');
        $field->setOffValue('NO');
        $vars = $field->loadVars();

        $this->assertArrayHasKey('onValue', $vars);
        $this->assertArrayHasKey('offValue', $vars);
        $this->assertSame('SI', $vars['onValue']);
        $this->assertSame('NO', $vars['offValue']);
    }

    // ---- RPillboxField ----

    public function testRPillboxFieldTipoEsPillbox(): void
    {
        $field = new RPillboxField('tags');
        $this->assertSame('pillbox', $field->getType());
    }

    // ---- RIdField (decorator) ----

    public function testRIdFieldDecoraConRefsCompartidas(): void
    {
        $base = new RSimpleTextField('id');
        $base->setLabel('Identificador');
        $decorator = new RIdField($base);

        // El decorador refleja el field base
        $this->assertSame('id', $decorator->getFieldName());
        $this->assertSame('Identificador', $decorator->getLabel());

        // Modificar el base se refleja en el decorador (referencia compartida)
        $base->setLabel('Nuevo Label');
        $this->assertSame('Nuevo Label', $decorator->getLabel());

        // Modificar el decorador también se refleja en el base
        $decorator->setLabel('Vuelta');
        $this->assertSame('Vuelta', $base->getLabel());
    }
}

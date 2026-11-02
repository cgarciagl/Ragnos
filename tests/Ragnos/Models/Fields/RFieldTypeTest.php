<?php

namespace Tests\Ragnos\Models\Fields;

use Ragnos\Models\Fields\RFieldType;
use Ragnos\Models\Fields\RFileUploadField;
use Ragnos\Models\Fields\RImageUploadField;
use Ragnos\Models\Fields\RSimpleTextField;
use Ragnos\Models\Fields\RSwitchField;
use Ragnos\Models\RConcreteDatasetModel;
use Tests\Ragnos\RagnosTestCase;

/**
 * Pruebas del enum RFieldType usado directamente en addField().
 * Cada caso del enum debe producir el mismo campo que su cadena equivalente.
 */
class RFieldTypeTest extends RagnosTestCase
{
    private RConcreteDatasetModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['Ragnos\Helpers\utiles_helper', 'Ragnos\Helpers\ragnos_helper', 'text']);
        $this->model = new RConcreteDatasetModel();
    }

    public function testSelectProduceUnDropdownConSusOpciones(): void
    {
        $field = $this->model->addFieldFromArray('status', [
            'label'   => 'Estado',
            'type'    => RFieldType::SELECT,
            'options' => ['A' => 'Activo', 'I' => 'Inactivo'],
        ]);

        $this->assertInstanceOf(RSimpleTextField::class, $field);
        $this->assertSame('dropdown', $field->getType());
        $this->assertSame(['A' => 'Activo', 'I' => 'Inactivo'], $field->getOptions());
    }

    public function testSwitchProduceUnRSwitchField(): void
    {
        $field = $this->model->addFieldFromArray('is_active', [
            'type'    => RFieldType::SWITCH,
            'default' => 1,
        ]);

        $this->assertInstanceOf(RSwitchField::class, $field);
        $this->assertSame('switch', $field->getType());
    }

    public function testFileProduceUnCampoDeCarga(): void
    {
        $field = $this->model->addFieldFromArray('contrato', ['type' => RFieldType::FILE]);

        $this->assertInstanceOf(RFileUploadField::class, $field);
        $this->assertSame('fileupload', $field->getType());
    }

    public function testImageProduceUnCampoDeImagen(): void
    {
        $field = $this->model->addFieldFromArray('foto', ['type' => RFieldType::IMAGE]);

        $this->assertInstanceOf(RImageUploadField::class, $field);
        $this->assertSame('imageupload', $field->getType());
    }

    public function testCadaCasoCorrespondeAUnaVistaExistente(): void
    {
        $vistas = array_map(
            fn ($archivo) => basename($archivo, 'field.php'),
            glob(ROOTPATH . 'src/Views/rfield/*field.php')
        );

        // Tipos que se dibujan con la vista genérica de texto (input con su atributo type)
        $tiposDeInput = ['text', 'number', 'date', 'email', 'password'];

        foreach (RFieldType::cases() as $caso) {
            $this->assertTrue(
                in_array($caso->fieldType(), $vistas, true) || in_array($caso->fieldType(), $tiposDeInput, true),
                "RFieldType::{$caso->name} ('{$caso->fieldType()}') no corresponde a ninguna vista de campo"
            );
        }
    }

    public function testMoneyAgregaLaReglaMoneySinDuplicarla(): void
    {
        $conEnum = $this->model->addFieldFromArray('precio', [
            'type'  => RFieldType::MONEY,
            'rules' => 'required',
        ]);
        $this->assertSame('text', $conEnum->getType());
        $this->assertSame('required|money', $conEnum->getRules());

        $conCadena = $this->model->addFieldFromArray('costo', [
            'type'  => 'money',
            'rules' => 'required|money',
        ]);
        $this->assertSame('text', $conCadena->getType());
        $this->assertSame('required|money', $conCadena->getRules());
    }

    public function testSetTypeConEnumGuardaElTipoInterno(): void
    {
        $field = new RSimpleTextField('status');
        $field->setType(RFieldType::SELECT);

        $this->assertSame('dropdown', $field->getType());
        $this->assertSame(RFieldType::SELECT, $field->getFieldTypeEnum());
    }

    public function testElEjemploDelLibroDibujaUnSelectYUnSwitch(): void
    {
        // Ejemplo del capítulo 5 del libro "Ragnos de Cero a Pro"
        $status = $this->model->addFieldFromArray('status', [
            'label'   => 'Estado',
            'type'    => RFieldType::SELECT,
            'options' => ['A' => 'Activo', 'I' => 'Inactivo'],
        ]);
        $activo = $this->model->addFieldFromArray('is_active', [
            'label'   => '¿Activo?',
            'type'    => RFieldType::SWITCH,
            'default' => 1,
        ]);

        $htmlStatus = $status->constructControl();
        $this->assertStringContainsString('<select name="status"', $htmlStatus);
        $this->assertStringContainsString('<option value="A">Activo</option>', $htmlStatus);
        $this->assertStringContainsString('<option value="I">Inactivo</option>', $htmlStatus);

        $htmlActivo = $activo->constructControl();
        $this->assertStringContainsString('type="checkbox"', $htmlActivo);
        $this->assertStringContainsString('name="is_active"', $htmlActivo);
    }

    public function testLosValoresAnterioresSiguenReconociendose(): void
    {
        $this->assertSame(RFieldType::SELECT, RFieldType::fromOrDefault('select'));
        $this->assertSame(RFieldType::FILE, RFieldType::fromOrDefault('file'));
        $this->assertSame(RFieldType::IMAGE, RFieldType::fromOrDefault('image'));
        $this->assertSame(RFieldType::SELECT, RFieldType::fromOrDefault('dropdown'));
    }
}

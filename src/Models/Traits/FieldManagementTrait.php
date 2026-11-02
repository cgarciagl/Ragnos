<?php

namespace Ragnos\Models\Traits;

use Ragnos\Models\Fields\RSimpleTextField;
use Ragnos\Models\Fields\RIdField;

trait FieldManagementTrait
{
    function realField($fieldName)
    {
        return $this->ofieldlist[$fieldName]->getFieldToShow();
    }
    function addFieldFromArray($fieldName, $array)
    {
        $array    = $this->normalizeFieldType($array);
        $type     = $array['type'];
        $classMap = [
            'switch'      => \Ragnos\Models\Fields\RSwitchField::class,
            'pillbox'     => \Ragnos\Models\Fields\RPillboxField::class,
            'fileupload'  => \Ragnos\Models\Fields\RFileUploadField::class,
            'imageupload' => \Ragnos\Models\Fields\RImageUploadField::class,
        ];

        if (array_key_exists($type, $classMap)) {
            $className                    = $classMap[$type];
            $this->ofieldlist[$fieldName] = new $className($fieldName);
        } else {
            // Default to SimpleTextField for 'text', 'password', 'date', etc.
            $this->ofieldlist[$fieldName] = new RSimpleTextField($fieldName);
        }

        $this->ofieldlist[$fieldName]->loadFromArray($array);
        $this->ofieldlist[$fieldName]->setDefaults();
        return $this->ofieldlist[$fieldName];
    }

    /**
     * Normaliza la clave 'type' de la configuración de un campo:
     * acepta el enum RFieldType (convirtiéndolo al tipo interno de Ragnos)
     * y traduce el tipo 'money' a la regla 'money', que es la que da formato de moneda.
     */
    private function normalizeFieldType(array $array): array
    {
        $type = $array['type'] ?? 'text';

        if ($type instanceof \Ragnos\Models\Fields\RFieldType) {
            $isMoney = ($type === \Ragnos\Models\Fields\RFieldType::MONEY);
            $type    = $type->fieldType();
        } else {
            $type    = trim((string) $type);
            $isMoney = ($type === 'money');
            if ($isMoney) {
                $type = 'text';
            }
        }

        if ($isMoney) {
            $rules = array_filter(explode('|', (string) ($array['rules'] ?? '')));
            if (!in_array('money', $rules, true)) {
                $rules[] = 'money';
            }
            $array['rules'] = implode('|', $rules);
        }

        $array['type'] = $type;
        return $array;
    }

    function completeFieldList()
    {
        $this->fillEmptyTablefields();
        $this->fillFieldlist();
        $this->addIdFieldToFieldlist();
        $this->setDefaults();
        $campos = [];
        foreach ($this->ofieldlist as $k => $f) {
            $campos[] = $k;
        }
        $this->allowedFields = $campos;
    }

    private function fillEmptyTablefields()
    {
        if (!count($this->tablefields)) {
            foreach ($this->ofieldlist as $k => $f) {
                $this->tablefields[] = $k;
            }
        }
    }

    private function fillFieldlist()
    {
        foreach ($this->tablefields as $field) {
            if (!(isset($this->ofieldlist[$field]))) {
                $this->ofieldlist[$field] = new RSimpleTextField($field);
            }
        }
    }

    private function addIdFieldToFieldlist()
    {
        if (!(isset($this->ofieldlist[$this->primaryKey]))) {
            $this->ofieldlist[$this->primaryKey] = new RIdField(new RSimpleTextField($this->primaryKey));
        }
    }

    private function setDefaults()
    {
        foreach ($this->ofieldlist as $k => $fieldItem) {
            $fieldItem->setDefaults();
        }
    }
    public function fieldByName($fieldname)
    {
        if (isset($this->ofieldlist[$fieldname])) {
            return $this->ofieldlist[$fieldname];
        }
        throw new \Exception("Field '$fieldname' not found in the field list.");
    }

    function textForTable($values, $fieldname)
    {
        $datasetField = $this->fieldByName($fieldname);
        $value        = $values[$this->realField($fieldname)] ?? '';

        switch ($datasetField->getType()) {
            case 'multiselect':
                $values = array_map(fn($v) => $datasetField->getOptions()[$v] ?? $v, explode(',', $value));
                $value  = implode(',', $values);
                break;
            case 'dropdown':
                $value = $datasetField->getOptions()[$value] ?? $value;
                break;
            case 'date':
                $value = date('d/m/Y', strtotime($value));
                break;
            case 'switch':
                /** @var \Ragnos\Models\Fields\RSwitchField $datasetField */
                $value = ($value == $datasetField->getOnValue()) ? '✅' : '❌';
                break;
        }

        //si el campo tiene la regla "money" se formatea el valor
        if (substr_count($datasetField->getRules(), 'money')) {
            $value = moneyFormat($value);
        }

        helper('text');
        return removeNewLines(character_limiter(strip_tags((string) $value), 30, '...'));
    }
}
<?php

namespace App\ThirdParty\Ragnos\Models\Traits;

use App\ThirdParty\Ragnos\Models\Fields\RSimpleTextField;
use App\ThirdParty\Ragnos\Models\Fields\RIdField;

trait FieldManagementTrait
{
    function realField($fieldName)
    {
        return $this->ofieldlist[$fieldName]->getFieldToShow();
    }
    function addFieldFromArray($fieldName, $array)
    {
        $this->ofieldlist[$fieldName] = new RSimpleTextField($fieldName);
        $this->ofieldlist[$fieldName]->loadFromArray($array);
        $this->ofieldlist[$fieldName]->setDefaults();
        return $this->ofieldlist[$fieldName];
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
                $values = array_map(function ($v) use ($datasetField) {
                    return $datasetField->getOptions()[$v] ?? $v;
                }, explode(',', $value));
                $value = implode(',', $values);
                break;
            case 'dropdown':
                $value = $datasetField->getOptions()[$value] ?? $value;
                break;
            case 'date':
                $value = date('d/m/Y', strtotime($value));
                break;
        }

        //si el campo tiene la regla "money" se formatea el valor
        if (substr_count($datasetField->getRules(), 'money')) {
            $value = moneyFormat($value);
        }

        helper('text');
        return removeNewLines(character_limiter(strip_tags($value), 30));
    }
}
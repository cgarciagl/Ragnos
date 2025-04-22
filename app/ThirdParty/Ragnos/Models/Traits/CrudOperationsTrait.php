<?php

namespace App\ThirdParty\Ragnos\Models\Traits;

trait CrudOperationsTrait
{
    private function performInsert()
    {
        if ($this->canInsert) {
            try {
                $inputDataArray = $this->createInputDataArray();
                if (isset($inputDataArray[$this->primaryKey])) {
                    unset($inputDataArray[$this->primaryKey]);
                }
                $this->controller->_beforeInsert($inputDataArray);
                $primaryKey               = $this->insert($inputDataArray);
                $_POST[$this->primaryKey] = $primaryKey;
                $this->insertedId         = $primaryKey;
                $this->controller->_afterInsert();
            } catch (\Exception $e) {
                $this->errors['general_error'] = $e->getMessage();
            }
        }
    }

    private function performUpdate()
    {
        if ($this->canUpdate) {
            try {
                $request        = request();
                $inputDataArray = $this->createInputDataArray();
                if (sizeof($inputDataArray) > 0) {
                    $this->controller->_beforeUpdate($inputDataArray);
                    $id = $request->getPost($this->primaryKey);
                    $this->update($id, $inputDataArray);
                    $this->controller->_afterUpdate();
                }
            } catch (\Exception $e) {
                $this->errors['general_error'] = $e->getMessage();
            }
        }
    }

    public function performDelete()
    {
        if ($this->canDelete) {
            try {
                $request        = request();
                $inputDataArray = $this->createInputDataArray();
                if (sizeof($inputDataArray) > 0) {
                    $this->controller->_beforeDelete($inputDataArray);
                    $id = $request->getPost('id');
                    $this->where($this->primaryKey, $id)->delete();
                    $this->controller->_afterDelete();
                }
            } catch (\Exception $e) {
                $this->errors['general_error'] = $e->getMessage();
            }
        }
    }

    function createInputDataArray()
    {
        $responseArray = [];
        $request       = request();
        if ($request->getPost($this->primaryKey)) {
            foreach ($this->ofieldlist as $k => $fieldItem) {
                if ($fieldItem->hasChanged()) {
                    if ($fieldItem->getQuery() == '') {
                        $responseArray[$fieldItem->getFieldName()] = $fieldItem->getDataFromInput($request);
                    }
                }
            }
        } else {
            foreach ($this->ofieldlist as $k => $fieldItem) {
                if ($fieldItem->getQuery() == '') {
                    $responseArray[$fieldItem->getFieldName()] = $fieldItem->getDataFromInput($request);
                }
            }
        }
        return $responseArray;
    }

    function getFormData($id = '')
    {
        $this->completeFieldList();
        if ($id != 'new') {
            $this->getValuesFor($id);
        } else {
            $this->checkForDefaultValues();
        }
        $data['fields'] = $this->ofieldlist;
        return view('App\ThirdParty\Ragnos\Views\rdatasetmodel/form_data', $data);
    }

    private function getValuesFor($id)
    {
        $a = array_keys($this->ofieldlist);
        $this->evaluaSelect($a, $this->table);
        if ($id != '') {
            $this->builder()->where($this->primaryKey, $id);
        }
        $this->checkRelations();
        $this->builder()->limit(1);
        $query = $this->builder()->get();
        $b     = $query->getRowArray();
        if (count($b) > 0) {
            foreach ($this->ofieldlist as $k => $fieldItem) {
                $fieldItem->setValue($b[$this->realField($fieldItem->getFieldName())]);
                if ($fieldItem instanceof YSearchField) {
                    $fieldItem->setIdValue($b[$fieldItem->getFieldName()]);
                }
            }
        }
    }

    function processFormInput()
    {
        $this->completeFieldList();
        $validation = \Config\Services::validation();
        $i          = 0;
        foreach ($this->ofieldlist as $k => $fieldItem) {
            $rules = $this->completeRules($fieldItem);
            if ($rules != '') {
                $validation->setRule(
                    $fieldItem->getFieldName(),
                    $fieldItem->getLabel(),
                    $rules,
                );
                $i++;
            }
        }
        $mustApplyValidations = ($i > 0);
        $this->checkValidations($mustApplyValidations);
    }

    private function completeRules($field)
    {
        $validationRules = $field->getRules();
        $id              = oldValue($this->primaryKey);

        $replacements = [
            'is_unique' => "is_unique[{$this->table}.{$field->getFieldName()},{$this->primaryKey},{$id}]",
            'readonly'  => 'readonly_Ragnos[' . $field->getFieldName() . ']',
            '|disabled' => '',
            'disabled'  => '',
            '|money'    => '',
            'money'     => ''
        ];

        foreach ($replacements as $search => $replace) {
            $validationRules = str_replace($search, $replace, $validationRules);
        }

        return trim($validationRules);
    }

    private function checkValidations($mustApplyValidations)
    {
        $validation = \Config\Services::validation();
        $request    = request();
        if (
            ($validation->withRequest($request)
                ->run() == FALSE) && ($mustApplyValidations)
        ) {
            foreach ($this->ofieldlist as $k => $fieldItem) {
                $error = $validation->getError($fieldItem->getFieldName());
                if ($error != '') {
                    $this->errors[$fieldItem->getFieldName()] = $error;
                }
            }
        } else {
            $this->processFormAction();
        }
    }

    function processFormAction()
    {
        $request = request();
        if ($request->getPost($this->primaryKey)) {
            $this->performUpdate();
        } else {
            $this->performInsert();
        }
    }

    private function checkForDefaultValues()
    {
        $this->checkRelations();
        foreach ($this->ofieldlist as $k => $fieldItem) {
            if ($fieldItem->getDefault() != NULL) {
                if (!$fieldItem->getValue()) {
                    $fieldItem->setValue($fieldItem->getDefault());
                }
            }
        }
    }
}
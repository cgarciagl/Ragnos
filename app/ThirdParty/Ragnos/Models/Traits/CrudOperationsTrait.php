<?php

namespace App\ThirdParty\Ragnos\Models\Traits;

use App\ThirdParty\Ragnos\Models\Fields\RSearchField;

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
                $this->logAudit('INSERT', $primaryKey, ['new' => $inputDataArray]);
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

                    $id = getRagnosInputValue($this->primaryKey);

                    if (fieldHasChanged($this->primaryKey)) {
                        $id                                = oldValue($this->primaryKey);
                        $inputDataArray[$this->primaryKey] = newValue($this->primaryKey);
                    }

                    $this->update($id, $inputDataArray);

                    $datosQueCambian = [];
                    foreach ($inputDataArray as $fieldName => $newValue) {
                        $oldValue = oldValue($fieldName);
                        if ($oldValue != $newValue) {
                            $datosQueCambian[$fieldName] = [
                                'old' => $oldValue,
                                'new' => $newValue
                            ];
                        }
                    }

                    if ($this->enableAudit) {
                        $this->logAudit('UPDATE', $id, $datosQueCambian);
                    }

                    $this->controller->_afterUpdate();
                }
            } catch (\Exception $e) {
                $this->errors['general_error'] = $e->getMessage();
            }
        }
    }

    public function performDelete($pid = null): bool
    {
        $band = false;
        if ($this->canDelete) {
            try {
                $request        = request();
                $inputDataArray = $this->createInputDataArray();
                if (sizeof($inputDataArray) > 0) {
                    $this->controller->_beforeDelete($inputDataArray);
                    $id = getRagnosInputValue('id');
                    if (!$id) {
                        $id = $pid;
                    }
                    if (!$id) {
                        throw new \Exception("ID del registro a eliminar no proporcionado.");
                    }
                    if ($this->enableAudit) {
                        $datosaEliminar = $this->where($this->primaryKey, $id)->first();
                        if (!$datosaEliminar) {
                            throw new \Exception("El registro con ID {$id} no existe.");
                        }
                        $this->logAudit('DELETE', $id, ['deleted_data' => $datosaEliminar]);
                    }
                    $band = $this->where($this->primaryKey, $id)->delete();
                    $this->controller->_afterDelete();

                }
            } catch (\Exception $e) {
                $this->errors['general_error'] = $e->getMessage();
            }
        }
        return $band;
    }

    function createInputDataArray()
    {
        $responseArray = [];
        $request       = request();
        $isUpdate      = getRagnosInputValue($this->primaryKey) !== null;

        foreach ($this->ofieldlist as $k => $fieldItem) {
            // Skip fields with queries
            if ($fieldItem->getQuery() != '') {
                continue;
            }

            // Check if the field has changed during an update
            if ($isUpdate && !$fieldItem->hasChanged()) {
                continue;
            }

            $fieldValue = $fieldItem->getDataFromInput($request);

            // Set NULL for empty RSearchField values
            if ($fieldItem instanceof RSearchField && $fieldValue === '') {
                $fieldValue = null;
            }

            $responseArray[$fieldItem->getFieldName()] = $fieldValue;
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
                if ($fieldItem instanceof RSearchField) {
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
        if (getRagnosInputValue($this->primaryKey)) {
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

    protected function logAudit($action, $recordId, $changes = null)
    {
        if (!$this->enableAudit)
            return;

        $auditModel = new \App\ThirdParty\Ragnos\Models\AuditLogModel();

        $auditModel->insert([
            'user_id'    => session()->get('usu_id') ?? 0, // Ajusta según tu sistema de auth
            'table_name' => $this->table, // O $this->getTableName()
            'record_id'  => $recordId,
            'action'     => $action,
            'changes'    => $changes ? json_encode($changes) : null,
            'ip_address' => request()->getIPAddress(),
            'user_agent' => (string) request()->getUserAgent()
        ]);
    }
}
<?php

namespace App\ThirdParty\Ragnos\Models\Traits;

use App\ThirdParty\Ragnos\Models\Fields\RSearchField;

trait CrudOperationsTrait
{
    private function performInsert(): void
    {
        if (!$this->canInsert) {
            $this->errors['general_error'] = 'No tiene permisos para crear registros.';
            return;
        }

        try {
            $inputDataArray = $this->createInputDataArray();
            if ($this->usesAutoIncrement() && isset($inputDataArray[$this->primaryKey])) {
                unset($inputDataArray[$this->primaryKey]);
            }

            $this->db->transBegin();
            $this->controller->_beforeInsert($inputDataArray);
            $primaryKey = $this->insert($inputDataArray);
            if ($primaryKey === false) {
                throw new \RuntimeException('No se pudo insertar el registro.');
            }

            $_POST[$this->primaryKey] = $primaryKey;
            $this->insertedId         = $primaryKey;
            $this->logAudit('INSERT', $primaryKey, ['new' => $inputDataArray]);
            $this->controller->_afterInsert();

            if (!$this->db->transCommit()) {
                throw new \RuntimeException('No se pudo confirmar la inserción.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            $this->insertedId = null;
            log_message('error', '[CrudOperationsTrait::performInsert] ' . $e->getMessage());
            $this->errors['general_error'] = $e->getMessage();
        }
    }

    private function performUpdate(): void
    {
        if (!$this->canUpdate) {
            $this->errors['general_error'] = 'No tiene permisos para actualizar registros.';
            return;
        }

        try {
            $request        = request();
            $inputDataArray = $this->createInputDataArray();
            if (sizeof($inputDataArray) > 0) {
                $this->db->transBegin();
                $this->controller->_beforeUpdate($inputDataArray);

                $id = getInputValue($this->primaryKey);
                if ($id === null || $id === '') {
                    throw new \InvalidArgumentException('ID del registro a actualizar no proporcionado.');
                }

                $pkKeyAnt = 'Ragnos_value_ant_' . $this->primaryKey;
                if (getInputValue($pkKeyAnt) !== null && fieldHasChanged($this->primaryKey)) {
                    $id                                = oldValue($this->primaryKey);
                    $inputDataArray[$this->primaryKey] = newValue($this->primaryKey);
                }

                if (!$this->update($id, $inputDataArray)) {
                    throw new \RuntimeException('No se pudo actualizar el registro.');
                }

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

                if (!$this->db->transCommit()) {
                    throw new \RuntimeException('No se pudo confirmar la actualización.');
                }
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', '[CrudOperationsTrait::performUpdate] ' . $e->getMessage());
            $this->errors['general_error'] = $e->getMessage();
        }
    }

    public function performDelete($pid = null): bool
    {
        $band = false;
        if ($this->canDelete) {
            try {
                $id = getInputValue('id') ?? $pid;
                if ($id === null || $id === '') {
                    throw new \InvalidArgumentException('ID del registro a eliminar no proporcionado.');
                }

                $currentRecord = $this->find($id);
                if (!$currentRecord) {
                    throw new \RuntimeException("El registro con ID {$id} no existe.");
                }
                setOldRecordCache($currentRecord);

                $this->db->transBegin();
                $this->controller->_beforeDelete();
                if ($this->enableAudit) {
                    $this->logAudit('DELETE', $id, ['deleted_data' => $currentRecord]);
                }

                $band = $this->where($this->primaryKey, $id)->delete();
                if (!$band) {
                    throw new \RuntimeException('No se pudo eliminar el registro.');
                }

                $this->controller->_afterDelete();
                if (!$this->db->transCommit()) {
                    throw new \RuntimeException('No se pudo confirmar la eliminación.');
                }
            } catch (\Throwable $e) {
                $this->db->transRollback();
                $band = false;
                log_message('error', '[CrudOperationsTrait::performDelete] ' . $e->getMessage());
                $this->errors['general_error'] = $e->getMessage();
            }
        } else {
            $this->errors['general_error'] = 'No tiene permisos para eliminar registros.';
        }
        return $band;
    }

    function createInputDataArray(): array
    {
        $responseArray = [];
        $request       = request();
        $isUpdate      = getInputValue($this->primaryKey) !== null;

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
        $data['fields']            = $this->ofieldlist;
        $data['detailsController'] = $this->controller->detailsController;
        $data['primaryKey']        = $this->primaryKey;
        $data['primaryKeyValue']   = $id;
        return view('App\ThirdParty\Ragnos\Views\rdatasetmodel/form_data', $data);
    }

    private function getValuesFor($id)
    {
        $a = array_keys($this->ofieldlist);
        $this->evaluaSelect($a, $this->table);
        if ($id != '') {
            $this->builder()->where($this->table . '.' . $this->primaryKey, $id);
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

        // Si es un update y el cliente no envió campos Ragnos_value_ant_ (modo API),
        // pre-cargamos el registro actual desde la DB para que oldValue() funcione
        // en callbacks, validaciones is_unique y el audit log.
        $id = getInputValue($this->primaryKey);
        if ($id !== null && getInputValue('Ragnos_value_ant_' . $this->primaryKey) === null) {
            $currentRecord = $this->find($id);
            if ($currentRecord) {
                setOldRecordCache($currentRecord);
            }
        }

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

    function processFormAction(): void
    {
        if ($this->isUpdateRequest()) {
            $this->performUpdate();
        } else {
            $this->performInsert();
        }
    }

    private function isUpdateRequest(): bool
    {
        $action = strtolower((string) getInputValue('Ragnos_action', ''));
        if ($action !== '') {
            return $action === 'update';
        }

        if (getInputValue('Ragnos_value_ant_' . $this->primaryKey) !== null) {
            return true;
        }

        return in_array(strtoupper(request()->getMethod()), ['PUT', 'PATCH'], true);
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
        if (!$this->enableAudit) {
            return;
        }

        // 1. Obtenemos el ID limpiamente (sin modificar la session global)
        $userId = $this->getCurrentUserId();

        $auditModel = new \App\ThirdParty\Ragnos\Models\AuditLogModel();

        $auditModel->insert([
            'user_id'    => $userId,
            'table_name' => $this->table,
            'record_id'  => $recordId,
            'action'     => $action,
            // 2. UNESCAPED_UNICODE para que guarde acentos y ñ correctamente en el JSON
            'changes'    => $changes ? json_encode($changes, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => request()->getIPAddress(),
            'user_agent' => (string) request()->getUserAgent()
        ]);
    }

    /**
     * Método auxiliar para resolver la identidad del usuario para auditoría
     */
    private function getCurrentUserId(): int
    {
        $auth = service('Admin_aut');
        return $auth->getUserId() ?? 0;
    }

    /**
     * Método auxiliar para resolver el nombre del usuario para auditoría
     */
    private function getCurrentUserName(): ?string
    {
        $auth = service('Admin_aut');
        return $auth->getUserName();
    }
}
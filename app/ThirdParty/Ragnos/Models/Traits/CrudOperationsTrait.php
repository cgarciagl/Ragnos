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

                    $id = getInputValue($this->primaryKey);

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
                    $id = getInputValue('id');
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
        if (getInputValue($this->primaryKey)) {
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
     * Método auxiliar para resolver la identidad sin efectos secundarios
     */
    private function getCurrentUserId(): int
    {
        // A. Si ya hay sesión (Web), usarla directo
        if (session()->has('usu_id')) {
            return (int) session()->get('usu_id');
        }

        // B. Si es API, intentar resolver el token
        if (isApiCall()) {
            $header = request()->getHeaderLine('Authorization');

            // Extraer el token si viene como "Bearer <token>"
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            } else {
                $token = $header; // Intento fallback por si envían el token crudo
            }

            if ($token) {
                $db = \Config\Database::connect();
                // Solo seleccionamos el ID para optimizar memoria
                $user = $db->table('gen_usuarios')
                    ->select('usu_id')
                    ->where('usu_token', $token)
                    ->get()
                    ->getRowArray();

                if ($user) {
                    return (int) $user['usu_id'];
                }
            }
        }

        return 0; // Usuario desconocido o sistema
    }
}
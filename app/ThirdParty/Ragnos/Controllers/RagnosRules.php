<?php

namespace App\ThirdParty\Ragnos\Controllers;

class RagnosRules
{
    public function readonly_Ragnos($value, $params, $data, &$error = null)
    {
        $isChanged = fieldHasChanged($params);
        if ($isChanged) {
            $error = lang('Ragnos.form_validation_readonly_Ragnos');
            return FALSE;
        }
        return TRUE;
    }
}

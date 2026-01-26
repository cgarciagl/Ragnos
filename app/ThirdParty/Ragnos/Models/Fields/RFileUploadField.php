<?php

namespace App\ThirdParty\Ragnos\Models\Fields;

use CodeIgniter\HTTP\IncomingRequest;

class RFileUploadField extends RField
{
    protected string $uploadPath = 'assets/uploads';
    protected bool $isImage = false;

    public function setUploadPath(string $path)
    {
        $this->uploadPath = $path;
    }

    public function loadFromArray(array $array): void
    {
        parent::loadFromArray($array);
        if (isset($array['uploadPath'])) {
            $this->setUploadPath($array['uploadPath']);
        }
    }

    public function hasChanged(): bool
    {
        $request = request();
        $file    = $request->getFile($this->getFieldName());

        // Si hay archivo subido válido
        if ($file && $file->isValid()) {
            return true;
        }

        // Si se marcó borrar
        if ($request->getPost('delete_' . $this->getFieldName())) {
            return true;
        }

        return false;
    }

    public function getDataFromInput(IncomingRequest $request): mixed
    {
        $file = $request->getFile($this->getFieldName());

        // Caso: borrar archivo
        if ($request->getPost('delete_' . $this->getFieldName())) {
            return '';
        }

        // Caso: subir archivo
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName(); // Nombre aleatorio

            // Ruta física en public
            $targetDir = FCPATH . $this->uploadPath;

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $file->move($targetDir, $newName);

            // Retornar ruta relativa normalizada (con slashes normales)
            return rtrim($this->uploadPath, '/\\') . '/' . $newName;
        }

        return '';
    }

    /**
     * Sobrescribe getRules para manejar la validación condicional en updates
     */
    public function getRules(): ?string
    {
        $rules = parent::getRules();
        if (empty($rules)) {
            return $rules;
        }

        // Verificamos si hay un archivo subido en el request actual
        $request = request();
        $file    = $request->getFile($this->getFieldName());

        // Si hay archivo válido subido, mantenemos todas las reglas
        if ($file && $file->isValid()) {
            return $rules;
        }

        // Si NO hay archivo válido subido (está vacío o error 4)

        // Verificamos si existe un archivo previo (edición)
        // Intentamos obtenerlo del POST (campo hidden inyectado) o de la propiedad value
        $currentValue = $request->getPost('Ragnos_current_' . $this->getFieldName());
        if (empty($currentValue) && !empty($this->value)) {
            $currentValue = $this->value;
        }

        // Si hay valor previo, significa que el usuario quiere "mantener" el archivo.
        if (!empty($currentValue)) {
            // Reglas específicas de archivo en CI4 que debemos remover para que no fallen
            // al no haber un $_FILES válido.
            $fileRules = ['uploaded', 'max_size', 'is_image', 'mime_in', 'ext_in', 'max_dims'];

            $ruleParts = explode('|', $rules);
            $newRules  = [];

            foreach ($ruleParts as $rule) {
                $rule = trim($rule);
                if (empty($rule))
                    continue;

                // Extraer nombre base de la regla (ej: uploaded[campo] -> uploaded)
                $ruleName = $rule;
                if (($pos = strpos($rule, '[')) !== false) {
                    $ruleName = substr($rule, 0, $pos);
                }

                // Si NO es una regla de archivo, la conservamos
                if (!in_array($ruleName, $fileRules)) {
                    $newRules[] = $rule;
                }
            }

            return implode('|', $newRules);
        }

        // Si no hay archivo nuevo Y no hay valor previo:
        // Dejamos las reglas tal cual. 
        // Si tenía 'uploaded', fallará correctamente (campo requerido).
        // Si no tenía 'uploaded', pasará (campo opcional).
        return $rules;
    }
}

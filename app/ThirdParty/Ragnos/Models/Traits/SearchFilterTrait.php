<?php

namespace App\ThirdParty\Ragnos\Models\Traits;

use App\ThirdParty\Ragnos\Models\Fields\RSearchField;

trait SearchFilterTrait
{
    private function setWhereForSearchInMultipleFields($textForSearch, $onlyfield = '')
    {
        $campos     = $onlyfield ? [$onlyfield] : $this->tablefields;
        $conditions = array_map(function ($k) use ($textForSearch) {
            return $this->buildConditionForField($k, $textForSearch);
        }, $campos);
        $conditions = array_filter($conditions);
        $this->builder()->where('(' . implode(' OR ', $conditions) . ')', NULL, FALSE);
    }

    private function buildConditionForField($fieldName, $textForSearch)
    {
        $field = $this->ofieldlist[$fieldName];
        if ($field->getType() == 'dropdown') {
            return $this->buildDropdownCondition($field, $textForSearch);
        } else {
            return $this->buildTextCondition($field, $textForSearch);
        }
    }

    private function buildDropdownCondition($field, $textForSearch)
    {
        $textForSearch = str_replace("'", "''", $textForSearch); // Escape single quotes
        $options       = array_filter($field->getOptions(), function ($v) use ($textForSearch) {
            return strpos(strtolower($v), strtolower($textForSearch)) !== false;
        });
        if (empty($options)) {
            return '';
        }
        $opciones = implode(',', array_map(function ($key) {
            return "'$key'";
        }, array_keys($options)));
        return "{$this->table}.{$this->realField($field->getFieldName())} IN ($opciones)";
    }

    private function buildTextCondition($field, $textForSearch)
    {
        // 1. Obtener la conexión a la base de datos de CodeIgniter 4
        $db = \Config\Database::connect();

        // 2. Escapar el valor de búsqueda para evitar inyección SQL
        // $db->escape() envuelve la cadena entre comillas y escapa el contenido.
        // También maneja la función de escape específica del driver (p. ej., mysqli_real_escape_string).
        $escapedText = $db->escape('%' . $textForSearch . '%');

        // 3. Determinar la columna/expresión a buscar (igual que antes)
        $sqlExpression = $field->getQuery() ?: ($field instanceof RSearchField ? $field->getSqlForSearch() : '');

        if (empty($sqlExpression)) {
            $table         = $field instanceof RSearchField ? $field->tablesearch : $this->table;
            $fieldName     = $this->realField($field->getFieldName());
            $sqlExpression = "$table.$fieldName";
        }

        // 4. Construir la condición concatenando el valor **escapado**
        if ($this->isPostgres()) {
            // La función LOWER se aplica solo al valor de búsqueda en el LIKE
            return "LOWER(CAST(($sqlExpression) AS TEXT)) LIKE LOWER($escapedText)";
        } else {
            // El texto escapado ya incluye las comillas simples
            return "($sqlExpression) LIKE $escapedText";
        }
    }

    function isPostgres()
    {
        $db = db_connect();
        return $db->getPlatform() === 'Postgre';
    }

    private function performSearchForJson()
    {
        $request     = request();
        $searchValue = htmlspecialchars_decode($request->getPost('search[value]'));

        if ($searchValue) {
            $field = $request->getPost('sOnlyField');
            $this->setWhereForSearchInMultipleFields($searchValue, $field);
        }

        $filter = base64_decode($request->getPost('sFilter'));
        if ($filter) {
            $this->builder()->where($filter, NULL, FALSE);
        }
    }

    function getCountForSearch()
    {
        $this->completeFieldList();
        $this->checkRelations();
        $this->performSearchForJson();
        return $this->builder()->countAllResults(false);
    }


    function getCountForSearchSQL($sql)
    {
        $this->completeFieldList();
        $this->checkRelations();
        $this->performSearchForJson();
        $sqlCompiled = $this->builder()->getCompiledSelect();
        $sqlCompiled = " WITH {$this->table} AS ({$sql})  " . $sqlCompiled;
        $db          = db_connect();
        $query       = $db->query($sqlCompiled);
        return $query->getNumRows();
    }

    public function checkRelations()
    {
        foreach ($this->ofieldlist as $k => $f) {
            if ($f->getQuery() != '') {
                $sql   = $f->getQuery();
                $campo = $f->getFieldToShow();
                $this->builder()->select("( $sql ) as $campo ", FALSE);
            } else if ($f instanceof RSearchField) {
                $f->checkRelation($this, $this->table);
            } else {
                $this->builder()->select($this->table . '.' . $this->realField($k));
            }
        }
    }

    private function evaluaSelect($campos, $tablename)
    {
        foreach ($campos as $campo) {
            $fieldItem = $this->fieldByName($campo);
            if ($fieldItem->getQuery() == '') {
                $this->builder()->select($tablename . '.' . $campo);
            }
        }
    }
}
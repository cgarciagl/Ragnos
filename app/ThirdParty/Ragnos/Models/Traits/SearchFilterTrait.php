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
        $textForSearch = str_replace("'", "''", $textForSearch); // Escape single quotes
        $sql           = $field->getQuery() ?: ($field instanceof RSearchField ? $field->getSqlForSearch() : '');

        if ($sql) {
            // Usar CAST según el motor de base de datos
            return $this->isPostgres()
                ? "LOWER(CAST(($sql) AS TEXT)) LIKE LOWER('%$textForSearch%')"
                : "($sql) LIKE '%$textForSearch%'";
        } else {
            $table     = $field instanceof RSearchField ? $field->tablesearch : $this->table;
            $fieldName = $this->realField($field->getFieldName());
            return $this->isPostgres()
                ? "LOWER(CAST($table.$fieldName AS TEXT)) LIKE LOWER('%$textForSearch%')"
                : "$table.$fieldName LIKE '%$textForSearch%'";
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
        $filter = str_replace("'", "''", $filter); // Escape single quotes
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
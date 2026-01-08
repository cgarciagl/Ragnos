<?php

namespace App\ThirdParty\Ragnos\Models\Traits;

trait JsonResultTrait
{
    function getTableAjax()
    {
        if ($this->table) {
            $count = $this->getCountForSearch();
            $this->performSearchForJson();
            $this->setLimitForJsonResult();
            $this->setOrderByForJsonResult();
            $query = $this->builder()->get();
            return $this->generateJsonResult($query, $count);
        } else
            return NULL;
    }

    function getTableAjaxBySQL($sql)
    {
        if ($this->table) {
            $count = $this->getCountForSearchSQL($sql);
            $this->checkRelations();
            $this->performSearchForJson();
            $this->setLimitForJsonResult();
            $this->setOrderByForJsonResult();
            $sqlCompiled = $this->builder()->getCompiledSelect();
            $sqlCompiled = " WITH {$this->table} AS ({$sql})  " . $sqlCompiled;
            $db          = db_connect();
            $query       = $db->query($sqlCompiled);
            return $this->generateJsonResult($query, $count);
        } else
            return NULL;
    }

    function getTableForAPI()
    {
        if ($this->table) {
            $count = $this->getCountForSearch();
            $this->performSearchForJson();
            $this->setLimitForJsonResult();
            $this->setOrderByForJsonResult();
            $query = $this->builder()->get();
            return ['data' => $query->getResultArray(), 'countAll' => $count];
        } else
            return NULL;
    }

    public function generateJsonResult($query, $count)
    {
        $request = request();

        $datos = $query->getResultArray();

        $responseData['draw']            = intval(getInputValue('draw'));
        $responseData['recordsTotal']    = $count;
        $responseData['recordsFiltered'] = $count;
        $responseData['sSearch']         = getInputValue('search');
        $responseData['data']            = [];

        $i = 0;
        foreach ($datos as $aRow) {
            foreach ($this->tablefields as $f) {
                $responseData['data'][$i][] = $this->textForTable($aRow, $f);
            }
            $responseData['data'][$i][] = addslashes(@$aRow[$this->primaryKey]);
            $i++;
        }
        return json_encode($responseData);
    }

    private function setOrderByForJsonResult()
    {
        $request     = request();
        $orderColumn = getInputValue('order[0][column]');
        $orderDir    = getInputValue('order[0][dir]');

        if (!empty($orderColumn)) {
            if ($orderColumn < count($this->tablefields)) {
                $this->builder()->orderBy($this->realField($this->tablefields[$orderColumn]), $orderDir);
            }
        } elseif (!empty($this->defaultSortingField)) {
            $this->builder()->orderBy($this->realField($this->defaultSortingField), $this->defaultSortingDir);
        }
    }

    private function setLimitForJsonResult()
    {
        $request = request();
        $limit   = (int) getInputValue('length') ?: 10; // Default limit
        $offset  = (int) getInputValue('start') ?: 0;   // Default offset
        $this->builder()->limit($limit, $offset);
    }
}
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

    public function generateJsonResult($query, $count)
    {
        $request = request();

        $datos = $query->getResultArray();

        $responseData['draw']            = intval($request->getPost('draw'));
        $responseData['recordsTotal']    = $count;
        $responseData['recordsFiltered'] = $count;
        $responseData['sSearch']         = $request->getPost('search');
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
        $request = request();
        if (($request->getPost('order[0][column]') != '')) {
            $col = $request->getPost('order[0][column]');
            $dir = $request->getPost('order[0][dir]');
            if ($col <= (count($this->tablefields) - 1)) {
                $this->builder()->orderBy($this->realField($this->tablefields[$col]), $dir);
            }
        } else {
            if ($this->defaultSortingField) {
                $this->builder()->orderBy($this->realField($this->defaultSortingField), $this->defaultSortingDir);
            }
        }
    }

    private function setLimitForJsonResult()
    {
        $request = request();
        $limit   = $request->getPost('length');
        $offset  = $request->getPost('start');
        $this->builder()->limit($limit, $offset);
    }
}
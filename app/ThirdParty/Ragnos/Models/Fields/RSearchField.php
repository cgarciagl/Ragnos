<?php

namespace App\ThirdParty\Ragnos\Models\Fields;

use App\ThirdParty\Ragnos\Models\Fields\rfieldDecorator;
use CodeIgniter\HTTP\IncomingRequest;

class RSearchField extends RFieldDecorator
{

    protected $fieldtoshow;
    protected $controller;
    protected $filter;
    protected $callback;
    protected $idvalue;
    protected mixed $default;



    protected $sqlForSearch;

    public $tablesearch;

    public function getIdValue()
    {
        return $this->idvalue;
    }


    public function setIdValue($idvalue)
    {
        $this->idvalue = $idvalue;
    }

    public function getController()
    {
        return $this->controller ?? '';
    }

    public function setController($controller)
    {
        $this->controller = $controller;
    }

    public function getFilter()
    {
        return $this->filter ?? '';
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function getCallback()
    {
        return $this->callback ?? '';
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;
    }


    public function getFieldToShow()
    {
        return $this->fieldtoshow ?? '';
    }

    public function setFieldToShow($fieldtoshow)
    {
        $this->fieldtoshow = $fieldtoshow;
    }

    public function constructControl(): string
    {
        $loadedVars                = $this->loadVars();
        $loadedVars['fieldtoshow'] = $this->getFieldToShow() ?? '';
        $loadedVars['controller']  = $this->getController() ?? '';
        $loadedVars['idvalue']     = $this->getIdValue() ?? '';
        $loadedVars['filter']      = $this->getFilter() ?? '';
        $loadedVars['callback']    = $this->getCallback() ?? '';
        $this->checkDefault($loadedVars);
        return view('App\ThirdParty\Ragnos\Views\rfield/searchfield', $loadedVars);
    }

    function checkDefault(&$inputArray)
    {
        if (isset($this->default)) {
            if (!isset($inputArray['idvalue'])) {
                if (is_array($this->default)) {
                    $inputArray['value']   = $this->default['text'];
                    $inputArray['idvalue'] = $this->default['id'];
                } else {
                    $inputArray['value'] = $this->default;
                }
            }
        }
    }

    public function checkRelation(&$model, $tablefield)
    {
        $mock              = importModelFromController($this->getController());
        $tablename         = $mock->table;
        $joincondition     = "{$tablename}.{$mock->primaryKey} = {$tablefield}.{$this->getFieldName()}";
        $this->tablesearch = $tablename;

        $mock->completeFieldList();
        $fieldToShow = $mock->tablefields[0];
        $this->setFieldToShow($fieldToShow);

        $sql = $mock->fieldByName($fieldToShow)->getQuery();
        unset($mock);

        $db = $model->builder();
        $db->join($tablename, $joincondition, 'left');

        if ($sql) {
            $this->setSQLforSearch($sql);
            $db->select("($sql) as $fieldToShow", false);
        } else {
            $db->select("{$tablename}.{$fieldToShow}");
        }
    }

    public function setSQLforSearch($sql)
    {
        $this->sqlForSearch = $sql;
    }

    public function getSQLforSearch()
    {
        return $this->sqlForSearch ?? '';
    }

    public function getDataFromInput(IncomingRequest $request): mixed
    {
        return $request->getPost('Ragnos_id_' . $this->getFieldName());
    }
}

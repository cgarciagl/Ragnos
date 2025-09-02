<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\Ragnos;


abstract class RDatasetController extends RDataset
{

    private $hasDetailsProp = FALSE;
    private $sortingField = -1;
    private $sortingDir = 'asc';

    public $master = NULL;

    /**
     * Constructor de la clase
     */
    function __construct()
    {
        parent::__construct();
        //si en el request viene el parametro "master" se asigna a la propiedad "master"
        $this->master = request()->getPost('Ragnos_master', NULL);
    }

    function setSortingField($value, $dir = 'asc')
    {
        $v = -1;
        if (is_string($value)) {
            $pos = array_search($value, $this->modelo->tablefields);
            if ($pos !== FALSE) {
                $v = $pos;
            }
        } elseif (is_integer($value)) {
            $v = $value;
        }
        $this->sortingField                = $v;
        $this->sortingDir                  = $dir;
        $this->modelo->defaultSortingField = $value;
        $this->modelo->defaultSortingDir   = $dir;
    }

    function setHasDetails($band): void
    {
        $this->hasDetailsProp = (bool) $band;
    }

    function hasDetails(): bool
    {
        return $this->hasDetailsProp;
    }

    /**
     * Devuelve la vista inicial con los datos de la relación definida
     */
    function index(): string
    {
        checkAjaxRequest(request());
        $tableData['content'] = $this->renderTable();
        return view('App\ThirdParty\Ragnos\Views\ragnos/template', $tableData);
    }

    /**
     * Devuelve una vista construida para administrar el CRUD del catálogo
     *
     * @return string
     */
    function renderTable(): string
    {
        $this->modelo->completeFieldList();
        $tableData = $this->getTableData();
        return view('App\ThirdParty\Ragnos\Views\rdatasetcontroller/table_view', $tableData);
    }

    private function getCommonData(): array
    {
        return [
            'controller_name'  => $this->getControllerName(),
            'controller_class' => get_class($this),
            'title'            => $this->title,
            'modelo'           => $this->modelo,
            'tablefields'      => $this->modelo->tablefields,
            'fieldlist'        => $this->modelo->ofieldlist,
            'sortingField'     => $this->sortingField,
            'sortingDir'       => $this->sortingDir,
        ];
    }

    private function getTableData(): array
    {
        $data               = $this->getCommonData();
        $data['hasdetails'] = $this->hasDetails();
        $data['master']     = $this->master;
        return $data;
    }

    private function getSearchData(): array
    {
        $data               = $this->getCommonData();
        $data['primaryKey'] = $this->modelo->primaryKey;
        $data['sSearch']    = request()->getPost('sSearch');
        $data['sFilter']    = request()->getPost('sFilter');
        return $data;
    }

    private function getControllerName(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * Devuelve una vista construida con los resultados de una búsqueda
     *
     * @return string
     */
    function renderSearchResults(): string
    {
        $this->modelo->completeFieldList();
        $searchData = $this->getSearchData();
        return view('App\ThirdParty\Ragnos\Views\rdatasetcontroller/search_view', $searchData);
    }



    /**
     * Devuelve una vista construida para generar reportes automatizados para el catálogo
     *
     * @return string
     */
    function renderReport(): string
    {
        helper('form');
        $data['controller_name']  = (new \ReflectionClass($this))->getShortName();
        $data['controller_class'] = get_class($this);
        $data['title']            = $this->title;
        $this->modelo->completeFieldList();
        $data['tablefields'] = $this->modelo->tablefields;
        $data['fieldlist']   = $this->modelo->ofieldlist;
        return view('App\ThirdParty\Ragnos\Views\rdatasetcontroller/report_view', $data);
    }

    /**
     * devuelve un objeto JSON, ya sea con los errores generados o con el resultado 'ok'
     */
    private function showErrorsOrOk()
    {
        $result = [];
        if (empty($this->modelo->errors)) {
            $result['result'] = 'ok';
            if ($this->modelo->insertedId) {
                $result['insertedid'] = $this->modelo->insertedId;
            }
        } else {
            $result['result'] = 'error';
            $result['errors'] = $this->modelo->errors;
        }
        returnAsJSON($result);
    }

    /**
     * Permite borrar un registro via AJAX
     */
    function delete()
    {
        if ($this->modelo->canDelete) {

            checkAjaxRequest(request());

            try {
                $this->processDelete();
            } catch (\Exception $e) {
                $this->modelo->errors['general_error'] = $e->getMessage();
            }
            $this->showErrorsOrOk();
        }
    }

    /**
     * Ejecuta el proceso de borrado con sus triggers
     */
    private function processDelete()
    {
        checkAjaxRequest(request());
        $this->modelo->performDelete();
        $this->showErrorsOrOk();
    }

    protected function _customFormDataFooter()
    {
        return '';
    }

    /**
     * Obtiene via AJAX el formulario para ingresar datos al catálogo
     *
     * Para mostrar el formulario vacío para un nuevo registro, solo no pasar ningún parámetro
     *
     * @param string $id
     */
    function getFormData($id = 'new')
    {
        checkAjaxRequest(request());

        $formSubmissionData = $this->modelo->getFormData($id);
        echo view('App\ThirdParty\Ragnos\Views\ragnos/justecho', ['content' => $formSubmissionData]);
        $formSubmissionData = $this->_customFormDataFooter();
        echo view('App\ThirdParty\Ragnos\Views\ragnos/justecho', ['content' => $formSubmissionData]);
    }

    /**
     * Devuelve los datos del catálogo en formato JSON, via AJAX
     */
    function getAjaxGridData()
    {
        checkAjaxRequest(request());
        $this->applyFilters();
        $ajaxTableResponse = $this->modelo->getTableAjax();
        returnAsJSON($ajaxTableResponse);
    }

    /**
     * Recibe los datos del formulario via AJAX, para procesarles
     */
    function formProcess()
    {
        checkAjaxRequest(request());

        $this->modelo->processFormInput();
        $this->showErrorsOrOk();

    }

    /**
     * Devuelve los resultados de una búsqueda, via AJAX
     */
    function searchByAjax()
    {
        checkAjaxRequest(request());
        echo $this->renderSearchResults();
    }

    /**
     * Devuelve una vista construida para mostrar la tabla de resultados de búsquedas
     */
    function search()
    {
        $data['content'] = $this->renderSearchResults();
        echo view('App\ThirdParty\Ragnos\Views\ragnos/template', $data);
    }

    /**
     * Devuelve el formulario del reporte del catálogo, via AJAX
     */
    function reportByAjax()
    {
        checkAjaxRequest(request());
        echo $this->renderReport();
    }

    /**
     * Devuelve la vista de tabla para administrar el catálogo, solo via AJAX
     */
    function tableByAjax()
    {
        checkAjaxRequest(request());
        echo $this->renderTable();
    }

    /**
     * Devuelve una vista construida para generar el reporte del catálogo
     */
    function report()
    {
        $data['content'] = $this->renderReport();
        echo view('App\ThirdParty\Ragnos\Views\ragnos/template', $data);
    }

    /**
     * Muestra los resultados del reporte en diferentes formatos
     */
    function showReport()
    {
        // $this->ydatasetreportlib = new YDatasetReportLib;
        // helper('url');
        // $this->applyFilters();
        // $data['tabla'] = $this->ydatasetreportlib->buildReport($this);
        // $this->ydatasetreportlib->renderReportOutput($data);
    }

    /**
     * Devuelve un JSON con los datos de un registro , solo via AJAX
     */
    function getRecordByAjax()
    {
        checkAjaxRequest(request());
        $id = request()->getPost('id');
        if ($id) {
            $this->modelo->completeFieldList();
            $res = $this->modelo->find($id);
            returnAsJSON($res);
        }
    }

    public function is_unique_Ragnos($inputString, $field)
    {
        $inputString     = request()->getPost($field);
        $primarykeyvalue = newValue($this->modelo->primaryKey);
        $this->modelo->setWhere($this->modelo->primaryKey . ' <> ', $primarykeyvalue);
        $this->modelo->setWhere($field, $inputString);
        $c = $this->builder()->countAllResults(false);
        if ($c > 0) {
            return FALSE;
        }
        return TRUE;
    }

    public function readonly_Ragnos($string, $field)
    {
        $isFieldChanged = fieldHasChanged($field);
        if ($isFieldChanged) {
            return FALSE;
        }
        return TRUE;
    }
}

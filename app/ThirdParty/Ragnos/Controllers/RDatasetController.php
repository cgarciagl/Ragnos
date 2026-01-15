<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\Ragnos;
use CodeIgniter\API\ResponseTrait;


abstract class RDatasetController extends RDataset
{
    private $hasDetailsProp = FALSE;
    public $detailsController = NULL;
    private $sortingField = -1;
    private $sortingDir = 'asc';

    public $master = NULL;

    /**
     * Almacena temporalmente los datos originales antes de un Update
     */
    private $originalDataForAudit = [];

    use ResponseTrait;

    /**
     * Constructor de la clase
     */
    function __construct()
    {
        parent::__construct();
        //si en el request viene el parametro "master" se asigna a la propiedad "master"
        $this->master = getInputValue('Ragnos_master', null);
    }

    function setEnableAudit($band): void
    {
        $this->modelo->enableAudit = (bool) $band;
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

    function setDetailsController($controllerClassName): void
    {
        $this->detailsController = $controllerClassName;
        $this->setHasDetails(TRUE);
    }

    /**
     * Devuelve la vista inicial con los datos de la relación definida
     */
    public function index(): string|\CodeIgniter\HTTP\Response
    {
        // 1. Si es API, delegamos y retornamos inmediatamente (Cláusula de Guarda)
        if (isApiCall()) {
            return $this->handleApiRequest();
        }

        // 2. Si no es API, asumimos flujo de Vista/HTML

        // 2.1 Si no es AJAX, cargamos la plantilla completa
        $request = request();
        if (!$request->isAJAX()) {
            return view('App\ThirdParty\Ragnos\Views\ragnos/catalogo_view', [
                'controller' => get_class($this),
                'object'     => $this
            ]);
        } else {
            checkAjaxRequest();
            return view('App\ThirdParty\Ragnos\Views\ragnos/template', [
                'content' => $this->renderTable()
            ]);
        }
    }

    /**
     * Maneja la lógica específica de la respuesta API
     */
    private function handleApiRequest(): \CodeIgniter\HTTP\Response
    {
        $this->applyFilters();

        // Obtenemos y normalizamos los datos
        $data = $this->getNormalizedTableData();

        return $this->respond([
            'status' => 200,
            'data'   => $data['data'] ?? [],
            'count'  => count($data['data'] ?? []),
            'total'  => $data['countAll'] ?? 0,
        ]);
    }

    /**
     * Normaliza la respuesta del modelo, asegurando que siempre sea un array
     */
    private function getNormalizedTableData(): array
    {
        $response = $this->modelo->getTableForAPI();

        return is_array($response) ? $response : [];
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
        $data['sSearch']    = getInputValue('sSearch');
        $data['sFilter']    = getInputValue('sFilter');
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
    function ajaxdelete()
    {
        if ($this->modelo->canDelete) {

            checkAjaxRequest();

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
        checkAjaxRequest();
        $this->modelo->performDelete();
        $this->showErrorsOrOk();
    }

    protected function _customFormDataFooter($id)
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
        checkAjaxRequest();

        $formSubmissionData = $this->modelo->getFormData($id);
        echo $formSubmissionData;
        $customFormDataFooter = $this->_customFormDataFooter($id);
        echo $customFormDataFooter;
    }

    /**
     * Devuelve los datos del catálogo en formato JSON, via AJAX
     */
    function getAjaxGridData()
    {
        checkAjaxRequest();
        $this->applyFilters();
        $ajaxTableResponse = $this->modelo->getTableAjax();
        returnAsJSON($ajaxTableResponse);
    }

    /**
     * Recibe los datos del formulario via AJAX, para procesarles
     */
    function formProcess()
    {
        checkAjaxRequest();

        $this->modelo->processFormInput();
        $this->showErrorsOrOk();

    }

    /**
     * Devuelve los resultados de una búsqueda, via AJAX
     */
    function searchByAjax()
    {
        checkAjaxRequest();
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
        checkAjaxRequest();
        echo $this->renderReport();
    }

    /**
     * Devuelve la vista de tabla para administrar el catálogo, solo via AJAX
     */
    function tableByAjax()
    {
        checkAjaxRequest();
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
        checkAjaxRequest();
        $id = getInputValue('id');
        if ($id) {
            $this->modelo->completeFieldList();
            $res = $this->modelo->find($id);
            returnAsJSON($res);
        }
    }

    public function is_unique_Ragnos($inputString, $field)
    {
        $inputString     = getInputValue($field);
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

    /**
     * Guarda el registro (Insert o Update)
     */
    public function save()
    {
        // 1. Obtener datos (Soporte híbrido JSON o POST)
        if (request()->getHeaderLine('Content-Type') === 'application/json') {
            $data = request()->getJSON(true);
        } else {
            $data = request()->getPost();
        }

        // Determinar ID y si es Insert o Update
        $pk       = $this->modelo->primaryKey; // Asumiendo que tu modelo tiene esta propiedad pública
        $id       = $data[$pk] ?? null;
        $isInsert = empty($id);

        // 2. Validaciones (Usando las reglas acumuladas en el Dataset)
        // Asumo que tienes una propiedad $this->validationRules poblada por addField
        if (!$this->validate($this->validationRules ?? [])) {
            $errors = $this->validator->getErrors();

            if (isApiCall()) {
                return $this->failValidationErrors($errors);
            }

            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            if ($isInsert) {
                // --- INSERT ---
                $this->modelo->performInsert($data);

                $newId = $this->modelo->insertedId;

                $message      = 'Registro creado exitosamente.';
                $responseData = ['id' => $newId];

            } else {
                // --- UPDATE ---
                $this->modelo->performUpdate($id, $data);

                $message      = 'Registro actualizado exitosamente.';
                $responseData = ['id' => $id];
            }


            if ($this->db->transStatus() === false) {
                throw new \Exception("Error al guardar en base de datos.");
            }

            // 4. Respuesta Exitosa
            if (isApiCall()) {
                return $this->respond([
                    'status'  => 200,
                    'message' => $message,
                    'data'    => $responseData
                ]);
            }

            // Redirección para Web (AdminLTE)
            return redirect()->to(current_url())->with('success', $message);

        } catch (\Exception $e) {

            if (isApiCall()) {
                return $this->failServerError($e->getMessage());
            }

            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un registro
     */
    public function delete($id = null)
    {
        // Si no viene por parámetro, intentar buscarlo en POST (para forms web) o JSON body
        if (!$id) {
            $id = getInputValue($this->modelo->primaryKey) ?? getInputValue('id');
        }

        if (!$id) {
            return isApiCall()
                ? $this->fail('ID no proporcionado', 400)
                : redirect()->back()->with('error', 'ID no proporcionado');
        }

        try {
            // Ejecutar borrado
            $deleted = $this->modelo->performDelete($id);

            if (!$deleted) {
                throw new \Exception("No se pudo eliminar el registro o no existe.");
            }

            // Respuesta
            if (isApiCall()) {
                return $this->respondDeleted(['id' => $id, 'message' => 'Registro eliminado']);
            }

            return redirect()->to(current_url())->with('success', 'Registro eliminado correctamente.');

        } catch (\Exception $e) {

            if (isApiCall()) {
                return $this->failServerError($e->getMessage());
            }

            return redirect()->back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function history($id)
    {
        checkApiCall();

        $auditModel = new \App\ThirdParty\Ragnos\Models\AuditLogModel();
        $logs       = $auditModel->where('table_name', $this->modelo->table)
            ->where('record_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->respond(['data' => $logs]);
    }
}

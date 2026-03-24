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

    /**
     * @param string|array $controllerClassName
     */
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
     * Devuelve la vista de tabla para administrar el catálogo, solo via AJAX
     */
    function tableByAjax()
    {
        checkAjaxRequest();
        echo $this->renderTable();
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
            
            // Inyectar datos en $_POST para compatibilidad nativa con los RSearchFields y el core de Ragnos
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    $_POST[$k] = $v;
                }
            }
        } else {
            $data = request()->getPost();
        }

        // Determinar ID y si es Insert o Update para el mensaje
        $pk       = $this->modelo->primaryKey;
        $id       = $data[$pk] ?? null;
        $isInsert = empty($id);

        try {
            // 2. Ejecutar validación y guardado usando el pipeline interno del modelo
            // Esto dispara completado de listado, carga de reglas, run() y processFormAction() (Insert/Update)
            $this->modelo->processFormInput();

            // 3. Evaluar los errores arrojados por el modelo
            if (!empty($this->modelo->errors)) {
                if (isApiCall()) {
                    return $this->failValidationErrors($this->modelo->errors);
                }
                return redirect()->back()->withInput()->with('errors', $this->modelo->errors);
            }

            // 4. Respuesta Exitosa
            $message      = $isInsert ? 'Registro creado exitosamente.' : 'Registro actualizado exitosamente.';
            $responseData = ['id' => $this->modelo->insertedId ?? $id];

            if (isApiCall()) {
                return $this->respond([
                    'status'  => $isInsert ? 201 : 200,
                    'message' => $message,
                    'data'    => $responseData
                ], $isInsert ? 201 : 200);
            }

            // Redirección para Web
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

    /**
     * Obtiene la configuración de campos (usado por el generador de reportes)
     */
    public function getFieldsConfig()
    {
        return $this->modelo->ofieldlist ?? [];
    }

    /**
     * Obtiene los campos visibles en tabla (usado por el generador de reportes)
     */
    public function getTableFields()
    {
        return $this->modelo->tablefields ?? [];
    }

    /**
     * Genera un reporte avanzado basado en la configuración del Dataset actual.
     * Esta función maneja tanto la visualización del formulario de configuración
     * como el procesamiento del mismo.
     *
     * @param string|null $configUrl La URL a la que debe apuntar el formulario. Si es null, se usa la URL actual.
     * @return mixed string|view Retorna la vista de configuración o el resultado del reporte
     */
    public function genericAdvancedReport($configUrl = null)
    {
        helper(['form', 'url']);

        // Si no se define URL, usamos cadena vacía para que el form haga POST a la misma URL actual
        // esto evita problemas con current_url() vs site_url() y posibles redirecciones que pierdan el POST
        if ($configUrl === null) {
            $configUrl = '';
        }

        // $this es la instancia del controlador Dataset
        $generator = new RDatasetReportGenerator($this);

        $request = \Config\Services::request();

        // A. Procesar POST (Generación)
        if (strtolower($request->getMethod()) === 'post') {
            $generator->processRequest($request);
            return $generator->renderResultView();
        }

        // B. Renderizar Configuración (GET)
        return $generator->renderConfigView($configUrl);
    }
}

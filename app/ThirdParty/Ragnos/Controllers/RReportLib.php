<?php

namespace App\ThirdParty\Ragnos\Controllers; // Mantener el espacio de nombres existente

use App\ThirdParty\Ragnos\Controllers\Ragnos;
use CodeIgniter\HTTP\IncomingRequest; // Importar para inyección de dependencia
use CodeIgniter\HTTP\ResponseInterface; // Importar para el tipo de retorno en renderReportOutput
use CodeIgniter\Database\BaseResult; // Para el tipo de retorno de get()
use CodeIgniter\Model; // Para tipar la propiedad modelo y métodos

abstract class RReportLib
{
    private array $reportfields = [];
    private string $descfilter = '';
    private array $groups = [];
    private int $totalrecords = 0;
    private int $grouprecords = 0;
    protected ?Model $modelo = null; // Usar Model para tipado y nullable

    protected IncomingRequest $request; // Inyectar la petición
    protected ResponseInterface $response; // Inyectar la respuesta
    protected string $title = ''; // Declarar la propiedad title que se usa

    public function __construct()
    {
        // Obtener los servicios de CodeIgniter directamente
        $this->request  = service('request');
        $this->response = service('response');
    }

    // El método __get no es una práctica común en CI4 para acceder a la instancia de CI.
    // Si realmente necesita esto, consideraría refactorizar para inyectar dependencias.
    // Sin embargo, si su framework lo requiere, se puede mantener, pero es una "bandera roja".
    public function __get($attr)
    {
        // Esto parece ser un remanente de CodeIgniter 3's $this->ci->load->library...
        // En CI4, las dependencias deben inyectarse o obtenerse a través de Services.
        // Si Ragnos::get_CI() proporciona una instancia de Application (o similar),
        // debería asegurarse de que el acceso a sus propiedades sea seguro.
        $CI = Ragnos::get_CI(); //
        if (isset($CI->$attr)) { //
            return $CI->$attr; //
        }
        return NULL; //
    }

    private function initReport($controller): void
    {
        // Se asume que $controller tiene los métodos getTitle() y una propiedad 'modelo'.
        // Es crucial que $controller->modelo sea una instancia de CodeIgniter\Model.
        if (!($controller->modelo instanceof Model)) { //
            // Considerar lanzar una excepción o loggear un error si no es un modelo válido
            log_message('error', 'El objeto modelo del controlador no es una instancia de CodeIgniter\Model.');
            throw new \RuntimeException('Modelo del controlador no es una instancia válida.');
        }

        $this->title  = $controller->getTitle(); //
        $this->modelo = $controller->modelo; //
        $this->modelo->completeFieldList(); //
        $this->modelo->checkRelations(); //
        $this->reportfields = $this->modelo->tablefields; //
    }

    public function buildReport($controller): string
    {
        $this->initReport($controller); //
        $modelo               = $controller->modelo; //
        $reportselectfields[] = $modelo->primaryKey; //
        foreach ($this->reportfields as $f) { //
            $reportselectfields[] = $modelo->realField($f); //
            $reportselectfields[] = $f; //
        }
        $reportselectfields = array_unique($reportselectfields); //
        return $this->getTable($reportselectfields); //
    }

    private function getTable(array $reportselectfields): string
    {
        $this->modelo->select($reportselectfields); //
        $limiteReporte = (int) env('Ragnos_report_limit', 0); // // Castear a int al obtener
        if ($limiteReporte > 0) { //
            $this->modelo->limit($limiteReporte); //
        }
        $this->applyFiltersAndOrder($this->modelo); //

        // Usar un método de CodeIgniter para obtener los resultados de la consulta
        // get() en un modelo de CodeIgniter 4 suele devolver QueryBuilder o Builder.
        // Asumiendo que $this->modelo->get($this->modelo->table) devuelve un objeto QueryBuilder o similar que se puede iterar.
        $queryResult = $this->modelo->get($this->modelo->table); //

        // Necesitamos asegurarnos de que $queryResult sea un objeto de resultados iterable.
        // Si get() devuelve QueryBuilder, quizás necesite $queryResult->getResultArray();
        if ($queryResult instanceof BaseResult) { // Check if it's a valid result object
            return $this->generateTable($queryResult->getResultArray(), $this->modelo);
        }
        // Si no es un BaseResult, asuma que es QueryBuilder y necesita getResultArray()
        return $this->generateTable($queryResult->getResultArray(), $this->modelo); // Asumiendo que devuelve QueryBuilder
    }


    private function applyFiltersAndOrder(Model $modelo): void
    {
        $ordertoapply = []; //
        for ($i = 1; $i <= 3; $i++) { //
            $nivel          = getInputValue("nivel{$i}", FILTER_SANITIZE_STRING); // Usar $this->request
            $filter         = getInputValue("filter{$i}", FILTER_SANITIZE_STRING); // Usar $this->request
            $ragnosIdFilter = getInputValue("Ragnos_id_filter{$i}"); // Usar $this->request

            if (!empty($nivel)) { // Usar !empty() para cadenas vacías
                if (!empty($filter)) { // Usar !empty()
                    $this->modelo->setWhere($nivel, $ragnosIdFilter); //
                    $this->descfilter .= " < {$modelo->ofieldlist[$nivel]->getLabel()} = '{$filter}' > "; //
                }
                $realField      = $modelo->realField($nivel); //
                $ordertoapply[] = $realField; //
                $a              = [ //
                    'field'     => $nivel,
                    'count'     => 0,
                    'current'   => '',
                    'realField' => $realField,
                    'label'     => $modelo->ofieldlist[$nivel]->getLabel()
                ];
                $this->groups[] = $a; //
            }
        }
        $ordertoapply[] = $modelo->tablefields[0]; //
        if (sizeof($ordertoapply) > 0) { //
            $this->modelo->setOrderByField(implode(',', $ordertoapply)); //
        }
    }

    private function generateTableHeader(Model $modelo): string
    {
        $this->grouprecords = 0; //
        $a['cuantoscampos'] = sizeof($this->reportfields); //
        $a['title']         = $this->title; //
        $a['reportfields']  = $this->reportfields; //
        $a['modelo']        = $modelo; //
        return view('App\ThirdParty\Ragnos\Views\rreportlib/table_header', $a); //
    }

    private function generateTableFooter(): string
    {
        $a['cuantoscampos'] = sizeof($this->reportfields); //
        $a['grouprecords']  = $this->grouprecords; //
        $a['title']         = $this->title; //
        return view('App\ThirdParty\Ragnos\Views\rreportlib/table_footer', $a); //
    }

    private function generateTableRow(array $row, Model $modelo): string
    {
        $temp_string = "<tr>"; //
        foreach ($this->reportfields as $f) { //
            $temp_string .= "<td> {$this->fieldToReport($row, $modelo, $f)} </td>"; //
        }
        $temp_string .= "</tr>"; //
        $this->totalrecords++; //
        $this->grouprecords++; //
        return $temp_string; //
    }

    private function generateRowOrLevel(array $row): string
    {
        $showldwritelevelheader = FALSE; //
        $showldwritelevelfooter = TRUE; //
        $encab                  = ''; //
        $this->calculateEncab($row, $showldwritelevelheader, $showldwritelevelfooter, $encab); //
        return $this->generateEncabAndDetail($row, $showldwritelevelheader, $showldwritelevelfooter, $encab); //
    }

    private function calculateEncab(array $row, bool &$showldwritelevelheader, bool &$showldwritelevelfooter, string &$encab): void
    {
        $i = 2; //
        foreach ($this->groups as &$g) { //
            $i++; //
            // Usar isset para @$row[$g['field']] o verificar clave
            if (($g['current'] != ($row[$g['field']] ?? null)) || ($showldwritelevelheader)) { //
                if ($g['current'] == '') { //
                    $showldwritelevelfooter = FALSE; //
                }
                $g['current']            = $row[$g['field']] ?? ''; // // Usar operador de coalescencia nula
                $showldwritelevelheader  = TRUE; //
                $encab                  .= "<h{$i}>{$g['label']}: " . ($row[$g['realField']] ?? '') . "  </h{$i}>";
            }
        }
    }

    private function generateEncabAndDetail(array $row, bool $showldwritelevelheader, bool $showldwritelevelfooter, string $encab): string
    {
        $temp_string = ''; //
        if ($showldwritelevelheader) { //
            if ($showldwritelevelfooter) { //
                $temp_string .= $this->generateTableFooter(); //
            }
            $temp_string .= $encab; //
            $temp_string .= $this->generateTableHeader($this->modelo); //
        }
        $temp_string .= $this->generateTableRow($row, $this->modelo); //
        return $temp_string; //
    }

    private function generateTable(array $queryResults, Model $modelo): string
    {
        $t            = lang('Ragnos.Ragnos_report_of'); //
        $temp_string  = "<h1> {$t} {$this->title} </h1>"; //
        $temp_string .= "<h2> {$this->descfilter} </h2>"; //
        if (sizeof($this->groups) == 0) { //
            $temp_string .= $this->generateTableHeader($modelo); //
        }
        foreach ($queryResults as $row) { // Iterar directamente sobre el array de resultados
            $temp_string .= $this->generateRowOrLevel($row); //
        }
        $temp_string .= $this->generateTableFooter(); //
        $t            = lang('Ragnos.Ragnos_total'); //
        $temp_string .= "<hr> <h3 style='text-align:right'> {$t}: {$this->totalrecords} {$this->title} </h3>"; //
        return $temp_string; //
    }

    /**
     * Genera la salida del reporte.
     *
     * @param array $data datos de entrada
     * @return ResponseInterface Devuelve el objeto Response de CodeIgniter.
     */
    public function renderReportOutput(array $data): ResponseInterface
    {
        $reportType = getInputValue('typeofreport', FILTER_SANITIZE_STRING); //

        if ($reportType === 'htm') { //
            $htmlContent = view('App\ThirdParty\Ragnos\Views\rreportlib/report_format_html_ajax', $data, ['save' => true]); //
            return $this->response->setStatusCode(200)
                ->setHeader('Content-Type', 'text/html; charset=utf-8')
                ->setBody($htmlContent);
        } elseif ($reportType === 'xls') { //
            $xlsContent = view('App\ThirdParty\Ragnos\Views\rreportlib/report_format_html', $data, ['save' => true]); //
            return $this->response->setStatusCode(200)
                ->setHeader('Content-type', 'application/vnd.ms-excel; name="excel"') //
                ->setHeader('Content-Disposition', 'attachment; filename="reporte.xls"') // Mejorar a 'attachment' para descarga
                ->setHeader('Pragma', 'no-cache') //
                ->setHeader('Expires', '0') //
                ->setBody($xlsContent);
        }

        // Si no se especifica un tipo de reporte válido, devolver una respuesta vacía o un error.
        return $this->response->setStatusCode(400)->setBody('Tipo de reporte no soportado.');
    }

    private function fieldToReport(array $row, Model $modelo, string $field): string
    {
        return $modelo->textForTable($row, $field); //
    }
}
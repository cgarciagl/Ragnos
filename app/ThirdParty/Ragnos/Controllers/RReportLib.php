<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\Ragnos;

abstract class RReportLib
{

    private $reportfields = [];
    private $descfilter = '';
    private $groups = [];
    private $totalrecords = 0;
    private $grouprecords = 0;
    private $modelo = NULL;

    public function __get($attr)
    {
        $CI = Ragnos::get_CI();
        if (isset($CI->$attr)) {
            return $CI->$attr;
        } else
            return NULL;
    }

    private function initReport($controller)
    {
        $this->title  = $controller->getTitle();
        $this->modelo = $controller->modelo;
        $this->modelo->completeFieldList();
        $this->modelo->checkRelations();
        $this->reportfields = $this->modelo->tablefields;
    }

    public function buildReport($controller)
    {
        $this->initReport($controller);
        $modelo               = $controller->modelo;
        $reportselectfields[] = $modelo->primaryKey;
        foreach ($this->reportfields as $f) {
            $reportselectfields[] = $modelo->realField($f);
            $reportselectfields[] = $f;
        }
        $reportselectfields = array_unique($reportselectfields);
        return $this->getTable($reportselectfields);
    }

    private function getTable($reportselectfields)
    {
        $this->modelo->select($reportselectfields);
        $limiteReporte = env('Ragnos_report_limit', 0);
        if ((int) $limiteReporte > 0) {
            $this->modelo->limit($limiteReporte);
        }
        $this->applyFiltersAndOrder($this->modelo);
        $query = $this->modelo->get($this->modelo->table);
        return $this->generateTable($query, $this->modelo);
    }

    private function applyFiltersAndOrder($modelo)
    {
        $request      = request();
        $ordertoapply = [];
        for ($i = 1; $i <= 3; $i++) {
            if ($request->getPost("nivel$i") != '') {
                if ($request->getPost("filter$i") != '') {
                    $this->modelo->setWhere($request->getPost("nivel$i", TRUE), $request->getPost("Ragnos_id_filter$i"));
                    $this->descfilter .= " < {$modelo->ofieldlist[$request->getPost('nivel' . $i)]->getLabel()} = '{$request->getPost('filter' . $i)}' > ";
                }
                $realField      = $modelo->realField($request->getPost("nivel$i"));
                $ordertoapply[] = $realField;
                $a              = [
                    'field'     => $request->getPost("nivel$i"),
                    'count'     => 0,
                    'current'   => '',
                    'realField' => $realField,
                    'label'     => $modelo->ofieldlist[$request->getPost('nivel' . $i)]->getLabel()
                ];
                $this->groups[] = $a;
            }
        }
        $ordertoapply[] = $modelo->tablefields[0];
        if (sizeof($ordertoapply) > 0) {
            $this->modelo->setOrderBy(implode(',', $ordertoapply));
        }
    }

    private function generateTableHeader($modelo)
    {
        $this->grouprecords = 0;
        $a['cuantoscampos'] = sizeof($this->reportfields);
        $a['title']         = $this->title;
        $a['reportfields']  = $this->reportfields;
        $a['modelo']        = $modelo;
        return view('App\ThirdParty\Ragnos\Views\rreportlib/table_header', $a);
    }

    private function generateTableFooter()
    {
        $a['cuantoscampos'] = sizeof($this->reportfields);
        $a['grouprecords']  = $this->grouprecords;
        $a['title']         = $this->title;
        return view('App\ThirdParty\Ragnos\Views\rreportlib/table_footer', $a);
    }

    private function generateTableRow($row, $modelo)
    {
        $temp_string = "<tr>";
        foreach ($this->reportfields as $f) {
            $temp_string .= "<td> {$this->fieldToReport($row, $modelo, $f)} </td>";
        }
        $temp_string .= "</tr>";
        $this->totalrecords++;
        $this->grouprecords++;
        return $temp_string;
    }

    private function generateRowOrLevel($row)
    {
        $showldwritelevelheader = FALSE;
        $showldwritelevelfooter = TRUE;
        $encab                  = '';
        $this->calculateEncab($row, $showldwritelevelheader, $showldwritelevelfooter, $encab);
        return $this->generateEncabAndDetail($row, $showldwritelevelheader, $showldwritelevelfooter, $encab);
    }

    private function calculateEncab($row, &$showldwritelevelheader, &$showldwritelevelfooter, &$encab)
    {
        $i = 2;
        foreach ($this->groups as &$g) {
            $i++;
            if (($g['current'] != @$row[$g['field']]) || ($showldwritelevelheader)) {
                if ($g['current'] == '') {
                    $showldwritelevelfooter = FALSE;
                }
                $g['current']           = $row[$g['field']];
                $showldwritelevelheader = TRUE;
                $encab .= "<h{$i}>{$g['label']}: {$row[$g['realField']]}  </h{$i}>";
            }
        }
    }

    private function generateEncabAndDetail($row, $showldwritelevelheader, $showldwritelevelfooter, $encab)
    {
        $temp_string = '';
        if ($showldwritelevelheader) {
            if ($showldwritelevelfooter) {
                $temp_string .= $this->generateTableFooter();
            }
            $temp_string .= $encab;
            $temp_string .= $this->generateTableHeader($this->modelo);
        }
        $temp_string .= $this->generateTableRow($row, $this->modelo);
        return $temp_string;
    }

    private function generateTable($query, $modelo)
    {
        $t           = lang('Ragnos.Ragnos_report_of');
        $temp_string = "<h1> {$t} {$this->title} </h1>";
        $temp_string .= "<h2> {$this->descfilter} </h2>";
        if (sizeof($this->groups) == 0) {
            $temp_string .= $this->generateTableHeader($modelo);
        }
        foreach ($query->getResultArray() as $row) {
            $temp_string .= $this->generateRowOrLevel($row);
        }
        $temp_string .= $this->generateTableFooter();
        $t           = lang('Ragnos.Ragnos_total');
        $temp_string .= "<hr> <h3 style='text-align:right'> {$t}: {$this->totalrecords} {$this->title} </h3>";
        return $temp_string;
    }

    /**
     * Genera la salida del reporte
     *
     * @param array $data datos de entrada
     */
    function renderReportOutput($data)
    {
        $this->buildHtmlReport($data);
        $this->buildXlsReport($data);
    }

    /**
     * Construye un reporte con los datos en formato html
     *
     * @param array $data array
     */
    private function buildHtmlReport($data)
    {
        $request = request();
        if ($request->getPost('typeofreport') == 'htm') {
            echo view('App\ThirdParty\Ragnos\Views\rreportlib/report_format_html_ajax', $data);
        }
    }

    /**
     * Construye un reporte con los datos en formato xls
     *
     * @param array $data array
     */
    private function buildXlsReport($data)
    {
        $request = request();
        if ($request->getPost('typeofreport') == 'xls') {
            $vista = view('App\ThirdParty\Ragnos\Views\rreportlib/report_format_html', $data);
            header("Content-type: application/vnd.ms-excel; name='excel'");
            header("Content-Disposition: filename=reporte.xls");
            header("Pragma: no-cache");
            header("Expires: 0");
            echo $vista;
        }
    }

    private function fieldToReport($row, $modelo, $field)
    {
        return $modelo->textForTable($row, $field);
    }
}

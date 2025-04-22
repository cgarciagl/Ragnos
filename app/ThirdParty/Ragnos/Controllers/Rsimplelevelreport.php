<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\Ragnos;

class RSimpleLevelReport
{
    private $title = '';
    private $descfilter = '';
    private $encab = '';
    private $groups = [];
    private $totalrecords = 0;
    private $grouprecords = 0;
    private $listfields = [];
    private $data = [];
    private $showTotals = TRUE;

    public function setShowTotals($showTotals)
    {
        $this->showTotals = (bool) $showTotals;
    }

    public function __get($attr)
    {
        $CI = Ragnos::get_CI();
        if (isset($CI->$attr)) {
            return $CI->$attr;
        } else
            return NULL;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    private $showldwritelevelheader = FALSE;
    private $showldwritelevelfooter = TRUE;

    public function getDescfilter()
    {
        return $this->descfilter;
    }

    public function setDescfilter($descfilter)
    {
        $this->descfilter = $descfilter;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    public function getListFields()
    {
        return $this->listfields;
    }

    public function setListFields($listfields)
    {
        $this->listfields = $listfields;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function quickSetup($title, $data, $listfields, $groups = [], $desc_filter = '')
    {
        $this->title        = $title;
        $this->data         = $data;
        $this->listfields   = $listfields;
        $this->groups       = $groups;
        $this->descfilter   = $desc_filter;
        $this->totalrecords = 0;
    }

    function generateTableHeader()
    {
        $this->grouprecords = 0;
        $output             = '<table>';
        $output .= '<thead><tr>';

        $percentagePerField = (int) (100 / count($this->listfields));

        foreach ($this->listfields as $f => $fieldLabel) {
            $output .= '<th width="' . $percentagePerField . '%">' . htmlspecialchars($fieldLabel) . '</th>';
        }

        $output .= '</tr></thead><tbody>';

        return $output;
    }


    function generateTableFooter()
    {
        $output = '</tbody><tfoot><tr>';
        $output .= '<td colspan="' . count($this->listfields) . '">';

        if ($this->showTotals) {
            $output .= '<h5 style="float:right"> Total = ' . htmlspecialchars($this->grouprecords) . ' ' . htmlspecialchars($this->title) . '</h5>';
        }

        $output .= '</td></tr></tfoot></table>';

        return $output;
    }


    function generateTableRow($row)
    {
        $temp_string = "<tr>";
        foreach ($this->listfields as $fieldIndex => $listFieldLabel) {
            $value       = isset($row[$fieldIndex]) ? $row[$fieldIndex] : $row[$listFieldLabel];
            $temp_string .= "<td> {$value} </td>";
        }
        $temp_string .= "</tr>";
        $this->totalrecords++;
        $this->grouprecords++;
        return $temp_string;
    }

    function generateRowOrLevel($row)
    {
        $this->showldwritelevelheader = FALSE;
        $this->showldwritelevelfooter = TRUE;
        $this->encab                  = '';
        $this->calculateEncab($row);
        return $this->generateEncabAndDetail($row);
    }

    function calculateEncab($row)
    {
        $i = 2;
        if ($this->groups) {
            foreach ($this->groups as $f => &$g) {
                $i++;

                if ((@$g['current'] != $row[$f]) || ($this->showldwritelevelheader)) {
                    if (@$g['current'] == '') {
                        $this->showldwritelevelfooter = FALSE;
                    }
                    $g['current']                 = $row[$f];
                    $this->showldwritelevelheader = TRUE;
                    helper('App\ThirdParty\Ragnos\Helpers\Ragnos_helper');
                    $this->encab .= "<h{$i}> " . ifSet($g['label'], $f) . ": {$row[$f]} </h{$i}>";
                }
            }
        }
    }

    function generateEncabAndDetail($row)
    {
        $summary = '';
        if ($this->showldwritelevelheader) {
            if ($this->showldwritelevelfooter) {
                $summary .= $this->generateTableFooter();
            }
            $summary .= $this->encab;
            $summary .= $this->generateTableHeader();
        }
        $summary .= $this->generateTableRow($row);
        return $summary;
    }

    function generate()
    {
        $output = '<div id="imprimible" class="row">';
        $output .= '<h1>' . htmlspecialchars($this->title) . '</h1>';

        if (!empty($this->descfilter)) {
            $output .= '<h4 style="text-align: center">' . htmlspecialchars($this->descfilter) . '</h4>';
        }

        if (empty($this->groups)) {
            $output .= $this->generateTableHeader();
        }

        foreach ($this->data as $row) {
            $output .= $this->generateRowOrLevel($row);
        }

        $output .= $this->generateTableFooter();
        $output .= '<hr />';

        if ($this->showTotals) {
            $output .= '<h3 style="text-align:right">Total: ' . htmlspecialchars($this->totalrecords) . ' ' . htmlspecialchars($this->title) . '</h3>';
        }

        $output .= '</div>';

        return $output;
    }


    function showSimpleView($rutadevuelta = 'admin/index')
    {
        $data['rutadevuelta'] = $rutadevuelta;
        $data['yo']           = $this;
        echo view('App\ThirdParty\Ragnos\Views\rreportlib/ysimplelevelreport', $data);
    }

    function render($rutadevuelta = 'admin/index')
    {
        $data['rutadevuelta'] = $rutadevuelta;
        $data['yo']           = $this;
        return view('App\ThirdParty\Ragnos\Views\rreportlib/ysimplelevelreport', $data);
    }
}

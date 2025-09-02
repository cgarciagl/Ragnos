<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\Ragnos;
use CodeIgniter\View\RendererInterface; // Para tipar el retorno de view()
use CodeIgniter\HTTP\IncomingRequest; // Si se necesitara inyectar

class RSimpleLevelReport
{
    private string $title = '';
    private string $descfilter = '';
    private string $encab = '';
    private array $groups = [];
    private int $totalrecords = 0;
    private int $grouprecords = 0;
    private array $listfields = [];
    private array $data = [];
    private bool $showTotals = true; //

    private bool $showldwritelevelheader = false; //
    private bool $showldwritelevelfooter = true; //

    // Opcional: Inyectar el servicio de Request si se fuera a usar getPost, getGet, etc.
    // protected IncomingRequest $request;

    public function __construct()
    {
        // Si no se usa request()->getPost/getGet en la clase, no es estrictamente necesario inyectarlo aquí.
        // Pero si se necesitara, sería: $this->request = service('request');

        // Cargar el helper de Ragnos una sola vez si se usa en la clase
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper'); // Cargar el helper aquí o en app/Config/Autoload.php
    }

    public function setShowTotals(bool $showTotals): void //
    {
        $this->showTotals = $showTotals; //
    }

    // El método __get() se mantiene dado el contexto de su framework Ragnos.
    public function __get(string $attr)
    {
        $CI = Ragnos::get_CI(); //
        if (isset($CI->$attr)) { //
            return $CI->$attr; //
        }
        return NULL; //
    }

    public function getData(): array //
    {
        return $this->data; //
    }

    public function setData(array $data): void //
    {
        $this->data = $data; //
    }

    public function getDescfilter(): string //
    {
        return $this->descfilter; //
    }

    public function setDescfilter(string $descfilter): void //
    {
        $this->descfilter = $descfilter; //
    }

    public function getGroups(): array //
    {
        return $this->groups; //
    }

    public function setGroups(array $groups): void //
    {
        $this->groups = $groups; //
    }

    public function getListFields(): array //
    {
        return $this->listfields; //
    }

    public function setListFields(array $listfields): void //
    {
        $this->listfields = $listfields; //
    }

    public function getTitle(): string //
    {
        return $this->title; //
    }

    public function setTitle(string $title): void //
    {
        $this->title = $title; //
    }

    public function quickSetup(string $title, array $data, array $listfields, array $groups = [], string $desc_filter = ''): void //
    {
        $this->title        = $title; //
        $this->data         = $data; //
        $this->listfields   = $listfields; //
        $this->groups       = $groups; //
        $this->descfilter   = $desc_filter; //
        $this->totalrecords = 0; //
    }

    public function generateTableHeader(): string //
    {
        $this->grouprecords = 0; //
        $output             = '<table>'; //
        $output .= '<thead><tr>'; //

        // Asegurarse de que $this->listfields no esté vacío para evitar división por cero.
        $numFields          = count($this->listfields); //
        $percentagePerField = $numFields > 0 ? (int) (100 / $numFields) : 0; //

        foreach ($this->listfields as $fieldIndex => $fieldLabel) { //
            $output .= '<th width="' . $percentagePerField . '%">' . htmlspecialchars($fieldLabel) . '</th>'; //
        }

        $output .= '</tr></thead><tbody>'; //

        return $output; //
    }

    public function generateTableFooter(): string //
    {
        $output = '</tbody><tfoot><tr>'; //
        $output .= '<td colspan="' . count($this->listfields) . '">'; //

        if ($this->showTotals) { //
            $output .= '<h5 style="float:right"> Total = ' . htmlspecialchars($this->grouprecords) . ' ' . htmlspecialchars($this->title) . '</h5>'; //
        }

        $output .= '</td></tr></tfoot></table>'; //

        return $output; //
    }

    public function generateTableRow(array $row): string //
    {
        $temp_string = "<tr>"; //
        foreach ($this->listfields as $fieldIndex => $listFieldLabel) { //
            // Mejorar la lógica para obtener el valor del campo
            // Primero intentar con $fieldIndex (nombre de la columna real)
            // Luego con $listFieldLabel (etiqueta, si se usa como clave en $row)
            $value       = $row[$fieldIndex] ?? ($row[$listFieldLabel] ?? ''); // Usar operador de coalescencia nula doblemente
            $temp_string .= "<td> " . htmlspecialchars((string) $value) . " </td>"; // Escapar HTML y asegurar que sea string
        }
        $temp_string .= "</tr>"; //
        $this->totalrecords++; //
        $this->grouprecords++; //
        return $temp_string; //
    }

    public function generateRowOrLevel(array $row): string //
    {
        $this->showldwritelevelheader = FALSE; //
        $this->showldwritelevelfooter = TRUE; //
        $this->encab                  = ''; //
        $this->calculateEncab($row); //
        return $this->generateEncabAndDetail($row); //
    }

    private function calculateEncab(array $row): void //
    {
        $i = 2; //
        if (!empty($this->groups)) { // // Verificar si hay grupos
            foreach ($this->groups as $f => &$g) { //
                $i++; //

                // Asegurar que la clave $f existe en $row
                $currentGroupValue = $row[$f] ?? null; // Usar coalescencia nula

                // La condición @$g['current'] es problemática. Es mejor inicializar $g['current']
                // o manejar su ausencia de forma explícita. Asumiremos que $g['current'] existe o es null.
                $gCurrent = $g['current'] ?? null;

                if (($gCurrent !== $currentGroupValue) || ($this->showldwritelevelheader)) { //
                    if ($gCurrent === null || $gCurrent === '') { // // Si no tiene un valor previo
                        $this->showldwritelevelfooter = FALSE; //
                    }
                    $g['current']                 = $currentGroupValue; //
                    $this->showldwritelevelheader = TRUE; //
                    // El helper 'App\ThirdParty\Ragnos\Helpers\ragnos_helper' ya se carga en el constructor.
                    $labelToUse  = isset($g['label']) ? $g['label'] : $f; // Usar isset para $g['label']
                    $this->encab .= "<h{$i}> " . htmlspecialchars($labelToUse) . ": " . htmlspecialchars((string) $currentGroupValue) . " </h{$i}>"; // Escapar HTML
                }
            }
        }
    }

    public function generateEncabAndDetail(array $row): string //
    {
        $summary = ''; //
        if ($this->showldwritelevelheader) { //
            if ($this->showldwritelevelfooter) { //
                $summary .= $this->generateTableFooter(); //
            }
            $summary .= $this->encab; //
            $summary .= $this->generateTableHeader(); //
        }
        $summary .= $this->generateTableRow($row); //
        return $summary; //
    }

    public function generate(): string //
    {
        $output = '<div id="imprimible" class="row">'; //
        $output .= '<h1>' . htmlspecialchars($this->title) . '</h1>'; //

        if (!empty($this->descfilter)) { //
            $output .= '<h4 style="text-align: center">' . htmlspecialchars($this->descfilter) . '</h4>'; //
        }

        if (empty($this->groups)) { //
            $output .= $this->generateTableHeader(); //
        }

        foreach ($this->data as $row) { //
            $output .= $this->generateRowOrLevel($row); //
        }

        $output .= $this->generateTableFooter(); //
        $output .= '<hr />'; //

        if ($this->showTotals) { //
            $output .= '<h3 style="text-align:right">Total: ' . htmlspecialchars((string) $this->totalrecords) . ' ' . htmlspecialchars($this->title) . '</h3>'; // // Castear a string y escapar
        }

        $output .= '</div>'; //

        return $output; //
    }

    public function showSimpleView(string $rutadevuelta = 'admin/index'): void //
    {
        $data['rutadevuelta'] = $rutadevuelta; //
        $data['yo']           = $this; //
        // Se recomienda devolver la vista en lugar de hacer echo aquí.
        // El controlador debería ser responsable de enviar la respuesta HTTP.
        echo view('App\ThirdParty\Ragnos\Views\rreportlib/ysimplelevelreport', $data); //
    }

    public function render(string $rutadevuelta = 'admin/index'): string //
    {
        $data['rutadevuelta'] = $rutadevuelta; //
        $data['yo']           = $this; //
        // Este método ya devuelve la vista, lo cual es la práctica preferida en CI4.
        return view('App\ThirdParty\Ragnos\Views\rreportlib/ysimplelevelreport', $data); //
    }
}
<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\Ragnos;
use CodeIgniter\View\RendererInterface; // Para tipar el retorno de view()
use CodeIgniter\HTTP\IncomingRequest; // Si se necesitara inyectar

class RSimpleLevelReport
{
    private string $title = '';
    private string $descfilter = '';
    private string $buffer = '';
    private array $groups = [];
    private int $totalrecords = 0;
    private int $grouprecords = 0;
    private array $listfields = [];
    private array $data = [];
    private bool $showTotals = true;

    // Nuevas propiedades para acumular sumas
    private array $summableFields = [];
    private array $groupTotals = [];
    private array $grandTotals = [];

    // Propiedades renombradas para mayor legibilidad
    private bool $shouldWriteGroupHeader = false;
    private bool $shouldWriteGroupFooter = true;

    public function __construct()
    {
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
    }

    public function setShowTotals(bool $showTotals): void
    {
        $this->showTotals = $showTotals;
    }

    // Define qué campos deben sumarse automáticamente (ej: ['Total', 'Deuda'])
    public function setSummableFields(array $fields): void
    {
        $this->summableFields = $fields;
    }

    public function __get(string $attr)
    {
        $CI = Ragnos::get_CI();
        if (isset($CI->$attr)) {
            return $CI->$attr;
        }
        return NULL;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getDescfilter(): string
    {
        return $this->descfilter;
    }

    public function setDescfilter(string $descfilter): void
    {
        $this->descfilter = $descfilter;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    public function getListFields(): array
    {
        return $this->listfields;
    }

    public function setListFields(array $listfields): void
    {
        $this->listfields = $listfields;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function quickSetup(string $title, array $data, array $listfields, array $groups = [], string $desc_filter = ''): void
    {
        $this->title        = $title;
        $this->data         = $data;
        $this->listfields   = $listfields;
        $this->groups       = $groups;
        $this->descfilter   = $desc_filter;
        $this->totalrecords = 0;

        // Intentar detectar campos sumables automáticamente si no se han definido
        if (empty($this->summableFields)) {
            $candidates           = ['Total', 'Monto', 'Precio', 'Importe', 'Saldo', 'Deuda', 'Pagado', 'Comprado', 'LimiteDeCredito', 'TotalVentasTrimestre', 'TotalVendidoUltimos6Meses', 'MargenTotal'];
            $this->summableFields = array_filter($listfields, function ($field) use ($candidates) {
                // Si la etiqueta o la clave está en candidatos
                return in_array($field, $candidates);
            });
        }
    }

    private function generateTableHeader(): string
    {
        // Reiniciar acumuladores de grupo
        $this->grouprecords = 0;
        $this->groupTotals  = array_fill_keys($this->summableFields, 0);

        $html  = '<div class="table-responsive mt-3 mb-4 shadow-sm rounded border">';
        $html .= '<table class="table table-sm table-striped table-hover mb-0 align-middle">';
        $html .= '<thead class="bg-light text-uppercase small text-secondary"><tr>';

        foreach ($this->listfields as $label) {
            $align  = in_array($label, $this->summableFields) ? 'text-end' : 'text-start';
            $html  .= "<th scope=\"col\" class=\"py-2 px-3 {$align}\">" . htmlspecialchars($label) . "</th>";
        }

        $html .= '</tr></thead><tbody>';
        return $html;
    }

    private function generateTableFooter(bool $isGrandTotal = false): string
    {
        // Si no mostramos totales, cerramos simple
        if (!$this->showTotals) {
            return '</tbody></table></div>';
        }

        $html = $isGrandTotal
            ? '<tfoot><tr class="bg-secondary text-white fw-bold">'
            : '</tbody><tfoot class="fw-bold fs-6 text-dark border-top bg-white"><tr>';

        $totalsSource = $isGrandTotal ? $this->grandTotals : $this->groupTotals;
        $label        = $isGrandTotal ? 'RESUMEN GENERAL' : 'SUBTOTAL';

        $isFirstColumn = true;

        foreach ($this->listfields as $key => $colLabel) {
            $isSummable = in_array($colLabel, $this->summableFields);
            $align      = $isSummable ? 'text-end' : 'text-start';
            $content    = '';

            if ($isFirstColumn) {
                $content       = $isGrandTotal ? $label : $label . " <span class='badge bg-light text-dark border ms-1'>{$this->grouprecords}</span>";
                $isFirstColumn = false;
            }

            if ($isSummable) {
                $val = $totalsSource[$colLabel] ?? 0;
                if (function_exists('moneyFormat')) {
                    $content = moneyFormat($val);
                } else {
                    $content = number_format($val, 2);
                }
            }

            $padding  = $isGrandTotal ? 'py-3 px-3' : 'py-2 px-3';
            $html    .= "<td class=\"{$align} {$padding}\">" . $content . "</td>";
        }

        $html .= '</tr></tfoot>';

        if (!$isGrandTotal) {
            $html .= '</table></div>';
        }

        return $html;
    }

    // Helper para limpiar strings de moneda ($ 1,500.00 -> 1500.00)
    private function cleanNumber($val)
    {
        if (is_numeric($val))
            return $val;
        if (is_string($val)) {
            // Eliminar todo excepto números, punto y signo menos
            return (float) preg_replace('/[^0-9.-]/', '', $val);
        }
        return 0;
    }

    private function generateTableRow(array $row): string
    {
        $html = "<tr>";
        foreach ($this->listfields as $key => $label) {
            $fieldName = is_string($key) ? $key : $label;
            $value     = $row[$fieldName] ?? '';

            // Acumular totales
            if (in_array($label, $this->summableFields)) {
                $numericVal = $this->cleanNumber($value);

                if (!isset($this->groupTotals[$label]))
                    $this->groupTotals[$label] = 0;
                $this->groupTotals[$label] += $numericVal;

                if (!isset($this->grandTotals[$label]))
                    $this->grandTotals[$label] = 0;
                $this->grandTotals[$label] += $numericVal;

                $html .= "<td class=\"text-end px-3\">" . htmlspecialchars((string) $value) . "</td>";
            } else {
                $html .= "<td class=\"px-3\">" . htmlspecialchars((string) $value) . "</td>";
            }
        }
        $html .= "</tr>";
        $this->totalrecords++;
        $this->grouprecords++;
        return $html;
    }

    // --- Lógica Principal de Control Break ---

    private function processRowLogic(array $row): string
    {
        $this->shouldWriteGroupHeader = false;
        $this->shouldWriteGroupFooter = true;
        $headerHtml                   = '';

        // Si hay grupos definidos, verificamos si cambiaron
        if (!empty($this->groups)) {
            $level = 2; // Niveles de título h3, h4...

            foreach ($this->groups as $fieldKey => &$groupConfig) {
                $level++;
                $currentVal = $row[$fieldKey] ?? null;
                $lastVal    = $groupConfig['current'] ?? null;

                // Si cambió el valor, o si un nivel superior cambió (flag activado)
                if (($lastVal !== $currentVal) || $this->shouldWriteGroupHeader) {

                    // Si es el primer registro del TODO (lastVal es null), no hay grupo anterior que cerrar
                    if ($lastVal === null) {
                        $this->shouldWriteGroupFooter = false;
                    }

                    // Actualizamos valor actual
                    $groupConfig['current']       = $currentVal;
                    $this->shouldWriteGroupHeader = true;

                    // Construimos el título del nuevo grupo
                    $label       = $groupConfig['label'] ?? $fieldKey;
                    $headerHtml .= "<div class='mt-4 mb-2 bg-light p-2 ps-3 border-start border-4 border-primary rounded-end shadow-sm d-flex align-items-center'>" .
                        "<span class='text-uppercase text-muted small fw-bold me-2'>" . htmlspecialchars($label) . ":</span>" .
                        "<span class='fs-5 fw-bold text-dark'>" . htmlspecialchars((string) $currentVal) . "</span>" .
                        "</div>";
                }
            }
        }

        $output = '';

        // 1. Si cambiamos de grupo
        if ($this->shouldWriteGroupHeader) {
            // Cerrar tabla anterior si corresponde
            if ($this->shouldWriteGroupFooter) {
                $output .= $this->generateTableFooter();
            }
            // Imprimir títulos de nuevos grupos
            $output .= $headerHtml;
            // Abrir nueva tabla
            $output .= $this->generateTableHeader();
        }

        // 2. Imprimir la fila de datos
        $output .= $this->generateTableRow($row);

        return $output;
    }

    public function generate(): string
    {
        $output = '<div id="imprimible" class="ragnos-report-container p-4 bg-white shadow rounded mb-5">';

        // Header del reporte con fecha
        $output .= '<div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">';
        $output .= '<div>';
        $output .= '<h2 class="mb-0 fw-bold text-dark">' . htmlspecialchars($this->title) . '</h2>';
        if (!empty($this->descfilter)) {
            $output .= '<p class="text-muted mb-0 mt-1">' . htmlspecialchars($this->descfilter) . '</p>';
        }
        $output .= '</div>';
        $output .= '<div class="text-end text-muted small">';
        $output .= '<div>' . date('d/m/Y') . '</div>';
        $output .= '<div>' . date('H:i') . '</div>';
        $output .= '</div>';
        $output .= '</div>';

        // Inicializar acumuladores globales
        $this->grandTotals = [];

        // Si no hay grupos, abrimos la tabla una única vez al inicio
        if (empty($this->groups)) {
            $output .= $this->generateTableHeader();
        }

        foreach ($this->data as $row) {
            $output .= $this->processRowLogic($row);
        }

        // Cerramos la última tabla abierta
        $output .= $this->generateTableFooter();

        $output .= '<hr class="my-4"/>';

        // Generar Tabla de Totales Generales (Resumen)
        if ($this->showTotals && !empty($this->summableFields)) {
            $output .= '<h4 class="mt-4">Resumen General</h4>';
            $output .= '<div class="table-responsive"><table class="table table-sm table-bordered">';
            // Reutilizamos la lógica del footer pero pasándole flag de GrandTotal
            $output .= $this->generateTableFooter(true);
            $output .= '</table></div>';
        } else if ($this->showTotals) {
            $output .= '<h4 class="text-end">Total General: ' .
                htmlspecialchars((string) $this->totalrecords) . ' registros</h4>';
        }

        $output .= '</div>';

        return $output;
    }

    public function render(string $rutadevuelta = 'admin/index'): string
    {
        $data['rutadevuelta'] = $rutadevuelta;
        $data['yo']           = $this;

        return view('App\ThirdParty\Ragnos\Views\rreportlib/ysimplelevelreport', $data);
    }
}
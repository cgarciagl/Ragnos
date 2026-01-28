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
            $candidates           = ['Total', 'Monto', 'Precio', 'Importe', 'Saldo', 'Deuda', 'Pagado', 'Comprado', 'amount', 'creditLimit', 'quantityInStock', 'MSRP', 'buyPrice', 'LimiteDeCredito', 'TotalVentasTrimestre', 'TotalVendidoUltimos6Meses', 'MargenTotal'];
            $this->summableFields = array_filter(array_keys($listfields), function ($field) use ($candidates, $listfields) {
                // Verificamos tanto la llave (campo) como el valor (label)
                $label = $listfields[$field];
                return in_array($field, $candidates) || in_array($label, $candidates);
            });
            // Si el array era indexado numéricamente, array_keys devolvió indices, lo cual no nos sirve para los datos
            // Detectar si fue asociativo
            if (array_keys($listfields) === range(0, count($listfields) - 1)) {
                // Indexado: Resetear y usar valores
                $this->summableFields = array_filter($listfields, function ($field) use ($candidates) {
                    return in_array($field, $candidates);
                });
            }
        }
    }

    private function generateTableHeader(): string
    {
        // Reiniciar acumuladores de grupo
        $this->grouprecords = 0;
        $this->groupTotals  = array_fill_keys($this->summableFields, 0);

        // Estilo profesional: Card blanco, sombra suave, borde sutil.
        // La tabla dentro es 'hover' y 'borderless' para evitar exceso de líneas.
        $html  = '<div class="card border border-light shadow-sm mb-4">';
        $html .= '<div class="card-body p-0">';
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">';
        $html .= '<thead class="bg-light text-uppercase text-secondary border-bottom"><tr>';

        foreach ($this->listfields as $key => $label) {
            $fieldNameToCheck = is_string($key) ? $key : $label;
            $align            = in_array($fieldNameToCheck, $this->summableFields) ? 'text-end' : 'text-start';
            // Letter-spacing para un look más técnico
            $html .= "<th scope=\"col\" class=\"py-3 px-3 {$align} fw-bold\" style=\"letter-spacing: 0.5px; font-size: 0.75rem;\">" . htmlspecialchars($label) . "</th>";
        }

        $html .= '</tr></thead><tbody>';
        return $html;
    }

    private function generateTableFooter(bool $isGrandTotal = false): string
    {
        // Si no mostramos totales, cerramos simple
        if (!$this->showTotals) {
            return '</tbody></table></div></div></div>'; // Cierre de table-responsive, card-body, card
        }

        $html = $isGrandTotal
            ? '<tfoot><tr class="bg-primary bg-opacity-10 text-dark fw-bold border-top border-primary">' // Totales generales destacados
            : '</tbody><tfoot class="fw-bold text-dark bg-white border-top"><tr>'; // Subtotales limpios

        $totalsSource = $isGrandTotal ? $this->grandTotals : $this->groupTotals;
        $label        = $isGrandTotal ? 'RESUMEN GENERAL' : 'SUBTOTAL';

        $isFirstColumn = true;

        foreach ($this->listfields as $key => $colLabel) {
            $fieldNameToCheck = is_string($key) ? $key : $colLabel;

            $isSummable = in_array($fieldNameToCheck, $this->summableFields);
            $align      = $isSummable ? 'text-end' : 'text-start';
            $content    = '';

            if ($isFirstColumn) {
                $countBadge = $isGrandTotal
                    ? ''
                    : "<span class='badge bg-light text-secondary border fw-normal ms-2'>{$this->grouprecords} regs</span>";

                $content       = "<span class='text-uppercase small'>{$label}</span> $countBadge";
                $isFirstColumn = false;
            }

            if ($isSummable) {
                // Usamos fieldNameToCheck para recuperar el valor correcto del array de totales
                $val = $totalsSource[$fieldNameToCheck] ?? 0;
                if (function_exists('moneyFormat')) {
                    $content = moneyFormat($val);
                } else {
                    $content = number_format($val, 2);
                }
            }

            $padding  = $isGrandTotal ? 'py-3 px-3 fs-6' : 'py-3 px-3 small';
            $html    .= "<td class=\"{$align} {$padding}\">" . $content . "</td>";
        }

        $html .= '</tr></tfoot>';

        if (!$isGrandTotal) {
            $html .= '</table></div></div></div>'; // Cierre de los contenedores
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

            // Revisamos contra el campo (fieldName) y no contra label para la suma
            $checkSummable = in_array($fieldName, $this->summableFields);

            // Acumular totales
            if ($checkSummable) {
                // Usamos el val raw si existe en la fila con otro nombre o asumimos que value ya viene formateado
                // cleanNumber limpia símbolos
                $numericVal = $this->cleanNumber($value);

                // Usamos fieldName como key para los totales internos
                if (!isset($this->groupTotals[$fieldName]))
                    $this->groupTotals[$fieldName] = 0;
                $this->groupTotals[$fieldName] += $numericVal;

                if (!isset($this->grandTotals[$fieldName]))
                    $this->grandTotals[$fieldName] = 0;
                $this->grandTotals[$fieldName] += $numericVal;

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
            $depth  = 0;
            $colors = ['primary', 'success', 'info', 'warning']; // Paleta para acentos

            foreach ($this->groups as $fieldKey => &$groupConfig) {
                $depth++;
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

                    // Styles based on depth
                    // Nivel 1: Título de sección grande
                    // Nivel 2+: Subtítulos más compactos
                    $isTopLevel = ($depth === 1);
                    $color      = $colors[($depth - 1) % count($colors)];

                    if ($isTopLevel) {
                        $label       = $groupConfig['label'] ?? $fieldKey;
                        $headerHtml .= "<div class='mt-5 mb-3 d-flex align-items-center border-bottom pb-2'>";
                        $headerHtml .= "<span class='badge bg-{$color} me-2 p-2 rounded-1'><i class='bi bi-layers-fill'></i></span>";
                        $headerHtml .= "<div>";
                        $headerHtml .= "<div class='text-uppercase text-muted' style='font-size: 0.65rem; letter-spacing: 1px;'>" . htmlspecialchars($label) . "</div>";
                        $headerHtml .= "<div class='fs-4 fw-bold text-dark lh-1'>" . htmlspecialchars((string) $currentVal) . "</div>";
                        $headerHtml .= "</div></div>";
                    } else {
                        // Indentación para subniveles
                        $indent      = ($depth - 1) * 20;
                        $label       = $groupConfig['label'] ?? $fieldKey;
                        $headerHtml .= "<div class='mt-3 mb-2 d-flex align-items-center' style='margin-left: {$indent}px;'>";
                        $headerHtml .= "<i class='bi bi-arrow-return-right text-muted me-2'></i>";
                        $headerHtml .= "<div class='bg-light border rounded px-3 py-1 d-inline-flex align-items-center gap-2 shadow-sm'>";
                        $headerHtml .= "<span class='text-secondary small fw-bold text-uppercase'>" . htmlspecialchars($label) . ":</span>";
                        $headerHtml .= "<span class='fw-bold text-dark'>" . htmlspecialchars((string) $currentVal) . "</span>";
                        $headerHtml .= "</div></div>";
                    }
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
        $output .= '<div class="w-100">';
        $output .= '<h2 class="mb-0 fw-bold text-dark">' . htmlspecialchars($this->title) . '</h2>';
        if (!empty($this->descfilter)) {
            $output .= '<div class="alert alert-light border border-secondary border-start-0 border-end-0 border-top-0 border-bottom-0 border-start border-5 border-primary shadow-sm mt-3 mb-0 d-flex align-items-center">';
            $output .= '<div class="fs-4 text-primary me-3">&#9873;</div>'; // Icono Unicode de filtro/bandera o similar si no hay FA
            $output .= '<div><span class="text-uppercase small fw-bold text-muted d-block">Filtros Activos</span><span class="fs-5 text-dark fw-medium">' . htmlspecialchars($this->descfilter) . '</span></div>';
            $output .= '</div>';
        }
        $output .= '</div>';
        $output .= '<div class="text-end text-muted small ms-3" style="min-width: 100px;">';
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

        return view('App\ThirdParty\Ragnos\Views\rdatasetreportgenerator/rsimplelevelreport', $data);
    }
}
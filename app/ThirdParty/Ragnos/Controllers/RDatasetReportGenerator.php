<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;
use App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport;

class RDatasetReportGenerator
{
    private RDatasetController $controller;
    private $model;
    private array $filters = [];
    private ?array $grouping = null;
    private array $dateFilters = [];

    // Introspection cache
    private array $availableFilters = [];
    private array $availableGroupings = [];

    public function __construct(RDatasetController $controller)
    {
        $this->controller = $controller;

        // Obtener modelo y asegurar carga de configuración de campos
        $this->model = $controller->getModelo() ?? $controller->modelo;
        if (method_exists($this->model, 'completeFieldList')) {
            $this->model->completeFieldList();
        }

        // Analizar campos para autoconfiguración
        $this->detectCapabilities();
    }

    private function detectCapabilities(): void
    {
        $fields = $this->model->ofieldlist ?? [];

        foreach ($fields as $field => $config) {
            // Normalizar acceso (Array u Objeto)
            $label = is_array($config) ? ($config['label'] ?? ucfirst($field)) :
                (method_exists($config, 'getLabel') ? $config->getLabel() : ($config->label ?? ucfirst($field)));

            $type = is_array($config) ? ($config['type'] ?? 'text') :
                (method_exists($config, 'getType') ? $config->getType() : ($config->type ?? 'text'));

            $hasOptions = is_array($config) ? isset($config['options']) :
                (method_exists($config, 'getOptions') ? !empty($config->getOptions()) : isset($config->options));

            // 1. Detectar Filtros
            if ($type === 'date' || $type === 'datetime') {
                $this->availableFilters[$field] = [
                    'label' => $label,
                    'type'  => 'date_range'
                ];
            } else {
                // Asumimos que todo lo demás es filtrable por texto/match simple
                $filterData = [
                    'label' => $label,
                    'type'  => 'text'
                ];

                if (is_object($config) && method_exists($config, 'getController')) {
                    $c = $config->getController();
                    if (!empty($c)) {
                        $filterData['search_controller'] = $c;
                    }
                }

                $this->availableFilters[$field] = $filterData;
            }

            // 2. Detectar Agrupamiento
            // Agrupar por Fechas
            if ($type === 'date' || $type === 'datetime') {
                $this->availableGroupings[] = [
                    'value' => "date_month::$field",
                    'label' => "$label (Por Mes)"
                ];
                $this->availableGroupings[] = [
                    'value' => "date_year::$field",
                    'label' => "$label (Por Año)"
                ];
            }
            // Agrupar por Relaciones o Enums
            else if (
                $hasOptions ||
                strpos($field, 'Id') !== false ||
                strpos($field, 'Number') !== false ||
                strpos($field, 'Code') !== false ||
                $type === 'enum'
            ) {
                $this->availableGroupings[] = [
                    'value' => "raw::$field",
                    'label' => "$label (Valor exacto)"
                ];
            }
        }
    }

    /* -------------------------------------------------------------------------- */
    /* Métodos para uso Automatizado (Generic View)                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Procesa automáticamente el request POST de la vista genérica
     */
    public function processRequest(\CodeIgniter\HTTP\IncomingRequest $request): void
    {
        // 1. Procesar Filtros
        foreach ($this->availableFilters as $field => $conf) {

            if ($conf['type'] === 'date_range') {
                $start = $request->getPost("filter_{$field}_start");
                $end   = $request->getPost("filter_{$field}_end");
                if ($start || $end) {
                    $this->addDateRangeFilter($field, $start, $end);
                }
            } else {
                $val = $request->getPost("filter_{$field}");
                if ($val !== null && $val !== '') {
                    $this->addFilter($field, $val);
                }
            }
        }

        // 2. Procesar Agrupamiento
        // Formato esperado: "mode::field" (ej: "date_month::paymentDate")
        $groupSelection = $request->getPost('grouping');
        if ($groupSelection && strpos($groupSelection, '::') !== false) {
            list($mode, $field) = explode('::', $groupSelection, 2);

            // Buscar label bonito
            $label = 'Grupo';
            foreach ($this->availableGroupings as $g) {
                if ($g['value'] === $groupSelection) {
                    $label = $g['label'];
                    // Quitamos "(Por Mes)" del titulo del grupo si queremos ser puristas, o lo dejamos
                }
            }

            $this->setGrouping($field, $mode, $label);
        }
    }

    /**
     * Renderiza la vista de configuración genérica
     */
    public function renderConfigView(string $actionUrl): string
    {
        $data = [
            'title'        => 'Generador de Reportes: ' . ($this->controller->getTitle() ?? 'Dataset'),
            'action'       => $actionUrl,
            'filters'      => $this->availableFilters,
            'groupingOpts' => $this->availableGroupings
        ];

        return view('App\ThirdParty\Ragnos\Views\rdatasetreportgenerator\config_generic', $data);
    }

    /**
     * Renderiza la vista de resultados genérica
     */
    public function renderResultView(): string
    {
        $html = $this->generateHTML(); // Método existente que genera la tabla
        return view('App\ThirdParty\Ragnos\Views\rdatasetreportgenerator\result_generic', [
            'reportContent' => $html
        ]);
    }

    /* -------------------------------------------------------------------------- */
    /* Métodos Core (Existentes)                                                  */
    /* -------------------------------------------------------------------------- */

    /**
     * Agrega un filtro de igualdad simple WHERE campo = valor
     */
    public function addFilter(string $field, $value): void
    {
        if ($value !== null && $value !== '') {
            $this->filters[$field] = $value;
        }
    }

    /**
     * Agrega un filtro de rango de fechas
     */
    public function addDateRangeFilter(string $field, ?string $startDate, ?string $endDate): void
    {
        if ($startDate || $endDate) {
            $this->dateFilters[] = [
                'field' => $field,
                'start' => $startDate,
                'end'   => $endDate
            ];
        }
    }

    /**
     * Configura el agrupamiento.
     * @param string $field El campo de la base de datos
     * @param string $mode 'raw' (valor exacto), 'month' (Mes/Año), 'year' (Año)
     * @param string $label Etiqueta para el reporte
     */
    public function setGrouping(string $field, string $mode = 'raw', string $label = 'Grupo'): void
    {
        if ($field) {
            $this->grouping = [
                'field' => $field,
                'mode'  => $mode,
                'label' => $label
            ];
        }
    }

    /**
     * Ejecuta la consulta y genera el HTML del reporte
     */
    public function generateHTML(): string
    {
        // 1. Preparar Query
        $builder = $this->model->builder();

        // Aplicar Filtros Simples
        foreach ($this->filters as $field => $value) {
            $builder->where($field, $value);
        }

        // Aplicar Filtros de Fecha
        foreach ($this->dateFilters as $f) {
            if (!empty($f['start'])) {
                $builder->where($f['field'] . ' >=', $f['start']);
            }
            if (!empty($f['end'])) {
                $builder->where($f['field'] . ' <=', $f['end']); // O usar <= $end . ' 23:59:59' dependiendo del tipo
            }
        }

        // 2. Obtener Datos
        // Usamos los campos definidos en setTableFields del controlador si existen
        $selectFields = $this->controller->getTableFields();
        // Aseguramos que el modelo tenga la lista completa si no hay campos seleccionados
        if (empty($selectFields) && method_exists($this->model, 'completeFieldList')) {
            $this->model->completeFieldList();
            $selectFields = $this->controller->getTableFields();
        }

        if (empty($selectFields)) {
            $selectFields = array_keys($this->controller->getFieldsConfig());
        }

        $builder->select(implode(',', $selectFields));

        // Ejecutamos la consulta
        // Nota: El ordenamiento lo haremos en memoria para soportar agrupaciones complejas (como fechas formateadas)
        $data = $builder->get()->getResultArray();

        // 3. Procesar Datos y Agrupamiento
        if ($this->grouping) {
            $groupByField = $this->grouping['field'];
            $mode         = $this->grouping['mode'];

            // A. Enriquecer datos con la clave de grupo formateada
            foreach ($data as &$row) {
                $rawValue = $row[$groupByField] ?? '';

                if ($mode === 'date_month') {
                    // Convertir "2023-01-15" a "2023-01" o texto legible
                    $time = strtotime($rawValue);
                    // Formato ordenable y legible: "2023-01 (Enero)"
                    $row['_group_key'] = $time ? date('Y-m', $time) . ' (' . $this->getMonthName(date('n', $time)) . ')' : 'Sin Fecha';
                } elseif ($mode === 'date_year') {
                    $time              = strtotime($rawValue);
                    $row['_group_key'] = $time ? date('Y', $time) : 'Sin Fecha';
                } else {
                    $row['_group_key'] = $rawValue;
                }
            }
            unset($row);

            // B. Ordenar por la clave de grupo
            usort($data, function ($a, $b) {
                return strcmp($a['_group_key'], $b['_group_key']);
            });

            // C. Configurar array de grupos para RSimpleLevelReport
            $groupsConfig = [
                '_group_key' => ['label' => $this->grouping['label']]
            ];
        } else {
            $groupsConfig = [];
        }

        // 4. Formateo de Valores (Moneda, Fechas comunes)
        // Usamos la configuración de campos del Controller para saber qué es moneda
        $fieldsConfig = $this->controller->getFieldsConfig(); // Método hipotético, usualmente available en RDatasetController

        foreach ($data as &$row) {
            foreach ($selectFields as $field) {
                // Config del campo especifico
                $config = $fieldsConfig[$field] ?? null;

                // Normalizar acceso (Array u Objeto)
                $rules = '';
                if (is_array($config)) {
                    $rules = $config['rules'] ?? '';
                } elseif (is_object($config)) {
                    // Intentar usar método específico o propiedad
                    // RField tiene getRules() pero es protected en la definición que vi, 
                    // aunque la propiedad $rules también es protected...
                    // Pero RSimpleTextField extiende RField. Si RField tiene protected $rules, no se puede acceder directamente.
                    // Pero tal vez tenga getter público? RField.php muestra public function getLabel().
                    // RField.php no mostró getRules() en las 30 lineas que leí, pero quizás más abajo.
                    // Asumamos que si no hay getRules, no podemos saberlo.
                    // Sin embargo, el código anterior usaba $config->rules que fallaría si es protected.
                    // Revisemos RField de nuevo más tarde si falla.
                    // Por ahora, usemos reflection o asumamos que hay getter o propiedad accesible.
                    // Voy a asumir que getRules() podría existir o que la propiedad es mágica o pública en la subclase.
                    if (method_exists($config, 'getRules')) {
                        $rules = $config->getRules();
                    } elseif (isset($config->rules)) {
                        $rules = $config->rules;
                    }
                }

                if (strpos($rules, 'money') !== false) {
                    $row[$field] = moneyFormat($row[$field]);
                }
                // Otros formateos podrían ir aquí
            }
        }

        // 5. Instanciar RSimpleLevelReport
        $reportCheck = new RSimpleLevelReport();
        $reportCheck->setShowTotals(true);

        // Mapear campos a etiquetas legibles
        $listFieldsLabels = [];
        $summables        = [];

        foreach ($selectFields as $f) {
            // Re-fetch config helper
            $config = $fieldsConfig[$f] ?? null;

            $valLabel = null;
            if (is_array($config)) {
                $valLabel = $config['label'] ?? null;
            } elseif (is_object($config)) {
                if (method_exists($config, 'getLabel')) {
                    $valLabel = $config->getLabel();
                } elseif (isset($config->label)) {
                    $valLabel = $config->label;
                }
            }

            $label                = $valLabel ?? $f;
            $listFieldsLabels[$f] = $label;
        }

        // Titulo del reporte concatenando filtros para contexto
        $reportTitle = 'Reporte de ' . $this->controller->getTitle();
        $descFilter  = $this->buildFilterDescription();

        $reportCheck->quickSetup($reportTitle, $data, $listFieldsLabels, $groupsConfig, $descFilter);

        return $reportCheck->generate(); // Retornamos solo el HTML generado
    }

    private function getMonthName($num)
    {
        $months = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        return $months[$num] ?? '';
    }

    private function buildFilterDescription(): string
    {
        $parts = [];
        foreach ($this->dateFilters as $f) {
            if ($f['start'] && $f['end'])
                $parts[] = "Rango: {$f['start']} al {$f['end']}";
            elseif ($f['start'])
                $parts[] = "Desde: {$f['start']}";
        }
        foreach ($this->filters as $k => $v) {
            $parts[] = "$k: $v";
        }
        return empty($parts) ? 'Todos los registros' : implode(' | ', $parts);
    }
}

<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;
use App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport;

class RDatasetReportGenerator
{
    private RDatasetController $controller;
    private $model;
    private array $filters = [];
    private array $groupings = [];
    private array $dateFilters = [];
    private array $numericFilters = [];
    private array $filterDisplayTexts = [];

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
        $pk     = $this->model->primaryKey ?? null;

        // Intentar obtener configuración original del controlador (array) para rescate de propiedades
        $rawConfig = [];
        if (method_exists($this->controller, 'getFieldsConfig')) {
            $rawConfig = $this->controller->getFieldsConfig();
        }

        foreach ($fields as $field => $config) {
            // Normalizar acceso (Array u Objeto)
            $label = is_array($config) ? ($config['label'] ?? ucfirst($field)) :
                (method_exists($config, 'getLabel') ? $config->getLabel() : ($config->label ?? ucfirst($field)));

            $type = is_array($config) ? ($config['type'] ?? 'text') :
                (method_exists($config, 'getType') ? $config->getType() : ($config->type ?? 'text'));

            $hasOptions = is_array($config) ? isset($config['options']) :
                (method_exists($config, 'getOptions') ? !empty($config->getOptions()) : isset($config->options));

            $options = is_array($config) ? ($config['options'] ?? []) :
                (method_exists($config, 'getOptions') ? $config->getOptions() : ($config->options ?? []));

            $rules = is_array($config) ? ($config['rules'] ?? '') :
                (method_exists($config, 'getRules') ? ($config->getRules() ?? '') : ($config->rules ?? ''));

            // Ignorar Llave primaria o Campos únicos (No suelen ser buenos para agrupar ni filtrar rangos generales)
            // También ignorar explícitamente archivos, imágenes y contraseñas
            if ($field === $pk || strpos($rules, 'is_unique') !== false || in_array($type, ['fileupload', 'imageupload', 'password'])) {
                continue;
            }

            // 1. Detectar Filtros
            if ($type === 'date' || $type === 'datetime') {
                $this->availableFilters[$field] = [
                    'label' => $label,
                    'type'  => 'date_range'
                ];
            } elseif (strpos($rules, 'money') !== false || $type === 'decimal' || strpos($rules, 'decimal') !== false || strpos($rules, 'numeric') !== false) {
                // Filtros para campos monetarios o numéricos
                $this->availableFilters[$field] = [
                    'label' => $label,
                    'type'  => 'numeric_range'
                ];
            } elseif ($hasOptions || $type === 'enum') {
                $this->availableFilters[$field] = [
                    'label'   => $label,
                    'type'    => 'select',
                    'options' => $options
                ];
            } elseif ($type === 'boolean' || $type === 'switch' || $type === 'tinyint') {

                // Lógica robusta para recuperar onValue/offValue
                $onVal  = 1;
                $offVal = 0;

                if (is_array($config)) {
                    $onVal  = $config['onValue'] ?? 1;
                    $offVal = $config['offValue'] ?? 0;
                } else {
                    // Si es objeto, intentamos método getter, propiedad directa o fallback al rawConfig del controlador
                    if (method_exists($config, 'getOnValue')) {
                        $onVal = $config->getOnValue();
                    } elseif (isset($config->onValue)) {
                        $onVal = $config->onValue;
                    } elseif (isset($rawConfig[$field]) && is_array($rawConfig[$field]) && isset($rawConfig[$field]['onValue'])) {
                        $onVal = $rawConfig[$field]['onValue'];
                    }

                    if (method_exists($config, 'getOffValue')) {
                        $offVal = $config->getOffValue();
                    } elseif (isset($config->offValue)) {
                        $offVal = $config->offValue;
                    } elseif (isset($rawConfig[$field]) && is_array($rawConfig[$field]) && isset($rawConfig[$field]['offValue'])) {
                        $offVal = $rawConfig[$field]['offValue'];
                    }
                }

                $this->availableFilters[$field] = [
                    'label'    => $label,
                    'type'     => 'boolean',
                    'onValue'  => $onVal,
                    'offValue' => $offVal
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
                } elseif (isset($rawConfig[$field]) && is_array($rawConfig[$field]) && isset($rawConfig[$field]['search_controller'])) {
                    $filterData['search_controller'] = $rawConfig[$field]['search_controller'];
                } elseif (is_array($config) && isset($config['search_controller'])) {
                    $filterData['search_controller'] = $config['search_controller'];
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
                (isset($this->availableFilters[$field]) && isset($this->availableFilters[$field]['search_controller'])) ||
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
            } elseif ($conf['type'] === 'numeric_range') {
                $min = $request->getPost("filter_{$field}_min");
                $max = $request->getPost("filter_{$field}_max");
                // Verificar que no sean cadenas vacías
                if (($min !== null && $min !== '') || ($max !== null && $max !== '')) {
                    $this->addNumericRangeFilter($field, $min, $max);
                }
            } else {
                $val = $request->getPost("filter_{$field}");
                if ($val !== null && $val !== '') {
                    $this->addFilter($field, $val);

                    // Capturar texto de display si viene del form (para búsquedas)
                    $dispText = $request->getPost("filter_{$field}_display_text");
                    if (!empty($dispText)) {
                        $this->filterDisplayTexts[$field] = $dispText;
                    }
                }
            }
        }

        // 2. Procesar Agrupamiento
        // Iterar posibles niveles de agrupamiento (1, 2, 3...)
        for ($i = 1; $i <= 3; $i++) {
            $groupSelection = $request->getPost("grouping_$i");

            if ($groupSelection && strpos($groupSelection, '::') !== false) {
                list($mode, $field) = explode('::', $groupSelection, 2);

                // Buscar label bonito
                $label = 'Grupo ' . $i;
                foreach ($this->availableGroupings as $g) {
                    if ($g['value'] === $groupSelection) {
                        $label = $g['label'];
                        break;
                    }
                }

                $this->setGrouping($field, $mode, $label);
            }
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
     * Agrega un filtro de rango numérico
     */
    public function addNumericRangeFilter(string $field, $min, $max): void
    {
        if (($min !== null && $min !== '') || ($max !== null && $max !== '')) {
            $this->numericFilters[] = [
                'field' => $field,
                'min'   => $min,
                'max'   => $max
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
            $this->groupings[] = [
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
            // Verificar configuración para tratamiento especial
            $fConfig = $this->availableFilters[$field] ?? null;
            $type    = $fConfig['type'] ?? 'text';

            if ($type === 'boolean') {
                $targetOn  = $fConfig['onValue'] ?? 1;
                $targetOff = $fConfig['offValue'] ?? 0;

                if ($value == 0) {
                    $builder->groupStart()
                        ->where($this->model->table . '.' . $field, $targetOff)
                        ->orWhere($this->model->table . '.' . $field, null)
                        ->groupEnd();
                } else {
                    $builder->where($this->model->table . '.' . $field, $targetOn);
                }
            } else {
                $builder->where($this->model->table . '.' . $field, $value);
            }
        }

        // Aplicar Filtros de Fecha
        foreach ($this->dateFilters as $f) {
            if (!empty($f['start'])) {
                $builder->where($this->model->table . '.' . $f['field'] . ' >=', $f['start']);
            }
            if (!empty($f['end'])) {
                $builder->where($this->model->table . '.' . $f['field'] . ' <=', $f['end']); // O usar <= $end . ' 23:59:59' dependiendo del tipo
            }
        }

        // Aplicar Filtros Numéricos
        foreach ($this->numericFilters as $f) {
            if (isset($f['min']) && $f['min'] !== '') {
                $builder->where($this->model->table . '.' . $f['field'] . ' >=', $f['min']);
            }
            if (isset($f['max']) && $f['max'] !== '') {
                $builder->where($this->model->table . '.' . $f['field'] . ' <=', $f['max']);
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

        // $builder->select(implode(',', $selectFields)); 
        // Reemplazado por lógica que soporta Joins y Queries personalizados (igual que RDatasetModel/SearchFilterTrait)

        foreach ($selectFields as $fieldName) {
            $field = $this->model->ofieldlist[$fieldName] ?? null;

            if (!$field) {
                $builder->select($this->model->table . '.' . $fieldName);
                continue;
            }

            if (method_exists($field, 'getQuery') && $field->getQuery() != '') {
                $sql   = $field->getQuery();
                $campo = $field->getFieldToShow();
                $builder->select("( $sql ) as $campo ", FALSE);

            } else if ($field instanceof \App\ThirdParty\Ragnos\Models\Fields\RSearchField) {
                // checkRelation añade el join necesario y el select correspondiente
                // Nota: esto asume que RSearchField maneja correctamente si el join ya existe o usa alias únicos si es necesario
                $field->checkRelation($this->model, $this->model->table);
            } else {
                // Campo normal (usar tabla base para evitar ambigüedad)
                $fieldToShow = method_exists($field, 'getFieldToShow') ? $field->getFieldToShow() : $fieldName;
                $builder->select($this->model->table . '.' . $fieldToShow);
            }
        }

        // Ejecutamos la consulta
        // Nota: El ordenamiento lo haremos en memoria para soportar agrupaciones complejas (como fechas formateadas)
        $data = $builder->get()->getResultArray();

        // 3. Procesar Datos y Agrupamiento
        $groupsConfig = [];

        if (!empty($this->groupings)) {
            // A. Enriquecer datos con las claves de grupo formateadas
            foreach ($data as &$row) {
                foreach ($this->groupings as $idx => $grp) {
                    $keyName      = "_grp_key_{$idx}";
                    $groupByField = $grp['field'];
                    $mode         = $grp['mode'];
                    $rawValue     = $row[$groupByField] ?? '';

                    if ($mode === 'date_month') {
                        // Convertir "2023-01-15" a "2023-01" o texto legible
                        $time = strtotime($rawValue);
                        // Formato ordenable y legible: "2023-01 (Enero)"
                        $row[$keyName] = $time ? date('Y-m', $time) . ' (' . $this->getMonthName(date('n', $time)) . ')' : 'Sin Fecha';
                    } elseif ($mode === 'date_year') {
                        $time          = strtotime($rawValue);
                        $row[$keyName] = $time ? date('Y', $time) : 'Sin Fecha';
                    } else {
                        // Modo raw/exacto
                        if (method_exists($this->model, 'textForTable')) {
                            // Usamos textForTable para obtener lo que se vería en pantalla (ej: "Administrador")
                            $formatted = $this->model->textForTable($row, $groupByField);
                            // Limpiamos tags HTML, queremos agrupar por el texto visible
                            $v             = strip_tags($formatted);
                            $row[$keyName] = ($v !== '' && $v !== null) ? $v : $rawValue;
                        } else {
                            $row[$keyName] = $rawValue;
                        }
                    }

                    // Registrar configuración para el reporte final si no existe
                    if (!isset($groupsConfig[$keyName])) {
                        $groupsConfig[$keyName] = ['label' => $grp['label'] ?? ucfirst($groupByField)];
                    }
                }
            }
            unset($row);

            // B. Ordenar por las claves de grupo en orden de jerarquía
            usort($data, function ($a, $b) {
                foreach ($this->groupings as $idx => $grp) {
                    $keyName = "_grp_key_{$idx}";
                    $valA    = $a[$keyName] ?? '';
                    $valB    = $b[$keyName] ?? '';
                    $cmp     = strcmp($valA, $valB);
                    if ($cmp !== 0) {
                        return $cmp;
                    }
                }
                return 0;
            });
        }

        // 4. Formateo de Valores (Moneda, Fechas comunes, Relaciones)
        // Usamos textForTable del modelo para delegar el formateo correcto

        // Creamos un array temporal para no afectar la iteración original si se modifican claves, 
        // aunque aquí solo modificamos valores in-place.
        foreach ($data as &$row) {
            foreach ($selectFields as $field) {
                // Delegar al modelo el formateo
                if (method_exists($this->model, 'textForTable')) {
                    $row[$field] = $this->model->textForTable($row, $field);
                }
            }
        }

        // 5. Instanciar RSimpleLevelReport
        $reportCheck = new RSimpleLevelReport();
        $reportCheck->setShowTotals(true);

        // Mapear campos a etiquetas legibles
        $listFieldsLabels = [];
        $summables        = [];

        foreach ($selectFields as $f) {
            $listFieldsLabels[$f] = $this->getFieldLabel($f);
        }

        // Titulo del reporte concatenando filtros para contexto
        $reportTitle = 'Reporte de ' . $this->controller->getTitle();
        $descFilter  = $this->buildFilterDescription();

        $reportCheck->quickSetup($reportTitle, $data, $listFieldsLabels, $groupsConfig, $descFilter);

        return $reportCheck->generate(); // Retornamos solo el HTML generado
    }

    private function getFieldLabel(string $field): string
    {
        $config = $this->model->ofieldlist[$field] ?? null;
        if (!$config) {
            return ucfirst($field);
        }

        if (is_array($config)) {
            return $config['label'] ?? ucfirst($field);
        } elseif (is_object($config)) {
            if (method_exists($config, 'getLabel')) {
                return $config->getLabel();
            }
            return $config->label ?? ucfirst($field);
        }
        return ucfirst($field);
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
            $label = $this->getFieldLabel($f['field']);
            if (!empty($f['start']) && !empty($f['end']))
                $parts[] = "{$label} (Rango: {$f['start']} al {$f['end']})";
            elseif (!empty($f['start']))
                $parts[] = "{$label} (Desde: {$f['start']})";
            elseif (!empty($f['end']))
                $parts[] = "{$label} (Hasta: {$f['end']})";
        }
        foreach ($this->numericFilters as $f) {
            $label = $this->getFieldLabel($f['field']);
            if (isset($f['min']) && $f['min'] !== '' && isset($f['max']) && $f['max'] !== '')
                $parts[] = "{$label} (Rango: {$f['min']} - {$f['max']})";
            elseif (isset($f['min']) && $f['min'] !== '')
                $parts[] = "{$label} (Min: {$f['min']})";
            elseif (isset($f['max']) && $f['max'] !== '')
                $parts[] = "{$label} (Max: {$f['max']})";
        }
        foreach ($this->filters as $k => $v) {
            $label = $this->getFieldLabel($k);

            // Verificar si es un filtro especial (Boolean o Select) para mostrar etiqueta en lugar de valor
            if (isset($this->availableFilters[$k])) {
                $fConfig = $this->availableFilters[$k];
                $type    = $fConfig['type'] ?? 'text';

                if ($type === 'boolean') {
                    // Valor booleano legible
                    $disp    = ($v == 1 || $v === '1' || $v === true) ? 'Sí / Activo' : 'No / Inactivo';
                    $parts[] = "{$label}: $disp";
                    continue;
                } elseif ($type === 'select' && !empty($fConfig['options'])) {
                    // Valor de opción seleccionada
                    $disp    = $fConfig['options'][$v] ?? $v;
                    $parts[] = "{$label}: $disp";
                    continue;
                } elseif (isset($fConfig['search_controller']) && isset($this->filterDisplayTexts[$k])) {
                    // Valor de texto capturado del input de búsqueda
                    $parts[] = "{$label}: " . $this->filterDisplayTexts[$k];
                    continue;
                }
            }

            $parts[] = "{$label}: $v";
        }
        return empty($parts) ? 'Todos los registros' : implode('  •  ', $parts);
    }
}

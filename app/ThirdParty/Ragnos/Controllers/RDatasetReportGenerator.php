<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;
use App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport;

class RDatasetReportGenerator
{
    private RDatasetController $controller;
    private $model;
    private array $filters = []; // Estructura: [$field => [ ['value'=>v, 'type'=>t, 'display_text'=>txt], ... ]]
    private array $groupings = [];
    private array $dateFilters = []; // Estructura: [$field => [ ['start'=>s, 'end'=>e], ... ]]
    private array $numericFilters = []; // Estructura: [$field => [ ['min'=>m, 'max'=>max], ... ]]
    private bool $filters_loaded_from_session = false;
    private array $filterTypes = []; // Para saber si el filtro es exacto o parcial (LIKE)

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
        // Acción de limpiar vía POST (AJAX)
        if ($request->getPost('clear_ragnos_session')) {
            $session    = service('session');
            $sessionKey = 'ragnos_report_' . md5(get_class($this->controller));
            $session->remove($sessionKey);
            returnAsJSON(['success' => true]);
        }

        // 1. Procesar Filtros
        // Los filtros ahora vienen en un array principal de POST: 'filters_data'
        $allFilters = $request->getPost('filters_data') ?? [];

        foreach ($allFilters as $field => $entries) {
            $conf = $this->availableFilters[$field] ?? null;
            if (!$conf)
                continue;

            foreach ($entries as $entry) {
                // Verificar que la entrada tenga contenido antes de procesar
                $hasContent = false;
                if ($conf['type'] === 'date_range') {
                    if (!empty($entry['start']) || !empty($entry['end']))
                        $hasContent = true;
                } elseif ($conf['type'] === 'numeric_range') {
                    if ((isset($entry['min']) && $entry['min'] !== '') || (isset($entry['max']) && $entry['max'] !== ''))
                        $hasContent = true;
                } else {
                    if (isset($entry['value']) && $entry['value'] !== '')
                        $hasContent = true;
                }

                if (!$hasContent)
                    continue;

                if ($conf['type'] === 'date_range') {
                    $this->addDateRangeFilter($field, $entry['start'] ?? null, $entry['end'] ?? null);
                } elseif ($conf['type'] === 'numeric_range') {
                    $this->addNumericRangeFilter($field, $entry['min'] ?? null, $entry['max'] ?? null);
                } else {
                    $val       = $entry['value'];
                    $matchType = $entry['match_type'] ?? null;

                    if ($matchType === null) {
                        $usePartial = ($conf['type'] === 'text' && !isset($conf['search_controller']));
                    } else {
                        $usePartial = ($matchType === 'partial');
                    }

                    $dispText = !empty($entry['display_text']) ? $entry['display_text'] : null;
                    $this->addFilter($field, $val, $usePartial, $dispText);

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

        // 3. Guardar en Sesión para persistencia
        $session    = service('session');
        $sessionKey = 'ragnos_report_' . md5(get_class($this->controller));
        $session->set($sessionKey, [
            'filters'        => $this->filters,
            'dateFilters'    => $this->dateFilters,
            'numericFilters' => $this->numericFilters,
            'groupings'      => $this->groupings
        ]);
    }

    /**
     * Renderiza la vista de configuración genérica
     */
    public function renderConfigView(string $actionUrl)
    {
        $session    = service('session');
        $sessionKey = 'ragnos_report_' . md5(get_class($this->controller));

        // Acción de limpiar vía GET
        if (request()->getGet('clear')) {
            $session->remove($sessionKey);
            return redirect()->to(current_url());
        }

        // Intentar cargar desde Sesión si están vacíos
        $saved = $session->get($sessionKey);

        if ($saved && empty($this->filters) && empty($this->dateFilters) && empty($this->numericFilters) && empty($this->groupings)) {
            $this->filters        = $saved['filters'] ?? [];
            $this->dateFilters    = $saved['dateFilters'] ?? [];
            $this->numericFilters = $saved['numericFilters'] ?? [];
            $this->groupings      = $saved['groupings'] ?? [];
        }

        $data = [
            'title'            => 'Generador de Reportes: ' . ($this->controller->getTitle() ?? 'Dataset'),
            'action'           => $actionUrl,
            'filters'          => $this->availableFilters,
            'groupingOpts'     => $this->availableGroupings,
            // Valores actuales para repoblar la vista
            'currentFilters'   => $this->filters,
            'currentDateFil'   => $this->dateFilters,
            'currentNumFil'    => $this->numericFilters,
            'currentGroupings' => $this->groupings
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
     * @param string $field Campo a filtrar
     * @param mixed $value Valor del filtro
     * @param bool $usePartialMatch Si es true, usa LIKE para búsqueda parcial
     */
    public function addFilter(string $field, $value, bool $usePartialMatch = false, ?string $displayText = null): void
    {
        if ($value !== null && $value !== '') {
            $this->filters[$field][] = [
                'value'        => $value,
                'type'         => $usePartialMatch ? 'partial' : 'exact',
                'display_text' => $displayText
            ];
        }
    }

    /**
     * Agrega un filtro de rango de fechas
     */
    public function addDateRangeFilter(string $field, ?string $startDate, ?string $endDate): void
    {
        if ($startDate || $endDate) {
            $this->dateFilters[$field][] = [
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
            $this->numericFilters[$field][] = [
                'min' => $min,
                'max' => $max
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

        // Aplicar Filtros Simples con lógica OR si hay múltiples para el mismo campo
        foreach ($this->filters as $field => $entries) {
            $fConfig = $this->availableFilters[$field] ?? null;
            $type    = $fConfig['type'] ?? 'text';

            $builder->groupStart();
            foreach ($entries as $idx => $entry) {
                $value      = $entry['value'];
                $filterType = $entry['type'] ?? 'exact';

                $method     = ($idx === 0) ? 'where' : 'orWhere';
                $likeMethod = ($idx === 0) ? 'like' : 'orLike';

                if ($type === 'boolean') {
                    $targetOn  = $fConfig['onValue'] ?? 1;
                    $targetOff = $fConfig['offValue'] ?? 0;

                    if ($value == 0) {
                        $builder->$method($this->model->table . '.' . $field, $targetOff)
                            ->orWhere($this->model->table . '.' . $field, null);
                    } else {
                        $builder->$method($this->model->table . '.' . $field, $targetOn);
                    }
                } elseif ($filterType === 'partial') {
                    $builder->$likeMethod($this->model->table . '.' . $field, $value);
                } else {
                    $builder->$method($this->model->table . '.' . $field, $value);
                }
            }
            $builder->groupEnd();
        }

        // Aplicar Filtros de Fecha con lógica OR si hay múltiples para el mismo campo
        foreach ($this->dateFilters as $field => $entries) {
            $builder->groupStart();
            foreach ($entries as $idx => $f) {
                if ($idx > 0)
                    $builder->orGroupStart();
                else
                    $builder->groupStart();

                if (!empty($f['start'])) {
                    $builder->where($this->model->table . '.' . $field . ' >=', $f['start']);
                }
                if (!empty($f['end'])) {
                    $builder->where($this->model->table . '.' . $field . ' <=', $f['end']);
                }
                $builder->groupEnd();
            }
            $builder->groupEnd();
        }

        // Aplicar Filtros Numéricos con lógica OR si hay múltiples para el mismo campo
        foreach ($this->numericFilters as $field => $entries) {
            $builder->groupStart();
            foreach ($entries as $idx => $f) {
                if ($idx > 0)
                    $builder->orGroupStart();
                else
                    $builder->groupStart();

                if (isset($f['min']) && $f['min'] !== '') {
                    $builder->where($this->model->table . '.' . $field . ' >=', $f['min']);
                }
                if (isset($f['max']) && $f['max'] !== '') {
                    $builder->where($this->model->table . '.' . $field . ' <=', $f['max']);
                }
                $builder->groupEnd();
            }
            $builder->groupEnd();
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
                        $row[$keyName] = $time ? date('Y-m', $time) . ' (' . $this->getMonthName(date('n', $time)) . ')' : lang('Ragnos.Ragnos_no_date');
                    } elseif ($mode === 'date_year') {
                        $time          = strtotime($rawValue);
                        $row[$keyName] = $time ? date('Y', $time) : lang('Ragnos.Ragnos_no_date');
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
        return lang("Ragnos.Ragnos_month_$num");
    }

    private function buildFilterDescription(): string
    {
        $parts = [];
        foreach ($this->dateFilters as $field => $entries) {
            $label    = $this->getFieldLabel($field);
            $subParts = [];
            foreach ($entries as $f) {
                if (!empty($f['start']) && !empty($f['end']))
                    $subParts[] = "{$f['start']} " . lang('Ragnos.Ragnos_date_range_to') . " {$f['end']}";
                elseif (!empty($f['start']))
                    $subParts[] = lang('Ragnos.Ragnos_from_lowercase') . " {$f['start']}";
                elseif (!empty($f['end']))
                    $subParts[] = lang('Ragnos.Ragnos_to_lowercase') . " {$f['end']}";
            }
            if (!empty($subParts))
                $parts[] = "$label: (" . implode(' ' . lang('Ragnos.Ragnos_or_conjunction') . ' ', $subParts) . ")";
        }
        foreach ($this->numericFilters as $field => $entries) {
            $label    = $this->getFieldLabel($field);
            $subParts = [];
            foreach ($entries as $f) {
                if (isset($f['min']) && $f['min'] !== '' && isset($f['max']) && $f['max'] !== '')
                    $subParts[] = "{$f['min']} - {$f['max']}";
                elseif (isset($f['min']) && $f['min'] !== '')
                    $subParts[] = lang('Ragnos.Ragnos_min_lowercase') . ": {$f['min']}";
                elseif (isset($f['max']) && $f['max'] !== '')
                    $subParts[] = lang('Ragnos.Ragnos_max_lowercase') . ": {$f['max']}";
            }
            if (!empty($subParts))
                $parts[] = "$label: (" . implode(' ' . lang('Ragnos.Ragnos_or_conjunction') . ' ', $subParts) . ")";
        }
        foreach ($this->filters as $k => $entries) {
            $label    = $this->getFieldLabel($k);
            $subParts = [];

            foreach ($entries as $idx => $entry) {
                $v          = $entry['value'];
                $filterType = $entry['type'] ?? 'exact';
                $disp       = $v;

                // Verificar si es un filtro especial (Boolean o Select)
                if (isset($this->availableFilters[$k])) {
                    $fConfig = $this->availableFilters[$k];
                    $type    = $fConfig['type'] ?? 'text';

                    if ($type === 'boolean') {
                        $disp = ($v == 1 || $v === '1' || $v === true) ? lang('Ragnos.Ragnos_yes') : lang('Ragnos.Ragnos_no');
                    } elseif ($type === 'select' && !empty($fConfig['options'])) {
                        $disp = $fConfig['options'][$v] ?? $v;
                    } elseif (isset($fConfig['search_controller'])) {
                        // Usar el display_text almacenado si existe
                        if (!empty($entry['display_text'])) {
                            $disp = $entry['display_text'];
                        }
                    }
                }

                $indicator  = ($filterType === 'partial') ? ' [' . lang('Ragnos.Ragnos_partial_match') . ']' : ' [=]';
                $subParts[] = "{$disp}{$indicator}";
            }

            if (!empty($subParts))
                $parts[] = "$label: (" . implode(' ' . lang('Ragnos.Ragnos_or_conjunction') . ' ', $subParts) . ")";
        }
        return empty($parts) ? lang('Ragnos.Ragnos_all_records') : implode('  •  ', $parts);
    }
}

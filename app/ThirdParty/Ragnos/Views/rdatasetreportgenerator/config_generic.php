<?= $this->extend('template/template_lte') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center animate__animated animate__fadeIn">
    <div class="col-12 col-xl-11">

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Header Premium -->
            <div class="card-header bg-white py-2 px-4 border-bottom">
                <div class="d-flex align-items-center w-100">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 d-none d-sm-block">
                        <i class="bi bi-file-earmark-bar-graph-fill text-primary fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.15rem; line-height: 1.2;">
                            <?= esc($title) ?>
                        </h5>
                        <div class="text-muted small"><?= lang('Ragnos.Ragnos_report_config_help') ?></div>
                    </div>
                    <div>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="bi bi-arrow-left me-1"></i>
                            <?= lang('Ragnos.Ragnos_back') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <form action="<?= esc($action) ?>" method="POST" id="reportConfigForm">
                    <?= csrf_field() ?>

                    <div class="row g-0">
                        <!-- COLUMNA IZQUIERDA: Área de Filtros (Con fondo sutil) -->
                        <div class="col-lg-8 border-end bg-light bg-opacity-10">
                            <div class="p-3 p-lg-4">

                                <!-- Barra de herramientas de filtros -->
                                <div class="bg-white p-3 rounded-3 shadow-sm border mb-3">
                                    <label
                                        class="form-label small fw-bold text-uppercase text-muted mb-2 tracking-wide">
                                        <i class="bi bi-plus-circle me-1"></i> <?= lang('Ragnos.Ragnos_add_filter') ?>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                                            <i class="bi bi-funnel"></i>
                                        </span>
                                        <select id="filterSelector"
                                            class="form-select border-start-0 border-end-0 bg-light fw-medium py-2"
                                            style="box-shadow: none;">
                                            <option value=""><?= lang('Ragnos.Ragnos_select_filter_option') ?></option>
                                            <?php foreach ($filters as $field => $config): ?>
                                                <option value="<?= esc($field) ?>" data-type="<?= esc($config['type']) ?>">
                                                    <?= esc($config['label']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-primary px-4 fw-bold" id="btnAddFilter">
                                            <?= lang('Ragnos.Ragnos_add_btn') ?>
                                        </button>
                                    </div>
                                </div>

                                <!-- Contenedor de Filtros Activos -->
                                <div class="d-flex align-items-center mb-3">
                                    <h6 class="fw-bold text-dark mb-0">
                                        <i class="bi bi-list-check me-2"></i><?= lang('Ragnos.Ragnos_filters') ?>
                                    </h6>
                                    <span class="badge bg-light text-dark border ms-2 rounded-pill px-2 py-1 small"
                                        id="filterCountBadge" style="display:none">0</span>
                                </div>

                                <div id="activeFiltersContainer" class="vstack gap-3 pb-4">
                                    <!-- Empty State Moderno -->
                                    <div id="noFiltersMessage" class="text-center py-5">
                                        <div class="mb-3">
                                            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle text-muted"
                                                style="width: 80px; height: 80px;">
                                                <i class="bi bi-filter opacity-25" style="font-size: 2.5rem;"></i>
                                            </div>
                                        </div>
                                        <h6 class="text-muted fw-bold"><?= lang('Ragnos.Ragnos_no_filters_active') ?>
                                        </h6>
                                        <p class="text-muted small mb-4">
                                            <?= lang('Ragnos.Ragnos_no_filters_active_help') ?>
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- COLUMNA DERECHA: Configuración Lateral -->
                        <div class="col-lg-4 bg-light bg-opacity-25 border-start">
                            <div class="p-3 p-lg-4 h-100 sticky-sidebar">
                                <h6
                                    class="fw-bold text-dark mb-3 pb-2 border-bottom border-2 w-100 d-flex align-items-center">
                                    <i class="bi bi-sliders me-2"></i>
                                    <?= lang('Ragnos.Ragnos_grouping') ?>
                                    <i class="bi bi-info-circle text-muted ms-auto fs-6" data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="<?= lang('Ragnos.Ragnos_grouping_hierarchy_help') ?>"></i>
                                </h6>

                                <div class="vstack gap-2">
                                    <?php for ($i = 1; $i <= 3; $i++):
                                        $currentVal = "";
                                        if (isset($currentGroupings[$i - 1])) {
                                            $g          = $currentGroupings[$i - 1];
                                            $currentVal = "{$g['mode']}::{$g['field']}";
                                        }
                                        ?>
                                        <div class="position-relative">
                                            <label class="form-label small fw-bold text-muted text-uppercase mb-0"
                                                style="font-size: 0.65rem;">
                                                <?= lang('Ragnos.Ragnos_level') ?>     <?= $i ?>
                                            </label>
                                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                                <span class="input-group-text bg-white border-end-0 ps-3 text-primary">
                                                    <i class="bi bi-layers<?= $i > 1 ? '-half' : '-fill' ?>"></i>
                                                </span>
                                                <select name="grouping_<?= $i ?>"
                                                    class="form-select border-start-0 border-light-subtle py-2 grouping-select"
                                                    data-level="<?= $i ?>" style="font-size: 0.95rem;">
                                                    <option value=""><?= lang('Ragnos.Ragnos_none_option') ?></option>
                                                    <?php foreach ($groupingOpts as $opt): ?>
                                                        <option value="<?= esc($opt['value']) ?>" <?= $currentVal === $opt['value'] ? 'selected' : '' ?>>
                                                            <?= esc($opt['label']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php if ($i < 3): ?>
                                                <div class="position-absolute start-0 ms-4 h-100 top-100 border-start border-dashed border-secondary opacity-25"
                                                    style="height: 10px !important;"></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>

                                <!-- Resumen dinámico de filtros (Punto 4) -->
                                <div id="filterSummarySidebar" class="mt-4 pt-3 border-top" style="display:none;">
                                    <h6 class="fw-bold text-dark mb-2 small text-uppercase tracking-wider">
                                        <i class="bi bi-funnel-fill me-2 text-primary"></i>Resumen de Filtros
                                    </h6>
                                    <div id="summaryContent" class="vstack gap-2">
                                        <!-- Se llena dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer de acciones Pegajoso -->
                    <div class="card-footer bg-white p-3 border-top text-end sticky-bottom shadow-lg">
                        <div class="container-fluid p-0">
                            <button type="button" id="btnClearReport"
                                class="btn btn-outline-secondary me-2 rounded-pill px-4">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> <?= lang('Ragnos.Ragnos_clear') ?>
                            </button>
                            <button type="submit" id="btnSubmitReport"
                                class="btn btn-primary rounded-pill px-5 shadow fw-bold animate__animated animate__pulse animate__infinite infinite-hover">
                                <span class="btn-text">Generar Reporte <i class="bi bi-arrow-right ms-2"></i></span>
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<!-- =========================================================================== -->
<!-- PLANTILLAS DE FILTROS (Templates Modernizados)                              -->
<!-- =========================================================================== -->

<script type="text/template" id="tpl-text">
    <div class="filter-card card border-0 shadow-sm animate__animated animate__fadeInUp mb-0" data-field="{field}">
        <div class="card-body p-3 border rounded-3 position-relative bg-white">
            <div class="position-absolute top-0 start-0 bottom-0 ms-0 rounded-start-3" style="width: 4px; background-color: var(--bs-primary);"></div>
            
            <div class="d-flex align-items-center mb-2 ps-2">
                <div class="drag-handle me-2 text-muted cursor-move" style="cursor: move;">
                    <i class="bi bi-grip-vertical fs-5"></i>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold f-label px-2 py-1">
                    <i class="bi bi-fonts me-1"></i> {label}
                </span>
                <button type="button" class="btn btn-icon btn-sm text-muted ms-auto p-0 btn-remove-filter hover-danger" title="Eliminar filtro" aria-label="Eliminar filtro">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="row g-2 align-items-center ps-2">
                <div class="col-md-7">
                    <input type="text" name="filters_data[{field}][{idx}][value]" class="form-control" placeholder="<?= lang('Ragnos.Ragnos_write_search_value') ?>">
                </div>
                <div class="col-md-5">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="filters_data[{field}][{idx}][match_type]" id="m_p_{field}_{idx}" value="partial" checked>
                        <label class="btn btn-outline-light text-dark border btn-sm small" for="m_p_{field}_{idx}"><?= lang('Ragnos.Ragnos_partial_match') ?></label>
                        <input type="radio" class="btn-check" name="filters_data[{field}][{idx}][match_type]" id="m_e_{field}_{idx}" value="exact">
                        <label class="btn btn-outline-light text-dark border btn-sm small" for="m_e_{field}_{idx}"><?= lang('Ragnos.Ragnos_exact_match') ?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="tpl-select">
    <div class="filter-card card border-0 shadow-sm animate__animated animate__fadeInUp mb-0" data-field="{field}">
        <div class="card-body p-3 border rounded-3 position-relative bg-white">
            <div class="position-absolute top-0 start-0 bottom-0 ms-0 rounded-start-3" style="width: 4px; background-color: var(--bs-success);"></div>

            <div class="d-flex align-items-center mb-2 ps-2">
                <div class="drag-handle me-2 text-muted cursor-move" style="cursor: move;">
                    <i class="bi bi-grip-vertical fs-5"></i>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success fw-bold f-label px-2 py-1">
                    <i class="bi bi-list-nested me-1"></i> {label}
                </span>
                <button type="button" class="btn btn-icon btn-sm text-muted ms-auto p-0 btn-remove-filter hover-danger" aria-label="Eliminar filtro">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="ps-2">
                <select name="filters_data[{field}][{idx}][value]" class="form-select">
                    <option value="">- Seleccione una opción -</option>
                    {options}
                </select>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="tpl-boolean">
    <div class="filter-card card border-0 shadow-sm animate__animated animate__fadeInUp mb-0" data-field="{field}">
        <div class="card-body p-3 border rounded-3 position-relative bg-white">
            <div class="position-absolute top-0 start-0 bottom-0 ms-0 rounded-start-3" style="width: 4px; background-color: var(--bs-info);"></div>

            <div class="d-flex align-items-center mb-2 ps-2">
                <div class="drag-handle me-2 text-muted cursor-move" style="cursor: move;">
                    <i class="bi bi-grip-vertical fs-5"></i>
                </div>
                <span class="badge bg-info bg-opacity-10 text-info fw-bold f-label px-2 py-1">
                    <i class="bi bi-toggle-on me-1"></i> {label}
                </span>
                <button type="button" class="btn btn-icon btn-sm text-muted ms-auto p-0 btn-remove-filter hover-danger" aria-label="Eliminar filtro">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="ps-2">
                <select name="filters_data[{field}][{idx}][value]" class="form-select">
                    <option value="1">✅ <?= lang('Ragnos.Ragnos_yes_active') ?></option>
                    <option value="0">❌ <?= lang('Ragnos.Ragnos_no_inactive') ?></option>
                </select>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="tpl-date_range">
    <div class="filter-card card border-0 shadow-sm animate__animated animate__fadeInUp mb-0" data-field="{field}">
        <div class="card-body p-3 border rounded-3 position-relative bg-white">
            <div class="position-absolute top-0 start-0 bottom-0 ms-0 rounded-start-3" style="width: 4px; background-color: var(--bs-warning);"></div>

            <div class="d-flex align-items-center mb-2 ps-2">
                <div class="drag-handle me-2 text-muted cursor-move" style="cursor: move;">
                    <i class="bi bi-grip-vertical fs-5"></i>
                </div>
                <span class="badge bg-warning bg-opacity-10 text-warning-emphasis fw-bold f-label px-2 py-1">
                    <i class="bi bi-calendar-range me-1"></i> {label}
                </span>
                <button type="button" class="btn btn-icon btn-sm text-muted ms-auto p-0 btn-remove-filter hover-danger" aria-label="Eliminar filtro">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="row g-2 ps-2">
                <div class="col-6">
                    <div class="form-floating">
                        <input type="date" name="filters_data[{field}][{idx}][start]" class="form-control" id="start_{field}_{idx}" placeholder="<?= lang('Ragnos.Ragnos_from') ?>">
                        <label for="start_{field}_{idx}" class="text-muted small"><?= lang('Ragnos.Ragnos_from') ?></label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-floating">
                        <input type="date" name="filters_data[{field}][{idx}][end]" class="form-control" id="end_{field}_{idx}" placeholder="<?= lang('Ragnos.Ragnos_to') ?>">
                        <label for="end_{field}_{idx}" class="text-muted small"><?= lang('Ragnos.Ragnos_to') ?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="tpl-numeric_range">
    <div class="filter-card card border-0 shadow-sm animate__animated animate__fadeInUp mb-0" data-field="{field}">
        <div class="card-body p-3 border rounded-3 position-relative bg-white">
            <div class="position-absolute top-0 start-0 bottom-0 ms-0 rounded-start-3" style="width: 4px; background-color: var(--bs-secondary);"></div>

            <div class="d-flex align-items-center mb-2 ps-2">
                <div class="drag-handle me-2 text-muted cursor-move" style="cursor: move;">
                    <i class="bi bi-grip-vertical fs-5"></i>
                </div>
                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold f-label px-2 py-1">
                    <i class="bi bi-123 me-1"></i> {label}
                </span>
                <button type="button" class="btn btn-icon btn-sm text-muted ms-auto p-0 btn-remove-filter hover-danger" aria-label="Eliminar filtro">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="row g-2 ps-2">
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted small"><?= lang('Ragnos.Ragnos_minimum') ?></span>
                        <input type="number" step="any" name="filters_data[{field}][{idx}][min]" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted small"><?= lang('Ragnos.Ragnos_maximum') ?></span>
                        <input type="number" step="any" name="filters_data[{field}][{idx}][max]" class="form-control" placeholder="inf">
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="tpl-search">
    <div class="filter-card card border-0 shadow-sm animate__animated animate__fadeInUp mb-0" data-field="{field}">
        <div class="card-body p-3 border rounded-3 position-relative bg-white">
            <div class="position-absolute top-0 start-0 bottom-0 ms-0 rounded-start-3" style="width: 4px; background-color: var(--bs-dark);"></div>

            <div class="d-flex align-items-center mb-2 ps-2">
                <div class="drag-handle me-2 text-muted cursor-move" style="cursor: move;">
                    <i class="bi bi-grip-vertical fs-5"></i>
                </div>
                <span class="badge bg-dark bg-opacity-10 text-dark fw-bold f-label px-2 py-1">
                    <i class="bi bi-search me-1"></i> {label}
                </span>
                <button type="button" class="btn btn-icon btn-sm text-muted ms-auto p-0 btn-remove-filter hover-danger" aria-label="Eliminar filtro">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="ps-2">
                 <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 ragnos-search-field" 
                           id="disp_{field}_{idx}" 
                           data-display-input="true"
                           data-target-id="val_{field}_{idx}"
                           data-target-text="txt_{field}_{idx}"
                           placeholder="<?= lang('Ragnos.Ragnos_search') ?> {label}..."
                           style="height: 38px;"
                           data-controller="{controller}">
                </div>
                <input type="hidden" name="filters_data[{field}][{idx}][value]" id="val_{field}_{idx}">
                <input type="hidden" name="filters_data[{field}][{idx}][display_text]" id="txt_{field}_{idx}">
            </div>
        </div>
    </div>
</script>

<style>
    /* Animación de resaltado para nuevos filtros */
    @keyframes filterHighlight {
        0% {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
        }

        100% {
            border-color: rgba(0, 0, 0, 0.125);
            box-shadow: none;
        }
    }

    .filter-card-new {
        animation: filterHighlight 2s ease-out;
    }

    /* Sticky Sidebar en escritorio */
    @media (min-width: 992px) {
        .sticky-sidebar {
            position: sticky;
            top: 1rem;
        }
    }

    .card-footer.sticky-bottom {
        z-index: 1020;
        backdrop-filter: blur(8px);
        background-color: rgba(255, 255, 255, 0.9) !important;
    }

    .filter-card.sortable-ghost {
        opacity: 0.4;
        background-color: var(--bs-light) !important;
    }

    .filter-error {
        border: 2px solid var(--bs-danger) !important;
        animation: headShake 0.5s;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    $(document).ready(function () {
        const filtersConfig = <?= json_encode($filters) ?>;
        let filterIndex = 0;

        // Inicializar Tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        function updNoMatchMsg() {
            var $container = $('#activeFiltersContainer');
            var count = $container.find('.filter-card').length;

            if (count > 0) {
                $('#noFiltersMessage').hide();
                $('#filterCountBadge').text(count).show();
                $('#filterSummarySidebar').fadeIn();

                // Actualizar resumen (Punto 4)
                let summaryHtml = '';
                $container.find('.filter-card').each(function () {
                    let label = $(this).find('.f-label').text().trim();
                    let colorClass = $(this).find('.badge').attr('class').match(/text-\w+/)[0];
                    summaryHtml += `
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check2-circle me-2 ${colorClass}"></i>
                            <span class="text-truncate text-muted" style="max-width: 180px;">${label}</span>
                        </div>`;
                });
                $('#summaryContent').html(summaryHtml);
            } else {
                $('#noFiltersMessage').show();
                $('#filterCountBadge').hide();
                $('#filterSummarySidebar').fadeOut();
            }
        }

        // Inicializar Drag & Drop (Punto 3)
        const filterList = document.getElementById('activeFiltersContainer');
        if (filterList) {
            new Sortable(filterList, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                onEnd: function () {
                    updNoMatchMsg(); // Refrescar el resumen al mover
                }
            });
        }

        function addFilterUI(field, initialData = null) {
            if (!field) return;

            const config = filtersConfig[field];
            let tplId = '#tpl-text';

            if (config.search_controller) tplId = '#tpl-search';
            else if (config.type === 'select') tplId = '#tpl-select';
            else if (config.type === 'boolean') tplId = '#tpl-boolean';
            else if (config.type === 'date_range') tplId = '#tpl-date_range';
            else if (config.type === 'numeric_range') tplId = '#tpl-numeric_range';

            let html = $(tplId).html();
            html = html.replace(/{field}/g, field)
                .replace(/{idx}/g, filterIndex++)
                .replace(/{label}/g, config.label);

            // Inyectar opciones si es select
            if (config.type === 'select' && config.options) {
                let optsHtml = '';
                $.each(config.options, function (v, l) {
                    optsHtml += `<option value="${v}">${l}</option>`;
                });
                html = html.replace(/{options}/g, optsHtml);
            }

            // Inyectar controlador si es búsqueda
            if (config.search_controller) {
                html = html.replace(/{controller}/g, config.search_controller);
            }

            const $filter = $(html);
            $filter.addClass('filter-card-new'); // Añadir animación de resaltado
            $('#activeFiltersContainer').append($filter);

            // Auto-scroll al nuevo filtro si es necesario
            if (!initialData) {
                $('html, body').animate({
                    scrollTop: $filter.offset().top - 200
                }, 400);
            }

            // Repoblar valores si hay data inicial
            if (initialData) {
                $.each(initialData, function (key, val) {
                    const $input = $filter.find(`[name*="[${key}]"]`);
                    if ($input.length) {
                        if ($input.is(':radio')) {
                            $filter.find(`[name*="[${key}]"][value="${val}"]`).prop('checked', true);
                        } else {
                            $input.val(val);
                        }
                    }

                    // Caso especial para el input visible de búsqueda
                    if (config.search_controller && key === 'display_text') {
                        $filter.find('.ragnos-search-field').val(val);
                    }
                });
            }

            updNoMatchMsg();

            // Inicializar Autocomplete usando el plugin jQuery .RagnosSearch()
            if (config.search_controller) {
                const $searchel = $filter.find('.ragnos-search-field');

                if ($.fn.RagnosSearch) {
                    $searchel.RagnosSearch({
                        controller: config.search_controller,
                        callback: function (e) {
                            let datos = e.data('searchdata');
                            if (datos) {
                                if (datos.y_id) {
                                    $filter.find('input[name*="[value]"]').val(datos.y_id);
                                }
                                let visibleText = datos.y_text || $searchel.val();
                                if (visibleText) {
                                    $filter.find('input[name*="[display_text]"]').val(visibleText);
                                }
                            } else {
                                if ($searchel.val() === '') {
                                    $filter.find('input[name*="[value]"]').val('');
                                    $filter.find('input[name*="[display_text]"]').val('');
                                }
                            }
                        }
                    });
                } else {
                    initSearch($searchel);
                }
            }

            // Auto-agrupamiento para campos de búsqueda (solo cuando el usuario añade el filtro manualmente)
            if (config.search_controller && !initialData) {
                const $groupSelects = $('.grouping-select');
                let alreadyGrouped = false;

                $groupSelects.each(function () {
                    const val = $(this).val();
                    if (val && val.indexOf('::' + field) !== -1) alreadyGrouped = true;
                });

                if (!alreadyGrouped) {
                    const $emptySelect = $groupSelects.filter(function () { return $(this).val() === ""; }).first();
                    if ($emptySelect.length) {
                        // Buscar el valor exacto de la opción que termina en ::field
                        const targetVal = $emptySelect.find(`option[value$="::${field}"]`).first().val();
                        if (targetVal) {
                            $emptySelect.val(targetVal).trigger('change');
                        }
                    }
                }
            }
        }

        $('#btnAddFilter').on('click', function () {
            const field = $('#filterSelector').val();
            addFilterUI(field);
        });

        // Repoblar filtros existentes (Estado del Reporte)
        <?php if (!empty($currentFilters)): ?>
            <?php foreach ($currentFilters as $field => $entries): ?>
                <?php foreach ($entries as $entry): ?>
                    addFilterUI('<?= esc($field) ?>', <?= json_encode($entry) ?>);
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($currentDateFil)): ?>
            <?php foreach ($currentDateFil as $field => $entries): ?>
                <?php foreach ($entries as $entry): ?>
                    addFilterUI('<?= esc($field) ?>', <?= json_encode($entry) ?>);
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($currentNumFil)): ?>
            <?php foreach ($currentNumFil as $field => $entries): ?>
                <?php foreach ($entries as $entry): ?>
                    addFilterUI('<?= esc($field) ?>', <?= json_encode($entry) ?>);
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>


        $(document).on('click', '.btn-remove-filter', function () {
            // Animacion de salida
            $(this).closest('.filter-card').addClass('animate__fadeOutRight');
            var card = $(this).closest('.filter-card');
            setTimeout(function () {
                card.remove();
                updNoMatchMsg();
            }, 300);
        });

        function initSearch($el) {
            // Fallback básico (legacy)
            const controller = $el.data('controller');
            const $hiddenVal = $el.closest('.filter-card').find('input[type="hidden"]').first();
            const $hiddenTxt = $el.closest('.filter-card').find('input[type="hidden"]').last();

            $el.autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "<?= base_url() ?>" + controller + "/search",
                        dataType: "json",
                        data: { term: request.term },
                        success: function (data) { response(data); }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    $hiddenVal.val(ui.item.id);
                    $hiddenTxt.val(ui.item.value);
                }
            });
        }

        // Lógica para deshabilitar opciones ya seleccionadas en agrupamiento
        $('.grouping-select').on('change', function () {
            const selectedValues = $('.grouping-select').map(function () { return $(this).val(); }).get();
            $('.grouping-select').each(function () {
                const $select = $(this);
                const currentVal = $select.val();
                $select.find('option').each(function () {
                    if ($(this).val() !== "" && selectedValues.includes($(this).val()) && $(this).val() !== currentVal) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });
        });

        // Botón de limpiar dinámico (AJAX con SweetAlert2)
        $('#btnClearReport').on('click', function () {
            Swal.fire({
                title: '<?= lang('Ragnos.Ragnos_wait') ?>',
                text: '<?= lang('Ragnos.Ragnos_clear_confirm') ?>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<?= lang('Ragnos.Ragnos_yes') ?>',
                cancelButtonText: '<?= lang('Ragnos.Ragnos_cancel') ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    const $btn = $(this);
                    const originalHtml = $btn.html();

                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Limpiando...');

                    $.ajax({
                        url: window.location.href,
                        method: 'POST',
                        data: {
                            clear_ragnos_session: 1,
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                        },
                        success: function (response) {
                            // Limpiar UI sin recargar
                            $('#activeFiltersContainer .filter-card').remove();
                            $('.grouping-select').val('').trigger('change');
                            updNoMatchMsg();

                            // Restaurar botón
                            $btn.prop('disabled', false).html(originalHtml);

                            Swal.fire({
                                icon: 'success',
                                title: '<?= lang('Ragnos.Ragnos_accept') ?>',
                                text: 'Filtros eliminados correctamente',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function () {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: '<?= lang('Ragnos.Ragnos_server_error') ?>'
                            });
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });

        // Validación y Estado de carga en el envío del formulario (Punto 1)
        $('#reportConfigForm').on('submit', function (e) {
            let hasError = false;
            let firstError = null;

            $('.filter-card').removeClass('filter-error');

            $('.filter-card').each(function () {
                let $card = $(this);
                let isEmpty = true;

                // Revisar todos los inputs relevantes en la tarjeta
                $card.find('input[type="text"], input[type="number"], input[type="date"], select').each(function () {
                    // Ignorar match_type radios
                    if ($(this).attr('name') && $(this).attr('name').indexOf('[match_type]') !== -1) return;
                    if ($(this).val() !== '') isEmpty = false;
                });

                if (isEmpty) {
                    $card.addClass('filter-error');
                    hasError = true;
                    if (!firstError) firstError = $card;
                }
            });

            if (hasError) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Filtros incompletos',
                    text: 'Por favor, complete los valores de los filtros añadidos o elimine los que no necesite.',
                    confirmButtonText: 'Entendido'
                });
                if (firstError) {
                    $('html, body').animate({
                        scrollTop: firstError.offset().top - 150
                    }, 500);
                }
                return false;
            }

            const $btn = $('#btnSubmitReport');
            const $btnText = $btn.find('.btn-text');

            $btn.prop('disabled', true).removeClass('animate__pulse animate__infinite');
            $btnText.html('<span class="spinner-border spinner-border-sm me-2"></span> Generando Reporte...');
        });
    });
</script>

<?= $this->endSection() ?>
<?= $this->extend('template/template_lte') ?>

<?= $this->section('content') ?>

<div class="container-fluid pt-2 pb-3">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border mt-1">
                <div class="card-header bg-white py-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 fw-bold text-dark fs-6"><i
                                class="bi bi-sliders me-2 text-primary"></i><?= esc($title) ?></h5>
                        <span class="text-muted ms-2 small">| Parámetros del reporte</span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <form action="<?= esc($action) ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row g-3">
                            <!-- Columna de Filtros -->
                            <div class="col-md-7 border-end">
                                <h6 class="text-secondary fw-bold mb-2 small">
                                    <i class="bi bi-funnel me-1"></i> Filtros
                                </h6>

                                <?php if (empty($filters)): ?>
                                    <div class="alert alert-light border text-center text-muted small">
                                        <i class="bi bi-info-circle me-1"></i> No se detectaron filtros aplicables para este
                                        dataset.
                                    </div>
                                <?php else: ?>
                                    <div class="vstack gap-3">
                                        <?php foreach ($filters as $field => $config): ?>

                                            <!-- Tipo: Rango de Fechas -->
                                            <?php if ($config['type'] === 'date_range'): ?>
                                                <div class="card border-0 bg-light shadow-sm">
                                                    <div
                                                        class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-2">
                                                        <label
                                                            class="fw-bold small text-dark mb-0"><?= esc($config['label']) ?></label>
                                                        <a href="javascript:void(0)" id="btn_clear_<?= $field ?>"
                                                            onclick="$('input[name=filter_<?= $field ?>_start]').val(''); $('input[name=filter_<?= $field ?>_end]').val('').trigger('change');"
                                                            class="badge bg-danger text-decoration-none" style="display:none;"
                                                            title="Limpiar rango de fechas">
                                                            <i class="bi bi-x"></i> Borrar
                                                        </a>
                                                        <script>
                                                            $(function () {
                                                                function checkDateFilters_<?= $field ?>() {
                                                                    let start = $('input[name=filter_<?= $field ?>_start]').val();
                                                                    let end = $('input[name=filter_<?= $field ?>_end]').val();
                                                                    if (start || end) {
                                                                        $('#btn_clear_<?= $field ?>').show();
                                                                    } else {
                                                                        $('#btn_clear_<?= $field ?>').hide();
                                                                    }
                                                                }
                                                                $('input[name=filter_<?= $field ?>_start], input[name=filter_<?= $field ?>_end]').on('change input', function () {
                                                                    checkDateFilters_<?= $field ?>();
                                                                });
                                                                checkDateFilters_<?= $field ?>(); // Check on load
                                                                // Re-check on pageshow to handle browser Back button history restoration
                                                                $(window).on('pageshow', function () {
                                                                    setTimeout(checkDateFilters_<?= $field ?>, 200);
                                                                });
                                                            });
                                                        </script>
                                                    </div>
                                                    <div class="card-body pt-0 pb-2">
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <div class="form-floating">
                                                                    <input type="date" name="filter_<?= $field ?>_start"
                                                                        class="form-control form-control-sm"
                                                                        id="floatStart_<?= $field ?>" placeholder="Desde">
                                                                    <label for="floatStart_<?= $field ?>">Desde</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-floating">
                                                                    <input type="date" name="filter_<?= $field ?>_end"
                                                                        class="form-control form-control-sm"
                                                                        id="floatEnd_<?= $field ?>" placeholder="Hasta">
                                                                    <label for="floatEnd_<?= $field ?>">Hasta</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tipo: Rango Numérico (Money) -->
                                            <?php elseif ($config['type'] === 'numeric_range'): ?>
                                                <div class="card border-0 bg-light shadow-sm">
                                                    <div
                                                        class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-2">
                                                        <label class="fw-bold small text-dark mb-0">
                                                            <?= esc($config['label']) ?>
                                                        </label>
                                                        <a href="javascript:void(0)" id="btn_clear_<?= $field ?>"
                                                            onclick="$('input[name=filter_<?= $field ?>_min]').val(''); $('input[name=filter_<?= $field ?>_max]').val('').trigger('change');"
                                                            class="badge bg-danger text-decoration-none" style="display:none;"
                                                            title="Limpiar rango">
                                                            <i class="bi bi-x"></i> Borrar
                                                        </a>
                                                        <script>
                                                            $(function () {
                                                                function checkNumFilters_<?= $field ?>() {
                                                                    let min = $('input[name=filter_<?= $field ?>_min]').val();
                                                                    let max = $('input[name=filter_<?= $field ?>_max]').val();
                                                                    if (min || max) {
                                                                        $('#btn_clear_<?= $field ?>').show();
                                                                    } else {
                                                                        $('#btn_clear_<?= $field ?>').hide();
                                                                    }
                                                                }
                                                                $('input[name=filter_<?= $field ?>_min], input[name=filter_<?= $field ?>_max]').on('change input', function () {
                                                                    checkNumFilters_<?= $field ?>();
                                                                });
                                                                checkNumFilters_<?= $field ?>();
                                                                $(window).on('pageshow', function () {
                                                                    setTimeout(checkNumFilters_<?= $field ?>, 200);
                                                                });
                                                            });
                                                        </script>
                                                    </div>
                                                    <div class="card-body pt-0 pb-2">
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <div class="form-floating">
                                                                    <input type="number" step="any" name="filter_<?= $field ?>_min"
                                                                        class="form-control form-control-sm"
                                                                        id="floatMin_<?= $field ?>" placeholder="Min">
                                                                    <label for="floatMin_<?= $field ?>">Mínimo</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-floating">
                                                                    <input type="number" step="any" name="filter_<?= $field ?>_max"
                                                                        class="form-control form-control-sm"
                                                                        id="floatMax_<?= $field ?>" placeholder="Max">
                                                                    <label for="floatMax_<?= $field ?>">Máximo</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tipo: Búsqueda Ragnos -->
                                            <?php elseif (isset($config['search_controller'])): ?>
                                                <div class="mb-1">
                                                    <label class="form-label fw-bold small text-secondary"
                                                        for="filter_<?= $field ?>_display"><?= esc($config['label']) ?></label>
                                                    <div class="input-group">
                                                        <input type="text" id="filter_<?= $field ?>_display"
                                                            name="filter_<?= $field ?>_display_text" class="form-control"
                                                            placeholder="Buscar <?= strtolower($config['label']) ?>...">
                                                    </div>
                                                    <input type="hidden" name="filter_<?= $field ?>" id="filter_<?= $field ?>">
                                                    <script>
                                                        $(function () {
                                                            // Función helper para guardar estado en el historial (Back button compliant)
                                                            // No usa sessionStorage, sino el estado de navegación de la pestaña actual
                                                            function saveState_<?= $field ?>(id, display) {
                                                                let state = history.state || {};
                                                                state['f_<?= $field ?>_id'] = id;
                                                                state['f_<?= $field ?>_disp'] = display;
                                                                history.replaceState(state, '');
                                                            }

                                                            // Restaurar estado si existe (al volver atrás)
                                                            let state = history.state;
                                                            if (state && state['f_<?= $field ?>_id']) {
                                                                $('#filter_<?= $field ?>').val(state['f_<?= $field ?>_id']);
                                                                $('#filter_<?= $field ?>_display').val(state['f_<?= $field ?>_disp']);
                                                            }

                                                            $('#filter_<?= $field ?>_display').RagnosSearch({
                                                                controller: '<?= str_replace('\\', '/', $config['search_controller']) ?>',
                                                                callback: function (e) {
                                                                    let datos = e.data('searchdata');
                                                                    if (datos && datos.y_id) {
                                                                        let id = datos.y_id;
                                                                        let disp = $('#filter_<?= $field ?>_display').val();

                                                                        $('#filter_<?= $field ?>').val(id);
                                                                        saveState_<?= $field ?>(id, disp);
                                                                    } else {
                                                                        $('#filter_<?= $field ?>').val('');
                                                                        saveState_<?= $field ?>('', '');
                                                                    }
                                                                }
                                                            });

                                                            // Limpieza segura en el envío del formulario
                                                            $('form').on('submit', function () {
                                                                let displayVal = $('#filter_<?= $field ?>_display').val();
                                                                if (!displayVal || displayVal.trim() === '') {
                                                                    $('#filter_<?= $field ?>').val('');
                                                                    saveState_<?= $field ?>('', '');
                                                                }
                                                            });
                                                        });
                                                    </script>
                                                </div>

                                                <!-- Tipo: Select (Enum/Opciones) -->
                                            <?php elseif ($config['type'] === 'select'): ?>
                                                <div class="form-floating mb-1">
                                                    <select name="filter_<?= $field ?>" class="form-select" id="sel_<?= $field ?>">
                                                        <option value="">- Todos -</option>
                                                        <?php foreach ($config['options'] as $val => $lbl): ?>
                                                            <option value="<?= esc($val) ?>">
                                                                <?= esc($lbl) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="sel_<?= $field ?>">
                                                        <?= esc($config['label']) ?>
                                                    </label>
                                                </div>

                                                <!-- Tipo: Boolean/Switch -->
                                            <?php elseif ($config['type'] === 'boolean'): ?>
                                                <div class="form-floating mb-1">
                                                    <select name="filter_<?= $field ?>" class="form-select"
                                                        id="sel_bool_<?= $field ?>">
                                                        <option value="">- Todos -</option>
                                                        <option value="1">Sí / Activo</option>
                                                        <option value="0">No / Inactivo</option>
                                                    </select>
                                                    <label for="sel_bool_<?= $field ?>">
                                                        <?= esc($config['label']) ?>
                                                    </label>
                                                </div>

                                                <!-- Tipo: Estándar (Texto/Numerico) -->
                                            <?php else: ?>
                                                <div class="form-floating mb-1">
                                                    <input type="text" name="filter_<?= $field ?>" class="form-control"
                                                        id="floatStd_<?= $field ?>"
                                                        placeholder="Filtrar por <?= strtolower($config['label']) ?>...">
                                                    <label for="floatStd_<?= $field ?>"><?= esc($config['label']) ?></label>
                                                </div>
                                            <?php endif; ?>

                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Columna de Agrupamiento -->
                            <div class="col-md-5 ps-md-4">
                                <h6 class="text-secondary fw-bold mb-2 small">
                                    <i class="bi bi-layers me-1"></i> Agrupamiento
                                </h6>

                                <?php if (empty($groupingOpts)): ?>
                                    <div class="list-group-item bg-white text-muted small fst-italic py-2 border rounded">
                                        Sin opciones disponibles.
                                    </div>
                                <?php else: ?>
                                    <div class="bg-light p-3 rounded border shadow-sm">
                                        <?php for ($i = 1; $i <= 3; $i++): ?>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-secondary mb-1">
                                                    <span class="badge bg-white text-dark border me-1"><?= $i ?></span> Nivel de
                                                    Agrupación
                                                </label>
                                                <select name="grouping_<?= $i ?>" class="form-select form-select-sm">
                                                    <option value="">- (Ninguno) -</option>
                                                    <?php foreach ($groupingOpts as $opt): ?>
                                                        <option value="<?= esc($opt['value']) ?>"><?= esc($opt['label']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted fst-italic mt-2 d-block" style="font-size: 0.75rem;">
                                        <i class="bi bi-info-circle me-1"></i> Seleccione el orden de jerarquía para los
                                        grupos.
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-start gap-2">
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm fw-bold">
                                <i class="bi bi-file-earmark-text me-1"></i> Generar Reporte
                            </button>
                            <a href="javascript:history.back()"
                                class="btn btn-light btn-sm border text-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
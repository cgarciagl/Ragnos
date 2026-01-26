<?= $this->extend('template/template_lte') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i><?= esc($title) ?></h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= esc($action) ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <!-- Columna de Filtros -->
                            <div class="col-md-7 border-end">
                                <h6 class="text-uppercase text-secondary fw-bold mb-3 small">
                                    <i class="fas fa-filter me-1"></i> Filtros Disponibles
                                </h6>

                                <?php if (empty($filters)): ?>
                                    <p class="text-muted small fst-italic">No se detectaron filtros aplicables para este
                                        dataset.</p>
                                <?php else: ?>
                                    <?php foreach ($filters as $field => $config): ?>

                                        <!-- Tipo: Rango de Fechas -->
                                        <?php if ($config['type'] === 'date_range'): ?>
                                            <div class="mb-3 p-2 bg-light rounded border-start border-3 border-info">
                                                <label
                                                    class="form-label fw-bold small text-dark mb-2"><?= esc($config['label']) ?></label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white">Desde</span>
                                                    <input type="date" name="filter_<?= $field ?>_start" class="form-control">
                                                </div>
                                                <div class="input-group input-group-sm mt-1">
                                                    <span class="input-group-text bg-white">Hasta&nbsp;</span>
                                                    <input type="date" name="filter_<?= $field ?>_end" class="form-control">
                                                </div>
                                            </div>

                                            <!-- Tipo: Estándar (Texto/Numerico) -->
                                        <?php else: ?>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small"><?= esc($config['label']) ?></label>
                                                <input type="text" name="filter_<?= $field ?>" class="form-control form-control-sm"
                                                    placeholder="Filtrar por <?= strtolower($config['label']) ?>...">
                                            </div>
                                        <?php endif; ?>

                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Columna de Agrupamiento -->
                            <div class="col-md-5 ps-md-4">
                                <h6 class="text-uppercase text-secondary fw-bold mb-3 small">
                                    <i class="fas fa-layer-group me-1"></i> Agrupamiento
                                </h6>

                                <div class="bg-light p-3 rounded">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="grouping" id="grp_none"
                                            value="" checked>
                                        <label class="form-check-label" for="grp_none">
                                            Sin Agrupamiento
                                        </label>
                                    </div>

                                    <?php if (!empty($groupingOpts)): ?>
                                        <hr class="my-2 opacity-25">
                                        <?php foreach ($groupingOpts as $opt): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="grouping"
                                                    id="grp_<?= $opt['value'] ?>" value="<?= $opt['value'] ?>">
                                                <label class="form-check-label" for="grp_<?= $opt['value'] ?>">
                                                    <?= esc($opt['label']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted small mt-2">No hay opciones de agrupación automática.</p>
                                    <?php endif; ?>
                                </div>

                                <div class="alert alert-info mt-4 small border-0 shadow-sm">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Seleccione los filtros deseados y el modo de agrupación para generar el reporte en
                                    tiempo real.
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="javascript:history.back()" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fas fa-file-invoice me-2"></i> Generar Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
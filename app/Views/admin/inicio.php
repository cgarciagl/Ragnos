<?= $this->extend('template/template_lte') ?>

<?php
/**
 * Panel de inicio genérico.
 *
 * Se muestra cuando la demo Classicmodels no está instalada: usa solo las
 * tablas del sistema (usuarios, grupos y bitácora de auditoría).
 *
 * @var array{usuarios:int, activos:int, grupos:int, cambios7dias:int} $contadores
 * @var list<array<string, mixed>>                                   $actividad
 * @var bool                                                         $esAdministrador
 * @var string|null                                                  $aviso
 */
$acciones = [
    'INSERT' => ['Alta', 'text-bg-success'],
    'UPDATE' => ['Edición', 'text-bg-primary'],
    'DELETE' => ['Baja', 'text-bg-danger'],
];
$tarjetas = [
    ['Usuarios registrados', $contadores['usuarios'], 'bi-people-fill', '#007bff'],
    ['Usuarios activos', $contadores['activos'], 'bi-person-check-fill', '#28a745'],
    ['Grupos de usuarios', $contadores['grupos'], 'bi-diagram-3-fill', '#3f51b5'],
    ['Cambios (últimos 7 días)', $contadores['cambios7dias'], 'bi-clock-history', '#fd7e14'],
];
?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-body animate__animated animate__fadeInDown">Bienvenido a Ragnos</h3>
        <p class="text-muted small mb-0">Resumen del sistema y actividad reciente</p>
    </div>
</div>

<?php if (! empty($aviso)): ?>
    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div><?= esc($aviso) ?></div>
    </div>
<?php endif ?>

<div class="row">
    <?php foreach ($tarjetas as $i => [$titulo, $valor, $icono, $color]): ?>
        <div class="col-lg-3 col-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: <?= 0.1 * ($i + 1) ?>s;">
            <div class="custom-info-card">
                <div class="icon-container" style="background-color: <?= $color ?>;">
                    <i class="bi <?= $icono ?>"></i>
                </div>
                <div class="text-content">
                    <p><?= esc($titulo) ?></p>
                    <h3><?= number_format((int) $valor) ?></h3>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="bi bi-activity me-2"></i>Actividad reciente</h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($actividad)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Aún no hay cambios registrados en la bitácora de auditoría.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>Tabla</th>
                                    <th class="text-end">Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($actividad as $fila): ?>
                                    <?php [$etiqueta, $clase] = $acciones[$fila['action']] ?? [$fila['action'], 'text-bg-secondary']; ?>
                                    <tr>
                                        <td class="text-nowrap small"><?= esc((string) $fila['created_at']) ?></td>
                                        <td><?= esc($fila['usu_nombre'] ?? 'Sistema') ?></td>
                                        <td><span class="badge <?= $clase ?>"><?= esc($etiqueta) ?></span></td>
                                        <td><code><?= esc($fila['table_name']) ?></code></td>
                                        <td class="text-end"><?= esc((string) $fila['record_id']) ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <?php if ($esAdministrador): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="bi bi-lightning-charge me-2"></i>Accesos rápidos</h3>
                </div>
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action" href="<?= site_url('usuarios') ?>">
                        <i class="bi bi-person-circle me-2"></i>Usuarios
                    </a>
                    <a class="list-group-item list-group-item-action" href="<?= site_url('gruposdeusuarios') ?>">
                        <i class="bi bi-people me-2"></i>Grupos de usuarios
                    </a>
                    <a class="list-group-item list-group-item-action" href="<?= site_url('admin/perfil') ?>">
                        <i class="bi bi-person-badge-fill me-2"></i>Mi perfil
                    </a>
                </div>
            </div>
        <?php endif ?>

        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="bi bi-rocket-takeoff me-2"></i>Primeros pasos</h3>
            </div>
            <div class="card-body small">
                <p class="mb-2">Genera tu primer módulo CRUD a partir de una tabla:</p>
                <pre class="bg-body-tertiary rounded p-2 mb-3 text-wrap text-break"><code>php spark ragnos:make</code></pre>
                <p class="mb-2">¿Quieres ver Ragnos en acción con datos reales? Importa la demo de la tienda de modelos a escala:</p>
                <pre class="bg-body-tertiary rounded p-2 mb-3 text-wrap text-break"><code>mysql -u root -p ragnos &lt; sampledatabase/demo_classicmodels.sql</code></pre>
                <a href="https://ragnos.build/docs/" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-book me-1"></i>Documentación
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

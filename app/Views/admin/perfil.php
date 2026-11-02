<?= $this->extend('template/template_lte') ?>

<?php
/**
 * Perfil del usuario con sesión iniciada.
 *
 * @var array<string, mixed>       $usuario      usu_id, usu_login, usu_nombre, usu_activo, usu_avatar, gru_nombre
 * @var list<array<string, mixed>> $actividad    Últimos movimientos del usuario en gen_audit_logs
 * @var array{total:int, mes:int, ultimo:?string} $estadisticas
 */
$avatar = ! empty($usuario['usu_avatar']) && is_file(FCPATH . $usuario['usu_avatar'])
    ? base_url($usuario['usu_avatar'])
    : base_url('img/avatar.svg');
$acciones = [
    'INSERT' => ['Alta', 'text-bg-success'],
    'UPDATE' => ['Edición', 'text-bg-primary'],
    'DELETE' => ['Baja', 'text-bg-danger'],
];
?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-body">Mi perfil</h3>
        <p class="text-muted small mb-0">Datos de tu cuenta y actividad reciente</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-body text-center pt-4">
                <img src="<?= esc($avatar, 'attr') ?>" alt="Foto de perfil"
                    class="rounded-circle border border-3 shadow-sm mb-3" width="112" height="112" style="object-fit: cover;">
                <h4 class="fw-bold mb-1"><?= esc($usuario['usu_nombre'] ?? '') ?></h4>
                <span class="badge rounded-pill text-bg-light border px-3 py-2">
                    <i class="bi bi-shield-check me-1"></i><?= esc($usuario['gru_nombre'] ?? 'Sin grupo') ?>
                </span>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Usuario</span>
                    <code><?= esc(strtolower((string) ($usuario['usu_login'] ?? ''))) ?></code>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Estado</span>
                    <?php if (($usuario['usu_activo'] ?? 'N') === 'S'): ?>
                        <span class="badge text-bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Inactivo</span>
                    <?php endif ?>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Cambios registrados</span>
                    <strong><?= number_format($estadisticas['total']) ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Últimos 30 días</span>
                    <strong><?= number_format($estadisticas['mes']) ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Última actividad</span>
                    <span><?= esc($estadisticas['ultimo'] ?? '—') ?></span>
                </li>
            </ul>
            <div class="card-body">
                <button type="button" class="btn btn-outline-primary w-100" id="perfil-cambiar-password">
                    <i class="bi bi-key me-1"></i>Cambiar contraseña
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="bi bi-activity me-2"></i>Mi actividad reciente</h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($actividad)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Aún no has registrado cambios en el sistema.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
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
</div>

<script>
    onReady(() => {
        document.getElementById('perfil-cambiar-password')?.addEventListener('click', () => {
            document.getElementById('btn-cambiar-password-propio')?.click();
        });
    });
</script>

<?= $this->endSection() ?>

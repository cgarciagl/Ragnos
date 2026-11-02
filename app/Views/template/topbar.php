<?php
$ragnosConfig = config('RagnosConfig');
$auth         = service('Admin_aut');
$appTitle     = $ragnosConfig->Ragnos_all_to_uppercase ? strtoupper($ragnosConfig->Ragnos_application_title) : $ragnosConfig->Ragnos_application_title;
?>

<div class="container-fluid px-3 px-lg-4">
    <!-- Brand Logo / App Title -->
    <a class="navbar-brand d-flex align-items-center gap-2 me-lg-4 py-1" href="<?= site_url('admin') ?>">
        <img src="<?= base_url('img/favicon.webp') ?>" alt="Logo" class="brand-image rounded" style="width: 28px; height: 28px; object-fit: contain;">
        <span class="brand-text fw-bold fs-5 text-truncate" style="max-width: 240px;"><?= $appTitle ?></span>
    </a>

    <!-- Mobile Navbar Toggler -->
    <button class="navbar-toggler border-0 shadow-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#topbarNavContent" aria-controls="topbarNavContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="bi bi-list fs-3 text-body"></i>
    </button>

    <!-- Collapsible Navigation & Tools -->
    <div class="collapse navbar-collapse" id="topbarNavContent">
        <!-- Main Navigation Links -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
            <?php foreach (service('menu')->getTopMenu() as $item): ?>
                <?php if (isset($item['children'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 px-2 py-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi <?= $item['icon'] ?>"></i>
                            <span><?= $item['title'] ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-start shadow-sm border-0 mt-2">
                            <?php foreach ($item['children'] as $child): ?>
                                <?php if (isset($child['divider'])): ?>
                                    <li><hr class="dropdown-divider my-1"></li>
                                <?php else: ?>
                                    <li>
                                        <a href="<?= $child['url'] ?>" class="dropdown-item d-flex align-items-center gap-2 py-2">
                                            <i class="bi <?= $child['icon'] ?> text-primary opacity-75"></i>
                                            <span><?= $child['title'] ?></span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 px-2 py-2" href="<?= $item['url'] ?>">
                            <i class="bi <?= $item['icon'] ?>"></i>
                            <span><?= $item['title'] ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>

        <!-- Right Tools & User Profile -->
        <ul class="navbar-nav ms-auto align-items-center flex-row gap-2">
            <!-- Dark / Light Theme Toggle -->
            <li class="nav-item">
                <button class="nav-link btn btn-link border-0 p-2 text-body" id="ragnos-theme-toggle" type="button" title="Alternar tema claro/oscuro" aria-label="Alternar tema">
                    <i class="bi bi-moon-stars-fill theme-icon-active fs-5"></i>
                </button>
            </li>

            <!-- Fullscreen Toggle -->
            <li class="nav-item d-none d-sm-block">
                <a class="nav-link p-2 text-body" href="#" data-lte-toggle="fullscreen" title="Pantalla completa">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen fs-5" style="display: none;"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit fs-5" style="display: block;"></i>
                </a>
            </li>

            <?php if ($auth->isLoggedIn()): ?>
                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img class="user-image rounded-circle shadow-sm border" src="<?= base_url('img/avatar.svg') ?>" alt="Avatar" style="width: 32px; height: 32px; object-fit: cover;">
                        <span class="d-none d-md-inline fw-semibold text-body"><?= esc($auth->name()); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="min-width: 230px;">
                        <li class="px-3 py-2 border-bottom bg-body-tertiary rounded-top">
                            <div class="fw-bold text-body"><?= esc($auth->name()); ?></div>
                            <small class="text-muted text-truncate d-block">Usuario del sistema</small>
                        </li>
                        <li>
                            <a href="<?= site_url('/admin/perfil'); ?>" class="dropdown-item d-flex align-items-center gap-2 py-2">
                                <i class="bi bi-person-circle text-primary"></i>
                                <span>Mi Perfil</span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center gap-2 py-2" id="btn-cambiar-password-propio">
                                <i class="bi bi-key text-warning"></i>
                                <span>Cambiar Contraseña</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a href="<?= site_url('admin/logout'); ?>" class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger">
                                <i class="bi bi-door-closed text-danger"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script>
    // Gestión del tema Dark/Light con persistencia
    (function () {
        const themeToggleBtn = document.getElementById('ragnos-theme-toggle');
        const themeIcon = themeToggleBtn?.querySelector('i');
        const storedTheme = localStorage.getItem('ragnos-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.body.setAttribute('data-bs-theme', theme);
            localStorage.setItem('ragnos-theme', theme);
            if (themeIcon) {
                themeIcon.className = theme === 'dark' ? 'bi bi-sun-fill fs-5 text-warning' : 'bi bi-moon-stars-fill fs-5 text-body';
            }
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: theme } }));
        }

        applyTheme(storedTheme);

        themeToggleBtn?.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    })();

    // Modal para cambio de contraseña propio
    document.getElementById('btn-cambiar-password-propio')?.addEventListener('click', function () {
        Swal.fire({
            title: 'Cambiar contraseña',
            html: `
                <div class="text-start">
                    <label for="pw-actual" class="form-label small mb-1">Contraseña actual</label>
                    <input type="password" id="pw-actual" class="form-control mb-3" autocomplete="current-password">
                    <label for="pw-nueva" class="form-label small mb-1">Nueva contraseña (mínimo 8 caracteres)</label>
                    <input type="password" id="pw-nueva" class="form-control mb-3" autocomplete="new-password">
                    <label for="pw-confirma" class="form-label small mb-1">Confirmar nueva contraseña</label>
                    <input type="password" id="pw-confirma" class="form-control" autocomplete="new-password">
                </div>`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Cambiar',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            didOpen: () => document.getElementById('pw-actual')?.focus(),
            preConfirm: () => {
                const actual = document.getElementById('pw-actual').value;
                const password = document.getElementById('pw-nueva').value;
                const confirma = document.getElementById('pw-confirma').value;
                if (!actual || !password) {
                    Swal.showValidationMessage('Completa todos los campos');
                    return false;
                }
                if (password.length < 8) {
                    Swal.showValidationMessage('La nueva contraseña debe tener al menos 8 caracteres');
                    return false;
                }
                if (password !== confirma) {
                    Swal.showValidationMessage('Las contraseñas no coinciden');
                    return false;
                }
                return getObject('admin/cambiar_password', { actual: actual, password: password })
                    .then(data => {
                        if (data.result === 'error') {
                            const errors = data.errors || {};
                            throw new Error(errors.actual || errors.password || 'Error al cambiar la contraseña');
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`${error.message || error}`);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Tu contraseña ha sido cambiada correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });
</script>
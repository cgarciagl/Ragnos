<?php
use App\ThirdParty\Ragnos\Controllers\Ragnos;
$auth = service('Admin_aut');
?>

<!-- Brand Logo -->
<div class="sidebar-brand">
    <a href="<?= site_url() ?>" class="brand-link">
        <span class="brand-text font-weight-light"><?= Ragnos::config()->Ragnos_application_title; ?></span>
    </a>
</div>

<div class="sidebar-wrapper">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul id="sidebarTree" class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu"
            data-accordion="false">

            <?php if ($auth->esdegrupo('administrador')): ?>
                <li class="nav-item">
                    <a class="nav-link">
                        <i class="bi bi-people"></i>
                        <p>
                            Usuarios
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= site_url('usuarios') ?>" class="nav-link">
                                <i class="bi bi-person-circle nav-icon"></i>
                                <p>Usuarios</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('gruposdeusuarios') ?>" class="nav-link">
                                <i class="bi bi-people nav-icon"></i>
                                <p>Grupos de usuarios</p>
                            </a>
                        </li>
                    </ul>
                </li>

            <?php endif; ?>

            <?php if ($auth->isloggedin()): ?>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('proceso/showprogress') ?>">
                        <i class="bi bi-gear"></i>
                        <p>Ajuste de precios</p>
                    </a>
                </li>

                <hr>
                <li class="nav-item">
                    <a href="<?= site_url('admin/logout'); ?>" class="nav-link">
                        <i class="bi bi-door-closed"></i>
                        <p>Cerrar sesión</p>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </nav>
</div>
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

            <?php if ($auth->isUserInGroup('administrador')): ?>
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

            <?php if ($auth->isLoggedIn()): ?>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-graph-up"></i>
                        <p>
                            Reportes
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= site_url('tienda/reportes/ventaspormes') ?>" class="nav-link">
                                <i class="bi bi-calendar2-week nav-icon"></i>
                                <p>Ventas por Mes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('tienda/reportes/ventasporpais') ?>" class="nav-link">
                                <i class="bi bi-globe-americas nav-icon"></i>
                                <p>Ventas por País</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('tienda/reportes/ventasporlinea') ?>" class="nav-link">
                                <i class="bi bi-box-seam nav-icon"></i>
                                <p>Ventas por Línea</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('tienda/reportes/margenporlinea') ?>" class="nav-link">
                                <i class="bi bi-graph-up-arrow nav-icon"></i>
                                <p>Margen de Ganancia</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('tienda/reportes/estadosdecuenta') ?>" class="nav-link">
                                <i class="bi bi-cash-coin nav-icon"></i>
                                <p>Estados de Cuenta</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('tienda/reportes/mejoresempleados') ?>" class="nav-link">
                                <i class="bi bi-person-check nav-icon"></i>
                                <p>Mejores Empleados</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= site_url('tienda/reportes/menorrotacion') ?>" class="nav-link">
                                <i class="bi bi-hourglass-bottom nav-icon"></i>
                                <p>Prod. Menor Rotación</p>
                            </a>
                        </li>
                    </ul>
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
<div class="container-fluid"> <!--begin::Start Navbar Links-->

    <ul class="navbar-nav">
        <li class="nav-item"> <a class="nav-link text-primary" data-lte-toggle="sidebar" href="#" role="button"> <i
                    class="bi bi-list"></i> </a> </li>
    </ul>


    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">
                <i class="bi bi-house-door"></i> <span class="d-none d-md-inline">Inicio</span></a>
        </li>

        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="<?= site_url('admin/perfil') ?>">
                <i class="bi bi-person-bounding-box"></i> <span class="d-none d-md-inline">Mi perfil</span></a>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-file-spreadsheet-fill"></i> <span class="d-none d-md-inline">Catálogos</span>
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a href="<?= site_url('tienda/oficinas') ?>" class="nav-link">
                        <i class="bi bi-building"></i>
                        Oficinas
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('tienda/empleados') ?>" class="nav-link">
                        <i class="bi bi-person-badge"></i>
                        Empleados
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('tienda/lineas') ?>" class="nav-link">
                        <i class="bi bi-tags"></i>
                        Lineas
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('tienda/productos') ?>" class="nav-link">
                        <i class="bi bi-car-front"></i>
                        Productos
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('tienda/clientes') ?>" class="nav-link">
                        <i class="bi bi-person"></i>
                        Clientes
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a href="<?= site_url('tienda/pagos') ?>" class="nav-link">
                        <i class="bi bi-cash"></i>
                        Pagos
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('tienda/ordenes') ?>" class="nav-link">
                        <i class="bi bi-send"></i>
                        Órdenes
                    </a>
                </li>

            </ul>
        </li>

    </ul>


    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">

        <li class="nav-item"> <a class="nav-link text-primary" href="#" data-lte-toggle="fullscreen"> <i
                    data-lte-icon="maximize" class="bi bi-arrows-fullscreen" style="display: none;"></i> <i
                    data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: block;"></i> </a> </li>

        <?php $auth = service('Admin_aut'); ?>
        <?php if ($auth->isLoggedIn()): ?>

                <li class="nav-item dropdown user-menu"> <a href="#" class="nav-link dropdown-toggle text-primary"
                        data-bs-toggle="dropdown" aria-expanded="false"> <img class="user-image rounded-circle shadow"
                            src="./img/avatar.jpg" alt="User Image">
                        <span class="d-none d-md-inline"><?= $auth->name(); ?></span> </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end"> <!--begin::User Image-->
                        <li class="user-header text-bg-primary"> <img class="user-image rounded-circle shadow"
                                src="./img/avatar.jpg" alt="User Image">
                            <p>
                                <?= $auth->name(); ?> - Web Developer
                                <small>Miembro desde Nov. 2024</small>
                            </p>
                        </li> <!--end::User Image--> <!--begin::Menu Body-->
                        <li class="user-body"> <!--begin::Row-->
                            <div class="row">
                                <!-- <div class="col-4 text-center"> <a href="#">Followers</a> </div>
                            <div class="col-4 text-center"> <a href="#">Sales</a> </div>
                            <div class="col-4 text-center"> <a href="#">Friends</a> </div> -->
                            </div> <!--end::Row-->
                        </li> <!--end::Menu Body--> <!--begin::Menu Footer-->
                        <li class="user-footer">
                            <a href="<?= site_url('/admin/perfil'); ?>" class="dropdown-item">
                                <i class="bi bi-person-circle nav-icon"></i> Perfil del usuario
                            </a>
                            <a href="<?= site_url('admin/logout'); ?>" class="dropdown-item">
                                <i class="bi bi-door-closed"></i> Cerrar sesión
                            </a>
                        </li> <!--end::Menu Footer-->
                    </ul>
                </li>
        <?php endif; ?>

    </ul>

</div>
<div class="container-fluid"> <!--begin::Start Navbar Links-->

    <ul class="navbar-nav">
        <li class="nav-item"> <a class="nav-link text-primary" data-lte-toggle="sidebar" href="#" role="button"> <i
                    class="bi bi-list"></i> </a> </li>
    </ul>


    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php foreach (service('menu')->getTopMenu() as $item): ?>
            <?php if (isset($item['children'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi <?= $item['icon'] ?>"></i> <span class="d-none d-md-inline"><?= $item['title'] ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($item['children'] as $child): ?>
                            <?php if (isset($child['divider'])): ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            <?php else: ?>
                                <li>
                                    <a href="<?= $child['url'] ?>" class="nav-link text-nowrap">
                                        <i class="bi <?= $child['icon'] ?>"></i>
                                        <?= $child['title'] ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="<?= $item['url'] ?>">
                        <i class="bi <?= $item['icon'] ?>"></i> <span
                            class="d-none d-md-inline"><?= $item['title'] ?></span></a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
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
                        <?php if ($auth->id() != 1): ?>
                            <a href="javascript:void(0);" class="dropdown-item" id="btn-cambiar-password-propio">
                                <i class="bi bi-key"></i> Cambiar contraseña
                            </a>
                        <?php endif; ?>
                        <a href="<?= site_url('admin/logout'); ?>" class="dropdown-item">
                            <i class="bi bi-door-closed"></i> Cerrar sesión
                        </a>
                    </li> <!--end::Menu Footer-->
                </ul>
            </li>
        <?php endif; ?>

    </ul>

</div>

<script>
    document.getElementById('btn-cambiar-password-propio')?.addEventListener('click', function () {
        Swal.fire({
            title: 'Cambiar Contraseña',
            input: 'password',
            inputLabel: 'Nueva contraseña',
            inputPlaceholder: 'Ingresa tu nueva contraseña',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Cambiar',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: (password) => {
                if (!password) {
                    Swal.showValidationMessage('La contraseña no puede estar vacía');
                    return false;
                }
                return getObject('admin/cambiar_password', { password: password })
                    .then(data => {
                        if (data.result === 'error') {
                            let errorMsg = 'Error al cambiar la contraseña';
                            if (data.errors && data.errors.password) {
                                errorMsg = data.errors.password;
                            }
                            throw new Error(errorMsg);
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
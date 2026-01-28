<?= $this->extend('template/template_lte') ?>

<?php $auth = service('Admin_aut'); ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-3">

        <!-- Profile Image -->
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    <img class="profile-user-img rounded-circle img-fluid border border-3 border-white shadow-sm"
                        src="./img/logomini.webp" id="fotoPerfil" alt="Foto de perfil"
                        style="width: 120px; height: 120px; object-fit: cover;">
                </div>

                <h3 class="profile-username text-center font-weight-bold mb-1">
                    <?= $auth->name(); ?>
                </h3>

                <p class="text-muted text-center mb-4">
                    <span class="badge bg-light text-dark border"><?= $auth->getField('gru_nombre'); ?></span>
                </p>

                <div class="row text-center mb-3">
                    <div class="col-4 border-end">
                        <h5 class="font-weight-bold mb-0">1,322</h5>
                        <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Seguidores</small>
                    </div>
                    <div class="col-4 border-end">
                        <h5 class="font-weight-bold mb-0">543</h5>
                        <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Siguiendo</small>
                    </div>
                    <div class="col-4">
                        <h5 class="font-weight-bold mb-0">13K</h5>
                        <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Amigos</small>
                    </div>
                </div>

                <button class="btn btn-primary w-100 shadow-sm rounded-pill" id="btnSeguir">
                    <i class="bi bi-person-plus-fill me-1"></i> Seguir
                </button>

                <script>
                    $(function () {
                        $('#btnSeguir').click(function (e) {
                            e.preventDefault();
                            Swal.fire({
                                title: '¿Seguir a este usuario?',
                                text: "Estás a punto de seguir a este usuario, ¿Estás seguro?",
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#0d6efd',
                                cancelButtonColor: '#dc3545',
                                confirmButtonText: 'Sí, seguir',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    showToast('Ahora sigues a este usuario!.', 'success');
                                }
                            });
                        });

                        $('#fotoPerfil').click(function () {
                            Swal.fire({
                                title: 'Cambiar foto de perfil',
                                text: "No puedes cambiar tu foto de perfil en este momento.",
                                icon: 'info',
                                confirmButtonColor: '#0d6efd',
                                confirmButtonText: 'Aceptar'
                            });
                        })
                    });
                </script>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->

        <!-- About Me Box -->
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white border-bottom-0 pt-3">
                <h3 class="card-title fw-bold"><i class="bi bi-person-lines-fill me-1 text-primary"></i> Acerca de mí
                </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body pt-0">
                <div class="mb-3">
                    <strong class="text-dark"><i class="bi bi-book me-1 text-secondary"></i> Educación</strong>
                    <p class="text-muted ps-4 mb-0">
                        Maestría en Redes con Mención Honorífica
                    </p>
                </div>

                <hr class="my-3">

                <div class="mb-3">
                    <strong class="text-dark"><i class="bi bi-geo-alt me-1 text-secondary"></i> Dirección</strong>
                    <p class="text-muted ps-4 mb-0">Xalapa, Veracruz</p>
                </div>

                <hr class="my-3">

                <div class="mb-3">
                    <strong class="text-dark"><i class="bi bi-pencil me-1 text-secondary"></i> Habilidades</strong>
                    <p class="text-muted ps-4 mt-2 mb-0">
                        <span
                            class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 pb-1 pt-1 ps-2 pe-2 me-1">Delphi</span>
                        <span
                            class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 pb-1 pt-1 ps-2 pe-2 me-1">MySQL</span>
                        <span
                            class="badge rounded-pill bg-info bg-opacity-10 text-info border border-info border-opacity-25 pb-1 pt-1 ps-2 pe-2 me-1">Javascript</span>
                        <span
                            class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 pb-1 pt-1 ps-2 pe-2 me-1">PHP</span>
                        <span
                            class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 pb-1 pt-1 ps-2 pe-2 me-1">Python</span>
                    </p>
                </div>

                <hr class="my-3">

                <div class="mb-0">
                    <strong class="text-dark"><i class="bi bi-file-earmark-text me-1 text-secondary"></i> Notas</strong>
                    <p class="text-muted ps-4 mb-0 small fst-italic">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolorem illo iste vel, odio aliquam
                        unde et
                        ea, distinctio numquam saepe.
                    </p>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
    <div class="col-md-9">
        <div class="card shadow-sm card-outline card-primary ">
            <div class="card-header p-2 border-bottom-0">
                <nav>
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item me-1" role="presentation">
                            <button class="nav-link active fw-bold" id="activity-tab" data-bs-toggle="pill"
                                data-bs-target="#activity" type="button" role="tab" aria-controls="activity"
                                aria-selected="true"><i class="bi bi-activity me-1"></i> Actividades</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="activity-tab" data-bs-toggle="pill"
                                data-bs-target="#timeline" type="button" role="tab" aria-controls="timeline"
                                aria-selected="false"><i class="bi bi-clock-history me-1"></i> Linea de
                                tiempo</button>
                        </li>
                    </ul>
                </nav>
            </div><!-- /.card-header -->
            <div class="card-body">
                <div class="tab-content">
                    <div class="active tab-pane" id="activity">
                        <!-- Post -->
                        <div class="post clearfix border-bottom pb-3 mb-3">
                            <div class="user-block d-flex align-items-center mb-3">
                                <img class="img-circle img-bordered-sm me-2 rounded-circle border"
                                    src="./img/avatar.jpg" alt="User Image" style="width: 40px; height: 40px;">
                                <div class="d-flex flex-column">
                                    <span class="username">
                                        <a href="#" class="text-decoration-none fw-bold text-dark">Jahir Castillo.</a>
                                        <a href="#" class="float-end btn-tool text-muted"><i class="bi bi-x-lg"></i></a>
                                    </span>
                                    <span class="description text-muted small">Compartió públicamente - 7:30 PM
                                        Hoy</span>
                                </div>
                            </div>
                            <!-- /.user-block -->
                            <p class="text-secondary">
                                Lorem ipsum dolor sit, amet consectetur adipisicing elit. Repudiandae quos facilis
                                dolorem? Consequuntur assumenda nulla commodi a! Veniam neque ipsa magnam harum,
                                asperiores laboriosam consequatur.
                            </p>

                            <p class="mb-2">
                                <a href="#" class="link-secondary text-sm me-3 text-decoration-none"><i
                                        class="bi bi-share me-1"></i> Compartir</a>
                                <a href="#" class="link-secondary text-sm me-3 text-decoration-none"><i
                                        class="bi bi-hand-thumbs-up me-1"></i> Like</a>
                                <span class="float-end">
                                    <a href="#" class="link-secondary text-sm text-decoration-none">
                                        <i class="bi bi-chat-fill me-1"></i> 5 comentarios
                                    </a>
                                </span>
                            </p>

                            <div class="input-group">
                                <input class="form-control form-control-sm rounded-pill bg-light border-0 px-3"
                                    name="comentario" type="text" placeholder="Escribe un comentario...">
                                <button class="btn btn-sm btn-link text-primary"><i
                                        class="bi bi-send-fill"></i></button>
                            </div>
                        </div>
                        <!-- /.post -->


                        <div class="d-flex align-items-center mb-3 mt-4">
                            <h5 class="m-0 text-secondary"><i class="bi bi-search me-2"></i>Herramientas de Búsqueda
                            </h5>
                            <hr class="flex-grow-1 ms-3">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="card bg-light border-0 shadow-none mb-3">
                                    <div class="card-body p-3">
                                        <div class="form-group">
                                            <label for="editusuario"
                                                class="form-label fw-bold small text-uppercase text-secondary">Búsqueda
                                                Rápida</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control border-start-0 ps-0" name=""
                                                    id="editusuario" placeholder="Escribe el nombre del usuario...">
                                            </div>
                                            <small class="form-text text-muted fst-italic">Ejemplo de búsqueda con
                                                autocompletado simple</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-light border-0 shadow-none h-100">
                                    <div class="card-body p-3">
                                        <div class="form-group">
                                            <label for="busquedausuario"
                                                class="form-label fw-bold small text-uppercase text-secondary">Catálogo
                                                Filtrado</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control bg-white" name=""
                                                    id="busquedausuario" placeholder="Buscar activos grupo 1...">
                                            </div>
                                            <small class="form-text text-muted">Filtro: Activos + Grupo 1</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-light border-0 shadow-none h-100">
                                    <div class="card-body p-3">
                                        <div class="form-group">
                                            <label for="busquedausuariosql"
                                                class="form-label fw-bold small text-uppercase text-secondary">Búsqueda
                                                SQL</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control bg-white" name=""
                                                    id="busquedausuariosql" placeholder="Buscar grupo 3...">
                                            </div>
                                            <small class="form-text text-muted">Filtro: Grupo 3 (SQL)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scripts de búsqueda originales mantenidos -->
                        <script>
                            $(function () {
                                RagnosSearch.setupSimpleSearch($('#editusuario'), 'admin/testusuarios', {}, function (e) {
                                    let datos = e.data('searchdata');
                                    if (datos && datos.Nombre) {
                                        e.val(datos.Nombre);
                                        e.data('usu_id', datos.usu_id || null);
                                        console.log('Usuario seleccionado:', {
                                            id: datos.usu_id,
                                            nombre: datos.Nombre
                                        });
                                    }
                                });

                                $('#busquedausuario').RagnosSearch({
                                    controller: 'usuarios',
                                    filter: btoa(JSON.stringify([
                                        { "field": "usu_activo", "op": "=", "value": "S" },
                                        { "field": "usu_grupo", "op": "=", "value": 1 }
                                    ])),
                                    callback: function (e) {
                                        let datos = e.data('searchdata');
                                        if (datos && datos.y_id) {
                                            console.log('usu_id', (datos.y_id));
                                        }
                                    }
                                });

                                $('#busquedausuariosql').RagnosSearch({
                                    controller: 'searchusuarios',
                                    filter: btoa(JSON.stringify([
                                        { "field": "usu_grupo", "op": "=", "value": 3 }
                                    ])),
                                    callback: function (e) {
                                        let datos = e.data('searchdata');
                                        if (datos && datos.y_id) {
                                            console.log('usu_id', (datos.y_id));
                                        }
                                    }
                                });
                            });
                        </script>

                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="timeline">
                        <!-- The timeline -->
                        <div class="timeline timeline-inverse">
                            <!-- timeline time label -->
                            <div class="time-label">
                                <span class="badge rounded-pill bg-danger px-3 py-2 shadow-sm">
                                    10 Feb. 2025
                                </span>
                            </div>
                            <!-- /.timeline-label -->

                            <!-- timeline item -->
                            <div>
                                <i class="timeline-icon bi bi-envelope-fill text-bg-primary"></i>

                                <div class="timeline-item shadow-sm border-0">
                                    <span class="time"><i class=" bi bi-clock"></i> 12:05</span>

                                    <h3 class="timeline-header"><a
                                            class="text-primary text-decoration-none fw-bold">Soporte técnico</a> te
                                        envío un
                                        correo</h3>

                                    <div class="timeline-body text-muted">
                                        Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                                        weebly ning heekya handango imeem plugg dopplr jibjab, movity
                                        jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                                        quora plaxo ideeli hulu weebly balihoo...
                                    </div>
                                    <div class="timeline-footer">
                                        <a class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">Leer más</a>
                                        <a class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">Borrar</a>
                                    </div>
                                </div>
                            </div>
                            <!-- END timeline item -->
                            <!-- timeline item -->
                            <div>
                                <i class="timeline-icon  bi bi-person-fill text-bg-info"></i>

                                <div class="timeline-item shadow-sm border-0">
                                    <span class="time"><i class="bi bi-clock"></i> hace 5 mins</span>

                                    <h3 class="timeline-header no-border"><a
                                            class="text-info text-decoration-none fw-bold">Mariana Arcos</a> aceptó
                                        su solicitud
                                    </h3>
                                </div>
                            </div>
                            <!-- END timeline item -->
                            <!-- timeline item -->
                            <div>
                                <i class="timeline-icon bi bi-chat-left-text-fill text-bg-warning"></i>

                                <div class="timeline-item shadow-sm border-0">
                                    <span class="time"><i class="bi bi-clock"></i> hace 27 mins</span>

                                    <h3 class="timeline-header"><a
                                            class="text-warning text-decoration-none fw-bold">Ragueb Chain</a> comentó
                                        tu
                                        artículo</h3>

                                    <div class="timeline-body">
                                        <p class="text-muted mb-0">Lorem ipsum dolor sit amet consectetur adipisicing
                                            elit. Laborum, animi non
                                            praesentium laboriosam laudantium eos id magnam, amet quibusdam quod, eaque
                                            nostrum architecto sapiente culpa veritatis! Praesentium sunt tempora id?
                                        </p>
                                    </div>
                                    <div class="timeline-footer">
                                        <a
                                            class="btn btn-warning btn-flat btn-sm text-white rounded-pill px-3 shadow-sm">Ver
                                            comentario</a>
                                    </div>
                                </div>
                            </div>
                            <!-- END timeline item -->
                            <!-- timeline time label -->
                            <div class="time-label">
                                <span class="badge rounded-pill bg-success px-3 py-2 shadow-sm">
                                    3 Ene. 2025
                                </span>
                            </div>
                            <!-- /.timeline-label -->
                            <!-- timeline item -->
                            <div>
                                <i class="timeline-icon bi bi-camera-fill text-bg-dark"></i>

                                <div class="timeline-item shadow-sm border-0">
                                    <span class="time"><i class="bi bi-clock"></i> hace 2 dias </span>

                                    <h3 class="timeline-header"><a class="text-dark text-decoration-none fw-bold"> Karla
                                            Ortiz </a> subió nuevas fotos</h3>

                                    <div class="timeline-body">
                                        <img src="./img/sample.webp" alt="..." class="rounded shadow-sm m-1 border"
                                            style="width: 100px; height: 100px; object-fit: cover;">
                                        <img src="./img/sample.webp" alt="..." class="rounded shadow-sm m-1 border"
                                            style="width: 100px; height: 100px; object-fit: cover;">
                                        <img src="./img/sample.webp" alt="..." class="rounded shadow-sm m-1 border"
                                            style="width: 100px; height: 100px; object-fit: cover;">
                                        <img src="./img/sample.webp" alt="..." class="rounded shadow-sm m-1 border"
                                            style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                            <!-- END timeline item -->
                            <div>
                                <i class="timeline-icon bi bi-clock text-bg-gray"></i>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->


                </div>
                <!-- /.tab-content -->
            </div><!-- /.card-body -->
        </div>


        <div class="card shadow-sm mt-4 card-outline card-success">
            <div class="card-header bg-white border-bottom-0 pb-0">
                <h3 class="card-title fw-bold text-success"><i class="bi bi-list-check me-2"></i>Tareas
                    asignadas</h3>
            </div> <!-- /.card-header -->
            <div class="card-body pt-2">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr class="text-uppercase text-muted" style="font-size: 0.85rem;">
                            <th style="width: 10px">#</th>
                            <th>Tarea</th>
                            <th>Progreso</th>
                            <th style="width: 80px" class="text-center">Etiqueta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="align-middle">
                            <td>1.</td>
                            <td class="fw-bold text-dark">Actualizar software <span class="ms-1">⌨️</span></td>
                            <td>
                                <div class="progress progress-xs rounded-pill bg-light" style="height: 6px;">
                                    <div class="progress-bar bg-danger" style="width: 55%"></div>
                                </div>
                            </td>
                            <td class="text-center"><span
                                    class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2">55%</span>
                            </td>
                        </tr>
                        <tr class="align-middle">
                            <td>2.</td>
                            <td class="fw-bold text-dark">Limpiar la base de datos <span class="ms-1">💻</span>
                            </td>
                            <td>
                                <div class="progress progress-xs rounded-pill bg-light" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: 70%"></div>
                                </div>
                            </td>
                            <td class="text-center"> <span
                                    class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2">70%</span>
                            </td>
                        </tr>
                        <tr class="align-middle">
                            <td>3.</td>
                            <td class="fw-bold text-dark">Ejecución de cronjobs <span class="ms-1">⏱️</span>
                            </td>
                            <td>
                                <div class="progress progress-xs rounded-pill bg-light" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 30%"></div>
                                </div>
                            </td>
                            <td class="text-center"> <span
                                    class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2">30%</span>
                            </td>
                        </tr>
                        <tr class="align-middle">
                            <td>4.</td>
                            <td class="fw-bold text-dark">Mantenimiento y solución de bugs <span class="ms-1">🪳</span>
                            </td>
                            <td>
                                <div class="progress progress-xs rounded-pill bg-light" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: 90%"></div>
                                </div>
                            </td>
                            <td class="text-center"> <span
                                    class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2">90%</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div> <!-- /.card-body -->
            <div class="card-footer bg-white border-top-0 clearfix pb-3">
                <ul class="pagination pagination-sm m-0 float-end shadow-sm">
                    <li class="page-item disabled"> <a class="page-link border-0 bg-light" href="#">«</a> </li>
                    <li class="page-item active"> <a class="page-link border-0 shadow-sm" href="#">1</a> </li>
                    <li class="page-item"> <a class="page-link border-0" href="#">2</a> </li>
                    <li class="page-item"> <a class="page-link border-0" href="#">3</a> </li>
                    <li class="page-item"> <a class="page-link border-0 bg-light" href="#">»</a> </li>
                </ul>
            </div>
        </div>

    </div>
    <!-- /.col -->
</div>

<?= $this->endSection() ?>
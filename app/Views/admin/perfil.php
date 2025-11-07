<?= $this->extend('template/template_lte') ?>

<?php $auth = service('Admin_aut'); ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-3">

        <!-- Profile Image -->
        <div class="card  shadow-lg border-primary">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img rounded-circle img-fluid" src="./img/avatar.jpg" id="fotoPerfil"
                        alt="Foto de perfil" width="25%">
                </div>

                <h5 class="profile-username text-center">
                    <?= $auth->nombre(); ?>
                </h5>

                <p class="text-muted text-center">
                    <?= $auth->campo('gru_nombre'); ?>
                </p>

                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item text-primary">
                        <b>Seguidores</b> <a class="float-end">1,322</a>
                    </li>
                    <li class="list-group-item text-primary">
                        <b>Siguiendo</b> <a class="float-end">543</a>
                    </li>
                    <li class="list-group-item text-primary">
                        <b>Amigos</b> <a class="float-end">13,287</a>
                    </li>
                </ul>

                <a class="btn btn-primary w-100" id="btnSeguir"><b>Seguir</b></a>

                <script>
                    $(function () {
                        $('#btnSeguir').click(function (e) {
                            e.preventDefault();
                            Swal.fire({
                                title: '¿Seguir a este usuario?',
                                text: "Estás a punto de seguir a este usuario, ¿Estás seguro?",
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
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
                                icon: 'error',
                                confirmButtonColor: '#3085d6',
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
        <div class="card  shadow-lg border-primary mt-3">
            <div class="card-header">
                <h3 class="card-title">Acerca de mi</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <strong><i class="bi bi-book mr-1"></i> Educación</strong>

                <p class="text-muted">
                    Maestría en Redes con Mención Honorífica
                </p>

                <hr>

                <strong><i class="bi bi-geo-alt mr-1"></i> Dirección</strong>

                <p class="text-muted">Xalapa, Veracruz</p>

                <hr>

                <strong><i class="bi bi-pencil mr-1"></i> Habilidades</strong>

                <p class="text-muted">
                    <span class="badge bg-danger">Delphi</span>
                    <span class="badge bg-success">MySQL</span>
                    <span class="badge bg-info">Javascript</span>
                    <span class="badge bg-warning">PHP</span>
                    <span class="badge bg-primary">Python</span>
                </p>

                <hr>

                <strong><i class="bi bi-file-earmark-text mr-1"></i> Notas</strong>

                <p class="text-muted">
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolorem illo iste vel, odio aliquam unde et
                    ea, distinctio numquam saepe, eligendi optio. Aperiam velit, iure ut debitis soluta magni nostrum.
                </p>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
    <div class="col-md-9">
        <div class="card shadow-lg border-primary ">
            <div class="card-header p-2">
                <nav>
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="activity-tab" data-bs-toggle="pill"
                                data-bs-target="#activity" type="button" role="tab" aria-controls="activity"
                                aria-selected="true">Actividades</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="activity-tab" data-bs-toggle="pill" data-bs-target="#timeline"
                                type="button" role="tab" aria-controls="timeline" aria-selected="false">Linea de
                                tiempo</button>
                        </li>
                    </ul>
                </nav>
            </div><!-- /.card-header -->
            <div class="card-body">
                <div class="tab-content">
                    <div class="active tab-pane" id="activity">
                        <!-- Post -->
                        <div class="post">
                            <div class="user-block">
                                <img class="direct-chat-img" src="./img/avatar.jpg" alt="User Image">
                                <span class="username">
                                    <a>Jahir Castillo.</a>
                                    <a class="float-end btn-tool"><i class="bi bi-x"></i></a>
                                </span>
                                <span class="description">Compartió - 7:30 PM Hoy</span>
                            </div>
                            <!-- /.user-block -->
                            <p>
                                Lorem ipsum dolor sit, amet consectetur adipisicing elit. Repudiandae quos facilis
                                dolorem? Consequuntur assumenda nulla commodi a! Veniam neque ipsa magnam harum,
                                asperiores laboriosam consequatur, nemo rerum odit corporis consectetur?
                            </p>

                            <p>
                                <a class="link-black text-sm me-2"><i class="bi bi-share"></i> Compartir</a>
                                <a class="link-black text-sm"><i class="bi bi-hand-thumbs-up"></i> Like</a>
                                <span class="float-end">
                                    <a class="link-black text-sm">
                                        <i class="bi bi-chat"></i> comentarios (5)
                                    </a>
                                </span>
                            </p>

                            <input class="form-control form-control-sm" type="text" placeholder="Escribe un comentario">
                        </div>
                        <!-- /.post -->



                        <div class="post clearfix">
                            <div class="form-group form-control">
                                <label for="">Busca Usuario:</label>
                                <input type="text" class="form-control" name="" id="editusuario"
                                    placeholder="Busca usuario">
                                <small class="form-text text-muted">Ejemplo de búsqueda</small>
                            </div>
                            <script>
                                $(function () {
                                    RagnosSearch.setupSimpleSearch($('#editusuario'), 'admin/testusuarios', {}, function (e) {
                                        let datos = e.data('searchdata');
                                        if (datos.Nombre) {
                                            e.val(datos.Nombre);
                                            e.data('usu_id', (datos.usu_id));
                                        }
                                    });
                                });
                            </script>
                        </div>

                        <hr>

                        <div class="post clearfix">
                            <div class="form-group form-control ">
                                <label for="">Busca Usuario:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="" id="busquedausuario"
                                        placeholder="Busca usuario">
                                </div>
                                <small class="form-text text-muted">Búsqueda regular de catálogo</small>
                            </div>

                            <script>
                                $(function () {
                                    $('#busquedausuario').RagnosSearch({
                                        controller: 'usuarios',
                                        filter: btoa(JSON.stringify([
                                            { "field": "usu_activo", "op": "=", "value": "S" },
                                            { "field": "usu_grupo", "op": "=", "value": 2 }
                                        ])),
                                        callback: function (e) {
                                            let datos = e.data('searchdata');
                                            console.log('datos', datos);
                                            if (datos && datos.y_id) {
                                                console.log('usu_id', (datos.y_id));
                                                console.log('usu_nombre', (datos.usu_nombre));
                                            }
                                        }
                                    });
                                });
                            </script>
                        </div>


                        <div class="post clearfix">
                            <div class="form-group form-control ">
                                <label for="">Busca Usuario:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="" id="busquedausuariosql"
                                        placeholder="Busca usuario SQL">
                                </div>
                                <small class="form-text text-muted">Búsqueda SQL</small>
                            </div>

                            <script>
                                $(function () {
                                    $('#busquedausuariosql').RagnosSearch({
                                        controller: 'searchusuarios',
                                        filter: btoa(JSON.stringify([
                                            { "field": "usu_grupo", "op": "=", "value": 1 }
                                        ])),
                                        callback: function (e) {
                                            let datos = e.data('searchdata');
                                            console.log('datos', datos);
                                            if (datos && datos.y_id) {
                                                console.log('usu_id', (datos.y_id));
                                                console.log('usu_nombre', (datos.usu_nombre));
                                            }
                                        }
                                    });
                                });
                            </script>
                        </div>


                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="timeline">
                        <!-- The timeline -->
                        <div class="timeline timeline-inverse">
                            <!-- timeline time label -->
                            <div class="time-label">
                                <span class="text-bg-danger">
                                    10 Feb. 2025
                                </span>
                            </div>
                            <!-- /.timeline-label -->

                            <!-- timeline item -->
                            <div>
                                <i class="timeline-icon bi bi-envelope-fill text-bg-primary"></i>

                                <div class="timeline-item">
                                    <span class="time"><i class=" bi bi-clock"></i> 12:05</span>

                                    <h3 class="timeline-header"><a>Soporte técnico</a> te envío un
                                        correo</h3>

                                    <div class="timeline-body">
                                        Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                                        weebly ning heekya handango imeem plugg dopplr jibjab, movity
                                        jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                                        quora plaxo ideeli hulu weebly balihoo...
                                    </div>
                                    <div class="timeline-footer">
                                        <a class="btn btn-primary btn-sm">Leer mas...</a>
                                        <a class="btn btn-danger btn-sm">Borrar</a>
                                    </div>
                                </div>
                            </div>
                            <!-- END timeline item -->
                            <!-- timeline item -->
                            <div>
                                <i class="timeline-icon  bi bi-person-fill text-bg-info"></i>

                                <div class="timeline-item">
                                    <span class="time"><i class="bi bi-clock"></i> hace 5 mins</span>

                                    <h3 class="timeline-header no-border"><a>Mariana Arcos</a> aceptó
                                        su solicitud
                                    </h3>
                                </div>
                            </div>
                            <!-- END timeline item -->
                            <!-- timeline item -->
                            <div>
                                <i class="timeline-icon bi bi-chat-left-text-fill text-bg-warning"></i>

                                <div class="timeline-item">
                                    <span class="time"><i class="bi bi-clock"></i> hace 27 mins</span>

                                    <h3 class="timeline-header"><a>Ragueb Chain</a> comentó tu
                                        artículo</h3>

                                    <div class="timeline-body">
                                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Laborum, animi non
                                            praesentium laboriosam laudantium eos id magnam, amet quibusdam quod, eaque
                                            nostrum architecto sapiente culpa veritatis! Praesentium sunt tempora id?
                                        </p>
                                    </div>
                                    <div class="timeline-footer">
                                        <a class="btn btn-warning btn-flat btn-sm">Ver comentario</a>
                                    </div>
                                </div>
                            </div>
                            <!-- END timeline item -->
                            <!-- timeline time label -->
                            <div class="time-label">
                                <span class="text-bg-success">
                                    3 Ene. 2025
                                </span>
                            </div>
                            <!-- /.timeline-label -->
                            <!-- timeline item -->
                            <div>
                                <i class="timeline-icon bi bi-camera-fill text-bg-purple"></i>

                                <div class="timeline-item">
                                    <span class="time"><i class="bi bi-clock"></i> hace 2 dias </span>

                                    <h3 class="timeline-header"><a> Karla Ortiz </a> subió nuevas fotos</h3>

                                    <div class="timeline-body">
                                        <img src="./img/sample.webp" alt="...">
                                        <img src="./img/sample.webp" alt="...">
                                        <img src="./img/sample.webp" alt="...">
                                        <img src="./img/sample.webp" alt="...">
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


        <div class="card  shadow-lg border-primary mt-3">
            <div class="card-header">
                <h3 class="card-title">Tareas asignadas</h3>
            </div> <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Tarea</th>
                            <th>Progreso</th>
                            <th style="width: 40px">Etiqueta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="align-middle">
                            <td>1.</td>
                            <td>Actualizar software ⌨️</td>
                            <td>
                                <div class="progress progress-xs">
                                    <div class="progress-bar progress-bar-danger" style="width: 55%"></div>
                                </div>
                            </td>
                            <td><span class="badge text-bg-danger">55%</span></td>
                        </tr>
                        <tr class="align-middle">
                            <td>2.</td>
                            <td>Limpiar la base de datos 💻</td>
                            <td>
                                <div class="progress progress-xs">
                                    <div class="progress-bar text-bg-warning" style="width: 70%"></div>
                                </div>
                            </td>
                            <td> <span class="badge text-bg-warning">70%</span> </td>
                        </tr>
                        <tr class="align-middle">
                            <td>3.</td>
                            <td>Ejecución de cronjobs ⏱️</td>
                            <td>
                                <div class="progress progress-xs progress-striped active">
                                    <div class="progress-bar text-bg-primary" style="width: 30%"></div>
                                </div>
                            </td>
                            <td> <span class="badge text-bg-primary">30%</span> </td>
                        </tr>
                        <tr class="align-middle">
                            <td>4.</td>
                            <td>Mantenimiento y solución de bugs 🪳</td>
                            <td>
                                <div class="progress progress-xs progress-striped active">
                                    <div class="progress-bar text-bg-success" style="width: 90%"></div>
                                </div>
                            </td>
                            <td> <span class="badge text-bg-success">90%</span> </td>
                        </tr>
                    </tbody>
                </table>
            </div> <!-- /.card-body -->
            <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-end">
                    <li class="page-item"> <a class="page-link" href="#">«</a> </li>
                    <li class="page-item"> <a class="page-link" href="#">1</a> </li>
                    <li class="page-item"> <a class="page-link" href="#">2</a> </li>
                    <li class="page-item"> <a class="page-link" href="#">3</a> </li>
                    <li class="page-item"> <a class="page-link" href="#">»</a> </li>
                </ul>
            </div>
        </div>

    </div>
    <!-- /.col -->
</div>

<?= $this->endSection() ?>
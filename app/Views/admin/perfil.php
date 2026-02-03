<?= $this->extend('template/template_lte') ?>

<?php $auth = service('Admin_aut'); ?>

<?= $this->section('content') ?>

<div class="row animate__animated animate__fadeIn">
    <!-- Columna Izquierda: Perfil y Datos Personales -->
    <div class="col-lg-4 col-xl-3">

        <!-- Card de Perfil Principal -->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 animate__animated animate__fadeInLeft">
            <!-- Fondo de cabecera decorativo -->
            <div class="bg-primary bg-gradient" style="height: 100px; opacity: 0.8;"></div>

            <div class="card-body box-profile pt-0" style="margin-top: -50px;">
                <div class="text-center mb-3 position-relative">
                    <div class="d-inline-block p-1 bg-white rounded-circle shadow-sm">
                        <img class="profile-user-img rounded-circle img-fluid border border-3 border-white shadow-sm hover-grow"
                            src="./img/logomini.webp" id="fotoPerfil" alt="Foto de perfil"
                            style="width: 110px; height: 110px; object-fit: cover; cursor: pointer; transition: transform 0.3s;">
                    </div>
                </div>

                <h4 class="profile-username text-center fw-bold text-dark mb-1">
                    <?= $auth->name(); ?>
                </h4>

                <p class="text-muted text-center mb-4 small">
                    <span
                        class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2 rounded-pill">
                        <i class="bi bi-shield-check me-1"></i><?= $auth->getField('gru_nombre'); ?>
                    </span>
                </p>

                <div class="row g-0 text-center mb-4 bg-light bg-opacity-50 rounded-3 py-3 mx-1">
                    <div class="col-4 border-end border-light-subtle">
                        <h6 class="fw-bold text-dark mb-0">1,322</h6>
                        <small class="text-muted text-uppercase tracking-wider"
                            style="font-size: 0.65rem;">Seguidores</small>
                    </div>
                    <div class="col-4 border-end border-light-subtle">
                        <h6 class="fw-bold text-dark mb-0">543</h6>
                        <small class="text-muted text-uppercase tracking-wider"
                            style="font-size: 0.65rem;">Siguiendo</small>
                    </div>
                    <div class="col-4">
                        <h6 class="fw-bold text-dark mb-0">13K</h6>
                        <small class="text-muted text-uppercase tracking-wider"
                            style="font-size: 0.65rem;">Amigos</small>
                    </div>
                </div>

                <div class="d-grid gap-2 px-2">
                    <button class="btn btn-primary shadow-sm rounded-pill fw-bold py-2 btn-hover-shine" id="btnSeguir">
                        <i class="bi bi-person-plus-fill me-1"></i> Seguir Usuario
                    </button>
                    <button class="btn btn-outline-secondary btn-sm border-light-subtle rounded-pill text-muted">
                        Enviar Mensaje
                    </button>
                </div>
            </div>
        </div>

        <!-- Card "Acerca de mí" Estilizada -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 animate__animated animate__fadeInLeft"
            style="animation-delay: 0.1s;">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="card-title fw-bold text-dark mb-0">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i>Información Personal
                </h6>
            </div>
            <div class="card-body pt-1">
                <div class="d-flex align-items-start mb-4">
                    <div class="bg-light rounded-3 p-2 me-3">
                        <i class="bi bi-book text-secondary"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold tracking-tighter"
                            style="font-size: 0.65rem;">Educación</small>
                        <span class="text-dark small fw-medium">Maestría en Redes con Mención Honorífica</span>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="bg-light rounded-3 p-2 me-3">
                        <i class="bi bi-geo-alt text-secondary"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold tracking-tighter"
                            style="font-size: 0.65rem;">Ubicación</small>
                        <span class="text-dark small fw-medium">Xalapa, Veracruz, México</span>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-light rounded-3 p-2 me-3">
                            <i class="bi bi-pencil text-secondary"></i>
                        </div>
                        <small class="text-muted text-uppercase fw-bold tracking-tighter"
                            style="font-size: 0.65rem;">Habilidades Técnicas</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2 ps-5 mt-1">
                        <span
                            class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 px-3">Delphi</span>
                        <span
                            class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-3">MySQL</span>
                        <span
                            class="badge rounded-pill bg-info bg-opacity-10 text-info border border-info border-opacity-10 px-3">JS/React</span>
                        <span
                            class="badge rounded-pill bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-10 px-3">PHP</span>
                        <span
                            class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3">Python</span>
                    </div>
                </div>

                <div
                    class="bg-light bg-opacity-50 rounded-3 p-3 mt-2 border-start border-3 border-primary border-opacity-25">
                    <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size: 0.6rem;">Biografía
                        Corta</small>
                    <p class="text-muted mb-0 small fst-italic lh-sm">
                        Especialista en desarrollo backend y arquitecturas de red con más de 10 años de experiencia en
                        soluciones empresariales.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Actividades, Herramientas y Tareas -->
    <div class="col-lg-8 col-xl-9">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate__animated animate__fadeInRight">
            <!-- Navegación de Pestañas Premium -->
            <div class="card-header bg-white p-0 border-bottom">
                <ul class="nav nav-tabs border-0 px-3 pt-2" id="profileTabs" role="tablist">
                    <li class="nav-item me-2" role="presentation">
                        <button
                            class="nav-link active border-0 border-bottom border-3 border-transparent fw-bold py-3 px-4"
                            id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab"
                            aria-controls="activity" aria-selected="true">
                            <i class="bi bi-activity-history me-2"></i>Actividades Recientes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link border-0 border-bottom border-3 border-transparent fw-bold py-3 px-4 text-muted"
                            id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab"
                            aria-controls="timeline" aria-selected="false">
                            <i class="bi bi-clock-history me-2"></i>Línea de Tiempo
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="profileTabsContent">

                    <!-- TAB: Actividades -->
                    <div class="tab-pane fade show active" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                        <!-- Post Estilizado -->
                        <div
                            class="post-item bg-white border border-light-subtle rounded-4 p-4 mb-4 hover-shadow transition-all">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle border border-2 border-white shadow-sm me-3"
                                        src="./img/avatar.jpg" alt="User Image" style="width: 48px; height: 48px;">
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">Jahir Castillo</h6>
                                        <small class="text-muted"><i class="bi bi-globe me-1"></i>Público • Hace 7:30
                                            PM</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                </div>
                            </div>

                            <p class="text-dark mb-4 lh-base fs-6 opacity-75">
                                Hemos completado la fase 1 del despliegue en la nueva infraestructura. Los tiempos de
                                respuesta han mejorado significativamente y el equipo está listo para la siguiente
                                etapa.
                            </p>

                            <div
                                class="d-flex align-items-center justify-content-between bg-light bg-opacity-50 rounded-3 p-3 mb-4">
                                <div class="d-flex gap-3 text-muted small">
                                    <a href="#"
                                        class="text-decoration-none text-primary fw-bold transition-all hover-grow">
                                        <i class="bi bi-hand-thumbs-up-fill me-1"></i> 24 Me gusta
                                    </a>
                                    <a href="#" class="text-decoration-none text-muted transition-all">
                                        <i class="bi bi-chat-text me-1"></i> 5 Comentarios
                                    </a>
                                </div>
                                <a href="#" class="text-muted small text-decoration-none transition-all hover-grow">
                                    <i class="bi bi-share me-1"></i> Compartir
                                </a>
                            </div>

                            <div class="d-flex align-items-center">
                                <img src="./img/logomini.webp" class="rounded-circle me-3 border shadow-sm"
                                    style="width: 32px; height: 32px;">
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control form-control-sm border-0 bg-light rounded-pill px-4"
                                        placeholder="Escribe un comentario amable...">
                                    <button class="btn btn-sm btn-link text-primary ms-1"><i
                                            class="bi bi-cursor-fill"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Herramientas de Búsqueda Ragnos -->
                        <div class="mt-5 mb-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                    <i class="bi bi-search text-primary fs-5"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-0">Ecosistema de Búsqueda Ragnos</h5>
                            </div>

                            <div class="row g-4">
                                <div class="col-12">
                                    <div
                                        class="card border-0 bg-light bg-opacity-25 rounded-4 border-start border-4 border-primary">
                                        <div class="card-body p-4">
                                            <label
                                                class="form-label small fw-bold text-primary text-uppercase tracking-widest mb-3">Autocompleta
                                                Rápido</label>
                                            <div class="input-group shadow-sm rounded-pill bg-white">
                                                <span class="input-group-text border-0 bg-transparent ps-4 text-muted">
                                                    <i class="bi bi-person-badge"></i>
                                                </span>
                                                <input type="text"
                                                    class="form-control border-0 py-3 ps-1 bg-transparent"
                                                    id="editusuario"
                                                    placeholder="Escribe el nombre del usuario para ver la magia...">
                                            </div>
                                            <div class="d-flex align-items-center mt-3 text-muted smaller">
                                                <i class="bi bi-info-circle me-2"></i>
                                                <span class="fst-italic">Este control utiliza
                                                    <code>setupSimpleSearch</code> para respuestas instantáneas.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div
                                        class="card border-0 bg-white border border-light-subtle rounded-4 h-100 shadow-sm hover-shadow transition-all">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-dark mb-3">Catálogo con Lógica de Negocio</h6>
                                            <div class="input-group shadow-sm rounded-3">
                                                <span class="input-group-text border-0 bg-light text-success">
                                                    <i class="bi bi-funnel-fill"></i>
                                                </span>
                                                <input type="text" class="form-control bg-light py-2 border-0"
                                                    id="busquedausuario" placeholder="Buscar usuarios activos...">
                                            </div>
                                            <p class="mt-3 text-muted small mb-0">
                                                <i class="bi bi-filter-circle me-1"></i> Filtro aplicado:
                                                <code>Activos + Grupo 1</code>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div
                                        class="card border-0 bg-white border border-light-subtle rounded-4 h-100 shadow-sm hover-shadow transition-all">
                                        <div class="card-body p-4">
                                            <h6 class="fw-bold text-dark mb-3">Búsqueda Nativa SQL</h6>
                                            <div class="input-group shadow-sm rounded-3">
                                                <span class="input-group-text border-0 bg-light text-warning">
                                                    <i class="bi bi-database-fill-gear"></i>
                                                </span>
                                                <input type="text" class="form-control bg-light py-2 border-0"
                                                    id="busquedausuariosql" placeholder="Consultar grupo 3...">
                                            </div>
                                            <p class="mt-3 text-muted small mb-0">
                                                <i class="bi bi-code-slash me-1"></i> Integración directa mediante
                                                Controlador SQL.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: Timeline -->
                    <div class="tab-pane fade" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
                        <div class="modern-timeline py-4">
                            <div class="time-stamp mb-5 animate__animated animate__fadeIn">
                                <span class="bg-primary text-white px-4 py-2 rounded-pill fw-bold shadow-sm">Hoy, 10 Feb
                                    2025</span>
                            </div>

                            <div
                                class="timeline-group border-start border-3 border-light ms-4 ps-4 mb-5 position-relative">

                                <div class="timeline-entry mb-5 position-relative">
                                    <div class="entry-icon position-absolute bg-primary text-white shadow"
                                        style="left: -42px; top: 0; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 2;">
                                        <i class="bi bi-envelope-check fs-6"></i>
                                    </div>
                                    <div class="card border-0 shadow-sm rounded-4 animate__animated animate__fadeInUp">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between mb-2">
                                                <h6 class="fw-bold text-dark mb-0">Soporte Técnico Especializado</h6>
                                                <small class="text-muted"><i class="bi bi-clock me-1"></i>12:05
                                                    PM</small>
                                            </div>
                                            <p class="text-muted small mb-4">Se ha completado la actualización del
                                                servidor principal siguiendo los protocolos establecidos.</p>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-primary btn-sm rounded-pill px-4">Ver
                                                    Detalle</button>
                                                <button
                                                    class="btn btn-light btn-sm rounded-pill px-4 text-danger">Archivar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-entry mb-5 position-relative">
                                    <div class="entry-icon position-absolute bg-info text-white shadow"
                                        style="left: -42px; top: 0; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 2;">
                                        <i class="bi bi-person-check fs-6"></i>
                                    </div>
                                    <div class="card border-0 shadow-sm rounded-4 animate__animated animate__fadeInUp"
                                        style="animation-delay: 0.1s;">
                                        <div class="card-body p-3 ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="./img/avatar.jpg" class="rounded-circle me-3"
                                                    style="width: 38px; height: 38px;">
                                                <div class="flex-grow-1">
                                                    <p class="mb-0 text-dark small"><span
                                                            class="fw-bold text-primary">Mariana Arcos</span> aceptó tu
                                                        invitación de colaboración.</p>
                                                    <small class="text-muted opacity-75">Hace 5 minutos</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-entry mb-5 position-relative">
                                    <div class="entry-icon position-absolute bg-warning text-white shadow"
                                        style="left: -42px; top: 0; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 2;">
                                        <i class="bi bi-chat-left-dots fs-6"></i>
                                    </div>
                                    <div class="card border-0 shadow-sm rounded-4 animate__animated animate__fadeInUp"
                                        style="animation-delay: 0.2s;">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between mb-3">
                                                <h6 class="fw-bold text-dark mb-0">Nuevo comentario de <span
                                                        class="text-warning">Ragueb Chain</span></h6>
                                                <small class="text-muted">Hace 27 mins</small>
                                            </div>
                                            <p
                                                class="bg-light p-3 rounded-3 text-muted small fst-italic mb-3 border-start border-3 border-warning">
                                                "Excelente artículo sobre optimización de kernels, me ha servido mucho
                                                para el proyecto actual."
                                            </p>
                                            <button
                                                class="btn btn-link btn-sm p-0 text-decoration-none text-muted">Contestar
                                                ahora <i class="bi bi-arrow-right"></i></button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="time-stamp mb-5 mt-4 animate__animated animate__fadeIn">
                                <span class="bg-success text-white px-4 py-2 rounded-pill fw-bold shadow-sm">3 Ene
                                    2025</span>
                            </div>

                            <div
                                class="timeline-group border-start border-3 border-light ms-4 ps-4 mb-5 position-relative">
                                <div class="timeline-entry mb-2 position-relative">
                                    <div class="entry-icon position-absolute bg-dark text-white shadow"
                                        style="left: -42px; top: 0; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 2;">
                                        <i class="bi bi-camera-fill fs-6"></i>
                                    </div>
                                    <div class="card border-0 shadow-sm rounded-4 animate__animated animate__fadeInUp">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between mb-3">
                                                <h6 class="fw-bold text-dark mb-0"><span class="text-dark">Karla
                                                        Ortiz</span> subió nuevas fotos</h6>
                                                <small class="text-muted">Hace 2 días</small>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <img src="./img/sample.webp"
                                                    class="rounded-3 shadow-sm hover-grow transition-all"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                                <img src="./img/sample.webp"
                                                    class="rounded-3 shadow-sm hover-grow transition-all"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                                <img src="./img/sample.webp"
                                                    class="rounded-3 shadow-sm hover-grow transition-all"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                                <img src="./img/sample.webp"
                                                    class="rounded-3 shadow-sm hover-grow transition-all"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-end mt-4 ms-4 ps-1">
                                <div class="bg-light rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;">
                                    <i class="bi bi-clock text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Tareas Estilo Dashboard -->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mt-4 animate__animated animate__fadeInUp"
            style="animation-delay: 0.3s;">
            <div
                class="card-header bg-white py-4 px-4 border-bottom-0 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Centro de Tareas y Pendientes</h5>
                    <p class="text-muted small mb-0">Gestión de flujo de trabajo en tiempo real</p>
                </div>
                <div
                    class="badge bg-success bg-opacity-10 text-success rounded-pill px-4 py-2 border border-success border-opacity-10 fw-bold">
                    <i class="bi bi-check-circle-fill me-2"></i>Todo bajo control
                </div>
            </div>

            <div class="card-body p-0 pb-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr class="text-muted small">
                                <th class="ps-4 border-0 py-3" style="width: 50px;">REF</th>
                                <th class="border-0">DESCRIPCIÓN DE LA TAREA</th>
                                <th class="border-0" style="width: 250px;">PROGRESO ACTUAL</th>
                                <th class="border-0 text-center" style="width: 120px;">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Tarea 1 -->
                            <tr class="transition-all hover-grow">
                                <td class="ps-4 text-muted fw-bold small">T-01</td>
                                <td>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="bg-danger bg-opacity-10 p-2 rounded-3 me-3">
                                            <i class="bi bi-code-square text-danger fs-5"></i>
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold text-dark">Refactorización del Core de
                                                Seguridad</span>
                                            <small class="text-muted">Prioridad Alta • Asignado por TI</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 rounded-pill bg-light" style="height: 8px;">
                                            <div class="progress-bar bg-danger rounded-pill shadow-sm"
                                                style="width: 55%"></div>
                                        </div>
                                        <span class="ms-3 fw-bold text-danger fst-italic small">55%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3 py-2 border border-danger border-opacity-25">Crítico</span>
                                </td>
                            </tr>
                            <!-- Tarea 2 -->
                            <tr class="transition-all hover-grow">
                                <td class="ps-4 text-muted fw-bold small">T-02</td>
                                <td>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-3">
                                            <i class="bi bi-database-check text-warning-emphasis fs-5"></i>
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold text-dark">Depuración de Base de Datos
                                                Legacy</span>
                                            <small class="text-muted">Mantenimiento Mensual</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 rounded-pill bg-light" style="height: 8px;">
                                            <div class="progress-bar bg-warning rounded-pill shadow-sm"
                                                style="width: 70%"></div>
                                        </div>
                                        <span class="ms-3 fw-bold text-warning-emphasis fst-italic small">70%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge rounded-pill bg-warning bg-opacity-10 text-warning-emphasis px-3 py-2 border border-warning border-opacity-25">En
                                        curso</span>
                                </td>
                            </tr>
                            <!-- Tarea 3 -->
                            <tr class="transition-all hover-grow">
                                <td class="ps-4 text-muted fw-bold small">T-03</td>
                                <td>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                            <i class="bi bi-clock-history text-primary fs-5"></i>
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold text-dark">Optimización de Cronjobs del
                                                Sistema</span>
                                            <small class="text-muted">Mejora de Rendimiento</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 rounded-pill bg-light" style="height: 8px;">
                                            <div class="progress-bar bg-primary rounded-pill shadow-sm"
                                                style="width: 30%"></div>
                                        </div>
                                        <span class="ms-3 fw-bold text-primary fst-italic small">30%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-2 border border-primary border-opacity-25">Iniciado</span>
                                </td>
                            </tr>
                            <!-- Tarea 4 -->
                            <tr class="transition-all hover-grow">
                                <td class="ps-4 text-muted fw-bold small">T-04</td>
                                <td>
                                    <div class="d-flex align-items-center py-2">
                                        <div class="bg-success bg-opacity-10 p-2 rounded-3 me-3">
                                            <i class="bi bi-bug text-success fs-5"></i>
                                        </div>
                                        <div>
                                            <span class="d-block fw-bold text-dark">Auditoría de Bugs y Soluciones
                                                UI</span>
                                            <small class="text-muted">Fase Final de QA</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 rounded-pill bg-light" style="height: 8px;">
                                            <div class="progress-bar bg-success rounded-pill shadow-sm"
                                                style="width: 90%"></div>
                                        </div>
                                        <span class="ms-3 fw-bold text-success fst-italic small">90%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2 border border-success border-opacity-25">Cerrando</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">Mostrando 4 tareas activas del ciclo actual</small>
                <nav>
                    <ul class="pagination pagination-sm m-0 shadow-none border-0 gap-1">
                        <li class="page-item disabled"><a class="page-link border-0 rounded-circle text-muted"
                                href="#"><i class="bi bi-chevron-left"></i></a></li>
                        <li class="page-item active"><a
                                class="page-link border-0 rounded-circle bg-primary text-white px-3" href="#">1</a></li>
                        <li class="page-item"><a class="page-link border-0 rounded-circle bg-light text-dark px-3"
                                href="#">2</a></li>
                        <li class="page-item"><a class="page-link border-0 rounded-circle bg-light text-dark px-3"
                                href="#">3</a></li>
                        <li class="page-item"><a class="page-link border-0 rounded-circle text-primary" href="#"><i
                                    class="bi bi-chevron-right"></i></a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos Premium para esta Vista */
    :root {
        --bs-primary: #0d6efd;
        --bs-primary-rgb: 13, 110, 253;
    }

    .hover-shadow {
        transition: box-shadow 0.3s ease;
    }

    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .hover-grow:hover {
        transform: scale(1.02);
    }

    .btn-hover-shine {
        position: relative;
        overflow: hidden;
    }

    .btn-hover-shine::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(to bottom right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.1) 100%);
        transform: rotate(45deg);
        transition: all 0.3s ease;
    }

    .btn-hover-shine:hover::after {
        left: 50%;
        top: 50%;
    }

    .nav-tabs .nav-link:hover {
        color: var(--bs-primary) !important;
    }

    .nav-tabs .nav-link.active {
        border-bottom-color: var(--bs-primary) !important;
        color: var(--bs-primary) !important;
    }

    .timeline-icon {
        transition: transform 0.3s ease;
    }

    .timeline-entry:hover .entry-icon {
        transform: scale(1.2);
    }

    .smaller {
        font-size: 0.75rem;
    }

    /* Mejora de la barra de búsqueda */
    .search-box-premium input:focus {
        box-shadow: none;
        background: #fff;
    }

    /* Bordes de tablas redondeados */
    .table-responsive {
        border-radius: 0 0 1rem 1rem;
    }

    /* Animación para barras de progreso */
    .progress-bar {
        transition: width 1.5s cubic-bezier(0.1, 0.7, 1.0, 0.1);
    }
</style>

<script>
    $(function () {
        // Inicializar Scripts de Búsqueda Ragnos Originales
        RagnosSearch.setupSimpleSearch($('#editusuario'), 'admin/testusuarios', {}, function (e) {
            let datos = e.data('searchdata');
            if (datos && datos.Nombre) {
                e.val(datos.Nombre);
                e.data('usu_id', datos.usu_id || null);
                showToast('Usuario encontrado: ' + datos.Nombre, 'info');
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

        // Eventos Premium
        $('#btnSeguir').click(function (e) {
            e.preventDefault();
            const $btn = $(this);
            Swal.fire({
                title: '¿Seguir a este usuario?',
                text: "Recibirás notificaciones sobre sus actividades.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check2 me-1"></i> Sí, seguir',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'rounded-pill px-4',
                    cancelButton: 'rounded-pill px-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $btn.html('<i class="bi bi-check-lg me-1"></i> Siguiendo').removeClass('btn-primary').addClass('btn-success');
                    showToast('Ahora sigues a este usuario.', 'success');
                }
            });
        });

        $('#fotoPerfil').click(function () {
            Swal.fire({
                title: 'Cambiar de Avatar',
                text: "Próximamente podrás personalizar tu imagen de perfil.",
                icon: 'info',
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Entendido',
                customClass: {
                    confirmButton: 'rounded-pill px-4'
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>
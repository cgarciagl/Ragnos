<!DOCTYPE html>
<html lang="ES" translate="no" class="notranslate">

<head>
    <?php

    use App\ThirdParty\Ragnos\Controllers\Ragnos;

    ?>
    <title><?= Ragnos::config()->Ragnos_application_title; ?></title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="google" content="notranslate" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="googlebot" content="noindex">
    <base href="<?= base_url(); ?>">
    <meta name="author" content="Carlos García Trujillo">
    <link rel="icon" type="image/png" href="<?= base_url(); ?>/img/favicon.webp" />

    <?php Ragnos::getHeaderAll(); ?>

    <script src="assets/js/custom.js"></script>

</head>

<body class="layout-fixed sidebar-expand-lg sidebar-mini sidebar-collapse bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Navbar -->
        <nav class="app-header navbar navbar-expand bg-body">
            <?= $this->include('template/topbar') ?>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <?= $this->include('template/sidebar') ?>
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <main class="app-main">
            <!-- Content Header (Page header) -->
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-12 col-sm-12 col-md-12">
                            <h1 class="m-0 text-dark"><?= (isset($tituloVentana)) ? $tituloVentana : ''; ?></h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <div id="contenedorPrincipal">
                                <div>
                                    <?= $this->renderSection('content') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <footer class="app-footer">
            <!-- To the right -->
            <div class="float-right d-none d-sm-block-down">

            </div>

            <strong>Copyright &copy; 2025</strong>
        </footer>
    </div>

</body>

</html>
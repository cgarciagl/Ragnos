<!DOCTYPE html>
<?php
$ragnosConfig = config('RagnosConfig');
$lang         = explode('_', $ragnosConfig->locale)[0];
?>
<html lang="<?= $lang ?>" translate="no" class="notranslate">

<head>
    <?php

    use App\ThirdParty\Ragnos\Controllers\Ragnos;

    ?>
    <title>
        <?= $ragnosConfig->Ragnos_all_to_uppercase ? strtoupper($ragnosConfig->Ragnos_application_title) : $ragnosConfig->Ragnos_application_title; ?>
    </title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="google" content="notranslate" />
    <meta name="theme-color" content="<?= $ragnosConfig->Ragnos_theme_color ?>">
    <meta name="googlebot" content="noindex">
    <base href="<?= base_url(); ?>">
    <meta name="author" content="Carlos García Trujillo">
    <link rel="icon" type="image/png" href="<?= base_url(); ?>/img/favicon.webp" />

    <!-- Anti-flicker dark mode script -->
    <script>
        (function () {
            const theme = localStorage.getItem('ragnos-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <?php Ragnos::getHeaderAll(); ?>

    <script>
        window.RagnosConfig = {
            currency: '<?= $ragnosConfig->currency ?>',
            locale: '<?= $ragnosConfig->locale ?>',
            themeColor: '<?= $ragnosConfig->Ragnos_theme_color ?>'
        };
    </script>
    <script src="assets/js/custom.js?v=<?= filemtime(FCPATH . 'assets/js/custom.js') ?>"></script>
</head>

<body class="layout-top-nav bg-body-tertiary min-vh-100 d-flex flex-column">
    <div class="app-wrapper flex-grow-1 d-flex flex-column">
        <!-- Top Navbar -->
        <nav class="app-header navbar navbar-expand-lg bg-body border-bottom shadow-sm sticky-top py-2">
            <?= $this->include('template/topbar') ?>
        </nav>

        <!-- Main Content Area -->
        <main class="app-main flex-grow-1 py-3 py-md-4">
            <div class="container-fluid px-3 px-lg-4">
                <?php if (isset($tituloVentana) && !empty($tituloVentana)): ?>
                    <div class="row mb-3">
                        <div class="col-12">
                            <h1 class="h3 mb-0 fw-bold text-body"><?= $tituloVentana; ?></h1>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="contenedorPrincipal">
                    <?= $this->renderSection('content') ?>
                </div>
            </div>
        </main>

        <!-- Main Footer -->
        <footer class="app-footer mt-auto py-3 bg-body border-top text-center text-md-start">
            <div
                class="container-fluid px-3 px-lg-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div class="text-body-secondary small">
                    <?= $ragnosConfig->Ragnos_footer_text ?>
                </div>
                <div class="text-body-secondary small">
                    Ragnos Framework v<?= CodeIgniter\CodeIgniter::CI_VERSION ?>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>

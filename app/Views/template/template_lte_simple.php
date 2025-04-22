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

</head>

<body class="login-page bg-body-secondary">

    <?= $this->renderSection('content') ?>

</body>

</html>
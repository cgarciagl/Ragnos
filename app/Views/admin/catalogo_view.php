<?= $this->extend('template/template_lte') ?>

<?= $this->section('content') ?>

<div class="row">
    <div id="catcontainer" class="col-md-12">

        <?php
        $c       = $controller;
        $class   = "App\\Controllers\\" . $c;
        $catalog = new $class();
        echo $catalog->renderTable();
        ?>

    </div>
</div>

<?= $this->endSection() ?>
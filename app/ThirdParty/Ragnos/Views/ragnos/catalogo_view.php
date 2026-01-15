<?= $this->extend('template/template_lte') ?>

<?= $this->section('content') ?>

<div class="row">
    <div id="catcontainer" class="col-md-12">
        <?php
        if ($object ?? null) {
            echo $object->renderTable();
        }
        ?>
    </div>
</div>

<?= $this->endSection() ?>
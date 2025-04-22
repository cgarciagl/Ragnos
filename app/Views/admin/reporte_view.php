<?= $this->extend('template/template_lte') ?>

<?= $this->section('content') ?>

<div class="row">
    <div id="catcontainer" class="col-md-12">

        <div class="card">
            <div class="card-body">
                <?= $contenido ?>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
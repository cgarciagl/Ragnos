<?= $this->extend('template/template_lte') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="row">
        <hr />
        <div class="col-6 mx-auto" id="divprogreso">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-gear spinner-border" id="girando"></i>
                        <span id="textotitulo">Procesando...</span>
                    </h3>
                </div>
                <div class="card-body">
                    <h4 id="textopbar">Iniciando proceso...</h4>

                    <div class="progress">
                        <div class="progress-bar" role="progressbar" id="pbar" style="width: 0%;">
                            0%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url(); ?>/assets/js/sse.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Assuming you have a route that handles the SSE endpoint
        const tracker = new SSEProgressTracker('<?= $url ?>');
        tracker.start();
    });
</script>
<?= $this->endSection() ?>
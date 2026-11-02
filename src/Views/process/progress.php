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
        const tracker = new SSEProgressTracker('<?= $url ?>');

        <?php if (isset($requireConfirmation) && $requireConfirmation): ?>
            document.getElementById('textopbar').innerText = '<?= lang('Ragnos.Ragnos_waiting_confirmation') ?>';
            document.getElementById('girando').classList.remove('spinner-border');

            Swal.fire({
                title: '<?= lang('Ragnos.Ragnos_confirmation_title') ?>',
                text: <?= json_encode($confirmationMessage) ?>,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<?= lang('Ragnos.Ragnos_yes') ?>',
                cancelButtonText: '<?= lang('Ragnos.Ragnos_cancel') ?>',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('textopbar').innerText = 'Iniciando proceso...';
                    document.getElementById('girando').classList.add('spinner-border');
                    tracker.start();
                } else {
                    document.getElementById('textopbar').innerText = '<?= lang('Ragnos.Ragnos_process_cancelled') ?>';
                    // Optional: go back
                    setTimeout(() => { if (window.history.length > 1) window.history.back(); else window.close(); }, 1000);
                }
            });
        <?php else: ?>
            tracker.start();
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>
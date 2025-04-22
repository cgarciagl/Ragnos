<?php
$totalrecords = 0;
$grouprecords = 0; ?>


<div class="d-print-none  bg-light shadow-sm" id="barradebotones">
    <div class="row">
        <div class="col-auto">
            <button id="imprimirbtn" class="btn btn-primary btn-sm">
                <?= 'Imprimir'//$this->lang->line('Ragnos_print') ?>
            </button>
        </div>
        <div class="col-auto">
            <button id="exporttoexcel" class="btn btn-success btn-sm">
                Guardar como Documento de Excel
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php echo $yo->generate(); ?>
    </div>
</div>

</div>

<script src="<?= base_url(); ?>/assets/js/printThis.min.js" type="text/javascript"></script>

<script>
    $(document).ready(function () {
        $('#imprimirbtn').click(function () {
            $('#imprimible').printThis({
                debug: false,
                importCSS: true,
                importStyle: false,
                printContainer: false,
                removeInline: true,
                loadCSS: "<?= base_url(); ?>/assets/css/forprint.min.css",
                pageTitle: "<?= $yo->getTitle() ?> <?= uniqid() ?>"
            });
        });

        $('#exporttoexcel').click(function () {
            var dt = new Date();
            var day = dt.getDate();
            var month = dt.getMonth() + 1;
            var year = dt.getFullYear();
            var hour = dt.getHours();
            var mins = dt.getMinutes();
            var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
            exportToExcel('exportado_' + postfix, $('#imprimible').html());
            e.preventDefault();
        });
    });
</script>
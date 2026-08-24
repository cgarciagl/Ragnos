<?php
$totalrecords = 0;
$grouprecords = 0; ?>


<div class="d-print-none bg-body p-3 rounded shadow-sm border mb-3" id="barradebotones">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> <?= lang('Ragnos.Ragnos_back') ?>
            </a>
        </div>
        <div class="d-flex align-items-center">
            <button id="imprimirbtn" class="btn btn-primary me-2">
                <i class="bi bi-printer-fill me-1"></i> <?= lang('Ragnos.Ragnos_print') ?>
            </button>
            <button id="exporttoexcel" class="btn btn-success">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> <?= lang('Ragnos.Ragnos_excel') ?>
            </button>
        </div>
    </div>
</div>

<div class="report-wrapper animate__animated animate__fadeIn" id="imprimible">
    <?php echo $yo->generate(); ?>
</div>



<script>
    onReady(() => {
        document.getElementById('imprimirbtn').addEventListener('click', () => {
            printElement('#imprimible', <?= json_encode($yo->getTitle() . ' ' . uniqid()) ?>);
        });

        document.getElementById('exporttoexcel').addEventListener('click', (event) => {
            var dt = new Date();
            var day = dt.getDate();
            var month = dt.getMonth() + 1;
            var year = dt.getFullYear();
            var hour = dt.getHours();
            var mins = dt.getMinutes();
            var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
            exportToExcel('exportado_' + postfix, document.getElementById('imprimible').innerHTML);
            event.preventDefault();
        });
    });
</script>

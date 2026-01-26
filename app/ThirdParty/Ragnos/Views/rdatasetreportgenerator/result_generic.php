<?= $this->extend('template/template_lte') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="javascript:history.back()" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Modificar Filtros
            </a>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-print me-1"></i> Imprimir / PDF
            </button>
        </div>
    </div>

    <div class="report-wrapper animate__animated animate__fadeIn">
        <?= $reportContent ?>
    </div>
</div>

<style>
    @media print {
        .report-wrapper {
            box-shadow: none !important;
            margin: 0 !important;
        }

        .btn,
        nav,
        header,
        footer {
            display: none !important;
        }

        body {
            background: white !important;
        }
    }
</style>

<?= $this->endSection() ?>
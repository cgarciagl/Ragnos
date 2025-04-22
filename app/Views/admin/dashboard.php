<?= $this->extend('template/template_lte') ?>

<?php $auth = service('Admin_aut'); ?>

<?= $this->section('content') ?>

<main class="app-main"> <!--begin::App Content Header-->
    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Administración</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">

                    </ol>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div>
    <div class="app-content"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Ventas</h3> <a role="button"
                                    class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                                    id="btnVerReporteDeVentas">Ver Reporte</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="position-relative mb-4">
                                <div id="chartventas"></div>
                            </div>
                        </div>
                    </div> <!-- /.card -->

                    <div class="card mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Ventas por línea</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="position-relative mb-4">
                                <div id="chartventasporlinea"></div>
                            </div>
                        </div>
                    </div> <!-- /.card -->

                </div> <!-- /.col-md-6 -->


                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Estados de cuenta</h3>
                                <span class="badge rounded-pill text-bg-info">Clientes con deuda*</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless table-striped table-vcenter"
                                    id="tableclientescondeuda">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Comprado</th>
                                            <th>Pagado</th>
                                            <th>Deuda</th>
                                            <th>Limite de crédito</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($estadosDeCuenta as $item): ?>
                                            <tr>
                                                <td data-customerNumber="<?= $item['customerNumber'] ?>">
                                                    <span class="btn-link ligacliente">
                                                        <?= $item['customerName'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-success"><?= moneyFormat($item['Comprado']) ?></td>
                                                <td class="text-primary"><?= moneyFormat($item['Pagado']) ?></td>
                                                <td class="text-danger"><?= moneyFormat($item['Deuda']) ?></td>
                                                <td><?= moneyFormat($item['LimiteDeCredito']) ?>
                                                    <?php if ($item['Deuda'] > $item['LimiteDeCredito']) {
                                                        echo '<span class="badge rounded-pill text-bg-warning">Sobregiro</span>';
                                                    } elseif ($item['Deuda'] < 0) {
                                                        echo '<span class="badge rounded-pill text-bg-success">Saldo a favor</span>';
                                                    } ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <script>
                                    $(function () {
                                        $('.ligacliente').on('click', function () {
                                            let cliente = $(this).text().trim();
                                            redirectByPost('<?= base_url('/catalogos/clientes') ?>', { RagnosPreSearch: cliente }, false);
                                        }).css('cursor', 'pointer').css('color', 'blue').css('text-decoration', 'underline');
                                    });
                                </script>
                            </div>
                        </div>
                    </div> <!-- /.card -->

                </div> <!-- /.col-md-6 -->
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div> <!--end::App Content-->
</main> <!--end::App Main--> <!--begin::Footer-->



<script src="https://cdn.jsdelivr.net/npm/apexcharts@4.1.0/dist/apexcharts.min.js" crossorigin="anonymous"></script>
<script>
    $(function () {
        ponTotalesEnTabla($('#tableclientescondeuda'), true, true);
    });

    let ventasultimos12meses = <?= json_encode($ventasultimos12meses) ?>;
    let meses = ventasultimos12meses.map(item => item.Mes).reverse();

    let ventasporlinea = <?= json_encode($ventasporlinea) ?>;

    <?php use App\ThirdParty\Ragnos\Controllers\Ragnos; ?>
    let currency = '<?= Ragnos::config()->currency ?? 'USD' ?>';

    let lineas = ventasporlinea.reduce((acc, item) => {
        if (!acc[item.productLine]) {
            acc[item.productLine] = { name: item.productLine, data: Array(meses.length).fill(0) };
        }
        let mesIndex = meses.indexOf(item.Mes);
        if (mesIndex !== -1) {
            acc[item.productLine].data[mesIndex] = item.Total;
        }
        return acc;
    }, {});

    let serieslineas = Object.values(lineas);

    const chartOptions = (title, series, onclick) => ({
        series: series,
        chart: {
            height: 350,
            type: 'line',
            zoom: { enabled: false },
            dropShadow: {
                enabled: true,
                color: '#000',
                top: 18,
                left: 7,
                blur: 10,
                opacity: 0.5
            },
            events: {
                click: onclick
            }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        title: { text: title, align: 'left' },
        grid: {
            row: {
                colors: ['#f3f3f3', 'transparent'],
                opacity: 0.5
            },
        },
        yaxis: {
            labels: {
                formatter: value => moneyFormat(value, currency)
            },
        },
        xaxis: { categories: meses },
    });

    new ApexCharts(document.querySelector("#chartventas"), chartOptions('Ventas últimos 12 meses',
        [{
            name: "Ventas",
            data: ventasultimos12meses.map(item => item.Total).reverse(),
        }],
        function (event, chartContext, opts) {
            let mes = opts.globals.categoryLabels[opts.dataPointIndex];
            if (mes) {
                let ventasPorLinea = serieslineas.map(linea => {
                    let ventas = linea.data[opts.dataPointIndex];
                    return `<tr><td>${linea.name}</td><td>${moneyFormat(ventas, currency)}</td></tr>`;
                }).join('');
                Swal.fire({
                    title: `Ventas por línea para el mes de ${mes}`,
                    html: `<table class="table table-hover table-borderless table-striped table-vcenter">
                        <thead>
                            <tr>
                                <th>Línea</th>
                                <th>Ventas</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${ventasPorLinea}
                        </tbody>
                       </table>`,
                    showCloseButton: true,
                    showConfirmButton: false,
                })
            }
        })).render();

    new ApexCharts(document.querySelector("#chartventasporlinea"), chartOptions('Ventas por línea', serieslineas)).render();

    $('#btnVerReporteDeVentas').on('click', (e) => {
        e.preventDefault();
        let tabla = convertToTable(ventasultimos12meses);
        tabla = tabla.replace(/<td>([^<]+)<\/td>/g, (match, p1) => {
            if (p1.match(/^\d+(\.\d+)?$/)) {
                return `<td>${moneyFormat(p1, currency)}</td>`;
            }
            return match;
        });
        Swal.fire({
            title: 'Ventas últimos 12 meses',
            html: tabla,
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Imprimir',
            showCancelButton: true,
            cancelButtonText: 'Aceptar'
        }).then((result) => {
            if (result.isConfirmed) {
                redirectByPost('tienda/reportes/ventaspormes', {}, false);
            }
        });
    });

</script>

<?= $this->endSection() ?>
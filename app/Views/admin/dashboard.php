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
                <?php
                if (!empty($datosinfobox) && is_array($datosinfobox)) {
                    $datos = $datosinfobox[0];
                } else {
                    $datos = [
                        'VentasUltimoSemestre'        => '0.00',
                        'OrdenesEnviadasSemestre'     => 0,
                        'ValorPromedioOrdenSemestral' => '0.00',
                        'MargenPromedioSemestral'     => '0.00'
                    ];
                }
                ?>

                <style>
                    .custom-info-card {
                        background-color: #fff;
                        border: 1px solid #e3e6f0;
                        /* Borde muy ligero */
                        border-radius: 0.5rem;
                        /* Esquinas redondeadas */
                        display: flex;
                        align-items: center;
                        padding: 1rem;
                        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.05);
                        /* Sombra ligera */
                        height: 100%;
                        /* Asegura que todas las tarjetas tengan la misma altura */
                    }

                    .icon-container {
                        width: 60px;
                        /* Tamaño fijo para el cuadrado del icono */
                        height: 60px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        border-radius: 0.25rem;
                        font-size: 2rem;
                        color: #fff;
                        /* Icono blanco */
                        margin-right: 1rem;
                        flex-shrink: 0;
                    }

                    .text-content h3 {
                        margin-bottom: 0.1rem;
                        font-weight: 700;
                    }

                    .text-content p {
                        margin: 0;
                        font-size: 0.9rem;
                        color: #6c757d;
                        /* Texto secundario gris */
                    }
                </style>


                <div class="row">
                    <div class="col-lg-3 col-6 mb-4">
                        <div class="custom-info-card">
                            <div class="icon-container" style="background-color: #007bff;">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="text-content">
                                <p>Ventas (Último Semestre)</p>
                                <h3>$<?php echo esc($datos['VentasUltimoSemestre']); ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6 mb-4">
                        <div class="custom-info-card">
                            <div class="icon-container" style="background-color: #28a745;">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div class="text-content">
                                <p>Margen Bruto (Último Semestre)</p>
                                <h3><?php echo esc($datos['MargenPromedioSemestral']); ?><sup
                                        style="font-size: 1rem">%</sup></h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6 mb-4">
                        <div class="custom-info-card">
                            <div class="icon-container" style="background-color: #3f51b5;">
                                <i class="bi bi-bag-check"></i>
                            </div>
                            <div class="text-content">
                                <p>Valor Promedio Orden (Semestre)</p>
                                <h3>$<?php echo esc($datos['ValorPromedioOrdenSemestral']); ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6 mb-4">
                        <div class="custom-info-card">
                            <div class="icon-container" style="background-color: #6c757d;">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div class="text-content">
                                <p>Órdenes Enviadas (Semestre)</p>
                                <h3><?php echo esc($datos['OrdenesEnviadasSemestre']); ?></h3>
                            </div>
                        </div>
                    </div>

                </div>
            </div>


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

                    <div class="card mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Empleados con más ventas en los últimos 3 meses</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="position-relative mb-4">
                                <table class="table table-hover table-borderless table-striped table-vcenter"
                                    id="tableEmpleadosMasVentas">
                                    <thead>
                                        <tr>
                                            <th>Número de empleado</th>
                                            <th>Empleado</th>
                                            <th>Oficina</th>
                                            <th>Ventas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($empleadosConMasVentasEnElUltimoTrimestre as $empleado): ?>
                                            <tr>
                                                <td> <span class="btn-link ligaempleado">
                                                        <?= $empleado['employeeNumber'] ?>
                                                    </span>
                                                </td>
                                                <td><?= $empleado['Empleado'] ?></td>
                                                <td><?= $empleado['Oficina'] ?></td>
                                                <td class="text-success">
                                                    <?= moneyFormat($empleado['TotalVentasTrimestre']) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <script>
                                    $(function () {
                                        $('.ligaempleado').on('click', function () {
                                            let empleado = $(this).text().trim();
                                            redirectByPost('<?= site_url('/catalogos/empleados') ?>', {
                                                sSearch: empleado
                                            }, false);
                                        });
                                    });
                                </script>

                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Margen de ganancia por línea en los últimos 6 meses</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless table-striped table-vcenter"
                                    id="tableMargenPorLinea">
                                    <thead>
                                        <tr>
                                            <th>Línea</th>
                                            <th>Margen Total</th>
                                            <th>Margen %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($margenDeGananciaPorLinea as $linea): ?>
                                            <tr>
                                                <td>
                                                    <span class="btn-link ligalinea"><?= $linea['productLine'] ?></span>
                                                </td>
                                                <td class="text-success"><?= moneyFormat($linea['MargenTotal']) ?></td>
                                                <td class="text-success"> <?= $linea['PorcentajeMargen'] ?> % </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <script>
                                    $(function () {
                                        $('.ligalinea').on('click', function () {
                                            let linea = $(this).text().trim();
                                            redirectByPost('<?= site_url('/catalogos/lineas') ?>', { sSearch: linea }, false);
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>

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
                                                <td>
                                                    <?= moneyFormat($item['LimiteDeCredito']) ?>
                                                    <?php if ($item['Deuda'] > $item['LimiteDeCredito']): ?>
                                                        <span class="badge rounded-pill text-bg-warning">Sobregiro</span>
                                                    <?php elseif ($item['Deuda'] < 0): ?>
                                                        <span class="badge rounded-pill text-bg-success">Saldo a favor</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <script>
                                    $(function () {
                                        $('.ligacliente').on('click', function () {
                                            let cliente = $(this).text().trim();
                                            redirectByPost('<?= site_url('/catalogos/clientes') ?>', { sSearch: cliente }, false);
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div> <!-- /.card -->

                    <div class="card mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Productos de menor rotación en los últimos 6 meses</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless table-striped table-vcenter"
                                    id="tableProductosMenorRotacion">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Línea</th>
                                            <th>Cantidad en stock</th>
                                            <th>Ventas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productosConMenorRotacion as $producto): ?>
                                            <tr>
                                                <td>
                                                    <span
                                                        class="btn-link ligaproducto"><?= $producto['productCode'] ?></span>
                                                </td>
                                                <td><?= $producto['productName'] ?></td>
                                                <td><?= $producto['productLine'] ?></td>
                                                <td><?= $producto['quantityInStock'] ?></td>
                                                <td class="text-danger">
                                                    <?= moneyFormat($producto['TotalVendidoUltimos6Meses']) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <script>
                                    $(function () {
                                        $('.ligaproducto').on('click', function () {
                                            let codigo = $(this).text().trim();
                                            redirectByPost('<?= site_url('/catalogos/productos') ?>', { sSearch: codigo }, false);
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>

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

<style>
    .btn-link {
        cursor: pointer;
        color: blue;
        text-decoration: underline;
    }

    .card-title {
        font-weight: bold;
    }
</style>

<?= $this->endSection() ?>
<?= $this->extend('template/template_lte') ?>

<?php $auth = service('Admin_aut'); ?>

<?= $this->section('content') ?>

<script src="<?= base_url(); ?>/assets/js/echarts/echarts.min.js" type="text/javascript"></script>
<script src="<?= base_url(); ?>/assets/js/echarts/world.js" type="text/javascript"></script>

<link rel="stylesheet" href="<?= base_url(); ?>/assets/css/dashboard.css?v=<?= time(); ?>">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-body animate__animated animate__fadeInDown">Administración</h3>
        <p class="text-muted small mb-0">Panel de control y métricas clave del sistema</p>
    </div>
</div>

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

    <div class="row">
        <div class="col-lg-3 col-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
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

        <div class="col-lg-3 col-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <div class="custom-info-card">
                <div class="icon-container" style="background-color: #28a745;">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="text-content">
                    <p>Margen Bruto (Último Semestre)</p>
                    <h3><?php echo esc($datos['MargenPromedioSemestral']); ?><sup style="font-size: 1rem">%</sup></h3>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
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

        <div class="col-lg-3 col-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
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
    <div class="col-lg-5 animate__animated animate__fadeInLeft" style="animation-delay: 0.6s;">
        <div class="card card-success card-outline mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title">Ventas</h3>
                <div class="card-tools ms-auto">
                    <a role="button"
                        class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover me-2"
                        id="btnVerReporteDeVentas">Ver Reporte</a>
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-4">
                    <div id="chartventas"></div>
                </div>
            </div>
        </div> <!-- /.card -->

        <div class="card card-info card-outline mb-4 animate__animated animate__fadeInLeft"
            style="animation-delay: 0.7s;">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title">Ventas por línea</h3>
                <div class="card-tools ms-auto">
                    <a href="<?= site_url('tienda/reportes/ventasporlinea') ?>"
                        class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover me-2">Ver
                        Reporte</a>
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-4">
                    <div id="chartventasporlinea"></div>
                </div>
            </div>
        </div> <!-- /.card -->

        <div class="card card-success card-outline mb-4 animate__animated animate__fadeInLeft"
            style="animation-delay: 0.8s;">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title">Empleados con más ventas en los últimos 3 meses</h3>
                <div class="card-tools ms-auto">
                    <a href="<?= site_url('tienda/reportes/mejoresempleados') ?>"
                        class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover me-2">Ver
                        Reporte</a>
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-4">
                    <table class="table table-hover table-borderless table-striped table-vcenter table-sm"
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

                    <div id="chartEmpleadosRanking" style="height: 300px;"></div>

                    <script>
                        $(function () {
                            $('.ligaempleado').on('click', function () {
                                let empleado = $(this).text().trim();
                                redirectByPost('<?= site_url('/tienda/empleados') ?>', {
                                    sSearch: empleado
                                }, false);
                            });
                        });
                    </script>

                </div>
            </div>
        </div>

        <div class="card card-primary card-outline mb-4 animate__animated animate__fadeInLeft"
            style="animation-delay: 0.9s;">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title">Margen de ganancia por línea en los últimos 6 meses</h3>
                <div class="card-tools ms-auto">
                    <a href="<?= site_url('tienda/reportes/margenporlinea') ?>"
                        class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover me-2">Ver
                        Reporte</a>
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover table-borderless table-striped table-vcenter table-sm"
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
                <hr>
                <div id="chartMargenDona" style="height: 400px;"></div>

                <script>
                    $(function () {
                        $('.ligalinea').on('click', function () {
                            let linea = $(this).text().trim();
                            redirectByPost('<?= site_url('/tienda/lineas') ?>', { sSearch: linea }, false);
                        });
                    });
                </script>
            </div>
        </div>

    </div> <!-- /.col-md-5 -->


    <div class="col-lg-7 animate__animated animate__fadeInRight" style="animation-delay: 0.6s;">
        <div class="card card-warning card-outline mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title">Estados de cuenta</h3>
                <div class="card-tools ms-auto d-flex align-items-center">
                    <span class="badge rounded-pill text-bg-info me-2">Clientes con deuda*</span>
                    <a href="<?= site_url('tienda/reportes/estadosdecuenta') ?>"
                        class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover me-2">Ver
                        Reporte</a>
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-striped table-vcenter table-sm"
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
                </div>
                <div id="chartParetoDeuda" style="height: 400px;"></div>
                <script>
                    $(function () {
                        $('.ligacliente').on('click', function () {
                            let cliente = $(this).text().trim();
                            redirectByPost('<?= site_url('/tienda/clientes') ?>', { sSearch: cliente }, false);
                        });
                    });
                </script>

            </div>
        </div> <!-- /.card -->

        <div class="card card-warning card-outline mb-4 animate__animated animate__fadeInRight"
            style="animation-delay: 0.7s;">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title">Productos de menor rotación en los últimos 6 meses</h3>
                <div class="card-tools ms-auto">
                    <a href="<?= site_url('tienda/reportes/menorrotacion') ?>"
                        class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover me-2">Ver
                        Reporte</a>
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-striped table-vcenter table-sm"
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
                                        <span class="btn-link ligaproducto"><?= $producto['productCode'] ?></span>
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
                </div>
                <script>
                    $(function () {
                        $('.ligaproducto').on('click', function () {
                            let codigo = $(this).text().trim();
                            redirectByPost('<?= site_url('/tienda/productos') ?>', { sSearch: codigo }, false);
                        });
                    });
                </script>

                <div id="chartInventarioMuerto" style="height: 350px;"></div>

            </div>
        </div>

    </div> <!-- /.col-md-7 -->
</div> <!--end::Row-->

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- 1. CONFIGURACIÓN GENERAL Y UTILIDADES ---

        // Ejecutar funciones de tablas existentes (Totales y limpieza)
        $(function () {
            ponTotalesEnTabla($('#tableclientescondeuda'));
            quitaTotaldeColumna($('#tableclientescondeuda'), [1, 2, 4]);

            ponTotalesEnTabla($('#tableEmpleadosMasVentas'));
            quitaTotaldeColumna($('#tableEmpleadosMasVentas'), [1, 2]);
            ponTotalesEnTabla($('#tableProductosMenorRotacion'));
            quitaTotaldeColumna($('#tableProductosMenorRotacion'), [1, 2]);
            ponTotalesEnTabla($('#tableMargenPorLinea'));
            quitaTotaldeColumna($('#tableMargenPorLinea'), 2);
        });

        // Obtención de datos desde PHP
        let ventasultimos12meses = <?= json_encode($ventasultimos12meses) ?>;
        // Invertimos para que sea cronológico (Ene -> Dic)
        let meses = ventasultimos12meses.map(item => item.Mes).reverse();
        let datosVentasTotales = ventasultimos12meses.map(item => item.Total).reverse();

        let ventasporlinea = <?= json_encode($ventasporlinea) ?>;

        <?php use App\ThirdParty\Ragnos\Controllers\Ragnos; ?>
        let currency = '<?= Ragnos::config()->currency ?? 'USD' ?>';

        // Procesamiento de datos para Ventas por Línea
        let lineasMap = ventasporlinea.reduce((acc, item) => {
            if (!acc[item.productLine]) {
                acc[item.productLine] = {
                    name: item.productLine,
                    type: 'line',
                    smooth: true,
                    data: Array(meses.length).fill(0)
                };
            }
            let mesIndex = meses.indexOf(item.Mes);
            if (mesIndex !== -1) {
                acc[item.productLine].data[mesIndex] = parseFloat(item.Total);
            }
            return acc;
        }, {});

        let serieslineas = Object.values(lineasMap);

        // Variables de instancias de gráficos
        let chartVentas, chartLineas, chartMargen, chartEmp, chartInv, chartPareto;

        function initAllCharts() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const theme = isDark ? 'dark' : null;

            // Disponer instancias previas si existen
            [chartVentas, chartLineas, chartMargen, chartEmp, chartInv, chartPareto].forEach(c => {
                if (c && typeof c.dispose === 'function') {
                    c.dispose();
                }
            });

            const commonGrid = {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            };

            const commonTooltip = {
                trigger: 'axis',
                backgroundColor: isDark ? '#1e293b' : '#ffffff',
                borderColor: isDark ? '#334155' : '#e2e8f0',
                textStyle: { color: isDark ? '#f8fafc' : '#1e293b' },
                formatter: function (params) {
                    let tooltipResult = params[0].axisValueLabel + '<br/>';
                    params.forEach(item => {
                        let val = typeof moneyFormat === 'function' ? moneyFormat(item.value, currency) : item.value;
                        tooltipResult += `${item.marker} ${item.seriesName}: <b>${val}</b><br/>`;
                    });
                    return tooltipResult;
                }
            };

            // --- GRÁFICA 1: VENTAS ÚLTIMOS 12 MESES ---
            const chartVentasDom = document.getElementById('chartventas');
            if (chartVentasDom) {
                chartVentasDom.style.height = '350px';
                chartVentas = echarts.init(chartVentasDom, theme);
                const optionVentas = {
                    backgroundColor: 'transparent',
                    title: { text: 'Ventas últimos 12 meses', textStyle: { fontSize: 13 } },
                    tooltip: commonTooltip,
                    grid: commonGrid,
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: meses
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: {
                            formatter: function (value) {
                                return typeof moneyFormat === 'function' ? moneyFormat(value, currency) : value;
                            }
                        }
                    },
                    series: [{
                        name: 'Ventas',
                        type: 'line',
                        smooth: true,
                        data: datosVentasTotales,
                        itemStyle: { color: '#3b82f6' },
                        areaStyle: { opacity: 0.12 }
                    }]
                };
                chartVentas.setOption(optionVentas);
                chartVentas.on('click', function (params) {
                    let mes = params.name;
                    let index = params.dataIndex;
                    if (mes) {
                        let ventasPorLineaHTML = serieslineas.map(linea => {
                            let ventas = linea.data[index];
                            return `<tr><td>${linea.name}</td><td>${moneyFormat(ventas, currency)}</td></tr>`;
                        }).join('');

                        Swal.fire({
                            title: `Ventas por línea para el mes de ${mes}`,
                            html: `<table class="table table-hover table-borderless table-striped table-vcenter table-sm">
                                <thead>
                                    <tr>
                                        <th>Línea</th>
                                        <th>Ventas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${ventasPorLineaHTML}
                                </tbody>
                               </table>`,
                            showCloseButton: true,
                            showConfirmButton: false,
                        });
                    }
                });
            }

            // --- GRÁFICA 2: VENTAS POR LÍNEA ---
            const chartLineasDom = document.getElementById('chartventasporlinea');
            if (chartLineasDom) {
                chartLineasDom.style.height = '350px';
                chartLineas = echarts.init(chartLineasDom, theme);
                const optionLineas = {
                    backgroundColor: 'transparent',
                    title: { text: 'Ventas por línea', textStyle: { fontSize: 13 } },
                    tooltip: commonTooltip,
                    legend: {
                        data: serieslineas.map(s => s.name),
                        bottom: 0,
                        textStyle: { color: isDark ? '#cbd5e1' : '#334155' }
                    },
                    grid: { ...commonGrid, bottom: '12%' },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: meses
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: {
                            formatter: function (value) {
                                return typeof moneyFormat === 'function' ? moneyFormat(value, currency) : value;
                            }
                        }
                    },
                    series: serieslineas
                };
                chartLineas.setOption(optionLineas);
            }

            // --- GRÁFICA 3: DONA DE RENTABILIDAD ---
            const datosMargen = <?= json_encode($margenDeGananciaPorLinea) ?>;
            const chartMargenDom = document.getElementById('chartMargenDona');
            if (chartMargenDom) {
                chartMargen = echarts.init(chartMargenDom, theme);
                const optionMargen = {
                    backgroundColor: 'transparent',
                    title: {
                        text: 'Aportación al Margen Total',
                        left: 'center',
                        textStyle: { fontSize: 13 }
                    },
                    tooltip: {
                        trigger: 'item',
                        backgroundColor: isDark ? '#1e293b' : '#ffffff',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        textStyle: { color: isDark ? '#f8fafc' : '#1e293b' },
                        formatter: function (params) {
                            let val = typeof moneyFormat === 'function' ? moneyFormat(params.value, currency) : params.value;
                            return `${params.name}<br/><b>${val}</b> (${params.percent}%)`;
                        }
                    },
                    series: [
                        {
                            name: 'Margen',
                            type: 'pie',
                            radius: ['40%', '70%'],
                            avoidLabelOverlap: false,
                            itemStyle: {
                                borderRadius: 5,
                                borderColor: isDark ? '#1e293b' : '#ffffff',
                                borderWidth: 2
                            },
                            label: {
                                show: false,
                                position: 'center'
                            },
                            emphasis: {
                                label: {
                                    show: true,
                                    fontSize: 15,
                                    fontWeight: 'bold'
                                }
                            },
                            data: datosMargen.map(item => ({
                                value: parseFloat(item.MargenTotal),
                                name: item.productLine
                            }))
                        }
                    ]
                };
                chartMargen.setOption(optionMargen);
                chartMargen.on('click', function (params) {
                    let linea = params.name;
                    if (linea) {
                        redirectByPost('<?= site_url('/tienda/lineas') ?>', { sSearch: linea }, false);
                    }
                });
            }

            // --- GRÁFICA 4: RANKING DE EMPLEADOS ---
            const datosEmpleados = <?= json_encode($empleadosConMasVentasEnElUltimoTrimestre) ?>;
            datosEmpleados.sort((a, b) => parseFloat(a.TotalVentasTrimestre) - parseFloat(b.TotalVentasTrimestre));
            const chartEmpDom = document.getElementById('chartEmpleadosRanking');
            if (chartEmpDom) {
                chartEmp = echarts.init(chartEmpDom, theme);
                const optionEmp = {
                    backgroundColor: 'transparent',
                    title: { text: '' },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: { type: 'shadow' },
                        backgroundColor: isDark ? '#1e293b' : '#ffffff',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        textStyle: { color: isDark ? '#f8fafc' : '#1e293b' }
                    },
                    grid: {
                        left: '3%',
                        right: '6%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'value',
                        boundaryGap: [0, 0.01],
                        axisLabel: {
                            formatter: function (val) {
                                return val >= 1000 ? (val / 1000) + 'k' : val;
                            }
                        }
                    },
                    yAxis: {
                        type: 'category',
                        data: datosEmpleados.map(item => item.Empleado)
                    },
                    series: [
                        {
                            name: 'Ventas Trimestre',
                            type: 'bar',
                            data: datosEmpleados.map(item => parseFloat(item.TotalVentasTrimestre)),
                            itemStyle: {
                                color: function (params) {
                                    var numItems = datosEmpleados.length;
                                    if (params.dataIndex === numItems - 1) {
                                        return '#22c55e';
                                    }
                                    return '#06b6d4';
                                },
                                borderRadius: [0, 4, 4, 0]
                            },
                            label: {
                                show: true,
                                position: 'right',
                                formatter: function (params) {
                                    return typeof moneyFormat === 'function' ? moneyFormat(params.value, currency) : params.value;
                                },
                                fontSize: 10,
                                color: isDark ? '#cbd5e1' : '#475569'
                            }
                        }
                    ]
                };
                chartEmp.setOption(optionEmp);
                chartEmp.on('click', function (params) {
                    let empleado = params.name;
                    if (empleado) {
                        redirectByPost('<?= site_url('/tienda/empleados') ?>', { sSearch: empleado }, false);
                    }
                });
            }

            // --- GRÁFICA 5: MATRIZ DE INVENTARIO (SCATTER) ---
            const datosInventario = <?= json_encode($productosConMenorRotacion) ?>;
            const chartInvDom = document.getElementById('chartInventarioMuerto');
            if (chartInvDom) {
                chartInv = echarts.init(chartInvDom, theme);
                const optionInv = {
                    backgroundColor: 'transparent',
                    title: {
                        text: 'Matriz de Rotación de Inventario',
                        subtext: 'Relación Stock vs. Ventas',
                        left: 'center',
                        top: 0,
                        textStyle: { fontSize: 13 },
                        subtextStyle: { fontSize: 11, color: isDark ? '#94a3b8' : '#64748b' }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#ffffff',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        textStyle: { color: isDark ? '#f8fafc' : '#1e293b' },
                        formatter: function (params) {
                            return `<b>${params.data[2]}</b><br/>` +
                                `Stock: <b>${params.data[1]}</b> u.<br/>` +
                                `Ventas: <b>${typeof moneyFormat === 'function' ? moneyFormat(params.data[0], currency) : '$' + params.data[0]}</b>`;
                        }
                    },
                    grid: {
                        left: '4%',
                        right: '8%',
                        top: '18%',
                        bottom: '12%',
                        containLabel: true
                    },
                    xAxis: {
                        name: 'Ventas ($)',
                        nameLocation: 'middle',
                        nameGap: 26,
                        type: 'value',
                        splitLine: { show: true, lineStyle: { type: 'dashed', opacity: 0.15 } },
                        axisLabel: {
                            formatter: function (val) {
                                return val >= 1000 ? (val / 1000).toFixed(0) + 'k' : val;
                            }
                        }
                    },
                    yAxis: {
                        name: 'Stock Actual',
                        nameLocation: 'end',
                        type: 'value',
                        splitLine: { show: true, lineStyle: { type: 'dashed', opacity: 0.15 } }
                    },
                    series: [{
                        type: 'scatter',
                        symbolSize: 14,
                        itemStyle: {
                            color: function (params) {
                                if (params.data[1] > 5000 && params.data[0] < 500) return '#ef4444';
                                return '#3b82f6';
                            },
                            opacity: 0.8
                        },
                        data: datosInventario.map(item => [
                            parseInt(item.TotalVendidoUltimos6Meses),
                            parseInt(item.quantityInStock),
                            item.productName
                        ]),
                        markArea: {
                            silent: true,
                            itemStyle: {
                                color: 'rgba(239, 68, 68, 0.12)'
                            },
                            label: {
                                position: 'insideTopLeft',
                                color: '#ef4444',
                                fontSize: 10,
                                fontWeight: '600',
                                distance: 8
                            },
                            data: [[
                                {
                                    name: 'Zona Crítica (Alto Stock / Baja Venta)',
                                    xAxis: 0,
                                    yAxis: 5000
                                },
                                {
                                    xAxis: 500,
                                    yAxis: 'max'
                                }
                            ]]
                        }
                    }]
                };
                chartInv.setOption(optionInv);
                chartInv.on('click', function (params) {
                    let producto = params.data[2];
                    if (producto) {
                        redirectByPost('<?= site_url('/tienda/productos') ?>', { sSearch: producto }, false);
                    }
                });
            }

            // --- GRÁFICA 6: PARETO DE DEUDA ---
            const datosDeudaRaw = <?= json_encode($estadosDeCuenta) ?>;
            const datosDeuda = datosDeudaRaw.map(item => ({
                name: item.customerName,
                value: parseFloat(item.Deuda)
            })).sort((a, b) => b.value - a.value);

            const totalDeudaCartera = datosDeuda.reduce((sum, item) => sum + item.value, 0);
            const nombresDeudores = [];
            const valoresDeuda = [];
            const porcentajesAcumulados = [];
            let acumulado = 0;

            datosDeuda.forEach(item => {
                nombresDeudores.push(item.name);
                valoresDeuda.push(item.value);
                acumulado += item.value;
                const porcentaje = totalDeudaCartera > 0 ? (acumulado / totalDeudaCartera) * 100 : 0;
                porcentajesAcumulados.push(porcentaje.toFixed(2));
            });

            const chartParetoDom = document.getElementById('chartParetoDeuda');
            if (chartParetoDom) {
                chartPareto = echarts.init(chartParetoDom, theme);
                const optionPareto = {
                    backgroundColor: 'transparent',
                    title: {
                        text: 'Análisis de Cartera Vencida (Pareto)',
                        subtext: 'Regla 80/20: Prioridad de Cobranza',
                        left: 'center',
                        top: 0,
                        textStyle: { fontSize: 13 },
                        subtextStyle: { fontSize: 11, color: isDark ? '#94a3b8' : '#64748b' }
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: { type: 'cross' },
                        backgroundColor: isDark ? '#1e293b' : '#ffffff',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        textStyle: { color: isDark ? '#f8fafc' : '#1e293b' },
                        formatter: function (params) {
                            var deuda = params[0];
                            var percent = params[1];
                            var valDeuda = typeof moneyFormat === 'function' ? moneyFormat(deuda.value, currency) : deuda.value;
                            return `<b>${deuda.name}</b><br/>` +
                                `Deuda: <b>${valDeuda}</b><br/>` +
                                `Acumulado: <b>${percent.value}%</b> del total`;
                        }
                    },
                    toolbox: {
                        feature: { saveAsImage: { show: true, title: 'Guardar' } }
                    },
                    grid: {
                        top: '22%',
                        right: '8%',
                        left: '5%',
                        bottom: '16%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: nombresDeudores,
                        axisLabel: {
                            interval: 0,
                            rotate: 35,
                            fontSize: 10
                        }
                    },
                    yAxis: [
                        {
                            type: 'value',
                            name: 'Deuda',
                            position: 'left',
                            axisLabel: {
                                formatter: function (value) {
                                    return value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value;
                                }
                            }
                        },
                        {
                            type: 'value',
                            name: '% Acum.',
                            min: 0,
                            max: 100,
                            position: 'right',
                            axisLabel: {
                                formatter: '{value}%'
                            }
                        }
                    ],
                    series: [
                        {
                            name: 'Deuda',
                            type: 'bar',
                            data: valoresDeuda,
                            yAxisIndex: 0,
                            itemStyle: { color: '#ef4444', borderRadius: [4, 4, 0, 0] },
                            barMaxWidth: 45
                        },
                        {
                            name: '% Acumulado',
                            type: 'line',
                            data: porcentajesAcumulados,
                            yAxisIndex: 1,
                            smooth: true,
                            symbol: 'circle',
                            symbolSize: 7,
                            itemStyle: { color: '#38bdf8' },
                            lineStyle: { width: 3, color: '#38bdf8' },
                            markLine: {
                                data: [{ yAxis: 80, name: 'Corte 80%' }],
                                lineStyle: { type: 'dashed', color: '#f59e0b' },
                                label: { formatter: '80% Impacto' }
                            }
                        }
                    ]
                };
                chartPareto.setOption(optionPareto);
                chartPareto.on('click', function (params) {
                    let cliente = params.name;
                    if (cliente) {
                        redirectByPost('<?= site_url('/tienda/clientes') ?>', { sSearch: cliente }, false);
                    }
                });
            }
        }

        // Inicializar todos los gráficos
        initAllCharts();

        // Responsive resize
        window.addEventListener('resize', function () {
            [chartVentas, chartLineas, chartMargen, chartEmp, chartInv, chartPareto].forEach(c => {
                if (c && typeof c.resize === 'function') {
                    c.resize();
                }
            });
        });

        // Re-renderizado instantáneo al alternar tema claro/oscuro
        window.addEventListener('themeChanged', function () {
            initAllCharts();
        });

        // --- BOTÓN REPORTE ---
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
    });
</script>

<?= $this->endSection() ?>
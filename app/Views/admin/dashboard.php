<?= $this->extend('template/template_lte') ?>

<?php $auth = service('Admin_aut'); ?>

<?= $this->section('content') ?>



<script src="<?= base_url(); ?>/assets/js/echarts/echarts.min.js" type="text/javascript"></script>
<script src="<?= base_url(); ?>/assets/js/echarts/world.js" type="text/javascript"></script>

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
                <div class="col">
                    <div class="card card-primary card-outline mb-4">
                        <div class="card-header">
                            <h3 class="card-title"> <i class="fas fa-globe-americas"></i> Distribución Global de Ventas
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div id="echarts-map" style="height: 400px;"></div>
                                </div>

                                <div class="col-md-4">
                                    <p class="text-center"><strong>Líderes de Mercado</strong></p>
                                    <div class="table-responsive" style="height: 400px; overflow-y: auto;">
                                        <table
                                            class="table table-hover table-borderless table-striped table-valign-middle table-sm">
                                            <thead>
                                                <tr>
                                                    <th>País</th>
                                                    <th class="text-right">Ventas</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Asumiendo que $mapa_ventas viene ordenado DESC desde el modelo
                                                foreach ($mapa_ventas as $pais):
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <i class="fas fa-flag text-muted"></i>
                                                            <?= $pais['Pais'] ?>
                                                        </td>
                                                        <td class="text-right text-success text-bold">
                                                            $
                                                            <?= number_format($pais['Total'], 0) ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        // 1. Obtener datos de PHP
                        var dbData = <?= json_encode($mapa_ventas) ?>;

                        // 2. Inicializar el gráfico
                        var chartDom = document.getElementById('echarts-map');
                        var myChart = echarts.init(chartDom);

                        // 3. Mapeo de Nombres (Tu BD vs ECharts)
                        // ECharts usa nombres en inglés estándar. Si tu BD tiene nombres diferentes,
                        // ECharts no los pintará. Este mapa ayuda a corregirlo al vuelo.
                        var nameMap = {
                            "USA": "United States",
                            "UK": "United Kingdom",
                            // Agrega más si ves países grises que deberían tener color
                        };

                        // 4. Transformar datos para ECharts
                        var chartData = dbData.map(function (item) {
                            // Si el nombre está en el mapa de corrección, úsalo, si no, usa el original
                            var mapName = nameMap[item.Pais] || item.Pais;
                            return {
                                name: mapName,
                                value: parseFloat(item.Total)
                            };
                        });

                        // Calcular el máximo valor para ajustar la escala de colores automáticamente
                        var maxValue = Math.max(...chartData.map(o => o.value)) || 100000;

                        // 5. Configuración del Mapa
                        var option = {
                            tooltip: {
                                trigger: 'item',
                                formatter: function (params) {
                                    if (params.value) {
                                        // Formato de moneda bonito en el tooltip
                                        var value = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(params.value);
                                        return params.name + '<br/>' + value;
                                    }
                                    return params.name + '<br/>Sin ventas';
                                }
                            },
                            visualMap: {
                                min: 0,
                                max: maxValue,
                                text: ['Alto', 'Bajo'],
                                realtime: false,
                                calculable: true,
                                inRange: {
                                    // Gradiente de colores: De azul claro a azul oscuro profesional
                                    color: ['#e0f3f8', '#007bff', '#004494']
                                },
                                left: 'left',
                                bottom: 'bottom'
                            },
                            series: [
                                {
                                    name: 'Ventas Globales',
                                    type: 'map',
                                    mapType: 'world',
                                    roam: true, // Permite zoom y moverse
                                    emphasis: {
                                        label: { show: true }, // Muestra el nombre al pasar el mouse
                                        itemStyle: {
                                            areaColor: '#ffc107' // Color amarillo al hacer hover
                                        }
                                    },
                                    data: chartData
                                }
                            ]
                        };

                        // 6. Renderizar
                        myChart.setOption(option);

                        // Hacerlo responsivo si cambian el tamaño de la ventana
                        window.addEventListener('resize', function () {
                            myChart.resize();
                        });
                    });
                </script>
            </div>

            <div class="row">
                <div class="col-lg-5">
                    <div class="card card-success card-outline mb-4">
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

                    <div class="card card-info card-outline mb-4">
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

                    <div class="card card-success card-outline mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Empleados con más ventas en los últimos 3 meses</h3>
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

                    <div class="card card-primary card-outline mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Margen de ganancia por línea en los últimos 6 meses</h3>
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


                <div class="col-lg-7">
                    <div class="card card-warning card-outline mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Estados de cuenta</h3>
                                <span class="badge rounded-pill text-bg-info">Clientes con deuda*</span>
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

                    <div class="card card-warning card-outline mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Productos de menor rotación en los últimos 6 meses</h3>
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
        </div> <!--end::Container-->
    </div> <!--end::App Content-->
</main> <!--end::App Main--> <!--begin::Footer-->

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

        // Procesamiento de datos para Ventas por Línea (Misma lógica que tenías)
        let lineasMap = ventasporlinea.reduce((acc, item) => {
            if (!acc[item.productLine]) {
                acc[item.productLine] = {
                    name: item.productLine,
                    type: 'line',
                    smooth: true, // Curva suave (equivalente a stroke: curve: smooth)
                    data: Array(meses.length).fill(0)
                };
            }
            let mesIndex = meses.indexOf(item.Mes);
            if (mesIndex !== -1) {
                acc[item.productLine].data[mesIndex] = parseFloat(item.Total); // Aseguramos float
            }
            return acc;
        }, {});

        let serieslineas = Object.values(lineasMap);

        // --- 2. CONFIGURACIÓN ECHARTS COMÚN ---

        const commonGrid = {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        };

        const commonTooltip = {
            trigger: 'axis',
            formatter: function (params) {
                let tooltipResult = params[0].axisValueLabel + '<br/>';
                params.forEach(item => {
                    // Usamos tu función global moneyFormat si existe, sino un fallback
                    let val = typeof moneyFormat === 'function' ? moneyFormat(item.value, currency) : item.value;
                    tooltipResult += `${item.marker} ${item.seriesName}: <b>${val}</b><br/>`;
                });
                return tooltipResult;
            }
        };

        // --- 3. GRÁFICA 1: VENTAS ÚLTIMOS 12 MESES ---

        var chartVentasDom = document.getElementById('chartventas');
        // Importante: ECharts necesita altura definida en CSS o style inline
        chartVentasDom.style.height = '350px';
        var chartVentas = echarts.init(chartVentasDom);

        var optionVentas = {
            title: { text: 'Ventas últimos 12 meses' },
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
                itemStyle: { color: '#007bff' },
                areaStyle: { opacity: 0.1 } // Un toque extra visual
            }]
        };

        chartVentas.setOption(optionVentas);

        // --- EVENTO CLICK: Funcionalidad SweetAlert ---
        chartVentas.on('click', function (params) {
            // params.name contiene el nombre del Mes (eje X)
            // params.dataIndex contiene el índice del array
            let mes = params.name;
            let index = params.dataIndex;

            if (mes) {
                // Construimos las filas usando el array procesado 'serieslineas'
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

        // --- 4. GRÁFICA 2: VENTAS POR LÍNEA ---

        var chartLineasDom = document.getElementById('chartventasporlinea');
        chartLineasDom.style.height = '350px';
        var chartLineas = echarts.init(chartLineasDom);

        var optionLineas = {
            title: { text: 'Ventas por línea' },
            tooltip: commonTooltip,
            legend: {
                data: serieslineas.map(s => s.name),
                bottom: 0
            },
            grid: { ...commonGrid, bottom: '10%' }, // Espacio extra para la leyenda
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
            series: serieslineas // Ya tiene el formato correcto {name, type='line', data}
        };

        chartLineas.setOption(optionLineas);

        // --- 5. RESPONSIVIDAD ---
        window.addEventListener('resize', function () {
            chartVentas.resize();
            chartLineas.resize();
            // Si tienes el mapa también en una variable global, agrégalo aquí:
            // if(typeof myChart !== 'undefined') myChart.resize(); 
        });

        // --- 6. BOTÓN REPORTE (Sin cambios lógicos, solo jQuery legacy) ---
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


        // ==========================================
        // SUGERENCIA 1: DONA DE RENTABILIDAD
        // ==========================================
        var datosMargen = <?= json_encode($margenDeGananciaPorLinea) ?>;
        var chartMargenDom = document.getElementById('chartMargenDona');

        if (chartMargenDom) {
            var chartMargen = echarts.init(chartMargenDom);
            var optionMargen = {
                title: {
                    text: 'Aportación al Margen Total',
                    left: 'center',
                    textStyle: { fontSize: 14 }
                },
                tooltip: {
                    trigger: 'item',
                    formatter: function (params) {
                        // Muestra: Nombre linea: $Monto (% del total)
                        let val = typeof moneyFormat === 'function' ? moneyFormat(params.value, currency) : params.value;
                        return `${params.name}<br/><b>${val}</b> (${params.percent}%)`;
                    }
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    show: false // Ocultamos leyenda si hay muchas líneas para no ensuciar
                },
                series: [
                    {
                        name: 'Margen',
                        type: 'pie',
                        radius: ['40%', '70%'], // Esto lo hace una "Dona"
                        avoidLabelOverlap: false,
                        itemStyle: {
                            borderRadius: 5,
                            borderColor: '#fff',
                            borderWidth: 2
                        },
                        label: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: 16,
                                fontWeight: 'bold'
                            }
                        },
                        data: datosMargen.map(item => ({
                            value: parseFloat(item.MargenTotal), // Asegúrate que este campo venga numérico o limpio
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

            // Resize automático
            window.addEventListener('resize', () => chartMargen.resize());
        }

        // ==========================================
        // SUGERENCIA 2: RANKING DE EMPLEADOS
        // ==========================================
        var datosEmpleados = <?= json_encode($empleadosConMasVentasEnElUltimoTrimestre) ?>;
        // Ordenamos de menor a mayor para que el gráfico de barras horizontales muestre el #1 arriba visualmente
        datosEmpleados.sort((a, b) => parseFloat(a.TotalVentasTrimestre) - parseFloat(b.TotalVentasTrimestre));

        var chartEmpDom = document.getElementById('chartEmpleadosRanking');

        if (chartEmpDom) {
            var chartEmp = echarts.init(chartEmpDom);
            var optionEmp = {
                title: { text: '' },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'value',
                    boundaryGap: [0, 0.01],
                    axisLabel: {
                        formatter: function (val) {
                            // Versión corta de moneda para eje X (ej. 10k)
                            return val >= 1000 ? (val / 1000) + 'k' : val;
                        }
                    }
                },
                yAxis: {
                    type: 'category',
                    data: datosEmpleados.map(item => item.Empleado) // Nombres en eje Y
                },
                series: [
                    {
                        name: 'Ventas Trimestre',
                        type: 'bar',
                        data: datosEmpleados.map(item => parseFloat(item.TotalVentasTrimestre)),
                        itemStyle: {
                            color: function (params) {
                                // Pinta de verde oscuro al mejor vendedor (el último del array ordenado)
                                var numItems = datosEmpleados.length;
                                if (params.dataIndex === numItems - 1) {
                                    return '#28a745';
                                }
                                return '#17a2b8'; // Azul info para el resto
                            }
                        },
                        label: {
                            show: true,
                            position: 'right', // Muestra el valor a la derecha de la barra
                            formatter: function (params) {
                                return typeof moneyFormat === 'function' ? moneyFormat(params.value, currency) : params.value;
                            },
                            fontSize: 10
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

            // Resize automático
            window.addEventListener('resize', () => chartEmp.resize());
        }

        // ==========================================
        // SUGERENCIA 3: MATRIZ DE INVENTARIO (SCATTER)
        // ==========================================
        var datosInventario = <?= json_encode($productosConMenorRotacion) ?>;
        var chartInvDom = document.getElementById('chartInventarioMuerto');

        if (chartInvDom) {
            var chartInv = echarts.init(chartInvDom);

            var optionInv = {
                title: {
                    text: 'Matriz de Rotación de Inventario',
                    subtext: 'Relación Stock vs. Ventas',
                    left: 'center'
                },
                tooltip: {
                    formatter: function (params) {
                        return `<b>${params.data[2]}</b><br/>` + // Nombre del producto
                            `Stock: ${params.data[1]}<br/>` +
                            `Ventas: ${params.data[0]}`;
                    }
                },
                grid: { left: '8%', right: '10%', top: '15%', bottom: '10%' },
                xAxis: {
                    name: 'Ventas (Unidades)',
                    type: 'value',
                    splitLine: { show: false }
                },
                yAxis: {
                    name: 'Stock Actual',
                    type: 'value',
                    splitLine: { show: false }
                },
                // Zonas visuales (MarkArea) para indicar peligro
                series: [{
                    type: 'scatter',
                    symbolSize: 15,
                    itemStyle: {
                        color: function (params) {
                            // Si tiene mucho stock (>5000) y pocas ventas (<500), color ROJO
                            if (params.data[1] > 5000 && params.data[0] < 500) return '#dc3545';
                            return '#007bff';
                        },
                        opacity: 0.7
                    },
                    // Formato de data para Scatter: [X, Y, NombreExtra]
                    data: datosInventario.map(item => [
                        parseInt(item.TotalVendidoUltimos6Meses), // Eje X: Ventas
                        parseInt(item.quantityInStock),   // Eje Y: Stock
                        item.productName                // Extra para tooltip
                    ]),
                    markArea: {
                        silent: true,
                        itemStyle: {
                            color: 'rgba(220, 53, 69, 0.1)' // Fondo rojo suave
                        },
                        data: [[
                            {
                                name: 'Zona Crítica\n[Alto Stock (>5000) / Baja Venta (<500)]',
                                xAxis: 0, // Desde 0 ventas
                                yAxis: 5000 // Desde 5000 stock (ajusta este umbral a tu realidad)
                            },
                            {
                                xAxis: 500, // Hasta 500 ventas
                                yAxis: 'max' // Hasta el máximo de stock
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

            window.addEventListener('resize', () => chartInv.resize());
        }


        // ==========================================
        // OPCIÓN RECOMENDADA: PARETO DE DEUDA
        // ==========================================
        var datosDeudaRaw = <?= json_encode($estadosDeCuenta) ?>; // <--- Verifica el nombre de tu variable PHP

        // 1. PREPARACIÓN DE DATOS (Matemática para Pareto)
        // Aseguramos que sea numérico y ordenamos de Mayor a Menor deuda
        var datosDeuda = datosDeudaRaw.map(item => ({
            name: item.customerName,            // <--- Ajusta 'customerName' al nombre real de tu campo
            value: parseFloat(item.Deuda)  // <--- Ajusta 'TotalDeuda' al nombre real de tu campo
        })).sort((a, b) => b.value - a.value);

        // Calculamos el total de la deuda para sacar los porcentajes
        var totalDeudaCartera = datosDeuda.reduce((sum, item) => sum + item.value, 0);

        // Generamos los arrays para ECharts
        var nombresDeudores = [];
        var valoresDeuda = [];
        var porcentajesAcumulados = [];
        var acumulado = 0;

        datosDeuda.forEach(item => {
            nombresDeudores.push(item.name);
            valoresDeuda.push(item.value);

            acumulado += item.value;
            var porcentaje = (acumulado / totalDeudaCartera) * 100;
            porcentajesAcumulados.push(porcentaje.toFixed(2)); // Guardamos con 2 decimales
        });

        // 2. CONFIGURACIÓN ECHARTS
        var chartParetoDom = document.getElementById('chartParetoDeuda');

        if (chartParetoDom) {
            var chartPareto = echarts.init(chartParetoDom);

            var optionPareto = {
                title: {
                    text: 'Análisis de Cartera Vencida (Pareto)',
                    subtext: 'Regla 80/20: Prioridad de Cobranza',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'cross' },
                    formatter: function (params) {
                        // params[0] es la Barra (Dinero), params[1] es la Línea (Porcentaje)
                        var deuda = params[0];
                        var percent = params[1];

                        var valDeuda = typeof moneyFormat === 'function' ? moneyFormat(deuda.value, currency) : deuda.value;

                        return `<b>${deuda.name}</b><br/>` +
                            `Deuda: ${valDeuda}<br/>` +
                            `Acumulado: ${percent.value}% del total`;
                    }
                },
                toolbox: {
                    feature: { saveAsImage: { show: true, title: 'Guardar' } }
                },
                grid: {
                    top: '20%',
                    right: '10%', // Espacio para el eje derecho
                    left: '10%',
                    bottom: '10%'
                },
                xAxis: {
                    type: 'category',
                    data: nombresDeudores,
                    axisLabel: {
                        interval: 0,
                        rotate: 45, // Rotamos nombres si son largos
                        fontSize: 10
                    }
                },
                yAxis: [
                    {
                        type: 'value',
                        name: 'Monto Deuda',
                        position: 'left',
                        axisLabel: {
                            formatter: function (value) {
                                // Formato corto para el eje (ej. 10k)
                                return value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value;
                            }
                        }
                    },
                    {
                        type: 'value',
                        name: 'Impacto Acumulado',
                        min: 0,
                        max: 100,
                        position: 'right',
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    }
                ],
                series: [
                    {
                        name: 'Deuda',
                        type: 'bar',
                        data: valoresDeuda,
                        yAxisIndex: 0, // Usa el eje izquierdo (Dinero)
                        itemStyle: { color: '#dc3545' }, // Rojo (peligro)
                        barMaxWidth: 50
                    },
                    {
                        name: '% Acumulado',
                        type: 'line',
                        data: porcentajesAcumulados,
                        yAxisIndex: 1, // Usa el eje derecho (Porcentaje)
                        smooth: true,
                        symbol: 'circle',
                        symbolSize: 8,
                        itemStyle: { color: '#343a40' }, // Gris oscuro
                        lineStyle: { width: 3 },
                        markLine: {
                            data: [{ yAxis: 80, name: 'Corte 80%' }], // Línea guía al 80%
                            lineStyle: { type: 'dashed', color: 'orange' },
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

            window.addEventListener('resize', () => chartPareto.resize());
        }

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

    /* Asegura que los contenedores tengan altura por si el script tarda */
    #chartventas,
    #chartventasporlinea {
        min-height: 350px;
        width: 100%;
    }
</style>

<?= $this->endSection() ?>
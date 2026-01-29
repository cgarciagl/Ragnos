<hr>
<?php if (empty($ventas)): ?>
    <div class="row">
        <div class="col-12 py-4">
            <div class="text-center text-muted">
                <i class="bi bi-receipt h1 mb-3 d-block text-secondary opacity-50"></i>
                <h5 class="fw-normal">No hay historial de ventas</h5>
                <p class="small mb-0">Este cliente aún no ha realizado ninguna compra.</p>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-6">
            <div class="card shadow-sm border-0 h-100">
                <h6 class="card-title mb-3 text-muted"><i class="bi bi-clock-history me-2"></i>Últimas Órdenes</h6>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0"># Orden</th>
                                    <th class="border-0">Fecha</th>
                                    <th class="border-0">Estado</th>
                                    <th class="border-0 text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimasOrdenes as $orden): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">#<?= $orden['orderNumber'] ?></td>
                                        <td class="small text-secondary"><?= $orden['orderDate'] ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = match ($orden['status']) {
                                                'Shipped'    => 'bg-success',
                                                'Cancelled'  => 'bg-danger',
                                                'On Hold'    => 'bg-warning text-dark',
                                                'In Process' => 'bg-info text-dark',
                                                'Resolved'   => 'bg-primary',
                                                default      => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge rounded-pill <?= $badgeClass ?> bg-opacity-75" style="font-weight: 500; font-size: 0.75em;">
                                                <?= $orderStatuses[$orden['status']] ?? $orden['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">$<?= number_format($orden['TotalVenta'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card shadow-sm border-0 h-100">
                <h6 class="card-title mb-3 text-muted"><i class="bi bi-graph-up me-2"></i>Tendencia de Ventas</h6>
                <div class="card-body">
                    <div id="chartVentasPedidos" style="width: 100%; height: 250px;"></div>
                </div>
            </div>

            <script>
                // 1. Definimos la lógica de la gráfica en una función para no repetir código
                function initChartVentas() {
                    var rawData = <?= json_encode($ventas) ?>;
                    var chartDom = document.getElementById('chartVentasPedidos');

                    // Validación de datos vacíos (Defensiva, aunque el PHP ya filtra)
                    if (!rawData || rawData.length === 0) {
                        return;
                    }

                    // Limpieza de instancia previa (Seguridad AJAX)
                    if (typeof echarts !== 'undefined') {
                        var existingChart = echarts.getInstanceByDom(chartDom);
                        if (existingChart) existingChart.dispose();
                    }

                    // Mapeo
                    var fechas = rawData.map(item => item.orderDate);
                    var montos = rawData.map(item => item.TotalVenta);

                    var myChart = echarts.init(chartDom);

                    var option = {
                        title: {
                            show: false
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: { type: 'cross', label: { backgroundColor: '#6a7985' } },
                            formatter: function (params) {
                                var item = params[0];
                                if (!item) return '';
                                var dataIndex = item.dataIndex;
                                var rawItem = rawData[dataIndex];
                                var valor = typeof moneyFormat === 'function' ? moneyFormat(rawItem.TotalVenta) : '$' + rawItem.TotalVenta;

                                return `<div style="font-size:12px">
                                <b>${rawItem.orderDate}</b><br/>
                                Orden: #${rawItem.orderNumber} (${rawItem.status})<br/>
                                Venta: <b style="color:#007bff">${valor}</b>
                                </div>`;
                            }
                        },
                        grid: { left: '0%', right: '2%', bottom: '0%', top: '5%', containLabel: true },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: fechas,
                            axisLine: { show: false },
                            axisTick: { show: false },
                            axisLabel: { fontSize: 10, color: '#888' }
                        },
                        yAxis: {
                            type: 'value',
                            splitLine: { show: true, lineStyle: { type: 'dashed', color: '#eee' } },
                            axisLabel: { show: false }
                        },
                        series: [{
                            name: 'Venta',
                            type: 'line',
                            data: montos,
                            smooth: true,
                            showSymbol: false,
                            symbolSize: 8,
                            lineStyle: { width: 2, color: '#007bff' },
                            areaStyle: {
                                opacity: 0.2,
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                    { offset: 0, color: 'rgba(0, 123, 255, 0.5)' },
                                    { offset: 1, color: 'rgba(0, 123, 255, 0.0)' }
                                ])
                            }
                        }]
                    };

                    myChart.setOption(option);

                    window.addEventListener('resize', function () {
                        if (myChart && !myChart.isDisposed()) {
                            myChart.resize();
                        }
                    });
                }

                // 2. Lógica de Carga Condicional
                $(function () {
                    var urlEcharts = "<?= base_url(); ?>/assets/js/echarts/echarts.min.js";

                    if (typeof echarts === 'undefined') {
                        $.getScript(urlEcharts)
                            .done(function (script, textStatus) {
                                initChartVentas();
                            })
                            .fail(function (jqxhr, settings, exception) {
                                console.error("Error al cargar ECharts: " + exception);
                            });
                    } else {
                        initChartVentas();
                    }
                });
            </script>
        </div>
    </div>
<?php endif; ?>
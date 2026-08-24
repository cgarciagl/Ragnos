<!-- Custom View: Client Sales Dashboard Footer -->
<div class="mt-5">
    <?php if (empty($historial)): ?>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 bg-body-secondary py-5">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-basket2 h1 text-muted opacity-25" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="fw-bold text-secondary mb-2">Sin historial de compras</h4>
                        <p class="text-muted mb-4">Este cliente aún no ha iniciado su actividad comercial.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php
        $ultimasOrdenes = array_slice($historial, 0, 5);
        $ventas         = array_reverse($historial);
        ?>
        <div class="row g-4 match-height">
            <!-- Left Column: Recent Orders -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div
                        class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-body mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Últimas
                                Órdenes</h5>
                            <small class="text-muted">Resumen de actividad reciente</small>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive px-4">
                            <table class="table table-hover align-middle mb-0"
                                style="border-collapse: separate; border-spacing: 0 8px;">
                                <thead class="text-secondary small text-uppercase">
                                    <tr>
                                        <th class="border-0 fw-semibold ps-0">Orden</th>
                                        <th class="border-0 fw-semibold">Fecha</th>
                                        <th class="border-0 fw-semibold">Estado</th>
                                        <th class="border-0 fw-semibold text-end pe-0">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimasOrdenes as $orden): ?>
                                        <?php
                                        // Status Color Mapping with Bootstrap Soft Colors
                                        $statusConfig = match ($orden['status']) {
                                            'Shipped'    => ['bg'    => 'bg-success', 'icon'    => 'bi-truck'],
                                            'Cancelled'  => ['bg'  => 'bg-danger', 'icon'  => 'bi-x-circle'],
                                            'On Hold'    => ['bg'    => 'bg-warning', 'icon'    => 'bi-pause-circle'],
                                            'In Process' => ['bg' => 'bg-info', 'icon' => 'bi-gear-wide-connected'],
                                            'Resolved'   => ['bg'   => 'bg-primary', 'icon'   => 'bi-check2-circle'],
                                            default      => ['bg'      => 'bg-secondary', 'icon'      => 'bi-circle']
                                        };
                                        $badgeBg      = $statusConfig['bg'];
                                        ?>
                                        <tr class="position-relative">
                                            <td class="ps-0 py-3 border-bottom-0">
                                                <span class="fw-bold text-body">#<?= $orden['orderNumber'] ?></span>
                                            </td>
                                            <td class="py-3 border-bottom-0">
                                                <div class="d-flex align-items-center text-muted small">
                                                    <i class="bi bi-calendar3 me-2 opacity-50"></i>
                                                    <?= date('M d, Y', strtotime($orden['orderDate'])) ?>
                                                </div>
                                            </td>
                                            <td class="py-3 border-bottom-0">
                                                <span
                                                    class="badge rounded-pill <?= $badgeBg ?> bg-opacity-10 text-body border border-opacity-10 d-inline-flex align-items-center px-2 py-1">
                                                    <i
                                                        class="bi <?= $statusConfig['icon'] ?> me-1 <?= str_replace('bg-', 'text-', $badgeBg) ?>"></i>
                                                    <?= $orderStatuses[$orden['status']] ?? $orden['status'] ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-0 py-3 border-bottom-0">
                                                <span
                                                    class="fw-bold text-body lead fs-6">$<?= number_format($orden['TotalVenta'], 2) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sales Trend Chart -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold text-body mb-0"><i class="bi bi-graph-up-arrow me-2 text-success"></i>Tendencia
                            de Ventas</h5>
                        <small class="text-muted">Desempeño histórico</small>
                    </div>
                    <div class="card-body p-4 d-flex align-items-center justify-content-center">
                        <div id="chartVentasPedidos" style="width: 100%; height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ECharts Logic -->
        <script>
            function initChartVentas() {
                var rawData = <?= json_encode($ventas) ?>;
                var chartDom = document.getElementById('chartVentasPedidos');

                if (!rawData || rawData.length === 0) return;

                if (typeof echarts !== 'undefined') {
                    var existingChart = echarts.getInstanceByDom(chartDom);
                    if (existingChart) existingChart.dispose();
                }

                var fechas = rawData.map(item => item.orderDate);
                var montos = rawData.map(item => item.TotalVenta);

                var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                var myChart = echarts.init(chartDom, isDark ? 'dark' : null);

                var option = {
                    backgroundColor: 'transparent',
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: isDark ? 'rgba(33, 37, 41, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        borderColor: isDark ? '#374151' : '#e9ecef',
                        borderWidth: 1,
                        textStyle: { color: isDark ? '#f8fafc' : '#495057' },
                        extraCssText: 'box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); border-radius: 8px;',
                        formatter: function (params) {
                            var item = params[0];
                            if (!item) return '';
                            var dataIndex = item.dataIndex;
                            var rawItem = rawData[dataIndex];
                            var valor = typeof moneyFormat === 'function' ? moneyFormat(rawItem.TotalVenta) : '$' + rawItem.TotalVenta;
                            return `<div class="p-1">
                                <div class="small text-muted mb-1">${rawItem.orderDate}</div>
                                <div class="fw-bold mb-1">Orden #${rawItem.orderNumber}</div>
                                <div class="text-primary fw-bold fs-6">${valor}</div>
                            </div>`;
                        }
                    },
                    grid: { left: '3%', right: '4%', bottom: '3%', top: '3%', containLabel: true },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: fechas,
                        axisLine: { show: false },
                        axisTick: { show: false },
                        axisLabel: { color: '#adb5bd', fontSize: 11, margin: 15 }
                    },
                    yAxis: {
                        type: 'value',
                        splitLine: { show: true, lineStyle: { type: 'dashed', color: '#f1f3f5' } },
                        axisLabel: { color: '#adb5bd', fontSize: 11 }
                    },
                    series: [{
                        name: 'Venta',
                        type: 'line',
                        data: montos,
                        smooth: 0.4,
                        symbol: 'circle',
                        symbolSize: 8,
                        itemStyle: { color: '#3b82f6', borderColor: '#fff', borderWidth: 2 },
                        lineStyle: { width: 3, color: '#3b82f6', shadowColor: 'rgba(59, 130, 246, 0.3)', shadowBlur: 10 },
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(59, 130, 246, 0.2)' },
                                { offset: 1, color: 'rgba(59, 130, 246, 0)' }
                            ])
                        }
                    }]
                };

                myChart.setOption(option);

                window.addEventListener('resize', () => {
                    myChart && !myChart.isDisposed() && myChart.resize();
                });
            }

            onReady(() => {
                const urlEcharts = "<?= base_url(); ?>/assets/js/echarts/echarts.min.js";
                if (typeof echarts === 'undefined') {
                    const script = document.createElement('script');
                    script.src = urlEcharts;
                    script.addEventListener('load', initChartVentas, { once: true });
                    script.addEventListener('error', () => console.error('Error loading ECharts.'), { once: true });
                    document.head.append(script);
                } else {
                    initChartVentas();
                }
            });
        </script>
    <?php endif; ?>
</div>

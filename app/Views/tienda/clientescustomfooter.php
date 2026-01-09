<hr>
<div class="row">
    <div class="col-6"></div>
    <div class="col-6">
        <div id="chartVentasPedidos" style="width: 100%; height: 250px;"></div>

        <script>
            // 1. Definimos la lógica de la gráfica en una función para no repetir código
            function initChartVentas() {
                var rawData = <?= json_encode($ventas) ?>;
                var chartDom = document.getElementById('chartVentasPedidos');

                // Validación de datos vacíos
                if (!rawData || rawData.length === 0) {
                    chartDom.className = "d-flex align-items-center justify-content-center";
                    chartDom.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-chart-line fa-2x mb-2"></i><br>
                    <h6 class="mb-0">No tiene ventas registradas</h6>
                </div>
            `;
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
                        text: 'Tendencia de Ventas',
                        textStyle: { fontSize: 12, color: '#999' },
                        left: 'left'
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
                    grid: { left: '0%', right: '2%', bottom: '0%', top: '15%', containLabel: true },
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
                // Pon aquí la ruta REAL a tu archivo echarts.min.js
                // Puede ser una ruta relativa como 'assets/js/echarts.min.js' o una CDN
                var urlEcharts = "<?= base_url(); ?>/assets/js/echarts/echarts.min.js";

                // Verificamos si ECharts ya está cargado en el navegador
                if (typeof echarts === 'undefined') {
                    // Si NO está definido, lo cargamos primero
                    $.getScript(urlEcharts)
                        .done(function (script, textStatus) {
                            // Una vez cargado exitosamente, iniciamos la gráfica
                            initChartVentas();
                        })
                        .fail(function (jqxhr, settings, exception) {
                            console.error("Error al cargar ECharts: " + exception);
                        });
                } else {
                    // Si YA estaba cargado (ej. navegaste y regresaste), ejecutamos directo
                    initChartVentas();
                }
            });
        </script>
    </div>
</div>
<hr>
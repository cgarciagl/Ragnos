<?= $this->extend('template/template_lte') ?>

<?= $this->section('content') ?>

<script src="<?= base_url(); ?>/assets/js/echarts/echarts.min.js" type="text/javascript"></script>
<script src="<?= base_url(); ?>/assets/js/echarts/world.js" type="text/javascript"></script>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0 animate__animated animate__fadeInDown">Distribución Global de Ventas</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= site_url('admin') ?>">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ventas por País</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col animate__animated animate__zoomIn">
                    <div class="card card-primary card-outline mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title"> <i class="bi bi-globe-americas"></i> Reporte Detallado
                            </h3>
                            <div class="card-tools ms-auto">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div id="echarts-map" style="height: 600px;"></div>
                                </div>

                                <div class="col-md-4">
                                    <p class="text-center"><strong>Líderes de Mercado</strong></p>
                                    <div class="table-responsive" style="height: 600px; overflow-y: auto;">
                                        <table
                                            class="table table-hover table-borderless table-striped table-valign-middle table-sm">
                                            <thead>
                                                <tr>
                                                    <th>País</th>
                                                    <th class="text-right">Ventas</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mapa_ventas as $pais): ?>
                                                    <tr>
                                                        <td>
                                                            <i class="bi bi-flag-fill text-muted"></i>
                                                            <?= $pais['Pais'] ?>
                                                        </td>
                                                        <td class="text-right text-success text-bold">
                                                            $ <?= number_format($pais['Total'], 0) ?>
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
                        var dbData = <?= json_encode($mapa_ventas) ?>;
                        var chartDom = document.getElementById('echarts-map');
                        var myChart = echarts.init(chartDom);
                        var nameMap = {
                            "USA": "United States",
                            "UK": "United Kingdom",
                        };

                        var chartData = dbData.map(function (item) {
                            var mapName = nameMap[item.Pais] || item.Pais;
                            return {
                                name: mapName,
                                value: parseFloat(item.Total)
                            };
                        });

                        var maxValue = Math.max(...chartData.map(o => o.value)) || 100000;

                        var option = {
                            tooltip: {
                                trigger: 'item',
                                formatter: function (params) {
                                    if (params.value) {
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
                                    roam: true,
                                    emphasis: {
                                        label: { show: true },
                                        itemStyle: {
                                            areaColor: '#ffc107'
                                        }
                                    },
                                    data: chartData
                                }
                            ]
                        };

                        myChart.setOption(option);
                        window.addEventListener('resize', function () {
                            myChart.resize();
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</main>

<?= $this->endSection() ?>
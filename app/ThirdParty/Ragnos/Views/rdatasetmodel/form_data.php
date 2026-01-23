<div class="card">
    <div class="card-body">
        <form class="" role="form" method='post' onsubmit='return false;'>
            <?php
            // Agrupar campos por pestañas
            $tabs    = [];
            $hasTabs = false;
            // Asegurar que haya un grupo 'General' por defecto para campos sin pestaña
            $tabs['General'] = [];

            foreach ($fields as $k => $fieldItem) {
                if (method_exists($fieldItem, 'getTab')) {
                    $tabName = $fieldItem->getTab();
                    if ($tabName) {
                        $hasTabs          = true;
                        $tabs[$tabName][] = $fieldItem;
                    } else {
                        $tabs['General'][] = $fieldItem;
                    }
                } else {
                    $tabs['General'][] = $fieldItem;
                }
            }

            // Si no hay pestañas específicas, se mantiene el comportamiento original
            if (!$hasTabs) {
                ?>
                <div class="row">
                    <?php foreach ($fields as $k => $fieldItem): ?>
                        <?= $fieldItem->constructControl() ?>
                    <?php endforeach; ?>
                </div>
                <?php
            } else {
                // Si la pestaña 'General' está vacía, la eliminamos
                if (empty($tabs['General'])) {
                    unset($tabs['General']);
                }

                // Generar un ID único para los Tabs
                $uniqueTabId = isset($primaryKeyValue) ? $primaryKey . '_' . $primaryKeyValue : uniqid();
                ?>
                <ul class="nav nav-tabs" id="myTab<?= $uniqueTabId ?>" role="tablist">
                    <?php $loop = 0;
                    foreach ($tabs as $tabName => $tabFields):
                        $active      = ($loop === 0) ? 'active' : '';
                        $safeTabName = md5($tabName) . $uniqueTabId; // Usar md5 para evitar caracteres extraños en IDs
                        ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $active ?>" id="tab-<?= $safeTabName ?>" data-bs-toggle="tab"
                                data-bs-target="#pane-<?= $safeTabName ?>" type="button" role="tab"
                                aria-controls="pane-<?= $safeTabName ?>"
                                aria-selected="<?= ($loop === 0) ? 'true' : 'false' ?>"><?= $tabName ?></button>
                        </li>
                        <?php $loop++; endforeach; ?>
                </ul>

                <div class="tab-content pt-3" id="myTabContent<?= $uniqueTabId ?>">
                    <?php $loop = 0;
                    foreach ($tabs as $tabName => $tabFields):
                        $active      = ($loop === 0) ? 'show active' : '';
                        $safeTabName = md5($tabName) . $uniqueTabId;
                        ?>
                        <div class="tab-pane fade <?= $active ?>" id="pane-<?= $safeTabName ?>" role="tabpanel"
                            aria-labelledby="tab-<?= $safeTabName ?>">
                            <div class="row">
                                <?php foreach ($tabFields as $fieldItem): ?>
                                    <?= $fieldItem->constructControl() ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php $loop++; endforeach; ?>
                </div>
                <?php
            }
            ?>
            <div id='group_general_error' class='general_error'></div>
        </form>
    </div>
</div>
<script>
    $(function () {
        <?php use App\ThirdParty\Ragnos\Controllers\Ragnos; ?>
        let currency = '<?= Ragnos::config()->currency ?? 'USD' ?>';
        $('input[money]').each(function () {
            $(this).val(moneyFormat($(this).val(), currency));
        });
    });
</script>

<?php if (isset($detailsController) && $detailsController != NULL): ?>
    <hr />
    <div class="row clearfix" id="panel<?= $primaryKey ?>_<?php echo $primaryKeyValue; ?>">
        <div class="card text-bg-dark">
            <h5 class="card-header">Detalles</h5>
            <div class="card-body">
                <div id="detalle<?= $primaryKey ?>_<?php echo $primaryKeyValue; ?>">

                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            let detailsController = '<?= controllerNameToURL($detailsController) ?>';
            let primaryKeyValue = '<?php echo $primaryKeyValue; ?>';
            if (primaryKeyValue == '') {
                $("#panel<?= $primaryKey ?>_<?php echo $primaryKeyValue; ?>").remove();
            } else {
                RagnosUtils.showControllerTableIn('#detalle<?= $primaryKey ?>_<?php echo $primaryKeyValue; ?>', detailsController, primaryKeyValue);
            }
        });
    </script>
<?php endif; ?>
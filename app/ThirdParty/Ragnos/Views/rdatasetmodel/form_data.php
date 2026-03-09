<?php $formCardId = 'form_card_' . uniqid(); ?>
<div class="card" id="<?= $formCardId ?>">
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

        // Detectar si este formulario está dentro de una sección de "Detalles"
        let $formCard = $('#<?= $formCardId ?>');
        if ($formCard.parents('[id^="detalle"]').length > 0) {
            // Añadir estilos para destacar la tarjeta
            $formCard.removeClass('border-0 shadow-sm');
            $formCard.addClass('border-warning border-3 shadow');

            // Agregar alerta solo si no se ha agregado antes
            if ($formCard.find('.alert-detail-form').length === 0) {
                let htmlAlert = `
                <div class="alert alert-warning d-flex align-items-center mb-0 alert-detail-form shadow-sm" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
                    <i class="bi bi-info-circle-fill fs-3 me-3"></i>
                    <div>
                        <h5 class="mb-1 text-dark fw-bold"><?= lang('Ragnos.Ragnos_detail_form_alert_title') ?></h5>
                        <p class="mb-0 text-dark"><?= lang('Ragnos.Ragnos_detail_form_alert_text') ?></p>
                    </div>
                </div>`;
                $formCard.prepend(htmlAlert);

                // Resaltar el botón de Aceptar propio de esta pestaña
                let $footer = $formCard.closest('.tab-pane').find('.card-footer');
                let $btnOk = $footer.find('.btn-success');
                if ($btnOk.length) {
                    $btnOk.removeClass('btn-success').addClass('btn-warning fw-bold text-dark border-dark');
                    $btnOk.html('<i class="bi bi-check-lg"></i>&nbsp; <?= lang('Ragnos.Ragnos_accept_and_save_detail') ?>');
                }
            }
        }
    });
</script>

<?php if (isset($detailsController) && $detailsController != NULL):
    $detailsControllers = is_array($detailsController) ? $detailsController : [$detailsController];
    ?>
    <hr />
    <div class="row clearfix" id="panel<?= $primaryKey ?>_<?php echo $primaryKeyValue; ?>">
        <div class="card shadow-sm border-secondary">
            <?php if (count($detailsControllers) == 1): ?>
                <div class="card-header pt-3 bg-light">
                    <h5 class="card-title mb-0">
                        <strong><?= lang('Ragnos.details') ?></strong>
                    </h5>
                </div>
                <div class="card-body">
                    <div id="detalle<?= $primaryKey ?>_<?php echo $primaryKeyValue; ?>_0"></div>
                </div>
            <?php else: ?>
                <div class="card-header pt-3 bg-light">
                    <ul class="nav nav-tabs card-header-tabs" id="detailsTab<?= $primaryKey ?>_<?= $primaryKeyValue ?>"
                        role="tablist">
                        <?php foreach ($detailsControllers as $index => $dc):
                            $tabName   = str_replace('Controller', '', basename(str_replace('\\', '/', $dc)));
                            $className = "\\App\\Controllers\\" . ltrim($dc, '\\');
                            if (class_exists($className)) {
                                try {
                                    $controllerInstance = new $className();
                                    if (method_exists($controllerInstance, 'getTitle') && !empty($controllerInstance->getTitle())) {
                                        $tabName = $controllerInstance->getTitle();
                                    }
                                } catch (\Exception $e) {
                                    // Ignorar si falla la instanciación y mantener el nombre de la clase
                                }
                            }
                            ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" id="details-tab-<?= $index ?>"
                                    data-bs-toggle="tab" data-bs-target="#details-pane-<?= $index ?>" type="button" role="tab"
                                    aria-controls="details-pane-<?= $index ?>"
                                    aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"><strong><?= $tabName ?></strong></button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="detailsTabContent<?= $primaryKey ?>_<?= $primaryKeyValue ?>">
                        <?php foreach ($detailsControllers as $index => $dc): ?>
                            <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="details-pane-<?= $index ?>"
                                role="tabpanel" aria-labelledby="details-tab-<?= $index ?>">
                                <div id="detalle<?= $primaryKey ?>_<?php echo $primaryKeyValue; ?>_<?= $index ?>"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            let primaryKeyValue = '<?php echo $primaryKeyValue; ?>';
            if (primaryKeyValue == '') {
                $("#panel<?= $primaryKey ?>_<?php echo $primaryKeyValue; ?>").remove();
            } else {
                <?php foreach ($detailsControllers as $index => $dc): ?>
                    <?php $urlCont = controllerNameToURL($dc); ?>
                    RagnosUtils.showControllerTableIn('#detalle<?= $primaryKey ?>_<?php echo $primaryKeyValue; ?>_<?= $index ?>', '<?= $urlCont ?>', primaryKeyValue);
                <?php endforeach; ?>
            }
        });
    </script>
<?php endif; ?>
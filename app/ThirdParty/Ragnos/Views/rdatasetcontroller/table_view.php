<?php
$controllerUniqueID = uniqid($controller_name);
$tableController    = $controller_name;
$clase              = mapClassToURL($controller_class);
?>

<div id="<?= $controllerUniqueID ?>" class="card  card-primary card-outline shadow-lg Ragnos-widget" data-idactivo=""
    data-preselect="">

    <?php if ($title): ?>
        <div class="card-header text-center">
            <h3 class="card-title">
                <?= $title; ?>
            </h3>
            <div class="card-tools"> <button type="button" class="btn btn-tool" data-lte-toggle="card-maximize"> <i
                        data-lte-icon="maximize" class="bi bi-fullscreen"></i> <i data-lte-icon="minimize"
                        class="bi bi-fullscreen-exit"></i> </button> </div>
        </div>
    <?php endif; ?>

    <div id="<?= $controllerUniqueID ?>tabs" class="card-body">
        <nav>
            <ul class="nav nav-tabs" role="tablist" id="<?= $controllerUniqueID ?>ultabs">
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-target="#<?= $controllerUniqueID ?>_Form" data-bs-toggle="tab"
                        id="tab_<?= $controllerUniqueID ?>_Form" type="button" role="tab">
                        <i class="bi bi-pencil"></i>&nbsp;
                        <?= lang('Ragnos.Ragnos_form_label') ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-target="#<?= $controllerUniqueID ?>_Tablediv"
                        data-bs-toggle="tab" id="tab_<?= $controllerUniqueID ?>_Table" type="button" role="tab">
                        <i class="bi bi-table"></i>&nbsp;
                        <?= lang('Ragnos.Ragnos_table_label') ?>
                    </button>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="<?= $controllerUniqueID ?>_Tablediv" class="tablediv tab-pane show active p-3" role="tabpanel">
                <!-- Toolbar & Filters -->
                <div
                    class="d-flex flex-wrap justify-content-between align-items-center mb-4 p-3 bg-light rounded border-start border-4 border-primary shadow-sm">
                    <div id="<?= $controllerUniqueID ?>_combo" class="d-flex align-items-center gap-2 mb-2 mb-lg-0">
                        <span class="fw-bold text-secondary text-uppercase small">
                            <?= lang('Ragnos.Ragnos_in') ?>:
                        </span>
                        <div style="min-width: 200px;">
                            <?php
                            // Primero, construye el array de opciones a partir de tus datos
                            $options = [];
                            foreach ($tablefields as $fieldItem) {
                                // Cada elemento del array de opciones debe tener el 'valueField' y el 'textField'
                                $options[] = [
                                    'value' => $fieldItem,
                                    'label' => $fieldlist[$fieldItem]->getLabel()
                                ];
                            }

                            // Opcional: añade la opción 'all' al inicio del array
                            array_unshift($options, [
                                'value' => '',
                                'label' => lang('Ragnos.Ragnos_all')
                            ]);

                            echo arrayToSelect(
                                $controllerUniqueID . '_sel', // Nombre del campo
                                $options,                     // El array de opciones que creamos
                                'value',                      // La clave para el valor de la opción
                                'label',                      // La clave para el texto de la opción
                                null,                         // No hay valor preseleccionado
                                [
                                    'id'    => $controllerUniqueID . '_sel',
                                    'class' => 'form-select shadow-sm'
                                ]
                            );
                            ?>
                        </div>
                        <i class="bi bi-filter"></i>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <?php if ($modelo->canInsert): ?>
                            <button id="btn_<?= $controllerUniqueID ?>_New" class="toolbtn btn btn-primary btn-lg shadow-sm"
                                title="<?= lang('Ragnos.Ragnos_new_record_tooltip') ?>">
                                <i class="bi bi-plus-lg me-1"></i>
                            </button>
                        <?php endif; ?>

                        <button id="btn_<?= $controllerUniqueID ?>_Refresh"
                            class="toolbtn btn btn-outline-secondary btn-lg bg-white shadow-sm"
                            title="<?= lang('Ragnos.Ragnos_refresh_data_tooltip') ?>">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>

                        <a href="<?= site_url($clase . '/genericAdvancedReport') ?>"
                            class="toolbtn btn btn-outline-secondary btn-lg bg-white shadow-sm"
                            title="<?= lang('Ragnos.Ragnos_advanced_report_tooltip') ?>">
                            <i class="bi bi-printer"></i>
                        </a>

                        <span
                            class="px-3 py-2 border rounded bg-white text-danger fw-bold shadow-sm Ragnos-searchingtitle align-items-center"
                            id="<?= $controllerUniqueID ?>_searching_title"></span>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive shadow-sm rounded border">
                    <table class="Ragnos_table table table-hover table-striped mb-0 align-middle"
                        id="<?= $controllerUniqueID ?>_table">
                        <thead class="bg-light">
                            <tr>
                                <?php foreach ($tablefields as $fieldItem): ?>
                                    <th class="text-secondary text-uppercase small py-3 fw-bold border-bottom-0">
                                        <?= $fieldlist[$fieldItem]->getLabel(); ?>
                                    </th>
                                <?php endforeach; ?>
                                <th width="30px" class="border-bottom-0"></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div id="<?= $controllerUniqueID ?>_Form" class="tab-pane " role="tabpanel">
                <div id="<?= $controllerUniqueID ?>_FormContent" class="Ragnos-formcontent"></div>
                <div class="card-footer">
                    <button class='btn btn-success' id="<?= $controllerUniqueID ?>btn_ok">
                        <i class="bi bi-check-lg"></i>&nbsp;
                        <?= lang('Ragnos.Ragnos_accept') ?>
                    </button>
                    <button class='btn btn-secondary' id="<?= $controllerUniqueID ?>btn_cancel">
                        <i class="bi bi-arrow-counterclockwise"></i>&nbsp;
                        <?= lang('Ragnos.Ragnos_cancel') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view(
    'App\ThirdParty\Ragnos\Views\rdatasetcontroller/table_view_js',
    [
        'controllerUniqueID' => $controllerUniqueID,
        'tableController'    => $tableController,
        'clase'              => $clase,
        'hasdetails'         => $hasdetails,
        'master'             => $master,
        'sortingField'       => $sortingField,
        'sortingDir'         => $sortingDir
    ]
);
?>
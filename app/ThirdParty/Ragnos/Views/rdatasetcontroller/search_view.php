<?php
$controllerUniqueID = uniqid($controller_name);
$tableController    = $controller_name;
$clase              = mapClassToURL($controller_class);
?>


<div id="<?= $controllerUniqueID ?>" class="card boxshadowround Ragnos-widget Ragnos-search-widget" data-idactivo=""
    data-preselect="">

    <?php if ($title): ?>
        <div class="card-header text-center">
            <h4 style="margin-top:0;margin-bottom: 0;">
                <?= lang('Ragnos.Ragnos_searching') ?>
                <?= $title; ?>
            </h4>
        </div>
    <?php endif; ?>

    <div id="<?= $controllerUniqueID ?>_Tablediv" class="tablediv card-body p-3">
        <!-- Toolbar & Filters -->
        <div
            class="flex-wrap justify-content-between align-items-center bg-light rounded border-start border-4 border-primary shadow-sm mb-2">
            <div id="<?= $controllerUniqueID ?>_combo" class="d-flex align-items-center gap-2 mb-2 mb-lg-0">
                <span class="fw-bold text-secondary text-uppercase small ms-2">
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

            <span
                class="px-3 py-2 border rounded bg-white text-danger fw-bold shadow-sm Ragnos-searchingtitle align-items-center"
                id="<?= $controllerUniqueID ?>_searching_title"></span>
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
    <div class="card-footer">
        <button id="<?= $controllerUniqueID ?>btn_ok_search" class="btn btn-success">
            <i class="bi bi-check-lg"></i>&nbsp;
            <?= lang('Ragnos.Ragnos_accept') ?>
        </button>
        <button id="<?= $controllerUniqueID ?>btn_cancel_search" class="btn btn-secondary">
            <i class="bi bi-arrow-counterclockwise"></i>&nbsp;
            <?= lang('Ragnos.Ragnos_cancel') ?>
        </button>
        <?php

        use App\ThirdParty\Ragnos\Controllers\Ragnos;

        if (
            Ragnos::get_CI()->activeRagnosObject->canUpdate() or
            Ragnos::get_CI()->activeRagnosObject->canInsert()
        ): ?>
            <!-- Quitemos temporalmente el botón para editar el catálogo, hasta decidir qué hacer con él -->
            <!-- <button id="<?= $controllerUniqueID ?>btn_search_admin" class="btn btn-primary col-md-offset-8 col-sm-offset-5">
                <i class="bi bi-pencil"></i>&nbsp;
                <?= lang('Ragnos.Ragnos_edit') ?>
            </button> -->
        <?php endif; ?>

    </div>
</div>

<div id="<?= $controllerUniqueID ?>admin_div" class="card text-bg-danger">
    <div class="card-footer" style="padding:0;">
        <button id="<?= $controllerUniqueID ?>btn_search_admin_back" class="btn btn-success">
            <i class="bi bi-backspace"></i>&nbsp;
            <?= lang('Ragnos.Ragnos_back') ?>
        </button>
    </div>
    <div id="<?= $controllerUniqueID ?>admin_container" class="card-body" style="padding-top:0;"></div>
</div>

<?= view('App\ThirdParty\Ragnos\Views\rdatasetcontroller/search_view_js', ['controllerUniqueID' => $controllerUniqueID, 'tableController' => $tableController, 'clase' => $clase]); ?>
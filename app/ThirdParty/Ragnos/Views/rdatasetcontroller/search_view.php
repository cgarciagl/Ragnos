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
    <h4><span class=" badge text-bg-danger Ragnos-searchingtitle"
            id="<?= $controllerUniqueID ?>_searching_title"></span>
    </h4>

    <div id="<?= $controllerUniqueID ?>_Tablediv" class="tablediv card-body">
        <div id="<?= $controllerUniqueID ?>_combo" style="display:inline;">
            <span>
                <?= lang('Ragnos.Ragnos_in') ?>:
            </span>
            <select name="<?= $controllerUniqueID ?>_sel" id="<?= $controllerUniqueID ?>_sel" class="form-control-sm">
                <option value="">
                    <?= lang('Ragnos.Ragnos_all') ?>
                </option>
                <?php foreach ($tablefields as $fieldItem): ?>
                    <option value="<?= $fieldItem ?>">
                        <?= $fieldlist[$fieldItem]->getLabel(); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <table class="Ragnos_table table table-bordered table-condensed" id="<?= $controllerUniqueID ?>_table">
            <thead>
                <tr>
                    <?php foreach ($tablefields as $fieldItem): ?>
                        <th>
                            <?= $fieldlist[$fieldItem]->getLabel(); ?>
                        </th>
                    <?php endforeach; ?>
                    <th width="30px"></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            </tfoot>
        </table>
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
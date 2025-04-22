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
            <div id="<?= $controllerUniqueID ?>_Tablediv" class="tablediv tab-pane show active" role="tabpanel">
                <div id="<?= $controllerUniqueID ?>_combo" style="display:inline;">
                    <span>
                        <?= lang('Ragnos.Ragnos_in') ?>:
                    </span>
                    <select name="<?= $controllerUniqueID ?>_sel" id="<?= $controllerUniqueID ?>_sel"
                        class="form-control-sm">
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
                <div class="btn-toolbar" style="margin-top:10px;margin-bottom:10px;">

                    <?php if ($modelo->canInsert): ?>
                        <button id="btn_<?= $controllerUniqueID ?>_New" class="toolbtn btn btn-primary">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                    <?php endif; ?>
                    <button id="btn_<?= $controllerUniqueID ?>_Refresh" class="toolbtn btn btn-primary">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>


                    <h4> <span class=" badge text-bg-danger Ragnos-searchingtitle"
                            id="<?= $controllerUniqueID ?>_searching_title"></span>
                    </h4>
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
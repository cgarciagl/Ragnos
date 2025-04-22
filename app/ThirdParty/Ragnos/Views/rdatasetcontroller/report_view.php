<?php
$controllerUniqueID = uniqid($controller_name);
$tableController    = $controller_name;
$clase              = mapClassToURL($controller_class);
?>

<div class="panel panel-primary boxshadowround Ragnos-widget">
    <?php if ($title): ?>
        <div class="panel-heading text-center">
            <h4 style="margin-top:0;margin-bottom: 0;">
                <?= lang('Ragnos.Ragnos_report_of') ?>
                <?= $title; ?>
            </h4>
        </div>
    <?php endif; ?>
    <div class="panel-footer">
        <button id="btnback" style="margin-top:15px;" class="btn btn-primary">
            <i class="bi bi-backspace"></i>&nbsp;
            <?= lang('Ragnos.Ragnos_back') ?>
        </button>
    </div>
    <hr />
    <div class="btn-toolbar" style="margin-left:15px;">
        <div class="btn-toolbar">
            <button id="btn_xls_<?= $controllerUniqueID ?>_View_Report" class="toolbtn btn btn-primary">
                <i class="bi bi-file-earmark-excel"></i> EXCEL
            </button>

            <button id="btn_htm_<?= $controllerUniqueID ?>_View_Report" class="toolbtn btn btn-primary">
                <i class="bi bi-filetype-html"></i> HTML
            </button>
            <!-- <button id="btn_chart_<?= $controllerUniqueID ?>_View_Report" class="toolbtn btn">
                <i class="bi bi-bar-chart"></i>
            </button> -->
        </div>
    </div>
    <div class="boxshadowround row" style="padding:5px;margin:5px;">
        <?=
            form_open("{$tableController}/showReport", ['id' => "{$controllerUniqueID}form_rep", 'method' => 'post'])
            ?>
        <input type="hidden" name="typeofreport" />

        <div class="grupos col-md-12">
            <?php for ($nivelIndex = 1; $nivelIndex <= 3; $nivelIndex++): ?>
                <div class="nivel well rplevel col-md-5 <?php
                if ($nivelIndex > 1) {
                    echo "hide";
                }
                ?>">
                    <h5>
                        <?= lang('Ragnos.Ragnos_group') ?>
                    </h5>
                    <?php
                    echo view('App\ThirdParty\Ragnos\Views\rdatasetcontroller/group_level_control_view', ['fieldlist' => $fieldlist, 'i' => $nivelIndex]);
                    ?>
                    <div class="filtergroup hide">
                        <h5>
                            <?= lang('Ragnos.Ragnos_filter') ?>
                        </h5>

                        <div class=''>
                            <div class='rpfilter input-group input-group-lg'>
                                <input type="text" class="reportgroupfilter form-control" name="filter<?= $nivelIndex ?>" />
                            </div>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
        </form>
    </div>
    <?= view('App\ThirdParty\Ragnos\Views\rdatasetcontroller/report_view_js', ['controllerUniqueID' => $controllerUniqueID, 'tableController' => $tableController]); ?>
</div>
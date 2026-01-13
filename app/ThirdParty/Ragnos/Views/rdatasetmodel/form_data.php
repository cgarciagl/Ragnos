<div class="card">
    <div class="card-body">
        <form class="" role="form" method='post' onsubmit='return false;'>
            <div class="row">
                <?php foreach ($fields as $k => $fieldItem): ?>
                    <?= $fieldItem->constructControl() ?>
                <?php endforeach; ?>
            </div>
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
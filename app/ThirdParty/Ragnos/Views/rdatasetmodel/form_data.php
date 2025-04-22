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
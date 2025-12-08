<?php
// Reemplazar las ocurrencias del carácter '\' por '/'
$controller = str_replace('\\', '/', $controller);
?>

<div class="divfield col-sm-4 mb-1">
    <div class="form-control" id="group_<?= $name ?>">
        <label class="control-label">
            <?= $label; ?> :
        </label>
        <div class="input-group">
            <?= view('App\ThirdParty\Ragnos\Views\rfield/inputfield', [
                'name'             => $name,
                'value'            => $value,
                'type'             => $type,
                'extra_attributes' => $extra_attributes,
                'placeholder'      => $placeholder,
            ]); ?>
        </div>
    </div>
    <script type="text/javascript">
        $('#<?= $name ?>').RagnosSearch({
            controller: '<?= $controller ?>',
            filter: '<?= base64_encode($filter) ?>',
            <?php if ($callback != '') {
                echo "callback: $callback,";
            } ?>
            <?php if ($isRequired) {
                echo "canSetToNull: false,";
            } else {
                echo "canSetToNull: true,";
            } ?>
        });
        $('#Ragnos_id_<?= $name ?>').val('<?= $idvalue ?>').attr('data-valueant', '<?= $idvalue ?>');
    </script>
</div>
<select name="nivel<?= $nivelIndex ?>" class="nivelselect form-control">
    <option value=""></option>
    <?php foreach ($fieldlist as $k => $fieldItem): ?>
        <?php if (method_exists($fieldItem, 'getController')): ?>
            <option value='<?= $fieldItem->getFieldName() ?>' data-controller='<?= $fieldItem->getController() ?>'
                data-filter='<?= base64_encode($fieldItem->getFilter()) ?>'>
                <?= $fieldItem->getLabel() ?>
            </option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>
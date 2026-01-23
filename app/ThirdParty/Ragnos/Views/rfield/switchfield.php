<div class="divfield col-sm-4 mb-3">
    <div class="form-check form-switch pt-2" id='group_<?= $name ?>'>
        <?php
        $onValue  = $onValue ?? '1';
        $offValue = $offValue ?? '0';
        $checked  = (isset($value) && ($value == $onValue || $value === true || $value === 'on')) ? 'checked' : '';
        ?>
        <input type="hidden" name="<?= $name ?>" value="<?= $offValue ?>">
        <input class="form-check-input" type="checkbox" role="switch" id="<?= $name ?>" name="<?= $name ?>"
            value="<?= $onValue ?>" <?= $checked ?> <?= $extra_attributes ?>>
        <label class="form-check-label" for="<?= $name ?>"><?= $label ?></label>
    </div>
</div>
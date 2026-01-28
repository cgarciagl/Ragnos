<div class="divfield col-sm-3 mb-2">
    <div class="form-check form-switch pt-2" id='group_<?= $name ?>'>
        <?php
        $onValue  = $onValue ?? '1';
        $offValue = $offValue ?? '0';
        $checked  = (isset($value) && ($value == $onValue || $value === true || $value === 'on')) ? 'checked' : '';
        ?>
        <input type="hidden" name="<?= $name ?>" value="<?= $offValue ?>" data-valueant="<?= esc($value) ?>">
        <input class="form-check-input" type="checkbox" role="switch" id="<?= $name ?>" name="<?= $name ?>"
            value="<?= $onValue ?>" <?= $checked ?> data-valueant="<?= esc($value) ?>" <?= $extra_attributes ?>>
        <label class="form-check-label" for="<?= $name ?>"><?= $label ?></label>
    </div>
</div>
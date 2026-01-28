<div class="divfield col-sm-12 mb-2">
    <div class="form-floating" id='group_<?= $name ?>'>
        <textarea name="<?= $name ?>" id="<?= $name ?>" class="form-control" style="height: 100px"
            data-valueant='<?= esc($value) ?>' placeholder="<?= $label ?>" <?= $extra_attributes ?>
            autocomplete="off"><?= esc($value) ?></textarea>
        <label for="<?= $name ?>"><?= $label ?></label>
    </div>
</div>
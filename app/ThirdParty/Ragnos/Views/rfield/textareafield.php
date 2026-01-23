<div class="divfield col-sm-12 mb-3">
    <div class="form-floating" id='group_<?= $name ?>'>
        <textarea name="<?= $name ?>" id="<?= $name ?>" class="form-control" style="height: 100px"
            data-valueant='<?= $value ?>' placeholder="<?= $label ?>" <?= $extra_attributes ?>
            autocomplete="off"><?= $value ?></textarea>
        <label for="<?= $name ?>"><?= $label ?></label>
    </div>
</div>
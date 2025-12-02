<div class="divfield col-sm-12 mb-1">
    <div class='form-control shadow-sm' id='group_<?= $name ?>'>
        <label class="control-label">
            <?= $label; ?> :
        </label>

        <div class='input-group'>
            <textarea name="<?= $name ?>" id="<?= $name ?>" class="form-control" rows="4" data-valueant='<?= $value ?>'
                width="100%" <?= $extra_attributes ?> autocomplete="off"><?= $value ?></textarea>
        </div>
    </div>
</div>
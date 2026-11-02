<div class="divfield col-sm-3 mb-2">
    <div id='group_<?= $name ?>'>
        <label class="form-label">
            <?= $label; ?> :
        </label>

        <div class='input-group' id='<?= $name ?>datetimepicker'>
            <input name="<?= $name; ?>" type='datetime-local' step="1" class="form-control"
                value="<?= esc(str_replace(' ', 'T', (string) $value)) ?>"
                data-valueant="<?= esc($value) ?>" <?= $extra_attributes; ?> />
            <span class="input-group-text">
                <i class="bi bi-calendar-event"></i>
            </span>
        </div>
    </div>
</div>

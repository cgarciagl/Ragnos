<div class="divfield col-sm-12 mb-2">
    <div class="form-floating" id="group_<?= $name ?>">
        <select class="form-select" id="<?= $name ?>_select" multiple aria-label="<?= esc($label) ?>"
            <?= $extra_attributes ?>>
            <?php
            $decoded = json_decode($value ?? '', true);
            $tags = json_last_error() === JSON_ERROR_NONE && is_array($decoded)
                ? $decoded
                : array_filter(array_map('trim', explode(',', (string) $value)));
            foreach ($tags as $tag): ?>
                <option value="<?= esc(trim($tag)) ?>" selected><?= esc(trim($tag)) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" id="<?= $name ?>" name="<?= $name ?>" value="<?= esc($value) ?>"
            data-valueant="<?= esc($value) ?>">
        <label for="<?= $name ?>_select"><?= esc($label) ?></label>
    </div>
</div>

<script>
    onReady(() => {
        const select = document.getElementById('<?= $name ?>_select');
        const hidden = document.getElementById('<?= $name ?>');
        if (select.hasAttribute('readonly')) {
            select.disabled = true;
            hidden.removeAttribute('data-valueant');
        }

        const control = new TomSelect(select, {
            placeholder: <?= json_encode($placeholder ?? $label) ?>,
            create: true,
            delimiter: ',',
            plugins: ['remove_button'],
            persist: false
        });
        control.on('change', () => {
            hidden.value = JSON.stringify(control.items);
            dispatchInputEvents(hidden);
        });
    });
</script>

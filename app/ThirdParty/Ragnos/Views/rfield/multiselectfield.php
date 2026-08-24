<div class="divfield col-sm-3 mb-2">
    <div id='group_<?= $name ?>'>
        <label class="form-label">
            <?= $label; ?> :
        </label>
        <?php
        helper('form');
        echo form_dropdown(
            $name . '_Ragnostemp',
            $options,
            $value,
            'class="form-control" multiple="multiple"'
        ); ?>
        <input type="hidden" name="<?= $name; ?>" value="<?= $value; ?>" data-valueant="<?= $value; ?>"
            <?= $extra_attributes; ?> />
    </div>
    <script>
        onReady(() => {
            const select = document.querySelector('select[name="<?= $name ?>_Ragnostemp"]');
            const hidden = document.querySelector('input[name="<?= $name ?>"]');
            const values = hidden.value ? hidden.value.split(',') : [];
            values.forEach((value) => {
                const option = Array.from(select.options).find((item) => item.value === value);
                if (option) option.selected = true;
            });

            const control = new TomSelect(select, {
                plugins: ['remove_button'],
                placeholder: 'Seleccione:'
            });
            control.on('change', () => {
                hidden.value = control.items.join(',');
                dispatchInputEvents(hidden);
            });
            if (hidden.hasAttribute('readonly')) control.disable();
        });
    </script>
</div>

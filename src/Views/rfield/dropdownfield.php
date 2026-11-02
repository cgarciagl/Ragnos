<div class="divfield col-sm-3 mb-2">
    <div class="form-floating" id="group_<?= $name ?>">
        <?php
        helper('form');
        echo form_dropdown(
            $name,
            $options,
            $value,
            'id="' . $name . '" data-valueant="' . $value . '" class="form-select" placeholder="' . $label . '" ' . $extra_attributes
        ); ?>
        <label for="<?= $name ?>"><?= $label ?></label>
    </div>
    <script>
        onReady(() => {
            const select = document.querySelector('select[name="<?= $name ?>"]');
            if (select.hasAttribute('readonly')) {
                select.disabled = true;
                select.removeAttribute('data-valueant');
            }
            new TomSelect(select, {
                placeholder: <?= json_encode($label) ?>,
                allowEmptyOption: true
            });
        });
    </script>
</div>

<div class="divfield col-sm-4 mb-1">
    <div class='form-control shadow-sm' id='group_<?= $name ?>'>
        <label class="control-label">
            <?= $label; ?> :
        </label>
        <?php
        helper('form');
        echo form_dropdown(
            $name,
            $options,
            $value,
            'data-valueant ="' . $value . '" class="form-select" ' . $extra_attributes
        ); ?>
    </div>
    <script>
        $(document).ready(function () {
            p = $('select[name="<?= $name ?>"]');
            if (p.is('[readonly]')) {
                p.prop("disabled", true);
                p.removeAttr("data-valueant");
            }
            p.select2({
                placeholder: "<?= $label; ?>",
                width: '100%',
                dropdownParent: $('#group_<?= $name ?>'),
                theme: 'bootstrap-5',
            });
        });
    </script>
</div>
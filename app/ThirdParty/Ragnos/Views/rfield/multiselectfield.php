<div class="divfield col-sm-4 mb-1">
    <div class='form-control shadow-sm' id='group_<?= $name ?>'>
        <label class="control-label">
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
        $(document).ready(function () {
            var $m = $('select[name="<?= $name ?>_Ragnostemp"]').multiselect({
                buttonClass: 'btn btn-danger',
                nonSelectedText: 'Seleccione:',
                allSelectedText: 'Se ha seleccionado todo ...',
                nSelectedText: ' - ha seleccionado varios ...',
                onChange: function (option, checked, select) {
                    var s = '';
                    var $opciones = $('select[name="<?= $name ?>_Ragnostemp"] option:selected');
                    $opciones.each(function (index, value) {
                        if (s != '') {
                            s = s + ',';
                        }
                        s = s + $(this).attr('value');
                    });
                    $('input[name="<?= $name ?>"]').attr('value', s);
                }
            });

            var s = $('input[name="<?= $name ?>"]').attr('value');
            var $b = s.split(',');
            $('select[name="<?= $name ?>_Ragnostemp"]').multiselect('select', $b, true);

            var p = $('input[name="<?= $name ?>"]');
            if (p.is('[readonly]')) {
                $('select[name="<?= $name ?>_Ragnostemp"]').multiselect('disable');
            }
        });
    </script>
</div>
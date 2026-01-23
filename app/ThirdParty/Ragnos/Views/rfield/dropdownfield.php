<div class="divfield col-sm-4 mb-3">
    <div class="form-floating" id='group_<?= $name ?>'>
        <?php
        helper('form');
        echo form_dropdown(
            $name,
            $options,
            $value,
            'id="' . $name . '" data-valueant ="' . $value . '" class="form-select" placeholder="' . $label . '" ' . $extra_attributes
        ); ?>
        <label for="<?= $name ?>"><?= $label ?></label>
        
        <style>
            /* Ajustes para Select2 dentro de Form Floating */
            #group_<?= $name ?> .select2-container .select2-selection {
                height: 58px; 
                padding-top: 1.625rem;
                padding-bottom: 0.625rem;
            }
            #group_<?= $name ?> .select2-container .select2-selection__arrow {
                height: 58px;
            }
            #group_<?= $name ?> .select2-container .select2-selection__rendered {
                padding-top: 3px;
            }
            #group_<?= $name ?> label {
                z-index: 1051; /* Asegurar visibilidad sobre select2 */
            }
        </style>
    </div>
    <script>
        $(document).ready(function () {
            let p = $('select[name="<?= $name ?>"]');
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
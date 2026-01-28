<div class="divfield col-sm-12 mb-2">
    <div class="form-floating" id='group_<?= $name ?>'>
        <select class="form-select" id="<?= $name ?>_select" multiple="multiple" aria-label="<?= esc($label) ?>"
            <?= $extra_attributes ?>>
            <?php
            $currentValue = $value;
            $tags         = [];

            // Intentar decodificar como JSON primero
            $decoded = json_decode($currentValue ?? '', true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $tags = $decoded;
            } elseif (!empty($currentValue)) {
                // Fallback: tratar como string separado por comas
                $tags = array_map('trim', explode(',', $currentValue));
            }

            foreach ($tags as $tag) {
                $tagVal = trim($tag);
                if (!empty($tagVal)) {
                    echo '<option value="' . esc($tagVal) . '" selected="selected">' . esc($tagVal) . '</option>';
                }
            }
            ?>
        </select>

        <!-- Input oculto para sincronizar el valor en formato JSON string -->
        <input type="hidden" id="<?= $name ?>" name="<?= $name ?>" value="<?= esc($value) ?>"
            data-valueant="<?= esc($value) ?>">
        <label for="<?= $name ?>_select"><?= esc($label) ?></label>

        <style>
            /* Estilos específicos para Select2 Multiple con Form Floating */
            #group_<?= $name ?> .select2-container .select2-selection--multiple {
                min-height: 58px;
                padding-top: 1.625rem;
                padding-bottom: 0.25rem;
            }

            #group_<?= $name ?> .select2-container .select2-selection--multiple .select2-selection__rendered {
                padding-left: 0.5rem;
                margin-top: 0px;
            }

            #group_<?= $name ?> .select2-container .select2-search__field {
                margin-top: 5px;
            }

            #group_<?= $name ?> label {
                z-index: 1051;
                /* Asegurar que el label quede sobre el select2 */
            }
        </style>
    </div>
</div>

<script>
    $(document).ready(function () {
        var $select = $('#<?= $name ?>_select');
        var $hidden = $('#<?= $name ?>');

        if ($select.is('[readonly]')) {
            $select.prop("disabled", true);
            $hidden.removeAttr("data-valueant");
        }

        $select.select2({
            placeholder: "<?= esc($placeholder ?? $label) ?>",
            tags: true,
            width: '100%',
            theme: 'bootstrap-5',
            dropdownParent: $('#group_<?= $name ?>'),
            tokenSeparators: [',']
        });

        // Sincronizar cambios hacia el input oculto
        $select.on('change', function () {
            var valArray = $(this).val();
            // Convertir a string JSON para mantener compatibilidad
            var jsonString = JSON.stringify(valArray ? valArray : []);
            $hidden.val(jsonString);
        });
    });
</script>
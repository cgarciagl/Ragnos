<?php
// Reemplazar las ocurrencias del carácter '\' por '/'
$controller = str_replace('\\', '/', $controller);
?>

<div class="divfield col-sm-3 mb-2">
    <div id="group_<?= $name ?>">
        <div class="input-group" id="inputgroup_<?= $name ?>">
            <div class="form-floating">
                <?= view('App\ThirdParty\Ragnos\Views\rfield/inputfield', [
                    'name'             => $name,
                    'value'            => $value,
                    'type'             => $type,
                    'extra_attributes' => $extra_attributes,
                    'placeholder'      => $label,
                ]); ?>
                <label for="<?= $name ?>"><?= $label ?></label>
            </div>
        </div>
    </div>

    <style>
        /* Ajuste de altura para que el botón de búsqueda coincida con el input flotante (58px) */
        #inputgroup_<?= $name ?> .btn,
        #inputgroup_<?= $name ?> .input-group-text,
        #inputgroup_<?= $name ?> button {
            height: 58px;
            z-index: 5;
        }

        /* Asegurar esquinas correctas en input group con floating labels */
        #inputgroup_<?= $name ?> .form-floating {
            flex-grow: 1;
        }

        #inputgroup_<?= $name ?> .form-floating .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
    </style>

    <script type="text/javascript">
        $(document).ready(function () {
            var $input = $('#<?= $name ?>');

            $input.RagnosSearch({
                controller: '<?= $controller ?>',
                filter: '<?= base64_encode($filter) ?>',
                <?php if ($callback != '') {
                    echo "callback: $callback,";
                } ?>
                <?php if ($isRequired) {
                    echo "canSetToNull: false,";
                } else {
                    echo "canSetToNull: true,";
                } ?>
            });
            $('#Ragnos_id_<?= $name ?>').val('<?= $idvalue ?>').attr('data-valueant', '<?= $idvalue ?>');

            // FIX: Si RagnosSearch insertó el botón dentro del .form-floating, moverlo al .input-group
            // Esto corrige el problema de "botón fuera de control" o mal alineado
            var $floatingDiv = $input.closest('.form-floating');
            var $searchBtn = $floatingDiv.contents().filter(function () {
                // Filtramos elementos que no sean el input original ni el label
                return this.id !== '<?= $name ?>' && this.tagName !== 'LABEL' && this.nodeType === 1;
            });

            if ($searchBtn.length > 0) {
                $searchBtn.appendTo($('#inputgroup_<?= $name ?>'));
                // Asegurar clase btn-outline-secondary o input-group-text si le falta estilo
                if (!$searchBtn.hasClass('btn') && !$searchBtn.hasClass('input-group-text')) {
                    $searchBtn.addClass('btn btn-outline-secondary');
                }
            }
        });
    </script>
</div>
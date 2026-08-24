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
        onReady(() => {
            const input = document.getElementById('<?= $name ?>');
            new RagnosSearch(input, {
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
            const hidden = document.getElementById('Ragnos_id_<?= $name ?>');
            hidden.value = <?= json_encode((string) $idvalue) ?>;
            hidden.dataset.valueant = <?= json_encode((string) $idvalue) ?>;
        });
    </script>
</div>

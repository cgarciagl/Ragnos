<div class="divfield col-sm-12 mb-3">
    <div class="form-floating" id='group_<?= $name ?>'>
        <textarea name="<?= $name ?>" id="<?= $name ?>" class="form-control"
            placeholder="<?= $label ?>"><?= $value ?></textarea>
        <label for="<?= $name ?>"><?= $label ?></label>

        <style>
            /* Integración de Summernote con estilo Floating Labels */
            #group_<?= $name ?>>label {
                /* Forzamos el estado "flotante" permanente */
                transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
                opacity: 0.65;
                z-index: 50;
                /* Por encima de la toolbar */
                width: auto;
                height: auto;
            }

            #group_<?= $name ?> .note-editor.note-frame {
                /* Coincidir estilo de borde con Bootstrap 5 */
                border-color: #dee2e6;
                border-radius: 0.375rem;
                box-shadow: none;
            }

            /* Crear espacio seguro para la etiqueta flotante en la barra de herramientas */
            #group_<?= $name ?> .note-toolbar {
                padding-top: 1.6rem;
                background-color: #f8f9fa;
                /* Fondo ligero para la barra */
                border-top-left-radius: 0.375rem;
                border-top-right-radius: 0.375rem;
            }
        </style>
    </div>
</div>

<script src="<?= base_url(); ?>/assets/js/summernote-bs5.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?= base_url(); ?>/assets/css/summernote-bs5.css" type="text/css" media="all" />
<script>
    $(document).ready(function () {
        $("textarea[name='<?= $name ?>']").summernote({
            callbacks: {
                onChange: function (contents, $editable) {
                    $("textarea[name='<?= $name ?>']").val(contents);
                }
            },
            height: 200,
            width: '100%',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
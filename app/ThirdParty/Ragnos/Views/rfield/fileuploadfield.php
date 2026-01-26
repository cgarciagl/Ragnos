<div class="divfield col-sm-4 mb-3">
    <label class="form-label" for="<?= $name ?>"><?= $label ?></label>
    <div class="position-relative" id='group_<?= $name ?>'>
        <div class="p-4 text-center border rounded bg-light"
            style="border: 2px dashed #ccc !important; position: relative;"
            ondragover="this.style.borderColor='#0d6efd'; this.style.backgroundColor='#e9ecef'; event.preventDefault();"
            ondragleave="this.style.borderColor='#ccc'; this.style.backgroundColor='#f8f9fa';"
            ondrop="this.style.borderColor='#ccc'; this.style.backgroundColor='#f8f9fa';">

            <input type="file" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;"
                id="<?= $name ?>" name="<?= $name ?>" data-valueant="<?= esc($value) ?>" <?= $extra_attributes ?? '' ?>
                onchange="updateFileBox_<?= $name ?>(this)">

            <input type="hidden" name="Ragnos_current_<?= $name ?>" value="<?= esc($value) ?>">

            <div id="placeholder_<?= $name ?>">
                <?php if (!empty($value)): ?>
                    <div class="mb-2 fs-2">📄</div>
                    <div class="text-break fw-bold"><?= esc($value) ?></div>
                    <div class="small text-muted mt-1">Click o arrastra para reemplazar</div>
                <?php else: ?>
                    <div class="mb-2 fs-2">📂</div>
                    <div class="fw-bold">Arrastra tu archivo aquí</div>
                    <div class="small text-muted">o haz click para buscar</div>
                <?php endif; ?>
            </div>

            <div id="new_file_info_<?= $name ?>" class="d-none">
                <div class="mb-2 fs-2 text-primary">🆕</div>
                <div class="text-break fw-bold text-primary" id="new_filename_<?= $name ?>"></div>
            </div>
        </div>

        <?php if (!empty($value)): ?>
            <div class="mt-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="delete_<?= $name ?>" name="delete_<?= $name ?>"
                        value="1">
                    <label class="form-check-label text-danger small" for="delete_<?= $name ?>">
                        Eliminar archivo actual
                    </label>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateFileBox_<?= $name ?>(input) {
            var placeholder = document.getElementById('placeholder_<?= $name ?>');
            var newInfo = document.getElementById('new_file_info_<?= $name ?>');
            var newName = document.getElementById('new_filename_<?= $name ?>');
            var delCheck = document.getElementById('delete_<?= $name ?>');

            if (input.files && input.files[0]) {
                placeholder.classList.add('d-none');
                newInfo.classList.remove('d-none');
                newName.textContent = input.files[0].name;
                if (delCheck) {
                    delCheck.checked = false;
                    delCheck.parentElement.style.display = 'none'; // Ocultar opción borrar si se reemplaza
                }
            } else {
                // Revert
                placeholder.classList.remove('d-none');
                newInfo.classList.add('d-none');
                if (delCheck) {
                    delCheck.parentElement.style.display = 'block';
                }
            }
        }
    </script>
</div>
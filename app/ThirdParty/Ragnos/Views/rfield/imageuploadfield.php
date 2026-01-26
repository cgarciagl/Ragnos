<div class="divfield col-sm-4 mb-3">
    <label class="form-label" for="<?= $name ?>"><?= $label ?></label>
    <div class="position-relative" id='group_<?= $name ?>'>
        <div class="p-2 text-center border rounded bg-light d-flex justify-content-center align-items-center"
            style="border: 2px dashed #ccc !important; min-height: 200px; position: relative; overflow: hidden;"
            ondragover="this.style.borderColor='#0d6efd'; this.style.backgroundColor='#e9ecef'; event.preventDefault();"
            ondragleave="this.style.borderColor='#ccc'; this.style.backgroundColor='#f8f9fa';"
            ondrop="this.style.borderColor='#ccc'; this.style.backgroundColor='#f8f9fa';">

            <input type="file" class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                style="cursor: pointer; z-index: 5;" id="<?= $name ?>" name="<?= $name ?>" accept="image/*"
                data-valueant="<?= esc($value) ?>" <?= $extra_attributes ?? '' ?>
                onchange="previewImage_<?= $name ?>(this)">

            <input type="hidden" name="Ragnos_current_<?= $name ?>" value="<?= esc($value) ?>">

            <!-- Current/Preview Image Layer -->
            <div id="preview_container_<?= $name ?>"
                class="w-100 h-100 d-flex justify-content-center align-items-center flex-column">
                <?php if (!empty($value)): ?>
                    <img src="<?= esc($value) ?>" id="img_prev_<?= $name ?>" class="img-fluid rounded shadow-sm"
                        style="max-height: 180px; z-index: 1;"
                        onerror="this.onerror=null;this.src='';this.parentElement.innerHTML='<div class=\'text-danger\'>Error cargando imagen</div>';">
                <?php else: ?>
                    <div id="placeholder_icon_<?= $name ?>" class="text-muted">
                        <div class="fs-1">🖼️</div>
                        <small>Arrastra o selecciona imagen</small>
                    </div>
                <?php endif; ?>
                <img id="new_img_prev_<?= $name ?>" class="img-fluid rounded shadow-sm d-none"
                    style="max-height: 180px; z-index: 2;">
            </div>
        </div>

        <?php if (!empty($value)): ?>
            <div class="mt-1 text-end">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="delete_<?= $name ?>" name="delete_<?= $name ?>"
                        value="1">
                    <label class="form-check-label text-danger small" for="delete_<?= $name ?>">
                        Eliminar imagen actual
                    </label>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function previewImage_<?= $name ?>(input) {
            var existingImg = document.getElementById('img_prev_<?= $name ?>');
            var newImg = document.getElementById('new_img_prev_<?= $name ?>');
            var placeholder = document.getElementById('placeholder_icon_<?= $name ?>');
            var delCheck = document.getElementById('delete_<?= $name ?>');

            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    if (existingImg) existingImg.classList.add('d-none');
                    if (placeholder) placeholder.classList.add('d-none');

                    newImg.src = e.target.result;
                    newImg.classList.remove('d-none');

                    if (delCheck) {
                        delCheck.checked = false;
                        delCheck.parentElement.style.display = 'none'; // Hide delete option when replaced
                    }
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                newImg.classList.add('d-none');
                if (existingImg) existingImg.classList.remove('d-none');
                if (placeholder) placeholder.classList.remove('d-none');
                if (delCheck) delCheck.parentElement.style.display = 'inline-block';
            }
        }
    </script>
</div>
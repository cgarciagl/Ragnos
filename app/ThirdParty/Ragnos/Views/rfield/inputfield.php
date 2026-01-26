<input type="<?= $type ?>" name="<?= $name ?>" id="<?= $name ?>" value="<?= esc($value) ?>" class="form-control" <?php if (App\ThirdParty\Ragnos\Controllers\Ragnos::config()->Ragnos_all_to_uppercase): ?>
        onChange="this.value = this.value.toUpperCase();" <?php endif; ?> data-valueant="<?= esc($value) ?>"
    placeholder="<?= $placeholder ?>" <?= $extra_attributes ?> autocomplete="off">
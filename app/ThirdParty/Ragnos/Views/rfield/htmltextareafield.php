<div class="divfield col-sm-4 mb-1">
    <div class='form-control shadow-sm' id='group_<?= $name ?>'>
        <label class="control-label">
            <?= $label; ?> :
        </label>

        <div class='input-group'>
            <textarea name="<?= $name ?>" id="<?= $name ?>">
            <?= $value ?>
        </textarea>
        </div>
    </div>
</div>

<script src="<?= base_url(); ?>/assets/js/summernote-bs5.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?= base_url(); ?>/assets/css/summernote-bs5.css" type="text/css" media="all" />
<script>
    $(document).ready(function () {
        $("textarea[name='<?= $name ?>']").summernote({
            onkeyup: function (e) {
                $("textarea[name='<?= $name ?>']").val($("#divsummernote<?= $name ?>").code().trim());
            },
            height: 100
        });
    });
</script>
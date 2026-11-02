<div class="divfield col-sm-12 mb-2">
    <div class="form-floating" id='group_<?= $name ?>'>
        <textarea name="<?= $name ?>" id="<?= $name ?>" class="form-control"
            placeholder="<?= esc($label, 'attr') ?>"><?= esc($value) ?></textarea>
        <label for="<?= $name ?>"><?= esc($label) ?></label>

    </div>
</div>

<script>
    onReady(() => {
        new RagnosRichTextEditor(document.querySelector("textarea[name='<?= $name ?>']"));
    });
</script>

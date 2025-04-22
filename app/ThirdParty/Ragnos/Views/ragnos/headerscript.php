<script type="text/javascript">
    var base_url = '<?= base_url() ?>';
    var wait_label = '<?= lang('Ragnos.Ragnos_wait') ?>';

    var Ragnos_csrf = {};

    <?php if (csrf_token()): ?>
        Ragnos_csrf['<?= csrf_token() ?>'] = '<?= csrf_hash(); ?>';
    <?php endif; ?>
</script>
<?= $this->extend('template/template_lte_simple') ?>

<?= $this->section('content') ?>


<div class="login-box">
    <div class="login-logo">
        <img src="./img/logo.webp" alt="Logo de la empresa" class="img-fluid" style="max-width: 75%;">
    </div>
    <div class="card card-outline card-primary shadow-lg">
        <div class="card-body login-card-body">
            <p class="login-box-msg fw-bold">
                <i class="bi bi-person-circle"></i> <?= lang('Admin.login_title') ?>
            </p>

            <form action="<?= site_url('admin/login'); ?>" method="post" id="formlogin">
                <div class="mb-3">
                    <label for="usuario" class="form-label"><?= lang('Admin.username_label') ?></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="usuario" name="usuario"
                            placeholder="<?= lang('Admin.username_placeholder') ?>" value="<?= set_value('usuario'); ?>"
                            required>
                        <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="pword" class="form-label"><?= lang('Admin.password_label') ?></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="pword" name="pword"
                            placeholder="<?= lang('Admin.password_placeholder') ?>" value="<?= set_value('pword'); ?>"
                            required>
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    </div>
                </div>

                <?php if (!empty($errors) && request()->is('post')): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> <?= esc($error) ?></p>
                        <?php endforeach; ?>
                    </div>

                    <script>
                        onReady(() => {
                            shakeElement(document.getElementById('formlogin'));
                        });
                    </script>
                <?php endif; ?>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary"><?= lang('Admin.login_button') ?></button>
                </div>
            </form>

            <div class="mt-3 text-center">
                <small class="text-muted">
                    <?= lang('Admin.demo_credentials_text') ?>
                </small>
            </div>

        </div>
    </div>
</div>


<script>
    onReady(() => {
        document.querySelector("input[name='usuario']").focus();
    });
</script>

<link rel="stylesheet" href="<?= base_url(); ?>/assets/css/login.css">

<?= $this->endSection() ?>

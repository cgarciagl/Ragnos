<?= $this->extend('template/template_lte_simple') ?>

<?= $this->section('content') ?>


<div class="login-box">
    <div class="login-logo">
        <img src="./img/logo.webp" alt="Logo de la empresa" class="img-fluid" style="max-width: 75%;">
    </div>
    <div class="card card-outline card-primary shadow-lg">
        <div class="card-body login-card-body">
            <p class="login-box-msg fw-bold">
                <i class="bi bi-person-circle"></i> Identifíquese
            </p>

            <form action="<?= site_url('admin/login'); ?>" method="post" id="formlogin">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="usuario" name="usuario"
                            placeholder="Ingrese su usuario" value="<?= set_value('usuario'); ?>" required>
                        <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="pword" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="pword" name="pword"
                            placeholder="Ingrese su contraseña" value="<?= set_value('pword'); ?>" required>
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
                        $(function () {
                            shakeElement(document.getElementById('formlogin'));
                        });
                    </script>
                <?php endif; ?>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Ingresar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(function () {
        $("input[name='usuario']").focus();
    });
    const numParticles = 50; // Número de partículas

    function createParticle() {
        const particle = document.createElement("div");
        particle.classList.add("particle");

        // Posición inicial aleatoria
        particle.style.top = `${Math.random() * 100}vh`;
        particle.style.left = `${Math.random() * 100}vw`;

        // Movimiento aleatorio
        particle.style.setProperty("--x", `${(Math.random() - 0.5) * 200}px`);
        particle.style.setProperty("--y", `${(Math.random() - 0.5) * 200}px`);

        // Tamaño aleatorio
        const size = Math.random() * 10 + 5;
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;

        document.body.appendChild(particle);

        // Eliminar y recrear después de la animación
        setTimeout(() => {
            particle.remove();
            createParticle();
        }, 10000);
    }

    // Crear las partículas iniciales
    for (let i = 0; i < numParticles; i++) {
        createParticle();
    }
</script>

<style>
    body {
        background-color: #1e1e2f;
        /* Color base oscuro elegante */
        background-image:
            linear-gradient(45deg, rgba(255, 255, 255, 0.3) 25%, transparent 25%, transparent 75%, rgba(255, 255, 255, 0.3) 75%, rgba(255, 255, 255, 0.3)),
            linear-gradient(-45deg, rgba(255, 255, 255, 0.3) 25%, transparent 25%, transparent 75%, rgba(255, 255, 255, 0.3) 75%, rgba(255, 255, 255, 0.3));
        background-size: 150px 150px;
        /* Tamaño del patrón */
        background-attachment: fixed;
        color: #ffffff;
        /* Texto legible */
        font-family: 'Poppins', sans-serif;

        overflow: hidden;
        position: relative;
        width: 100vw;
        height: 100vh;
    }

    .particle {
        position: absolute;
        width: 8px;
        height: 8px;
        background-color: orangered;
        /* Azul brillante */
        border-radius: 50%;
        box-shadow: 0 0 8px yellow;
        animation: moveParticle 10s linear infinite;
    }

    @keyframes moveParticle {
        0% {
            transform: translate(0, 0);
            opacity: 0.5;
        }

        50% {
            opacity: 1;
        }

        100% {
            transform: translate(var(--x), var(--y));
            opacity: 0.2;
        }
    }

    .login-box .card {
        z-index: 1;
    }
</style>

<?= $this->endSection() ?>
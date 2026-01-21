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

            <div class="mt-3 text-center">
                <small class="text-muted">Use <strong>admin</strong> / <strong>admin</strong> para ingresar al
                    demo</small>
            </div>

        </div>
    </div>
</div>


<script>
    $(function () {
        $("input[name='usuario']").focus();
    });
    document.addEventListener("DOMContentLoaded", () => {
        let canvas = document.getElementById("particles-canvas");
        if (!canvas) {
            canvas = document.createElement("canvas");
            canvas.id = "particles-canvas";
            canvas.style.position = "fixed";
            canvas.style.top = "0";
            canvas.style.left = "0";
            canvas.style.width = "100%";
            canvas.style.height = "100%";
            canvas.style.zIndex = "0";
            canvas.style.pointerEvents = "none";
            document.body.appendChild(canvas);
        }

        const ctx = canvas.getContext("2d");
        let particlesArray;

        const resizeCanvas = () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        };

        window.addEventListener("resize", () => {
            resizeCanvas();
            initParticles();
        });
        resizeCanvas();

        const getFireColor = () => {
            const colors = [
                "255, 165, 0", // Orange
                "255, 69, 0", // Red-Orange
                "255, 215, 0", // Gold
                "234, 88, 12", // Brand Orange
            ];
            return colors[Math.floor(Math.random() * colors.length)];
        };

        class Particle {
            constructor(x, y, dx, dy, size, alpha, color) {
                this.x = x;
                this.y = y;
                this.dx = dx;
                this.dy = dy;
                this.size = size;
                this.alpha = alpha;
                this.color = color;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2, false);
                ctx.fillStyle = `rgba(${this.color}, ${this.alpha})`;
                ctx.shadowBlur = 10;
                ctx.shadowColor = `rgba(${this.color}, 0.8)`;
                ctx.fill();
                ctx.shadowBlur = 0;
            }
            update() {
                if (this.x > canvas.width) this.x = 0;
                if (this.x < 0) this.x = canvas.width;
                if (this.y < -10) this.y = canvas.height + 10;

                this.x += this.dx;
                this.y += this.dy;

                if (Math.random() > 0.95) {
                    this.alpha = Math.random() * 0.5 + 0.5;
                }

                this.draw();
            }
        }

        function initParticles() {
            particlesArray = [];
            let numberOfParticles = (canvas.height * canvas.width) / 15000;
            for (let i = 0; i < numberOfParticles; i++) {
                let size = Math.random() * 3 + 1;
                let x = Math.random() * canvas.width;
                let y = Math.random() * canvas.height;
                let dx = (Math.random() - 0.5) * 1;
                let dy = -Math.random() * 1.5 - 0.5;
                let alpha = Math.random() * 0.6 + 0.4;
                let color = getFireColor();
                particlesArray.push(new Particle(x, y, dx, dy, size, alpha, color));
            }
        }

        function animateParticles() {
            requestAnimationFrame(animateParticles);
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particlesArray.forEach((p) => p.update());
        }

        initParticles();
        animateParticles();

        $('body').removeClass('bg-body-secondary');
    });
</script>

<style>
    body {
        background-color: #05060a !important;
        color: #ffffff;
        overflow-x: hidden;
        line-height: 1.6;
        min-height: 100vh;
    }

    .login-box .card {
        z-index: 1;
    }
</style>

<?= $this->endSection() ?>
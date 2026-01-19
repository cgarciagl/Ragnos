document.addEventListener("DOMContentLoaded", () => {
  /* --- Language Switcher --- */
  let currentLang = navigator.language.startsWith("es") ? "es" : "en";

  const updateLang = () => {
    const showEs = currentLang === "es";
    document.querySelectorAll('[data-lang="es"]').forEach((el) => {
      el.classList.toggle("hidden", !showEs);
    });
    document.querySelectorAll('[data-lang="en"]').forEach((el) => {
      el.classList.toggle("hidden", showEs);
    });
    
    // Update button text
    const btn = document.getElementById("langToggle");
    if(btn) btn.innerText = showEs ? "EN / ES" : "ES / EN";
  };

  // Initial call
  updateLang();

  const langBtn = document.getElementById("langToggle");
  if (langBtn) {
    langBtn.addEventListener("click", () => {
      currentLang = currentLang === "es" ? "en" : "es";
      updateLang();
    });
  }

  /* --- Particles Background --- */
  const canvas = document.getElementById("particles-canvas");
  if (canvas) {
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

    const particleColorRGB = "234, 88, 12"; // Primary orange

    class Particle {
      constructor(x, y, dx, dy, size, alpha) {
        this.x = x;
        this.y = y;
        this.dx = dx;
        this.dy = dy;
        this.size = size;
        this.alpha = alpha;
      }
      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2, false);
        ctx.fillStyle = `rgba(${particleColorRGB}, ${this.alpha})`;
        ctx.fill();
      }
      update() {
        if (this.x > canvas.width || this.x < 0) this.dx = -this.dx;
        if (this.y > canvas.height || this.y < 0) this.dy = -this.dy;
        this.x += this.dx;
        this.y += this.dy;
        this.draw();
      }
    }

    function initParticles() {
      particlesArray = [];
      let numberOfParticles = (canvas.height * canvas.width) / 25000;
      for (let i = 0; i < numberOfParticles; i++) {
        let size = Math.random() * 2 + 1;
        let x = Math.random() * canvas.width;
        let y = Math.random() * canvas.height;
        let dx = (Math.random() - 0.5) * 0.5;
        let dy = (Math.random() - 0.5) * 0.5;
        let alpha = Math.random() * 0.5 + 0.2;
        particlesArray.push(new Particle(x, y, dx, dy, size, alpha));
      }
    }

    function connectParticles() {
      for (let a = 0; a < particlesArray.length; a++) {
        for (let b = a; b < particlesArray.length; b++) {
          let dx = particlesArray[a].x - particlesArray[b].x;
          let dy = particlesArray[a].y - particlesArray[b].y;
          let distance = dx * dx + dy * dy;

          if (distance < (canvas.width / 7) * (canvas.height / 7)) {
            let opacityValue = 1 - distance / 20000;
            if (opacityValue > 0) {
              ctx.strokeStyle = `rgba(${particleColorRGB}, ${opacityValue * 0.2})`;
              ctx.lineWidth = 1;
              ctx.beginPath();
              ctx.moveTo(particlesArray[a].x, particlesArray[a].y);
              ctx.lineTo(particlesArray[b].x, particlesArray[b].y);
              ctx.stroke();
            }
          }
        }
      }
    }

    function animateParticles() {
      requestAnimationFrame(animateParticles);
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      particlesArray.forEach((p) => p.update());
      connectParticles();
    }

    initParticles();
    animateParticles();
  }
});

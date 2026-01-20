document.addEventListener("DOMContentLoaded", () => {
  /* --- Language Switcher --- */
  let currentLang = navigator.language.startsWith("es") ? "es" : "en";

  const updateLang = () => {
    const showEs = currentLang === "es";
    
    // Helper to toggle visibility and pause video if hidden
    const toggleElement = (el, isVisible) => {
      el.classList.toggle("hidden", !isVisible);
      if (!isVisible && el.tagName === "IFRAME") {
        el.contentWindow?.postMessage(
          '{"event":"command","func":"pauseVideo","args":""}',
          "*"
        );
      }
    };

    document.querySelectorAll('[data-lang="es"]').forEach((el) => toggleElement(el, showEs));
    document.querySelectorAll('[data-lang="en"]').forEach((el) => toggleElement(el, !showEs));
    
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

    // Fire Spark Colors: Bright Orange, Gold, Red-Orange
    const getFireColor = () => {
      const colors = [
        "255, 165, 0", // Orange
        "255, 69, 0",  // Red-Orange
        "255, 215, 0", // Gold
        "234, 88, 12"  // Brand Orange
      ];
      return colors[Math.floor(Math.random() * colors.length)];
    };

    class Particle {
      constructor(x, y, dx, dy, size, alpha, color) {
        this.x = x;
        this.y = y;
        this.dx = dx; // Horizontal drift
        this.dy = dy; // Vertical rise
        this.size = size;
        this.alpha = alpha;
        this.color = color;
      }
      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2, false);
        ctx.fillStyle = `rgba(${this.color}, ${this.alpha})`;
        // Add a glow effect
        ctx.shadowBlur = 10;
        ctx.shadowColor = `rgba(${this.color}, 0.8)`;
        ctx.fill();
        ctx.shadowBlur = 0; // Reset
      }
      update() {
        // Wrap around horizontally
        if (this.x > canvas.width) this.x = 0;
        if (this.x < 0) this.x = canvas.width;
        
        // Wrap around vertically (bottom to top if rising, or top to bottom)
        // Since we want sparks to rise, they go up (dy is negative).
        // If they go off top, reset to bottom.
        if (this.y < -10) this.y = canvas.height + 10;
        
        this.x += this.dx;
        this.y += this.dy;
        
        // Random slight flicker
        if(Math.random() > 0.95) {
           this.alpha = Math.random() * 0.5 + 0.5;
        }
        
        this.draw();
      }
    }

    function initParticles() {
      particlesArray = [];
      // Increase density slightly
      let numberOfParticles = (canvas.height * canvas.width) / 15000;
      for (let i = 0; i < numberOfParticles; i++) {
        let size = Math.random() * 3 + 1; // Slightly larger
        let x = Math.random() * canvas.width;
        let y = Math.random() * canvas.height;
        // Upward movement mostly
        let dx = (Math.random() - 0.5) * 1; 
        let dy = -Math.random() * 1.5 - 0.5; // Always negative (up), speed 0.5 to 2
        let alpha = Math.random() * 0.6 + 0.4; // Brighter
        let color = getFireColor();
        particlesArray.push(new Particle(x, y, dx, dy, size, alpha, color));
      }
    }

    // Connect particles logic removed to make them look more like individual sparks
    // But we can keep a subtle version if requested. User asked for "sparks", 
    // usually sparks don't connect. Let's remove connections for a cleaner "fire" look.
    
    function animateParticles() {
      requestAnimationFrame(animateParticles);
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      particlesArray.forEach((p) => p.update());
    }

    initParticles();
    animateParticles();
  }
});

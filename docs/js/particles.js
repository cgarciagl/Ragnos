document.addEventListener("DOMContentLoaded", function () {
  const numParticles = 50; // Número de partículas

  function createParticle() {
    const particle = document.createElement("div");
    particle.classList.add("particle");

    // Posición inicial aleatoria
    // Usamos document.documentElement.scrollHeight para cubrir todo el scroll
    const updatePosition = () => {
      const maxHeight = Math.max(
        document.body.scrollHeight,
        document.body.offsetHeight,
        document.documentElement.clientHeight,
        document.documentElement.scrollHeight,
        document.documentElement.offsetHeight
      );

      particle.style.top = `${Math.random() * maxHeight}px`;
      particle.style.left = `${Math.random() * 100}vw`;
    };

    updatePosition();

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
      if (document.hidden) {
        // Si la tab no está activa, no generamos carga cpu innecesaria
        return;
      }
      createParticle();
    }, 10000);
  }

  // Crear las partículas iniciales
  for (let i = 0; i < numParticles; i++) {
    createParticle();
  }

  // Reactivar loop si se vuelve a la tab
  document.addEventListener("visibilitychange", function () {
    if (
      !document.hidden &&
      document.querySelectorAll(".particle").length < numParticles
    ) {
      for (let i = 0; i < numParticles; i++) {
        createParticle();
      }
    }
  });
});

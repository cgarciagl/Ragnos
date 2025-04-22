// SSE Progress Tracker
class SSEProgressTracker {
  constructor(url, options = {}) {
    this.url = fixUrl(url);
    this.eventSource = null;
    this.onProgressTitle = options.onProgressTitle || this.defaultProgressTitle;
    this.onProgress = options.onProgress || this.defaultProgress;
    this.onProgressText = options.onProgressText || this.defaultProgressText;
    this.onComplete = options.onComplete || this.defaultComplete;
  }

  start() {
    // Close any existing connection
    if (this.eventSource) {
      this.eventSource.close();
    }

    // Create new EventSource connection
    this.eventSource = new EventSource(this.url);

    // Set up event listeners
    this.eventSource.addEventListener("progress_title", (event) => {
      this.onProgressTitle(event.data);
    });

    this.eventSource.addEventListener("progress", (event) => {
      const percentage = parseInt(event.data, 10);
      this.onProgress(percentage);
    });

    this.eventSource.addEventListener("progress_text", (event) => {
      this.onProgressText(event.data);
    });

    this.eventSource.addEventListener("process_complete", (event) => {
      const data = JSON.parse(event.data);
      this.onComplete(data);
      this.eventSource.close();
    });

    // Handle any connection errors
    this.eventSource.onerror = (error) => {
      console.error("SSE Connection Error:", error);
      this.eventSource.close();
    };
  }

  // Default handlers (can be overridden in constructor)
  defaultProgressTitle(title) {
    const titleElement = document.getElementById("textotitulo");
    if (titleElement) titleElement.textContent = title;
  }

  defaultProgress(percentage) {
    const progressBar = document.getElementById("pbar");
    if (progressBar) {
      progressBar.style.width = `${percentage}%`;
      progressBar.textContent = `${percentage}%`;
    }
  }

  defaultProgressText(text) {
    const progressText = document.getElementById("textopbar");
    if (progressText) progressText.textContent = text;
  }

  defaultComplete(data) {
    console.log("Process completed in", data.time, "seconds");

    // Remove spinner
    const spinner = document.getElementById("girando");
    if (spinner) spinner.classList.remove("spinner-border");

    // Optional: Add a "Back to Home" button
    const progressDiv = document.getElementById("divprogreso");
    if (progressDiv) {
      const backButton = document.createElement("div");
      const message =
        data.additionalData?.message ||
        (window.language === "en" ? "Process Completed" : "Proceso Completado");
      const backButtonText =
        window.language === "en" ? "Back to Home" : "Volver al Inicio";
      const timeText = window.language === "en" ? "seconds" : "segundos";

      backButton.innerHTML = `
            <hr>
            <h6>${message} , ${data.time} ${timeText}</h6>
            <a href="${window.base_url || "/"}" class="btn btn-success col-3">
              <i class="bi bi-house-door-fill"></i> ${backButtonText}
            </a>
        `;
      progressDiv.after(backButton);
    }
  }
}

// Usage example
// function initSSEProgress() {
//   const tracker = new SSEProgressTracker("/your-process-endpoint");
//   tracker.start();
// }

// Call this when you want to start tracking progress
// initSSEProgress();

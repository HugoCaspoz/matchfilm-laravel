document.addEventListener("DOMContentLoaded", () => {
  // Animación para botones
  const buttons = document.querySelectorAll(".btn")
  buttons.forEach((button) => {
    button.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-3px)"
    })
    button.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)"
    })
  })

  // Notificaciones temporales
  const alerts = document.querySelectorAll(".alert")
  const bootstrap = window.bootstrap // Declare the bootstrap variable
  if (typeof bootstrap !== "undefined") {
    alerts.forEach((alert) => {
      setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert)
        bsAlert.close()
      }, 5000)
    })
  }

  // Búsqueda en tiempo real (opcional)
  const searchInput = document.querySelector('input[name="query"]')
  if (searchInput) {
    let typingTimer
    const doneTypingInterval = 1000 // 1 segundo

    searchInput.addEventListener("keyup", function () {
      clearTimeout(typingTimer)
      if (this.value && this.value.length >= 3) {
        // Solo buscar si hay al menos 3 caracteres
        typingTimer = setTimeout(() => {
          document.querySelector('form[action*="friends/search"]').submit()
        }, doneTypingInterval)
      }
    })
  }
})

document.addEventListener("DOMContentLoaded", () => {
  // Referencias a elementos del DOM
  const matchesContainer = document.getElementById("matches-container")
  const watchButtons = document.querySelectorAll(".btn-watch")
  const watchModal = document.getElementById("watchModal")
  const friendNameSpan = document.getElementById("friendName")
  const watchDateInput = document.getElementById("watchDate")
  const watchMessageInput = document.getElementById("watchMessage")
  const sendInviteBtn = document.getElementById("sendInviteBtn")
  const alertContainer = document.getElementById("alert-container")

  // Obtener el token CSRF para las peticiones POST
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

  // Obtener el nombre del amigo seleccionado
  const selectedFriendName = document.querySelector(".friend-item.active .friend-name")?.textContent.trim()

  // Función para mostrar alertas
  function showAlert(message, type = "success") {
    if (alertContainer) {
      alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
          <strong>${message}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `

      // Auto-cerrar la alerta después de 3 segundos
      setTimeout(() => {
        const alert = alertContainer.querySelector(".alert")
        if (alert) {
          alert.classList.remove("show")
          setTimeout(() => {
            alertContainer.innerHTML = ""
          }, 300)
        }
      }, 3000)
    }
  }

  // Importar Bootstrap
  const bootstrap = window.bootstrap

  // Inicializar el modal de Bootstrap
  let watchModalInstance
  if (typeof bootstrap !== "undefined" && watchModal) {
    watchModalInstance = new bootstrap.Modal(watchModal)
  }

  // Manejar clics en los botones "Ver juntos"
  watchButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const movieId = this.getAttribute("data-movie-id")
      const movieTitle = this.getAttribute("data-movie-title")
      // Reemplazar esta línea:
      // const movieTitle = this.closest(".match-card").querySelector(".match-info h3").textContent

      // Con esta:
      // const movieTitle = this.getAttribute("data-movie-title")

      // Configurar el modal
      if (friendNameSpan) friendNameSpan.textContent = selectedFriendName || "tu amigo"
      if (watchDateInput) watchDateInput.value = new Date().toISOString().split("T")[0] // Fecha actual
      if (watchMessageInput) watchMessageInput.value = ""

      // Almacenar datos para el envío
      if (sendInviteBtn) {
        sendInviteBtn.setAttribute("data-movie-id", movieId)
        sendInviteBtn.setAttribute("data-movie-title", movieTitle)
      }

      // Mostrar el modal
      if (watchModalInstance) {
        watchModalInstance.show()
      }
    })
  })

  // Manejar el envío de invitación
  if (sendInviteBtn) {
    sendInviteBtn.addEventListener("click", function () {
      const movieId = this.getAttribute("data-movie-id")
      const movieTitle = this.getAttribute("data-movie-title")
      const watchDate = watchDateInput ? watchDateInput.value : ""
      const watchMessage = watchMessageInput ? watchMessageInput.value : ""

      // Validar fecha
      if (!watchDate) {
        showAlert("Por favor, selecciona una fecha para ver la película.", "warning")
        return
      }

      // Aquí iría la lógica para enviar la invitación
      // Por ahora, solo mostraremos un mensaje de éxito
      showAlert(`Invitación enviada para ver "${movieTitle}" el ${watchDate}.`, "success")

      // Cerrar el modal
      if (watchModalInstance) {
        watchModalInstance.hide()
      }
    })
  }

  // Manejar el despliegue de descripciones al hacer hover
  const matchCards = document.querySelectorAll(".match-card")
  matchCards.forEach((card) => {
    const overview = card.querySelector(".match-overview")
    if (overview) {
      card.addEventListener("mouseenter", () => {
        overview.style.transform = "translateY(0)"
      })

      card.addEventListener("mouseleave", () => {
        overview.style.transform = "translateY(101%)"
      })
    }
  })
})

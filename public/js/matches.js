document.addEventListener("DOMContentLoaded", () => {
  // Referencias a elementos del DOM
  const favoriteButtons = document.querySelectorAll(".favorite-btn")
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
  favoriteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const movieId = this.getAttribute("data-movie-id")
      const movieTitle = this.getAttribute("data-movie-title")

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

  // Modificar el evento de clic del botón sendInviteBtn para enviar una solicitud AJAX
  if (sendInviteBtn) {
    sendInviteBtn.addEventListener("click", function () {
      const movieId = this.getAttribute("data-movie-id")
      const movieTitle = this.getAttribute("data-movie-title")
      const watchDate = watchDateInput ? watchDateInput.value : ""
      const watchMessage = watchMessageInput ? watchMessageInput.value : ""
      const friendId = document.querySelector(".friend-item.active")?.getAttribute("href")?.split("friend_id=")[1]

      // Validar fecha
      if (!watchDate) {
        showAlert("Por favor, selecciona una fecha para ver la película.", "warning")
        return
      }

      // Enviar la invitación al servidor
      fetch("/notifications/movie-invitation", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
          friend_id: friendId,
          movie_id: movieId,
          movie_title: movieTitle,
          watch_date: watchDate,
          message: watchMessage,
        }),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Error al enviar la invitación")
          }
          return response.json()
        })
        .then((data) => {
          showAlert(`Invitación enviada para ver "${movieTitle}" el ${watchDate}.`, "success")

          // Cerrar el modal
          if (watchModalInstance) {
            watchModalInstance.hide()
          }
        })
        .catch((error) => {
          console.error("Error:", error)
          showAlert("Error al enviar la invitación. Inténtalo de nuevo.", "danger")
        })
    })
  }

  // Manejar el despliegue de descripciones al hacer hover
  const movieCards = document.querySelectorAll(".movie-card")
  movieCards.forEach((card) => {
    const overview = card.querySelector(".movie-overview")
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

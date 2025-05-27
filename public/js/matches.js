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

  // Función para mostrar alertas mejorada
  function showAlert(message, type = "success") {
    if (alertContainer) {
      // Mapear tipos de alerta
      const alertTypeMap = {
        success: "success",
        error: "danger",
        warning: "warning",
        danger: "danger",
      }

      const alertType = alertTypeMap[type] || "success"

      alertContainer.innerHTML = `
        <div class="alert alert-${alertType} alert-dismissible fade show" role="alert">
          <strong>${message}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `

      // Auto-cerrar la alerta después de 5 segundos
      setTimeout(() => {
        const alert = alertContainer.querySelector(".alert")
        if (alert) {
          alert.classList.remove("show")
          setTimeout(() => {
            alertContainer.innerHTML = ""
          }, 300)
        }
      }, 5000)
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
    button.addEventListener("click", function (e) {
      e.preventDefault() // Prevenir comportamiento por defecto

      const movieId = this.getAttribute("data-movie-id")
      const movieTitle = this.getAttribute("data-movie-title")
      const friendId = this.getAttribute("data-friend-id")

      // Validar que tenemos los datos necesarios
      if (!movieId || !movieTitle || !friendId) {
        showAlert("Error: Faltan datos de la película o amigo", "error")
        return
      }

      // Configurar el modal
      if (friendNameSpan) friendNameSpan.textContent = selectedFriendName || "tu amigo"

      // Establecer fecha mínima como hoy y valor por defecto
      if (watchDateInput) {
        const today = new Date().toISOString().split("T")[0]
        watchDateInput.min = today
        watchDateInput.value = today
      }

      if (watchMessageInput) watchMessageInput.value = ""

      // Almacenar datos para el envío
      if (sendInviteBtn) {
        sendInviteBtn.setAttribute("data-movie-id", movieId)
        sendInviteBtn.setAttribute("data-movie-title", movieTitle)
        sendInviteBtn.setAttribute("data-friend-id", friendId)
      }

      // Actualizar título del modal
      const modalTitle = document.getElementById("watchModalLabel")
      if (modalTitle) {
        modalTitle.textContent = `Ver "${movieTitle}" juntos`
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
      const friendId = this.getAttribute("data-friend-id")
      const watchDate = watchDateInput ? watchDateInput.value : ""
      const watchMessage = watchMessageInput ? watchMessageInput.value : ""

      // Validaciones
      if (!movieId || !movieTitle || !friendId) {
        showAlert("Error: Faltan datos necesarios para enviar la invitación", "error")
        return
      }

      if (!watchDate) {
        showAlert("Por favor, selecciona una fecha para ver la película.", "warning")
        return
      }

      // Validar que la fecha no sea en el pasado
      const selectedDate = new Date(watchDate)
      const today = new Date()
      today.setHours(0, 0, 0, 0)

      if (selectedDate < today) {
        showAlert("La fecha no puede ser en el pasado.", "warning")
        return
      }

      // Deshabilitar el botón y mostrar loading
      const originalText = this.textContent
      this.disabled = true
      this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Enviando...'

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
          // Manejar diferentes códigos de estado
          if (!response.ok) {
            return response.json().then((data) => {
              throw new Error(data.message || `Error ${response.status}: ${response.statusText}`)
            })
          }
          return response.json()
        })
        .then((data) => {
          if (data.success) {
            showAlert(data.message || `Invitación enviada para ver "${movieTitle}" el ${watchDate}.`, "success")

            // Cerrar el modal
            if (watchModalInstance) {
              watchModalInstance.hide()
            }

            // Limpiar el formulario
            if (watchMessageInput) watchMessageInput.value = ""
          } else {
            showAlert(data.message || "Error al enviar la invitación", "error")
          }
        })
        .catch((error) => {
          console.error("Error:", error)
          showAlert(error.message || "Error al enviar la invitación. Inténtalo de nuevo.", "error")
        })
        .finally(() => {
          // Rehabilitar el botón
          this.disabled = false
          this.textContent = originalText
        })
    })
  }

  // Limpiar datos cuando se cierra el modal
  if (watchModal) {
    watchModal.addEventListener("hidden.bs.modal", () => {
      if (watchMessageInput) watchMessageInput.value = ""
      if (sendInviteBtn) {
        sendInviteBtn.removeAttribute("data-movie-id")
        sendInviteBtn.removeAttribute("data-movie-title")
        sendInviteBtn.removeAttribute("data-friend-id")
      }
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

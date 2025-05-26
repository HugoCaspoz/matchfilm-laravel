document.addEventListener("DOMContentLoaded", () => {
  // Funciones auxiliares
  function showAlert(type, message) {
    const alertContainer = document.getElementById("alert-container")
    if (alertContainer) {
      const alert = document.createElement("div")
      alert.className = `alert alert-${type} alert-dismissible fade show`
      alert.innerHTML = `
        <strong>${message}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `
      alertContainer.innerHTML = ""
      alertContainer.appendChild(alert)

      // Scroll to top to show the alert
      window.scrollTo({ top: 0, behavior: "smooth" })
    }
  }

  // Validación del nombre de amigo en tiempo real
  const nombreAmigoInput = document.getElementById("nombreAmigo")
  if (nombreAmigoInput) {
    nombreAmigoInput.addEventListener("input", function () {
      const nombreAmigo = this.value.trim()
      const usernameError = document.getElementById("usernameError")

      if (!usernameError) return

      if (nombreAmigo.length > 0 && nombreAmigo.length < 5) {
        usernameError.textContent = "El nombre de usuario debe tener al menos 5 caracteres."
        usernameError.style.color = "red"
      } else if (nombreAmigo.length > 255) {
        usernameError.textContent = "El nombre de usuario es demasiado largo."
        usernameError.style.color = "red"
      } else {
        usernameError.textContent = ""
      }
    })

    // Permitir enviar con Enter
    nombreAmigoInput.addEventListener("keypress", function(event) {
      if (event.key === "Enter") {
        event.preventDefault()
        const btnAgregarAmigo = document.getElementById("btnAgregarAmigo")
        if (btnAgregarAmigo) {
          btnAgregarAmigo.click()
        }
      }
    })
  }

  // Botón para agregar amigo desde el formulario
  const btnAgregarAmigo = document.getElementById("btnAgregarAmigo")
  if (btnAgregarAmigo) {
    btnAgregarAmigo.addEventListener("click", (event) => {
      event.preventDefault()

      const nombreAmigoInput = document.getElementById("nombreAmigo")
      const usernameError = document.getElementById("usernameError")

      if (!nombreAmigoInput) {
        console.error("No se encontró el input nombreAmigo")
        return
      }

      const nombreAmigo = nombreAmigoInput.value.trim()

      // Limpiar errores previos
      if (usernameError) {
        usernameError.textContent = ""
      }

      if (nombreAmigo.length < 5) {
        if (usernameError) {
          usernameError.textContent = "El nombre de usuario debe tener al menos 5 caracteres."
        }
        return
      }

      if (nombreAmigo.length > 255) {
        if (usernameError) {
          usernameError.textContent = "El nombre de usuario es demasiado largo."
        }
        return
      }

      agregarAmigo(nombreAmigo)
    })
  }

  // Manejar notificaciones
  const markAsReadButtons = document.querySelectorAll(".mark-as-read")
  if (markAsReadButtons.length > 0) {
    markAsReadButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const notificationId = this.getAttribute("data-notification-id")
        const notificationCard = this.closest(".notification-card")

        // Enviar solicitud para marcar como leída
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

        fetch(`/notifications/read/${notificationId}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
          },
        })
          .then((response) => {
            if (response.ok) {
              // Animar la desaparición de la notificación
              if (notificationCard) {
                notificationCard.style.transition = "opacity 0.3s ease"
                notificationCard.style.opacity = "0"
                setTimeout(() => {
                  notificationCard.remove()

                  // Verificar si no hay más notificaciones
                  const remainingNotifications = document.querySelectorAll(".notification-card")
                  if (remainingNotifications.length === 0) {
                    const notificationList = document.querySelector("#notificaciones")
                    if (notificationList) {
                      notificationList.innerHTML = '<p class="text-white-50 text-center">No tienes notificaciones</p>'
                    }
                  }
                }, 300)
              }
            } else {
              console.error("Error al marcar notificación como leída")
            }
          })
          .catch((error) => console.error("Error:", error))
      })
    })
  }
})

// Función para enviar solicitud de amistad (global para usar desde onclick)
function agregarAmigo(friendIdentifier) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

  // Encontrar el botón correspondiente y deshabilitarlo
  let button = null
  if (typeof friendIdentifier === 'string' && !isNaN(friendIdentifier)) {
    // Es un ID numérico, buscar por data-user-id
    button = document.querySelector(`[data-user-id="${friendIdentifier}"]`)
  } else {
    // Es un username, buscar el botón principal
    button = document.getElementById("btnAgregarAmigo")
  }

  if (button) {
    button.disabled = true
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...'
  }

  // Mostrar indicador de carga
  showAlert("info", "Enviando solicitud...")

  fetch("/friends/request", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": csrfToken,
      "Accept": "application/json"
    },
    body: JSON.stringify({
      friend_id: friendIdentifier.toString().trim()
    }),
  })
    .then((response) => {
      return response.json().then(data => {
        if (response.ok) {
          return data
        } else {
          throw new Error(data.message || "Error al enviar solicitud de amistad")
        }
      })
    })
    .then((data) => {
      // Mostrar mensaje de éxito
      showAlert("success", data.message || "Solicitud de amistad enviada correctamente!")

      // Limpiar el campo de entrada si existe
      const inputField = document.getElementById("nombreAmigo")
      if (inputField) {
        inputField.value = ""
      }

      const usernameError = document.getElementById("usernameError")
      if (usernameError) {
        usernameError.textContent = ""
      }

      // Recargar la página después de 2 segundos
      setTimeout(() => {
        window.location.reload()
      }, 2000)
    })
    .catch((error) => {
      // Mostrar mensaje de error
      showAlert("danger", error.message)
    })
    .finally(() => {
      // Rehabilitar botón
      if (button) {
        button.disabled = false
        if (button.id === "btnAgregarAmigo") {
          button.innerHTML = '<i class="fas fa-user-plus me-2"></i>Enviar solicitud de amistad'
        } else {
          button.innerHTML = '<i class="fas fa-user-plus me-1"></i> Agregar'
        }
      }
    })
}

// Función para eliminar amigo (global para usar desde onclick)
function eliminarAmigo(friendId) {
  if (confirm("¿Estás seguro de que quieres eliminar a este amigo?")) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

    // Mostrar indicador de carga
    showAlert("info", "Eliminando amigo...")

    fetch(`/friends/remove/${friendId}`, {
      method: "DELETE",
      headers: {
        "X-CSRF-TOKEN": csrfToken,
        "Accept": "application/json"
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Error al eliminar el amigo")
        }

        // Mostrar mensaje de éxito
        showAlert("success", "Amigo eliminado correctamente!")

        // Recargar la página después de 2 segundos
        setTimeout(() => {
          window.location.reload()
        }, 2000)
      })
      .catch((error) => {
        // Mostrar mensaje de error
        showAlert("danger", `Error al eliminar el amigo: ${error.message}`)
      })
  }
}

// Función auxiliar para mostrar alertas
function showAlert(type, message) {
  const alertContainer = document.getElementById("alert-container")
  if (alertContainer) {
    const alert = document.createElement("div")
    alert.className = `alert alert-${type} alert-dismissible fade show`
    alert.innerHTML = `
      <strong>${message}</strong>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `
    alertContainer.innerHTML = ""
    alertContainer.appendChild(alert)

    // Scroll to top to show the alert
    window.scrollTo({ top: 0, behavior: "smooth" })
  }
}

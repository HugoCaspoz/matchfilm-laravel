document.addEventListener("DOMContentLoaded", () => {
  console.log("Friends.js cargado correctamente")

  // ===== FUNCIONES AUXILIARES =====

  function showAlert(type, message) {
    const alertContainer = document.getElementById("alert-container")
    if (alertContainer) {
      // Limpiar alertas anteriores
      alertContainer.innerHTML = ""

      const alert = document.createElement("div")
      alert.className = `alert alert-${type} alert-dismissible fade show`
      alert.innerHTML = `
        <strong>${message}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `
      alertContainer.appendChild(alert)

      // Scroll suave hacia arriba para mostrar la alerta
      window.scrollTo({ top: 0, behavior: "smooth" })

      // Auto-cerrar después de 5 segundos para alertas de éxito
      if (type === "success") {
        setTimeout(() => {
          const alertElement = alertContainer.querySelector(".alert")
          if (alertElement) {
            alertElement.classList.remove("show")
            setTimeout(() => {
              if (alertContainer.contains(alertElement)) {
                alertContainer.removeChild(alertElement)
              }
            }, 300)
          }
        }, 5000)
      }
    }
  }

  function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]')
    if (!token) {
      console.error("CSRF token no encontrado")
      return null
    }
    return token.getAttribute("content")
  }

  function disableButton(button, loadingText = "Cargando...") {
    if (button) {
      button.disabled = true
      button.dataset.originalText = button.innerHTML
      button.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${loadingText}`
    }
  }

  function enableButton(button) {
    if (button && button.dataset.originalText) {
      button.disabled = false
      button.innerHTML = button.dataset.originalText
      delete button.dataset.originalText
    }
  }

  // ===== VALIDACIÓN EN TIEMPO REAL =====

  const nombreAmigoInput = document.getElementById("nombreAmigo")
  const usernameError = document.getElementById("usernameError")

  if (nombreAmigoInput && usernameError) {
    nombreAmigoInput.addEventListener("input", function () {
      const nombreAmigo = this.value.trim()

      // Limpiar errores previos
      usernameError.textContent = ""
      usernameError.style.color = "red"

      if (nombreAmigo.length > 0 && nombreAmigo.length < 3) {
        usernameError.textContent = "El nombre de usuario debe tener al menos 3 caracteres."
      } else if (nombreAmigo.length > 255) {
        usernameError.textContent = "El nombre de usuario es demasiado largo."
      } else if (nombreAmigo.length > 0 && !/^[a-zA-Z0-9_.-]+$/.test(nombreAmigo)) {
        usernameError.textContent = "Solo se permiten letras, números, guiones y puntos."
      }
    })

    // Permitir enviar con Enter
    nombreAmigoInput.addEventListener("keypress", (event) => {
      if (event.key === "Enter") {
        event.preventDefault()
        const btnAgregarAmigo = document.getElementById("btnAgregarAmigo")
        if (btnAgregarAmigo && !btnAgregarAmigo.disabled) {
          btnAgregarAmigo.click()
        }
      }
    })
  }

  // ===== BOTÓN AGREGAR AMIGO =====

  const btnAgregarAmigo = document.getElementById("btnAgregarAmigo")
  if (btnAgregarAmigo) {
    btnAgregarAmigo.addEventListener("click", (event) => {
      event.preventDefault()

      if (!nombreAmigoInput) {
        console.error("No se encontró el input nombreAmigo")
        showAlert("danger", "Error: Campo de nombre de usuario no encontrado")
        return
      }

      const nombreAmigo = nombreAmigoInput.value.trim()

      // Limpiar errores previos
      if (usernameError) {
        usernameError.textContent = ""
      }

      // Validaciones
      if (!nombreAmigo) {
        if (usernameError) {
          usernameError.textContent = "Por favor, ingresa un nombre de usuario."
        }
        nombreAmigoInput.focus()
        return
      }

      if (nombreAmigo.length < 3) {
        if (usernameError) {
          usernameError.textContent = "El nombre de usuario debe tener al menos 3 caracteres."
        }
        nombreAmigoInput.focus()
        return
      }

      if (nombreAmigo.length > 255) {
        if (usernameError) {
          usernameError.textContent = "El nombre de usuario es demasiado largo."
        }
        nombreAmigoInput.focus()
        return
      }

      if (!/^[a-zA-Z0-9_.-]+$/.test(nombreAmigo)) {
        if (usernameError) {
          usernameError.textContent = "Solo se permiten letras, números, guiones y puntos."
        }
        nombreAmigoInput.focus()
        return
      }

      // Enviar solicitud
      window.agregarAmigo(nombreAmigo)
    })
  }

  // ===== NOTIFICACIONES =====

  const markAsReadButtons = document.querySelectorAll(".mark-as-read")
  if (markAsReadButtons.length > 0) {
    markAsReadButtons.forEach((button) => {
      button.addEventListener("click", function (event) {
        event.preventDefault()

        const notificationId = this.getAttribute("data-notification-id")
        const notificationCard = this.closest(".notification-card")
        const csrfToken = getCsrfToken()

        if (!csrfToken) {
          showAlert("danger", "Error de seguridad. Recarga la página.")
          return
        }

        if (!notificationId) {
          showAlert("danger", "Error: ID de notificación no válido")
          return
        }

        // Deshabilitar botón
        disableButton(this, "Marcando...")

        fetch(`/notifications/read/${notificationId}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            Accept: "application/json",
          },
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error(`Error ${response.status}: ${response.statusText}`)
            }
            return response.json()
          })
          .then((data) => {
            // Animar la desaparición de la notificación
            if (notificationCard) {
              notificationCard.style.transition = "opacity 0.3s ease, transform 0.3s ease"
              notificationCard.style.opacity = "0"
              notificationCard.style.transform = "translateX(100%)"

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

            showAlert("success", "Notificación marcada como leída")
          })
          .catch((error) => {
            console.error("Error al marcar notificación:", error)
            showAlert("danger", "Error al marcar la notificación como leída")
            enableButton(this)
          })
      })
    })
  }
})

// ===== FUNCIONES GLOBALES =====

// Función para enviar solicitud de amistad
window.agregarAmigo = (friendIdentifier) => {
  const csrfToken = window.getCsrfToken()

  if (!csrfToken) {
    window.showAlert("danger", "Error de seguridad. Recarga la página.")
    return
  }

  if (!friendIdentifier || friendIdentifier.toString().trim() === "") {
    window.showAlert("danger", "Error: Identificador de usuario no válido")
    return
  }

  // Encontrar el botón correspondiente
  let button = null
  if (typeof friendIdentifier === "string" && !isNaN(friendIdentifier)) {
    // Es un ID numérico, buscar por data-user-id
    button = document.querySelector(`[data-user-id="${friendIdentifier}"]`)
  } else {
    // Es un username, buscar el botón principal
    button = document.getElementById("btnAgregarAmigo")
  }

  // Deshabilitar botón
  if (button) {
    window.disableButton(button, "Enviando...")
  }

  // Mostrar indicador de carga
  window.showAlert("info", "Enviando solicitud de amistad...")

  fetch("/friends/request", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": csrfToken,
      Accept: "application/json",
    },
    body: JSON.stringify({
      friend_id: friendIdentifier.toString().trim(),
    }),
  })
    .then((response) => {
      return response.json().then((data) => {
        if (response.ok) {
          return data
        } else {
          throw new Error(data.message || `Error ${response.status}: ${response.statusText}`)
        }
      })
    })
    .then((data) => {
      // Mostrar mensaje de éxito
      window.showAlert("success", data.message || "¡Solicitud de amistad enviada correctamente!")

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
      console.error("Error al enviar solicitud:", error)
      window.showAlert("danger", error.message || "Error al enviar la solicitud de amistad")
    })
    .finally(() => {
      // Rehabilitar botón
      if (button) {
        window.enableButton(button)
      }
    })
}

// Función para eliminar amigo
window.eliminarAmigo = (friendId) => {
  if (!friendId) {
    window.showAlert("danger", "Error: ID de amigo no válido")
    return
  }

  if (!confirm("¿Estás seguro de que quieres eliminar a este amigo? Esta acción no se puede deshacer.")) {
    return
  }

  const csrfToken = window.getCsrfToken()

  if (!csrfToken) {
    window.showAlert("danger", "Error de seguridad. Recarga la página.")
    return
  }

  // Encontrar y deshabilitar el botón de eliminar
  const deleteButton = document.querySelector(`[onclick*="eliminarAmigo(${friendId})"]`)
  if (deleteButton) {
    window.disableButton(deleteButton, "Eliminando...")
  }

  // Mostrar indicador de carga
  window.showAlert("info", "Eliminando amigo...")

  fetch(`/friends/remove/${friendId}`, {
    method: "DELETE",
    headers: {
      "X-CSRF-TOKEN": csrfToken,
      Accept: "application/json",
      "Content-Type": "application/json",
    },
  })
    .then((response) => {
      if (!response.ok) {
        return response
          .json()
          .then((data) => {
            throw new Error(data.message || `Error ${response.status}: ${response.statusText}`)
          })
          .catch(() => {
            throw new Error(`Error ${response.status}: ${response.statusText}`)
          })
      }

      // Si la respuesta es exitosa, verificar si hay contenido JSON
      const contentType = response.headers.get("content-type")
      if (contentType && contentType.includes("application/json")) {
        return response.json()
      } else {
        return { success: true, message: "Amigo eliminado correctamente" }
      }
    })
    .then((data) => {
      // Mostrar mensaje de éxito
      window.showAlert("success", data.message || "¡Amigo eliminado correctamente!")

      // Animar la eliminación del elemento de la lista
      const friendCard = deleteButton ? deleteButton.closest(".friend-card, .card, .list-group-item") : null
      if (friendCard) {
        friendCard.style.transition = "opacity 0.3s ease, transform 0.3s ease"
        friendCard.style.opacity = "0"
        friendCard.style.transform = "translateX(-100%)"

        setTimeout(() => {
          friendCard.remove()

          // Verificar si no hay más amigos
          const remainingFriends = document.querySelectorAll(".friend-card, .friend-item")
          if (remainingFriends.length === 0) {
            const friendsList = document.querySelector("#friends-list, .friends-container")
            if (friendsList) {
              friendsList.innerHTML = '<p class="text-center text-muted">No tienes amigos agregados</p>'
            }
          }
        }, 300)
      } else {
        // Si no se puede animar, recargar después de 1 segundo
        setTimeout(() => {
          window.location.reload()
        }, 1000)
      }
    })
    .catch((error) => {
      console.error("Error al eliminar amigo:", error)
      window.showAlert("danger", error.message || "Error al eliminar el amigo")

      // Rehabilitar botón en caso de error
      if (deleteButton) {
        window.enableButton(deleteButton)
      }
    })
}

// Log para confirmar que el script se cargó
console.log("Friends.js completamente cargado y listo")

// Exponer funciones auxiliares globalmente
window.getCsrfToken = getCsrfToken
window.showAlert = showAlert
window.disableButton = disableButton
window.enableButton = enableButton

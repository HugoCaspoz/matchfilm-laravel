// Funciones globales para notificaciones
window.acceptInvitation = (notificationId) => {
  const button = event.target
  const originalText = button.innerHTML

  // Mostrar loading
  button.disabled = true
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aceptando...'

  fetch(`/notifications/${notificationId}/accept`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message, "success")
        // Recargar la página para actualizar la vista
        setTimeout(() => {
          window.location.reload()
        }, 1000)
      } else {
        showNotification(data.message || "Error al aceptar la invitación", "error")
        button.disabled = false
        button.innerHTML = originalText
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Error al aceptar la invitación", "error")
      button.disabled = false
      button.innerHTML = originalText
    })
}

window.declineInvitation = (notificationId) => {
  if (confirm("¿Estás seguro de que quieres declinar esta invitación?")) {
    const button = event.target
    const originalText = button.innerHTML

    // Mostrar loading
    button.disabled = true
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Declinando...'

    fetch(`/notifications/${notificationId}/decline`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification(data.message, "success")
          // Recargar la página para actualizar la vista
          setTimeout(() => {
            window.location.reload()
          }, 1000)
        } else {
          showNotification(data.message || "Error al declinar la invitación", "error")
          button.disabled = false
          button.innerHTML = originalText
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showNotification("Error al declinar la invitación", "error")
        button.disabled = false
        button.innerHTML = originalText
      })
  }
}

// NUEVAS FUNCIONES PARA SOLICITUDES DE AMISTAD
window.acceptFriendRequest = (friendshipId) => {
  const button = event.target
  const originalText = button.innerHTML

  // Mostrar loading
  button.disabled = true
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aceptando...'

  fetch(`/friends/accept/${friendshipId}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
      Accept: "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message, "success")

        // Ocultar los botones y mostrar estado procesado
        const notificationItem = button.closest(".notification-item")
        const friendRequestButtons = notificationItem.querySelector(".friend-request-buttons")
        const headerContent = notificationItem.querySelector(".notification-header-content")

        // Agregar clase processed
        notificationItem.classList.add("processed")

        // Ocultar botones de acción
        if (friendRequestButtons) {
          friendRequestButtons.style.display = "none"
        }

        // Agregar badge de estado
        if (!headerContent.querySelector(".processed-status")) {
          const statusDiv = document.createElement("div")
          statusDiv.className = "processed-status"
          statusDiv.innerHTML = `
            <span class="status-badge accepted">
              <i class="fas fa-check"></i> Solicitud aceptada
            </span>
          `
          headerContent.appendChild(statusDiv)
        }
      } else {
        showNotification(data.message || "Error al aceptar la solicitud", "error")
        button.disabled = false
        button.innerHTML = originalText
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Error al aceptar la solicitud", "error")
      button.disabled = false
      button.innerHTML = originalText
    })
}

window.rejectFriendRequest = (friendshipId) => {
  if (confirm("¿Estás seguro de que quieres rechazar esta solicitud de amistad?")) {
    const button = event.target
    const originalText = button.innerHTML

    // Mostrar loading
    button.disabled = true
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rechazando...'

    fetch(`/friends/reject/${friendshipId}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        Accept: "application/json",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification(data.message, "success")

          // Ocultar los botones y mostrar estado procesado
          const notificationItem = button.closest(".notification-item")
          const friendRequestButtons = notificationItem.querySelector(".friend-request-buttons")
          const headerContent = notificationItem.querySelector(".notification-header-content")

          // Agregar clase processed
          notificationItem.classList.add("processed")

          // Ocultar botones de acción
          if (friendRequestButtons) {
            friendRequestButtons.style.display = "none"
          }

          // Agregar badge de estado
          if (!headerContent.querySelector(".processed-status")) {
            const statusDiv = document.createElement("div")
            statusDiv.className = "processed-status"
            statusDiv.innerHTML = `
              <span class="status-badge declined">
                <i class="fas fa-times"></i> Solicitud rechazada
              </span>
            `
            headerContent.appendChild(statusDiv)
          }
        } else {
          showNotification(data.message || "Error al rechazar la solicitud", "error")
          button.disabled = false
          button.innerHTML = originalText
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showNotification("Error al rechazar la solicitud", "error")
        button.disabled = false
        button.innerHTML = originalText
      })
  }
}

// Función para mostrar notificaciones toast
function showNotification(message, type) {
  const notification = document.createElement("div")
  notification.className = `toast-notification ${type}`
  notification.innerHTML = `
        <i class="fas fa-${type === "success" ? "check" : "exclamation-triangle"}"></i>
        <span>${message}</span>
    `

  document.body.appendChild(notification)

  // Mostrar la notificación
  setTimeout(() => {
    notification.classList.add("show")
  }, 100)

  // Ocultar después de 3 segundos
  setTimeout(() => {
    notification.classList.remove("show")
    setTimeout(() => {
      if (document.body.contains(notification)) {
        document.body.removeChild(notification)
      }
    }, 300)
  }, 3000)
}

document.addEventListener("DOMContentLoaded", () => {
  console.log("Notifications JS loaded")

  // Auto-actualizar notificaciones cada 30 segundos
  setInterval(() => {
    fetch("/notifications/count")
      .then((response) => response.json())
      .then((data) => {
        const badge = document.querySelector(".notification-badge")
        if (badge) {
          if (data.count > 0) {
            badge.textContent = data.count
            badge.style.display = "inline"
          } else {
            badge.style.display = "none"
          }
        }
      })
      .catch((error) => console.error("Error al actualizar notificaciones:", error))
  }, 30000)
})

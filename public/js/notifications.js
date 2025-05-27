// Funcionalidad para declinar invitaciones
function declineInvitation(notificationId) {
  if (confirm("¿Estás seguro de que quieres declinar esta invitación?")) {
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
          // Recargar la página para actualizar las notificaciones
          location.reload()
        } else {
          alert("Error al declinar la invitación: " + data.message)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al declinar la invitación")
      })
  }
}

// Auto-actualizar notificaciones cada 30 segundos
setInterval(() => {
  fetch("/notifications/unread-count")
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

document.addEventListener("DOMContentLoaded", () => {
  // Verificar si el usuario está logueado
  const token = localStorage.getItem("token")
  const username = localStorage.getItem("username")
  const userDropdown = document.getElementById("userDropdown")
  const navActions = document.querySelector(".nav-actions")

  // Importar Bootstrap
  const bootstrap = window.bootstrap

  // Marcar el enlace activo según la página actual
  const currentPage = window.location.pathname
  const navLinks = document.querySelectorAll(".nav-link")

  navLinks.forEach((link) => {
    const href = link.getAttribute("href")
    if (href && currentPage.includes(href.split("/").pop())) {
      link.classList.add("active")
    }
  })

  // Efecto de scroll para el header
  window.addEventListener("scroll", () => {
    const header = document.querySelector(".app-header")
    if (header) {
      if (window.scrollY > 10) {
        header.classList.add("scrolled")
      } else {
        header.classList.remove("scrolled")
      }
    }
  })

  // Inicializar dropdowns de Bootstrap
  const dropdownElementList = [].slice.call(document.querySelectorAll(".dropdown-toggle"))
  if (typeof bootstrap !== "undefined") {
    const dropdownList = dropdownElementList.map((dropdownToggleEl) => new bootstrap.Dropdown(dropdownToggleEl))
  }

  // Verificar notificaciones no leídas
  const checkUnreadNotifications = () => {
    if (document.querySelector('meta[name="csrf-token"]')) {
      fetch("/notifications/count")
        .then((response) => response.json())
        .then((data) => {
          const notificationLink = document.querySelector('a[href*="notifications"]')
          if (notificationLink && data.count > 0) {
            // Añadir indicador de notificaciones no leídas
            if (!notificationLink.querySelector(".notification-badge")) {
              const badge = document.createElement("span")
              badge.className = "notification-badge"
              badge.textContent = data.count
              notificationLink.appendChild(badge)
            } else {
              notificationLink.querySelector(".notification-badge").textContent = data.count
            }
          }
        })
        .catch((error) => console.error("Error al verificar notificaciones:", error))
    }
  }

  // Si el usuario está autenticado, verificar notificaciones
  if (document.querySelector('a[href*="notifications"]')) {
    checkUnreadNotifications()
    // Verificar cada 30 segundos
    setInterval(checkUnreadNotifications, 30000)
  }

  // Función para mostrar alertas
  function showAlert(message) {
    const alertContainer = document.createElement("div")
    alertContainer.className = "alert-container"
    alertContainer.innerHTML = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `
    document.body.appendChild(alertContainer)

    // Eliminar la alerta después de 5 segundos
    setTimeout(() => {
      alertContainer.remove()
    }, 5000)
  }
})

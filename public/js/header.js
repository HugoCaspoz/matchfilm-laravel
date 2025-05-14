document.addEventListener("DOMContentLoaded", () => {
    // Verificar si el usuario está logueado
    const token = localStorage.getItem("token")
    const username = localStorage.getItem("username")
    const userDropdown = document.getElementById("userDropdown")
    const navActions = document.querySelector(".nav-actions")
  
    if (token && username) {
      // Obtener la imagen de perfil del usuario
      const url = "http://localhost/matchfilm/api/get_image.php"
      const options = {
        method: "GET",
        headers: {
          Authorization: token,
        },
      }
  
      fetch(url, options)
        .then((res) => {
          if (res.status == 200) {
            return res.json()
          } else if (res.status == 401) {
            console.log("Token caducado")
            localStorage.clear()
            showAlert("Su sesión ha expirado. Debe iniciar sesión de nuevo.")
            window.location.href = "http://localhost/matchfilm/pages/login.php"
            throw new Error("Token caducado")
          } else {
            console.log("Error en la consulta")
            localStorage.clear()
            throw new Error("Error en la consulta")
          }
        })
        .then((data) => {
          // Mostrar el nombre de usuario
          const usernameDisplay = document.getElementById("username-display")
          if (usernameDisplay) {
            usernameDisplay.textContent = username
          }
  
          // Actualizar el botón de usuario con la imagen de perfil
          if (userDropdown) {
            if (data.image) {
              userDropdown.innerHTML = `
                <img src="data:image/jpeg;base64,${data.image}" alt="Foto de perfil" class="profile-img">
                <span id="username-display">${username}</span>
              `
            } else {
              userDropdown.innerHTML = `
                <i class="fas fa-user-circle"></i>
                <span id="username-display">${username}</span>
              `
            }
          }
        })
        .catch((error) => {
          console.error("Error:", error)
        })
  
      // Marcar el enlace activo según la página actual
      const currentPage = window.location.pathname
      const navLinks = document.querySelectorAll(".nav-link")
  
      navLinks.forEach((link) => {
        const href = link.getAttribute("href")
        if (href && currentPage.includes(href.split("/").pop())) {
          link.classList.add("active")
        }
      })
  
      // Funcionalidad de cerrar sesión
      const logoutBtn = document.getElementById("logout-btn")
      if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
          // Eliminar token y username del localStorage
          localStorage.removeItem("token")
          localStorage.removeItem("username")
  
          // Redirigir a la página de login
          window.location.href = "http://localhost/matchfilm/pages/login.php"
        })
      }
    } else {
      // Si no está logueado, mostrar botones de login y registro
      if (navActions) {
        navActions.innerHTML = `
          <a href="http://localhost/matchfilm/pages/login.php" class="btn btn-login">
            Iniciar sesión
          </a>
          <a href="http://localhost/matchfilm/pages/register.php" class="btn btn-register">
            Registrarse
          </a>
        `
      }
  
      // Modificar los enlaces para usuarios no autenticados
      const navLinks = document.querySelectorAll(".nav-link")
      navLinks.forEach((link) => {
        const href = link.getAttribute("href")
        if (href && (href.includes("likes.php") || href.includes("profile.php") || href.includes("perfil.php"))) {
          link.href = "http://localhost/matchfilm/pages/login.php"
          link.setAttribute("title", "Inicia sesión para acceder")
        }
      })
    }
  
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
  
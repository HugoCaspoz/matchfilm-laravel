document.addEventListener("DOMContentLoaded", () => {
    if (!localStorage.getItem("token")) {
      const msjAlert = document.getElementById("alert")
      const username = document.getElementById("username")
      const password = document.getElementById("password")
      const togglePasswordBtn = document.getElementById("togglePassword")
      let validar = false
  
      // Navbar scroll effect
      const navbar = document.querySelector(".navbar")
      window.addEventListener("scroll", () => {
        if (window.scrollY > 50) {
          navbar.classList.add("scrolled")
        } else {
          navbar.classList.remove("scrolled")
        }
      })
  
      // Toggle password visibility
      togglePasswordBtn.addEventListener("click", () => {
        const type = password.getAttribute("type") === "password" ? "text" : "password"
        password.setAttribute("type", type)
        togglePasswordBtn.innerHTML =
          type === "password" ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>'
      })
  
      // Username validation
      username.addEventListener("blur", () => {
        if (username.value.length < 5) {
          document.getElementById("usernameError").innerHTML = "El usuario debe tener al menos 5 caracteres"
          validar = false
        } else {
          document.getElementById("usernameError").innerHTML = ""
          validar = true
        }
      })
  
      // Password validation
      password.addEventListener("blur", () => {
        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{5,}$/.test(password.value)) {
          document.getElementById("passwordError").innerHTML =
            "La contraseña debe contener al menos una letra minúscula, mayúscula y un número"
          validar = false
        } else {
          document.getElementById("passwordError").innerHTML = ""
          validar = true
        }
      })
  
      // Login button click handler
      document.getElementById("loginBtn").addEventListener("click", () => {
        // Reset error messages
        document.getElementById("usernameError").innerHTML = ""
        document.getElementById("passwordError").innerHTML = ""
  
        // Validate fields
        let isValid = true
  
        if (username.value.trim() === "") {
          document.getElementById("usernameError").innerHTML = "El usuario es obligatorio"
          isValid = false
        } else if (username.value.length < 5) {
          document.getElementById("usernameError").innerHTML = "El usuario debe tener al menos 5 caracteres"
          isValid = false
        }
  
        if (password.value.trim() === "") {
          document.getElementById("passwordError").innerHTML = "La contraseña es obligatoria"
          isValid = false
        } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{5,}$/.test(password.value)) {
          document.getElementById("passwordError").innerHTML =
            "La contraseña debe contener al menos una letra minúscula, mayúscula y un número"
          isValid = false
        }
  
        if (!isValid) {
          msjAlert.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <strong>Error en el formulario</strong> - Por favor, revisa los campos marcados.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`
          return
        }
  
        // If validation passes, proceed with login
        login()
      })
  
      // Reset button handler
      document.getElementById("cancel").addEventListener("click", () => {
        username.value = ""
        password.value = ""
        document.getElementById("usernameError").innerHTML = ""
        document.getElementById("passwordError").innerHTML = ""
      })
  
      function login() {
        // Show loading state
        document.getElementById("loginBtn").disabled = true
        document.getElementById("loginBtn").innerHTML =
          '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Iniciando sesión...'
  
        const logusername = {
          username: username.value.trim(),
          password: password.value.trim(),
        }
  
        const url = "http://localhost/matchfilm/api/post_login.php"
        const options = {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(logusername),
        }
  
        fetch(url, options)
          .then((res) => {
            if (res.status == 200) {
              return res.json()
            }
            if (res.status == 401) {
              throw new Error("Credenciales no válidas")
            } else {
              throw new Error("Error en el inicio de sesión")
            }
          })
          .then((data) => {
            localStorage.setItem("token", data.token)
            localStorage.setItem("username", data.username)
            const currentDate = new Date()
            localStorage.setItem("tokenStartTime", currentDate.getTime())
  
            msjAlert.innerHTML = `
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Inicio de sesión correcto!</strong>
                <p>Serás redirigido al inicio en unos segundos...</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>`
  
            // Redirect after 2 seconds
            setTimeout(() => {
              window.location.href = "./index.php"
            }, 2000)
          })
          .catch((error) => {
            msjAlert.innerHTML = `
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error</strong> - ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>`
          })
          .finally(() => {
            // Reset button state
            document.getElementById("loginBtn").disabled = false
            document.getElementById("loginBtn").innerHTML = "Iniciar Sesión"
          })
      }
    } else {
      document.getElementById("alert").innerHTML = `
        <div class="alert alert-info alert-dismissible fade show" role="alert">
          <strong>¡Ya tienes la sesión iniciada!</strong>
          <a href="./index.php" class="btn btn-primary ms-3">Ir a Inicio</a>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`
    }
  })
  
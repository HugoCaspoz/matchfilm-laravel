document.addEventListener("DOMContentLoaded", () => {
    if (!localStorage.getItem("token")) {
      const username = document.getElementById("username")
      const email = document.getElementById("email")
      const password = document.getElementById("password")
      const imageInput = document.getElementById("image")
      const imagePreview = document.getElementById("image-preview")
      const previewContainer = document.getElementById("preview-container")
      const removeImageBtn = document.getElementById("remove-image")
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
  
      // Image preview functionality
      imageInput.addEventListener("change", function () {
        if (this.files && this.files[0]) {
          const reader = new FileReader()
  
          reader.onload = (e) => {
            imagePreview.src = e.target.result
            previewContainer.classList.remove("d-none")
          }
  
          reader.readAsDataURL(this.files[0])
        }
      })
  
      // Remove image functionality
      removeImageBtn.addEventListener("click", () => {
        imageInput.value = ""
        previewContainer.classList.add("d-none")
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
  
      // Email validation
      email.addEventListener("blur", () => {
        const emailValue = email.value
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(emailValue)) {
          document.getElementById("emailError").innerHTML = "El email no es válido"
          validar = false
        } else {
          document.getElementById("emailError").innerHTML = ""
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
  
      // Register button click handler
      document.getElementById("registerBtn").addEventListener("click", () => {
        // Reset all error messages
        document.getElementById("usernameError").innerHTML = ""
        document.getElementById("emailError").innerHTML = ""
        document.getElementById("passwordError").innerHTML = ""
        document.getElementById("profilePictureError").innerHTML = ""
  
        // Validate all fields
        let isValid = true
  
        if (username.value.trim() === "") {
          document.getElementById("usernameError").innerHTML = "El usuario es obligatorio"
          isValid = false
        } else if (username.value.length < 5) {
          document.getElementById("usernameError").innerHTML = "El usuario debe tener al menos 5 caracteres"
          isValid = false
        }
  
        if (email.value.trim() === "") {
          document.getElementById("emailError").innerHTML = "El email es obligatorio"
          isValid = false
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
          document.getElementById("emailError").innerHTML = "El email no es válido"
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
  
        if (!imageInput.files[0]) {
          document.getElementById("profilePictureError").innerHTML = "La foto de perfil es obligatoria"
          isValid = false
        }
  
        if (!isValid) {
          document.getElementById("alert").innerHTML = `
                      <div class="alert alert-danger alert-dismissible fade show" role="alert">
                          <strong>Error en el formulario</strong> - Por favor, revisa los campos marcados.
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>`
          return
        }
  
        // If validation passes, proceed with form submission
        const nuevoUser = new FormData()
        nuevoUser.append("username", username.value.trim())
        nuevoUser.append("email", email.value.trim())
        nuevoUser.append("password", password.value.trim())
        nuevoUser.append("image", imageInput.files[0])
  
        // Show loading state
        document.getElementById("registerBtn").disabled = true
        document.getElementById("registerBtn").innerHTML =
          '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Registrando...'
  
        const url = "http://localhost/matchfilm/api/post_register.php"
        const options = {
          method: "POST",
          body: nuevoUser,
        }
  
        fetch(url, options)
          .then((res) => {
            if (res.status == 201) {
              return res.json()
            } else {
              throw new Error("Error en el registro")
            }
          })
          .then((data) => {
            document.getElementById("alert").innerHTML = `
                          <div class="alert alert-success alert-dismissible fade show" role="alert">
                              <strong>¡Te has registrado correctamente!</strong>
                              <p>Serás redirigido a la página de inicio de sesión en unos segundos...</p>
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>`
  
            // Reset form
            document.getElementById("username").value = ""
            document.getElementById("email").value = ""
            document.getElementById("password").value = ""
            document.getElementById("image").value = ""
            previewContainer.classList.add("d-none")
  
            // Redirect after 3 seconds
            setTimeout(() => {
              window.location.href = "./login.php"
            }, 3000)
          })
          .catch((e) => {
            document.getElementById("alert").innerHTML = `
                          <div class="alert alert-danger alert-dismissible fade show" role="alert">
                              <strong>Error de registro</strong> - No se pudo completar el registro. Inténtalo de nuevo.
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>`
          })
          .finally(() => {
            // Reset button state
            document.getElementById("registerBtn").disabled = false
            document.getElementById("registerBtn").innerHTML = "Crear cuenta"
          })
      })
  
      // Reset button handler
      document.getElementById("cancel").addEventListener("click", () => {
        document.getElementById("username").value = ""
        document.getElementById("email").value = ""
        document.getElementById("password").value = ""
        document.getElementById("image").value = ""
        document.getElementById("usernameError").innerHTML = ""
        document.getElementById("emailError").innerHTML = ""
        document.getElementById("passwordError").innerHTML = ""
        document.getElementById("profilePictureError").innerHTML = ""
        previewContainer.classList.add("d-none")
      })
    } else {
      document.getElementById("alert").innerHTML = `
              <div class="alert alert-info alert-dismissible fade show" role="alert">
                  <strong>¡Ya estás logueado!</strong>
                  <a href="./index.php" class="btn btn-primary ms-3">Ir al Inicio</a>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>`
    }
  })
  
document.addEventListener("DOMContentLoaded", () => {
  // Declare the bootstrap variable if it's not already defined
  const bootstrap = window.bootstrap

  // Inicializar tooltips de Bootstrap si están disponibles
  if (bootstrap && bootstrap.Tooltip) {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))
  }

  // Mostrar la primera letra del nombre de usuario en el avatar si no hay imagen
  const profileAvatar = document.querySelector(".profile-avatar-placeholder")
  if (profileAvatar) {
    const username = profileAvatar.getAttribute("data-username")
    if (username) {
      profileAvatar.textContent = username.charAt(0).toUpperCase()
    }
  }

  // Validación del formulario de edición de perfil
  const profileForm = document.getElementById("profile-edit-form")
  if (profileForm) {
    profileForm.addEventListener("submit", (event) => {
      let isValid = true

      // Validar nombre de usuario
      const usernameInput = document.getElementById("username")
      if (usernameInput && usernameInput.value.trim().length < 5) {
        showInputError(usernameInput, "El nombre de usuario debe tener al menos 5 caracteres")
        isValid = false
      } else {
        clearInputError(usernameInput)
      }

      // Validar email
      const emailInput = document.getElementById("email")
      if (emailInput && !isValidEmail(emailInput.value.trim())) {
        showInputError(emailInput, "Por favor ingresa un email válido")
        isValid = false
      } else {
        clearInputError(emailInput)
      }

      // Validar contraseña si se ha ingresado
      const passwordInput = document.getElementById("password")
      const passwordConfirmInput = document.getElementById("password_confirmation")

      if (passwordInput && passwordInput.value.trim() !== "") {
        if (passwordInput.value.trim().length < 8) {
          showInputError(passwordInput, "La contraseña debe tener al menos 8 caracteres")
          isValid = false
        } else if (passwordConfirmInput && passwordInput.value !== passwordConfirmInput.value) {
          showInputError(passwordConfirmInput, "Las contraseñas no coinciden")
          isValid = false
        } else {
          clearInputError(passwordInput)
          if (passwordConfirmInput) clearInputError(passwordConfirmInput)
        }
      }

      // Validar imagen de perfil
      const profileImageInput = document.getElementById("profile_image")
      if (profileImageInput && profileImageInput.files.length > 0) {
        const file = profileImageInput.files[0]
        const fileType = file.type
        const validImageTypes = ["image/jpeg", "image/png", "image/gif"]

        if (!validImageTypes.includes(fileType)) {
          showInputError(profileImageInput, "Por favor selecciona una imagen válida (JPEG, PNG, GIF)")
          isValid = false
        } else if (file.size > 2 * 1024 * 1024) {
          // 2MB
          showInputError(profileImageInput, "La imagen no debe superar los 2MB")
          isValid = false
        } else {
          clearInputError(profileImageInput)
        }
      }

      if (!isValid) {
        event.preventDefault()
        showFormError("Por favor corrige los errores en el formulario")
      }
    })
  }

  // Previsualización de imagen de perfil
  const profileImageInput = document.getElementById("profile_image")
  const imagePreview = document.getElementById("image-preview")

  if (profileImageInput && imagePreview) {
    profileImageInput.addEventListener("change", function () {
      if (this.files && this.files[0]) {
        const reader = new FileReader()

        reader.onload = (e) => {
          imagePreview.src = e.target.result
          imagePreview.style.display = "block"
        }

        reader.readAsDataURL(this.files[0])
      }
    })
  }

  // Confirmación para eliminar cuenta
  const deleteAccountBtn = document.getElementById("delete-account-btn")
  if (deleteAccountBtn) {
    deleteAccountBtn.addEventListener("click", () => {
      if (!confirm("¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer.")) {
        event.preventDefault()
      }
    })
  }

  // Funciones auxiliares
  function showInputError(input, message) {
    const formGroup = input.closest(".form-group")
    if (formGroup) {
      const errorElement = formGroup.querySelector(".error-message") || document.createElement("div")
      errorElement.className = "error-message text-danger mt-1"
      errorElement.textContent = message

      if (!formGroup.querySelector(".error-message")) {
        formGroup.appendChild(errorElement)
      }

      input.classList.add("is-invalid")
    }
  }

  function clearInputError(input) {
    const formGroup = input.closest(".form-group")
    if (formGroup) {
      const errorElement = formGroup.querySelector(".error-message")
      if (errorElement) {
        errorElement.remove()
      }

      input.classList.remove("is-invalid")
    }
  }

  function showFormError(message) {
    const alertContainer = document.getElementById("alert-container")
    if (alertContainer) {
      const alert = document.createElement("div")
      alert.className = "alert alert-danger alert-dismissible fade show"
      alert.innerHTML = `
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `

      alertContainer.innerHTML = ""
      alertContainer.appendChild(alert)

      // Scroll to the top to show the error
      window.scrollTo({ top: 0, behavior: "smooth" })
    }
  }

  function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return re.test(email)
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
                notificationCard.style.opacity = "0"
                setTimeout(() => {
                  notificationCard.remove()

                  // Verificar si no hay más notificaciones
                  const remainingNotifications = document.querySelectorAll(".notification-card")
                  if (remainingNotifications.length === 0) {
                    const notificationList = document.querySelector(".notification-list")
                    if (notificationList) {
                      notificationList.innerHTML = '<p class="text-center text-white-50">No tienes notificaciones</p>'
                    }
                  }
                }, 300)
              }
            }
          })
          .catch((error) => console.error("Error:", error))
      })
    })
  }
})

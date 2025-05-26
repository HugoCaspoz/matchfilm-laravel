document.addEventListener("DOMContentLoaded", () => {
  // Declare the bootstrap variable if it's not already defined
  const bootstrap = window.bootstrap

  // Inicializar tooltips de Bootstrap si están disponibles
  if (bootstrap && bootstrap.Tooltip) {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))
  }

  // Mostrar la primera letra del nombre de usuario en el avatar
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

      // Validar nombre
      const nameInput = document.getElementById("name")
      if (nameInput && nameInput.value.trim().length < 2) {
        showInputError(nameInput, "El nombre debe tener al menos 2 caracteres")
        isValid = false
      } else if (nameInput) {
        clearInputError(nameInput)
      }

      // Validar nombre de usuario
      const usernameInput = document.getElementById("username")
      if (usernameInput && usernameInput.value.trim().length < 5) {
        showInputError(usernameInput, "El nombre de usuario debe tener al menos 5 caracteres")
        isValid = false
      } else if (usernameInput) {
        clearInputError(usernameInput)
      }

      // Validar biografía
      const bioInput = document.getElementById("bio")
      if (bioInput && bioInput.value.trim().length > 500) {
        showInputError(bioInput, "La biografía no puede tener más de 500 caracteres")
        isValid = false
      } else if (bioInput) {
        clearInputError(bioInput)
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

      if (!isValid) {
        event.preventDefault()
        showFormError("Por favor corrige los errores en el formulario")
      }
    })
  }

  // Confirmación para eliminar cuenta
  const deleteAccountBtn = document.getElementById("delete-account-btn")
  if (deleteAccountBtn) {
    deleteAccountBtn.addEventListener("click", (event) => {
      if (!confirm("¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer.")) {
        event.preventDefault()
      }
    })
  }

  // Funciones auxiliares
  function showInputError(input, message) {
    const formGroup = input.closest(".form-group") || input.closest(".mb-3") || input.parentElement
    if (formGroup) {
      // Remover error anterior si existe
      const existingError = formGroup.querySelector(".error-message")
      if (existingError) {
        existingError.remove()
      }

      const errorElement = document.createElement("div")
      errorElement.className = "error-message text-danger mt-1"
      errorElement.textContent = message
      formGroup.appendChild(errorElement)

      input.classList.add("is-invalid")
    }
  }

  function clearInputError(input) {
    const formGroup = input.closest(".form-group") || input.closest(".mb-3") || input.parentElement
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

  function showFormSuccess(message) {
    const alertContainer = document.getElementById("alert-container")
    if (alertContainer) {
      const alert = document.createElement("div")
      alert.className = "alert alert-success alert-dismissible fade show"
      alert.innerHTML = `
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `

      alertContainer.innerHTML = ""
      alertContainer.appendChild(alert)

      // Scroll to the top to show the success message
      window.scrollTo({ top: 0, behavior: "smooth" })
    }
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
                    const notificationList = document.querySelector(".notification-list")
                    if (notificationList) {
                      notificationList.innerHTML = '<p class="text-center text-white-50">No tienes notificaciones</p>'
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

  // Funcionalidad para agregar amigo
  const btnAgregarAmigo = document.getElementById("btnAgregarAmigo")
  if (btnAgregarAmigo) {
    btnAgregarAmigo.addEventListener("click", () => {
      const nombreAmigo = document.getElementById("nombreAmigo").value.trim()
      const usernameError = document.getElementById("usernameError")
      
      // Limpiar errores previos
      usernameError.textContent = ""
      
      if (nombreAmigo.length < 5) {
        usernameError.textContent = "El nombre de usuario debe tener al menos 5 caracteres."
        return
      }
      
      if (nombreAmigo.length > 255) {
        usernameError.textContent = "El nombre de usuario es demasiado largo."
        return
      }
      
      agregarAmigo(nombreAmigo)
    })
  }

  // Validación en tiempo real del nombre de amigo
  const nombreAmigoInput = document.getElementById("nombreAmigo")
  if (nombreAmigoInput) {
    nombreAmigoInput.addEventListener("input", function () {
      const nombreAmigo = this.value.trim()
      const usernameError = document.getElementById("usernameError")

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

  function agregarAmigo(nombreAmigo) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")
    const btnAgregarAmigo = document.getElementById("btnAgregarAmigo")
    
    // Deshabilitar botón durante la solicitud
    if (btnAgregarAmigo) {
      btnAgregarAmigo.disabled = true
      btnAgregarAmigo.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...'
    }

    fetch("/friends/request", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
      },
      body: JSON.stringify({
        friend_id: nombreAmigo,
      }),
    })
      .then((response) => {
        if (response.ok) {
          return response.json()
        }
        return response.json().then((err) => {
          throw new Error(err.message || "Error al agregar amigo")
        })
      })
      .then((data) => {
        showFormSuccess("Solicitud de amistad enviada correctamente!")
        
        // Limpiar el formulario
        const nombreAmigoInput = document.getElementById("nombreAmigo")
        if (nombreAmigoInput) {
          nombreAmigoInput.value = ""
        }
        
        // Recargar la página después de un breve delay
        setTimeout(() => {
          window.location.reload()
        }, 2000)
      })
      .catch((error) => {
        showFormError(error.message)
      })
      .finally(() => {
        // Rehabilitar botón
        if (btnAgregarAmigo) {
          btnAgregarAmigo.disabled = false
          btnAgregarAmigo.innerHTML = '<i class="fas fa-user-plus me-2"></i>Enviar solicitud'
        }
      })
  }

  // Confirmación para eliminar amigos
  const removeFriendForms = document.querySelectorAll('form[action*="friends/remove"]')
  removeFriendForms.forEach(form => {
    form.addEventListener('submit', function(event) {
      event.preventDefault()
      
      if (confirm('¿Estás seguro de que quieres eliminar esta pareja? Esta acción no se puede deshacer.')) {
        this.submit()
      }
    })
  })
})
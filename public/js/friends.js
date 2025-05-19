document.addEventListener("DOMContentLoaded", () => {
  // Validación del nombre de amigo
  const nombreAmigoInput = document.getElementById("nombreAmigo")
  if (nombreAmigoInput) {
    nombreAmigoInput.addEventListener("blur", function () {
      const nombreAmigo = this.value.trim()
      const usernameError = document.getElementById("usernameError")

      if (nombreAmigo.length < 5) {
        usernameError.innerHTML = "El nombre de usuario debe tener al menos 5 letras."
      } else {
        usernameError.innerHTML = ""
      }
    })
  }

  // Botón para agregar amigo
  const btnAgregarAmigo = document.getElementById("btnAgregarAmigo")
  if (btnAgregarAmigo) {
    btnAgregarAmigo.addEventListener("click", () => {
      const nombreAmigo = document.getElementById("nombreAmigo").value.trim()
      if (nombreAmigo.length >= 5) {
        agregarAmigo(nombreAmigo)
      }
    })
  }
})

// Función para enviar solicitud de amistad
function agregarAmigo(nombreAmigo) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

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
        throw new Error(err.message || "Error al enviar solicitud de pareja")
      })
    })
    .then((data) => {
      document.getElementById("alert").innerHTML = `
          <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
              <strong>Solicitud de pareja enviada correctamente!</strong>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>`
      setTimeout(() => {
        window.location.reload()
      }, 2000)
    })
    .catch((error) => {
      document.getElementById("alert").innerHTML = `
          <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
              <strong>${error.message}</strong>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>`
    })
}

// Función para eliminar amigo
function eliminarAmigo(friendId) {
  if (confirm("¿Estás seguro de que quieres eliminar a esta pareja?")) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

    fetch(`/friends/remove/${friendId}`, {
      method: "DELETE",
      headers: {
        "X-CSRF-TOKEN": csrfToken,
      },
    })
      .then((response) => {
        if (response.ok) {
          document.getElementById("alert").innerHTML = `
                  <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                      <strong>Pareja eliminada correctamente!</strong>
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>`
          setTimeout(() => {
            window.location.reload()
          }, 2000)
        } else {
          throw new Error("Error al eliminar la pareja")
        }
      })
      .catch((error) => {
        document.getElementById("alert").innerHTML = `
              <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                  <strong>Error al eliminar la pareja!</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>`
      })
  }
}

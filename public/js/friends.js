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
      } else {
        const usernameError = document.getElementById("usernameError")
        if (usernameError) {
          usernameError.innerHTML = "El nombre de usuario debe tener al menos 5 letras."
        }
      }
    })
  }
})

// Función para enviar solicitud de amistad
function agregarAmigo(nombreAmigo) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

  // Mostrar indicador de carga
  document.getElementById("alert").innerHTML = `
    <div class="alert alert-info alert-dismissible fade show text-center" role="alert">
      <strong>Enviando solicitud...</strong>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`

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
      // Primero verificamos si la respuesta es ok
      if (!response.ok) {
        // Si no es ok, intentamos parsear el JSON para obtener el mensaje de error
        return response.json().then((data) => {
          throw new Error(data.message || "Error al enviar solicitud de amistad")
        })
      }
      // Si la respuesta es ok, parseamos el JSON
      return response.json()
    })
    .then((data) => {
      // Mostrar mensaje de éxito
      document.getElementById("alert").innerHTML = `
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
          <strong>Solicitud de amistad enviada correctamente!</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`

      // Limpiar el campo de entrada
      const inputField = document.getElementById("nombreAmigo")
      if (inputField) {
        inputField.value = ""
      }

      // Recargar la página después de 2 segundos
      setTimeout(() => {
        window.location.reload()
      }, 2000)
    })
    .catch((error) => {
      // Mostrar mensaje de error
      document.getElementById("alert").innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
          <strong>${error.message}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`
    })
}

// Función para eliminar amigo
function eliminarAmigo(friendId) {
  if (confirm("¿Estás seguro de que quieres eliminar a este amigo?")) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

    // Mostrar indicador de carga
    document.getElementById("alert").innerHTML = `
      <div class="alert alert-info alert-dismissible fade show text-center" role="alert">
        <strong>Eliminando amigo...</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>`

    fetch(`/friends/remove/${friendId}`, {
      method: "DELETE",
      headers: {
        "X-CSRF-TOKEN": csrfToken,
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Error al eliminar el amigo")
        }

        // Mostrar mensaje de éxito
        document.getElementById("alert").innerHTML = `
          <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
            <strong>Amigo eliminado correctamente!</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>`

        // Recargar la página después de 2 segundos
        setTimeout(() => {
          window.location.reload()
        }, 2000)
      })
      .catch((error) => {
        // Mostrar mensaje de error
        document.getElementById("alert").innerHTML = `
          <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
            <strong>Error al eliminar el amigo: ${error.message}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>`
      })
  }
}

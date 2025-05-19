// Función para agregar amigo directamente (sin solicitud)
function agregarAmigo(userId) {
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
      friend_id: userId,
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

      // Recargar la página después de 2 segundos
      setTimeout(() => {
        window.location.href = "/friends"
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

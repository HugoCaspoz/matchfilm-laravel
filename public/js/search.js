// Función para agregar amigo directamente (sin solicitud)
function agregarAmigo(userId) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

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
      if (response.ok) {
        return response.json()
      }
      return response.json().then((err) => {
        throw new Error(err.message || "Error al agregar amigo")
      })
    })
    .then((data) => {
      document.getElementById("alert").innerHTML = `
          <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
              <strong>amigo agregada correctamente!</strong>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>`
      setTimeout(() => {
        window.location.href = "/friends"
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

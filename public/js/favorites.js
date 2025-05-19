document.addEventListener("DOMContentLoaded", () => {
  // Referencias a elementos del DOM
  const resultados = document.getElementById("resultados")
  const searchInput = document.getElementById("searchInput")
  const searchButton = document.getElementById("searchButton")
  const searchActionButton = document.getElementById("searchActionButton")
  const resultadosBusqueda = document.getElementById("resultadosBusqueda")
  const addMovieButton = document.getElementById("addMovieButton")
  const searchBar = document.querySelector(".search-bar")
  const alertContainer = document.getElementById("alert-container")

  // Obtener el token CSRF para las peticiones POST
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

  // Función para mostrar alertas
  function showAlert(message, type = "success") {
    if (alertContainer) {
      alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <strong>${message}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `

      // Auto-cerrar la alerta después de 3 segundos
      setTimeout(() => {
        const alert = alertContainer.querySelector(".alert")
        if (alert) {
          alert.classList.remove("show")
          setTimeout(() => {
            alertContainer.innerHTML = ""
          }, 300)
        }
      }, 3000)
    }
  }

  // Función para determinar el color según la calificación
  function getColor(vote) {
    if (vote >= 7.5) {
      return "green"
    } else if (vote >= 5) {
      return "orange"
    } else {
      return "red"
    }
  }

  // Función para marcar/desmarcar una película como favorita
  function toggleFavorite(movieId, action) {
    fetch(`/favorites/${movieId}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
        Accept: "application/json",
      },
      body: JSON.stringify({ action: action }),
    })
      .then((response) => {
        if (response.ok) {
          return response.json()
        }
        throw new Error("Error en la respuesta del servidor")
      })
      .then((data) => {
        showAlert(data.message)

        // Si estamos en la página de favoritos, actualizar la lista
        if (window.location.pathname.includes("/favorites") && !window.location.pathname.includes("/search")) {
          // Recargar la página para mostrar los cambios
          setTimeout(() => {
            window.location.reload()
          }, 1000)
        } else {
          // Actualizar el botón de favorito en la búsqueda
          const button = document.querySelector(`button[data-movie-id="${movieId}"]`)
          if (button) {
            if (action === "like") {
              button.innerHTML = '<i class="fas fa-heart-broken"></i>'
              button.classList.remove("btn-outline-danger")
              button.classList.add("btn-danger")
              button.setAttribute("data-action", "unlike")
            } else {
              button.innerHTML = '<i class="far fa-heart"></i>'
              button.classList.remove("btn-danger")
              button.classList.add("btn-outline-danger")
              button.setAttribute("data-action", "like")
            }
          }
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showAlert("Error al procesar la solicitud", "danger")
      })
  }

  // Inicializar botones de favoritos
  function initFavoriteButtons() {
    const favoriteButtons = document.querySelectorAll(".favorite-btn")
    favoriteButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const movieId = this.getAttribute("data-movie-id")
        const action = this.getAttribute("data-action")
        toggleFavorite(movieId, action)
      })
    })
  }

  // Función para buscar películas
  function searchMovies() {
    const query = searchInput.value.trim()
    if (!query) {
      showAlert("Por favor, ingresa el nombre de una película", "warning")
      return
    }

    resultadosBusqueda.innerHTML =
      '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>'

    fetch(`/favorites/search?query=${encodeURIComponent(query)}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Error en la respuesta del servidor")
        }
        return response.text()
      })
      .then((html) => {
        // Extraer solo el contenido de resultados
        const parser = new DOMParser()
        const doc = parser.parseFromString(html, "text/html")
        const resultsContainer = doc.querySelector(".grid")

        if (resultsContainer) {
          resultadosBusqueda.innerHTML = ""
          resultadosBusqueda.appendChild(resultsContainer)

          // Inicializar botones de favoritos en los resultados
          initFavoriteButtons()
        } else {
          resultadosBusqueda.innerHTML = '<p class="text-center">No se encontraron resultados.</p>'
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        resultadosBusqueda.innerHTML = '<p class="text-center text-danger">Error al buscar películas.</p>'
      })
  }

  // Event Listeners
  if (searchButton) {
    searchButton.addEventListener("click", () => {
      searchBar.style.display = searchBar.style.display === "none" || searchBar.style.display === "" ? "block" : "none"
    })
  }

  if (searchActionButton) {
    searchActionButton.addEventListener("click", searchMovies)
  }

  if (searchInput) {
    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        searchMovies()
      }
    })
  }

  // Inicializar botones de favoritos al cargar la página
  initFavoriteButtons()

  // Manejar el despliegue de descripciones al hacer hover
  const movieCards = document.querySelectorAll(".movie-card")
  movieCards.forEach((card) => {
    const overview = card.querySelector(".movie-overview")
    if (overview) {
      card.addEventListener("mouseenter", () => {
        overview.style.transform = "translateY(0)"
      })

      card.addEventListener("mouseleave", () => {
        overview.style.transform = "translateY(101%)"
      })
    }
  })
})

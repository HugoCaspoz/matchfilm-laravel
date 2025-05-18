document.addEventListener("DOMContentLoaded", () => {
  // Datos de películas disponibles desde el controlador
  const movies = window.moviesData || []
  let currentMovieIndex = 0
  let color

  const matchModal = document.getElementById("matchModal")
  const continueBtn = document.getElementById("continueBtn")
  const viewMatchesBtn = document.getElementById("viewMatchesBtn")

  // Función para mostrar el modal de match
  function showMatchModal(movieTitle, movieImage, friendName) {
    const matchUsername = document.getElementById("matchUsername")
    const matchMovieTitle = document.getElementById("matchMovieTitle")
    const matchMovieImage = document.getElementById("matchMovieImage")

    if (matchUsername && matchMovieTitle && matchMovieImage && matchModal) {
      matchUsername.textContent = friendName
      matchMovieTitle.textContent = movieTitle
      matchMovieImage.src = movieImage
      matchModal.style.display = "flex"
    }
  }

  // Cerrar el modal y continuar explorando
  if (continueBtn) {
    continueBtn.addEventListener("click", () => {
      if (matchModal) {
        matchModal.style.display = "none"
      }
      loadNextMovie()
    })
  }

  // Ir a la página de matches
  if (viewMatchesBtn) {
    viewMatchesBtn.addEventListener("click", () => {
      window.location.href = "/matches"
    })
  }

  function createMovieCard() {
    const movieElement = document.getElementById("movie")
    if (movieElement) {
      movieElement.className = "movie-card"
      movieElement.innerHTML = `
                <div class="movie-poster">
                <img id="linkImagen" alt="Poster de la película" />
                <div class="movie-rating">
                    <span id="nota" class=""></span>
                </div>
                </div>

                <div id="movie-info" class="movie-info">
                <h3 id="titulo" class="movie-title"></h3>
                </div>

                <div id="overview" class="movie-overview">
                <h4>Descripción:</h4>
                <p id="descripcion"></p>
                </div>
            `
      return true
    }
    return false
  }

  function showLoadingState() {
    const movieElement = document.getElementById("movie")
    if (movieElement) {
      movieElement.className = "loading-container"
      movieElement.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
                </div>
                <p>Buscando películas...</p>
            `
      return true
    }
    return false
  }

  function showNoMoreMoviesState() {
    const movieElement = document.getElementById("movie")
    if (movieElement) {
      movieElement.className = "not-logged-container"
      movieElement.innerHTML = `
                <div class="not-logged-content">
                <i class="fas fa-check-circle mb-4" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h2>¡Ya has visto todas las películas!</h2>
                <p>Has valorado todas las películas disponibles. Vuelve más tarde para descubrir nuevas películas.</p>
                <a href="/movies?page=1" class="btn-login mt-3">Refrescar películas</a>
                </div>
            `

      // Ocultar los botones de acción
      const accionesElement = document.getElementById("acciones")
      if (accionesElement) {
        accionesElement.style.display = "none"
      }

      return true
    }
    return false
  }

  function loadNextMovie() {
    // Si no hay más películas, mostrar mensaje
    if (movies.length === 0 || currentMovieIndex >= movies.length - 1) {
      // Obtener el número de página actual de la URL o usar 1 como predeterminado
      const urlParams = new URLSearchParams(window.location.search)
      const currentPage = Number.parseInt(urlParams.get("page") || "1")

      if (currentPage < 5) {
        // Redirigir a la siguiente página
        window.location.href = `/movies?page=${currentPage + 1}`
      } else {
        // Mostrar mensaje de que no hay más películas
        showNoMoreMoviesState()
      }
      return
    }

    // Avanzar al siguiente índice
    currentMovieIndex++

    // Mostrar la película
    showMovie(movies[currentMovieIndex])
  }

  function showMovie(movie) {
    // Primero creamos la estructura de la tarjeta si no existe
    if (!document.getElementById("linkImagen")) {
      if (!createMovieCard()) {
        console.error("No se pudo crear la tarjeta de película")
        return
      }
    }

    const linkImagen = document.getElementById("linkImagen")
    const titulo = document.getElementById("titulo")
    const descripcion = document.getElementById("descripcion")
    const nota = document.getElementById("nota")

    if (linkImagen && titulo && descripcion && nota) {
      linkImagen.src = movie.poster_path
        ? "https://image.tmdb.org/t/p/w500" + movie.poster_path
        : "https://via.placeholder.com/500x750?text=No+Image"
      titulo.textContent = movie.title
      descripcion.textContent = movie.overview || "No hay descripción disponible."
      nota.textContent = movie.vote_average ? movie.vote_average.toFixed(1) : "N/A"

      // Asignar clase según la nota
      if (color) nota.classList.remove(color)
      color = getColor(movie.vote_average)
      nota.classList.add(color)
    } else {
      console.error("No se encontraron los elementos necesarios en el DOM")
      showErrorMessage("Error al cargar la película. Por favor, recarga la página.")
    }
  }

  function getColor(vote) {
    if (vote >= 7.5) {
      return "green"
    } else if (vote >= 5) {
      return "orange"
    } else {
      return "red"
    }
  }

  function showSuccessMessage(message) {
    const alertContainer = document.getElementById("alert")
    if (alertContainer) {
      alertContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `
      setTimeout(() => {
        const alertElement = alertContainer.querySelector(".alert")
        if (alertElement) {
          alertElement.classList.remove("show")
        }
      }, 3000)
    }
  }

  function showErrorMessage(message) {
    const alertContainer = document.getElementById("alert")
    if (alertContainer) {
      alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `
      setTimeout(() => {
        const alertElement = alertContainer.querySelector(".alert")
        if (alertElement) {
          alertElement.classList.remove("show")
        }
      }, 3000)
    }
  }

  // Mostrar la primera película si hay películas disponibles
  if (movies.length > 0) {
    showMovie(movies[currentMovieIndex])
  } else {
    // Si no hay películas disponibles, mostrar mensaje
    showNoMoreMoviesState()
  }

  // Event listeners para los botones de like y dislike
  const like = document.getElementById("like")
  const dislike = document.getElementById("dislike")

  if (like && dislike) {
    like.addEventListener("click", () => {
      // Añadir clase para animación
      like.classList.add("clicked")
      setTimeout(() => like.classList.remove("clicked"), 300)

      const currentMovie = movies[currentMovieIndex]
      if (!currentMovie) return

      const tituloElement = document.getElementById("titulo")
      const linkImagenElement = document.getElementById("linkImagen")

      const currentMovieTitle = tituloElement ? tituloElement.textContent || "" : ""
      const currentMovieImage = linkImagenElement ? linkImagenElement.src || "" : ""

      // Obtener el token CSRF
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

      // Usar la ruta correcta para el like
      fetch(`/movies/${currentMovie.id}/like`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
      })
        .then((res) => {
          if (res.ok) {
            return res.json()
          }
          showErrorMessage("No se pudo registrar tu like. Inténtalo de nuevo.")
          throw new Error("Error al dar like")
        })
        .then((data) => {
          // Verificar si hay match
          if (data && data.match) {
            // Mostrar modal de match
            showMatchModal(currentMovieTitle, currentMovieImage, data.match.user.name)
          } else {
            // Cargar siguiente película
            loadNextMovie()
          }
        })
        .catch((err) => {
          console.error(err)
          loadNextMovie()
        })
    })

    dislike.addEventListener("click", () => {
      // Añadir clase para animación
      dislike.classList.add("clicked")
      setTimeout(() => dislike.classList.remove("clicked"), 300)

      const currentMovie = movies[currentMovieIndex]
      if (!currentMovie) return

      // Obtener el token CSRF
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content")

      // Usar la ruta correcta para el dislike
      fetch(`/movies/${currentMovie.id}/dislike`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
      })
        .then((res) => {
          if (res.ok) {
            return res.json()
          }
          showErrorMessage("No se pudo registrar tu dislike. Inténtalo de nuevo.")
          throw new Error("Error al dar dislike")
        })
        .then((data) => {
          loadNextMovie()
        })
        .catch((err) => {
          console.error(err)
          loadNextMovie()
        })
    })
  }
})

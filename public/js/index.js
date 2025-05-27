document.addEventListener("DOMContentLoaded", () => {
  // Datos de películas disponibles desde el controlador
  const movies = window.moviesData || []
  let currentMovieIndex = 0
  let color

  // Debug: Verificar que tenemos datos
  console.log("🎬 Datos de películas cargados:", movies)
  console.log("📊 Total de películas:", movies.length)

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

  function showErrorState(message = "Error al cargar películas") {
    const movieElement = document.getElementById("movie")
    if (movieElement) {
      movieElement.className = "not-logged-container"
      movieElement.innerHTML = `
        <div class="not-logged-content">
          <i class="fas fa-exclamation-triangle mb-4" style="font-size: 3rem; color: #dc3545;"></i>
          <h2>Error al cargar películas</h2>
          <p>${message}</p>
          <button onclick="window.location.reload()" class="btn-login mt-3">Recargar página</button>
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
    console.log("🔄 Cargando siguiente película...")
    console.log("�� Índice actual:", currentMovieIndex)
    console.log("📊 Total películas:", movies.length)

    // Si no hay más películas en el array actual
    if (movies.length === 0) {
      console.log("❌ No hay películas en el array")
      showNoMoreMoviesState()
      return
    }

    if (currentMovieIndex >= movies.length - 1) {
      console.log("📄 Llegamos al final de la página actual")
      // Obtener el número de página actual de la URL o usar 1 como predeterminado
      const urlParams = new URLSearchParams(window.location.search)
      const currentPage = Number.parseInt(urlParams.get("page") || "1")

      if (currentPage < 5) {
        console.log("➡️ Redirigiendo a página", currentPage + 1)
        // Redirigir a la siguiente página
        window.location.href = `/movies?page=${currentPage + 1}`
      } else {
        console.log("🏁 No hay más páginas disponibles")
        // Mostrar mensaje de que no hay más películas
        showNoMoreMoviesState()
      }
      return
    }

    // Avanzar al siguiente índice
    currentMovieIndex++
    console.log("➡️ Nuevo índice:", currentMovieIndex)

    // Mostrar la película
    showMovie(movies[currentMovieIndex])
  }

  function showMovie(movie) {
    console.log("🎬 Mostrando película:", movie)

    if (!movie) {
      console.error("❌ No se recibió objeto de película")
      showErrorState("No se pudo cargar la información de la película")
      return
    }

    // Verificar que los elementos existen en el DOM
    let linkImagen = document.getElementById("linkImagen")
    let titulo = document.getElementById("titulo")
    let descripcion = document.getElementById("descripcion")
    let nota = document.getElementById("nota")

    // Si no existen, crear la estructura
    if (!linkImagen || !titulo || !descripcion || !nota) {
      console.log("🔧 Creando estructura de tarjeta de película...")
      if (!createMovieCard()) {
        console.error("❌ No se pudo crear la tarjeta de película")
        showErrorState("Error al crear la interfaz de película")
        return
      }

      // Volver a obtener las referencias después de crear la estructura
      linkImagen = document.getElementById("linkImagen")
      titulo = document.getElementById("titulo")
      descripcion = document.getElementById("descripcion")
      nota = document.getElementById("nota")
    }

    // Verificar nuevamente que todos los elementos existen
    if (linkImagen && titulo && descripcion && nota) {
      // Configurar imagen
      const posterUrl = movie.poster_path
        ? "https://image.tmdb.org/t/p/w500" + movie.poster_path
        : "https://via.placeholder.com/500x750?text=No+Image"

      linkImagen.src = posterUrl
      linkImagen.onerror = function () {
        console.log("⚠️ Error al cargar imagen, usando placeholder")
        this.src = "https://via.placeholder.com/500x750?text=Sin+Imagen"
      }

      // Configurar título
      titulo.textContent = movie.title || "Título no disponible"

      // Configurar descripción
      descripcion.textContent = movie.overview || "No hay descripción disponible."

      // Configurar nota
      const rating = movie.vote_average || 0
      nota.textContent = rating > 0 ? rating.toFixed(1) : "N/A"

      // Asignar clase según la nota
      if (color) nota.classList.remove(color)
      color = getColor(rating)
      nota.classList.add(color)

      console.log("✅ Película mostrada correctamente:", movie.title)

      // Mostrar los botones de acción si estaban ocultos
      const accionesElement = document.getElementById("acciones")
      if (accionesElement) {
        accionesElement.style.display = "flex"
      }
    } else {
      console.error("❌ No se encontraron los elementos necesarios en el DOM")
      console.error("Elementos encontrados:", {
        linkImagen: !!linkImagen,
        titulo: !!titulo,
        descripcion: !!descripcion,
        nota: !!nota,
      })
      showErrorState("Error al cargar la interfaz de película")
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

  // INICIALIZACIÓN: Mostrar la primera película
  console.log("🚀 Inicializando aplicación...")

  if (movies.length > 0) {
    console.log("✅ Hay películas disponibles, mostrando la primera")
    showMovie(movies[currentMovieIndex])
  } else {
    console.log("❌ No hay películas disponibles")
    showNoMoreMoviesState()
  }

  // Event listeners para los botones de like y dislike
  const like = document.getElementById("like")
  const dislike = document.getElementById("dislike")

  console.log("🔘 Botones encontrados:", {
    like: !!like,
    dislike: !!dislike,
  })

  if (like && dislike) {
    like.addEventListener("click", () => {
      console.log("❤️ Usuario dio like")

      // Añadir clase para animación
      like.classList.add("clicked")
      setTimeout(() => like.classList.remove("clicked"), 300)

      const currentMovie = movies[currentMovieIndex]
      if (!currentMovie) {
        console.error("❌ No hay película actual para dar like")
        return
      }

      console.log("🎬 Dando like a:", currentMovie.title)

      const tituloElement = document.getElementById("titulo")
      const linkImagenElement = document.getElementById("linkImagen")

      const currentMovieTitle = tituloElement ? tituloElement.textContent || "" : ""
      const currentMovieImage = linkImagenElement ? linkImagenElement.src || "" : ""

      // Obtener el token CSRF
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content")

      if (!csrfToken) {
        console.error("❌ No se encontró el token CSRF")
        showErrorMessage("Error de seguridad. Recarga la página.")
        return
      }

      // Usar la ruta correcta para el like
      fetch(`/movies/${currentMovie.id}/like`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
      })
        .then((res) => {
          console.log("📡 Respuesta del servidor:", res.status)
          if (res.ok) {
            return res.json()
          }
          throw new Error(`Error HTTP: ${res.status}`)
        })
        .then((data) => {
          console.log("✅ Like registrado:", data)

          // Verificar si hay match
          if (data && data.match) {
            console.log("🎉 ¡MATCH encontrado!", data.match)
            // Mostrar modal de match
            showMatchModal(currentMovieTitle, currentMovieImage, data.match.user.name)
          } else {
            console.log("➡️ No hay match, cargando siguiente película")
            // Cargar siguiente película
            loadNextMovie()
          }
        })
        .catch((err) => {
          console.error("❌ Error al dar like:", err)
          showErrorMessage("No se pudo registrar tu like. Inténtalo de nuevo.")
          // Continuar con la siguiente película aunque haya error
          setTimeout(() => loadNextMovie(), 1000)
        })
    })

    dislike.addEventListener("click", () => {
      console.log("👎 Usuario dio dislike")

      // Añadir clase para animación
      dislike.classList.add("clicked")
      setTimeout(() => dislike.classList.remove("clicked"), 300)

      const currentMovie = movies[currentMovieIndex]
      if (!currentMovie) {
        console.error("❌ No hay película actual para dar dislike")
        return
      }

      console.log("🎬 Dando dislike a:", currentMovie.title)

      // Obtener el token CSRF
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content")

      if (!csrfToken) {
        console.error("❌ No se encontró el token CSRF")
        showErrorMessage("Error de seguridad. Recarga la página.")
        return
      }

      // Usar la ruta correcta para el dislike
      fetch(`/movies/${currentMovie.id}/dislike`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
      })
        .then((res) => {
          console.log("📡 Respuesta del servidor:", res.status)
          if (res.ok) {
            return res.json()
          }
          throw new Error(`Error HTTP: ${res.status}`)
        })
        .then((data) => {
          console.log("✅ Dislike registrado:", data)
          console.log("➡️ Cargando siguiente película")
          loadNextMovie()
        })
        .catch((err) => {
          console.error("❌ Error al dar dislike:", err)
          showErrorMessage("No se pudo registrar tu dislike. Inténtalo de nuevo.")
          // Continuar con la siguiente película aunque haya error
          setTimeout(() => loadNextMovie(), 1000)
        })
    })
  } else {
    console.error("❌ No se encontraron los botones de like/dislike")
  }

  // Debug: Mostrar información del DOM al cargar
  console.log("🔍 Estado del DOM:")
  console.log("- Elemento movie:", !!document.getElementById("movie"))
  console.log("- Elemento acciones:", !!document.getElementById("acciones"))
  console.log("- Elemento alert:", !!document.getElementById("alert"))
  console.log("- Meta CSRF:", !!document.querySelector('meta[name="csrf-token"]'))
})

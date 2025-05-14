document.addEventListener("DOMContentLoaded", () => {
    if (localStorage.getItem("token")) {
      let movie_id
      let color
      let amigo
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
          matchModal.classList.add("active")
        }
      }
  
      // Cerrar el modal y continuar explorando
      if (continueBtn) {
        continueBtn.addEventListener("click", () => {
          if (matchModal) {
            matchModal.classList.remove("active")
          }
          peliculasAmigo()
        })
      }
  
      // Ir a la página de matches
      if (viewMatchesBtn) {
        viewMatchesBtn.addEventListener("click", () => {
          window.location.href = "./likes.php"
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
  
      function peliculasAmigo() {
        // Mostrar estado de carga
        if (!showLoadingState()) {
          console.error("No se pudo mostrar el estado de carga")
          return
        }
  
        const url = `http://localhost/matchfilm/api/post_amigos.php`
        const options = {
          method: "GET",
          headers: {
            Authorization: `${localStorage.getItem("token")}`,
          },
        }
  
        fetch(url, options)
          .then((res) => {
            if (res.status == 200) {
              return res.json()
            } else {
              buscar()
              return null
            }
          })
          .then((data) => {
            if (data && data.length > 0) {
              if (data[0].nombre_usuario == localStorage.getItem("username")) {
                amigo = data[0].nombre_amigo
              } else {
                amigo = data[0].nombre_usuario
              }
              const pareja = {
                usuario: localStorage.getItem("username"),
                amigo: amigo,
              }
              console.log(pareja)
              const url = "http://localhost/matchfilm/api/get_pelisPendientes.php"
              const options = {
                method: "POST",
                headers: {
                  Authorization: `${localStorage.getItem("token")}`,
                  "Content-Type": "application/json",
                },
                body: JSON.stringify(pareja),
              }
              fetch(url, options)
                .then((res) => {
                  if (res.status == 200) {
                    return res.json()
                  } else if (res.status == 401) {
                    buscar()
                    return null
                  }
                })
                .then((data) => {
                  if (data && data.length > 0) {
                    console.log(data[0].movie_id)
                    buscarId(data[0].movie_id)
                  } else {
                    buscar()
                  }
                })
                .catch((error) => {
                  console.error("Error:", error)
                  buscar()
                })
            } else {
              buscar()
            }
          })
          .catch((error) => {
            console.error("Error:", error)
            buscar()
          })
      }
  
      function buscarId(id) {
        fetch(`http://localhost/matchfilm/assets/php/buscarId.php?id=${id}`)
          .then((response) => response.json())
          .then((response) => {
            console.log(response)
  
            // Primero creamos la estructura de la tarjeta
            if (!createMovieCard()) {
              console.error("No se pudo crear la tarjeta de película")
              return
            }
  
            // Ahora que la estructura está creada, podemos acceder a los elementos
            setTimeout(() => {
              const linkImagen = document.getElementById("linkImagen")
              const titulo = document.getElementById("titulo")
              const descripcion = document.getElementById("descripcion")
              const nota = document.getElementById("nota")
  
              if (linkImagen && titulo && descripcion && nota) {
                linkImagen.src = "https://image.tmdb.org/t/p/w500" + response.poster_path
                titulo.innerHTML = response.title
                descripcion.innerHTML = response.overview
                nota.innerHTML = response.vote_average.toFixed(1)
                if (color) nota.classList.remove(color)
                color = getColor(response.vote_average)
                nota.classList.add(color)
                movie_id = response.id
              } else {
                console.error("No se encontraron los elementos necesarios en el DOM")
                showErrorMessage("Error al cargar la película. Por favor, recarga la página.")
              }
            }, 100) // Pequeño retraso para asegurar que el DOM se ha actualizado
          })
          .catch((err) => {
            console.error(err)
            showErrorMessage("No se pudo cargar la película. Inténtalo de nuevo.")
          })
      }
  
      function buscar() {
        fetch("http://localhost/matchfilm/assets/php/buscar.php")
          .then((response) => response.json())
          .then((response) => {
            // Primero creamos la estructura de la tarjeta
            if (!createMovieCard()) {
              console.error("No se pudo crear la tarjeta de película")
              return
            }
  
            const numPeli = Math.floor(Math.random() * 20)
            console.log(response.results[numPeli])
  
            // Ahora que la estructura está creada, podemos acceder a los elementos
            setTimeout(() => {
              const linkImagen = document.getElementById("linkImagen")
              const titulo = document.getElementById("titulo")
              const descripcion = document.getElementById("descripcion")
              const nota = document.getElementById("nota")
  
              if (linkImagen && titulo && descripcion && nota) {
                linkImagen.src = "https://image.tmdb.org/t/p/w500" + response.results[numPeli].poster_path
                titulo.innerHTML = response.results[numPeli].title
                descripcion.innerHTML = response.results[numPeli].overview
                nota.innerHTML = response.results[numPeli].vote_average.toFixed(1)
                if (color) nota.classList.remove(color)
                color = getColor(response.results[numPeli].vote_average)
                nota.classList.add(color)
                movie_id = response.results[numPeli].id
              } else {
                console.error("No se encontraron los elementos necesarios en el DOM")
                showErrorMessage("Error al cargar la película. Por favor, recarga la página.")
              }
            }, 100) // Pequeño retraso para asegurar que el DOM se ha actualizado
          })
          .catch((err) => {
            console.error(err)
            showErrorMessage("No se pudo cargar la película. Inténtalo de nuevo.")
          })
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
  
      // Iniciar la carga de películas
      peliculasAmigo()
  
      // Event listeners para los botones de like y dislike
      const like = document.getElementById("like")
      const dislike = document.getElementById("dislike")
  
      if (like && dislike) {
        like.addEventListener("click", () => {
          // Añadir clase para animación
          like.classList.add("clicked")
          setTimeout(() => like.classList.remove("clicked"), 300)
  
          const tituloElement = document.getElementById("titulo")
          const linkImagenElement = document.getElementById("linkImagen")
  
          const currentMovieTitle = tituloElement ? tituloElement.textContent || "" : ""
          const currentMovieImage = linkImagenElement ? linkImagenElement.src || "" : ""
  
          const likeData = {
            movie_id: movie_id,
            like: true,
          }
          const url = "http://localhost/matchfilm/api/post_like.php"
          const options = {
            method: "POST",
            headers: {
              Authorization: `${localStorage.getItem("token")}`,
              "Content-Type": "application/json",
            },
            body: JSON.stringify(likeData),
          }
          fetch(url, options)
            .then((res) => {
              if (res.status == 201) {
                return res.json()
              }
              if (res.status == 400) {
                showErrorMessage("No se pudo registrar tu like. Inténtalo de nuevo.")
                throw new Error("Error al dar like")
              }
            })
            .then((data) => {
              // Verificar si hay match
              if (data && data.match) {
                // Mostrar modal de match
                showMatchModal(currentMovieTitle, currentMovieImage, amigo)
  
                // Enviar notificación de match
                const notificationData = {
                  nombre_amigo: amigo,
                  notificacion: "match",
                }
                const notificationUrl = "http://localhost/matchfilm/api/post_notificacion.php"
                const notificationOptions = {
                  method: "POST",
                  headers: {
                    Authorization: `${localStorage.getItem("token")}`,
                    "Content-Type": "application/json",
                  },
                  body: JSON.stringify(notificationData),
                }
                fetch(notificationUrl, notificationOptions)
                  .then((res) => {
                    if (res.status == 200) {
                      return res.json()
                    }
                  })
                  .catch((err) => console.error("Error al enviar notificación:", err))
              } else {
                // Cargar siguiente película
                peliculasAmigo()
              }
            })
            .catch((err) => {
              console.error(err)
              peliculasAmigo()
            })
        })
  
        dislike.addEventListener("click", () => {
          // Añadir clase para animación
          dislike.classList.add("clicked")
          setTimeout(() => dislike.classList.remove("clicked"), 300)
  
          const dislikeData = {
            movie_id: movie_id,
            like: false,
          }
          const url = "http://localhost/matchfilm/api/post_like.php"
          const options = {
            method: "POST",
            headers: {
              Authorization: `${localStorage.getItem("token")}`,
              "Content-Type": "application/json",
            },
            body: JSON.stringify(dislikeData),
          }
          fetch(url, options)
            .then((res) => {
              if (res.status == 201) {
                return res.json()
              }
              if (res.status == 400) {
                showErrorMessage("No se pudo registrar tu dislike. Inténtalo de nuevo.")
                throw new Error("Error al dar dislike")
              }
            })
            .then((data) => {
              peliculasAmigo()
            })
            .catch((err) => {
              console.error(err)
              peliculasAmigo()
            })
        })
      }
    } else {
      // Usuario no logueado
      localStorage.removeItem("token")
      localStorage.removeItem("username")
      const movieElement = document.getElementById("movie")
      if (movieElement) {
        movieElement.className = "not-logged-container"
        movieElement.innerHTML = `
          <div class="not-logged-content">
            <i class="fas fa-lock mb-4" style="font-size: 3rem; color: var(--primary-color);"></i>
            <h2>No tienes la sesión iniciada</h2>
            <p>Inicia sesión para descubrir películas y hacer match con tus amigos.</p>
            <a href="./login.php" id="irLogin" class="btn-login">
              <i class="fas fa-sign-in-alt me-2"></i>Iniciar sesión
            </a>
          </div>
        `
      }
  
      const accionesElement = document.getElementById("acciones")
      if (accionesElement) {
        accionesElement.style.display = "none"
      }
    }
  })
  
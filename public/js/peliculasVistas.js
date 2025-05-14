if (localStorage.getItem('token')) {
    let resultados = document.getElementById("resultados");
    peliculasVistas();

    function peliculasVistas() {
        resultados.innerHTML = '';
        let url = `http://localhost/matchfilm/api/get_peliculasVistas.php`;
        let options = {
            method: 'GET',
            headers: {
                'Authorization': `${localStorage.getItem('token')}`,
            },
        };
        fetch(url, options)
            .then(res => {
                if (res.status == 200) {
                    return res.json();
                }
                if (res.status == 400) {
                    resultados.innerHTML = "<h2>No has visto ninguna película</h2>";
                }
            }).then(data => {
                console.log(data);
                if (data.length === 0) {
                    resultados.innerHTML = "<h2>No has visto ninguna película</h2>";
                }
                let movieIds = new Set();
                data.forEach(pelicula => {
                    if (!movieIds.has(pelicula.movie_id)) {
                        movieIds.add(pelicula.movie_id);
                        console.log(pelicula.movie_id);
                        fetch(`http://localhost/matchfilm/assets/php/get_movie.php?id=${pelicula.movie_id}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log(data);
                                resultados.innerHTML += `
                                <div id="movie">
                                    <img src="https://image.tmdb.org/t/p/w500${data.poster_path}" alt="${data.title}"/>
                                    <div id="movie-info">
                                        <h3>${data.title}</h3>
                                        <span class="${getColor(data.vote_average)}">${data.vote_average.toFixed(1)}</span>
                                    </div>
                                    <div id="overview">
                                    <h3>Descripción:</h3>
                                    <p>${data.overview}</p>
                                    </div>
                                </div>
                                `;
                            })
                            .catch(err => console.error(err));
                    }
                });
            })
            .catch(err => console.error(err));
    }

    function getColor(vote) {
        if (vote >= 7.5) {
            return "green";
        } else if (vote >= 5) {
            return "orange";
        } else {
            return "red";
        }
    }

    document.getElementById('searchActionButton').addEventListener('click', function() {
        var movieTitle = document.getElementById('searchInput').value;
        if(movieTitle) {
            console.log(movieTitle);
            fetch(`http://localhost/matchfilm/assets/php/buscarPeliculas.php?query=${movieTitle}`)
            .then(response => {
                console.log(response)
                return response.json()
            })
            .then(data => {
                var resultadosBusqueda = document.getElementById('resultadosBusqueda');
                resultadosBusqueda.innerHTML = '';

                if (data.results && data.results.length > 0) {
                    data.results.forEach(movie => {
                        var movieItem = document.createElement('div');
                        movieItem.className = 'result-item';
                        movieItem.innerHTML = `
                            <h5>${movie.title}</h5>
                            <input type="checkbox" class="movie-checkbox" data-movie-id="${movie.id}">
                        `;
                        resultadosBusqueda.appendChild(movieItem);
                    });
                } else {
                    resultadosBusqueda.innerHTML = '<p>No se encontraron resultados.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching movie data:', error);
            });
        } else {
            alert('Por favor, ingresa el nombre de una película.');
        }
    });

    document.getElementById('searchButton').addEventListener('click', function() {
        var searchBar = document.querySelector('.search-bar');
        searchBar.style.display = searchBar.style.display === 'none' ? 'block' : 'none';
    });

    document.getElementById('addMovieButton').addEventListener('click', function() {
        var selectedMovies = [];
        var checkboxes = document.querySelectorAll('.movie-checkbox:checked');
        checkboxes.forEach(function(checkbox) {
            selectedMovies.push({
                "movie_id": checkbox.getAttribute('data-movie-id')
            });
        });

        selectedMovies.forEach(function(movie) {
            console.log(movie);
            fetch('http://localhost/matchfilm/api/post_peliculasVistas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': localStorage.getItem('token')
                    },
                    body: JSON.stringify(movie)
                })
                .then(res => {
                    console.log(res);
                    if (res.status == 201) {
                        return res.json()
                    } else {
                        console.log("Error al agregar la película");
                        close
                    }
                })
                .then(data => {
                    console.log(data);
                    peliculasVistas();
                })
                .catch(error => {
                    console.error('Hubo un problema con tu operación de fetch:', error);
                    showAlert('Error al conectar con el servidor', 'error');
                });
        });
    });

} else {
    localStorage.removeItem('token');
    localStorage.removeItem('username');
    localStorage.clear();
    document.getElementById('alert').innerHTML = `
    <div class="container-fluid bg-light p-5 text-center">
    <h1>No has iniciado sesión</h1>
    <p>Inicia sesión para acceder al contenido.</p>
    <a href="./login.php" id="irLogin" class="btn btn-primary">Iniciar sesión</a>
    </div>`;
}

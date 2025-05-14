if (localStorage.getItem('token')) {

    let resultados = document.getElementById("resultados");
    let btnMatch = document.getElementById('matches');
    btnMatch.addEventListener('click', match);
    let btnLike = document.getElementById('like');
    btnLike.addEventListener('click', likes);

    match();

    function likes() {
        resultados.innerHTML = '';
        btnMatch.classList.remove('btnPulsado');
        btnLike.classList.add('btnPulsado');
        resultados.innerHTML = '';
        let url = `http://localhost/matchfilm/api/get_likes.php`;
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
                    resultados.innerHTML = "<h2>No tienes ningún like</h2>";
                }
            }).then(data => {
                console.log(data);
                if (data.length === 0) {
                    resultados.innerHTML = "<h2>No tienes ningún like</h2>";
                }
                data.forEach(pelicula => {
                    console.log(pelicula.movie_id);
                    fetch(`http://localhost/matchfilm/assets/php/get_movie.php?id=${pelicula.movie_id}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log(data);
                            resultados.innerHTML += `
                            <div id="movie">
                            <button class="btn" onclick="agregarVistas(${data.id})">Película ya vista</button>
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
                });
            })
            .catch(err => console.error(err));
    }

    function match() {
        resultados.innerHTML = '';
        btnMatch.classList.add('btnPulsado');
        btnLike.classList.remove('btnPulsado');
        let nombreUsuario = localStorage.getItem('username');
        let url = `http://localhost/matchfilm/api/post_amigos.php`;
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
                    document.getElementById('resultados').innerHTML='No tienes amigo, agregue a uno';
                    close;
                }
            }).then(data => {
                console.log(data);
                console.log(nombreUsuario);
                let amigo;
                if (nombreUsuario == data[0].nombre_amigo) {
                    amigo = data[0].nombre_usuario;
                } else if (nombreUsuario == data[0].nombre_usuario) {
                    amigo = data[0].nombre_amigo;
                }
                let amigos = {
                    "amigo": amigo
                };
                let url = 'http://localhost/matchfilm/api/get_match.php';
                let options = {
                    method: 'POST',
                    headers: {
                        'Authorization': `${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(amigos)
                };
                fetch(url, options)
                    .then(response => {
                        console.log(response);
                        if (response.status === 400) {
                            resultados.innerHTML = 'No tienes ningun match todavía';
                            throw new Error('No tienes ningún match todavía');
                        }
                        if (response.status == 200) {
                            return response.json();
                        }
                    })
                    .then(data => {
                        if (!data) {
                            resultados.innerHTML = "<h2>No tenéis ningún Match todavía, sigue intentandolo</h2>";
                            return;
                        }
                        console.log(data);
                        data.forEach(pelicula => {
                            fetch(`http://localhost/matchfilm/assets/php/get_movie.php?id=${pelicula.movie_id}`)
                                .then(response => response.json())
                                .then(data => {
                                    resultados.innerHTML += `
                                    <div id="movie">
                                        <button class="btn" onclick="agregarVistas(${data.id})">Película ya vista</button>
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
                        });
                    })
                    .catch((error) => console.error('Error:', error));
            })
            .catch(() => {
                console.log('Error');
            });
    }
    function agregarVistas (movie_id){
        let movie={
            "movie_id": movie_id
        }
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
            if (res.status==201){
                return res.json()
            }
            else{
                console.log("Error al agregar la película");
                close
            }
        })
        .then(data => {
            console.log(data);
            fetch('http://localhost/matchfilm/api/post_peliculasVistas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': localStorage.getItem('token')
                },
                body: JSON.stringify(movie)
            }).then(res => {
                console.log(res);
                if (res.status==201){
                    return res.json()
                }
                else{
                    console.log("Error al agregar la película");
                    close
                }
            })
            likes();
        })
        .catch(error => {
            console.error('Hubo un problema con tu operación de fetch:', error);
            showAlert('Error al conectar con el servidor', 'error');
        });
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
} else {
    localStorage.removeItem('token');
    localStorage.removeItem('username');
    document.getElementById('alert').innerHTML = `
    <div class="container-fluid bg-light p-5 text-center">
    <h1>No has iniciado sesión</h1>
    <p>Inicia sesión para acceder al contenido.</p>
    <a href="./login.php" id="irLogin" class="btn btn-primary">Iniciar sesión</a>
    </div>`;
}

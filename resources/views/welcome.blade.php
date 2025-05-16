<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchFilm - Encuentra películas que ver con tus amigos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="'css/landing.css'">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-film me-2"></i>MatchFilm
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#como-funciona">Cómo funciona</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#caracteristicas">Características</a>
                    </li>
                    @guest
                        <li class="nav-item">
                            <a class="nav-link btn-login" href="{{ route('login') }}">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-register" href="{{ route('register') }}">Registrarse</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('movies.index') }}">Películas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('matches.index') }}">Matches</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->username ?? Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}">Mi Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Cerrar Sesión</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-text">
                    <h1 class="display-4 fw-bold">Encuentra la película perfecta para ver juntos</h1>
                    <p class="lead mb-4">MatchFilm conecta tus gustos cinematográficos con los de tus amigos para descubrir qué películas ver juntos. ¡Nunca más discutas sobre qué película elegir!</p>
                    <div class="hero-buttons">
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Comenzar ahora</a>
                            <a href="#como-funciona" class="btn btn-outline-light btn-lg ms-3">Cómo funciona</a>
                        @else
                            <a href="{{ route('movies.index') }}" class="btn btn-primary btn-lg">Explorar películas</a>
                            <a href="{{ route('matches.index') }}" class="btn btn-outline-light btn-lg ms-3">Ver mis matches</a>
                        @endguest
                    </div>
                </div>
                <div class="col-lg-6 hero-image">
                    <img src="https://source.unsplash.com/random/600x500/?friends,movies" alt="Amigos viendo películas" class="img-fluid">
                </div>
            </div>
        </div>
        <div class="hero-wave">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,149.3C960,160,1056,160,1152,138.7C1248,117,1344,75,1392,53.3L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- How it Works Section -->
    <section class="how-it-works-section" id="como-funciona">
        <div class="container">
            <div class="section-header text-center">
                <h2>Cómo funciona MatchFilm</h2>
                <p class="lead">Tres simples pasos para encontrar la película perfecta</p>
            </div>
            <div class="row steps-container">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-user-plus"></i>
                            <span class="step-number">1</span>
                        </div>
                        <h3>Agrega a tu amigo</h3>
                        <p>Invita a tu pareja o amigo a unirse a MatchFilm y conéctense para comenzar a descubrir películas juntos.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-thumbs-up"></i>
                            <span class="step-number">2</span>
                        </div>
                        <h3>Selecciona tus películas</h3>
                        <p>Explora nuestro catálogo y da like a las películas que te gustaría ver. Cuantas más selecciones, mejores serán las coincidencias.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-heart"></i>
                            <span class="step-number">3</span>
                        </div>
                        <h3>¡MATCH!</h3>
                        <p>Cuando tú y tu amigo coincidan en una película, ¡es un match! Prepara las palomitas, la manta y disfruten juntos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="caracteristicas">
        <div class="container">
            <div class="section-header text-center">
                <h2>Descubre todas las posibilidades</h2>
                <p class="lead">MatchFilm te ofrece una experiencia única para compartir el cine</p>
            </div>
            <div class="row align-items-center feature-row">
                <div class="col-lg-6 feature-image">
                    <img src="https://source.unsplash.com/random/500x400/?movies,catalog" alt="Catálogo de películas" class="img-fluid rounded-3 shadow">
                </div>
                <div class="col-lg-6 feature-text">
                    <div class="feature-icon">
                        <i class="fas fa-film"></i>
                    </div>
                    <h3>Amplio catálogo de películas</h3>
                    <p>Accede a miles de películas de todos los géneros y épocas. Desde los últimos estrenos hasta los clásicos de siempre, todo está en MatchFilm.</p>
                </div>
            </div>
            <div class="row align-items-center feature-row reverse">
                <div class="col-lg-6 feature-text">
                    <div class="feature-icon">
                        <i class="fas fa-magic"></i>
                    </div>
                    <h3>Algoritmo de coincidencia inteligente</h3>
                    <p>Nuestro sistema analiza tus gustos y los de tu amigo para encontrar las películas perfectas que ambos disfrutarán. Cuanto más uses MatchFilm, mejores serán las recomendaciones.</p>
                </div>
                <div class="col-lg-6 feature-image">
                    <img src="https://source.unsplash.com/random/500x400/?algorithm,data" alt="Algoritmo de coincidencia" class="img-fluid rounded-3 shadow">
                </div>
            </div>
            <div class="row align-items-center feature-row">
                <div class="col-lg-6 feature-image">
                    <img src="https://source.unsplash.com/random/500x400/?friends,celebration" alt="Celebración de match" class="img-fluid rounded-3 shadow">
                </div>
                <div class="col-lg-6 feature-text">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Notificaciones de match instantáneas</h3>
                    <p>Recibe notificaciones al instante cuando tú y tu amigo coincidan en una película. ¡Es hora de organizar una noche de cine!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header text-center">
                <h2>Lo que dicen nuestros usuarios</h2>
                <p class="lead">Descubre cómo MatchFilm está cambiando la forma de disfrutar el cine</p>
            </div>
            <div class="row testimonials-container">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-avatar">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Usuario" class="img-fluid rounded-circle">
                        </div>
                        <div class="testimonial-content">
                            <p>"Antes mi novio y yo pasábamos horas decidiendo qué película ver. Con MatchFilm, encontramos películas que nos gustan a ambos en segundos. ¡Es genial!"</p>
                            <div class="testimonial-name">Laura G.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-avatar">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Usuario" class="img-fluid rounded-circle">
                        </div>
                        <div class="testimonial-content">
                            <p>"Gracias a MatchFilm descubrí que mi mejor amigo y yo tenemos gustos cinematográficos muy parecidos. Ahora organizamos noches de cine cada semana."</p>
                            <div class="testimonial-name">Carlos M.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-avatar">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Usuario" class="img-fluid rounded-circle">
                        </div>
                        <div class="testimonial-content">
                            <p>"La interfaz es súper intuitiva y el catálogo de películas es enorme. Me encanta recibir notificaciones cuando hay un match con mi pareja."</p>
                            <div class="testimonial-name">Ana P.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="mb-4">¿Listo para encontrar tu próxima película favorita?</h2>
            <p class="lead mb-5">Únete a MatchFilm hoy y comienza a descubrir películas que te encantarán a ti y a tus amigos.</p>
            <div class="cta-buttons">
                @guest
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Crear cuenta gratis</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg ms-3">Iniciar sesión</a>
                @else
                    <a href="{{ route('movies.index') }}" class="btn btn-primary btn-lg">Explorar películas</a>
                    <a href="{{ route('matches.index') }}" class="btn btn-outline-light btn-lg ms-3">Ver mis matches</a>
                @endguest
            </div>
        </div>
        <div class="cta-wave">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#212529" fill-opacity="1" d="M0,160L48,170.7C96,181,192,203,288,202.7C384,203,480,181,576,165.3C672,149,768,139,864,154.7C960,171,1056,213,1152,218.7C1248,224,1344,192,1392,176L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5><i class="fas fa-film me-2"></i>MatchFilm</h5>
                    <p>Encuentra la película perfecta para ver con tus amigos y pareja. Nunca más discutas sobre qué película elegir.</p>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Enlaces</h5>
                    <ul class="list-unstyled">
                        <li><a href="#como-funciona">Cómo funciona</a></li>
                        <li><a href="#caracteristicas">Características</a></li>
                        @guest
                            <li><a href="{{ route('login') }}">Iniciar sesión</a></li>
                            <li><a href="{{ route('register') }}">Registrarse</a></li>
                        @else
                            <li><a href="{{ route('movies.index') }}">Películas</a></li>
                            <li><a href="{{ route('matches.index') }}">Matches</a></li>
                        @endguest
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Términos de uso</a></li>
                        <li><a href="#">Política de privacidad</a></li>
                        <li><a href="#">Cookies</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5>Síguenos</h5>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="copyright">&copy; {{ date('Y') }} MatchFilm. Todos los derechos reservados a Hugo Casado.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="js/landing.js"></script>
</body>
</html>

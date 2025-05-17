<header class="app-header">
    <div class="container">
        <nav class="navbar">
            <a href="{{ route('movies.index') }}" class="nav-logo">
                <div class="logo-container">
                    <img src="{{ asset('img/logo.png') }}" alt="MatchFilm Logo">
                </div>
                <span class="logo-text">MatchFilm</span>
            </a>

            <div class="nav-links">
                <a href="{{ route('movies.index') }}" class="nav-link {{ request()->routeIs('movies.index') ? 'active' : '' }}" title="Descubrir películas">
                    <i class="fas fa-film"></i>
                    <span class="nav-text">Descubrir</span>
                </a>

                <a href="{{ route('movies.likes') }}" class="nav-link {{ request()->routeIs('movies.likes') ? 'active' : '' }}" title="Mis likes">
                    <i class="fas fa-heart"></i>
                    <span class="nav-text">Likes</span>
                </a>

                <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.show') ? 'active' : '' }}" title="Mi perfil">
                    <i class="fas fa-user"></i>
                    <span class="nav-text">Perfil</span>
                </a>
            </div>

            <div class="nav-actions">
                <div class="dropdown">
                    <button class="btn-user dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i>
                        <span id="username-display">{{ Auth::user()->username ?? Auth::user()->name ?? 'Usuario' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user-cog me-2"></i>Mi perfil</a></li>
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <button type="submit" class="dropdown-item" id="logout-btn">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>

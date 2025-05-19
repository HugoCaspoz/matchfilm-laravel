<header class="app-header">
    <nav class="navbar">
        <!-- Logo -->
        <a href="{{ route('movies.index') }}" class="nav-logo">
            <div class="logo-container">
                <img src="{{ asset('images/logo.png') }}" alt="MatchFilm Logo" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </div>
            <span class="logo-text">MatchFilm</span>
        </a>

        <!-- Navigation Links -->
        <div class="nav-links">
            <a href="{{ route('movies.index') }}" class="nav-link {{ request()->routeIs('movies.index') ? 'active' : '' }}">
                <i class="fas fa-film"></i>
                <span class="nav-text">Películas</span>
            </a>
            
            @auth
            <a href="{{ route('favorites.index') }}" class="nav-link {{ request()->routeIs('favorites.*') ? 'active' : '' }}">
                <i class="fas fa-heart"></i>
                <span class="nav-text">Favoritas</span>
            </a>
            
            <a href="{{ route('matches.index') }}" class="nav-link {{ request()->routeIs('matches.index') ? 'active' : '' }}">
                <i class="fas fa-star"></i>
                <span class="nav-text">Matches</span>
            </a>
            
            <a href="{{ route('friends.index') }}" class="nav-link {{ request()->routeIs('friends.index') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span class="nav-text">Amigos</span>
            </a>
            
            <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
                <i class="fas fa-bell"></i>
                <span class="nav-text">Notificaciones</span>
            </a>
            @endauth
        </div>

        <!-- User Actions -->
        <div class="nav-actions">
            @auth
                <div class="dropdown">
                    <button class="btn-user" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i>
                        <span id="username-display">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-cog me-2"></i> Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item" id="logout-btn">
                                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn-login">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}" class="btn-register">
                    Registrarse
                </a>
            @endauth
        </div>
    </nav>
</header>

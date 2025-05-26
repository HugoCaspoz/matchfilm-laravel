<header class="app-header">
    <div class="container">
        <nav class="navbar">
            <a href="{{ route('movies.index') }}" class="nav-logo">
                <div class="logo-container">
                    <img src="{{ asset('img/logo.png') }}" alt="MatchFilm Logo">
                </div>
                <span class="logo-text">MatchFilm</span>
            </a>

            <!-- Mobile menu button -->
            <button class="mobile-menu-btn" type="button" id="mobileMenuBtn" aria-label="Toggle navigation">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>

            <div class="nav-links" id="navLinks">
                <a href="{{ route('movies.index') }}" class="nav-link {{ request()->routeIs('movies.index') ? 'active' : '' }}" title="Descubrir películas">
                    <i class="fas fa-film"></i>
                    <span class="nav-text">Películas</span>
                </a>

                @auth
                <a href="{{ route('favorites.index') }}" class="nav-link {{ request()->routeIs('favorites.*') ? 'active' : '' }}" title="Mis favoritas">
                    <i class="fas fa-heart"></i>
                    <span class="nav-text">Favoritas</span>
                </a>

                <a href="{{ route('matches.index') }}" class="nav-link {{ request()->routeIs('matches.index') ? 'active' : '' }}" title="Mis matches">
                    <i class="fas fa-star"></i>
                    <span class="nav-text">Matches</span>
                </a>

                <a href="{{ route('friends.index') }}" class="nav-link {{ request()->routeIs('friends.index') ? 'active' : '' }}" title="Mis amigos">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Amigos</span>
                </a>

                <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}" title="Notificaciones">
                    <i class="fas fa-bell"></i>
                    <span class="nav-text">Notificaciones</span>
                    @if(isset($unreadNotifications) && $unreadNotifications > 0)
                        <span class="notification-badge">{{ $unreadNotifications }}</span>
                    @endif
                </a>

                <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.show') ? 'active' : '' }}" title="Mi perfil">
                    <i class="fas fa-user"></i>
                    <span class="nav-text">Perfil</span>
                </a>
                @endauth

                <!-- Mobile user actions -->
                <div class="mobile-user-actions">
                    <div class="mobile-user-info">
                        <i class="fas fa-user-circle"></i>
                        <span>{{ Auth::user()->username ?? Auth::user()->name ?? 'Usuario' }}</span>
                    </div>
                    <a href="{{ route('profile.show') }}" class="mobile-nav-link">
                        <i class="fas fa-user-cog"></i>Mi perfil
                    </a>
                    <a href="{{ route('profile.edit') }}" class="mobile-nav-link">
                        <i class="fas fa-cog"></i>Configuración
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mobile-logout-form">
                        @csrf
                        <button type="submit" class="mobile-nav-link logout-btn">
                            <i class="fas fa-sign-out-alt"></i>Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>

            <div class="nav-actions desktop-only">
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

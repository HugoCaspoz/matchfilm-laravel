<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mis Películas Favoritas') }}
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('favorites.search') }}" class="btn btn-advanced-search">
                    <i class="fas fa-search-plus me-2"></i> Búsqueda Avanzada
                </a>
            </div>
        </div>
    </x-slot>

    <div class="favorites-container">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Contenedor de alertas -->
            <div id="alert-container" class="alert-section">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>{{ session('success') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>{{ session('error') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>

            <!-- Sección de búsqueda - SIEMPRE VISIBLE -->
            <div class="search-section">
                <div class="search-card">
                    <div class="search-header">
                        <h5 class="search-title">
                            <i class="fas fa-search me-2"></i>
                            Buscar películas para añadir a favoritos
                        </h5>
                        <span class="search-subtitle">Encuentra nuevas películas y márcalas como favoritas</span>
                    </div>
                    
                    <div class="search-input-container">
                        <div class="search-input-group">
                            <input type="text" 
                                   class="form-control search-input" 
                                   id="searchInput" 
                                   placeholder="Escribe el nombre de una película...">
                            <button class="btn btn-search" id="searchActionButton">
                                <i class="fas fa-search"></i>
                                <span class="btn-text">Buscar</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Resultados de búsqueda -->
                    <div id="resultadosBusqueda" class="search-results"></div>
                </div>
            </div>

            <!-- Sección de películas favoritas -->
            <div class="favorites-section">
                <div class="favorites-card">
                    @if(count($movies) === 0)
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h3 class="empty-title">Aún no tienes películas favoritas</h3>
                            <p class="empty-description">
                                Usa el buscador de arriba para encontrar películas increíbles y marcarlas como favoritas. 
                                ¡Comienza a crear tu colección personal de cine!
                            </p>
                            <div class="empty-actions">
                                <button class="btn btn-primary" onclick="document.getElementById('searchInput').focus()">
                                    <i class="fas fa-search me-2"></i>
                                    Buscar películas
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="favorites-header">
                            <h5 class="favorites-title">
                                <i class="fas fa-heart me-2"></i>
                                Tus películas favoritas
                            </h5>
                            <span class="favorites-count">{{ count($movies) }} película{{ count($movies) !== 1 ? 's' : '' }}</span>
                        </div>
                        
                        <div id="resultados" class="movies-grid">
                            @foreach($movies as $movie)
                                <div class="movie-card">
                                    <div class="movie-poster">
                                        @if(isset($movie['poster_path']) && $movie['poster_path'])
                                            <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" 
                                                 alt="{{ $movie['title'] }}"
                                                 loading="lazy">
                                        @else
                                            <div class="movie-placeholder">
                                                <i class="fas fa-film"></i>
                                                <span>Sin imagen</span>
                                            </div>
                                        @endif
                                        
                                        <!-- Botón de favorito -->
                                        <button class="favorite-btn btn-remove" 
                                                data-movie-id="{{ $movie['id'] }}" 
                                                data-action="unlike"
                                                title="Quitar de favoritos">
                                            <i class="fas fa-heart-broken"></i>
                                        </button>
                                        
                                        <!-- Rating -->
                                        @if(isset($movie['vote_average']) && $movie['vote_average'] > 0)
                                            <div class="movie-rating">
                                                @php
                                                    $rating = $movie['vote_average'];
                                                    $colorClass = $rating >= 7.5 ? 'high' : ($rating >= 5 ? 'medium' : 'low');
                                                @endphp
                                                <span class="rating-badge rating-{{ $colorClass }}">
                                                    <i class="fas fa-star"></i>
                                                    {{ number_format($rating, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                        
                                        <!-- Información básica -->
                                        <div class="movie-info">
                                            <h3 class="movie-title">{{ $movie['title'] }}</h3>
                                            @if(isset($movie['release_date']))
                                                <span class="movie-year">{{ substr($movie['release_date'], 0, 4) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Descripción al hacer hover -->
                                    @if(isset($movie['overview']) && $movie['overview'])
                                        <div class="movie-overview">
                                            <h4>Descripción:</h4>
                                            <p>{{ $movie['overview'] }}</p>
                                            @if(isset($movie['liked_at']))
                                                <div class="favorite-date">
                                                    <i class="fas fa-heart me-1"></i>
                                                    Añadida el {{ $movie['liked_at']->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/favorites.css') }}">
    @endpush

    @push('scripts')
    <script src="{{ asset('js/favorites.js') }}"></script>
    @endpush
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mis Películas Favoritas') }}
            </h2>
            <div class="d-flex">
                <button id="searchButton" class="btn me-2" style="background-color: #586294; color: white;">
                    <i class="fas fa-search me-2"></i> Buscar
                </button>
                <a href="{{ route('favorites.search') }}" class="btn" style="background-color: #ab9079; color: white;">
                    <i class="fas fa-search-plus me-2"></i> Búsqueda Avanzada
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="alert-container">
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

            <!-- Barra de búsqueda desplegable -->
            <div class="search-container">
                <div class="search-bar">
                    <div class="search-input-group">
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar películas...">
                        <button class="btn btn-primary" id="searchActionButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="resultadosBusqueda" class="bg-white p-3 rounded"></div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <ul class="nav nav-tabs mb-4" id="movieTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="liked-tab" data-bs-toggle="tab" data-bs-target="#liked" type="button" role="tab" aria-controls="liked" aria-selected="true">
                                <i class="fas fa-heart me-2"></i> Me gustan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="disliked-tab" data-bs-toggle="tab" data-bs-target="#disliked" type="button" role="tab" aria-controls="disliked" aria-selected="false">
                                <i class="fas fa-heart-broken me-2"></i> No me gustan
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="movieTabsContent">
                        <div class="tab-pane fade show active" id="liked" role="tabpanel" aria-labelledby="liked-tab">
                            @if(count($likedMovies) === 0)
                                <div class="text-center py-8 no-results">
                                    <i class="fas fa-heart text-4xl text-gray-400 mb-4"></i>
                                    <h3 class="text-xl font-semibold mb-2">Aún no tienes películas favoritas</h3>
                                    <p class="text-gray-500 mb-4">Explora películas y marca las que te gusten para verlas aquí.</p>
                                    <a href="{{ route('favorites.search') }}" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Buscar Películas
                                    </a>
                                </div>
                            @else
                                <div id="resultados" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                    @foreach($likedMovies as $movie)
                                        <div class="movie-card">
                                            <div class="movie-poster">
                                                @if(isset($movie['poster_path']) && $movie['poster_path'])
                                                    <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                        <i class="fas fa-film text-4xl text-gray-400"></i>
                                                    </div>
                                                @endif
                                                <div class="movie-actions">
                                                    <button 
                                                        class="favorite-btn btn-danger favorite-btn" 
                                                        data-movie-id="{{ $movie['id'] }}" 
                                                        data-action="unlike"
                                                    >
                                                        <i class="fas fa-heart-broken"></i>
                                                    </button>
                                                    <button 
                                                        class="favorite-btn btn-secondary" 
                                                        data-movie-id="{{ $movie['id'] }}" 
                                                        data-action="remove"
                                                        title="Quitar valoración"
                                                    >
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="movie-info">
                                                    <h3>{{ $movie['title'] }}</h3>
                                                    @if(isset($movie['vote_average']))
                                                        @php
                                                            $colorClass = "red";
                                                            if ($movie['vote_average'] >= 7.5) {
                                                                $colorClass = "green";
                                                            } elseif ($movie['vote_average'] >= 5) {
                                                                $colorClass = "orange";
                                                            }
                                                        @endphp
                                                        <span class="{{ $colorClass }}">{{ number_format($movie['vote_average'], 1) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="movie-overview">
                                                <h3>Descripción:</h3>
                                                <p>{{ $movie['overview'] ?? 'No hay descripción disponible.' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <div class="tab-pane fade" id="disliked" role="tabpanel" aria-labelledby="disliked-tab">
                            @if(count($dislikedMovies) === 0)
                                <div class="text-center py-8 no-results">
                                    <i class="fas fa-heart-broken text-4xl text-gray-400 mb-4"></i>
                                    <h3 class="text-xl font-semibold mb-2">No has marcado películas que no te gustan</h3>
                                    <p class="text-gray-500 mb-4">Marca las películas que no te gustan para evitar recomendaciones similares.</p>
                                    <a href="{{ route('favorites.search') }}" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Buscar Películas
                                    </a>
                                </div>
                            @else
                                <div id="resultados-disliked" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                    @foreach($dislikedMovies as $movie)
                                        <div class="movie-card">
                                            <div class="movie-poster">
                                                @if(isset($movie['poster_path']) && $movie['poster_path'])
                                                    <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                        <i class="fas fa-film text-4xl text-gray-400"></i>
                                                    </div>
                                                @endif
                                                <div class="movie-actions">
                                                    <button 
                                                        class="favorite-btn btn-danger" 
                                                        data-movie-id="{{ $movie['id'] }}" 
                                                        data-action="like"
                                                        title="Me gusta"
                                                    >
                                                        <i class="fas fa-heart"></i>
                                                    </button>
                                                    <button 
                                                        class="favorite-btn btn-secondary" 
                                                        data-movie-id="{{ $movie['id'] }}" 
                                                        data-action="remove"
                                                        title="Quitar valoración"
                                                    >
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="movie-info">
                                                    <h3>{{ $movie['title'] }}</h3>
                                                    @if(isset($movie['vote_average']))
                                                        @php
                                                            $colorClass = "red";
                                                            if ($movie['vote_average'] >= 7.5) {
                                                                $colorClass = "green";
                                                            } elseif ($movie['vote_average'] >= 5) {
                                                                $colorClass = "orange";
                                                            }
                                                        @endphp
                                                        <span class="{{ $colorClass }}">{{ number_format($movie['vote_average'], 1) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="movie-overview">
                                                <h3>Descripción:</h3>
                                                <p>{{ $movie['overview'] ?? 'No hay descripción disponible.' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
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

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Películas') }}
        </h2>
    </x-slot>

    <div class="main-container">
        <div id="alert" class="alert-container"></div>

        <main id="main">
            <div class="card-container">
                @if(count($movies) > 0)
                    <div id="movie" class="movie-card">
                        <div class="movie-poster">
                            @if(isset($movies[0]['poster_path']) && $movies[0]['poster_path'])
                                <img id="linkImagen" src="https://image.tmdb.org/t/p/w500{{ $movies[0]['poster_path'] }}" alt="Poster de la película" />
                            @else
                                <img id="linkImagen" src="https://via.placeholder.com/500x750?text=No+Image" alt="Poster de la película" />
                            @endif
                            <div class="movie-rating">
                                @if(isset($movies[0]['vote_average']))
                                    @php
                                        $rating = $movies[0]['vote_average'];
                                        $ratingClass = $rating >= 7.5 ? 'green' : ($rating >= 5 ? 'orange' : 'red');
                                    @endphp
                                    <span id="nota" class="{{ $ratingClass }}">{{ number_format($rating, 1) }}</span>
                                @else
                                    <span id="nota" class="">N/A</span>
                                @endif
                            </div>
                        </div>

                        <div id="movie-info" class="movie-info">
                            <h3 id="titulo" class="movie-title">{{ $movies[0]['title'] ?? 'Cargando...' }}</h3>
                        </div>

                        <div id="overview" class="movie-overview">
                            <h4>Descripción:</h4>
                            <p id="descripcion">{{ $movies[0]['overview'] ?? 'No hay descripción disponible.' }}</p>
                        </div>
                    </div>

                    <div id="acciones" class="action-buttons">
                        <button id="dislike" class="action-btn dislike">
                            <i class="fas fa-times"></i>
                        </button>
                        <button id="like" class="action-btn like">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                @else
                    <div class="not-logged-container">
                        <div class="not-logged-content">
                            <i class="fas fa-film mb-4" style="font-size: 3rem; color: var(--primary-color);"></i>
                            <h2>No hay películas disponibles</h2>
                            <p>No se pudieron cargar películas en este momento. Inténtalo de nuevo más tarde.</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="instructions">
                <div class="instruction-item">
                    <div class="instruction-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <p>Desliza hacia arriba para ver la descripción completa</p>
                </div>
                <div class="instruction-item">
                    <div class="instruction-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <p>Da like a las películas que quieras ver con tu amigo</p>
                </div>
                <div class="instruction-item">
                    <div class="instruction-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <p>Recibirás una notificación cuando haya un match</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Match Modal -->
    <div class="match-modal" id="matchModal" style="display: none;">
        <div class="match-content">
            <div class="match-header">
                <h2><i class="fas fa-heart"></i> ¡MATCH!</h2>
                <p>Tú y <span id="matchUsername"></span> queréis ver esta película</p>
            </div>
            <div class="match-movie">
                <img id="matchMovieImage" alt="Poster de la película" />
                <h3 id="matchMovieTitle"></h3>
            </div>
            <div class="match-actions">
                <button id="continueBtn" class="btn-continue">Seguir explorando</button>
                <a href="{{ route('matches.index') }}" class="btn-view-matches">Ver mis matches</a>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    @endpush

    @push('scripts')
    <script>
        // Pasar los datos de películas al JavaScript
        window.moviesData = @json($movies);
    </script>
    <script src="{{ asset('js/index.js') }}"></script>
    @endpush
</x-app-layout>

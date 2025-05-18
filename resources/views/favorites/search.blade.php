<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Buscar Películas') }}
            </h2>
            <a href="{{ route('favorites.index') }}" class="btn" style="background-color: #ab9079; color: white;">
                <i class="fas fa-heart me-2"></i> Mis Favoritas
            </a>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('favorites.search') }}" method="GET" class="mb-0">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-grow">
                                <input type="text" name="query" value="{{ $query }}" class="form-control w-full" placeholder="Buscar películas por título..." required>
                            </div>
                            <div>
                                <button type="submit" class="btn w-full md:w-auto" style="background-color: #ab9079; color: white;">
                                    <i class="fas fa-search me-2"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(!$query)
                        <div class="text-center py-8 no-results">
                            <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">Busca tus películas favoritas</h3>
                            <p class="text-gray-500">Escribe el título de una película en el buscador para encontrarla y marcarla como favorita.</p>
                        </div>
                    @elseif(count($results) === 0)
                        <div class="text-center py-8 no-results">
                            <i class="fas fa-film text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">No se encontraron resultados</h3>
                            <p class="text-gray-500">No se encontraron películas para "{{ $query }}". Intenta con otro término de búsqueda.</p>
                        </div>
                    @else
                        <h3 class="text-xl font-semibold mb-4">Resultados para "{{ $query }}"</h3>
                        <div id="resultados" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            @foreach($results as $movie)
                                <div class="movie-card">
                                    <div class="relative">
                                        @if(isset($movie['poster_path']) && $movie['poster_path'])
                                            <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}">
                                        @else
                                            <div class="w-full h-64 flex items-center justify-center bg-gray-200">
                                                <i class="fas fa-film text-4xl text-gray-400"></i>
                                            </div>
                                        @endif
                                        <button
                                            class="favorite-btn {{ isset($movie['user_liked']) && $movie['user_liked'] ? 'btn-danger' : 'btn-outline-danger' }} favorite-btn"
                                            data-movie-id="{{ $movie['id'] }}"
                                            data-action="{{ isset($movie['user_liked']) && $movie['user_liked'] ? 'unlike' : 'like' }}"
                                        >
                                            <i class="{{ isset($movie['user_liked']) && $movie['user_liked'] ? 'fas' : 'far' }} fa-heart"></i>
                                        </button>
                                    </div>
                                    <div class="movie-info">
                                        <h3>{{ $movie['title'] }}</h3>
                                        @if(isset($movie['vote_average']))
                                            <span class="{{ getColor($movie['vote_average']) }}">{{ number_format($movie['vote_average'], 1) }}</span>
                                        @endif
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

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/favorites.css') }}">
    @endpush

    @push('scripts')
    <script src="{{ asset('js/favorites.js') }}"></script>
    <script>
        // Función para determinar el color según la calificación
        function getColor(vote) {
            if (vote >= 7.5) {
                return "green";
            } else if (vote >= 5) {
                return "orange";
            } else {
                return "red";
            }
        }
    </script>
    @endpush
</x-app-layout>

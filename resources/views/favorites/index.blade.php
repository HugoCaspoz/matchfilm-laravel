<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mis Películas Favoritas') }}
            </h2>
            <a href="{{ route('favorites.search') }}" class="btn" style="background-color: #ab9079; color: white;">
                <i class="fas fa-search me-2"></i> Buscar Películas
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(count($movies) === 0)
                        <div class="text-center py-8">
                            <i class="fas fa-heart text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">Aún no tienes películas favoritas</h3>
                            <p class="text-gray-500 mb-4">Explora películas y marca las que te gusten para verlas aquí.</p>
                            <a href="{{ route('favorites.search') }}" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Buscar Películas
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            @foreach($movies as $movie)
                                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300 movie-card">
                                    <div class="relative h-64">
                                        @if(isset($movie['poster_path']) && $movie['poster_path'])
                                            <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                <i class="fas fa-film text-4xl text-gray-400"></i>
                                            </div>
                                        @endif
                                        <div class="absolute top-0 right-0 m-2">
                                            <form action="{{ route('favorites.toggle', $movie['id']) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="unlike">
                                                <button type="submit" class="bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition-colors duration-300">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @if(isset($movie['vote_average']))
                                            <div class="absolute bottom-0 left-0 bg-gray-900 bg-opacity-75 text-white px-2 py-1 m-2 rounded">
                                                <i class="fas fa-star text-yellow-400 mr-1"></i>
                                                {{ number_format($movie['vote_average'], 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <h3 class="font-bold text-lg mb-2 truncate">{{ $movie['title'] }}</h3>
                                        <p class="text-gray-600 text-sm mb-2">
                                            @if(isset($movie['release_date']))
                                                {{ \Carbon\Carbon::parse($movie['release_date'])->format('Y') }}
                                            @else
                                                Año desconocido
                                            @endif
                                        </p>
                                        <p class="text-gray-500 text-xs">
                                            <i class="fas fa-clock mr-1"></i> Añadida {{ \Carbon\Carbon::parse($movie['liked_at'])->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="px-4 pb-4">
                                        <a href="{{ route('movies.show', $movie['id']) }}" class="text-blue-500 hover:text-blue-700 text-sm">
                                            Ver detalles <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
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
    <style>
        .movie-card {
            transition: transform 0.3s ease;
        }
        .movie-card:hover {
            transform: translateY(-5px);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Código JavaScript si es necesario
        });
    </script>
    @endpush
</x-app-layout>

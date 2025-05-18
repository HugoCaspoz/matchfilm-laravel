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
                        <div class="text-center py-8">
                            <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">Busca tus películas favoritas</h3>
                            <p class="text-gray-500">Escribe el título de una película en el buscador para encontrarla y marcarla como favorita.</p>
                        </div>
                    @elseif(count($results) === 0)
                        <div class="text-center py-8">
                            <i class="fas fa-film text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">No se encontraron resultados</h3>
                            <p class="text-gray-500">No se encontraron películas para "{{ $query }}". Intenta con otro término de búsqueda.</p>
                        </div>
                    @else
                        <h3 class="text-xl font-semibold mb-4">Resultados para "{{ $query }}"</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            @foreach($results as $movie)
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
                                                @if(isset($movie['user_liked']) && $movie['user_liked'])
                                                    <input type="hidden" name="action" value="unlike">
                                                    <button type="submit" class="bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition-colors duration-300">
                                                        <i class="fas fa-heart"></i>
                                                    </button>
                                                @else
                                                    <input type="hidden" name="action" value="like">
                                                    <button type="submit" class="bg-gray-200 text-gray-600 rounded-full p-2 hover:bg-red-500 hover:text-white transition-colors duration-300">
                                                        <i class="far fa-heart"></i>
                                                    </button>
                                                @endif
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
                                        <p class="text-gray-600 text-sm">
                                            @if(isset($movie['release_date']))
                                                {{ \Carbon\Carbon::parse($movie['release_date'])->format('Y') }}
                                            @else
                                                Año desconocido
                                            @endif
                                        </p>
                                        @if(isset($movie['overview']))
                                            <p class="text-gray-500 text-sm mt-2 line-clamp-3">
                                                {{ \Illuminate\Support\Str::limit($movie['overview'], 100) }}
                                            </p>
                                        @endif
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
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Código JavaScript para manejar favoritos con AJAX si es necesario
            const favoriteButtons = document.querySelectorAll('.favorite-toggle');

            favoriteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    const url = form.getAttribute('action');
                    const formData = new FormData(form);

                    fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar el botón según la acción
                            const action = form.querySelector('input[name="action"]').value;
                            if (action === 'like') {
                                this.innerHTML = '<i class="fas fa-heart"></i>';
                                this.classList.remove('bg-gray-200', 'text-gray-600');
                                this.classList.add('bg-red-500', 'text-white');
                                form.querySelector('input[name="action"]').value = 'unlike';
                            } else {
                                this.innerHTML = '<i class="far fa-heart"></i>';
                                this.classList.remove('bg-red-500', 'text-white');
                                this.classList.add('bg-gray-200', 'text-gray-600');
                                form.querySelector('input[name="action"]').value = 'like';
                            }

                            // Mostrar mensaje de éxito
                            const alertContainer = document.getElementById('alert-container');
                            alertContainer.innerHTML = `
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>${data.message}</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>

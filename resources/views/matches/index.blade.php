<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Matches') }}
        </h2>
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
                    @if($friends->isEmpty())
                        <div class="text-center py-8 no-friends">
                            <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">Aún no tienes amigos</h3>
                            <p class="text-gray-500 mb-4">Para ver matches, primero debes agregar amigos.</p>
                            <a href="{{ route('friends.index') }}" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Agregar Amigos
                            </a>
                        </div>
                    @else
                        <div class="friend-selector">
                            <h3 class="text-lg font-semibold mb-3">Selecciona un amigo para ver tus matches</h3>
                            <div class="friend-list">
                                @foreach($friends as $friend)
                                    <a href="{{ route('matches.index', ['friend_id' => $friend->id]) }}" 
                                       class="friend-item {{ $selectedFriend && $selectedFriend->id == $friend->id ? 'active' : '' }}">
                                        <div class="friend-avatar">
                                            {{ substr($friend->name, 0, 1) }}
                                        </div>
                                        <div class="friend-name">
                                            {{ $friend->name }}
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if(!$friends->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        @if($selectedFriend)
                            <h3 class="text-xl font-semibold mb-4">
                                Matches con {{ $selectedFriend->name }}
                            </h3>
                            
                            @if(count($matches) === 0)
                                <div class="text-center py-8 no-matches">
                                    <i class="fas fa-film text-4xl text-gray-400 mb-4"></i>
                                    <h3 class="text-xl font-semibold mb-2">Aún no tienen matches</h3>
                                    <p class="text-gray-500 mb-4">Cuando tú y {{ $selectedFriend->name }} den like a las mismas películas, aparecerán aquí.</p>
                                    <a href="{{ route('movies.index') }}" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Explorar Películas
                                    </a>
                                </div>
                            @else
                                <div id="matches-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                    @foreach($matches as $movie)
                                        <div class="match-card">
                                            <div class="match-poster">
                                                @if(isset($movie['poster_path']) && $movie['poster_path'])
                                                    <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                        <i class="fas fa-film text-4xl text-gray-400"></i>
                                                    </div>
                                                @endif
                                                <div class="match-badge">
                                                    <i class="fas fa-heart"></i> Match
                                                </div>
                                                <div class="match-info">
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
                                            <div class="match-overview">
                                                <h3>Descripción:</h3>
                                                <p>{{ $movie['overview'] ?? 'No hay descripción disponible.' }}</p>
                                                <div class="match-actions">
                                                    <button class="btn-watch" data-movie-id="{{ $movie['id'] }}" data-movie-title="{{ $movie['title'] }}">
                                                        <i class="fas fa-play-circle"></i> Ver juntos
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para "Ver juntos" -->
    <div class="modal fade" id="watchModal" tabindex="-1" aria-labelledby="watchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="watchModalLabel">Ver película juntos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Quieres enviar una invitación a <span id="friendName"></span> para ver esta película juntos?</p>
                    <div class="form-group mt-3">
                        <label for="watchDate">Fecha propuesta:</label>
                        <input type="date" class="form-control" id="watchDate" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group mt-3">
                        <label for="watchMessage">Mensaje (opcional):</label>
                        <textarea class="form-control" id="watchMessage" rows="3" placeholder="Ej: Podríamos verla en mi casa"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="sendInviteBtn">Enviar invitación</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/matches.css') }}">
    @endpush

    @push('scripts')
    <script src="{{ asset('js/matches.js') }}"></script>
    @endpush
</x-app-layout>

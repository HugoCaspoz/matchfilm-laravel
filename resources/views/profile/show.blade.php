<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Perfil') }}
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

            <div class="profile-container">
                <div class="profile-section">
                    <div class="card">
                        <div class="card-body">
                            <div class="profile-header">
                                <div class="profile-avatar profile-avatar-placeholder" data-username="{{ $user->name }}">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                
                                <div class="profile-info">
                                    <h1>{{ $user->name }}</h1>
                                    <p>{{ '@' . $user->username }}</p>
                                    <p class="text-muted">{{ $user->email }}</p>
                                    <a href="{{ route('profile.edit') }}" class="btn btn-primary mt-3">
                                        <i class="fas fa-edit me-2"></i>Editar Perfil
                                    </a>
                                </div>
                            </div>
                            
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <h3>{{ $stats['liked_movies'] }}</h3>
                                    <p>Películas que te gustan</p>
                                </div>
                                <div class="stat-item">
                                    <h3>{{ $stats['total_matches'] }}</h3>
                                    <p>Matches totales</p>
                                </div>
                                <div class="stat-item">
                                    <h3>{{ $stats['friends_count'] }}</h3>
                                    <p>{{ $stats['friends_count'] == 1 ? 'Amigo' : 'Amigos' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="profile-section">
                    <div class="partner-section">
                        <h2>Mi Pareja</h2>
                        <div class="card partner-card">
                            <div id="amigo">
                                @if($pendingRequests->isNotEmpty())
                                    <div class="mb-4">
                                        <h5 class="card-title">Solicitudes pendientes</h5>
                                        @foreach($pendingRequests as $request)
                                            <div class="card mb-2" style="background-color: rgba(255, 255, 255, 0.1);">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle">{{ $request->username ?? $request->name }}</h6>
                                                    <div class="d-flex mt-2">
                                                        <form action="{{ route('friends.accept', $request->id) }}" method="POST" class="me-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success">Aceptar</button>
                                                        </form>
                                                        <form action="{{ route('friends.reject', $request->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger">Rechazar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                @if($acceptedFriends->isEmpty())
                                    <h5 class="card-title">No tienes pareja</h5>
                                    <div class="add-friend-form">
                                        <input type="text" id="nombreAmigo" class="form-control mb-2" placeholder="Nombre de usuario" maxlength="255">
                                        <p id="usernameError" class="text-danger"></p>
                                        <button type="button" id="btnAgregarAmigo" class="btn btn-primary">
                                            <i class="fas fa-user-plus me-2"></i>Enviar solicitud
                                        </button>
                                    </div>
                                @else
                                    <h5 class="card-title">Tus parejas</h5>
                                    @foreach($acceptedFriends as $friend)
                                        <div class="friend-item mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="friend-info">
                                                    <h6 class="mb-1"><b>{{ $friend->username ?? $friend->name }}</b></h6>
                                                    <small class="text-muted">{{ $friend->email }}</small>
                                                </div>
                                                <form action="{{ route('friends.remove', $friend->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('¿Estás seguro de que quieres eliminar esta pareja?')">
                                                        <i class="fas fa-user-times me-1"></i>Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    <!-- Formulario para agregar más amigos -->
                                    <div class="add-friend-form mt-4">
                                        <h6>Agregar nueva pareja</h6>
                                        <input type="text" id="nombreAmigo" class="form-control mb-2" placeholder="Nombre de usuario" maxlength="255">
                                        <p id="usernameError" class="text-danger"></p>
                                        <button type="button" id="btnAgregarAmigo" class="btn btn-primary btn-sm">
                                            <i class="fas fa-user-plus me-2"></i>Enviar solicitud
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="notifications-section">
                <h2>Notificaciones</h2>
                <div class="notification-list">
                    @if($notifications->isEmpty())
                        <p class="text-center text-white-50">No tienes notificaciones</p>
                    @else
                        @foreach($notifications as $notification)
                            <div class="card notification-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <b>
                                            @if($notification->type == 'match')
                                                <i class="fas fa-heart me-2 text-danger"></i>
                                            @elseif($notification->type == 'friend_request')
                                                <i class="fas fa-user-plus me-2 text-primary"></i>
                                            @elseif($notification->type == 'friend_accepted')
                                                <i class="fas fa-user-check me-2 text-success"></i>
                                            @else
                                                <i class="fas fa-bell me-2 text-info"></i>
                                            @endif
                                            {{ $notification->message }}
                                        </b>
                                    </h5>
                                    <p class="card-text">
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </p>
                                    <button type="button" class="btn btn-primary btn-sm mark-as-read" data-notification-id="{{ $notification->id }}">
                                        <i class="fas fa-check me-1"></i>Marcar como leída
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    @endpush

    @push('scripts')
    <script src="{{ asset('js/profile.js') }}"></script>
    @endpush
</x-app-layout>
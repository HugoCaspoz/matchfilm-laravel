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
                                    <h3>{{ $user->movieLikes()->where('liked', true)->count() }}</h3>
                                    <p>Películas que te gustan</p>
                                </div>
                                <div class="stat-item">
                                    <h3>{{ $user->initiatedMatches()->count() + $user->receivedMatches()->count() }}</h3>
                                    <p>Matches totales</p>
                                </div>
                                <div class="stat-item">
                                    <h3>{{ $user->friends()->count() }}</h3>
                                    <p>Amigos</p>
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
                                @php
                                    // Verificar si hay solicitudes pendientes
                                    $pendingRequests = \App\Models\Friend::where('friend_id', Auth::id())
                                                        ->where('status', 'pending')
                                                        ->with('user')
                                                        ->get();
                                    
                                    // Obtener amigos aceptados
                                    $friends = \App\Models\Friend::where(function($query) use ($user) {
                                        $query->where('user_id', $user->id)
                                            ->orWhere('friend_id', $user->id);
                                    })
                                    ->where('status', 'accepted')
                                    ->get()
                                    ->map(function($friendship) use ($user) {
                                        $friendId = $friendship->user_id == $user->id ? $friendship->friend_id : $friendship->user_id;
                                        return \App\Models\User::find($friendId);
                                    });
                                @endphp
                                
                                @if($pendingRequests->isNotEmpty())
                                    <div class="mb-4">
                                        <h5 class="card-title">Solicitudes pendientes</h5>
                                        @foreach($pendingRequests as $request)
                                            <div class="card mb-2" style="background-color: rgba(255, 255, 255, 0.1);">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle">{{ $request->user->username ?? $request->user->name }}</h6>
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
                                
                                @if($friends->isEmpty())
                                    <h5 class="card-title">No tienes pareja</h5>
                                    <input type="text" id="nombreAmigo" class="form-control" placeholder="Nombre de usuario">
                                    <p id="usernameError"></p>
                                    <button type="button" id="btnAgregarAmigo" class="btn btn-primary">Enviar solicitud</button>
                                @else
                                    @foreach($friends as $friend)
                                        <h5 class="card-title"><b>{{ $friend->username ?? $friend->name }}</b></h5>
                                        <form action="{{ route('friends.remove', $friend->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Eliminar Pareja</button>
                                        </form>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="notifications-section">
                <h2>Notificaciones</h2>
                <div class="notification-list">
                    @php
                        $notifications = $user->notifications()->where('read', false)->with('fromUser')->orderBy('created_at', 'desc')->get();
                    @endphp
                    
                    @if($notifications->isEmpty())
                        <p class="text-center text-white-50">No tienes notificaciones</p>
                    @else
                        @foreach($notifications as $notification)
                            <div class="card notification-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <b>
                                            @if($notification->type == 'match')
                                                <i class="fas fa-heart me-2"></i>
                                            @elseif($notification->type == 'friend_request')
                                                <i class="fas fa-user-plus me-2"></i>
                                            @elseif($notification->type == 'friend_accepted')
                                                <i class="fas fa-user-check me-2"></i>
                                            @else
                                                <i class="fas fa-bell me-2"></i>
                                            @endif
                                            {{ $notification->message }}
                                        </b>
                                    </h5>
                                    <p class="card-text">
                                        <small>{{ $notification->created_at->diffForHumans() }}</small>
                                    </p>
                                    <button type="button" class="btn btn-primary btn-sm mark-as-read" data-notification-id="{{ $notification->id }}">
                                        Marcar como leída
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

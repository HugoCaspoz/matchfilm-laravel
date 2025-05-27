<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mi Pareja') }}
            </h2>
            <a href="{{ route('friends.search') }}" class="btn" style="background-color: #e50914; color: white;">
                <i class="fas fa-user-plus me-2"></i> Buscar Pareja
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <div id="alert-container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <div class="row">
            <!-- Sección de solicitudes pendientes -->
            @php
                $pendingRequests = \App\Models\Friend::where('friend_id', Auth::id())
                                    ->where('status', 'pending')
                                    ->with('user')
                                    ->get();
            @endphp

            @if($pendingRequests->isNotEmpty())
                <div class="col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Solicitudes Pendientes</h3>
                            <div class="pending-requests">
                                @foreach($pendingRequests as $request)
                                    <div class="card mb-3" style="background-color: rgba(255, 255, 255, 0.1);">
                                        <div class="card-body">
                                            <h5 class="card-title"><b>{{ $request->user->username ?? $request->user->name }}</b></h5>
                                            <p class="card-text">Te ha enviado una solicitud para ser pareja</p>
                                            <div class="d-flex mt-3">
                                                <form action="{{ route('friends.accept', $request->id) }}" method="POST" class="me-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Aceptar</button>
                                                </form>
                                                <form action="{{ route('friends.reject', $request->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger">Rechazar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Mis Amigos</h3>
                        <div id="amigos">
                            @php
                                $friends = \App\Models\Friend::where(function($query) {
                                    $query->where('user_id', Auth::id())
                                        ->orWhere('friend_id', Auth::id());
                                })
                                ->where('status', 'accepted')
                                ->get()
                                ->map(function($friendship) {
                                    $friendId = $friendship->user_id == Auth::id() ? $friendship->friend_id : $friendship->user_id;
                                    return \App\Models\User::find($friendId);
                                });
                            @endphp

                            <div class="mb-4">
                                <div class="card" style="background-color: rgba(255, 255, 255, 0.1);">
                                    <div class="card-body">
                                        <h5 class="card-title">Agregar nuevo amigo</h5>
                                        <div class="add-friend-form">
                                            <input type="text" id="nombreAmigo" class="form-control mb-2" placeholder="Nombre de usuario" maxlength="255">
                                            <p id="usernameError" class="text-danger"></p>
                                            <button type="button" id="btnAgregarAmigo" class="btn" style="background-color: #e50914; color: white;">
                                                <i class="fas fa-user-plus me-2"></i>Enviar solicitud de amistad
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($friends->isEmpty())
                                <div class="text-center py-3">
                                    <p class="text-white-50">No tienes amigos todavía. Envía solicitudes para conectar con otros usuarios.</p>
                                </div>
                            @else
                                <h5 class="mb-3">Amigos actuales</h5>
                                @foreach($friends as $friend)
                                    <div class="card mb-2" style="background-color: rgba(255, 255, 255, 0.1);">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                            <div class="friend-info">
                                                <h5 class="card-title mb-0"><b>{{ $friend->username ?? $friend->name }}</b></h5>
                                                <small class="text-muted">{{ $friend->email }}</small>
                                            </div>
                                            <button type="button" onclick="eliminarAmigo('{{ $friend->id }}')" class="btn btn-sm btn-danger">
                                                <i class="fas fa-user-times"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Notificaciones</h3>
                        <div id="notificaciones">
                            @if(!isset($notifications) || $notifications->isEmpty())
                                <p class="text-white-50 text-center">No tienes notificaciones</p>
                            @else
                                @foreach($notifications as $notification)
                                    <div class="card mb-3 notification-card" style="background-color: rgba(255, 255, 255, 0.1);">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <b>
                                                    @if($notification->type == 'friend_request')
                                                        <i class="fas fa-user-plus me-2 text-primary"></i>
                                                    @elseif($notification->type == 'friend_accepted')
                                                        <i class="fas fa-user-check me-2 text-success"></i>
                                                    @else
                                                        <i class="fas fa-bell me-2 text-info"></i>
                                                    @endif
                                                    {{ $notification->fromUser->username ?? $notification->fromUser->name ?? 'Usuario' }}
                                                </b>
                                            </h5>
                                            <p class="card-text">{{ $notification->message }}</p>
                                            <p class="card-text">
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </p>

                                            @if($notification->type == 'friend_request')
                                                @php
                                                    $data = json_decode($notification->data, true);
                                                    $friendshipId = $data['friendship_id'] ?? null;
                                                @endphp
                                                @if($friendshipId)
                                                    <div class="d-flex mt-3">
                                                        <form action="{{ route('friends.accept', $friendshipId) }}" method="POST" class="me-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm">Aceptar</button>
                                                        </form>
                                                        <form action="{{ route('friends.reject', $friendshipId) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-sm">Rechazar</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @else
                                                <button type="button" class="btn btn-sm mark-as-read" style="background-color: #e50914; color: white;" data-notification-id="{{ $notification->id }}">
                                                    <i class="fas fa-check me-1"></i>Marcar como leída
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @endpush

    @push('scripts')
    <script src="{{ asset('js/friends.js') }}"></script>
    @endpush
</x-app-layout>

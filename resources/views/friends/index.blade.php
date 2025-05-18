<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mi Pareja') }}
            </h2>
            <a href="{{ route('friends.search') }}" class="btn" style="background-color: #ab9079; color: white;">
                <i class="fas fa-user-plus me-2"></i> Buscar Pareja
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <div id="alert">
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
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Mi Pareja</h3>
                        <div id="amigo">
                            @if($friends->isEmpty())
                                <div class="card" style="background-color: rgba(255, 255, 255, 0.1);">
                                    <div class="card-body">
                                        <h5 class="card-title">No tienes pareja</h5>
                                        <input type="text" id="nombreAmigo" class="form-control" placeholder="Nombre de usuario"><br>
                                        <p id="usernameError" class="text-danger"></p>
                                        <button type="button" id="btnAgregarAmigo" class="btn" style="background-color: #ab9079; color: white;">Agrega a tu pareja</button>
                                    </div>
                                </div>
                            @else
                                @foreach($friends as $friend)
                                    <div class="card" style="background-color: rgba(255, 255, 255, 0.1);">
                                        <div class="card-body">
                                            <h5 class="card-title"><b>{{ $friend->username ?? $friend->name }}</b></h5>
                                            <button type="button" onclick="eliminarAmigo('{{ $friend->id }}')" class="btn btn-danger">Eliminar Pareja</button>
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
                                    <div class="card mb-3" style="background-color: rgba(255, 255, 255, 0.1);">
                                        <div class="card-body">
                                            <h5 class="card-title"><b>{{ $notification->fromUser->username ?? $notification->fromUser->name ?? 'Usuario' }}</b></h5>
                                            <p class="card-text">{{ $notification->message }}</p>
                                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm" style="background-color: #ab9079; color: white;">
                                                    Marcar como leída
                                                </button>
                                            </form>
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
</x-app-layout>

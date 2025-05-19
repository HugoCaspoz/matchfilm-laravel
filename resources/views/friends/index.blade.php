<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mis Amigos') }}
            </h2>
            <a href="{{ route('friends.search') }}" class="btn" style="background-color: #ab9079; color: white;">
                <i class="fas fa-user-plus me-2"></i> Buscar amigo
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
                        <h3 class="card-title mb-4">Mis Amigos</h3>
                        <div id="amigo">
                            @if($friends->isEmpty())
                                <div class="card" style="background-color: rgba(255, 255, 255, 0.1);">
                                    <div class="card-body">
                                        <h5 class="card-title">No tienes amigo</h5>
                                        <input type="text" id="nombreAmigo" class="form-control" placeholder="Nombre de usuario"><br>
                                        <p id="usernameError" class="text-danger"></p>
                                        <button type="button" id="btnAgregarAmigo" class="btn" style="background-color: #ab9079; color: white;">Agrega a tu amigo</button>
                                    </div>
                                </div>
                            @else
                                @foreach($friends as $friend)
                                    <div class="card" style="background-color: rgba(255, 255, 255, 0.1);">
                                        <div class="card-body">
                                            <h5 class="card-title"><b>{{ $friend->username ?? $friend->name }}</b></h5>
                                            <button type="button" onclick="eliminarAmigo('{{ $friend->id }}')" class="btn btn-danger">Eliminar Amigo</button>
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

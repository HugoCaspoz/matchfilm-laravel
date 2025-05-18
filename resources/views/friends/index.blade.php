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
                            @if($notifications->isEmpty())
                                <p class="text-white-50 text-center">No tienes notificaciones</p>
                            @else
                                @foreach($notifications as $notification)
                                    <div class="card mb-3" style="background-color: rgba(255, 255, 255, 0.1);">
                                        <div class="card-body">
                                            <h5 class="card-title"><b>{{ $notification->fromUser->username ?? $notification->fromUser->name }}</b></h5>
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

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/friends.css') }}">
    <style>
        body {
            background-color: #ab9079 !important;
            font-family: 'Poppins', sans-serif;
        }
        .card {
            background-color: #586294;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .card-title {
            color: #fff;
        }
        .btn:hover {
            background-color: #586294 !important;
            color: #ab9079 !important;
            border: 2px solid #ab9079 !important;
            border-radius: 5px;
            transition: 0.5s;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset('js/friends.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Validación del nombre de amigo
            const nombreAmigoInput = document.getElementById('nombreAmigo');
            if (nombreAmigoInput) {
                nombreAmigoInput.addEventListener('blur', function() {
                    let nombreAmigo = this.value.trim();
                    let usernameError = document.getElementById('usernameError');

                    if (nombreAmigo.length < 5) {
                        usernameError.innerHTML = 'El nombre de usuario debe tener al menos 5 letras.';
                    } else {
                        usernameError.innerHTML = '';
                    }
                });
            }

            // Botón para agregar amigo
            const btnAgregarAmigo = document.getElementById('btnAgregarAmigo');
            if (btnAgregarAmigo) {
                btnAgregarAmigo.addEventListener('click', function() {
                    const nombreAmigo = document.getElementById('nombreAmigo').value.trim();
                    if (nombreAmigo.length >= 5) {
                        agregarAmigo(nombreAmigo);
                    }
                });
            }
        });

        // Función para agregar amigo directamente (sin solicitud)
        function agregarAmigo(nombreAmigo) {
            fetch('{{ route("friends.request") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    friend_id: nombreAmigo
                })
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                return response.json().then(err => {
                    throw new Error(err.message || 'Error al agregar pareja');
                });
            })
            .then(data => {
                document.getElementById('alert').innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        <strong>Pareja agregada correctamente!</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            })
            .catch(error => {
                document.getElementById('alert').innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                        <strong>${error.message}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
            });
        }

        // Función para eliminar amigo
        function eliminarAmigo(friendId) {
            if (confirm('¿Estás seguro de que quieres eliminar a esta pareja?')) {
                fetch(`{{ url('friends/remove') }}/${friendId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (response.ok) {
                        document.getElementById('alert').innerHTML = `
                            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                <strong>Pareja eliminada correctamente!</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>`;
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error('Error al eliminar la pareja');
                    }
                })
                .catch(error => {
                    document.getElementById('alert').innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                            <strong>Error al eliminar la pareja!</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                });
            }
        }
    </script>
    @endpush
</x-app-layout>

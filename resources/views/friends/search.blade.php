<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Buscar Pareja') }}
            </h2>
            <a href="{{ route('friends.index') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i> Volver
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

        <div class="card">
            <div class="card-body">
                <form action="{{ route('friends.search') }}" method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="query" value="{{ $query }}" class="form-control" placeholder="Buscar por nombre de usuario" aria-label="Buscar amigos" aria-describedby="button-search">
                        <button class="btn" type="submit" id="button-search" style="background-color: #ab9079; color: white;">
                            <i class="fas fa-search me-2"></i> Buscar
                        </button>
                    </div>
                </form>

                @if($query)
                    <h3 class="card-title mb-4">Resultados para "{{ $query }}"</h3>

                    @if($results->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x mb-3" style="color: #ab9079;"></i>
                            <h4 class="text-white">No se encontraron usuarios</h4>
                            <p class="text-white-50">Intenta con otro nombre o invita a tus amigos a unirse.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($results as $user)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card" style="background-color: rgba(255, 255, 255, 0.1);">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px; background-color: #ab9079 !important;">
                                                        {{ substr($user->username ?? $user->name, 0, 1) }}
                                                    </div>
                                                    <div class="ms-3">
                                                        <h5 class="mb-0 text-white">{{ $user->username ?? $user->name }}</h5>
                                                        <small class="text-white-50">{{ $user->email }}</small>
                                                    </div>
                                                </div>
                                                @if(isset($user->is_friend) && $user->is_friend)
                                                    <span class="badge bg-success">Ya es tu pareja</span>
                                                @else
                                                    <button type="button" onclick="agregarAmigo('{{ $user->id }}')" class="btn" style="background-color: #ab9079; color: white;">
                                                        <i class="fas fa-user-plus me-1"></i> Agregar
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x mb-3" style="color: #ab9079;"></i>
                        <h4 class="text-white">Busca usuarios para hacer match</h4>
                        <p class="text-white-50 mb-4">Encuentra a otros usuarios por su nombre de usuario y agrégalos para empezar a hacer match con películas.</p>
                    </div>
                @endif
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
        // Función para agregar amigo directamente (sin solicitud)
        function agregarAmigo(userId) {
            fetch('{{ route("friends.request") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    friend_id: userId
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
                    window.location.href = '{{ route("friends.index") }}';
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
    </script>
    @endpush
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Perfil') }}
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

            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Información Personal</h3>

                    <form id="profile-edit-form" method="post" action="{{ route('profile.update') }}" class="edit-form">
                        @csrf
                        @method('put')

                        <div class="form-group mb-3">
                            <label for="name">Nombre</label>
                            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="username">Nombre de usuario</label>
                            <input id="username" name="username" type="text" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="email">Correo electrónico</label>
                            <input id="email" type="email" class="form-control" value="{{ $user->email }}" disabled readonly>
                            <small class="form-text text-muted">El correo electrónico no se puede modificar</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">Nueva contraseña</label>
                            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror">
                            <small class="form-text text-muted">Dejar en blanco para mantener la contraseña actual</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password_confirmation">Confirmar contraseña</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control">
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('profile.show') }}" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body danger-zone">
                    <h3>Zona de Peligro</h3>
                    <p>Una vez que elimines tu cuenta, todos tus datos serán eliminados permanentemente.</p>

                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        Eliminar mi cuenta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para eliminar cuenta -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAccountModalLabel">Confirmar eliminación de cuenta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Esta acción no se puede deshacer. Por favor, ingresa tu contraseña para confirmar.</p>

                    <form id="delete-account-form" method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')

                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <input id="delete-password" name="password" type="password" class="form-control" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="delete-account-form" class="btn btn-danger" id="delete-account-btn">Eliminar mi cuenta</button>
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

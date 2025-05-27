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
                            <label for="username">Nombre de usuario <span class="text-muted">(mínimo 5 caracteres)</span></label>
                            <input id="username" 
                                   name="username" 
                                   type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   value="{{ old('username', $user->username) }}" 
                                   required
                                   minlength="5"
                                   maxlength="50">
                            <div id="username-feedback" class="invalid-feedback"></div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('username');
            const usernameFeedback = document.getElementById('username-feedback');
            const form = document.getElementById('profile-edit-form');

            // Función para validar username
            function validateUsername() {
                const username = usernameInput.value.trim();

                if (username.length === 0) {
                    usernameInput.classList.remove('is-valid', 'is-invalid');
                    usernameFeedback.textContent = '';
                    return false;
                } else if (username.length < 5) {
                    usernameInput.classList.remove('is-valid');
                    usernameInput.classList.add('is-invalid');
                    usernameFeedback.textContent = 'El username debe tener al menos 5 caracteres';
                    usernameFeedback.style.display = 'block';
                    return false;
                } else {
                    usernameInput.classList.remove('is-invalid');
                    usernameInput.classList.add('is-valid');
                    usernameFeedback.textContent = '';
                    usernameFeedback.style.display = 'none';
                    return true;
                }
            }

            // Validar en tiempo real
            usernameInput.addEventListener('input', function() {
                // Filtrar caracteres no válidos
                const validPattern = /^[a-zA-Z0-9_]*$/;
                const currentValue = this.value;
                
                if (!validPattern.test(currentValue)) {
                    this.value = currentValue.replace(/[^a-zA-Z0-9_]/g, '');
                }
                
                validateUsername();
            });

            // Validar al perder el foco
            usernameInput.addEventListener('blur', function() {
                validateUsername();
            });

            // Validar antes de enviar
            form.addEventListener('submit', function(e) {
                const isUsernameValid = validateUsername();
                
                if (!isUsernameValid) {
                    e.preventDefault();
                    usernameInput.focus();
                }
            });
        });
    </script>

    <style>
        .form-control.is-valid {
            border-color: #198754;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 1.38 1.38 3.22-3.22.94.94-4.16 4.16z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 1.4 1.4 1.4-1.4M8.6 6l-1.4 1.4 1.4 1.4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .text-muted {
            font-size: 0.875em;
        }

        .danger-zone {
            border-left: 4px solid #dc3545;
            background-color: #f8f9fa;
        }
    </style>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    @endpush

    @push('scripts')
    <script src="{{ asset('js/profile.js') }}"></script>
    @endpush
</x-app-layout>

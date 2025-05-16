<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Assets -->
        @if(config('assets.use_vite', false))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <link href="{{ config('assets.css.bootstrap') }}" rel="stylesheet">
            <link href="{{ config('assets.css.fontawesome') }}" rel="stylesheet">
            <link href="{{ config('assets.css.custom') }}" rel="stylesheet">
        @endif
        
        <style>
            /* Estilos básicos para formularios */
            .form-control {
                display: block;
                width: 100%;
                padding: 0.375rem 0.75rem;
                font-size: 1rem;
                line-height: 1.5;
                color: #212529;
                background-color: #fff;
                background-clip: padding-box;
                border: 1px solid #ced4da;
                border-radius: 0.25rem;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
                margin-bottom: 1rem;
            }
            
            .form-label {
                margin-bottom: 0.5rem;
                font-weight: 500;
                display: block;
            }
            
            .btn-primary {
                color: #fff;
                background-color: #e50914;
                border-color: #e50914;
                padding: 0.5rem 1rem;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: 0.25rem;
                cursor: pointer;
            }
            
            .btn-primary:hover {
                background-color: #cc0812;
                border-color: #cc0812;
            }
            
            .invalid-feedback {
                display: block;
                width: 100%;
                margin-top: 0.25rem;
                font-size: 0.875em;
                color: #dc3545;
            }
            
            .is-invalid {
                border-color: #dc3545;
            }
        </style>
    </head>
    <body class="bg-light">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <i class="fas fa-film me-2"></i>MatchFilm
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/login">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register">Registrarse</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <i class="fas fa-film fa-3x text-danger"></i>
                                <h3 class="mt-2">MatchFilm</h3>
                            </div>
                            
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container text-center">
                <p>&copy; {{ date('Y') }} MatchFilm. Todos los derechos reservados a Hugo Casado.</p>
            </div>
        </footer>
        
        <!-- JavaScript -->
        @if(!config('assets.use_vite', false))
            <script src="{{ config('assets.js.bootstrap') }}"></script>
            <script src="{{ config('assets.js.custom') }}"></script>
        @endif
    </body>
</html>
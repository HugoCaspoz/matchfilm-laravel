@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="hero-section text-center py-5 mb-5 bg-dark text-white rounded">
            <h1 class="display-4">Encuentra tu película perfecta</h1>
            <p class="lead">Conecta con personas que comparten tus gustos cinematográficos</p>
            @guest
                <div class="mt-4">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg me-2">Regístrate</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">Iniciar Sesión</a>
                </div>
            @else
                <div class="mt-4">
                    <a href="{{ route('movies.index') }}" class="btn btn-primary btn-lg">Explorar Películas</a>
                </div>
            @endguest
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-film fa-3x mb-3 text-primary"></i>
                <h3 class="card-title">Descubre Películas</h3>
                <p class="card-text">Explora miles de películas y encuentra nuevos títulos que se ajusten a tus gustos.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                <h3 class="card-title">Conecta con Otros</h3>
                <p class="card-text">Encuentra personas con gustos cinematográficos similares y comparte tus opiniones.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-heart fa-3x mb-3 text-primary"></i>
                <h3 class="card-title">Haz Match</h3>
                <p class="card-text">Crea matches con otros usuarios basados en películas que ambos disfrutan.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">Películas Populares</h2>
    </div>
</div>

<div class="row">
    @foreach($popularMovies['results'] ?? [] as $movie)
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                @if($movie['poster_path'])
                    <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" class="card-img-top" alt="{{ $movie['title'] }}">
                @else
                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 300px;">
                        <span class="text-white">Sin imagen</span>
                    </div>
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $movie['title'] }}</h5>
                    <p class="card-text small">{{ \Illuminate\Support\Str::limit($movie['overview'] ?? '', 100) }}</p>
                </div>
                <div class="card-footer bg-white">
                    <a href="{{ route('movies.show', $movie['id']) }}" class="btn btn-primary w-100">Ver detalles</a>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="text-center mt-4">
    <a href="{{ route('movies.index') }}" class="btn btn-outline-primary">Ver más películas</a>
</div>
@endsection
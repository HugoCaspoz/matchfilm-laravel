@extends('layouts.app')

@section('title', 'Mis Matches')

@section('content')
<div class="container">
    <h1 class="mb-4">Mis Matches</h1>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Matches Pendientes</h5>
                </div>
                <div class="card-body">
                    @if($pendingMatches->count() > 0)
                        <div class="list-group">
                            @foreach($pendingMatches as $match)
                                <div class="list-group-item list-group-item-action match-card">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">
                                                @if($match->user_id_1 == Auth::id())
                                                    Match con {{ $match->userTwo->username }}
                                                @else
                                                    {{ $match->userOne->username }} quiere hacer match contigo
                                                @endif
                                            </h5>
                                            <p class="mb-1">Película: {{ $match->movie->title ?? 'Película no disponible' }}</p>
                                        </div>
                                        <div>
                                            @if($match->user_id_2 == Auth::id())
                                                <form method="POST" action="{{ route('matches.accept', $match->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">Aceptar</button>
                                                </form>
                                                <form method="POST" action="{{ route('matches.reject', $match->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">Rechazar</button>
                                                </form>
                                            @else
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No tienes matches pendientes.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Matches Aceptados</h5>
                </div>
                <div class="card-body">
                    @if($acceptedMatches->count() > 0)
                        <div class="list-group">
                            @foreach($acceptedMatches as $match)
                                <div class="list-group-item list-group-item-action match-card">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">
                                                Match con 
                                                @if($match->user_id_1 == Auth::id())
                                                    {{ $match->userTwo->username }}
                                                @else
                                                    {{ $match->userOne->username }}
                                                @endif
                                            </h5>
                                            <p class="mb-1">Película: {{ $match->movie->title ?? 'Película no disponible' }}</p>
                                            <small class="text-muted">Fecha: {{ $match->matched_at->format('d/m/Y') }}</small>
                                        </div>
                                        <div>
                                            <a href="{{ route('messages.show', $match->user_id_1 == Auth::id() ? $match->user_id_2 : $match->user_id_1) }}" class="btn btn-primary btn-sm">
                                                Enviar mensaje
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No tienes matches aceptados.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Posibles Matches</h5>
                </div>
                <div class="card-body">
                    @if($potentialMatches->count() > 0)
                        <div class="row">
                            @foreach($potentialMatches as $potential)
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $potential['username'] }}</h5>
                                            <p class="card-text">{{ $potential['common_count'] }} películas en común</p>
                                            
                                            <div class="d-flex mb-3">
                                                @foreach($potential['common_movies'] as $movie)
                                                    <div class="me-2">
                                                        @if($movie->poster_path)
                                                            <img src="https://image.tmdb.org/t/p/w92{{ $movie->poster_path }}" 
                                                                alt="{{ $movie->title }}" 
                                                                class="img-thumbnail" 
                                                                style="width: 60px;"
                                                                data-bs-toggle="tooltip" 
                                                                title="{{ $movie->title }}">
                                                        @else
                                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                                                style="width: 60px; height: 90px;"
                                                                data-bs-toggle="tooltip" 
                                                                title="{{ $movie->title }}">
                                                                <small>Sin imagen</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            
                                            <a href="{{ route('profile.show', ['user' => $potential['user_id']]) }}" class="btn btn-outline-primary btn-sm">Ver perfil</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No hay posibles matches disponibles. ¡Valora más películas para encontrar coincidencias!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
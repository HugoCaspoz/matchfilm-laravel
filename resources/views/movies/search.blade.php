@extends('layouts.app')

@section('title', 'Búsqueda de películas')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Resultados de búsqueda: "{{ $query }}"</h1>
        
        <form action="{{ route('movies.search') }}" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="query" class="form-control" placeholder="Buscar películas..." value="{{ $query }}">
                <button class="btn btn-primary" type="submit">Buscar</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    @if(count($results['results'] ?? []) > 0)
        @foreach($results['results'] as $movie)
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    @if(isset($movie['poster_path']) && $movie['poster_path'])
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
    @else
        <div class="col-12">
            <div class="alert alert-info">
                No se encontraron resultados para "{{ $query }}". Intenta con otra búsqueda.
            </div>
        </div>
    @endif
</div>

@if(isset($results['total_pages']) && $results['total_pages'] > 1)
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Paginación de resultados">
            <ul class="pagination">
                @for($i = 1; $i <= min(5, $results['total_pages']); $i++)
                    <li class="page-item {{ $i == ($results['page'] ?? 1) ? 'active' : '' }}">
                        <a class="page-link" href="{{ route('movies.search', ['query' => $query, 'page' => $i]) }}">{{ $i }}</a>
                    </li>
                @endfor
            </ul>
        </nav>
    </div>
@endif
@endsection
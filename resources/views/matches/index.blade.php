<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Películas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Películas que te pueden gustar</h3>
                        <p class="text-gray-600">Basado en tus preferencias y valoraciones</p>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @forelse($matches as $match)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                <a href="{{ route('movies.show', $match->movie->tmdb_id) }}">
                                    @if($match->movie->poster_url)
                                        <img src="{{ $match->movie->poster_url }}" alt="{{ $match->movie->title }}" class="w-full h-64 object-cover">
                                    @else
                                        <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500">Sin imagen</span>
                                        </div>
                                    @endif
                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold">{{ $match->movie->title }}</h3>
                                        <p class="text-sm text-gray-600">{{ $match->movie->release_year ?? 'N/A' }}</p>
                                        
                                        <div class="flex justify-between items-center mt-2">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                                <span class="ml-1 text-sm text-gray-600">{{ $match->movie->rating ?? 'N/A' }}</span>
                                            </div>
                                            
                                            <div class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full">
                                                {{ round($match->match_score) }}% match
                                            </div>
                                        </div>
                                        
                                        @if($match->liked !== null)
                                            <div class="mt-2">
                                                @if($match->liked)
                                                    <span class="text-green-500 text-sm">Te gusta</span>
                                                @else
                                                    <span class="text-red-500 text-sm">No te gusta</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <p class="text-gray-500">No tienes películas guardadas</p>
                                <a href="{{ route('movies.index') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Explorar películas
                                </a>
                            </div>
                        @endforelse
                    </div>
                    
                    <div class="mt-6">
                        {{ $matches->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
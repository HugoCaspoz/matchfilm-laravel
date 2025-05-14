<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $movie->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/3">
                            @if($movie->poster_url)
                                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="w-full rounded-lg shadow-lg">
                            @else
                                <div class="w-full h-96 bg-gray-200 flex items-center justify-center rounded-lg">
                                    <span class="text-gray-500">Sin imagen</span>
                                </div>
                            @endif
                            
                            <div class="mt-6">
                                <form action="{{ route('matches.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="movie_id" value="{{ $movie->tmdb_id }}">
                                    
                                    <div class="flex space-x-4 mb-4">
                                        <button type="submit" name="liked" value="1" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                            Me gusta
                                        </button>
                                        <button type="submit" name="liked" value="0" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                                            No me gusta
                                        </button>
                                    </div>
                                    
                                    <div class="flex items-center mt-4">
                                        <input type="checkbox" name="watched" value="1" id="watched" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label for="watched" class="ml-2 text-sm text-gray-700">Ya he visto esta película</label>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="md:w-2/3 md:pl-8 mt-6 md:mt-0">
                            <h1 class="text-3xl font-bold">{{ $movie->title }}</h1>
                            
                            <div class="flex items-center mt-2">
                                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <span class="ml-1 text-lg">{{ $movie->rating ?? 'N/A' }}</span>
                            </div>
                            
                            <div class="mt-4 flex flex-wrap">
                                <div class="mr-6 mb-4">
                                    <span class="text-gray-600">Año:</span>
                                    <span class="font-medium">{{ $movie->release_year ?? 'N/A' }}</span>
                                </div>
                                <div class="mr-6 mb-4">
                                    <span class="text-gray-600">Duración:</span>
                                    <span class="font-medium">{{ $movie->duration ? $movie->duration . ' min' : 'N/A' }}</span>
                                </div>
                                <div class="mr-6 mb-4">
                                    <span class="text-gray-600">Director:</span>
                                    <span class="font-medium">{{ $movie->director ?? 'N/A' }}</span>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <span class="text-gray-600">Géneros:</span>
                                <div class="flex flex-wrap mt-1">
                                    @if(is_array($movie->genres) && count($movie->genres) > 0)
                                        @foreach($movie->genres as $genre)
                                            <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-sm mr-2 mb-2">{{ $genre }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-gray-500">No disponible</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <h2 class="text-xl font-semibold mb-2">Sinopsis</h2>
                                <p class="text-gray-700">{{ $movie->description ?? 'No hay sinopsis disponible.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
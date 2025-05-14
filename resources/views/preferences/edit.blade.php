<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Preferencias') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif
                    
                    <form action="{{ route('preferences.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-6">
                            <label for="favorite_genres" class="block text-sm font-medium text-gray-700 mb-2">Géneros favoritos</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                @foreach($popularGenres as $genre)
                                    <div class="flex items-center">
                                        <input type="checkbox" name="favorite_genres[]" id="genre_{{ $loop->index }}" value="{{ $genre }}" 
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                               {{ is_array($preferences->favorite_genres) && in_array($genre, $preferences->favorite_genres) ? 'checked' : '' }}>
                                        <label for="genre_{{ $loop->index }}" class="ml-2 text-sm text-gray-700">{{ $genre }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="favorite_directors" class="block text-sm font-medium text-gray-700 mb-2">Directores favoritos</label>
                            <input type="text" name="favorite_directors" id="favorite_directors" 
                                   value="{{ is_array($preferences->favorite_directors) ? implode(', ', $preferences->favorite_directors) : '' }}" 
                                   placeholder="Ej: Christopher Nolan, Steven Spielberg, Quentin Tarantino"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <p class="mt-1 text-sm text-gray-500">Separa los nombres con comas</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="preferred_duration" class="block text-sm font-medium text-gray-700 mb-2">Duración preferida (minutos)</label>
                                <input type="number" name="preferred_duration" id="preferred_duration" 
                                       value="{{ $preferences->preferred_duration }}" min="0" max="300"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            
                            <div>
                                <label for="min_rating" class="block text-sm font-medium text-gray-700 mb-2">Rating mínimo (0-10)</label>
                                <input type="number" name="min_rating" id="min_rating" 
                                       value="{{ $preferences->min_rating }}" min="0" max="10" step="0.1"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="min_year" class="block text-sm font-medium text-gray-700 mb-2">Año mínimo</label>
                                <input type="number" name="min_year" id="min_year" 
                                       value="{{ $preferences->min_year }}" min="1900" max="{{ date('Y') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            
                            <div>
                                <label for="max_year" class="block text-sm font-medium text-gray-700 mb-2">Año máximo</label>
                                <input type="number" name="max_year" id="max_year" 
                                       value="{{ $preferences->max_year }}" min="1900" max="{{ date('Y') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Guardar preferencias
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
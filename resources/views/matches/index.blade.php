<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Matches') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($matches->isEmpty())
                        <div class="text-center py-8">
                            <i class="fas fa-film text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">Aún no tienes matches</h3>
                            <p class="text-gray-500 mb-4">Cuando tú y tus amigos den like a las mismas películas, aparecerán aquí.</p>
                            <a href="{{ route('movies.index') }}" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Explorar películas
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($matches as $match)
                                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                                    <div class="relative h-64">
                                        @if($match->movie_poster)
                                            <img src="{{ $match->movie_poster }}" alt="{{ $match->movie_title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                <i class="fas fa-film text-4xl text-gray-400"></i>
                                            </div>
                                        @endif
                                        <div class="absolute top-0 right-0 bg-red-500 text-white px-3 py-1 rounded-bl-lg">
                                            <i class="fas fa-heart mr-1"></i> Match
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="font-bold text-lg mb-2 truncate">{{ $match->movie_title }}</h3>
                                        <p class="text-gray-600 mb-4">
                                            <i class="fas fa-user mr-1"></i> Match con {{ $match->friend->name }}
                                        </p>
                                        <p class="text-gray-500 text-sm">
                                            <i class="fas fa-calendar-alt mr-1"></i> {{ $match->created_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @endpush
</x-app-layout>

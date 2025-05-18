<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/3 p-4 flex flex-col items-center">
                            @if($user->profile_image)
                                <img src="{{ Storage::url($user->profile_image) }}" alt="{{ $user->name }}" class="w-48 h-48 object-cover rounded-full mb-4">
                            @else
                                <div class="w-48 h-48 bg-gray-300 rounded-full flex items-center justify-center mb-4">
                                    <span class="text-4xl text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif

                            <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
                            <p class="text-gray-600 mb-4">{{ '@' . $user->username }}</p>

                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Editar Perfil') }}
                            </a>
                        </div>

                        <div class="md:w-2/3 p-4">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Información Personal</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Correo electrónico</p>
                                        <p>{{ $user->email }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Miembro desde</p>
                                        <p>{{ $user->created_at->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Biografía</h3>
                                <p>{{ $user->bio ?? 'No hay biografía disponible.' }}</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold mb-2 border-b pb-2">Estadísticas</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-gray-100 p-4 rounded-lg text-center">
                                        <p class="text-2xl font-bold">{{ $user->movieLikes()->where('liked', true)->count() }}</p>
                                        <p class="text-sm text-gray-600">Películas que te gustan</p>
                                    </div>
                                    <div class="bg-gray-100 p-4 rounded-lg text-center">
                                        <p class="text-2xl font-bold">{{ $user->initiatedMatches()->count() + $user->receivedMatches()->count() }}</p>
                                        <p class="text-sm text-gray-600">Matches totales</p>
                                    </div>
                                    <div class="bg-gray-100 p-4 rounded-lg text-center">
                                        <p class="text-2xl font-bold">{{ $user->friends()->count() }}</p>
                                        <p class="text-sm text-gray-600">Amigos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

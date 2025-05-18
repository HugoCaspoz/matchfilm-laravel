<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notificaciones') }}
            </h2>
            @if($notifications->where('read', false)->count() > 0)
                <form action="{{ route('notifications.read.all') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150">
                        Marcar todas como leídas
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($notifications->isEmpty())
                        <div class="text-center py-8">
                            <i class="fas fa-bell text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">No tienes notificaciones</h3>
                            <p class="text-gray-500">Recibirás notificaciones cuando haya actividad relacionada con tus amigos y matches.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($notifications as $notification)
                                <div class="flex p-4 {{ $notification->read ? 'bg-gray-50' : 'bg-red-50' }} rounded-lg">
                                    <div class="flex-shrink-0 mr-4">
                                        @if($notification->type == 'match')
                                            <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white">
                                                <i class="fas fa-heart"></i>
                                            </div>
                                        @elseif($notification->type == 'friend_request')
                                            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                        @elseif($notification->type == 'friend_accepted')
                                            <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white">
                                                <i class="fas fa-user-check"></i>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center text-white">
                                                <i class="fas fa-bell"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="font-medium">{{ $notification->message }}</p>
                                                <p class="text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if(!$notification->read)
                                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-gray-500 hover:text-gray-700">
                                                        Marcar como leída
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        @if($notification->type == 'match' && isset($notification->data['movie_title']))
                                            <div class="mt-2 flex items-center">
                                                @if(isset($notification->data['movie_poster']))
                                                    <img src="{{ $notification->data['movie_poster'] }}" alt="{{ $notification->data['movie_title'] }}" class="w-12 h-16 object-cover rounded mr-3">
                                                @endif
                                                <div>
                                                    <p class="font-medium">{{ $notification->data['movie_title'] }}</p>
                                                    <a href="{{ route('matches.index') }}" class="text-xs text-red-500 hover:text-red-700">
                                                        Ver todos mis matches
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notificaciones') }}
            </h2>
            @if($notifications->where('read', false)->count() > 0)
                <form action="{{ route('notifications.read.all') }}" method="POST">
                    @csrf
                    <button type="submit" class="mark-all-btn">
                        <i class="fas fa-check-double mr-2"></i> Marcar todas como leídas
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
                        <div class="empty-state">
                            <i class="fas fa-bell"></i>
                            <h3>No tienes notificaciones</h3>
                            <p>Recibirás notificaciones cuando haya actividad relacionada con tus amigos y matches.</p>
                        </div>
                    @else
                        <div class="notification-list">
                            @foreach($notifications as $notification)
                                <div class="notification-item {{ $notification->read ? 'read' : 'unread' }}">
                                    <div class="notification-icon 
                                        @if($notification->type == 'match') match
                                        @elseif($notification->type == 'friend_request' || $notification->type == 'friend_accepted') friend
                                        @elseif($notification->type == 'movie_invitation') invitation
                                        @else default @endif">
                                        @if($notification->type == 'match')
                                            <i class="fas fa-heart"></i>
                                        @elseif($notification->type == 'friend_request')
                                            <i class="fas fa-user-plus"></i>
                                        @elseif($notification->type == 'friend_accepted')
                                            <i class="fas fa-user-check"></i>
                                        @elseif($notification->type == 'movie_invitation')
                                            <i class="fas fa-film"></i>
                                        @else
                                            <i class="fas fa-bell"></i>
                                        @endif
                                    </div>
                                    
                                    <div class="notification-content">
                                        <div class="notification-header">
                                            <h4 class="notification-title">{{ $notification->message }}</h4>
                                            <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                        
                                        @if($notification->type == 'match' && isset($notification->data['movie_title']))
                                            <div class="movie-details">
                                                <div class="movie-poster">
                                                    @if(isset($notification->data['movie_poster']))
                                                        <img src="{{ $notification->data['movie_poster'] }}" alt="{{ $notification->data['movie_title'] }}">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                            <i class="fas fa-film text-gray-400"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="movie-info">
                                                    <h5 class="movie-title">{{ $notification->data['movie_title'] }}</h5>
                                                    <div class="notification-actions">
                                                        @if(!$notification->read)
                                                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                                                @csrf
                                                                <button type="submit" class="btn-mark-read">
                                                                    <i class="fas fa-check mr-1"></i> Marcar como leída
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <a href="{{ route('matches.index') }}" class="btn-primary-action">
                                                            <i class="fas fa-eye mr-1"></i> Ver mis matches
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($notification->type == 'movie_invitation' && isset($notification->data['movie_title']))
                                            <div class="movie-details">
                                                <div class="movie-poster">
                                                    @if(isset($notification->data['movie_poster']))
                                                        <img src="{{ $notification->data['movie_poster'] }}" alt="{{ $notification->data['movie_title'] }}">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                            <i class="fas fa-film text-gray-400"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="movie-info">
                                                    <h5 class="movie-title">{{ $notification->data['movie_title'] }}</h5>
                                                    <div class="movie-date">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        <span>{{ \Carbon\Carbon::parse($notification->data['watch_date'])->format('d/m/Y') }}</span>
                                                    </div>
                                                    @if(isset($notification->data['message']) && !empty($notification->data['message']))
                                                        <div class="movie-message">
                                                            "{{ $notification->data['message'] }}"
                                                        </div>
                                                    @endif
                                                    <div class="notification-actions">
                                                        @if(!$notification->read)
                                                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                                                @csrf
                                                                <button type="submit" class="btn-mark-read">
                                                                    <i class="fas fa-check mr-1"></i> Marcar como leída
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <a href="{{ route('matches.index') }}" class="btn-primary-action">
                                                            <i class="fas fa-check-circle mr-1"></i> Aceptar invitación
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="notification-actions">
                                                @if($notification->type == 'friend_request' && isset($notification->data['friendship_id']))
                                                    <form action="{{ route('friends.accept', $notification->data['friendship_id']) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="btn-primary-action">
                                                            <i class="fas fa-check mr-1"></i> Aceptar solicitud
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('friends.reject', $notification->data['friendship_id']) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="btn-mark-read">
                                                            <i class="fas fa-times mr-1"></i> Rechazar
                                                        </button>
                                                    </form>
                                                @elseif(!$notification->read)
                                                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn-mark-read">
                                                            <i class="fas fa-check mr-1"></i> Marcar como leída
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="mt-6">
                                {{ $notifications->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
    @endpush
</x-app-layout>

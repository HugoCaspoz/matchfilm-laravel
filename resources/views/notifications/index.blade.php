<x-app-layout>
    <x-slot name="header">
        <div class="notifications-header">
            <h2 class="notifications-title">
                {{ __('Notificaciones') }}
            </h2>
            @if($notifications->where('read', false)->count() > 0)
                <form action="{{ route('notifications.read.all') }}" method="POST">
                    @csrf
                    <button type="submit" class="mark-all-btn">
                        <i class="fas fa-check-double"></i> 
                        Marcar todas como leídas
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="notifications-container">
            @if($notifications->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-bell"></i>
                    <h3>No tienes notificaciones</h3>
                    <p>Recibirás notificaciones cuando haya actividad relacionada con tus amigos y matches.</p>
                </div>
            @else
                <div class="notification-list">
                    @foreach($notifications as $notification)
                        @php
                            // Decodificar datos JSON de forma segura
                            $data = null;
                            if ($notification->data) {
                                $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
                            }
                        @endphp
                        
                        <div class="notification-item {{ $notification->read ? 'read' : 'unread' }}">
                            <!-- Header de la notificación -->
                            <div class="notification-header">
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
                                
                                <div class="notification-header-content">
                                    <h4 class="notification-title">{{ $notification->message }}</h4>
                                    <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            
                            @if(($notification->type == 'match' || $notification->type == 'movie_invitation') && $data && isset($data['movie_title']))
                                <!-- Contenido de la película -->
                                <div class="movie-content">
                                    <div class="movie-poster">
                                        @if(isset($data['movie_poster']) && $data['movie_poster'])
                                            <img src="{{ $data['movie_poster'] }}" alt="{{ $data['movie_title'] }}">
                                        @else
                                            <div class="movie-poster-placeholder">
                                                <i class="fas fa-film"></i>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="movie-info">
                                        <h5 class="movie-title">{{ $data['movie_title'] }}</h5>
                                        
                                        @if($notification->type == 'movie_invitation')
                                            <div class="movie-details">
                                                @if(isset($data['watch_date']) && $data['watch_date'])
                                                    <div class="movie-date">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        <span class="date-text">Fecha propuesta: {{ \Carbon\Carbon::parse($data['watch_date'])->format('d/m/Y') }}</span>
                                                    </div>
                                                @endif
                                                
                                                @if(isset($data['message']) && !empty(trim($data['message'])))
                                                    <div class="movie-message">
                                                        <i class="fas fa-comment"></i>
                                                        <span class="message-text">"{{ $data['message'] }}"</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Acciones -->
                                <div class="notification-actions">
                                    @if($notification->type == 'movie_invitation')
                                        <a href="{{ route('matches.index') }}" class="btn btn-success">
                                            <i class="fas fa-check"></i> Aceptar invitación
                                        </a>
                                        <button onclick="declineInvitation({{ $notification->id }})" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Declinar
                                        </button>
                                    @else
                                        <a href="{{ route('matches.index') }}" class="btn btn-primary">
                                            <i class="fas fa-eye"></i> Ver mis matches
                                        </a>
                                    @endif
                                    
                                    @if(!$notification->read)
                                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline">
                                                <i class="fas fa-check"></i> Marcar como leída
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <!-- Notificaciones estándar -->
                                <div class="notification-actions">
                                    @if($notification->type == 'friend_request' && $data && isset($data['friendship_id']))
                                        <form action="{{ route('friends.accept', $data['friendship_id']) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-check"></i> Aceptar solicitud
                                            </button>
                                        </form>
                                        <form action="{{ route('friends.reject', $data['friendship_id']) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Rechazar
                                            </button>
                                        </form>
                                    @elseif(!$notification->read)
                                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline">
                                                <i class="fas fa-check"></i> Marcar como leída
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="pagination-container">
                        {{ $notifications->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
    @endpush
</x-app-layout>

@php
    $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;
    $isActive = request()->routeIs('notifications.index');
@endphp

<a href="{{ route('notifications.index') }}" class="navbar-notification-link {{ $isActive ? 'active' : '' }}" aria-label="Notifications">
    <i class="fas fa-bell"></i>
    @if ($unreadCount > 0)
        <span class="navbar-notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
    @endif
</a>

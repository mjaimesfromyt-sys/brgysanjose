{{--
    Notification bell for the resident top bar. Sits beside the Log out
    button. Shows an unread count and the 8 most recent items; each item
    links through to the related list (bookings / requests / rentals).
--}}
@php
    $notifUnread = auth()->user()->unreadNotifications()->count();
    $notifRecent = auth()->user()->notifications()->latest()->limit(8)->get();
@endphp

<div class="dropdown notif-menu">
    <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2 position-relative"
            type="button" id="notifBell" data-bs-toggle="dropdown" aria-expanded="false"
            aria-label="Notifications{{ $notifUnread ? ' ('.$notifUnread.' unread)' : '' }}">
        @include('partials.icon', ['name' => 'bell', 'size' => 18])
        <span class="d-xl-none">Notifications</span>
        @if ($notifUnread > 0)
            <span class="badge rounded-pill bg-danger notif-menu__count">
                {{ $notifUnread > 9 ? '9+' : $notifUnread }}
            </span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-end notif-menu__panel p-0" aria-labelledby="notifBell">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <span class="fw-bold">Notifications</span>
            @if ($notifUnread > 0)
                <form method="POST" action="{{ route('notifications.readAll') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
                </form>
            @endif
        </div>

        <div class="notif-menu__list">
            @forelse ($notifRecent as $note)
                <form method="POST" action="{{ route('notifications.read', $note->id) }}" class="m-0">
                    @csrf
                    <button type="submit"
                            class="notif-menu__item {{ $note->read_at ? '' : 'is-unread' }}">
                        <span class="notif-menu__icon">
                            @include('partials.icon', ['name' => $note->data['icon'] ?? 'info', 'size' => 18])
                        </span>
                        <span class="notif-menu__body">
                            <span class="notif-menu__title">{{ $note->data['title'] ?? 'Notification' }}</span>
                            <span class="notif-menu__text">{{ \Illuminate\Support\Str::limit($note->data['body'] ?? '', 90) }}</span>
                            <span class="notif-menu__time">{{ $note->created_at->diffForHumans() }}</span>
                        </span>
                    </button>
                </form>
            @empty
                <div class="px-3 py-4 text-center text-muted small">No notifications yet.</div>
            @endforelse
        </div>

        <a href="{{ route('notifications.index') }}"
           class="d-block text-center border-top px-3 py-2 small fw-semibold text-decoration-none">
            View all notifications
        </a>
    </div>
</div>

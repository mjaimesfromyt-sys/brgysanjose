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
    <button class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center position-relative notif-bell-compact"
            type="button" id="notifBell" data-bs-toggle="dropdown" aria-expanded="false"
            aria-label="Notifications{{ $notifUnread ? ' ('.$notifUnread.' unread)' : '' }}">
        @include('partials.icon', ['name' => 'bell', 'size' => 18])
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

{{-- 👉 AJAX POLLING: mo-check kada 20 seconds kon naa bay bag-ong notification --}}
@once
<script>
(function () {
    const POLL_INTERVAL = 20000;
    const pollUrl = "{{ route('notifications.poll') }}";
    let lastCount = {{ $notifUnread }};

    function updateBadge(count) {
        const bell = document.getElementById('notifBell');
        if (!bell) return;

        let badge = bell.querySelector('.notif-menu__count');

        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'badge rounded-pill bg-danger notif-menu__count';
                bell.appendChild(badge);
            }
            badge.textContent = count > 9 ? '9+' : count;
        } else if (badge) {
            badge.remove();
        }
    }

    function showToast(note) {
        const wrap = document.getElementById('notifToastWrap') || (function () {
            const el = document.createElement('div');
            el.id = 'notifToastWrap';
            el.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:1080;display:flex;flex-direction:column;gap:10px;max-width:340px;';
            document.body.appendChild(el);
            return el;
        })();

        const toast = document.createElement('a');
        toast.href = note.url;
        toast.style.cssText = 'background:#166534;color:#fff;padding:12px 16px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.2);text-decoration:none;display:block;animation:notifSlide .3s ease;';
        toast.innerHTML = '<div style="font-weight:700;font-size:14px;margin-bottom:2px;">' + note.title + '</div>'
                        + '<div style="font-size:12.5px;opacity:.9;line-height:1.35;">' + note.body + '</div>';

        wrap.appendChild(toast);
        setTimeout(() => toast.remove(), 8000);
    }

    async function poll() {
        try {
            const res = await fetch(pollUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;

            const data = await res.json();
            updateBadge(data.count);

            if (data.count > lastCount && data.notifications.length) {
                showToast(data.notifications[0]);
            }
            lastCount = data.count;
        } catch (e) {
            // Hilom lang kon mag-fail ang network — mo-retry ra sa sunod nga interval
        }
    }

    setInterval(poll, POLL_INTERVAL);
})();
</script>
<style>
@keyframes notifSlide { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: none; } }
</style>
@endonce


<style>
.notif-bell-compact {
    width: 38px;
    height: 38px;
    padding: 0;
}
.notif-bell-compact .notif-menu__count {
    position: absolute;
    top: -4px;
    right: -4px;
    font-size: 10px;
    padding: 2px 5px;
}
</style>

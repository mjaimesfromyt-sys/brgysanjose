@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-4">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle mb-0">Updates on your bookings, document requests and equipment rentals.</p>
    </div>
    @if (auth()->user()->unreadNotifications()->count() > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">Mark all read</button>
        </form>
    @endif
</div>

<div class="card-soft">
    @forelse ($notifications as $note)
        <form method="POST" action="{{ route('notifications.read', $note->id) }}" class="m-0">
            @csrf
            <button type="submit" class="notif-row {{ $note->read_at ? '' : 'is-unread' }}">
                <span class="notif-row__icon">
                    @include('partials.icon', ['name' => $note->data['icon'] ?? 'info', 'size' => 22])
                </span>
                <span class="notif-row__body">
                    <span class="notif-row__title">{{ $note->data['title'] ?? 'Notification' }}</span>
                    <span class="notif-row__text">{{ $note->data['body'] ?? '' }}</span>
                    <span class="notif-row__time">{{ $note->created_at->diffForHumans() }}</span>
                </span>
                @unless ($note->read_at)
                    <span class="notif-row__dot" aria-label="Unread"></span>
                @endunless
            </button>
        </form>
    @empty
        <div class="empty">
            <div class="empty__title">No notifications</div>
            <p class="mb-0">You'll see updates here when the status of one of your requests changes.</p>
        </div>
    @endforelse
</div>

@if ($notifications->hasPages())
    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
@endif
@endsection

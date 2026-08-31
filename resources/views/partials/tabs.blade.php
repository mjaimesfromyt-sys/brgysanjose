{{--
    Status filter tabs shared by the admin residents / bookings / requests screens.

    Usage:
        @include('partials.tabs', [
            'routeName' => 'admin.bookings.index',
            'current'   => $status,
            'counts'    => $counts,
            'tabs'      => ['pending' => 'Pending', 'approved' => 'Approved'],
        ])
--}}
<ul class="nav nav-pills gap-2 mb-4">
    @foreach ($tabs as $key => $label)
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 {{ $current === $key ? 'active' : '' }}"
               href="{{ route($routeName, ['status' => $key]) }}">
                <span>{{ $label }}</span>
                <span class="badge rounded-pill {{ $current === $key ? 'text-bg-light' : 'text-bg-secondary' }}">
                    {{ $counts[$key] ?? 0 }}
                </span>
            </a>
        </li>
    @endforeach
</ul>

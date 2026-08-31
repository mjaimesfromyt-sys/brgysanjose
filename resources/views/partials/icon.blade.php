{{--
    Inline SVG icon set (Lucide-style, 1.75px stroke).
    Inlined rather than pulled from an icon font/CDN so there is no extra
    network request and nothing to break on shared hosting.

    Icons always sit ALONGSIDE a text label, never replacing one — the
    primary audience includes elderly residents (CLAUDE.md §0).

    Usage: @include('partials.icon', ['name' => 'users'])
           @include('partials.icon', ['name' => 'users', 'size' => 24])
--}}
@php($iconSize = $size ?? 20)
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $iconSize }}" height="{{ $iconSize }}"
     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        {{-- navigation --}}
        @case('dashboard')
            <rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/>
            <rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>
            @break
        @case('home')
            <path d="m3 10 9-7 9 7v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M9 21v-8h6v8"/>
            @break
        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            @break
        @case('user')
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            @break
        @case('file-text')
            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v5h6"/>
            <path d="M8 13h8M8 17h8M8 9h2"/>
            @break
        @case('calendar-check')
            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
            <path d="m9 16 2 2 4-4"/>
            @break
        @case('calendar')
            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
            @break
        @case('building')
            <rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4"/>
            <path d="M8 6h.01M16 6h.01M8 10h.01M16 10h.01M8 14h.01M16 14h.01"/>
            @break
        @case('clipboard')
            <rect x="8" y="2" width="8" height="4" rx="1"/>
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
            <path d="M9 13h6M9 17h4"/>
            @break
        @case('chart')
            <path d="M3 3v18h18"/><rect x="7" y="12" width="3" height="6" rx="1"/>
            <rect x="12.5" y="8" width="3" height="10" rx="1"/><rect x="18" y="5" width="3" height="13" rx="1"/>
            @break
        @case('refund')
            <path d="M3 7v6h6"/>
            <path d="M3.5 13a9 9 0 1 0 2.3-9.3L3 7"/>
            <path d="M12 8v4l3 2"/>
            @break
        @case('bell')
            <path d="M10.268 21a2 2 0 0 0 3.464 0"/>
            <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/>
            @break
        @case('megaphone')
            <path d="m3 11 15-7v16l-15-7Z"/><path d="M18 8a3 3 0 0 1 0 6"/>
            <path d="M7 12.5V19a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4.5"/>
            @break
        @case('newspaper')
            <path d="M4 4h13a1 1 0 0 1 1 1v14a2 2 0 0 0 2 2H5a2 2 0 0 1-2-2V5a1 1 0 0 1 1-1Z"/>
            <path d="M18 8h2a1 1 0 0 1 1 1v10a2 2 0 0 1-2 2"/><path d="M7 8h7M7 12h7M7 16h4"/>
            @break

        {{-- actions --}}
        @case('logout')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>
            @break
        @case('menu')
            <path d="M3 6h18M3 12h18M3 18h18"/>
            @break
        @case('close')
            <path d="M18 6 6 18M6 6l12 12"/>
            @break
        @case('arrow-right')
            <path d="M5 12h14M12 5l7 7-7 7"/>
            @break
        @case('arrow-left')
            <path d="M19 12H5M12 19l-7-7 7-7"/>
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('edit')
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/>
            @break
        @case('trash')
            <path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6M14 11v6"/>
            @break
        @case('print')
            <path d="M6 9V2h12v7"/><rect x="6" y="14" width="12" height="8" rx="1"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            @break
        @case('eye')
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
            @break

        {{-- status & meta --}}
        @case('pin')
            <path d="M12 17v5"/><path d="M9 10.76V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5.76a2 2 0 0 0 .59 1.42L17 13.5V17H7v-3.5l1.41-1.32A2 2 0 0 0 9 10.76Z"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
            @break
        @case('map-pin')
            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
            @break
        @case('mail')
            <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>
            @break
        @case('phone')
            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>
            @break
        @case('check-circle')
            <circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-6"/>
            @break
        @case('alert')
            <path d="M12 3 2 20h20L12 3Z"/><path d="M12 10v4M12 17h.01"/>
            @break
        @case('info')
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
            @break
        @case('shield')
            <path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5Z"/><path d="m9 12 2 2 4-4"/>
            @break
        @case('ticket')
            <path d="M3 9V7a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v2a3 3 0 0 0 0 6v2a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-2a3 3 0 0 0 0-6Z"/>
            <path d="M13 6v2M13 11v2M13 16v2"/>
            @break
        @case('history')
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 6v6l4 2"/>
            <path d="M12 12a4 4 0 1 1-4-4"/>
            @break
        @case('package')
            <path d="M16.5 9.4 7.55 4.24"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
            <path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>
            @break
    @endswitch
</svg>

@extends('layouts.admin')
@section('title', 'Residents')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Resident Accounts</h1>
    <p class="page-subtitle">Verify registrations and confirm whether each applicant lives in the barangay.</p>

    <!-- Tab Navigation -->
    <ul class="nav nav-pills gap-2 mb-4">
        @foreach (["pending" => "Pending", "active" => "Active", "rejected" => "Rejected"] as $key => $label)
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 {{ $status === $key ? "active" : "" }}"
                   href="{{ route("admin.residents.index", ["status" => $key]) }}">
                    <span>{{ $label }}</span>
                    <span class="badge rounded-pill {{ $status === $key ? "text-bg-light" : "text-bg-secondary" }}">
                        {{ $counts[$key] ?? 0 }}
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</div>

@if ($users->isEmpty())
    <div class="card-soft">
        <div class="empty">
            <div class="empty__title">No {{ $status }} accounts</div>
            <p class="mb-0">Accounts with this status will appear here.</p>
        </div>
    </div>
@else
    <div class="stats-row">
        <div class="stat-card danger">
            <div class="stat-icon">&#128308;</div>
            <div class="stat-content">
                <div class="stat-label">Rejected</div>
                <div class="stat-value">{{ $counts['rejected'] ?? 0 }}</div>
            </div>
        </div>
        <div class="stat-card amber">
            <div class="stat-icon">&#9888;&#65039;</div>
            <div class="stat-content">
                <div class="stat-label">Total Residents</div>
                <div class="stat-value">{{ $users->count() }}</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon">&#10003;</div>
            <div class="stat-content">
                <div class="stat-label">Active</div>
                <div class="stat-value">{{ $counts['active'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <!-- ID Photo Lightbox -->
    <div id="idViewer" class="id-viewer d-none">
        <img id="idViewerImg" src="" alt="ID Photo" class="w-100">
        <div class="id-viewer-body">
            <button type="button" id="idViewerClose" class="btn btn-outline-secondary btn-sm mb-2">✕ Close</button>
            <div class="id-viewer-meta">
                <strong><span id="idViewerName"></span></strong><br>
                <span id="idViewerType"></span> 
                <span id="idViewerNum"></span>
                <span id="idViewerPurok"></span>
            </div>
            <a id="idViewerOpen" href="" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Open full resolution</a>
        </div>
    </div>

    <div class="resident-grid">
        @foreach ($users as $user)
            <div class="resident-card {{ $status === 'rejected' ? 'is-rejected' : '' }} {{ $status === 'pending' ? 'is-pending' : '' }} {{ $status === 'active' ? 'is-active' : '' }}">
                <div class="resident-avatar">
                    {{ \Illuminate\Support\Str::limit(strip_tags($user->name), 2) }}
                </div>
                <div class="resident-body">
                    <div class="resident-name">{{ $user->name }}</div>
                    <div class="resident-detail">
                        &#128222; {{ $user->contact_no ?? '—' }}
                        @if ($user->purok)
                            · Barangay San Jose, Purok {{ $user->purok }}
                        @endif
                        @if ($user->birthdate)
                            · {{ \Carbon\Carbon::parse($user->birthdate)->format('M d, Y') }} ({{ \Carbon\Carbon::parse($user->birthdate)->age }} y/o) · {{ $user->civil_status ?? 'Single' }}
                        @endif
                    </div>

                    @if ($user->id_type)
                        <div class="resident-id">
                            <span class="pill pill--info">{{ $user->id_type }}: <strong>{{ $user->id_number }}</strong></span>
                            @if ($user->id_photo_front)
                                <button type="button" onclick="openIdViewer('/{{ ltrim($user->id_photo_front, '/') }}', '{{ addslashes($user->name) }}', '{{ $user->id_type ?? 'Government ID' }}', '{{ $user->id_number ?? '' }}', '{{ $user->purok ?? '' }}')" class="btn btn-sm btn-outline-success ms-2">
                                    &#128269; Inspect ID Photo
                                </button>
                            @endif
                        </div>
                    @else
                        <span class="text-muted small">No ID submitted</span>
                    @endif

                    <div class="resident-type">
                        @if ($user->resident_type)
                            <span class="pill {{ $user->resident_type === 'resident' ? 'pill--approved' : 'pill--neutral' }}">
                                {{ $user->resident_type === 'resident' ? 'Resident' : 'Non-resident' }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>

                    @if ($status === 'rejected' && $user->rejection_reason)
                        <div class="rejection-reason">
                            <div class="reason-label">&#10060; Reason for rejection</div>
                            <div class="reason-text">{{ $user->rejection_reason }}</div>
                        </div>
                    @endif

                    <div class="resident-actions">
                        @if ($status === 'pending')
                            <form method="POST" action="{{ route('admin.residents.approve', $user) }}" class="d-inline">
                                @csrf
                                <select name="resident_type" class="form-select form-select-sm d-inline" style="max-width:140px" aria-label="Resident type">
                                    <option value="resident" {{ $user->declared_type === 'resident' ? 'selected' : '' }}>Resident</option>
                                    <option value="non_resident" {{ $user->declared_type === 'non_resident' ? 'selected' : '' }}>Non-resident</option>
                                </select>
                                <button class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#rej-{{ $user->id }}">
                                Reject
                            </button>
                            <div class="collapse mt-2" id="rej-{{ $user->id }}">
                                <form method="POST" action="{{ route('admin.residents.reject', $user) }}" class="d-inline">
                                    @csrf
                                    <textarea name="rejection_reason" rows="2" class="form-control form-control-sm mb-2" required minlength="3" placeholder="Reason (required — shown to the applicant)"></textarea>
                                    <button class="btn btn-sm btn-danger">Confirm rejection</button>
                                </form>
                            </div>
                        @elseif ($status === 'rejected')
                            <div class="reason-display">
                                <div class="reason-text">{{ $user->rejection_reason }}</div>
                            </div>
                            <form method="POST" action="{{ route('admin.residents.reconsider', $user) }}" class="d-inline">
                                @csrf
                                <select name="resident_type" class="form-select form-select-sm d-inline" style="max-width:140px" aria-label="Resident type">
                                    <option value="resident" {{ $user->declared_type === 'resident' ? 'selected' : '' }}>Resident</option>
                                    <option value="non_resident" {{ $user->declared_type === 'non_resident' ? 'selected' : '' }}>Non-resident</option>
                                </select>
                                <button class="btn btn-sm btn-success">Re-verify</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="editRejectionReason({{ $user->id }})">
                                &#10022; Edit reason
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection

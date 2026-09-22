@extends('layouts.admin')
@section('title', 'Residents')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Resident Accounts</h1>
    <p class="page-subtitle">Verify registrations and confirm whether each applicant lives in the barangay.</p>
</div>

@include('partials.tabs', [
    'routeName' => 'admin.residents.index',
    'current'   => $status,
    'counts'    => $counts,
    'tabs'      => ['pending' => 'Pending', 'active' => 'Active', 'rejected' => 'Rejected'],
])

@if ($users->isEmpty())
    <div class="card-soft">
        <div class="empty">
            <div class="empty__title">No {{ $status }} accounts</div>
            <p class="mb-0">Accounts with this status will appear here.</p>
        </div>
    </div>
@else
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Verification / ID</th>
                        <th>Type</th>
                        @if ($status === 'pending')
                            <th class="text-end">Action</th>
                        @elseif ($status === 'rejected')
                            <th style="min-width:220px">Reason for rejection</th>
                            <th class="text-end">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <div class="text-muted small">{{ $user->email }}</div>
                            </td>
                            <td>{{ $user->contact_no ?? '—' }}</td>
                            <td>
                                {{ $user->address ?? '—' }}
                                @if ($user->purok)
                                    <div class="text-muted small">Purok: <strong>{{ $user->purok }}</strong></div>
                                @endif
                                @if ($user->birthdate)
                                    <div class="text-muted small mt-1">
                                        Birthdate: {{ \Carbon\Carbon::parse($user->birthdate)->format('M d, Y') }} ({{ \Carbon\Carbon::parse($user->birthdate)->age }} y/o) &bull; {{ $user->civil_status ?? 'Single' }}
                                    </div>
                                @endif
                            </td>
                            <td style="min-width:200px">
                                @if ($user->id_type)
                                    <div class="mb-1">
                                        <span class="badge bg-light text-dark border">
                                            {{ $user->id_type }}: <strong>{{ $user->id_number }}</strong>
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted small">No ID submitted</span>
                                @endif

                                @if ($user->id_photo_front)
                                    <div class="mt-1 mb-1">
                                        <button type="button" onclick="openIdViewer('/{{ ltrim($user->id_photo_front, '/') }}', '{{ addslashes($user->name) }}', '{{ $user->id_type ?? 'Government ID' }}', '{{ $user->id_number ?? '' }}', '{{ $user->purok ?? '' }}')" class="btn btn-sm btn-outline-success py-1 px-3 rounded-pill fw-semibold shadow-sm">
                                            Inspect Valid ID Photo &rarr;
                                        </button>
                                        <a href="/{{ ltrim($user->id_photo_front, '/') }}" target="_blank" class="small text-muted ms-1 text-decoration-none" title="Open original photo in new tab">&nearr;</a>
                                    </div>
                                @endif

                                @if ($user->declared_type)
                                    <div class="text-muted small">
                                        Declared: {{ $user->declared_type === 'resident' ? 'Resident' : 'Non-resident' }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if ($user->resident_type)
                                    <span class="pill {{ $user->resident_type === 'resident' ? 'pill--approved' : 'pill--neutral' }}">
                                        {{ $user->resident_type === 'resident' ? 'Resident' : 'Non-resident' }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            @if ($status === 'pending')
                                <td class="text-end" style="min-width:290px">
                                    <form method="POST" action="{{ route('admin.residents.approve', $user) }}"
                                          class="d-flex gap-2 justify-content-end mb-2">
                                        @csrf
                                        <select name="resident_type" class="form-select form-select-sm"
                                                required style="max-width:150px" aria-label="Resident type">
                                            <option value="resident" {{ $user->declared_type === 'resident' ? 'selected' : '' }}>Resident</option>
                                            <option value="non_resident" {{ $user->declared_type === 'non_resident' ? 'selected' : '' }}>Non-resident</option>
                                        </select>
                                        <button class="btn btn-sm btn-success">Approve</button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="collapse" data-bs-target="#rej-{{ $user->id }}">
                                        Reject
                                    </button>

                                    <div class="collapse mt-2 text-start" id="rej-{{ $user->id }}">
                                        <form method="POST" action="{{ route('admin.residents.reject', $user) }}">
                                            @csrf
                                            <textarea name="rejection_reason" rows="2" class="form-control form-control-sm mb-2"
                                                      required minlength="3"
                                                      placeholder="Reason (required — shown to the applicant)"></textarea>
                                            <button class="btn btn-sm btn-danger w-100">Confirm rejection</button>
                                        </form>
                                    </div>
                                </td>
                            @endif

                            @if ($status === 'rejected')
                                <td class="small" style="max-width:300px">
                                    @if ($user->rejection_reason)
                                        <span class="text-dark">{{ $user->rejection_reason }}</span>
                                    @else
                                        <span class="text-muted fst-italic">No reason recorded</span>
                                    @endif
                                </td>
                                <td class="text-end" style="min-width:280px">
                                    @if ($user->rejection_reason)
                                        <p class="small text-muted mb-2 text-start">
                                            Reason: {{ $user->rejection_reason }}
                                        </p>
                                    @endif
                                    <form method="POST" action="{{ route('admin.residents.reconsider', $user) }}"
                                          class="d-flex gap-2 justify-content-end">
                                        @csrf
                                        <select name="resident_type" class="form-select form-select-sm"
                                                required style="max-width:150px" aria-label="Resident type">
                                            <option value="resident" {{ $user->declared_type === 'resident' ? 'selected' : '' }}>Resident</option>
                                            <option value="non_resident" {{ $user->declared_type === 'non_resident' ? 'selected' : '' }}>Non-resident</option>
                                        </select>
                                        <button class="btn btn-sm btn-success">Approve now</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- DEDICATED ID PHOTO LIGHTBOX VIEWER --}}
<div id="idViewerOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.82); z-index:99999; align-items:center; justify-content:center; padding:20px;" onclick="if(event.target === this) closeIdViewer()">
    <div style="background:#ffffff; border-radius:16px; max-width:820px; width:100%; max-height:90vh; overflow:hidden; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);" onclick="event.stopPropagation()">
        <div style="padding:16px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
            <div>
                <h6 id="idViewerTitle" style="font-weight:bold; margin:0; color:#0f172a; font-size:16px;">Valid ID Verification</h6>
                <div id="idViewerSub" style="font-size:12.5px; color:#64748b; margin-top:2px;"></div>
            </div>
            <button type="button" onclick="closeIdViewer()" style="border:none; background:transparent; font-size:28px; line-height:1; cursor:pointer; color:#64748b; padding:0 6px;">&times;</button>
        </div>
        <div style="padding:20px; text-align:center; overflow-y:auto; background:#f1f5f9; display:flex; align-items:center; justify-content:center; min-height:320px;">
            <img id="idViewerImg" src="" alt="Valid ID Preview" style="max-height:65vh; max-width:100%; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.12); border:1.5px solid #cbd5e1; object-fit:contain;">
        </div>
        <div style="padding:12px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
            <a id="idViewerExternalLink" href="#" target="_blank" style="font-size:13px; font-weight:600; color:#16a34a; text-decoration:none;">
                Open Full Resolution in New Tab &nearr;
            </a>
            <button type="button" onclick="closeIdViewer()" style="padding:6px 18px; border-radius:20px; border:1px solid #cbd5e1; background:#ffffff; font-size:13px; font-weight:600; cursor:pointer; color:#334155;">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function openIdViewer(url, name, type, num, purok) {
    document.getElementById("idViewerImg").src = url;
    document.getElementById("idViewerTitle").textContent = "Valid ID Verification: " + name;
    document.getElementById("idViewerSub").textContent = (type || "Government ID") + (num ? " • ID No: " + num : "") + (purok ? " • " + purok : "");
    document.getElementById("idViewerExternalLink").href = url;
    const overlay = document.getElementById("idViewerOverlay");
    overlay.style.display = "flex";
}
function closeIdViewer() {
    const overlay = document.getElementById("idViewerOverlay");
    if (overlay) overlay.style.display = "none";
    const img = document.getElementById("idViewerImg");
    if (img) img.src = "";
}
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") closeIdViewer();
});
</script>

@endsection

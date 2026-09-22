@extends('layouts.admin')
@section('title', 'Document Requests')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Document Requests</h1>
    <p class="page-subtitle">Validate requests to issue a claim code, then mark them claimed at the counter.</p>
</div>

{{-- CLAIM-CODE QUICK LOOKUP: type/paste a code at the counter, get the request instantly --}}
<div class="card-soft mb-4" style="max-width:640px">
    <div class="p-3">
        <label for="claimLookup" class="form-label small fw-semibold mb-2">
            🔎 Claim-code quick lookup
        </label>
        <div class="position-relative">
            <input type="text" id="claimLookup" autocomplete="off"
                   class="form-control"
                   placeholder="Type or paste a claim code, e.g. BRGY-2026-0042…">
            <div id="claimLookupResults" class="list-group mt-2" style="display:none"></div>
        </div>
    </div>
</div>

<script>
(function () {
    const input = document.getElementById("claimLookup");
    const box = document.getElementById("claimLookupResults");
    if (!input || !box) return;

    let timer = null, lastQ = "";

    function esc(s) {
        const d = document.createElement("div");
        d.textContent = s == null ? "" : String(s);
        return d.innerHTML;
    }

    function render(rows) {
        if (!rows.length) {
            box.innerHTML = '<div class="list-group-item text-muted small">No request matches this code.</div>';
            box.style.display = "block";
            return;
        }
        box.innerHTML = rows.map(function (r) {
            const paid = r.payment === 'paid';
            return '<a class="list-group-item list-group-item-action" href="' + esc(r.url) + '">' +
                '<div class="d-flex justify-content-between align-items-center gap-2">' +
                '<div><div class="fw-semibold">' + esc(r.claim_code) + ' <span class=\"text-muted fw-normal\">· ' + esc(r.kind) + '</span></div>' +
                '<div class=\"small text-muted\">' + esc(r.what) + ' — ' + esc(r.who) + '</div></div>' +
                '<span class="pill ' + (paid ? 'pill--approved' : 'pill--neutral') + '">' + esc(r.payment) + '</span>' +
                '</div></a>';
        }).join("");
        box.style.display = "block";
    }

    input.addEventListener("input", function () {
        const q = input.value.trim();
        clearTimeout(timer);
        if (!q) { box.style.display = "none"; return; }
        timer = setTimeout(function () {
            fetch("{{ route('admin.requests.lookup') }}?code=" + encodeURIComponent(q),
                  { headers: { "X-Requested-With": "XMLHttpRequest" } })
                .then(function (r) { return r.json(); })
                .then(function (d) { render(d.results || []); })
                .catch(function () {});
        }, 250);
    });

    document.addEventListener("click", function (e) {
        if (!box.contains(e.target) && e.target !== input) box.style.display = "none";
    });
})();
</script>

@include('partials.tabs', [
    'routeName' => 'admin.requests.index',
    'current'   => $status,
    'counts'    => $counts,
    'tabs'      => [
        'pending'   => 'Pending',
        'validated' => 'Validated',
        'claimed'   => 'Claimed',
        'rejected'  => 'Rejected',
    ],
])

@if ($requests->isEmpty())
    <div class="card-soft">
        <div class="empty">
            <div class="empty__title">No {{ $status }} requests</div>
            <p class="mb-0">Requests with this status will appear here.</p>
        </div>
    </div>
@else
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Resident</th>
                        <th>Document</th>
                        <th>Purpose</th>
                        {{-- SWAPPED-COLS --}}
                        @if ($status === 'rejected')
                            <th class="text-nowrap">Requested</th>
                            <th class="text-nowrap">Status</th>
                        @else
                            <th class="text-nowrap">Payment</th>
                            @if ($status === 'validated' || $status === 'claimed')<th class="text-nowrap">Claim code</th>@endif
                            <th class="text-nowrap">Requested</th>
                        @endif
                        @if ($status === 'rejected')<th style="min-width:220px">Reason for rejection</th>@endif
                        @if ($status !== 'rejected')<th class="text-end text-nowrap">{{ $status === 'claimed' ? 'Status' : 'Action' }}</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $req)
                        <tr>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 220px;">{{ $req->user->name }}</div>
                                <div class="text-muted small">
                                    {{ $req->user->isResident() ? 'Resident' : 'Non-resident' }}
                                    @if ($req->user->purok) &middot; {{ $req->user->purok }} @endif
                                </div>
                            </td>
                            <td class="text-nowrap fw-medium">{{ $req->transactionType->name }}</td>
                            <td>{{ $req->purpose ?? '—' }}</td>

                            @if ($status !== 'rejected')
                            <td class="text-nowrap">
                                @include('partials.payment-pill', ['model' => $req, 'markPaidRoute' => route('admin.requests.markPaid', $req)])
                            </td>
                            @endif

                            @if ($status === 'validated' || $status === 'claimed')
                                <td class="text-nowrap">
                                    <span class="fw-bold font-monospace" style="font-size: 13px;">{{ $req->claim_code }}</span>
                                </td>
                            @endif

                            <td class="text-muted text-nowrap">{{ $req->created_at->format('M d, Y') }}</td>

                            @if ($status === 'rejected')
                                <td class="text-nowrap">@include('partials.status', ['status' => $req->status])</td>
                            @endif

                            @if ($status === 'rejected')
                                <td class="small" style="max-width:300px">
                                    @if ($req->admin_remarks)
                                        <span class="text-dark">{{ $req->admin_remarks }}</span>
                                    @else
                                        <span class="text-muted fst-italic">No reason recorded</span>
                                    @endif
                                </td>
                            @endif

                            <!-- ACTION COLUMN -->
                            @if ($status === 'pending')
                                <td class="text-end text-nowrap">
                                    <div class="d-inline-flex justify-content-end align-items-center" style="gap: 8px;">
                                        <!-- 👉 DILI MA-CLICK ANG VALIDATE KON UNPAID PA -->
                                        @if ($req->payment_status === 'unpaid')
                                            <button type="button" class="btn btn-sm btn-success opacity-50 text-nowrap" disabled 
                                                    title="Cannot validate: Payment must be marked as paid first." 
                                                    style="cursor: not-allowed; font-size: 12px; padding: 5px 12px;">
                                                Validate &amp; issue code
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('admin.requests.validate', $req) }}" class="m-0">
                                                @csrf
                                                <button class="btn btn-sm btn-success text-nowrap" style="font-size: 12px; padding: 5px 12px;">
                                                    Validate &amp; issue code
                                                </button>
                                            </form>
                                        @endif

                                        @if ($req->payment_status === 'paid')
                                            <button type="button" class="btn btn-sm btn-outline-danger opacity-50 text-nowrap" disabled
                                                    title="Cannot reject: payment has already been collected."
                                                    style="cursor: not-allowed; font-size: 12px; padding: 5px 12px;">
                                                Reject
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-danger text-nowrap"
                                                    style="font-size: 12px; padding: 5px 12px;"
                                                    data-bs-toggle="collapse" data-bs-target="#rej-{{ $req->id }}">
                                                Reject
                                            </button>
                                        @endif
                                    </div>

                                    <div class="collapse mt-2 text-start" id="rej-{{ $req->id }}">
                                        <form method="POST" action="{{ route('admin.requests.reject', $req) }}" class="m-0">
                                            @csrf
                                            <textarea name="admin_remarks" rows="2" class="form-control form-control-sm mb-2"
                                                      required minlength="3"
                                                      placeholder="Reason (required — shown to the resident)"></textarea>
                                            <button class="btn btn-sm btn-danger w-100" style="font-size: 12px;">Confirm rejection</button>
                                        </form>
                                    </div>
                                </td>
                            @elseif ($status === 'validated')
                                <td class="text-end text-nowrap">
                                    <div class="d-inline-flex justify-content-end align-items-center" style="gap: 8px;">
                                        <a href="{{ route('documents.pdf', $req->verification_code ?: $req->id) }}" target="_blank" 
                                           class="btn btn-sm btn-outline-dark text-nowrap d-inline-flex align-items-center gap-1 shadow-none" 
                                           style="font-size: 12px; padding: 5px 11px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg>
                                            Print PDF
                                        </a>

                                        <form method="POST" action="{{ route('admin.requests.claimed', $req) }}" class="m-0"
                                              onsubmit="return confirm('Mark this request as claimed? Do this when the resident has presented their code and IDs at the hall.')">
                                            @csrf
                                            <button class="btn btn-sm btn-success text-nowrap" style="font-size: 12px; padding: 5px 11px;">Mark as claimed</button>
                                        </form>
                                    </div>
                                </td>
                            @elseif ($status === 'claimed')
                                <td class="text-end text-nowrap">
                                    @include('partials.status', ['status' => $req->status])
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
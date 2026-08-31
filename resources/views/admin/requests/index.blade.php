@extends('layouts.admin')
@section('title', 'Document Requests')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Document Requests</h1>
    <p class="page-subtitle">Validate requests to issue a claim code, then mark them claimed at the counter.</p>
</div>

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
            <table class="table">
                <thead>
                    <tr>
                        <th>Resident</th>
                        <th>Document</th>
                        <th>Purpose</th>
                        <th>Payment</th>
                        @if ($status === 'validated' || $status === 'claimed')<th>Claim code</th>@endif
                        <th>Requested</th>
                        @if ($status === 'pending' || $status === 'validated')<th class="text-end">Action</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $req)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $req->user->name }}</div>
                                <div class="text-muted small">
                                    {{ $req->user->isResident() ? 'Resident' : 'Non-resident' }}
                                    @if ($req->user->purok) &middot; {{ $req->user->purok }} @endif
                                </div>
                            </td>
                            <td>{{ $req->transactionType->name }}</td>
                            <td>{{ $req->purpose ?? '—' }}</td>
                            <td>
                                @include('partials.payment-pill', ['model' => $req, 'markPaidRoute' => route('admin.requests.markPaid', $req)])
                            </td>

                            @if ($status === 'validated' || $status === 'claimed')
                                <td>
                                    <span class="fw-bold" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace">
                                        {{ $req->claim_code }}
                                    </span>
                                </td>
                            @endif

                            <td class="text-muted">{{ $req->created_at->format('M d, Y') }}</td>

                            @if ($status === 'pending')
                                <td class="text-end" style="min-width:260px">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <form method="POST" action="{{ route('admin.requests.validate', $req) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success">Validate &amp; issue code</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="collapse" data-bs-target="#rej-{{ $req->id }}">
                                            Reject
                                        </button>
                                    </div>

                                    <div class="collapse mt-2 text-start" id="rej-{{ $req->id }}">
                                        <form method="POST" action="{{ route('admin.requests.reject', $req) }}">
                                            @csrf
                                            <textarea name="admin_remarks" rows="2" class="form-control form-control-sm mb-2"
                                                      placeholder="Reason (shown to resident, optional)"></textarea>
                                            <button class="btn btn-sm btn-danger w-100">Confirm rejection</button>
                                        </form>
                                    </div>
                                </td>
                            @elseif ($status === 'validated')
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.requests.claimed', $req) }}"
                                          onsubmit="return confirm('Mark this request as claimed? Do this when the resident has presented their code and IDs at the hall.')">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">Mark as claimed</button>
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
@endsection

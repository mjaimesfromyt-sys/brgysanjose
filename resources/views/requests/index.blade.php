@extends('layouts.app')
@section('title', 'My Requests')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2">
            @include('partials.icon', ['name' => 'file-text', 'size' => 26])
            My Document Requests
        </h1>
        <p class="page-subtitle">Track the status of your barangay documents.</p>
    </div>
    <a href="{{ route('requests.create') }}" class="btn btn-primary">
        @include('partials.icon', ['name' => 'plus', 'size' => 18])
        <span class="ms-1">New request</span>
    </a>
</div>

@if ($requests->isEmpty())
    <div class="card-soft">
        <div class="empty">
            @include('partials.icon', ['name' => 'file-text', 'size' => 32])
            <div class="empty__title mt-2">No requests yet</div>
            <p>Apply for a clearance, certificate or permit without queueing.</p>
            <a href="{{ route('requests.create') }}" class="btn btn-primary mt-2">Request a document</a>
        </div>
    </div>
@else
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Purpose</th>
                        <th>Requested</th>
                        <th>Claim code</th>
                        <th class="text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $req)
                        <tr>
                            <td class="fw-semibold">{{ $req->transactionType->name }}</td>
                            <td>{{ $req->purpose ?? '—' }}</td>
                            <td class="text-muted">{{ $req->created_at->format('M d, Y') }}</td>
                            <td>
                                @if ($req->claim_code)
                                    <a href="{{ route('requests.receipt', $req) }}" class="small fw-semibold text-decoration-none d-block">View receipt</a>
                                    <span class="fw-bold" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace">
                                        {{ $req->claim_code }}
                                    </span>
                                    @if ($req->status === 'validated')
                                        <div class="text-muted small">Present this at the hall</div>
                                    @endif
                                @elseif ($req->payment_method !== 'cash' && $req->payment_status === 'unpaid')
                                    <form method="POST" action="{{ route('requests.pay.retry', $req) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary">Pay now</button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @include('partials.status', ['status' => $req->status])
                                @if ($req->status === 'rejected' && $req->admin_remarks)
                                    <div class="text-muted small mt-1">{{ $req->admin_remarks }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

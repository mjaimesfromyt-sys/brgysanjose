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
                        <th>Type</th>
                        @if ($status === 'pending' || $status === 'rejected')
                            <th class="text-end">Decision</th>
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
                                    <div class="text-muted small">Purok: {{ $user->purok }}</div>
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
                                                      placeholder="Reason (shown to applicant, optional)"></textarea>
                                            <button class="btn btn-sm btn-danger w-100">Confirm rejection</button>
                                        </form>
                                    </div>
                                </td>
                            @endif

                            @if ($status === 'rejected')
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
@endsection

@extends('layouts.admin')

@section('title', 'Captain\'s Appointments & Schedule Management')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Captain's Appointments &amp; Schedule Management</h4>
            <p class="text-muted mb-0 small">Manage consultations, hearing requests, and Punong Barangay availability blocks.</p>
        </div>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#blockScheduleModal">
            @include('partials.icon', ['name' => 'calendar', 'size' => 16]) Block Out Time / Travel
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Upcoming Schedule Blocks -->
    @if($unavailabilities->isNotEmpty())
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-danger-subtle py-3 border-0">
                <h6 class="mb-0 fw-bold text-danger">
                    🔴 Active Schedule Blocks &amp; Official Travel (Kapitan Out-of-Office)
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Time Window</th>
                            <th>Official Reason / Location</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unavailabilities as $block)
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    {{ \Carbon\Carbon::parse($block->date)->format('M d, Y') }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($block->start_time)->format('h:i A') }} – 
                                    {{ \Carbon\Carbon::parse($block->end_time)->format('h:i A') }}
                                </td>
                                <td>{{ $block->reason }}</td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('admin.appointments.unavailability.destroy', $block->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this blocked schedule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove Block</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Appointments Review Table -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">Resident Appointment Requests</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.appointments.index', ['status' => 'all']) }}" class="btn {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}" class="btn {{ $status === 'pending' ? 'btn-primary' : 'btn-outline-secondary' }}">Pending</a>
                <a href="{{ route('admin.appointments.index', ['status' => 'approved']) }}" class="btn {{ $status === 'approved' ? 'btn-primary' : 'btn-outline-secondary' }}">Approved</a>
                <a href="{{ route('admin.appointments.index', ['status' => 'rejected']) }}" class="btn {{ $status === 'rejected' ? 'btn-primary' : 'btn-outline-secondary' }}">Rejected</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Resident</th>
                        <th>Date &amp; Time</th>
                        <th>Category</th>
                        <th style="width: 30%;">Reason / Narrative</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $item->user->name ?? 'Unknown Resident' }}</div>
                                <small class="text-muted">{{ $item->user->phone_number ?? $item->user->email ?? 'No contact info' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}</div>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }} – 
                                    {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $item->category }}
                                </span>
                            </td>
                            <td>
                                <div class="small p-2 rounded bg-light border text-break">
                                    {{ $item->reason }}
                                </div>
                                @if($item->admin_remarks)
                                    <div class="small text-danger mt-1">
                                        <strong>Rejection note:</strong> {{ $item->admin_remarks }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($item->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($item->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($item->status === 'pending')
                                    <form action="{{ route('admin.appointments.update-status', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-success px-3">Approve</button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-outline-danger px-3 ms-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal{{ $item->id }}">
                                        Reject
                                    </button>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.appointments.update-status', $item->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title fw-bold">Reject Appointment Request</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-2">Provide a clear note to the resident explaining the reason for rejection (e.g., schedule conflict, referral to Lupong Tagapamayapa, etc.):</p>
                                                        <textarea class="form-control" name="admin_remarks" rows="3" placeholder="Reason for rejection..." required></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-danger px-3">Confirm Rejection</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">No action needed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No appointment requests found for this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($appointments->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $appointments->appends(['status' => $status])->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Block Out Schedule / Lakaw ni Kapitan -->
<div class="modal fade" id="blockScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.appointments.unavailability.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title fw-bold mb-0">Block Out Time / Kapitan Lakaw</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="block_date" class="form-label fw-bold small">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="block_date" name="date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="block_start" class="form-label fw-bold small">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="block_start" name="start_time" value="08:00" required>
                        </div>
                        <div class="col-6">
                            <label for="block_end" class="form-label fw-bold small">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="block_end" name="end_time" value="17:00" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="block_reason" class="form-label fw-bold small">Purpose / Lakaw Details <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="block_reason" name="reason" placeholder="e.g. Official Business — Munisipyo Meeting" required>
                        <div class="form-text">This will be shown on the resident's schedule viewer to explain unavailability.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-4">Save Blocked Slot</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
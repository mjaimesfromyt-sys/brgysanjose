@extends('layouts.app')

@section('title', 'My Captain\'s Appointments')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">My Appointments with Punong Barangay</h4>
            <p class="text-muted mb-0 small">Track your requested mediation, consultation, and hearing schedules.</p>
        </div>
        <a href="{{ route('appointments.create') }}" class="btn btn-primary">
            @include('partials.icon', ['name' => 'calendar', 'size' => 16]) Book New Appointment
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Time Slot</th>
                        <th>Purpose / Category</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td class="ps-4 fw-semibold text-dark">
                                {{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }} – 
                                {{ \Carbon\Carbon::parse($appointment->end_time)->format('h:i A') }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $appointment->category }}
                                </span>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $appointment->reason }}">
                                    {{ $appointment->reason }}
                                </span>
                            </td>
                            <td>
                                @if($appointment->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($appointment->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="mb-2">📅</div>
                                You have not booked any appointments with Kapitan yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($appointments->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
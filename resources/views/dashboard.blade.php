@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="h3">Dashboard</h1>
        <p class="mb-1">Welcome, <strong>{{ auth()->user()->name }}</strong>.</p>
        <p class="mb-1">Role: <span class="badge bg-secondary">{{ ucfirst(auth()->user()->role) }}</span></p>
        <p>Account status:
            <span class="badge bg-{{ auth()->user()->isVerified() ? 'success' : 'warning' }}">
                {{ ucfirst(auth()->user()->status) }}
            </span>
        </p>
        <p class="text-muted">More features coming in the next modules: booking, events calendar, and the information site.</p>
    </div>
</div>
@endsection
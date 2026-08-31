@extends('layouts.admin')
@section('title', 'Facilities')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Facilities</h1>
    <p class="page-subtitle">Manage which venues residents can reserve.</p>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold">Existing facilities</h2>
            </div>

            @if ($facilities->isEmpty())
                <div class="empty">
                    <div class="empty__title">No facilities yet</div>
                    <p class="mb-0">Add your first bookable venue using the form.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Capacity</th>
                                <th>Fee</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($facilities as $facility)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $facility->name }}</div>
                                        <div class="text-muted small">{{ $facility->description }}</div>
                                    </td>
                                    <td>{{ $facility->capacity ?? '—' }}</td>
                                    <td>{{ is_null($facility->fee) ? '—' : '₱' . number_format($facility->fee, 2) }}</td>
                                    <td>
                                        <span class="pill {{ $facility->is_active ? 'pill--approved' : 'pill--neutral' }}">
                                            {{ $facility->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.facilities.toggle', $facility) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary">
                                                {{ $facility->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold">Add a facility</h2>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.facilities.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="2"
                                  class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="capacity" class="form-label">Capacity</label>
                        <input id="capacity" name="capacity" type="number" min="1"
                               value="{{ old('capacity') }}" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="fee" class="form-label">Fee (₱, leave blank if free)</label>
                        <input id="fee" name="fee" type="number" step="0.01" min="0"
                               value="{{ old('fee') }}" class="form-control">
                    </div>

                    <button class="btn btn-primary w-100">Add facility</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

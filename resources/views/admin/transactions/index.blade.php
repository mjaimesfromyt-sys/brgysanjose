@extends('layouts.admin')
@section('title', 'Transactions')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Transaction Types</h1>
    <p class="page-subtitle">The documents residents can request, their fees and their requirements.</p>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold">All transaction types</h2>
            </div>

            @if ($types->isEmpty())
                <div class="empty">
                    <div class="empty__title">No transaction types yet</div>
                    <p class="mb-0">Add the documents your barangay issues.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Residency</th>
                                <th>Fee</th>
                                <th>Reqs</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($types as $type)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $type->name }}</span>
                                        @unless ($type->is_active)
                                            <span class="pill pill--neutral ms-1">Inactive</span>
                                        @endunless
                                    </td>
                                    <td>
                                        @if ($type->requires_residency)
                                            <span class="pill pill--info">Residents</span>
                                        @else
                                            <span class="pill pill--neutral">All</span>
                                        @endif
                                    </td>
                                    <td>{{ is_null($type->fee) ? '—' : '₱' . number_format($type->fee, 2) }}</td>
                                    <td>{{ $type->requirements_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.transactions.edit', $type) }}"
                                           class="btn btn-sm btn-outline-secondary">Edit</a>
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
                <h2 class="h6 mb-0 fw-bold">Add transaction type</h2>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.transactions.store') }}">
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
                        <label for="fee" class="form-label">Fee (₱, leave blank if none)</label>
                        <input id="fee" name="fee" type="number" step="0.01" min="0"
                               value="{{ old('fee') }}" class="form-control">
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="requires_residency" id="requires_residency"
                               value="1" class="form-check-input" {{ old('requires_residency') ? 'checked' : '' }}>
                        <label for="requires_residency" class="form-check-label">
                            Requires residency (residents only)
                        </label>
                    </div>

                    <button class="btn btn-primary w-100">Add</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

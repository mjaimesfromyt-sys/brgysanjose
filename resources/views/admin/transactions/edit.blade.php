@extends('layouts.admin')
@section('title', 'Edit ' . $transactionType->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.transactions.index') }}" class="text-decoration-none small fw-semibold">
        &larr; All transaction types
    </a>
    <h1 class="page-title mt-2">{{ $transactionType->name }}</h1>
    <p class="page-subtitle">Update the details and the checklist residents must bring.</p>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold">Details</h2>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.transactions.update', $transactionType) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" name="name" value="{{ old('name', $transactionType->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="2"
                                  class="form-control">{{ old('description', $transactionType->description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="fee" class="form-label">Fee (₱)</label>
                        <input id="fee" name="fee" type="number" step="0.01" min="0"
                               value="{{ old('fee', $transactionType->fee) }}" class="form-control">
                    </div>

                    <div class="mb-2 form-check">
                        <input type="checkbox" name="requires_residency" id="requires_residency"
                               value="1" class="form-check-input"
                               {{ $transactionType->requires_residency ? 'checked' : '' }}>
                        <label for="requires_residency" class="form-check-label">Requires residency</label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" id="is_active"
                               value="1" class="form-check-input"
                               {{ $transactionType->is_active ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">Active (visible on info site)</label>
                    </div>

                    <button class="btn btn-primary w-100">Save changes</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold">Requirements</h2>
            </div>

            <div class="p-3">
                <ul class="list-unstyled m-0 mb-3">
                    @forelse ($transactionType->requirements as $req)
                        <li class="d-flex justify-content-between align-items-center gap-2 py-2 border-bottom">
                            <span>{{ $req->item }}</span>
                            <form method="POST"
                                  action="{{ route('admin.transactions.requirements.delete', [$transactionType, $req]) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </li>
                    @empty
                        <li class="text-muted py-2">No requirements yet.</li>
                    @endforelse
                </ul>

                <form method="POST"
                      action="{{ route('admin.transactions.requirements.add', $transactionType) }}"
                      class="d-flex gap-2">
                    @csrf
                    <input name="item" class="form-control @error('item') is-invalid @enderror"
                           placeholder="New requirement" required>
                    <button class="btn btn-primary">Add</button>
                </form>
                @error('item') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>
@endsection

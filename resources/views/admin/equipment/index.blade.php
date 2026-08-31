@extends('layouts.admin')
@section('title', 'Equipment Catalog')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Equipment</h1>
    <p class="page-subtitle">Manage chairs, tables, tents and other items residents can rent.</p>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-soft h-100">
            <div class="p-3 border-bottom">
                <h2 class="h6 mb-0 fw-bold">Catalog</h2>
            </div>

            @if ($equipment->isEmpty())
                <div class="empty">
                    <div class="empty__title">No equipment yet</div>
                    <p class="mb-0">Add your first rentable item using the form.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Fee</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($equipment as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->name }}</div>
                                        <div class="text-muted small">{{ $item->description }}</div>
                                    </td>
                                    <td>{{ is_null($item->fee) ? '—' : '₱' . number_format($item->fee, 2) }} <span class="text-muted small">each</span></td>
                                    <td>{{ number_format($item->total_stock) }}</td>
                                    <td>
                                        <span class="pill {{ $item->is_active ? 'pill--approved' : 'pill--neutral' }}">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.equipment.toggle', $item) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary">
                                                {{ $item->is_active ? 'Disable' : 'Enable' }}
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
                <h2 class="h6 mb-0 fw-bold">Add equipment</h2>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.equipment.store') }}">
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
                        <label for="fee" class="form-label">Fee (₱ per unit, leave blank if free)</label>
                        <input id="fee" name="fee" type="number" step="0.01" min="0"
                               value="{{ old('fee') }}" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="total_stock" class="form-label">Total stock</label>
                        <input id="total_stock" name="total_stock" type="number" min="0"
                               value="{{ old('total_stock', 0) }}"
                               class="form-control @error('total_stock') is-invalid @enderror" required>
                        @error('total_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button class="btn btn-primary w-100">Add equipment</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Equipment Catalog')

@section('content')
<div class="mb-4">
    <h1 class="page-title">Equipment</h1>
    <p class="page-subtitle">Manage chairs, tables, tents and other items residents can rent. Click a row to edit it.</p>
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
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Fee</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($equipment as $item)
                                <tr class="equip-row" style="cursor: pointer;"
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $item->name }}"
                                    data-description="{{ $item->description }}"
                                    data-fee="{{ $item->fee }}"
                                    data-stock="{{ $item->total_stock }}"
                                    data-active="{{ $item->is_active ? 1 : 0 }}"
                                    data-url="{{ route('admin.equipment.update', $item) }}">
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
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h2 class="h6 mb-0 fw-bold" id="formTitle">Add equipment</h2>
                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 d-none" id="cancelEdit">
                    Cancel
                </button>
            </div>

            <div class="p-3">
                <form method="POST" action="{{ route('admin.equipment.store') }}" id="equipForm">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="formMethod">

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

                    <div class="form-check mb-3 d-none" id="activeWrap">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Active <span class="text-muted small">(uncheck to hide from residents)</span>
                        </label>
                    </div>

                    <button class="btn btn-primary w-100" id="submitBtn">Add equipment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form       = document.getElementById('equipForm');
    const method     = document.getElementById('formMethod');
    const title      = document.getElementById('formTitle');
    const submitBtn  = document.getElementById('submitBtn');
    const cancelBtn  = document.getElementById('cancelEdit');
    const activeWrap = document.getElementById('activeWrap');
    const addAction  = form.getAttribute('action');

    function toAddMode() {
        form.setAttribute('action', addAction);
        method.value = 'POST';
        title.textContent = 'Add equipment';
        submitBtn.textContent = 'Add equipment';
        submitBtn.classList.remove('btn-success');
        submitBtn.classList.add('btn-primary');
        cancelBtn.classList.add('d-none');
        activeWrap.classList.add('d-none');

        form.querySelector('#name').value = '';
        form.querySelector('#description').value = '';
        form.querySelector('#fee').value = '';
        form.querySelector('#total_stock').value = 0;
        form.querySelector('#is_active').checked = true;

        document.querySelectorAll('.equip-row').forEach(r => r.classList.remove('table-active'));
    }

    document.querySelectorAll('.equip-row').forEach(function (row) {
        row.addEventListener('click', function () {
            form.setAttribute('action', this.dataset.url);
            method.value = 'PUT';
            title.textContent = 'Edit: ' + this.dataset.name;
            submitBtn.textContent = 'Save changes';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');
            cancelBtn.classList.remove('d-none');
            activeWrap.classList.remove('d-none');

            form.querySelector('#name').value = this.dataset.name;
            form.querySelector('#description').value = this.dataset.description || '';
            form.querySelector('#fee').value = this.dataset.fee || '';
            form.querySelector('#total_stock').value = this.dataset.stock;
            form.querySelector('#is_active').checked = this.dataset.active === '1';

            document.querySelectorAll('.equip-row').forEach(r => r.classList.remove('table-active'));
            this.classList.add('table-active');

            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });

    cancelBtn.addEventListener('click', toAddMode);
});
</script>
@endsection

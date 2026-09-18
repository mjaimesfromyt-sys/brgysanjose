@extends('layouts.admin')
@section('title', 'Staff & Sub-Admin Management')

@section('content')
<div class="container-fluid py-2">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Staff &amp; Sub-Admin Management</h1>
            <p class="text-muted small mb-0">Manage barangay hall staff accounts, designations, and access credentials.</p>
        </div>

        <!-- Add Staff Button -->
        <button type="button" class="btn btn-success d-inline-flex align-items-center gap-1.5 shadow-sm fw-bold px-3 py-2" 
                data-bs-toggle="modal" data-bs-target="#addStaffModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg>
            Add New Staff
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background-color: #e8f5e9; color: #166534;">
            <strong>✓ Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Staff Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white" style="border: 1px solid #e2e8f0;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Staff Member</th>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Position / Title</th>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Email (Login ID)</th>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Contact No</th>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Role</th>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Status</th>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffMembers as $staff)
                        <tr>
                            <td class="py-3 px-3">
                                <div class="d-flex align-items-center gap-2.5">
                                    @if ($staff->avatar_url)
                                        <img src="{{ $staff->avatar_url }}" alt="" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover; border: 1.5px solid #c8e6c9;">
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" style="width: 36px; height: 36px; background-color: #166534; font-size: 12px;">
                                            {{ $staff->initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 14px;">{{ $staff->name }}</div>
                                        <small class="text-muted">Account ID: #{{ $staff->id }}</small>
                                    </div>
                                </div>
                            </td>

                            <!-- 👉 Position / Designation -->
                            <td class="py-3 px-3">
                                <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold" style="font-size: 12px;">
                                    💼 {{ $staff->position ?? ($staff->role === 'super_admin' ? 'Punong Barangay / Super Admin' : 'Barangay Staff') }}
                                </span>
                            </td>

                            <td class="py-3 px-3 text-dark fw-medium" style="font-size: 13.5px;">
                                {{ $staff->email }}
                            </td>

                            <td class="py-3 px-3 text-dark" style="font-size: 13px;">
                                {{ $staff->contact_no ?? '—' }}
                            </td>

                            <td class="py-3 px-3">
                                @if($staff->role === 'super_admin')
                                    <span class="badge rounded-pill px-2.5 py-1 text-white" style="background-color: #7e22ce; font-size: 11px;">Super Admin</span>
                                @else
                                    <span class="badge rounded-pill px-2.5 py-1 text-dark border" style="background-color: #f1f5f9; font-size: 11px;">Sub-Admin / Staff</span>
                                @endif
                            </td>

                            <td class="py-3 px-3">
                                @if($staff->status === 'active')
                                    <span class="badge rounded-pill px-2.5 py-1" style="background-color: #e8f5e9; color: #166534; font-size: 11px;">Active</span>
                                @else
                                    <span class="badge rounded-pill px-2.5 py-1" style="background-color: #fee2e2; color: #991b1b; font-size: 11px;">Inactive</span>
                                @endif
                            </td>

                            <td class="py-3 px-3 text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary me-1 fw-semibold" style="font-size: 12px; padding: 4px 10px;" 
                                        data-bs-toggle="modal" data-bs-target="#editStaffModal{{ $staff->id }}">
                                    Edit Credentials
                                </button>

                                @if($staff->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.staff.toggle', $staff) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to change this staff member\'s status?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $staff->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" style="font-size: 12px; padding: 4px 8px;">
                                            {{ $staff->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $staffMembers->links() }}
    </div>

</div>

<!-- ================= MODAL: ADD NEW STAFF ================= -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1070;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold" id="addStaffModalLabel" style="font-size: 15.5px;">Add New Barangay Staff</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.staff.store') }}">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">First Name</label>
                            <input type="text" name="first_name" class="form-control" required placeholder="e.g. Juan">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required placeholder="e.g. Dela Cruz">
                        </div>
                    </div>

                    <!-- 👉 Position Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Barangay Position / Designation</label>
                        <select name="position" class="form-select" required>
                            <option value="Barangay Secretary">Barangay Secretary</option>
                            <option value="Barangay Treasurer">Barangay Treasurer</option>
                            <option value="Barangay Records Officer">Barangay Records Officer</option>
                            <option value="Barangay Desk Clerk">Barangay Desk Clerk</option>
                            <option value="Barangay Administrator">Barangay Administrator</option>
                            <option value="Barangay Staff" selected>Barangay Staff</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Email (Login ID)</label>
                        <input type="email" name="email" class="form-control" required placeholder="e.g. staff1@brgysanjose.site">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Contact No</label>
                        <input type="text" name="contact_no" class="form-control" placeholder="e.g. 09123456789">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Role Access</label>
                        <select name="role" class="form-select">
                            <option value="admin" selected>Sub-Admin / Staff (Operations only)</option>
                            <option value="super_admin">Super Admin (Full system access)</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Minimum 8 characters">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success px-3 fw-bold">Create Staff Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODALS: EDIT CREDENTIALS FOR EACH STAFF ================= -->
@foreach($staffMembers as $staff)
    <div class="modal fade" id="editStaffModal{{ $staff->id }}" tabindex="-1" aria-labelledby="editStaffModalLabel{{ $staff->id }}" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered" style="z-index: 1070;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light py-3 border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="editStaffModalLabel{{ $staff->id }}" style="font-size: 15px;">
                        Edit Credentials — {{ $staff->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.staff.update', $staff) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4 bg-white">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ $staff->first_name }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ $staff->last_name }}" required>
                            </div>
                        </div>

                        <!-- 👉 Position Edit -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Barangay Position / Designation</label>
                            <input type="text" name="position" class="form-control" 
                                   value="{{ $staff->position ?? ($staff->role === 'super_admin' ? 'Punong Barangay' : 'Barangay Secretary') }}" 
                                   placeholder="e.g. Barangay Secretary, Barangay Treasurer" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Email (Login ID)</label>
                            <input type="email" name="email" class="form-control" value="{{ $staff->email }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Contact No</label>
                            <input type="text" name="contact_no" class="form-control" value="{{ $staff->contact_no }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-success">New Password (Ilisi kon mag-alili og staff)</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank if keeping current password">
                            <small class="text-muted" style="font-size: 11px;">Leave blank to keep existing password. Minimum 8 characters if changing.</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-dark">Account Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ $staff->status === 'active' ? 'selected' : '' }}>Active (Can log in)</option>
                                <option value="inactive" {{ $staff->status === 'inactive' ? 'selected' : '' }}>Inactive (Blocked)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success px-3 fw-bold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

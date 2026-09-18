@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@section('content')
<style>
    /* Executive Admin Dashboard Styling */
    .admin-hero-banner {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 24px 28px;
        box-shadow: 0 4px 20px -4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .admin-hero-banner::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #166534, #22c55e, #3b82f6);
    }

    /* KPI Metric Cards */
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px 18px;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        position: relative;
        overflow: hidden;
    }
    .kpi-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
    }
    .kpi-residents::before { background: linear-gradient(90deg, #16a34a, #4ade80); }
    .kpi-requests::before  { background: linear-gradient(90deg, #0284c7, #38bdf8); }
    .kpi-bookings::before  { background: linear-gradient(90deg, #9333ea, #c084fc); }
    .kpi-rentals::before   { background: linear-gradient(90deg, #d97706, #fbbf24); }
    .kpi-inuse::before     { background: linear-gradient(90deg, #e11d48, #fb7185); }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -4px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }

    .kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 12px;
    }
    .icon-bg-residents { background-color: #f0fdf4; color: #16a34a; }
    .icon-bg-requests  { background-color: #f0f9ff; color: #0284c7; }
    .icon-bg-bookings  { background-color: #faf5ff; color: #9333ea; }
    .icon-bg-rentals   { background-color: #fffbeb; color: #d97706; }
    .icon-bg-inuse     { background-color: #fff1f2; color: #e11d48; }

    .kpi-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .kpi-value {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        margin-bottom: 6px;
    }
    .kpi-meta {
        font-size: 11.5px;
        color: #64748b;
    }

    /* Analytics Container */
    .admin-analytics-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.02);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Segmented Progress Bar */
    .status-progress-bar {
        height: 12px;
        border-radius: 50px;
        overflow: hidden;
        display: flex;
        background-color: #f1f5f9;
        margin-bottom: 16px;
    }
</style>

<!-- ================= 1. EXECUTIVE HERO BANNER ================= -->
<div class="admin-hero-banner">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h4 fw-bold text-dark mb-0">Control Panel Dashboard</h1>
                @if(auth()->user()->isSuperAdmin())
                    <span class="badge rounded-pill bg-purple-subtle text-purple border border-purple-subtle px-2.5 py-1 fw-bold" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;">
                        ★ Super Admin Console
                    </span>
                @else
                    <span class="badge rounded-pill bg-light text-dark border px-2.5 py-1 fw-semibold">
                        Sub-Admin Console
                    </span>
                @endif
            </div>
            <p class="text-muted small mb-0">Barangay San Jose, Talibon, Bohol &bull; Real-time Operational &amp; Transaction Overview</p>
        </div>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <div class="text-end d-none d-md-block">
                <div class="text-muted small fw-bold text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px;">Current Date</div>
                <div class="fw-bold text-dark" style="font-size: 14px;">{{ now()->format('l, F j, Y') }}</div>
            </div>
            <img src="{{ asset('images/barangay-seal.png') }}" alt="Barangay Seal" style="width: 58px; height: 58px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(22, 101, 52, 0.15));">
        </div>
    </div>
</div>

<!-- ================= 2. FIVE COLOR-CODED KPI METRIC CARDS ================= -->
<div class="row g-3 mb-4">
    <!-- 1. Active Residents -->
    <div class="col-12 col-sm-6 col-xl">
        <a href="{{ route('admin.residents.index') }}" class="kpi-card kpi-residents">
            <div>
                <div class="kpi-icon icon-bg-residents">👥</div>
                <div class="kpi-label">Active Residents</div>
                <div class="kpi-value">{{ $residentsActive }}</div>
            </div>
            <div class="kpi-meta">
                @if ($residentsPending > 0)
                    <span class="badge bg-danger rounded-pill px-2">{{ $residentsPending }} awaiting</span>
                @else
                    <span class="text-muted">0 awaiting verification</span>
                @endif
            </div>
        </a>
    </div>

    <!-- 2. Document Requests -->
    <div class="col-12 col-sm-6 col-xl">
        <a href="{{ route('admin.requests.index') }}" class="kpi-card kpi-requests">
            <div>
                <div class="kpi-icon icon-bg-requests">📄</div>
                <div class="kpi-label">Document Requests</div>
                <div class="kpi-value text-primary">{{ $requestsPending }}</div>
            </div>
            <div class="kpi-meta">
                @if ($requestsPending > 0)
                    <span class="badge bg-warning text-dark rounded-pill px-2">pending review</span>
                @else
                    <span class="text-muted">All caught up</span>
                @endif
            </div>
        </a>
    </div>

    <!-- 3. Facility Bookings -->
    <div class="col-12 col-sm-6 col-xl">
        <a href="{{ route('admin.bookings.index') }}" class="kpi-card kpi-bookings">
            <div>
                <div class="kpi-icon icon-bg-bookings">🏀</div>
                <div class="kpi-label">Booking Requests</div>
                <div class="kpi-value" style="color: #9333ea;">{{ $bookingsPending }}</div>
            </div>
            <div class="kpi-meta">
                @if ($bookingsPending > 0)
                    <span class="badge bg-warning text-dark rounded-pill px-2">pending approval</span>
                @else
                    <span class="text-muted">No pending bookings</span>
                @endif
            </div>
        </a>
    </div>

    <!-- 4. Equipment Rentals -->
    <div class="col-12 col-sm-6 col-xl">
        <a href="{{ route('admin.rentals.index') }}" class="kpi-card kpi-rentals">
            <div>
                <div class="kpi-icon icon-bg-rentals">📦</div>
                <div class="kpi-label">Equipment Rentals</div>
                <div class="kpi-value text-warning-emphasis">{{ $rentalsPending }}</div>
            </div>
            <div class="kpi-meta">
                @if ($rentalsPending > 0)
                    <span class="badge bg-warning text-dark rounded-pill px-2">pending approval</span>
                @else
                    <span class="text-muted">No pending rentals</span>
                @endif
            </div>
        </a>
    </div>

    <!-- 5. In Use Today -->
    <div class="col-12 col-sm-6 col-xl">
        <a href="{{ route('admin.bookings.index') }}" class="kpi-card kpi-inuse">
            <div>
                <div class="kpi-icon icon-bg-inuse">🏛️</div>
                <div class="kpi-label">In Use Today</div>
                <div class="kpi-value text-danger">{{ $bookingsToday }}</div>
            </div>
            <div class="kpi-meta text-muted">
                approved bookings active
            </div>
        </a>
    </div>
</div>

<!-- ================= 3. ANALYTICS & INSIGHTS CARDS ================= -->
<div class="row g-3 mb-4">
    <!-- Requests By Status -->
    <div class="col-lg-4">
        <div class="admin-analytics-card">
            <div>
                <div class="d-flex align-items-center justify-content-between pb-2 border-bottom mb-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5" style="font-size: 14.5px;">
                        <span>📋</span> Requests by Status
                    </h6>
                    <a href="{{ route('admin.requests.index') }}" class="small fw-semibold text-success text-decoration-none">View all &rarr;</a>
                </div>

                @php
                    $totalReqs = array_sum(array_column($requestsByStatus, 'value'));
                    $colors = ['Pending' => '#f59e0b', 'Validated' => '#3b82f6', 'Claimed' => '#10b981', 'Rejected' => '#ef4444'];
                @endphp

                <!-- Segmented Progress Bar -->
                <div class="status-progress-bar">
                    @foreach ($requestsByStatus as $stat)
                        @if ($totalReqs > 0 && $stat['value'] > 0)
                            <div style="width: {{ ($stat['value'] / $totalReqs) * 100 }}%; background-color: {{ $colors[$stat['label']] ?? '#94a3b8' }};" title="{{ $stat['label'] }}: {{ $stat['value'] }}"></div>
                        @endif
                    @endforeach
                </div>

                <!-- Legend List -->
                <div class="d-flex flex-column gap-2">
                    @foreach ($requestsByStatus as $stat)
                        <div class="d-flex justify-content-between align-items-center small">
                            <span class="d-flex align-items-center gap-2">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: {{ $colors[$stat['label']] ?? '#94a3b8' }};"></span>
                                <span class="fw-semibold text-dark">{{ $stat['label'] }}</span>
                            </span>
                            <span class="badge bg-light text-dark border px-2.5 py-1 fw-bold">{{ $stat['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-3 pt-2 border-top text-muted small" style="font-size: 11px;">
                Total Document Requests: <strong>{{ $totalReqs }}</strong>
            </div>
        </div>
    </div>

    <!-- Approved Bookings Per Facility -->
    <div class="col-lg-4">
        <div class="admin-analytics-card">
            <div>
                <div class="d-flex align-items-center justify-content-between pb-2 border-bottom mb-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5" style="font-size: 14.5px;">
                        <span>🏟️</span> Bookings per Facility
                    </h6>
                    <a href="{{ route('admin.bookings.index') }}" class="small fw-semibold text-success text-decoration-none">Manage &rarr;</a>
                </div>

                <div class="d-flex flex-column gap-2">
                    @foreach ($bookingsByFacility as $fac)
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2" style="background-color: #f8fafc; border: 1px solid #f1f5f9;">
                            <span class="small fw-semibold text-dark text-truncate" style="max-width: 200px;">{{ $fac['label'] }}</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold">{{ $fac['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-3 pt-2 border-top text-muted small" style="font-size: 11px;">
                Shows total approved reservations across public facilities.
            </div>
        </div>
    </div>

    <!-- Requests Last 6 Months (Trend) -->
    <div class="col-lg-4">
        <div class="admin-analytics-card">
            <div>
                <div class="d-flex align-items-center justify-content-between pb-2 border-bottom mb-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5" style="font-size: 14.5px;">
                        <span>📈</span> Requests (Last 6 Months)
                    </h6>
                    <a href="{{ route('reports.requests') }}" class="small fw-semibold text-success text-decoration-none">Reports &rarr;</a>
                </div>

                @php
                    $maxMonth = max(array_column($requestsByMonth, 'value')) ?: 1;
                @endphp

                <!-- CSS Bar Chart -->
                <div class="d-flex align-items-end justify-content-between pt-4 pb-2" style="height: 140px;">
                    @foreach ($requestsByMonth as $m)
                        @php $pct = ($m['value'] / $maxMonth) * 100; @endphp
                        <div class="d-flex flex-column align-items-center gap-1" style="flex: 1;">
                            <span class="small fw-bold text-muted" style="font-size: 10.5px;">{{ $m['value'] }}</span>
                            <div style="width: 22px; height: {{ max($pct, 8) }}%; background: linear-gradient(180deg, #16a34a, #22c55e); border-radius: 4px;" title="{{ $m['full'] }}: {{ $m['value'] }}"></div>
                            <span class="text-muted fw-semibold" style="font-size: 10px;">{{ $m['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-3 pt-2 border-top text-muted small" style="font-size: 11px;">
                Monthly trend of incoming clearance and certification requests.
            </div>
        </div>
    </div>
</div>

<!-- ================= 4. RECENT ACTIVITY TABLES ================= -->
<div class="row g-3">
    <!-- Recent Document Requests -->
    <div class="col-lg-6">
        <div class="admin-analytics-card">
            <div class="d-flex align-items-center justify-content-between pb-2 border-bottom mb-3">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 14.5px;">Recent Document Requests</h6>
                <a href="{{ route('admin.requests.index') }}" class="small fw-semibold text-success text-decoration-none">View queue &rarr;</a>
            </div>

            @if ($recentRequests->isEmpty())
                <div class="py-4 text-center text-muted small">No recent requests filed.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <tbody>
                            @foreach ($recentRequests as $req)
                                <tr>
                                    <td class="py-2.5 ps-0">
                                        <div class="fw-bold text-dark">{{ $req->user->name }}</div>
                                        <small class="text-muted">{{ $req->transactionType->name ?? 'Document' }}</small>
                                    </td>
                                    <td class="py-2.5 text-end pe-0">
                                        <span class="badge rounded-pill px-2.5 py-1" style="font-size: 10.5px; background-color: {{ $req->status === 'claimed' ? '#dcfce7; color: #166534;' : ($req->status === 'validated' ? '#e0f2fe; color: #0284c7;' : '#fef3c7; color: #d97706;') }}">
                                            {{ ucfirst($req->status) }}
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

    <!-- Upcoming Bookings -->
    <div class="col-lg-6">
        <div class="admin-analytics-card">
            <div class="d-flex align-items-center justify-content-between pb-2 border-bottom mb-3">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 14.5px;">Upcoming Facility Reservations</h6>
                <a href="{{ route('admin.bookings.index') }}" class="small fw-semibold text-success text-decoration-none">View all &rarr;</a>
            </div>

            @if ($upcomingBookings->isEmpty())
                <div class="py-4 text-center text-muted small">No upcoming facility reservations scheduled.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <tbody>
                            @foreach ($upcomingBookings as $bk)
                                <tr>
                                    <td class="py-2.5 ps-0">
                                        <div class="fw-bold text-dark">{{ $bk->facility->name }}</div>
                                        <small class="text-muted">{{ $bk->user->name }} &bull; {{ $bk->start_date->format('M d, Y') }}</small>
                                    </td>
                                    <td class="py-2.5 text-end pe-0">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1" style="font-size: 10.5px;">
                                            Approved
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
</div>
@endsection
@extends('layouts.admin')
@section('title', 'Admin Activity Log')

@section('content')
<div class="container py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: #0f172a;">Admin Activity Log</h1>
            <p class="text-muted small mb-0">Track and review administrative actions across all barangay modules.</p>
        </div>

        @if(request('filter') === 'today' || request('module') || request('search'))
            <a href="{{ route('admin.activity-log.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5 rounded-pill px-3 shadow-sm">
                <span>&times;</span> Clear All Filters
            </a>
        @endif
    </div>
    
    <style>
        .activity-card-link {
            text-decoration: none;
            display: block;
        }

        .activity-card {
            border-radius: 16px;
            padding: 22px 20px;
            color: white;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 140px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .activity-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 12px 25px rgba(0,0,0,0.18);
        }

        /* The Sauce: Glowing Active States */
        .activity-card.is-active {
            border-color: #ffffff !important;
            transform: translateY(-4px) scale(1.02);
        }
        .card-total.is-active {
            box-shadow: 0 0 25px rgba(37, 99, 235, 0.65), 0 8px 16px rgba(0,0,0,0.15);
        }
        .card-today.is-active {
            box-shadow: 0 0 25px rgba(22, 163, 74, 0.65), 0 8px 16px rgba(0,0,0,0.15);
        }
        .card-module.is-active {
            box-shadow: 0 0 25px rgba(234, 88, 12, 0.65), 0 8px 16px rgba(0,0,0,0.15);
        }

        .active-pill-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.28);
            backdrop-filter: blur(4px);
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .activity-card h5 {
            font-size: 14.5px;
            font-weight: 600;
            opacity: .92;
            margin-bottom: 0;
        }

        .activity-card h2 {
            font-size: 38px;
            font-weight: 800;
            margin-top: 10px;
            margin-bottom: 0;
            letter-spacing: -0.5px;
        }

        .card-total {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
        }

        .card-today {
            background: linear-gradient(135deg, #15803d, #22c55e);
        }

        .card-admin {
            background: linear-gradient(135deg, #7e22ce, #a855f7);
        }

        .card-module {
            background: linear-gradient(135deg, #c2410c, #f97316);
        }

        .activity-icon {
            font-size: 30px;
            float: right;
            opacity: .85;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
        }

        .filter-banner {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 13.5px;
            color: #166534;
        }
    </style>

    <!-- ================= 4 INTERACTIVE DASHBOARD CARDS ================= -->
    <div class="row g-3 mb-4">

        <!-- 1. Total Activities Card (Click to View All) -->
        <div class="col-md-3">
            <a href="{{ route('admin.activity-log.index', ['filter' => 'all']) }}" class="activity-card-link" title="Click to view all activities">
                <div class="activity-card card-total {{ (!request('filter') || request('filter') === 'all') && !request('module') ? 'is-active' : '' }}">
                    <span class="activity-icon">📋</span>
                    <h5>Total Activities</h5>
                    <h2>{{ $totalActivities }}</h2>
                    @if ((!request('filter') || request('filter') === 'all') && !request('module'))
                        <span class="active-pill-badge">● Viewing All</span>
                    @endif
                </div>
            </a>
        </div>

        <!-- 2. Today Card (Click to Filter Today Only) -->
        <div class="col-md-3">
            <a href="{{ route('admin.activity-log.index', ['filter' => 'today']) }}" class="activity-card-link" title="Click to view only today's activities">
                <div class="activity-card card-today {{ request('filter') === 'today' ? 'is-active' : '' }}">
                    <span class="activity-icon">📅</span>
                    <h5>Today</h5>
                    <h2>{{ $todayActivities }}</h2>
                    @if (request('filter') === 'today')
                        <span class="active-pill-badge">● Filter Active</span>
                    @endif
                </div>
            </a>
        </div>

        <!-- 3. Active Admins Card (Clickable) -->
        <div class="col-md-3">
            <div class="activity-card card-admin" 
                 >
                <span class="activity-icon">👤</span>
                <h5>Active Admins</h5>
                <h2>{{ $activeAdmins }}</h2>
            </div>
        </div>

                <!-- 4. Top Module Card (Click to Filter by Top Module) -->
        <div class="col-md-3">
            @if ($topModule)
                <a href="{{ route('admin.activity-log.index', ['module' => $topModule->log_name]) }}" class="activity-card-link" title="Click to filter by {{ ucfirst(str_replace('_',' ', $topModule->log_name)) }}">
                    <div class="activity-card card-module {{ request('module') === $topModule->log_name ? 'is-active' : '' }}">
                        <span class="activity-icon">📊</span>
                        <h5>Top Module</h5>
                        <h2 style="font-size: 22px; text-transform: capitalize; margin-top: 14px;">
                            {{ str_replace('_',' ', $topModule->log_name) }}
                        </h2>
                        @if (request('module') === $topModule->log_name)
                            <span class="active-pill-badge">● Filter Active</span>
                        @endif
                    </div>
                </a>
            @else
                <div class="activity-card card-module">
                    <span class="activity-icon">📊</span>
                    <h5>Top Module</h5>
                    <h2 style="font-size: 22px;">None</h2>
                </div>
            @endif
        </div>
    </div>

    <!-- Active Filter Feedback Banner -->
    @if(request('filter') === 'today')
        <div class="filter-banner mb-3 d-flex justify-content-between align-items-center">
            <span>📅 <strong>Filter Applied:</strong> Showing only activities recorded <strong>Today ({{ $todayActivities }} records)</strong>.</span>
            <a href="{{ route('admin.activity-log.index') }}" class="text-success fw-bold text-decoration-none small">&larr; Show All Activities</a>
        </div>
    @endif

    <!-- Search & Module Filter Form -->
    <div class="card border-0 shadow-sm p-3 mb-4 rounded-3" style="border: 1px solid #e2e8f0;">
        <form id="activityFilterForm" method="GET" action="{{ route('admin.activity-log.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="filter" value="{{ request('filter', 'all') }}">

            <div class="col-md-3 col-12">
                <select name="module" class="form-select form-select-sm">
                    <option value="">All Modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $module)) }}
                        </option>
                    @endforeach
                </select>
            </div>

                        <div class="col-md-5 col-12">
                <input type="text" id="searchInput" name="search" class="form-control form-control-sm" placeholder="Search activity description or user..." value="{{ request('search') }}" autofocus>
            </div>

            @if(request('module') || request('search') || request('filter') === 'today')
                <div class="col-md-2 col-6">
                    <a href="{{ route('admin.activity-log.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        Reset
                    </a>
                </div>
            @endif

            <div class="col-md-2 col-6 ms-auto">
                <button type="button" onclick="printActivityLogs()" class="btn btn-sm btn-outline-dark w-100 fw-semibold d-flex align-items-center justify-content-center gap-1">
                    <span>🖨️</span> Print Logs
                </button>
            </div>
        </form>
    </div>

    <!-- Activity Log Table -->
    <div class="table-wrap card border-0 shadow-sm rounded-3 overflow-hidden" style="border: 1px solid #e2e8f0;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Date &amp; Time</th>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Admin</th>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Module</th>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Action</th>
                        <th class="py-3 px-3 small text-muted text-uppercase fw-bold">Record / Ref</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="py-3 px-3 text-nowrap" style="font-size: 13px;">
                                <span class="fw-semibold text-dark">{{ $log->created_at->format('M d, Y') }}</span>
                                <span class="text-muted small d-block">{{ $log->created_at->format('h:i A') }}</span>
                            </td>

                            <td class="py-3 px-3">
                                <span class="fw-bold text-dark" style="font-size: 13px;">{{ $log->causer?->name ?? 'System' }}</span>
                                <span class="text-muted small d-block">{{ ucfirst($log->causer?->role ?? 'Admin') }}</span>
                            </td>

                            <td class="py-3 px-3">
                                <span class="badge rounded-pill px-2.5 py-1 text-dark border" style="background-color: #f1f5f9; font-size: 11px;">
                                    {{ ucfirst(str_replace('_',' ', $log->log_name)) }}
                                </span>
                            </td>

                            <td class="py-3 px-3 text-dark" style="font-size: 13px;">
                                {{ $log->description }}
                            </td>

                            <td class="py-3 px-3 text-nowrap">
                                <span class="badge font-monospace text-primary border" style="background-color: #eff6ff; font-size: 11.5px;">
                                    {{ $log->properties['control_number'] ?? ($log->subject_id ? '#' . $log->subject_id : '#' . $log->id) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div style="font-size: 28px; margin-bottom: 8px;">🔍</div>
                                <div class="fw-semibold">No activity logs found</div>
                                <small>Try adjusting your search criteria or filter.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $logs->links() }}
    </div>

</div>
@endsection
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("activityFilterForm");
    if (!form) return;

    const moduleSelect = form.querySelector("select[name='module']");
    const searchInput = document.getElementById("searchInput");

    if (moduleSelect) {
        moduleSelect.addEventListener("change", function () {
            form.submit();
        });
    }

    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                form.submit();
            }, 500);
        });

        // I-set ang cursor focus sa pinaka tumoy sa gi-type
        if (searchInput.value) {
            const val = searchInput.value;
            searchInput.value = "";
            searchInput.value = val;
        }
    }
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("activityFilterForm");
    if (!form) return;

    const moduleSelect = form.querySelector("select[name='module']");
    const searchInput = document.getElementById("searchInput");

    if (moduleSelect) {
        moduleSelect.addEventListener("change", function () {
            form.submit();
        });
    }

    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                form.submit();
            }, 500);
        });

        if (searchInput.value) {
            const val = searchInput.value;
            searchInput.value = "";
            searchInput.value = val;
        }
    }
});
</script>
<script>
function printActivityLogs() {
    const tableWrap = document.querySelector(".table-wrap");
    if (!tableWrap) return;

    const printWin = window.open("", "_blank", "width=1100,height=700");
    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admin Activity Log</title>
            <style>
                @page { size: landscape; margin: 12mm; }
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 10px;
                    color: #1e293b;
                }
                .header {
                    margin-bottom: 16px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 2px solid #334155;
                    padding-bottom: 8px;
                }
                .header h2 { margin: 0; font-size: 20px; font-weight: bold; }
                .header span { font-size: 12px; color: #64748b; }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 12px;
                }
                th, td {
                    border: 1px solid #cbd5e1;
                    padding: 8px 10px;
                    text-align: left;
                    vertical-align: middle;
                }
                th {
                    background-color: #f1f5f9 !important;
                    font-weight: 600;
                    text-transform: uppercase;
                    font-size: 11px;
                }
                .badge, .pill {
                    border: 1px solid #94a3b8;
                    padding: 2px 6px;
                    border-radius: 4px;
                    font-size: 10px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Admin Activity Log</h2>
                <span>Printed: ${new Date().toLocaleString()}</span>
            </div>
            ${tableWrap.innerHTML}
        </body>
        </html>
    `);

    printWin.document.close();
    printWin.focus();
    setTimeout(() => {
        printWin.print();
        printWin.close();
    }, 300);
}
</script>
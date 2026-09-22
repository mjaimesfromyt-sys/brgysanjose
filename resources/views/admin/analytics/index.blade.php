@extends('layouts.admin')
@section('title', 'Analytics')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="page-title">Analytics</h1>
        <p class="page-subtitle">Income and activity across bookings, documents, and rentals.</p>
    </div>
    <div class="btn-group" role="group" aria-label="Date range">
        @foreach ([7 => '7 days', 30 => '30 days', 90 => '90 days'] as $d => $label)
            <a href="{{ route('admin.analytics.index', ['days' => $d]) }}"
               class="btn btn-sm {{ $days === $d ? 'btn-success' : 'btn-outline-success' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

{{-- KPI CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card-soft h-100 p-3">
            <div class="text-muted small">Collected ({{ $days }} days)</div>
            <div class="fs-4 fw-bold text-success">₱{{ number_format($kpi['collected_range'], 2) }}</div>
            <div class="text-muted small">{{ $kpi['transactions'] }} paid transactions</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card-soft h-100 p-3">
            <div class="text-muted small">Average per transaction</div>
            <div class="fs-4 fw-bold">₱{{ number_format($kpi['avg_ticket'], 2) }}</div>
            <div class="text-muted small">across all services</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card-soft h-100 p-3">
            <div class="text-muted small">Outstanding (unpaid)</div>
            <div class="fs-4 fw-bold text-warning">₱{{ number_format($kpi['outstanding'], 2) }}</div>
            <div class="text-muted small">pending &amp; approved requests</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card-soft h-100 p-3">
            <div class="text-muted small">Document requests</div>
            <div class="fs-4 fw-bold">{{ $volume ? array_sum(array_column($volume, 'value')) : 0 }}</div>
            <div class="text-muted small">submitted in {{ $days }} days</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8">
        <div class="card-soft p-3 h-100">
            <h6 class="fw-bold mb-3">💰 Daily income collected (₱)</h6>
            <canvas id="incomeChart" height="110"></canvas>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card-soft p-3 h-100">
            <h6 class="fw-bold mb-3">Income by service</h6>
            <canvas id="serviceChart" height="220"></canvas>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card-soft p-3 h-100">
            <h6 class="fw-bold mb-3">📄 Top document types by revenue</h6>
            @if ($topDocs->isEmpty())
                <p class="text-muted small mb-0">No paid document requests in this period.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Document</th><th class="text-end">Count</th><th class="text-end">Revenue</th></tr></thead>
                    <tbody>
                        @foreach ($topDocs as $doc)
                            <tr>
                                <td>{{ $doc->label }}</td>
                                <td class="text-end">{{ $doc->cnt }}</td>
                                <td class="text-end fw-semibold">₱{{ number_format($doc->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card-soft p-3 h-100">
            <h6 class="fw-bold mb-3">🏟️ Busiest facilities (approved bookings)</h6>
            @if ($topFacilities->isEmpty())
                <p class="text-muted small mb-0">No approved bookings in this period.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Facility</th><th class="text-end">Bookings</th></tr></thead>
                    <tbody>
                        @foreach ($topFacilities as $fac)
                            <tr>
                                <td>{{ $fac->label }}</td>
                                <td class="text-end fw-semibold">{{ $fac->cnt }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const income = @json($income);
    const perService = @json($perService);

    const labels = income.bookings.map(function (p) {
        const d = new Date(p.date + 'T00:00:00');
        return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
    });

    const stack = (ctx, key) => ({
        label: key === 'documents' ? 'Documents' : (key === 'bookings' ? 'Bookings' : 'Rentals'),
        data: income[key].map(p => p.value),
        backgroundColor: key === 'documents' ? 'rgba(2,132,199,.75)' : (key === 'bookings' ? 'rgba(22,163,74,.75)' : 'rgba(217,119,6,.75)'),
        stack: 'income',
        borderRadius: 3,
    });

    new Chart(document.getElementById('incomeChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [stack(null, 'bookings'), stack(null, 'documents'), stack(null, 'rentals')],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: { maxTicksLimit: 10 } },
                y: { stacked: true, beginAtZero: true, ticks: { callback: v => '₱' + v } },
            },
        },
    });

    new Chart(document.getElementById('serviceChart'), {
        type: 'doughnut',
        data: {
            labels: perService.map(s => s.label),
            datasets: [{
                data: perService.map(s => s.value),
                backgroundColor: ['rgba(22,163,74,.8)', 'rgba(2,132,199,.8)', 'rgba(217,119,6,.8)'],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            cutout: '62%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } },
                tooltip: { callbacks: { label: c => ' ' + c.label + ': ₱' + c.parsed.toLocaleString(undefined, { minimumFractionDigits: 2 }) } },
            },
        },
    });
})();
</script>
@endsection

@extends('layout.app')

@push('styles')
<style>
    .chart-container {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
        margin-bottom: 28px;
    }
    .chart-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 16px;
        text-align: center;
    }
    .chart-wrapper {
        position: relative;
        height: 380px;
        margin: 16px 0;
    }
    .chart-wrapper canvas { height: 100% !important; width: 100% !important; }

    /* Stat cards */
    .stat-card {
        background: linear-gradient(135deg, #a02626, #c73333);
        color: #fff;
        padding: 18px 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(160,38,38,.3);
        margin-bottom: 16px;
    }
    .stat-number { font-size: 30px; font-weight: 700; margin-bottom: 4px; }
    .stat-label  { font-size: 13px; opacity: .9; }

    /* Filter bar */
    .vr-filter-bar {
        background: #fff;
        border-radius: 8px;
        padding: 14px 18px;
        box-shadow: 0 1px 6px rgba(0,0,0,.08);
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .vr-filter-bar .vr-label { font-weight: 600; font-size: .85rem; color: #555; white-space: nowrap; }

    /* Active toggle */
    .btn-filter-active {
        background: #a02626 !important;
        border-color: #a02626 !important;
        color: #fff !important;
    }

    @media print {
        .no-print { display: none !important; }
        .chart-container { page-break-inside: avoid; }
    }
</style>
@endpush

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="mb-0" style="font-size:1.4rem;">
                        <i class="fas fa-chart-pie mr-2" style="color:#a02626;"></i>
                        {{ $header_title }}
                    </h1>
                </div>
                <div class="col-sm-6 text-right no-print">
                    <a href="{{ url('admin/exams/examiner-confirmation') }}" class="btn btn-sm btn-secondary mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                    <button onclick="window.print()" class="btn btn-sm btn-success">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="col-md-12">@include('_message')</div>

    <section class="content">
        <div class="container-fluid">

            {{-- ── Filter Bar ───────────────────────────────────────────────────── --}}
            <div class="vr-filter-bar no-print">
                {{-- Year select --}}
                <span class="vr-label"><i class="fas fa-calendar-alt mr-1"></i> Year:</span>
                <form method="GET" action="{{ url('admin/exams/visual_report') }}"
                      class="d-flex align-items-center" style="gap:6px;" id="yearForm">
                    <input type="hidden" name="filter" id="hid-filter" value="{{ $filterMode }}">
                    <select name="year_id" class="form-control form-control-sm" style="max-width:130px;"
                            onchange="document.getElementById('yearForm').submit()">
                        @foreach($allYears as $yr)
                            <option value="{{ $yr->id }}" {{ $selectedYearId == $yr->id ? 'selected' : '' }}>
                                {{ $yr->year_name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                {{-- Participant filter toggle --}}
                <span class="vr-label ml-3"><i class="fas fa-filter mr-1"></i> Show:</span>
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ url('admin/exams/visual_report') }}?year_id={{ $selectedYearId }}&filter=all"
                       class="btn btn-outline-secondary {{ $filterMode === 'all' ? 'btn-filter-active' : '' }}">
                        All Confirmed
                        <span class="badge badge-light ml-1">{{ $totalShown }}</span>
                    </a>
                    <a href="{{ url('admin/exams/visual_report') }}?year_id={{ $selectedYearId }}&filter=last_year"
                       class="btn btn-outline-secondary {{ $filterMode === 'last_year' ? 'btn-filter-active' : '' }}">
                        <i class="fas fa-history mr-1"></i> {{ $selectedYearName }} Participants
                        <span class="badge badge-light ml-1">{{ $filterMode === 'last_year' ? $totalShown : '—' }}</span>
                    </a>
                </div>

                <span class="text-muted ml-auto" style="font-size:.8rem;">
                    Showing <strong>{{ $totalShown }}</strong> examiner(s)
                    &nbsp;·&nbsp; {{ $selectedYearName }} exam cycle
                    @if($filterMode === 'last_year')
                        &nbsp;·&nbsp; <em>Filtered to {{ $selectedYearName }} participants</em>
                    @endif
                </span>
            </div>

            {{-- ── Stat Summary Cards ───────────────────────────────────────────── --}}
            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ array_sum($availabilityData) }}</div>
                        <div class="stat-label">Total Examiners</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">
                            {{ ($availabilityData['FCS'] ?? 0) + ($availabilityData['MCS'] ?? 0) + ($availabilityData['FCS and MCS'] ?? 0) }}
                        </div>
                        <div class="stat-label">Available</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ $availabilityData['Not Available'] ?? 0 }}</div>
                        <div class="stat-label">Not Available</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ count($countryData) }}</div>
                        <div class="stat-label">Countries</div>
                    </div>
                </div>
            </div>

            @if($totalShown === 0)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    No examiners match the current filter. Try changing the year or switching to "All Confirmed".
                </div>
            @else

            {{-- ── Row 1: Availability + Participation ─────────────────────────── --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <h3 class="chart-title">Examiner Availability</h3>
                        <div class="chart-wrapper">
                            <canvas id="availabilityChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h3 class="chart-title">Participation Type</h3>
                        <div class="chart-wrapper">
                            <canvas id="participationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Row 2: Country distribution ──────────────────────────────────── --}}
            <div class="row">
                <div class="col-12">
                    <div class="chart-container">
                        <h3 class="chart-title">Top 10 Countries by Examiner Count</h3>
                        <div class="chart-wrapper">
                            <canvas id="countryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            @endif

        </div>
    </section>
</div>
</div>

<script type="application/json" id="chartData">
{
    "availability": {
        "labels": @json(array_keys($availabilityData)),
        "data":   @json(array_values($availabilityData))
    },
    "participation": {
        "labels": @json(array_keys($participationData)),
        "data":   @json(array_values($participationData))
    },
    "country": {
        "labels": @json(array_keys($countryData)),
        "data":   @json(array_values($countryData))
    }
}
</script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
$(function () {
    var raw;
    try { raw = JSON.parse($('#chartData').text()); }
    catch(e) { console.error('chart data error', e); return; }

    function makeChart(id, config) {
        var el = document.getElementById(id);
        if (!el) return;
        new Chart(el.getContext('2d'), config);
    }

    // ── Availability doughnut ─────────────────────────────────────────────────
    if (raw.availability.data.some(function(v){ return v > 0; })) {
        makeChart('availabilityChart', {
            type: 'doughnut',
            data: {
                labels: raw.availability.labels,
                datasets: [{ data: raw.availability.data,
                    backgroundColor: ['#28a745','#17a2b8','#007bff','#dc3545'],
                    borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    datalabels: { color:'#fff', font:{weight:'bold',size:14},
                        formatter: function(v){ return v === 0 ? '' : v; } }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // ── Participation pie ─────────────────────────────────────────────────────
    if (raw.participation.data.some(function(v){ return v > 0; })) {
        makeChart('participationChart', {
            type: 'pie',
            data: {
                labels: raw.participation.labels,
                datasets: [{ data: raw.participation.data,
                    backgroundColor: ['#a02626','#ffc107','#6c757d','#fd7e14'],
                    borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    datalabels: { color:'#fff', font:{weight:'bold',size:14},
                        formatter: function(v){ return v === 0 ? '' : v; } }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // ── Country bar ───────────────────────────────────────────────────────────
    if (raw.country.data.some(function(v){ return v > 0; })) {
        makeChart('countryChart', {
            type: 'bar',
            data: {
                labels: raw.country.labels,
                datasets: [{ label: 'Examiners', data: raw.country.data,
                    backgroundColor: 'rgba(160,38,38,.8)',
                    borderColor: '#a02626', borderWidth: 1 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: { anchor:'center', align:'center', color:'#fff',
                        font:{size:12,weight:'bold'},
                        formatter: function(v){ return v === 0 ? '' : v; } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize:1, precision:0 } },
                    x: { ticks: { maxRotation:45, font:{size:11} } }
                }
            },
            plugins: [ChartDataLabels]
        });
    }
});
</script>
@endpush

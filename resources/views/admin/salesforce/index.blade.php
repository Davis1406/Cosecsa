@extends('layout.app')

@section('title', 'Salesforce Application')

@push('styles')
<style>
    .sf-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px;
               padding:20px 24px; color:#fff; margin-bottom:1.2rem; }
    .sf-hero h4 { font-weight:700; margin:0 0 4px; }

    .filter-bar { background:#fff; border:1px solid #e9ecef; border-radius:8px;
                  padding:14px 16px; margin-bottom:1.2rem; }
    body.dark-mode .filter-bar { background:#374151; border-color:#4a5568; }

    .stat-card { background: linear-gradient(135deg, #a02626, #c73333); color:#fff;
                 padding: 16px 18px; border-radius: 8px; text-align:center;
                 box-shadow: 0 2px 10px rgba(160,38,38,.25); margin-bottom: 16px; }
    .stat-card.st-good    { background: linear-gradient(135deg, #1a6e3c, #28a05a); box-shadow:0 2px 10px rgba(26,110,60,.25); }
    .stat-card.st-warn    { background: linear-gradient(135deg, #a3690c, #d68f16); box-shadow:0 2px 10px rgba(163,105,12,.25); }
    .stat-card.st-neutral { background: linear-gradient(135deg, #495057, #6c757d); box-shadow:0 2px 10px rgba(73,80,87,.25); }
    .stat-number { font-size: 26px; font-weight: 700; margin-bottom: 2px; }
    .stat-label  { font-size: 12px; opacity: .9; text-transform:uppercase; letter-spacing:.03em; }

    .chart-container { background:#fff; border-radius:8px; padding:18px; box-shadow:0 1px 6px rgba(0,0,0,.08); margin-bottom:22px; }
    body.dark-mode .chart-container { background:#374151; }
    .chart-title { font-size:14px; font-weight:700; color:#a02626; margin-bottom:12px; text-transform:uppercase; letter-spacing:.03em; }
    .chart-wrapper { position:relative; height:300px; }
    .chart-wrapper canvas { height:100% !important; width:100% !important; }

    .sf-table thead th { background:#f8f0f0; color:#a02626; font-size:.75rem;
                          text-transform:uppercase; letter-spacing:.04em; }
    .stage-pill { display:inline-block; padding:3px 12px; border-radius:20px; font-weight:600; font-size:.78rem; }
    .stage-received { background:#fff3cd; color:#856404; }
    .stage-complete  { background:#d4edda; color:#155724; }
    .stage-review    { background:#cce5ff; color:#004085; }
    .stage-rejected  { background:#f8d7da; color:#721c24; }
    .stage-default   { background:#e9ecef; color:#495057; }

    @media print {
        .no-print { display:none !important; }
        .chart-container { page-break-inside: avoid; }
    }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                <div class="sf-hero d-flex flex-wrap justify-content-between align-items-center no-print">
                    <div>
                        <h4><i class="fas fa-cloud mr-2"></i>Salesforce Application</h4>
                        <div style="font-size:.85rem;opacity:.85;">
                            Applications received from the COSECSA Salesforce CRM (cosecsa2.lightning.force.com)
                        </div>
                    </div>
                    <div class="d-flex" style="gap:.5rem;">
                        <button type="button" onclick="window.print()" class="btn btn-light btn-sm font-weight-bold" style="color:#a02626;">
                            <i class="fas fa-print mr-1"></i>Print / Export Report
                        </button>
                        <form method="POST" action="{{ route('admin.salesforce.sync') }}">
                            @csrf
                            <button type="submit" class="btn btn-light btn-sm font-weight-bold" style="color:#a02626;">
                                <i class="fas fa-sync-alt mr-1"></i>Sync Now
                            </button>
                        </form>
                    </div>
                </div>

                {{-- ── Filters ─────────────────────────────────────────────────── --}}
                <div class="filter-bar no-print">
                    <form method="GET" action="{{ url('admin/salesforce') }}"
                          class="d-flex flex-wrap align-items-end" style="gap:.75rem;">
                        <div style="flex:1;min-width:200px;">
                            <label class="d-block mb-1 small font-weight-bold text-muted">Search</label>
                            <input type="text" name="q" value="{{ $search }}" placeholder="Name, email, PEN..."
                                   class="form-control form-control-sm" onchange="this.form.submit()">
                        </div>
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Application Year</label>
                            <select name="application_year" class="form-control form-control-sm" style="width:150px;" onchange="this.form.submit()">
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ (string)$appYear === (string)$y ? 'selected' : '' }}>
                                        {{ $y }} @if($y == \App\Http\Controllers\SalesforceSyncController::DEFAULT_APPLICATION_YEAR) (current) @endif
                                    </option>
                                @endforeach
                                <option value="all" {{ $appYear === 'all' ? 'selected' : '' }}>All years</option>
                            </select>
                            @if($appYear !== 'all')
                                <small class="text-muted d-block mt-1">Jul {{ $appYear - 1 }} – Jun {{ $appYear }}</small>
                            @endif
                        </div>
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Programme</label>
                            <select name="programme" class="form-control form-control-sm" style="width:190px;" onchange="this.form.submit()">
                                <option value="">All programmes</option>
                                @foreach($programmes as $p)
                                    <option value="{{ $p }}" {{ $programme == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Level</label>
                            <select name="level" class="form-control form-control-sm" style="width:170px;" onchange="this.form.submit()">
                                <option value="">All levels</option>
                                @foreach($levels as $l)
                                    <option value="{{ $l }}" {{ $level == $l ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Country</label>
                            <select name="country" class="form-control form-control-sm" style="width:160px;" onchange="this.form.submit()">
                                <option value="">All countries</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c }}" {{ $country == $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Stage</label>
                            <select name="stage" class="form-control form-control-sm" style="width:190px;" onchange="this.form.submit()">
                                <option value="">All stages</option>
                                @foreach($stages as $s)
                                    <option value="{{ $s }}" {{ $stage == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Received</label>
                            <select name="received" class="form-control form-control-sm" style="width:110px;" onchange="this.form.submit()">
                                <option value="">Any</option>
                                <option value="1" {{ $received === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ $received === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Approved</label>
                            <select name="approved" class="form-control form-control-sm" style="width:110px;" onchange="this.form.submit()">
                                <option value="">Any</option>
                                <option value="1" {{ $approved === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ $approved === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div>
                            <a href="{{ url('admin/salesforce') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        </div>
                    </form>
                </div>

                {{-- ── Stat cards ──────────────────────────────────────────────── --}}
                <div class="row">
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-number">{{ number_format($total) }}</div>
                            <div class="stat-label">Total Applications</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card st-good">
                            <div class="stat-number">{{ number_format($receivedCount) }}</div>
                            <div class="stat-label">Received</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card st-good">
                            <div class="stat-number">{{ number_format($approvedCount) }}</div>
                            <div class="stat-label">Complete</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card st-warn">
                            <div class="stat-number">{{ number_format($rejectedCount) }}</div>
                            <div class="stat-label">Rejected / Withdrawn</div>
                        </div>
                    </div>
                </div>

                @if($total === 0)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        No applications match the current filters
                        @if($appYear !== 'all')
                            for the <strong>{{ $appYear }}</strong> application window
                        @endif
                        . Try "All years" or clearing filters, or click <strong>Sync Now</strong> to pull the latest data.
                    </div>
                @else

                {{-- ── Visual report (collapsible) ─────────────────────────────── --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background:#f8f0f0;">
                        <h6 class="mb-0 font-weight-bold" style="color:#a02626;">
                            <i class="fas fa-chart-pie mr-2"></i>Visual Report
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary no-print" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <div class="chart-title">By Application Stage</div>
                                    <div class="chart-wrapper"><canvas id="stageChart"></canvas></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <div class="chart-title">By Application Level</div>
                                    <div class="chart-wrapper"><canvas id="levelChart"></canvas></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <div class="chart-title">Top Programmes</div>
                                    <div class="chart-wrapper"><canvas id="programmeChart"></canvas></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <div class="chart-title">Top Countries</div>
                                    <div class="chart-wrapper"><canvas id="countryChart"></canvas></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="chart-container">
                                    <div class="chart-title">
                                        Applications Over Time
                                        {{ $appYear !== 'all' ? '(' . $appYear . ' window, Jul–Jun)' : '(by intake year)' }}
                                    </div>
                                    <div class="chart-wrapper"><canvas id="trendChart"></canvas></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Data table ──────────────────────────────────────────────── --}}
                <div class="card no-print">
                    <div class="card-body p-0">
                        <table id="applicationsTable" class="table table-sm table-bordered table-striped sf-table mb-0" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Applicant</th>
                                    <th>Email</th>
                                    <th>Programme</th>
                                    <th>Level</th>
                                    <th>Country</th>
                                    <th>PEN</th>
                                    <th>Date Applied</th>
                                    <th>Stage</th>
                                    <th>Received</th>
                                    <th>Approved</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($applications as $app)
                                @php
                                    $stageLower = strtolower($app->application_stage ?? '');
                                    $pillClass = 'stage-default';
                                    if (str_contains($stageLower, 'complete')) $pillClass = 'stage-complete';
                                    elseif (str_contains($stageLower, 'received')) $pillClass = 'stage-received';
                                    elseif (str_contains($stageLower, 'review') || str_contains($stageLower, 'pending')) $pillClass = 'stage-review';
                                    elseif (str_contains($stageLower, 'reject') || str_contains($stageLower, 'withdrawn')) $pillClass = 'stage-rejected';
                                @endphp
                                <tr>
                                    <td>{{ $app->applicant_name ?: '—' }}</td>
                                    <td>{{ $app->applicant_email ?: '—' }}</td>
                                    <td>{{ $app->programme_name ?: '—' }}</td>
                                    <td>{{ $app->application_level ?: '—' }}</td>
                                    <td>{{ $app->country ?: '—' }}</td>
                                    <td>{{ $app->entry_number ?: '—' }}</td>
                                    <td data-order="{{ $app->date_of_application }}">
                                        {{ $app->date_of_application ? \Carbon\Carbon::parse($app->date_of_application)->format('d M Y') : '—' }}
                                    </td>
                                    <td><span class="stage-pill {{ $pillClass }}">{{ $app->application_stage ?: '—' }}</span></td>
                                    <td>{{ $app->application_received ? 'Yes' : 'No' }}</td>
                                    <td>{{ $app->application_approved ? 'Yes' : 'No' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @endif

            </div>
        </section>
    </div>
</div>

<script type="application/json" id="sfChartData">
{
    "stage":     { "labels": @json($stageCounts->keys()),     "data": @json($stageCounts->values()) },
    "programme": { "labels": @json($programmeCounts->keys()), "data": @json($programmeCounts->values()) },
    "country":   { "labels": @json($countryCounts->keys()),   "data": @json($countryCounts->values()) },
    "trend":     { "labels": @json($trendLabels),              "data": @json($trendCounts) }
}
</script>
<script type="application/json" id="sfLevelData">
{ "labels": @json($applications->countBy('application_level')->keys()), "data": @json($applications->countBy('application_level')->values()) }
</script>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#applicationsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copyHtml5',  className: 'btn-sm' },
            { extend: 'csvHtml5',   className: 'btn-sm', title: 'salesforce_applications' },
            { extend: 'excelHtml5', className: 'btn-sm', title: 'salesforce_applications' },
            { extend: 'pdfHtml5',   className: 'btn-sm', title: 'Salesforce Applications', orientation: 'landscape', pageSize: 'A4' },
            { extend: 'print',      className: 'btn-sm' }
        ],
        pageLength: 25,
        order: [[6, 'desc']]
    });

    var raw, levelRaw;
    try {
        raw = JSON.parse(document.getElementById('sfChartData').text);
        levelRaw = JSON.parse(document.getElementById('sfLevelData').text);
    } catch (e) { console.error('chart data parse error', e); return; }

    // Validated sequential burgundy ramp (light -> dark), interpolated per bar by rank.
    var seqLight = [[217,154,154],[199,107,107],[176,69,74],[160,38,38],[110,26,26]];
    function interp(ramp, t) {
        var n = ramp.length - 1;
        var pos = t * n, i = Math.floor(pos), f = pos - i;
        var a = ramp[Math.min(i, n)], b = ramp[Math.min(i + 1, n)];
        var r = Math.round(a[0] + (b[0]-a[0])*f), g = Math.round(a[1] + (b[1]-a[1])*f), bl = Math.round(a[2] + (b[2]-a[2])*f);
        return 'rgb(' + r + ',' + g + ',' + bl + ')';
    }
    function rampColors(count) {
        if (count <= 1) return ['rgb(160,38,38)'];
        var colors = [];
        for (var i = 0; i < count; i++) colors.push(interp(seqLight, i / (count - 1)));
        return colors;
    }

    function makeChart(id, config) {
        var el = document.getElementById(id);
        if (!el) return;
        new Chart(el.getContext('2d'), config);
    }

    // ── Stage breakdown (horizontal bar, sequential ramp by rank) ──────────────
    if (raw.stage.data.length) {
        var stageColors = rampColors(raw.stage.data.length);
        makeChart('stageChart', {
            type: 'bar',
            data: { labels: raw.stage.labels, datasets: [{ data: raw.stage.data, backgroundColor: stageColors, borderRadius: 4, maxBarThickness: 26 }] },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: { anchor: 'end', align: 'end', color: '#555', font: { size: 11, weight: 'bold' } }
                },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            },
            plugins: [ChartDataLabels]
        });
    }

    // ── Level breakdown (validated 2-color categorical) ────────────────────────
    if (levelRaw.data.length) {
        makeChart('levelChart', {
            type: 'doughnut',
            data: { labels: levelRaw.labels, datasets: [{ data: levelRaw.data, backgroundColor: ['#a02626', '#2b6ca3'], borderWidth: 2, borderColor: '#fff' }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    datalabels: { color: '#fff', font: { weight: 'bold', size: 13 }, formatter: function (v) { return v; } }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // ── Top programmes (horizontal bar) ─────────────────────────────────────────
    if (raw.programme.data.length) {
        var progColors = rampColors(raw.programme.data.length);
        makeChart('programmeChart', {
            type: 'bar',
            data: { labels: raw.programme.labels, datasets: [{ data: raw.programme.data, backgroundColor: progColors, borderRadius: 4, maxBarThickness: 22 }] },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: { anchor: 'end', align: 'end', color: '#555', font: { size: 11, weight: 'bold' } }
                },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            },
            plugins: [ChartDataLabels]
        });
    }

    // ── Top countries (horizontal bar) ──────────────────────────────────────────
    if (raw.country.data.length) {
        var countryColors = rampColors(raw.country.data.length);
        makeChart('countryChart', {
            type: 'bar',
            data: { labels: raw.country.labels, datasets: [{ data: raw.country.data, backgroundColor: countryColors, borderRadius: 4, maxBarThickness: 22 }] },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: { anchor: 'end', align: 'end', color: '#555', font: { size: 11, weight: 'bold' } }
                },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            },
            plugins: [ChartDataLabels]
        });
    }

    // ── Trend line (single series, no direct labels — hover tooltip carries detail) ──
    if (raw.trend.data.length) {
        makeChart('trendChart', {
            type: 'line',
            data: {
                labels: raw.trend.labels,
                datasets: [{
                    data: raw.trend.data, borderColor: '#a02626', backgroundColor: 'rgba(160,38,38,.08)',
                    borderWidth: 2, fill: true, tension: .25, pointRadius: 4, pointBackgroundColor: '#a02626'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, datalabels: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            },
            plugins: [ChartDataLabels]
        });
    }
});
</script>
@endpush

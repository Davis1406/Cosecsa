@extends('layout.app')

@section('title', 'Country report')

@push('styles')
<style>
    .cr-bar { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; max-width:1000px; margin:0 auto 16px; }
    .cr-bar label { display:block; font-size:.74rem; font-weight:600; opacity:.7; margin:0 0 3px; }
    .cr-bar select { min-width:150px; }
    .cr-bar .cr-spacer { flex:1; }
    .cr-btn { display:inline-flex; align-items:center; gap:7px; border:1px solid #a02626; background:#a02626; color:#fff !important; border-radius:8px; padding:7px 15px; font-size:.86rem; font-weight:600; cursor:pointer; }
    .cr-btn.alt { background:transparent; color:#a02626 !important; }
    body.dark-mode .cr-btn.alt { color:#f48a8a !important; border-color:#f48a8a; }
    .cr-btn[disabled] { opacity:.6; cursor:wait; }
    .cr-empty { max-width:1000px; margin:0 auto; padding:48px 20px; text-align:center; opacity:.7; border:1px dashed #cbd0d8; border-radius:12px; }
    .cr-checks { max-width:1000px; margin:16px auto 40px; border-left:3px solid #d69e2e; padding:4px 0 4px 16px; }
    .cr-checks h6 { font-weight:700; margin-bottom:6px; }
    .cr-checks li { font-size:.88rem; padding:2px 0; }
    .cr-scroll { overflow-x:auto; padding-bottom:4px; }

    /* The sheet is a document that gets saved as an image or printed: it keeps its own white design in dark mode too. */
    #cr-sheet { width:1000px; margin:0 auto; background:#fff !important; color:#1f2937 !important; font-family:Helvetica,Arial,sans-serif; border:1px solid #e8eaee; }
    #cr-sheet * { box-sizing:border-box; }
    #cr-sheet .cr-head { display:flex; align-items:center; gap:20px; background:#a02626; color:#fff; padding:28px 44px; }
    #cr-sheet .cr-head img { width:66px; height:66px; background:#fff; border-radius:50%; padding:4px; object-fit:contain; }
    #cr-sheet .cr-head h1 { font-size:29px; font-weight:700; margin:0; color:#fff !important; }
    #cr-sheet .cr-head p { font-size:16px; margin:6px 0 0; color:#f6dede !important; }
    #cr-sheet .cr-gold { height:6px; background:#FEC503; }
    #cr-sheet .cr-main { padding:30px 44px 26px; }
    #cr-sheet .cr-cards { display:flex; gap:16px; margin-bottom:30px; }
    #cr-sheet .cr-card { flex:1; border:1px solid #e8eaee; border-top:4px solid #a02626; border-radius:10px; padding:16px 20px; }
    #cr-sheet .cr-card .n { font-size:52px; font-weight:700; line-height:1.1; color:#a02626 !important; }
    #cr-sheet .cr-card .l { font-size:15px; font-weight:700; margin-top:4px; color:#1f2937 !important; }
    #cr-sheet .cr-card .s { font-size:13.5px; margin-top:4px; color:#6b7280 !important; }
    #cr-sheet section.cr-sec { margin:0 0 26px; padding:0; }
    #cr-sheet h2 { font-size:17px; font-weight:700; margin:0 0 4px; color:#1f2937 !important; }
    #cr-sheet .cr-note { font-size:13px; color:#6b7280 !important; margin-bottom:10px; }
    #cr-sheet table { width:100%; border-collapse:collapse; font-size:15px; background:#fff !important; }
    #cr-sheet th { text-align:right; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#6b7280 !important; background:#fff !important; border:0; border-bottom:2px solid #a02626; padding:7px 10px; }
    #cr-sheet td { text-align:right; padding:8px 10px; border:0; border-bottom:1px solid #eceef2; color:#1f2937 !important; background:#fff !important; font-variant-numeric:tabular-nums; }
    #cr-sheet th:first-child, #cr-sheet td:first-child { text-align:left; }
    #cr-sheet th:not(:first-child), #cr-sheet td:not(:first-child) { width:140px; }
    #cr-sheet tr.cr-total td { font-weight:700; background:#fbeeee !important; border-bottom:0; }
    #cr-sheet td.cr-none { text-align:center; color:#6b7280 !important; }
    #cr-sheet .cr-foot { font-size:12.5px; color:#6b7280 !important; border-top:1px solid #e8eaee; padding-top:12px; }

    @media print {
        @page { size:A4 portrait; margin:8mm; }
        body * { visibility:hidden; }
        #cr-sheet, #cr-sheet * { visibility:visible; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        #cr-sheet { position:absolute; left:0; top:0; border:0; margin:0; zoom:.73; }
        html, body, .wrapper, .content-wrapper, .content, .container-wrapper, .cr-scroll { width:730px !important; max-width:730px !important; min-width:0 !important; padding:0 !important; margin:0 !important; overflow:visible !important; }
        .main-sidebar, .main-header, .main-footer, .cr-bar, .cr-checks, .content-header { display:none !important; }
        .content-wrapper { margin:0 !important; background:#fff !important; min-height:0 !important; }
        .cr-scroll { overflow:visible; }
        #cr-sheet section.cr-sec { break-inside:avoid; }
    }
</style>
@endpush

@section('content')
@php
    $date = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('j F Y') : null;
@endphp
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                <form method="GET" id="crForm" class="cr-bar" action="{{ url('admin/countries/report') }}">
                    <div>
                        <label for="crCountry">Country</label>
                        <select id="crCountry" class="form-control form-control-sm">
                            <option value="">Choose a country…</option>
                            @foreach ($countries as $c)
                                <option value="{{ $c->id }}" @if ((string) $countryId === (string) $c->id) selected @endif>{{ trim($c->country_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($report)
                        <div>
                            <label for="crExam">Exam year</label>
                            <select id="crExam" name="exam_year" class="form-control form-control-sm">
                                @foreach ($report['exam_years'] as $y)<option value="{{ $y }}" @if ($y == $report['exam_year']) selected @endif>{{ $y }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="crIntake">Application intake</label>
                            <select id="crIntake" name="intake" class="form-control form-control-sm">
                                @foreach ($report['intake_years'] as $y)<option value="{{ $y }}" @if ($y == $report['intake_year']) selected @endif>July {{ $y - 1 }} – June {{ $y }}</option>@endforeach
                            </select>
                        </div>
                        <span class="cr-spacer"></span>
                        <button type="button" class="cr-btn" id="crDownload"><i class="fas fa-image"></i>Download image</button>
                        <button type="button" class="cr-btn alt" onclick="window.print()"><i class="fas fa-print"></i>Print / PDF</button>
                    @endif
                </form>

                @if (! $report)
                    <div class="cr-empty"><i class="fas fa-globe-africa fa-2x mb-2"></i><br>Choose a country to build its report on candidates and trainees.</div>
                @else
                    @php
                        $name = $report['country']['name'];
                        $tr = $report['trainees']; $p2 = $report['part2']; $ap = $report['applications'];
                        $gender = [['Female', 'female'], ['Male', 'male']];
                        $trCols = $tr['totals']['unknown'] ? array_merge($gender, [['Not recorded', 'unknown']]) : $gender;
                        $p2Cols = $p2['totals']['unknown'] ? array_merge($gender, [['Not recorded', 'unknown']]) : $gender;
                        $apCols = $ap['totals']['other'] ? [['FCS', 'fcs'], ['MCS', 'mcs'], ['Other', 'other']] : [['FCS', 'fcs'], ['MCS', 'mcs']];
                        $sections = [
                            ['1. Active trainees', 'By programme.' . ($tr['future_excluded'] ? ' Excludes ' . $tr['future_excluded'] . ' trainees with a ' . (now()->year + 1) . ' or later intake.' : ''), 'Programme', $trCols, $tr, 'No active trainees.'],
                            ['2. FCS Part II exam candidates, ' . $report['exam_year'], 'Candidates promoted to Part II, by specialty.', 'Specialty', $p2Cols, $p2, 'No candidates promoted to Part II.'],
                            ['3. Applications, by stage', $ap['date_from']
                                ? 'Salesforce applications dated ' . $date($ap['date_from']) . ' to ' . $date($ap['date_to']) . '.'
                                : 'Salesforce applications from ' . $date($ap['window'][0]) . ' to ' . $date($ap['window'][1]) . '.', 'Stage', $apCols, $ap, 'No applications in this intake.'],
                        ];
                    @endphp

                    <div class="cr-scroll">
                        <div id="cr-sheet" data-file="COSECSA_{{ \Illuminate\Support\Str::slug($name, '_') }}_update_{{ $report['as_of'] }}.png">
                            <div class="cr-head">
                                <img src="{{ asset('dist/img/Cosecsa_Logo.png') }}" alt="COSECSA">
                                <div>
                                    <h1>Updates on Candidates and Trainees: {{ $name }}</h1>
                                    <p>College of Surgeons of East, Central and Southern Africa · as of {{ $date($report['as_of']) }}</p>
                                </div>
                            </div>
                            <div class="cr-gold"></div>
                            <div class="cr-main">
                                <div class="cr-cards">
                                    <div class="cr-card"><div class="n">{{ number_format($tr['totals']['total']) }}</div><div class="l">Active trainees</div><div class="s">FCS {{ $tr['fcs'] }} · MCS {{ $tr['mcs'] }}</div></div>
                                    <div class="cr-card"><div class="n">{{ number_format($p2['totals']['total']) }}</div><div class="l">FCS Part II candidates, {{ $report['exam_year'] }}</div><div class="s">Female {{ $p2['totals']['female'] }} · Male {{ $p2['totals']['male'] }}</div></div>
                                    <div class="cr-card"><div class="n">{{ number_format($ap['totals']['total']) }}</div><div class="l">Applications this intake</div><div class="s">FCS {{ $ap['totals']['fcs'] }} · MCS {{ $ap['totals']['mcs'] }}</div></div>
                                </div>

                                @foreach ($sections as [$title, $note, $first, $cols, $data, $none])
                                    <section class="cr-sec">
                                        <h2>{{ $title }}</h2>
                                        <div class="cr-note">{{ $note }}</div>
                                        <table>
                                            <thead><tr><th>{{ $first }}</th>@foreach ($cols as [$label])<th>{{ $label }}</th>@endforeach<th>Total</th></tr></thead>
                                            <tbody>
                                                @forelse ($data['rows'] as $row)
                                                    <tr><td>{{ $row['label'] }}</td>@foreach ($cols as [, $key])<td>{{ number_format($row[$key]) }}</td>@endforeach<td>{{ number_format($row['total']) }}</td></tr>
                                                @empty
                                                    <tr><td class="cr-none" colspan="{{ count($cols) + 2 }}">{{ $none }}</td></tr>
                                                @endforelse
                                                @if (count($data['rows']))
                                                    <tr class="cr-total"><td>Total</td>@foreach ($cols as [, $key])<td>{{ number_format($data['totals'][$key]) }}</td>@endforeach<td>{{ number_format($data['totals']['total']) }}</td></tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </section>
                                @endforeach

                                <div class="cr-foot">Source: COSECSA MIS.@if ($report['synced_at']) Salesforce applications synced {{ $date($report['synced_at']) }}.@endif</div>
                            </div>
                        </div>
                    </div>

                    <div class="cr-checks">
                        <h6>Check before presenting <span class="text-muted font-weight-normal small">(not part of the image or print)</span></h6>
                        <ul class="mb-0 pl-3">
                            @forelse ($report['checks'] as $check)
                                <li><strong>{{ number_format($check['count']) }}</strong> · {{ $check['label'] }}</li>
                            @empty
                                <li>Nothing stands out.</li>
                            @endforelse
                            <li>MCS exam candidates for {{ $report['exam_year'] }} (not in the report): <strong>{{ number_format($report['mcs_candidates']) }}</strong></li>
                            <li>Salesforce country matched: {{ $report['salesforce_countries'] ? implode(', ', $report['salesforce_countries']) : 'none' }}</li>
                        </ul>
                    </div>
                @endif

            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
@if ($report)
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endif
<script>
$(function () {
    var base = @json(url('admin/countries/report'));
    // a new country starts from the same years; the year pickers reload the same country
    $('#crCountry').on('change', function () {
        if (!this.value) { window.location = base; return; }
        var q = $('#crExam').length ? '?' + $.param({ exam_year: $('#crExam').val(), intake: $('#crIntake').val() }) : '';
        window.location = base + '/' + this.value + q;
    });
    $('#crExam, #crIntake').on('change', function () {
        window.location = base + '/' + $('#crCountry').val() + '?' + $.param({ exam_year: $('#crExam').val(), intake: $('#crIntake').val() });
    });

    $('#crDownload').on('click', function () {
        var $b = $(this), sheet = document.getElementById('cr-sheet');
        if (!window.html2canvas) { window.alert('The image tool could not be loaded. Check the connection, or use Print / PDF.'); return; }
        $b.prop('disabled', true);
        window.html2canvas(sheet, { scale: 2, backgroundColor: '#ffffff', useCORS: true, logging: false, windowWidth: 1300, scrollX: 0, scrollY: -window.scrollY })
            .then(function (canvas) {
                canvas.toBlob(function (blob) {
                    var a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = sheet.getAttribute('data-file');
                    document.body.appendChild(a); a.click(); a.remove();
                    setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
                    $b.prop('disabled', false);
                }, 'image/png');
            })
            .catch(function () { window.alert('The image could not be created. Use Print / PDF instead.'); $b.prop('disabled', false); });
    });
});
</script>
@endpush

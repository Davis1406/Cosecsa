@extends('layout.app')

@section('content')
@php
    $matched   = $summary['matched'] ?? 0;
    $toPromote = collect($rows)->where('status', 'matched')->where('remark', 'Pass')
                    ->filter(fn ($r) => empty($r['candidate']['promoted_at']))->count();
    $statusLabels = [
        'matched' => 'Matched', 'not_found' => 'No candidate', 'duplicate' => 'Duplicate ref', 'ambiguous' => 'Several candidates',
        'no_year' => 'No exam year', 'no_ref' => 'No External Ref', 'bad_remark' => 'Remark not Pass/Fail',
    ];
@endphp
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 style="font-size:1.4rem;">Review Part I Results</h1>
                    <p class="text-muted mb-0" style="font-size:.85rem;">
                        {{ $fileName }} — nothing has been saved yet. Check the matches below, then import.
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ url('admin/associates/candidates/part-one') }}" class="btn btn-cosecsa-dark">
                        <i class="fas fa-arrow-left mr-1"></i> Choose another file
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('_message')

            <div class="row">
                @foreach([
                    ['Rows in file', $summary['total'] ?? 0, 'stitch-tile-teal', 'all'],
                    ['Matched', $matched, 'stitch-tile-green', 'matched'],
                    ['Pass', $summary['pass'] ?? 0, 'stitch-tile-gold', 'pass'],
                    ['Fail', $summary['fail'] ?? 0, 'stitch-tile-maroon', 'fail'],
                    ['Not matched', $summary['unmatched'] ?? 0, 'stitch-tile-maroon', 'unmatched'],
                    ['With warnings', $summary['warnings'] ?? 0, 'stitch-tile-blue', 'warnings'],
                ] as [$label, $value, $cls, $filter])
                    <div class="col-6 col-md-4 col-xl-2 mb-3">
                        <div class="stitch-tile {{ $cls }} p1-tile" data-filter="{{ $filter }}" role="button" title="Show these rows">
                            <div class="stitch-tile-label">{{ $label }}</div>
                            <div class="stitch-tile-value">{{ number_format($value) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Columns in this file</h3></div>
                <div class="card-body py-2">
                    @include('admin.associates.candidates._part_one_columns', ['present' => collect($columns)->pluck('header')->all()])
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rows <small class="text-muted ml-1" id="p1Showing"></small></h3>
                </div>
                <div class="card-body">
                    <script type="application/json" id="p1Rows">{!! json_encode($rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                    <table id="p1Table" class="table table-sm table-hover mb-0" style="width:100%;font-size:.84rem;">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Status</th>
                                <th class="p1-th-key">External Ref</th>
                                <th>Name in file</th>
                                <th>Matched candidate</th>
                                <th class="p1-th-key">Class</th>
                                <th class="p1-th-imp text-right">P I %</th>
                                <th class="p1-th-imp text-right">P II %</th>
                                <th class="p1-th-imp text-right">Overall %</th>
                                <th class="p1-th-imp text-right">Pass Mark</th>
                                <th class="p1-th-imp">Remark</th>
                                <th>Part II</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ url('admin/associates/candidates/part-one/import') }}"
                          onsubmit="return confirm('Save Part I results for {{ $matched }} candidates?');">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="p1Promote" name="promote" value="1" checked>
                            <label class="custom-control-label" for="p1Promote">
                                Mark the <strong>{{ $toPromote }}</strong> candidates who passed (and aren't already) as <strong>promoted to Part II</strong>
                            </label>
                        </div>
                        <p class="text-muted mb-3" style="font-size:.82rem;">
                            Saves each matched candidate's Part I result (both papers, overall %, pass mark, Pass/Fail)
                            @if($summary['updates'] ?? 0) — <strong>{{ $summary['updates'] }}</strong> already have a Part I result for this year and will be updated @endif.
                            Rows that didn't match are skipped. Repeat PI, MMED and Sponsor are not changed.
                        </p>
                        <button type="submit" class="btn btn-cosecsa" {{ $matched ? '' : 'disabled' }}>
                            <i class="fas fa-file-import mr-1"></i> Import {{ $matched }} results
                        </button>
                        <a href="{{ url('admin/associates/candidates/part-one') }}" class="btn btn-cosecsa-dark ml-1">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .p1-tile { cursor:pointer; }
    .p1-tile.p1-active { outline:2px solid #a02626; outline-offset:2px; }
    #p1Table th.p1-th-key { background:rgba(160,38,38,.10); }
    #p1Table th.p1-th-imp { background:rgba(254,197,3,.22); }
    #p1Table td.p1-key { background:rgba(160,38,38,.05); font-weight:600; }
    #p1Table td.p1-imp { background:rgba(254,197,3,.10); }
    #p1Table td { vertical-align:middle; }
    .p1-note { display:block; font-size:.76rem; color:#856404; }
    .p1-note.p1-info { color:#6b7280; }
    body.dark-mode .p1-note { color:#FEC503; }
    body.dark-mode .p1-note.p1-info { color:#a0aec0; }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    var labels = @json($statusLabels);
    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function pct(d, type) {
        if (type === 'sort' || type === 'type') return d == null ? -1 : d;
        return d == null ? '—' : Number(d).toFixed(2).replace(/\.?0+$/, '');
    }
    var filter = 'all';

    var dt = $('#p1Table').DataTable({
        deferRender: true,
        data: JSON.parse(document.getElementById('p1Rows').textContent || '[]'),
        pageLength: 25,
        order: [[1, 'desc'], [0, 'asc']],
        columns: [
            { data: 'line' },
            { data: 'status', render: function (d, type, r) {
                // Unmatched first when sorting by status.
                if (type === 'sort') return d === 'matched' ? 0 : 1;
                var label = labels[d] || d;
                if (type !== 'display') return label;
                return d === 'matched' ? '<span class="badge badge-success">Matched</span>' : '<span class="badge badge-danger">' + esc(label) + '</span>';
            } },
            { data: 'ref', className: 'p1-key', render: function (d, type) { return type === 'display' ? esc(d || '—') : (d || ''); } },
            { data: null, render: function (d, type, r) {
                var n = [r.other_names, r.last_name].filter(Boolean).join(' ');
                return type === 'display' ? esc(n) + (r.email ? '<small class="text-muted d-block">' + esc(r.email) + '</small>' : '') : n;
            } },
            { data: null, render: function (d, type, r) {
                var c = r.candidate;
                if (!c) return type === 'display' ? '<span class="text-muted">—</span>' : '';
                return type === 'display' ? esc(c.name) + '<small class="text-muted d-block">' + esc(c.programme_name || '') + (c.cand_no ? ' · ' + esc(c.cand_no) : '') + '</small>' : c.name;
            } },
            { data: 'class', className: 'p1-key', render: function (d, type) { return type === 'display' ? esc(d) : (d || ''); } },
            { data: 'p1_percent', className: 'p1-imp text-right', render: pct },
            { data: 'p2_percent', className: 'p1-imp text-right', render: pct },
            { data: 'overall', className: 'p1-imp text-right font-weight-bold', render: pct },
            { data: 'pass_mark', className: 'p1-imp text-right', render: pct },
            { data: 'remark', className: 'p1-imp', render: function (d, type) {
                if (type !== 'display') return d || '';
                return d === 'Pass' ? '<span class="badge badge-success">Pass</span>' : d === 'Fail' ? '<span class="badge badge-danger">Fail</span>' : esc(d || '—');
            } },
            { data: null, render: function (d, type, r) {
                var text = !r.candidate ? '' : r.candidate.promoted_at ? 'Already promoted' : (r.remark === 'Pass' ? 'Will promote' : '—');
                if (type !== 'display') return text;
                if (text === 'Will promote') return '<span class="badge" style="background:#FEC503;color:#3a2a00;">Will promote</span>';
                if (text === 'Already promoted') return '<span class="badge badge-secondary">Already promoted</span>';
                return '<span class="text-muted">' + (text || '—') + '</span>';
            } },
            { data: null, orderable: false, render: function (d, type, r) {
                var notes = (r.warnings || []).map(function (w) { return '<span class="p1-note"><i class="fas fa-exclamation-triangle mr-1"></i>' + esc(w) + '</span>'; });
                if (r.existing) notes.push('<span class="p1-note p1-info"><i class="fas fa-sync-alt mr-1"></i>Updates existing result (' + esc(r.existing.overall) + '% ' + esc(r.existing.result) + ')</span>');
                if (type !== 'display') return (r.warnings || []).join('; ');
                return notes.join('') || '<span class="text-muted">—</span>';
            } }
        ],
        drawCallback: function () {
            var info = this.api().page.info();
            $('#p1Showing').text(filter === 'all' ? '' : '(' + info.recordsDisplay + ' shown)');
        }
    });

    $.fn.dataTable.ext.search.push(function (settings, data, i, r) {
        if (settings.nTable.id !== 'p1Table' || filter === 'all') return true;
        if (filter === 'matched')   return r.status === 'matched';
        if (filter === 'unmatched') return r.status !== 'matched';
        if (filter === 'pass')      return r.status === 'matched' && r.remark === 'Pass';
        if (filter === 'fail')      return r.status === 'matched' && r.remark === 'Fail';
        if (filter === 'warnings')  return (r.warnings || []).length > 0;
        return true;
    });

    $('.p1-tile').on('click', function () {
        var f = $(this).data('filter');
        filter = (filter === f) ? 'all' : f;
        $('.p1-tile').removeClass('p1-active');
        if (filter !== 'all') $(this).addClass('p1-active');
        dt.draw();
    });
});
</script>
@endpush

@extends('layout.app')

@section('content')
@php
    $s = $summary;
    $attention = ($s['not_found'] ?? 0) + ($s['ambiguous'] ?? 0) + ($s['duplicate'] ?? 0) + ($s['conflict'] ?? 0) + ($s['invalid'] ?? 0);
    $ready = $s['update'] ?? 0;
    $statusLabels = [
        'update' => 'Will update', 'unchanged' => 'Up to date', 'not_found' => 'No candidate', 'ambiguous' => 'Several candidates',
        'duplicate' => 'Duplicate PEN', 'conflict' => 'Number clash', 'invalid' => 'Incomplete row',
    ];
@endphp
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 style="font-size:1.4rem;">Review MCS Part II groups</h1>
                    <p class="text-muted mb-0" style="font-size:.85rem;">
                        {{ $fileName }} (sheet "{{ $sheet }}", exam year {{ $year }}) — <strong>nothing has been saved yet</strong>.
                        Check what will change below, then import.
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ url('admin/associates/candidates/import') }}" class="btn btn-cosecsa-dark">
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
                    ['Rows in file', $s['total'] ?? 0, 'stitch-tile-teal', 'all'],
                    ['Will update', $ready, 'stitch-tile-green', 'update'],
                    ['Already up to date', $s['unchanged'] ?? 0, 'stitch-tile-blue', 'unchanged'],
                    ['Need attention (skipped)', $attention, 'stitch-tile-maroon', 'attention'],
                    ['New groups to create', count($s['new_groups'] ?? []), 'stitch-tile-gold', 'newgroup'],
                ] as [$label, $value, $cls, $filter])
                    <div class="col-6 col-md-4 col-xl-2 mb-3">
                        <div class="stitch-tile {{ $cls }} pg-tile" data-filter="{{ $filter }}" role="button" title="Show these rows">
                            <div class="stitch-tile-label">{{ $label }}</div>
                            <div class="stitch-tile-value">{{ number_format($value) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($attention)
                <div class="alert alert-warning" style="font-size:.88rem;">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>{{ $attention }}</strong> row(s) will be skipped because they could not be matched safely
                    ({{ collect([
                        'not_found' => 'no candidate', 'ambiguous' => 'several candidates', 'duplicate' => 'repeated PEN',
                        'conflict' => 'number clash', 'invalid' => 'incomplete',
                    ])->filter(fn ($l, $k) => ($s[$k] ?? 0) > 0)->map(fn ($l, $k) => $s[$k] . ' ' . $l)->implode(', ') }}).
                    Click <em>Need attention</em> to see them.
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">Groups after the import</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:.85rem;">
                        <thead><tr><th>Group</th><th class="text-right">Candidates</th><th class="text-right">Morning</th><th class="text-right">Afternoon</th><th></th></tr></thead>
                        <tbody>
                            @forelse($groups['by_group'] as $g)
                                <tr>
                                    <td><strong>{{ $g['group'] }}</strong></td>
                                    <td class="text-right">{{ $g['candidates'] }}</td>
                                    <td class="text-right">{{ $g['morning'] }}</td>
                                    <td class="text-right">{{ $g['afternoon'] }}</td>
                                    <td>@if($g['new'])<span class="badge" style="background:#FEC503;color:#3a2a00;">New group, will be created</span>@endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">No candidate in this file can be updated.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">What will change <small class="text-muted ml-1" id="pgShowing"></small></h3>
                </div>
                <div class="card-body">
                    <script type="application/json" id="pgRows">{!! json_encode($rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                    <table id="pgTable" class="table table-sm table-hover mb-0" style="width:100%;font-size:.84rem;">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Status</th>
                                <th>PEN</th>
                                <th>Name in file</th>
                                <th>Matched candidate</th>
                                <th class="pg-th-imp">Group</th>
                                <th class="pg-th-imp">Candidate no.</th>
                                <th class="pg-th-imp">Session</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ url('admin/associates/candidates/import/part-two-groups') }}"
                          onsubmit="return confirm('Save groups, candidate numbers and sessions for {{ $ready }} candidates?');">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="exam_year" value="{{ $year }}">
                        @if(($s['not_promoted'] ?? 0) > 0)
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="pgPromote" name="promote" value="1">
                                <label class="custom-control-label" for="pgPromote">
                                    Also mark the <strong>{{ $s['not_promoted'] }}</strong> candidates in this file who aren't yet as <strong>promoted to Part II</strong>
                                </label>
                            </div>
                        @endif
                        <p class="text-muted mb-3" style="font-size:.82rem;">
                            Saves only the <strong>group</strong>, <strong>candidate number</strong> and <strong>session</strong> of the
                            <strong>{{ $ready }}</strong> candidates marked "Will update". Nothing else on their record changes, and
                            rows that need attention are skipped. Each candidate is matched by PEN for exam year {{ $year }}.
                        </p>
                        <button type="submit" class="btn btn-cosecsa" {{ $ready || ($s['not_promoted'] ?? 0) ? '' : 'disabled' }}>
                            <i class="fas fa-file-import mr-1"></i> Import {{ $ready }} update{{ $ready === 1 ? '' : 's' }}
                        </button>
                        <a href="{{ url('admin/associates/candidates/import') }}" class="btn btn-cosecsa-dark ml-1">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .pg-tile { cursor:pointer; }
    .pg-tile.pg-active { outline:2px solid #a02626; outline-offset:2px; }
    #pgTable th.pg-th-imp { background:rgba(254,197,3,.22); }
    #pgTable td { vertical-align:middle; }
    .pg-old { text-decoration:line-through; color:#8a8f98; margin-right:.25rem; }
    .pg-new { font-weight:700; color:#1b6e3a; }
    .pg-same { color:#6b7280; }
    .pg-note { display:block; font-size:.76rem; color:#856404; }
    body.dark-mode .pg-note { color:#FEC503; }
    body.dark-mode .pg-new { color:#6fdc98; }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    var labels = @json($statusLabels);
    var attention = ['not_found', 'ambiguous', 'duplicate', 'conflict', 'invalid'];
    var filter = 'all';
    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    // "old → new" for one field; plain value when it does not change
    function change(r, field, shown) {
        var c = (r.changes || {})[field];
        if (!r.candidate) return '<span class="text-muted">—</span>';
        if (!c) return '<span class="pg-same">' + esc(shown || '—') + '</span>';
        return (c.from ? '<span class="pg-old">' + esc(c.from) + '</span>' : '<span class="pg-old text-muted">empty</span>') + '→ <span class="pg-new">' + esc(c.to) + '</span>';
    }

    var dt = $('#pgTable').DataTable({
        deferRender: true,
        data: JSON.parse(document.getElementById('pgRows').textContent || '[]'),
        pageLength: 25,
        order: [[0, 'asc']],
        columns: [
            { data: 'line' },
            { data: 'status', render: function (d, type) {
                var label = labels[d] || d;
                if (type !== 'display') return label;
                var cls = d === 'update' ? 'badge-success' : d === 'unchanged' ? 'badge-secondary' : 'badge-danger';
                return '<span class="badge ' + cls + '">' + esc(label) + '</span>';
            } },
            { data: 'pen', render: function (d, type) { return type === 'display' ? '<strong>' + esc(d || '—') + '</strong>' : (d || ''); } },
            { data: null, render: function (d, type, r) {
                return type === 'display' ? esc(r.name) + (r.email ? '<small class="text-muted d-block">' + esc(r.email) + '</small>' : '') : r.name;
            } },
            { data: null, render: function (d, type, r) {
                var c = r.candidate;
                if (!c) return type === 'display' ? '<span class="text-muted">—</span>' : '';
                return type === 'display' ? esc(c.name) + '<small class="text-muted d-block">' + esc(c.pen) + (c.matched_by === 'email' ? ' · matched by email' : '') + '</small>' : c.name;
            } },
            { data: null, render: function (d, type, r) {
                var txt = r.candidate ? ((r.changes.group || {}).to || r.candidate.group || '') : '';
                return type === 'display' ? change(r, 'group', r.candidate && r.candidate.group) + (r.new_group ? ' <span class="badge" style="background:#FEC503;color:#3a2a00;">new</span>' : '') : txt;
            } },
            { data: null, render: function (d, type, r) {
                return type === 'display' ? change(r, 'candidate_no', r.candidate && r.candidate.candidate_no) : (r.candidate_no || '');
            } },
            { data: null, render: function (d, type, r) {
                return type === 'display' ? change(r, 'session', r.candidate && r.candidate.session) : (r.session || '');
            } },
            { data: null, orderable: false, render: function (d, type, r) {
                var notes = (r.issues || []).map(function (w) { return '<span class="pg-note"><i class="fas fa-exclamation-triangle mr-1"></i>' + esc(w) + '</span>'; });
                if (type !== 'display') return (r.issues || []).join('; ');
                return notes.join('') || '<span class="text-muted">—</span>';
            } }
        ],
        drawCallback: function () {
            var info = this.api().page.info();
            $('#pgShowing').text(filter === 'all' ? '' : '(' + info.recordsDisplay + ' shown)');
        }
    });

    $.fn.dataTable.ext.search.push(function (settings, data, i, r) {
        if (settings.nTable.id !== 'pgTable' || filter === 'all') return true;
        if (filter === 'attention') return attention.indexOf(r.status) !== -1;
        if (filter === 'newgroup')  return !!r.new_group;
        return r.status === filter;
    });

    $('.pg-tile').on('click', function () {
        var f = $(this).data('filter');
        filter = (filter === f) ? 'all' : f;
        $('.pg-tile').removeClass('pg-active');
        if (filter !== 'all') $(this).addClass('pg-active');
        dt.draw();
    });
});
</script>
@endpush

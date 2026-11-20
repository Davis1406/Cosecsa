@extends('layout.app')

@section('title', 'Fellowship by Election')

@push('styles')
@include('admin.election_applications._style')
<style>
    .fe-tabs { display:inline-flex; gap:4px; padding:4px; background:var(--fe-soft); border:1px solid var(--fe-line); border-radius:10px; flex-wrap:wrap; }
    .fe-tab { border:0; background:transparent; color:var(--fe-muted); font-size:.86rem; font-weight:600; padding:6px 14px; border-radius:7px; cursor:pointer; }
    .fe-tab .n { margin-left:6px; font-variant-numeric:tabular-nums; opacity:.8; }
    .fe-tab.active { background:var(--fe-surface); color:var(--fe-text); box-shadow:0 1px 3px rgba(0,0,0,.12); }
    .fe-bar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; margin-bottom:14px; }
    .fe-filters { display:flex; flex-wrap:wrap; gap:8px; }
    .fe-filters .fe-input { width:230px; } .fe-filters .fe-select { width:auto; }
    table.fe-table { width:100%; border-collapse:collapse; }
    .fe-table th { font-size:.72rem; font-weight:650; letter-spacing:.06em; text-transform:uppercase; color:var(--fe-muted); padding:12px 14px; border-bottom:1px solid var(--fe-line) !important; border-top:0 !important; background:transparent; white-space:nowrap; }
    .fe-table td { padding:12px 14px; border-top:1px solid var(--fe-line) !important; vertical-align:middle; font-size:.88rem; background:transparent !important; }
    .fe-table tbody tr:first-child td { border-top:0 !important; }
    .fe-table tbody tr:hover td { background:var(--fe-soft) !important; }
    .fe-who { display:flex; align-items:center; gap:12px; min-width:200px; }
    .fe-who a.name { font-weight:600; text-decoration:none; }
    .fe-who a.name:hover { color:var(--fe-accent); }
    .fe-clip { max-width:190px; color:var(--fe-muted); font-size:.82rem; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
    .fe-actions { display:flex; gap:6px; justify-content:flex-end; }
    .fe-check { width:16px; height:16px; accent-color:var(--fe-accent); cursor:pointer; }
    .fe-empty { padding:40px 0; text-align:center; color:var(--fe-muted); }
    .fe-selbar { display:none; align-items:center; gap:12px; padding:10px 14px; margin-bottom:14px; border:1px solid var(--fe-accent); background:var(--fe-accent-soft); border-radius:10px; }
    .fe-selbar.on { display:flex; }
    .fe-foot { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; padding:12px 16px; border-top:1px solid var(--fe-line); font-size:.82rem; color:var(--fe-muted); }
    .fe-foot .pagination { margin:0; }
    .fe .page-link { background:var(--fe-surface); border-color:var(--fe-line); color:var(--fe-text); }
    .fe .page-item.active .page-link { background:var(--fe-accent); border-color:var(--fe-accent); color:#fff; }
    .fe .page-item.disabled .page-link { opacity:.5; }
</style>
@endpush

@section('content')
@php
    $canManage = Auth::user()->hasPermission('fellows.manage');
    $years = collect($applications)->pluck('year')->filter()->unique()->sortDesc()->values();
    $defaultYear = $years->first() ?: date('Y');
@endphp
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">
            @include('_message')
            @if (session('skipped'))
                <div class="alert alert-warning">
                    <strong>Skipped:</strong>
                    <ul class="mb-0 pl-3">@foreach (session('skipped') as $s)<li>{{ $s }}</li>@endforeach</ul>
                </div>
            @endif
        </div>

        <section class="content">
            <div class="container-wrapper">
            <div class="fe">

                <div class="fe-head">
                    <div>
                        <h1 class="fe-title">Fellowship by Election</h1>
                        <div class="fe-sub">
                            Applications from the COSECSA Ordinary Fellow by Election form.
                            @if ($lastSync) Synced {{ \Carbon\Carbon::parse($lastSync)->diffForHumans() }}. @endif
                        </div>
                    </div>
                    @if ($canManage)
                        <form method="POST" action="{{ route('admin.election.sync') }}">
                            @csrf
                            <button type="submit" class="fe-btn" @if(! $configured) disabled title="The Wufoo API key has not been set on the server yet" @endif>
                                <i class="fas fa-sync-alt"></i>Sync with Wufoo
                            </button>
                        </form>
                    @endif
                </div>

                @unless ($configured)
                    <div class="fe-note mb-3"><i class="fas fa-info-circle mr-1"></i>Showing the entries loaded from the Wufoo export. Live sync and the uploaded documents switch on once the Wufoo API key is set on the server.</div>
                @endunless

                <div class="fe-bar">
                    <div class="fe-tabs" id="feTabs">
                        <button type="button" class="fe-tab active" data-status="">All<span class="n" data-n="">{{ $counts['all'] }}</span></button>
                        <button type="button" class="fe-tab" data-status="pending">Pending<span class="n" data-n="pending">{{ $counts['pending'] }}</span></button>
                        <button type="button" class="fe-tab" data-status="approved">Approved<span class="n" data-n="approved">{{ $counts['approved'] }}</span></button>
                        <button type="button" class="fe-tab" data-status="rejected">Rejected<span class="n" data-n="rejected">{{ $counts['rejected'] }}</span></button>
                    </div>
                    <div class="fe-filters">
                        <input type="search" id="feSearch" class="fe-input" placeholder="Search name, email, country…">
                        <select id="feYear" class="fe-select" title="Year applied">
                            <option value="">All years</option>
                            @foreach ($years as $y)<option value="{{ $y }}" @if ($loop->first) selected @endif>{{ $y }}</option>@endforeach
                        </select>
                        <select id="feKnown" class="fe-select" title="Already in the system">
                            <option value="">Everyone</option>
                            <option value="new">Not in the system</option>
                            <option value="tentative">Already tentative</option>
                            <option value="fellow">Already a Fellow</option>
                            <option value="missing">Fellow with missing details</option>
                        </select>
                    </div>
                </div>

                @if ($canManage)
                    <div class="fe-selbar" id="feSelBar">
                        <strong id="feSelCount">0</strong><span>selected</span>
                        <button type="button" class="fe-btn ok" id="feBulkBtn"><i class="fas fa-check"></i>Approve selected</button>
                        <button type="button" class="fe-btn" id="feSelClear">Clear</button>
                    </div>
                @endif

                <div class="fe-panel">
                    <div style="overflow-x:auto;">
                    <table id="feTable" class="fe-table">
                        <thead>
                            <tr>
                                @if ($canManage)<th style="width:34px;"><input type="checkbox" class="fe-check" id="fePickAll" title="Select every pending application shown"></th>@endif
                                <th>Applicant</th>
                                <th>Country</th>
                                <th>Qualification</th>
                                <th>In the system</th>
                                <th>Status</th>
                                @if ($canManage)<th></th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applications as $a)
                                @php
                                    $k = $a['known'] ?? null;
                                    $kind = ! $k ? 'new' : ($k['kind'] === 'fellow' ? 'fellow' : 'tentative');
                                    $isFellowRecord = $k && in_array($k['kind'], ['fellow', 'fellow_tentative'], true);
                                    $missing = (int) ($a['missing'] ?? 0);
                                    $initials = collect(preg_split('/\s+/', trim(preg_replace('/^(Dr|Prof|Mr|Mrs|Ms|Miss)\.?\s+/i', '', $a['name']))))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                                    $canApprove = $a['status'] !== 'approved' && $kind !== 'fellow';
                                    $search = strtolower($a['name'] . ' ' . $a['email'] . ' ' . $a['country'] . ' ' . $a['organisation']);
                                @endphp
                                <tr data-id="{{ $a['id'] }}" data-year="{{ $a['year'] }}" data-status="{{ $a['status'] }}" data-known="{{ $kind }}" data-missing="{{ $missing > 0 ? 1 : 0 }}" data-name="{{ $a['name'] }}" data-search="{{ $search }}">
                                    @if ($canManage)
                                        <td>@if ($a['status'] === 'pending' && $kind !== 'fellow')<input type="checkbox" class="fe-check fe-pick" value="{{ $a['id'] }}">@endif</td>
                                    @endif
                                    <td>
                                        <div class="fe-who">
                                            <span class="fe-avatar">{{ strtoupper($initials) ?: '?' }}</span>
                                            <div>
                                                <a class="name" href="{{ route('admin.election.show', $a['id']) }}">{{ $a['name'] }}</a>
                                                @if ($a['times_applied'] > 1)<span class="fe-tag ml-1" title="Applied more than once (same email)">· {{ $a['times_applied'] }} entries</span>@endif
                                                <div class="fe-note">{{ $a['email'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $a['country'] ?: '—' }}</td>
                                    <td><div class="fe-clip" title="{{ $a['specialty'] }}">{{ $a['specialty'] ?: '—' }}</div></td>
                                    <td>
                                        @if ($kind === 'fellow')
                                            <div class="fe-sys fellow" title="Matched by {{ $k['by'] }}">Fellow<small>{{ $k['label'] }}</small></div>
                                        @elseif ($kind === 'tentative')
                                            <div class="fe-sys tentative" title="Matched by {{ $k['by'] }}">Tentative<small>{{ $k['label'] }}</small></div>
                                        @else
                                            <span class="fe-note">—</span>
                                        @endif
                                        @if ($isFellowRecord && $missing > 0)
                                            <div><a href="{{ route('admin.election.show', $a['id']) }}#fellow-record" class="fe-note" style="color:var(--fe-warn-fg) !important;"><i class="fas fa-exclamation-circle mr-1"></i>{{ $missing }} {{ $missing === 1 ? 'detail' : 'details' }} missing</a></div>
                                        @endif
                                    </td>
                                    <td data-order="{{ $a['status'] }}">
                                        <span class="fe-pill {{ $a['status'] }}">{{ ucfirst($a['status']) }}</span>
                                        <div class="fe-note mt-1" style="white-space:nowrap;">{{ $a['submitted_at'] ? \Carbon\Carbon::parse($a['submitted_at'])->format('d M Y') : '' }}</div>
                                    </td>
                                    @if ($canManage)
                                        <td>
                                            <div class="fe-actions">
                                                @if ($canApprove)
                                                    <button type="button" class="fe-btn fe-icon-btn ok fe-approve" title="Approve"><i class="fas fa-check"></i></button>
                                                @endif
                                                @if ($a['status'] === 'pending')
                                                    <button type="button" class="fe-btn fe-icon-btn ghost-danger fe-reject" title="Reject"><i class="fas fa-times"></i></button>
                                                @endif
                                                <a href="{{ route('admin.election.show', $a['id']) }}" class="fe-btn fe-icon-btn" title="Open"><i class="fas fa-chevron-right"></i></a>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    <div class="fe-empty" id="feEmpty" style="display:none;">No applications match.</div>
                    <div class="fe-foot"><span id="feInfo"></span><ul class="pagination pagination-sm" id="fePager"></ul></div>
                </div>

                @if ($canManage)
                    {{-- One approve dialog serves a single row and the bulk button; JS points the form at the right place. --}}
                    <div class="modal fade fe-modal" id="feApproveModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <form method="POST" id="feApproveForm" class="modal-content">
                                @csrf
                                <input type="hidden" name="return" value="list">
                                <div class="modal-header"><h5 class="modal-title" id="feApproveTitle">Approve</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                <div class="modal-body">
                                    <p class="fe-note mb-3" id="feApproveText"></p>
                                    <label class="fe-label">Fellowship year</label>
                                    <input type="number" name="fellowship_year" class="fe-input mb-3" value="{{ $defaultYear }}" min="2000" max="2100" required>
                                    <div id="feNoteWrap"><label class="fe-label">Note (optional)</label><textarea name="note" rows="2" class="fe-textarea"></textarea></div>
                                    <div id="feIds"></div>
                                </div>
                                <div class="modal-footer"><button type="button" class="fe-btn" data-dismiss="modal">Cancel</button><button type="submit" class="fe-btn ok"><i class="fas fa-check"></i>Approve</button></div>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade fe-modal" id="feRejectModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <form method="POST" id="feRejectForm" class="modal-content">
                                @csrf
                                <input type="hidden" name="return" value="list">
                                <input type="hidden" name="status" value="rejected">
                                <div class="modal-header"><h5 class="modal-title" id="feRejectTitle">Reject</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                <div class="modal-body"><label class="fe-label">Reason (optional)</label><textarea name="note" rows="3" class="fe-textarea"></textarea></div>
                                <div class="modal-footer"><button type="button" class="fe-btn" data-dismiss="modal">Cancel</button><button type="submit" class="fe-btn primary">Reject</button></div>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var base = @json(url('admin/fellowship-by-election'));
    var status = '';
    var table = $('#feTable').DataTable({
        order: [], pageLength: 25, autoWidth: false, dom: 't',
        columnDefs: [{ orderable: false, targets: {{ $canManage ? '[0, 6]' : '[]' }} }],
        drawCallback: function () { renderFooter(this.api()); }
    });

    function pass(tr, withStatus) {
        var y = $('#feYear').val(), k = $('#feKnown').val(), q = $.trim($('#feSearch').val()).toLowerCase();
        if (withStatus && status && tr.getAttribute('data-status') !== status) return false;
        if (y && tr.getAttribute('data-year') !== y) return false;
        if (k === 'missing') { if (tr.getAttribute('data-missing') !== '1') return false; }
        else if (k && tr.getAttribute('data-known') !== k) return false;
        return !q || tr.getAttribute('data-search').indexOf(q) !== -1;
    }
    $.fn.dataTable.ext.search.push(function (settings, data, index) {
        return settings.nTable.id !== 'feTable' || pass(table.row(index).node(), true);
    });

    // Tab counts follow the year / "in the system" / search filters, so they always add up to what the table can show.
    function refreshTabs() {
        var n = { '': 0, pending: 0, approved: 0, rejected: 0 };
        table.rows().every(function () { var tr = this.node(); if (pass(tr, false)) { n['']++; n[tr.getAttribute('data-status')]++; } });
        $('#feTabs [data-n]').each(function () { $(this).text(n[$(this).data('n')] || 0); });
    }

    function renderFooter(api) {
        var i = api.page.info(), pages = i.pages, cur = i.page, html = '';
        $('#feInfo').text(i.recordsDisplay ? 'Showing ' + (i.start + 1) + '–' + i.end + ' of ' + i.recordsDisplay : '');
        $('#feEmpty').toggle(!i.recordsDisplay);
        if (pages > 1) {
            html += '<li class="page-item' + (cur === 0 ? ' disabled' : '') + '"><a class="page-link" href="#" data-p="' + (cur - 1) + '">&lsaquo;</a></li>';
            for (var p = 0; p < pages; p++) {
                if (p === 0 || p === pages - 1 || Math.abs(p - cur) <= 1) html += '<li class="page-item' + (p === cur ? ' active' : '') + '"><a class="page-link" href="#" data-p="' + p + '">' + (p + 1) + '</a></li>';
                else if (Math.abs(p - cur) === 2) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            html += '<li class="page-item' + (cur === pages - 1 ? ' disabled' : '') + '"><a class="page-link" href="#" data-p="' + (cur + 1) + '">&rsaquo;</a></li>';
        }
        $('#fePager').html(html);
    }
    $('#fePager').on('click', 'a', function (e) { e.preventDefault(); var p = +$(this).data('p'); if (p >= 0) table.page(p).draw('page'); });

    function redraw() { refreshTabs(); table.draw(); }
    $('#feTabs').on('click', '.fe-tab', function () { status = $(this).data('status') || ''; $('#feTabs .fe-tab').removeClass('active'); $(this).addClass('active'); table.draw(); });
    $('#feYear, #feKnown').on('change', redraw);
    $('#feSearch').on('input', redraw);

    // ---- selection (bulk approve) ----
    function picked() { return $(table.rows().nodes()).find('.fe-pick:checked'); }
    function updateSel() { var n = picked().length; $('#feSelCount').text(n); $('#feSelBar').toggleClass('on', n > 0); }
    $('#feTable').on('change', '.fe-pick', updateSel);
    $('#fePickAll').on('change', function () {
        var on = this.checked;
        $(table.rows({ search: 'applied' }).nodes()).find('.fe-pick').prop('checked', on);
        updateSel();
    });
    $('#feSelClear').on('click', function () { picked().prop('checked', false); $('#fePickAll').prop('checked', false); updateSel(); });

    // ---- approve / reject dialogs ----
    $('#feTable').on('click', '.fe-approve', function () {
        var tr = $(this).closest('tr'), id = tr.data('id'), known = tr.data('known');
        $('#feApproveForm').attr('action', base + '/' + id + '/approve');
        $('#feIds').empty(); $('#feNoteWrap').show();
        $('#feApproveTitle').text('Approve ' + tr.data('name'));
        $('#feApproveText').text(known === 'tentative'
            ? 'They are already tentative, so this links the application to them. Nothing new is added.'
            : 'They are added to the tentative list as Fellow By Election, shown in Fellows › Certificate QR codes until confirmed.');
        $('#feApproveModal').modal('show');
    });
    $('#feBulkBtn').on('click', function () {
        var ids = picked().map(function () { return this.value; }).get();
        $('#feApproveForm').attr('action', base + '/bulk-approve');
        $('#feIds').html(ids.map(function (i) { return '<input type="hidden" name="ids[]" value="' + i + '">'; }).join(''));
        $('#feNoteWrap').hide();
        $('#feApproveTitle').text('Approve ' + ids.length + ' applications');
        $('#feApproveText').text('Anyone already tentative is linked, not added twice. Anyone who is already a Fellow is skipped and listed afterwards.');
        $('#feApproveModal').modal('show');
    });
    $('#feTable').on('click', '.fe-reject', function () {
        var tr = $(this).closest('tr');
        $('#feRejectForm').attr('action', base + '/' + tr.data('id') + '/review');
        $('#feRejectTitle').text('Reject ' + tr.data('name'));
        $('#feRejectModal').modal('show');
    });

    redraw();
});
</script>
@endpush

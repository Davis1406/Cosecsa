@extends('layout.app')

@section('content')
@include('admin._dt_prefetch', ['url' => url('admin/associates/fellows/list-rows')])
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ url('admin/associates/fellows/reports') }}"
                           class="btn btn-info mr-2">
                            <span class="fas fa-chart-bar mr-1"></span> Analytics
                        </a>
                        <a href="{{ url('admin/associates/fellows/import_fellows') }}"
                           class="btn btn-secondary mr-2"
                           style="color:#333; background-color:#FEC503; border-color:#FEC503;">
                            <span class="fas fa-upload mr-1"></span> Upload Fellows
                        </a>
                        <button type="button" class="btn btn-outline-danger mr-2"
                                data-toggle="modal" data-target="#addFromAssociateModal">
                            <span class="fas fa-user-friends mr-1"></span> Add Fellow from Associate
                        </button>
                        <a href="{{ url('admin/associates/fellows/add') }}"
                           class="btn btn-primary"
                           style="background-color:#a02626; border-color:#a02626;">
                            <span class="fas fa-user-plus mr-1"></span> Add New Fellow
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                {{-- Filter Bar --}}
                <div class="card card-outline card-secondary mb-2 shadow-sm">
                    <div class="card-body py-2">
                        @php
                        $filterDefs = [
                            ['id'=>'filterProgramme', 'label'=>'Programme',       'options'=>$filterProgrammes,         'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterCountry',   'label'=>'Country',         'options'=>$filterCountries,          'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterType',      'label'=>'Fellowship Type', 'options'=>$filterTypes,              'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterYear',      'label'=>'Year',            'options'=>$filterYears,              'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterGender',    'label'=>'Gender',          'options'=>collect(['Male','Female']), 'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterTags',      'label'=>'Tags',            'options'=>$filterTags,               'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterAlumni',    'label'=>'Alumni',          'options'=>collect(['unique','all','0']), 'default'=>[], 'optLabels'=>[
                                'unique' => 'Unique Alumni (' . number_format($uniqueAlumniCount ?? 0) . ')',
                                'all'    => 'All Alumni (' . number_format($allAlumniCount ?? 0) . ')',
                                '0'      => 'Non-Alumni Only',
                            ]],
                        ];
                        @endphp
                        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                            @foreach($filterDefs as $fd)
                            <div class="chk-filter-wrap" data-filter="{{ $fd['id'] }}">
                                <button type="button" class="btn btn-sm btn-outline-secondary chk-filter-btn" data-filter="{{ $fd['id'] }}">
                                    {{ $fd['label'] }}
                                    <span class="badge badge-danger chk-badge ml-1" style="display:none;font-size:.65rem;"></span>
                                    <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                                </button>
                                <div class="chk-filter-panel shadow" id="{{ $fd['id'] }}-panel" style="display:none;">
                                    @if($fd['id'] === 'filterAlumni')
                                    <div class="small text-muted mb-2" style="border-bottom:1px solid #eee;padding-bottom:.4rem;">
                                        "Unique" = one row per person. "All" also lists each additional FCS specialty as its own row, matching the source alumni spreadsheet.
                                    </div>
                                    @endif
                                    @if(collect($fd['options'])->count() > 6)
                                    <input type="text" class="form-control form-control-sm chk-search mb-1" placeholder="Search…" autocomplete="off">
                                    @endif
                                    <div class="chk-list">
                                        @foreach($fd['options'] as $opt)
                                        <label class="chk-item">
                                            <input type="checkbox" class="chk-option" data-filter="{{ $fd['id'] }}" value="{{ $opt }}">
                                            {{ !empty($fd['optLabels'][$opt]) ? $fd['optLabels'][$opt] : $opt }}
                                        </label>
                                        @endforeach
                                    </div>
                                    <div class="chk-footer">
                                        <a href="#" class="chk-select-all small">All</a>
                                        <a href="#" class="chk-clear small text-danger">Clear</a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <button id="btnClearFilters" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i>Clear All
                            </button>
                            <small class="text-muted ml-auto" id="filteredCount"></small>
                        </div>
                    </div>
                </div>

                {{-- Certificate QR codes: the QR printed on each certificate. FCS candidates who passed Part I, and the Fellows by
                     Election of the year. The QR shows when hovering a name; see also Fellow page > Certificate QR. --}}
                <div class="card card-outline card-success mb-2 shadow-sm" id="qrCard">
                    <div class="card-header py-2 d-flex align-items-center flex-wrap" style="gap:.75rem;">
                        <img src="{{ asset('dist/img/Cosecsa_Logo_sm.png') }}" alt="COSECSA" width="42" height="42" style="object-fit:contain;flex:0 0 auto;">
                        <div style="line-height:1.2;">
                            <div style="font-size:1.05rem;font-weight:700;color:#222;">Certificate QR codes</div>
                            <small class="text-muted">College of Surgeons of East, Central and Southern Africa</small>
                        </div>
                        <div class="ml-auto d-flex align-items-center flex-wrap" style="gap:.5rem;">
                            <label class="mb-0 small text-muted" for="qrYear">Year</label>
                            <select id="qrYear" class="form-control form-control-sm" style="width:auto;">
                                @foreach(range(now()->year, now()->year - 4) as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach
                            </select>
                            <button type="button" class="btn btn-sm btn-success" id="qrShow"><i class="fas fa-eye mr-1"></i>Show list</button>
                        </div>
                    </div>
                    <div class="card-body py-2 d-none" id="qrBody"></div>
                </div>

                {{-- the QR of the name being hovered --}}
                <div id="qrHover" style="display:none;position:fixed;z-index:1080;width:210px;background:#fff;border:1px solid #dfe3ec;border-radius:12px;box-shadow:0 10px 30px rgba(20,30,60,.22);padding:12px;text-align:center;pointer-events:none;">
                    <div id="qrHoverQr" style="line-height:0;"></div>
                    <div id="qrHoverName" style="margin-top:8px;font-weight:700;font-size:.9rem;line-height:1.25;"></div>
                    <div id="qrHoverType" class="small text-muted"></div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Fellows</h3></div>
                            <div class="card-body">
                                <table id="fellowstable" class="table table-bordered table-striped"
                                       data-rows-url="{{ url('admin/associates/fellows/list-rows') }}"
                                       data-base-url="{{ url('admin/associates/fellows') }}"
                                       data-app-url="{{ url('') }}"
                                       data-can-transcript="{{ Auth::user()->hasPermission('transcripts.view') ? 1 : 0 }}"
                                       data-can-delete="{{ Auth::user()->isSuperAdmin() ? 1 : 0 }}">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>Specialty</th>
                                            <th>Fellowship Type</th>
                                            <th>Fellowship Year</th>
                                            <th>Hospital / Organisation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div> <!-- /.card-body -->
                        </div> <!-- /.card -->
                    </div> <!-- /.col -->
                </div> <!-- /.row -->
            </div> <!-- /.container-wrapper -->
        </section>
    </div>
</div>

{{-- Add Fellow from Associate: someone already an Examiner/Country Rep/Member
     gets a fellows record created off their existing login, instead of a
     brand-new duplicate account. Reverse of the "Add Role" flow on the
     fellow view page. --}}
<div class="modal fade" id="addFromAssociateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="addFromAssociateForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-friends mr-1"></i> Add Fellow from Associate</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="afaAlert" class="alert d-none" role="alert"></div>

                <div class="form-group">
                    <label>Search In</label>
                    <select class="form-control" id="afa_type" name="source_type">
                        <option value="examiner">Examiners</option>
                        <option value="country_rep">Country Representatives</option>
                        <option value="member">Members</option>
                        <option value="programme_director">Programme Directors</option>
                        <option value="trainee">Trainees</option>
                        <option value="candidate">Candidates</option>
                        <option value="trainer">Trainers (ToT)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Search Name / Email <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="afa_search" placeholder="Type a name or email...">
                    <div class="list-group mt-1 d-none afa-results" id="afa_results" style="max-height:180px; overflow-y:auto;"></div>
                    <div class="d-none mt-2" id="afa_selected"></div>
                    <input type="hidden" id="afa_source_id" name="source_id">
                </div>

                <div class="d-none" id="afa_fields">
                    <hr>
                    <p class="small text-muted mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        A Fellow ID is assigned automatically (next number after the last fellow).
                    </p>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Fellowship Type</label>
                            <select class="form-control" name="category_id">
                                <option value="">Select…</option>
                                <option value="5">Fellow by Examination</option>
                                <option value="6">Foundation Fellow</option>
                                <option value="7">Fellow By Election</option>
                                <option value="8">Honorary Fellow (ASEA)</option>
                                <option value="9">Overseas Fellow</option>
                                <option value="10">Honorary Fellow (COSECSA)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Admission Year</label>
                            <input type="text" class="form-control" name="admission_year" placeholder="e.g. 2015">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Fellowship Year</label>
                            <input type="text" class="form-control" name="fellowship_year" placeholder="e.g. 2018">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" id="afaSubmit" disabled>Add as Fellow</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* ── Checkbox filter dropdowns ── */
    .chk-filter-wrap { position: relative; display: inline-block; }
    .chk-filter-panel {
        position: absolute; top: calc(100% + 4px); left: 0; z-index: 1055;
        background: #fff; border: 1px solid #ced4da; border-radius: 6px;
        min-width: 190px; max-width: 260px; padding: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .chk-list { max-height: 220px; overflow-y: auto; }
    .chk-item {
        display: flex; align-items: center; gap: 6px;
        padding: 3px 2px; font-size: .82rem; font-weight: normal;
        cursor: pointer; white-space: nowrap; margin: 0;
    }
    .chk-item:hover { background: #f8f0f0; border-radius: 4px; }
    .chk-item input[type="checkbox"] { margin: 0; cursor: pointer; accent-color: #a02626; }
    .chk-footer {
        display: flex; justify-content: space-between;
        border-top: 1px solid #eee; margin-top: 6px; padding-top: 5px;
        font-size: .78rem;
    }
    .chk-footer a { color: #6c757d; }
    .chk-footer a:hover { color: #a02626; text-decoration: none; }
    .chk-filter-btn { white-space: nowrap; }
    /* ── Table ── */
    #fellowstable td { vertical-align: middle; }
    .fellow-spec-count {
        background:#a02626; color:#fff; border-radius:50%; width:14px; height:14px;
        font-size:.6rem; font-weight:700; display:inline-flex; align-items:center;
        justify-content:center; line-height:1; flex-shrink:0;
    }
    .action-btn { padding: 2px 8px; line-height: 1.4; border-radius: 4px; }
    .action-btn:hover { background-color: #f0f0f0; }
    .dropdown-menu { min-width: 130px; font-size: .875rem; }
    .dropdown-item { padding: 6px 14px; }
    .dropdown-item:hover { background-color: #f8f0f0; }
    .paginate_button.active>.page-link { background-color: #a02626 !important; border-color: #a02626 !important; color: white; }
    .paginate_button>.page-link { color: #a02626; }
    .paginate_button>.page-link:focus, .paginate_button.active>.page-link:focus { box-shadow: none !important; outline: none !important; }
    #fellowFilters label { color: #555; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {

    function getChecked(filterId) {
        return $('.chk-option[data-filter="' + filterId + '"]:checked')
               .map(function () { return this.value; }).get();
    }

    function updateBadge(filterId) {
        var checked = getChecked(filterId);
        var $badge  = $('.chk-filter-btn[data-filter="' + filterId + '"] .chk-badge');
        if (checked.length) $badge.text(checked.length).show();
        else $badge.hide();
    }

    function redraw() {
        var dt   = $('#fellowstable').DataTable();
        dt.draw();
        var info = dt.page.info();
        $('#filteredCount').text(
            info.recordsDisplay < info.recordsTotal
                ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : ''
        );
    }

    // DataTable custom search filter (rows are JSON objects, see custom.js)
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, row) {
        if (settings.nTable.id !== 'fellowstable') return true;

        var chkProgramme = getChecked('filterProgramme');
        var chkCountry   = getChecked('filterCountry');
        var chkType      = getChecked('filterType');
        var chkYear      = getChecked('filterYear');
        var chkGender    = getChecked('filterGender');
        var chkAlumni    = getChecked('filterAlumni');

        if (chkProgramme.length && chkProgramme.indexOf(String(row.programme || '')) === -1) return false;
        if (chkCountry.length   && chkCountry.indexOf(String(row.country     || '')) === -1) return false;
        if (chkType.length      && chkType.indexOf(String(row.ftype          || '')) === -1) return false;
        if (chkYear.length      && chkYear.indexOf(String(row.year           || '')) === -1) return false;
        if (chkGender.length    && chkGender.indexOf(String(row.gender       || '')) === -1) return false;
        var chkTags = getChecked('filterTags');
        if (chkTags.length) {
            var rowTags = row.tags || [];
            if (!chkTags.some(function (t) { return rowTags.indexOf(t) !== -1; })) return false;
        }
        if (chkAlumni.length) {
            var wantsYes = chkAlumni.indexOf('unique') !== -1 || chkAlumni.indexOf('all') !== -1;
            var wantsNo  = chkAlumni.indexOf('0') !== -1;
            var rowAlumni = String(row.alumni);
            if (!((wantsYes && rowAlumni === '1') || (wantsNo && rowAlumni === '0'))) return false;
        }
        return true;
    });

    // ── "All Alumni" split-specialty rows — added/removed from the table
    // (not just hidden), so the default row count never changes unless this
    // view is actively selected. ──
    var extraRowsAdded  = false;

    // Additional-specialty rows (loaded with the table data) as row objects.
    function extraRowData(r) {
        return {
            kind: 'extra', id: r.fellow_id, name: r.name, email: r.email,
            cid: r.country_id, country: r.country_name, pid: null, programme: '',
            specialty: (r.specialty || '').replace(/^FCS\s+/i, '') || null,
            ftype: 'Fellow by Examination', year: r.year, hospital: null,
            gender: '', alumni: 1, specs: 1
        };
    }

    function syncExtraAlumniRows() {
        var dt = $('#fellowstable').DataTable();
        var wantsAll = getChecked('filterAlumni').indexOf('all') !== -1;
        if (wantsAll && !extraRowsAdded) {
            dt.rows.add(($('#fellowstable').data('extra-rows') || []).map(extraRowData)).draw(false);
            extraRowsAdded = true;
        } else if (!wantsAll && extraRowsAdded) {
            dt.rows(function (idx, d) { return d.kind === 'extra'; }).remove().draw(false);
            extraRowsAdded = false;
        }
    }

    // Panel open/close
    $(document).on('click', '.chk-filter-btn', function (e) {
        e.stopPropagation();
        var filterId = $(this).data('filter');
        var $panel   = $('#' + filterId + '-panel');
        $('.chk-filter-panel').not($panel).hide();
        $panel.toggle();
    });
    $(document).on('click', '.chk-filter-panel', function (e) { e.stopPropagation(); });
    $(document).on('click', function () { $('.chk-filter-panel').hide(); });

    // In-panel search
    $(document).on('input', '.chk-search', function () {
        var q = $(this).val().toLowerCase();
        $(this).closest('.chk-filter-panel').find('.chk-item').each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
        });
    });

    // Checkbox change
    $(document).on('change', '.chk-option', function () {
        var filterId = $(this).data('filter');
        // "Unique Alumni" and "All Alumni" are two views of the same set, not
        // combinable filters — selecting one clears the other.
        if (filterId === 'filterAlumni' && this.checked && (this.value === 'unique' || this.value === 'all')) {
            var other = this.value === 'unique' ? 'all' : 'unique';
            $('.chk-option[data-filter="filterAlumni"][value="' + other + '"]').prop('checked', false);
        }
        updateBadge(filterId);
        if (filterId === 'filterAlumni') syncExtraAlumniRows();
        redraw();
    });

    // Select All / Clear per panel
    $(document).on('click', '.chk-select-all', function (e) {
        e.preventDefault();
        var $panel   = $(this).closest('.chk-filter-panel');
        var filterId = $panel.closest('.chk-filter-wrap').data('filter');
        $panel.find('.chk-item:visible .chk-option').prop('checked', true);
        if (filterId === 'filterAlumni') {
            // "all" supersedes "unique" when both would otherwise be checked
            $('.chk-option[data-filter="filterAlumni"][value="unique"]').prop('checked', false);
        }
        updateBadge(filterId);
        if (filterId === 'filterAlumni') syncExtraAlumniRows();
        redraw();
    });
    $(document).on('click', '.chk-clear', function (e) {
        e.preventDefault();
        var $panel   = $(this).closest('.chk-filter-panel');
        var filterId = $panel.closest('.chk-filter-wrap').data('filter');
        $panel.find('.chk-option').prop('checked', false);
        updateBadge(filterId);
        if (filterId === 'filterAlumni') syncExtraAlumniRows();
        redraw();
    });

    // Clear All
    $('#btnClearFilters').on('click', function () {
        $('.chk-option').prop('checked', false);
        $('.chk-badge').hide();
        syncExtraAlumniRows();
        redraw();
        $('#filteredCount').text('');
    });
});
</script>
@endpush

@push('scripts')
<script>
$(function () {
    // ── Add Fellow from Associate ───────────────────────────────────────────
    var afaTimer = null;

    function afaResetSelection() {
        $('#afa_source_id').val('');
        $('#afa_selected').addClass('d-none').empty();
        $('#afa_fields').addClass('d-none');
        $('#afaSubmit').prop('disabled', true);
    }

    $('#addFromAssociateModal').on('hidden.bs.modal', function () {
        $('#afa_search').val('');
        $('#afa_results').addClass('d-none').empty();
        $('#afaAlert').addClass('d-none');
        afaResetSelection();
    });

    $('#afa_type').on('change', afaResetSelection);

    $('#afa_search').on('input', function () {
        var q = $(this).val().trim();
        afaResetSelection();
        clearTimeout(afaTimer);

        if (q.length < 2) {
            $('#afa_results').addClass('d-none').empty();
            return;
        }

        afaTimer = setTimeout(function () {
            $.get('{{ route("fellows.search-associates") }}', { type: $('#afa_type').val(), q: q })
                .done(function (res) {
                    var results = res.results || [];
                    var $list = $('#afa_results').empty();

                    if (!results.length) {
                        $list.append('<div class="list-group-item small text-muted">No matches (or already a fellow).</div>');
                    } else {
                        results.forEach(function (r) {
                            var name = r.name || '';
                            var $item = $('<button type="button" class="list-group-item list-group-item-action"></button>')
                                .text(name + (r.email ? ' — ' + r.email : '') + (r.country ? ' (' + r.country + ')' : ''))
                                .data('id', r.id)
                                .data('label', name);
                            $list.append($item);
                        });
                    }
                    $list.removeClass('d-none');
                });
        }, 300);
    });

    $(document).on('click', '#afa_results .list-group-item-action', function () {
        var id = $(this).data('id');
        var label = $(this).data('label');

        $('#afa_source_id').val(id);
        $('#afa_selected').removeClass('d-none')
            .html('<span class="badge badge-secondary p-2"><i class="fas fa-check mr-1"></i>' +
                  $('<div>').text(label).html() + '</span>');
        $('#afa_results').addClass('d-none').empty();
        $('#afa_fields').removeClass('d-none');
        $('#afaSubmit').prop('disabled', false);
    });

    $('#addFromAssociateForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#afa_source_id').val()) return;

        var $btn = $('#afaSubmit').prop('disabled', true).text('Adding...');
        var $alert = $('#afaAlert').addClass('d-none');

        $.ajax({
            url: '{{ route("fellows.from-associate") }}',
            method: 'POST',
            data: $(this).serialize(),
        }).done(function (res) {
            $alert.removeClass('d-none alert-danger').addClass('alert-success').text(res.message || 'Fellow added.');
            setTimeout(function () {
                window.location.href = '{{ url("admin/associates/fellows/view") }}/' + res.fellow_id;
            }, 900);
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to add fellow.';
            $alert.removeClass('d-none alert-success').addClass('alert-danger').text(msg);
            $btn.prop('disabled', false).text('Add as Fellow');
        });
    });
});
</script>
@endpush

@push('scripts')
<script src="{{ asset('js/vendor/qrcode-generator-1.4.4.min.js') }}"></script>
<script src="{{ asset('js/vendor/jszip-3.10.1.min.js') }}"></script>
<script src="{{ asset('js/fellow-qr.js') }}"></script>
<script>
// Certificate QR codes for a year: FCS candidates who passed Part I, plus the Fellows by Election of that year. The list
// is plain text; a name's QR appears while hovering it. The QR codes are drawn here in the browser; downloads are a
// >= 4000 px PNG and a vector SVG per person, or those currently listed in one ZIP.
(function () {
    var batchUrl = @json(url('admin/associates/fellows/certificate-batch')),
        profile  = { fellow: @json(url('admin/associates/fellows/view')), candidate: @json(url('admin/associates/candidates/view')) },
        verifyBase = @json(rtrim(config('app.url'), '/') . '/verify/');
    var $body = $('#qrBody'), $show = $('#qrShow'), $hover = $('#qrHover'), people = [], year = '', made = {}, hideTimer = null;

    function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function qrOf(p) { return made[p.key] || (made[p.key] = FellowQR.make(verifyBase + p.token)); }   // drawn only when needed
    function typeBadge(t, kind) {
        var c = /election/i.test(t) ? ['#fff4d6', '#8a6100'] : (kind === 'candidate' ? ['#e6f6ec', '#1d7a41'] : ['#e3f0fb', '#1a5d96']);
        return '<span class="badge" style="background:' + c[0] + ';color:' + c[1] + ';font-weight:600;font-size:.76rem;padding:.35em .6em;">' + esc(t) + '</span>';
    }
    function when(t) { return t ? t.replace(/:\d\d$/, '') : ''; }
    function personOf(el) { var k = $(el).closest('tr').data('key'); return people.filter(function (p) { return p.key === k; })[0]; }
    function fileName(p, used) {
        var b = FellowQR.slug(p.name) + '_' + FellowQR.slug(p.type), n = b, i = 2;
        while (used && used[n]) { n = b + '-' + (i++); }
        if (used) used[n] = 1;
        return n;
    }

    // ── the list, with search and filters ──
    function shown() {
        var q = $.trim($('#qrSearch').val()).toLowerCase(), t = $('#qrType').val(), g = $('#qrProg').val();
        return people.filter(function (p) {
            return (!t || p.type === t) && (!g || p.trained_in === g) && (!q || (p.name + ' ' + (p.country || '')).toLowerCase().indexOf(q) !== -1);
        });
    }
    function draw() {
        var list = shown(), rows = list.map(function (p, i) {
            return '<tr data-key="' + p.key + '"><td class="text-muted">' + (i + 1) + '</td>' +
                '<td><a href="' + esc(profile[p.kind]) + '/' + p.id + '" class="qr-name" style="color:#222;font-weight:600;">' + esc(p.name) + '</a></td>' +
                '<td>' + typeBadge(p.type, p.kind) + '</td>' +
                '<td>' + esc(p.trained_in || '—') + '</td><td>' + esc(p.country || '—') + '</td>' +
                '<td class="small text-muted">' + esc(p.basis) + '</td>' +
                '<td class="text-center" title="' + (p.last_scan ? 'Last scanned ' + esc(when(p.last_scan)) : 'Not scanned yet') + '">' + (p.scans ? '<strong style="color:#2e8b57;">' + p.scans + '</strong>' : '<span class="text-muted">0</span>') + '</td>' +
                '<td class="text-nowrap"><button type="button" class="btn btn-xs btn-outline-secondary qr-png" title="PNG, 4000 px or more"><i class="fas fa-download"></i> PNG</button> ' +
                '<button type="button" class="btn btn-xs btn-outline-secondary qr-svg" title="Vector, sharp at any size"><i class="fas fa-download"></i> SVG</button> ' +
                '<button type="button" class="btn btn-xs btn-outline-secondary qr-peek" title="Show the QR"><i class="fas fa-qrcode"></i></button></td></tr>';
        }).join('');
        $('#qrRows').html(rows || '<tr><td colspan="8" class="text-center text-muted py-3">Nobody matches.</td></tr>');
        $('#qrCount').text(list.length === people.length ? people.length + ' people' : list.length + ' of ' + people.length);
        $('#qrZipPng, #qrZipSvg').prop('disabled', !list.length);
    }
    function render(data) {
        people = data.fellows || []; year = data.year; made = {};
        $hover.hide();
        if (!people.length) {
            $body.html('<p class="text-muted mb-0 py-2">Nobody yet: no FCS candidate has a Part I pass in ' + esc(year) + ', and no Fellow by Election has fellowship year ' + esc(year) + '.</p>').removeClass('d-none');
            return;
        }
        var nc = people.filter(function (p) { return p.kind === 'candidate'; }).length, nf = people.length - nc;
        var types = {}, progs = {};
        people.forEach(function (p) { types[p.type] = 1; if (p.trained_in) progs[p.trained_in] = 1; });
        function opts(o) { return Object.keys(o).sort().map(function (k) { return '<option value="' + esc(k) + '">' + esc(k) + '</option>'; }).join(''); }
        $body.html(
            '<p class="small text-muted mb-2"><strong>' + people.length + ' people for ' + esc(year) + '</strong>: ' + nc + ' FCS candidate' + (nc === 1 ? '' : 's') + ' who passed Part I, and ' + nf +
            ' Fellow' + (nf === 1 ? '' : 's') + ' by Election. <strong>Hover a name to see their QR.</strong> Each QR opens that person\'s public verification page.</p>' +
            '<div class="d-flex align-items-center flex-wrap mb-2" style="gap:.5rem;">' +
              '<input type="search" id="qrSearch" class="form-control form-control-sm" style="width:210px;" placeholder="Search name or country">' +
              '<select id="qrType" class="form-control form-control-sm" style="width:auto;"><option value="">All types</option>' + opts(types) + '</select>' +
              '<select id="qrProg" class="form-control form-control-sm" style="width:auto;"><option value="">All programmes</option>' + opts(progs) + '</select>' +
              '<small class="text-muted" id="qrCount"></small>' +
              '<span class="ml-auto"><button type="button" class="btn btn-sm btn-outline-success" id="qrZipPng" title="Everything listed, one PNG each (4000 px or more), as one ZIP; can be large"><i class="fas fa-file-archive mr-1"></i>ZIP of PNGs</button> ' +
              '<button type="button" class="btn btn-sm btn-outline-success" id="qrZipSvg" title="Everything listed, one vector SVG each, as one ZIP"><i class="fas fa-file-archive mr-1"></i>ZIP of SVGs</button></span>' +
            '</div>' +
            '<div style="max-height:62vh;overflow:auto;border:1px solid #eee;border-radius:6px;"><table class="table table-sm table-hover mb-0" id="qrTable"><thead style="position:sticky;top:0;background:#f7f8fa;z-index:1;"><tr><th>#</th><th>Name</th><th>Type</th><th>Programme</th><th>Country</th><th>Included because</th><th class="text-center">Scans</th><th>Download</th></tr></thead><tbody id="qrRows"></tbody></table></div>'
        ).removeClass('d-none');
        draw();
    }

    $show.on('click', function () {
        $show.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading…');
        $.getJSON(batchUrl, { year: $('#qrYear').val() })
            .done(render)
            .fail(function (xhr) { $body.html('<div class="alert alert-danger mb-0">' + esc((xhr.responseJSON && xhr.responseJSON.message) || 'The list could not be loaded.') + '</div>').removeClass('d-none'); })
            .always(function () { $show.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i>Refresh'); });
    });
    $body.on('input change', '#qrSearch, #qrType, #qrProg', draw);

    // ── the QR while hovering a name (or after tapping the QR button, for touch screens) ──
    function showHover(el, p) {
        clearTimeout(hideTimer);
        $('#qrHoverQr').html(qrOf(p).svg()); $('#qrHoverName').text(p.name); $('#qrHoverType').text([p.type, p.trained_in].filter(Boolean).join(' · '));
        $hover.show();
        var r = el.getBoundingClientRect(), w = $hover.outerWidth(), h = $hover.outerHeight(), x = r.right + 14, y = r.top + r.height / 2 - h / 2;
        if (x + w > window.innerWidth - 8) x = Math.max(8, r.left - w - 14);
        $hover.css({ left: x + 'px', top: Math.min(Math.max(8, y), window.innerHeight - h - 8) + 'px' });
    }
    function hideHover() { hideTimer = setTimeout(function () { $hover.hide(); }, 80); }
    $body.on('mouseenter', '.qr-name', function () { showHover(this, personOf(this)); });
    $body.on('mouseleave', '.qr-name', hideHover);
    $body.on('click', '.qr-peek', function (e) {
        e.stopPropagation();
        var p = personOf(this), shownNow = $hover.is(':visible') && $('#qrHoverName').text() === p.name;
        if (shownNow) { $hover.hide(); } else { showHover($(this).closest('tr').find('.qr-name')[0], p); }
    });
    $(document).on('click', function () { $hover.hide(); });
    $body.on('scroll', function () { $hover.hide(); });

    // ── downloads ──
    $body.on('click', '.qr-png', function () {
        var p = personOf(this), btn = $(this).prop('disabled', true);
        qrOf(p).png().then(function (b) { FellowQR.save(b, fileName(p) + '.png'); }).finally(function () { btn.prop('disabled', false); });
    });
    $body.on('click', '.qr-svg', function () {
        var p = personOf(this);
        FellowQR.save(new Blob([qrOf(p).svg(1024)], { type: 'image/svg+xml' }), fileName(p) + '.svg');
    });

    // Everything currently listed in one ZIP (with a CSV of who and which link): PNGs (large) or SVGs (small).
    async function zipListed(kind, $btn) {
        var list = shown(), label = $btn.html(), z = new JSZip(), used = {}, csv = [['Name', 'Type', 'Programme', 'Country', 'Included because', 'Verification link', 'File']];
        if (kind === 'png' && list.length > 60 && !confirm(list.length + ' PNGs of 4000 px each make a large ZIP (roughly ' + Math.round(list.length * 0.35) + ' MB) and may take a minute. Continue?\n\nTip: the SVG ZIP is tiny and prints sharper.')) return;
        $('#qrZipPng, #qrZipSvg').prop('disabled', true);
        try {
            for (var i = 0; i < list.length; i++) {
                var p = list[i], base = fileName(p, used), q = qrOf(p);
                $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>' + (i + 1) + ' of ' + list.length + '…');
                if (kind === 'png') { z.file(base + '.png', await q.png()); } else { z.file(base + '.svg', q.svg(1024)); }
                csv.push([p.name, p.type, p.trained_in, p.country, p.basis, verifyBase + p.token, base + '.' + kind].map(function (v) { return '"' + String(v == null ? '' : v).replace(/"/g, '""') + '"'; }));
            }
            z.file('qr-codes.csv', '\ufeff' + csv.map(function (r) { return r.join(','); }).join('\r\n'));
            FellowQR.save(await z.generateAsync({ type: 'blob', compression: 'STORE' }), 'cosecsa-certificate-qr-' + year + '-' + kind + '.zip');
        } catch (e) {
            alert('The ZIP could not be made: ' + (e && e.message ? e.message : e));
        }
        $btn.html(label);
        $('#qrZipPng, #qrZipSvg').prop('disabled', !shown().length);
    }
    $body.on('click', '#qrZipPng', function () { zipListed('png', $(this)); });
    $body.on('click', '#qrZipSvg', function () { zipListed('svg', $(this)); });
})();
</script>
@endpush

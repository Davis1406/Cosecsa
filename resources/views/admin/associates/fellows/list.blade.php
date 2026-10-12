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

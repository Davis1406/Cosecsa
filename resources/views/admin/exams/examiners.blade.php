@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-lg-4 d-flex align-items-center flex-wrap mb-2 mb-lg-0" style="gap:.5rem;">
                    <h4 class="mb-0">Examiners</h4>
                    {{-- Year filter (server-side page reload) --}}
                    <form method="GET" action="{{ url('admin/exams/examiners') }}" class="d-flex align-items-center" style="gap:.3rem;">
                        <select name="year_id" class="form-control form-control-sm" style="max-width:140px;"
                                onchange="this.form.submit()">
                            <option value="" {{ $noYearSelected ? 'selected' : '' }}>— All Years —</option>
                            @foreach($allExamYears as $yr)
                                <option value="{{ $yr->id }}" {{ !$noYearSelected && $selectedYearId == $yr->id ? 'selected' : '' }}>
                                    {{ $yr->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-lg-8 d-flex flex-wrap justify-content-lg-end" style="gap:.4rem;">
                    <a href="{{ url('admin/exams/visual_report') }}{{ $noYearSelected ? '' : '?year_id='.$selectedYearId }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-chart-pie mr-1"></i> Visual Report
                    </a>
                    <a href="{{ url('admin/exams/import') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </a>
                    <a href="{{ route('exams.upload.confirmation') }}" class="btn btn-sm btn-info">
                        <i class="fas fa-clipboard-check mr-1"></i> Upload Confirmation
                    </a>
                    <a href="{{ route('examiners.bulk.upload.docs') }}" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-upload mr-1"></i> Bulk CV/Photo
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            data-toggle="modal" data-target="#addExaminerFromAssociateModal">
                        <i class="fas fa-user-friends mr-1"></i> Add Examiner from Associate
                    </button>
                    <a href="{{ url('admin/exams/add_examiner') }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-plus mr-1"></i> Add Examiner
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="col-md-12">@include('_message')</div>

    <section class="content">
        <div class="container-wrapper">
            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-header flex-wrap" style="gap:.5rem;">
                            {{-- Row 1: participant toggle + email actions --}}
                            <div class="d-flex align-items-center flex-wrap mb-2" style="gap:.5rem;">
                                <div class="btn-group btn-group-sm mr-auto" role="group">
                                    <button class="btn btn-outline-secondary active" id="btn-all">
                                        All <span class="badge badge-secondary ml-1">{{ $getExaminers->count() }}</span>
                                    </button>
                                    <button class="btn btn-outline-primary" id="btn-lastyear">
                                        {{ $lastYear }} Participants
                                        <span class="badge badge-primary ml-1">{{ $getExaminers->where('participated_last_year', true)->count() }}</span>
                                    </button>
                                </div>
                                <div class="d-flex" style="gap:.4rem;">
                                    <a href="{{ route('exams.email.template') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-pencil-alt mr-1"></i> Edit Template
                                    </a>
                                    <div class="chk-filter-wrap" data-filter="col-vis">
                                        <button type="button" class="btn btn-sm btn-outline-secondary chk-filter-btn" data-filter="col-vis">
                                            <i class="fas fa-columns mr-1"></i> Columns
                                            <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                                        </button>
                                        <div class="chk-filter-panel shadow" id="col-vis-panel" style="display:none;min-width:160px;">
                                            <div class="chk-list">
                                                <label class="chk-item">
                                                    <input type="checkbox" id="col-toggle-shift"> Shift
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-success" id="btn-email-selected" disabled
                                            data-toggle="modal" data-target="#emailModal">
                                        <i class="fas fa-envelope mr-1"></i>
                                        Email Selected <span id="sel-count" class="badge badge-light ml-1">0</span>
                                    </button>
                                </div>
                            </div>
                            {{-- Row 2: filters --}}
                            @php
                            $examFilterDefs = [
                                ['id'=>'filter-programme',   'label'=>'Specialty',    'options'=>collect($programmes)],
                                ['id'=>'filter-country',     'label'=>'Country',      'options'=>collect($countries)],
                                ['id'=>'filter-designation', 'label'=>'Designation',  'options'=>collect($designationOptions)],
                                ['id'=>'filter-status',      'label'=>'Status',       'options'=>collect(['Active','Inactive','Deceased'])],
                                ['id'=>'filter-role',        'label'=>'Role',         'options'=>collect($roleOptions)],
                            ];
                            @endphp
                            <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                                @foreach($examFilterDefs as $fd)
                                <div class="chk-filter-wrap" data-filter="{{ $fd['id'] }}">
                                    <button type="button" class="btn btn-sm btn-outline-secondary chk-filter-btn" data-filter="{{ $fd['id'] }}">
                                        {{ $fd['label'] }}
                                        <span class="badge badge-danger chk-badge ml-1" style="display:none;font-size:.65rem;"></span>
                                        <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                                    </button>
                                    <div class="chk-filter-panel shadow" id="{{ $fd['id'] }}-panel" style="display:none;">
                                        @if($fd['options']->count() > 6)
                                        <input type="text" class="form-control form-control-sm chk-search mb-1" placeholder="Search…" autocomplete="off">
                                        @endif
                                        <div class="chk-list">
                                            @foreach($fd['options'] as $opt)
                                            <label class="chk-item">
                                                <input type="checkbox" class="chk-option" data-filter="{{ $fd['id'] }}" value="{{ $opt }}">
                                                {{ $opt }}
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
                                <button id="btn-clear-filters" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times mr-1"></i> Clear
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            {{-- Rows are built client-side from this JSON (see custom.js #examinerstable). --}}
                            <script type="application/json" id="examinersRows">{!! json_encode($getExaminers->values(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                            <table id="examinerstable" class="table table-bordered table-striped"
                                   data-app-url="{{ url('') }}"
                                   data-csrf="{{ csrf_token() }}"
                                   data-from="{{ request()->path() }}"
                                   data-can-delete="{{ Auth::user()->isSuperAdmin() ? 1 : 0 }}">
                                <thead>
                                    <tr>
                                        <th style="width:36px;">
                                            <input type="checkbox" id="chk-all" title="Select all visible">
                                        </th>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Country</th>
                                        <th>Examiner ID</th>
                                        <th>Specialty</th>
                                        <th>Designation</th>
                                        <th id="col-shift-header">Shift</th>
                                        <th style="width:160px;">Notes</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div>

{{-- ── Email Modal ─────────────────────────────────────────────────────── --}}
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('examiners.bulk.email') }}" id="emailForm">
                @csrf
                <div class="modal-header" style="background:#a02626; color:#fff;">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope mr-2"></i>
                        Send Email to Selected Examiners
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="recipient-pills" class="mb-3" style="max-height:120px; overflow-y:auto;"></div>

                    <div class="form-group">
                        <label><strong>From</strong></label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }} ({{ config('mail.from.address') }})" readonly
                               style="background:#f8f9fa; color:#6c757d;">
                        <small class="text-muted">Sent as you — replies from examiners will go to your own email ({{ Auth::user()->email }}).</small>
                    </div>
                    <div class="form-group">
                        <label><strong>Subject</strong> <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="modal-subject" class="form-control"
                               value="{{ optional(DB::table('email_templates')->where('key','examiner_bulk')->first())->subject }}"
                               required>
                    </div>
                    <div class="form-group">
                        <label>
                            <strong>Message</strong> <span class="text-danger">*</span>
                            <small class="text-muted ml-2">(HTML from template — edit via
                                <a href="{{ route('exams.email.template') }}" target="_blank">Edit Template</a>)
                            </small>
                        </label>
                        <textarea name="body" id="modal-body" class="form-control" rows="8" required>{{ optional(DB::table('email_templates')->where('key','examiner_bulk')->first())->body }}</textarea>
                        <small class="text-muted"><code>[Name]</code> will be replaced with each examiner's name automatically.</small>
                    </div>

                    {{-- Hidden inputs for selected examiner IDs injected by JS --}}
                    <div id="hidden-ids"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="send-btn">
                        <i class="fas fa-paper-plane mr-1"></i>
                        Send to <span id="send-count">0</span> examiner(s)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Add Examiner from Associate: someone who already has a login (Fellow, Country Rep,
     Member, ...) gets an examiners record on that login instead of a duplicate account.
     Afterwards they can be confirmed for this year (a specialty, or tentative) and sent the
     online-course login, or just left on the list. Mirrors "Add Fellow from Associate". --}}
<div class="modal fade" id="addExaminerFromAssociateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="addExaminerFromAssociateForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-friends mr-1"></i> Add Examiner from Associate</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="aeaAlert" class="alert d-none" role="alert"></div>

                <div class="form-group">
                    <label>Search In</label>
                    <select class="form-control" id="aea_type" name="source_type">
                        <option value="fellow">Fellows</option>
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
                    <input type="text" class="form-control" id="aea_search" placeholder="Type a name or email...">
                    <div class="list-group mt-1 d-none" id="aea_results" style="max-height:180px; overflow-y:auto;"></div>
                    <div class="d-none mt-2" id="aea_selected"></div>
                    <input type="hidden" id="aea_source_id" name="source_id">
                </div>

                <div class="d-none" id="aea_fields">
                    <hr>
                    <p class="small text-muted mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        The Examiner ID is assigned automatically &mdash; the next number after the last examiner
                        (<strong id="aea_next_id">…</strong>). They keep the login they already have.
                    </p>

                    <div class="form-group">
                        <label>Specialty</label>
                        <select class="form-control" name="specialty" id="aea_specialty">
                            <option value="">Select…</option>
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label class="d-block">Confirm for {{ date('Y') }}?</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="aea_confirm_none" name="confirm_as" value="" checked>
                            <label class="custom-control-label" for="aea_confirm_none">No &mdash; just add to the examiners list</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="aea_confirm_specialty" name="confirm_as" value="specialty">
                            <label class="custom-control-label" for="aea_confirm_specialty">Yes &mdash; confirmed with a specialty</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="aea_confirm_tentative" name="confirm_as" value="tentative">
                            <label class="custom-control-label" for="aea_confirm_tentative">Yes &mdash; tentative</label>
                        </div>
                    </div>

                    <div class="d-none form-group" id="aea_availability">
                        <label class="d-block">Available for <span class="text-danger">*</span></label>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="aea_mcs" name="exam_availability[]" value="MCS">
                            <label class="custom-control-label" for="aea_mcs">MCS</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="aea_fcs" name="exam_availability[]" value="FCS">
                            <label class="custom-control-label" for="aea_fcs">FCS</label>
                        </div>
                    </div>

                    <div class="d-none" id="aea_share">
                        <hr>
                        <label class="d-block">Share the online course login by email?</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="aea_share_no" name="aea_share" value="no" checked>
                            <label class="custom-control-label" for="aea_share_no">No &mdash; leave them on the list only</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="aea_share_yes" name="aea_share" value="yes">
                            <label class="custom-control-label" for="aea_share_yes">Yes &mdash; email them their login</label>
                        </div>
                        <div class="d-none mt-2" id="aea_share_fields">
                            <select class="form-control form-control-sm" id="aea_draft"></select>
                            <p class="small text-muted mt-1 mb-0">
                                A new random password is generated and set on their login, then emailed (same as the other examiners).
                                If they are also a fellow, this is the password they use there too.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" id="aeaSubmit" disabled>Add as Examiner</button>
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
    #examinerstable td { vertical-align: middle; }
    .action-btn { padding: 2px 8px; line-height: 1.4; border-radius: 4px; }
    .dropdown-menu { min-width: 130px; font-size: .875rem; }
    .dropdown-item { padding: 6px 14px; }
    .dropdown-item:hover { background-color: #f8f0f0; }
    .paginate_button.active>.page-link { background-color: #a02626 !important; border-color: #a02626 !important; color: white; }
    .paginate_button>.page-link { color: #a02626; }
    .paginate_button>.page-link:focus, .paginate_button.active>.page-link:focus { box-shadow: none !important; outline: none !important; }

    /* 2025 badge */
    .badge-primary { font-size: .72rem; }

    /* Recipient pills */
    .recipient-pill {
        display: inline-block;
        background: #e9f0fb;
        border: 1px solid #b8d0f5;
        border-radius: 999px;
        padding: 2px 10px;
        font-size: .8rem;
        margin: 2px 3px;
        color: #0055a5;
    }

    /* Highlight last-year rows faintly when filter active */
    #examinerstable.filter-lastyear tr.examiner-row:not(.last-year-row) { opacity: .3; }
</style>
@endpush

@push('scripts')
<script>
$(function () {

    // custom.js already initialised #examinerstable — just get the instance.
    var table = $('#examinerstable').DataTable();

    // ── Column visibility: Shift (column index 8, hidden by default in custom.js) ──
    var SHIFT_COL = 8;

    $('#col-toggle-shift').on('change', function () {
        table.column(SHIFT_COL).visible(this.checked);
    });

    // ── Active filters ────────────────────────────────────────────────────────
    var filterMode = 'all';   // 'all' | 'lastyear'

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

    // ── Participant toggle buttons ─────────────────────────────────────────────
    $('#btn-all').on('click', function () {
        filterMode = 'all';
        $(this).addClass('active').siblings().removeClass('active');
        $('#examinerstable').removeClass('filter-lastyear');
        table.draw();
        syncSelectAll();
    });

    $('#btn-lastyear').on('click', function () {
        filterMode = 'lastyear';
        $(this).addClass('active').siblings().removeClass('active');
        $('#examinerstable').addClass('filter-lastyear');
        table.draw();
        $('#examinerstable tbody tr.examiner-row:not(.last-year-row) .row-chk').prop('checked', false);
        updateEmailButton();
        syncSelectAll();
    });

    // ── Checkbox filter panel open/close ──────────────────────────────────────
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
        updateBadge($(this).data('filter'));
        table.draw();
        syncSelectAll();
    });

    // Select All / Clear per panel
    $(document).on('click', '.chk-select-all', function (e) {
        e.preventDefault();
        var $panel = $(this).closest('.chk-filter-panel');
        $panel.find('.chk-item:visible .chk-option').prop('checked', true);
        updateBadge($panel.closest('.chk-filter-wrap').data('filter'));
        table.draw(); syncSelectAll();
    });
    $(document).on('click', '.chk-clear', function (e) {
        e.preventDefault();
        var $panel   = $(this).closest('.chk-filter-panel');
        var filterId = $panel.closest('.chk-filter-wrap').data('filter');
        $panel.find('.chk-option').prop('checked', false);
        updateBadge(filterId);
        table.draw(); syncSelectAll();
    });

    $('#btn-clear-filters').on('click', function () {
        filterMode = 'all';
        $('#btn-all').addClass('active').siblings().removeClass('active');
        $('#examinerstable').removeClass('filter-lastyear');
        $('.chk-option').prop('checked', false);
        $('.chk-badge').hide();
        table.draw();
        syncSelectAll();
    });

    // ── Unified DataTable search extension ────────────────────────────────────
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, row) {
        if (settings.nTable.id !== 'examinerstable') return true;

        // Last-year participant filter
        if (filterMode === 'lastyear' && !row.participated_last_year) return false;

        // Programme — substring OR-match against data-programmes
        var chkProgramme = getChecked('filter-programme');
        if (chkProgramme.length) {
            var allProgs = (row.specialty || '').toLowerCase();
            var anyMatch = chkProgramme.some(function (p) { return allProgs.indexOf(p.toLowerCase()) !== -1; });
            if (!anyMatch) return false;
        }

        // Country — exact match against column 4
        var chkCountry = getChecked('filter-country');
        if (chkCountry.length && chkCountry.indexOf((data[4] || '').trim()) === -1) return false;

        // Designation — exact match from data-desig
        var chkDesig = getChecked('filter-designation');
        if (chkDesig.length && chkDesig.indexOf((row.examiner_designation || '').trim()) === -1) return false;

        // Status — exact match from data-status
        var chkStatus = getChecked('filter-status');
        if (chkStatus.length && chkStatus.indexOf(row.status || 'Active') === -1) return false;

        // Role — case-insensitive from data-role
        var chkRole = getChecked('filter-role');
        if (chkRole.length) {
            var rowRole = (row.role_name || '').trim().toLowerCase();
            if (!chkRole.some(function (r) { return r.toLowerCase() === rowRole; })) return false;
        }

        return true;
    });

    // ── Cross-page checkbox selection ─────────────────────────────────────────
    // selectedIds: Map of exmId → {email, name} — persists across DT page changes
    var selectedIds = {};

    function updateEmailButton() {
        var n = Object.keys(selectedIds).length;
        $('#sel-count').text(n);
        $('#send-count').text(n);
        $('#btn-email-selected').prop('disabled', n === 0);
    }

    function syncSelectAll() {
        var $visible = $('#examinerstable tbody tr:visible .row-chk');
        var allChecked = $visible.length > 0 && $visible.filter(':not(:checked)').length === 0;
        $('#chk-all').prop('checked', allChecked);
    }

    // Restore checkboxes after each DataTable page draw
    table.on('draw', function () {
        $('#examinerstable tbody .row-chk').each(function () {
            var id = $(this).val();
            $(this).prop('checked', selectedIds.hasOwnProperty(id));
        });
        syncSelectAll();
        updateEmailButton();
    });

    // Individual row checkbox change
    $('#examinerstable').on('change', '.row-chk', function () {
        var id    = $(this).val();
        var email = $(this).data('email');
        var name  = $(this).data('name');
        if (this.checked) {
            selectedIds[id] = { email: email, name: name };
        } else {
            delete selectedIds[id];
        }
        updateEmailButton();
        syncSelectAll();
    });

    // Select-all header checkbox — only affects current page
    $('#chk-all').on('change', function () {
        var checked = this.checked;
        $('#examinerstable tbody tr:visible .row-chk').each(function () {
            var id    = $(this).val();
            var email = $(this).data('email');
            var name  = $(this).data('name');
            $(this).prop('checked', checked);
            if (checked) {
                selectedIds[id] = { email: email, name: name };
            } else {
                delete selectedIds[id];
            }
        });
        updateEmailButton();
    });

    // ── Email modal ───────────────────────────────────────────────────────────
    $('#emailModal').on('show.bs.modal', function () {
        var pills  = '';
        var hidden = '';
        $.each(selectedIds, function (exmId, info) {
            pills  += '<span class="recipient-pill"><i class="fas fa-user mr-1"></i>' + info.name + '</span>';
            hidden += '<input type="hidden" name="examiner_ids[]" value="' + exmId + '">';
        });
        $('#recipient-pills').html(pills || '<em class="text-muted">No recipients selected.</em>');
        $('#hidden-ids').html(hidden);
        $('#send-count').text(Object.keys(selectedIds).length);
    });
});
</script>
@endpush

@push('scripts')
<script>
$(function () {
    // ── Add Examiner from Associate ─────────────────────────────────────────
    var aeaTimer = null, aeaLoaded = false;

    function aeaSync() {
        var confirm = $('input[name="confirm_as"]:checked').val();
        $('#aea_availability').toggleClass('d-none', confirm !== 'specialty');
        $('#aea_share').toggleClass('d-none', !confirm);
        if (!confirm) { $('#aea_share_no').prop('checked', true); }
        $('#aea_share_fields').toggleClass('d-none', !confirm || $('input[name="aea_share"]:checked').val() !== 'yes');
    }

    function aeaResetSelection() {
        $('#aea_source_id').val('');
        $('#aea_selected').addClass('d-none').empty();
        $('#aea_fields').addClass('d-none');
        $('#aeaSubmit').prop('disabled', true);
    }

    function aeaLoadOptions() {
        if (aeaLoaded) return;
        $.get('{{ route("examiners.associate-options") }}').done(function (res) {
            aeaLoaded = true;
            $('#aea_next_id').text(res.next_examiner_id || '');
            var $spec = $('#aea_specialty');
            (res.specialty_options || []).forEach(function (n) { $spec.append($('<option>').val(n).text(n)); });
            var $draft = $('#aea_draft').empty();
            var drafts = res.credential_drafts || [];
            if (!drafts.length) {
                $draft.append('<option value="">No credential email set up (Draft Emails)</option>');
                $('#aea_share_yes').prop('disabled', true);
            }
            drafts.forEach(function (d) { $draft.append($('<option>').val(d.id).text(d.name)); });
        });
    }

    $('#addExaminerFromAssociateModal').on('show.bs.modal', aeaLoadOptions);
    $('#addExaminerFromAssociateModal').on('hidden.bs.modal', function () {
        $('#aea_search').val('');
        $('#aea_results').addClass('d-none').empty();
        $('#aeaAlert').addClass('d-none');
        $('#aea_confirm_none, #aea_share_no').prop('checked', true);
        $('#aea_specialty').val('');
        $('#aea_mcs, #aea_fcs').prop('checked', false);
        aeaResetSelection();
        aeaSync();
        // The next ID moves on once someone is added, so fetch it again next time.
        aeaLoaded = false; $('#aea_specialty').find('option:gt(0)').remove();
    });

    $('#aea_type').on('change', aeaResetSelection);
    $('input[name="confirm_as"], input[name="aea_share"]').on('change', aeaSync);

    $('#aea_search').on('input', function () {
        var q = $(this).val().trim();
        aeaResetSelection();
        clearTimeout(aeaTimer);

        if (q.length < 2) { $('#aea_results').addClass('d-none').empty(); return; }

        aeaTimer = setTimeout(function () {
            $.get('{{ route("examiners.search-associates") }}', { type: $('#aea_type').val(), q: q })
                .done(function (res) {
                    var results = res.results || [];
                    var $list = $('#aea_results').empty();
                    if (!results.length) {
                        $list.append('<div class="list-group-item small text-muted">No matches (or already an examiner).</div>');
                    } else {
                        results.forEach(function (r) {
                            var name = r.name || '';
                            $list.append($('<button type="button" class="list-group-item list-group-item-action"></button>')
                                .text(name + (r.email ? ' — ' + r.email : '') + (r.country ? ' (' + r.country + ')' : ''))
                                .data('id', r.id).data('label', name));
                        });
                    }
                    $list.removeClass('d-none');
                });
        }, 300);
    });

    $(document).on('click', '#aea_results .list-group-item-action', function () {
        $('#aea_source_id').val($(this).data('id'));
        $('#aea_selected').removeClass('d-none')
            .html('<span class="badge badge-secondary p-2"><i class="fas fa-check mr-1"></i>' +
                  $('<div>').text($(this).data('label')).html() + '</span>');
        $('#aea_results').addClass('d-none').empty();
        $('#aea_fields').removeClass('d-none');
        $('#aeaSubmit').prop('disabled', false);
    });

    $('#addExaminerFromAssociateForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#aea_source_id').val()) return;

        var confirm = $('input[name="confirm_as"]:checked').val();
        var share = confirm && $('input[name="aea_share"]:checked').val() === 'yes';
        var $alert = $('#aeaAlert').addClass('d-none');

        if (confirm === 'specialty' && (!$('#aea_specialty').val() || !$('#aea_mcs').is(':checked') && !$('#aea_fcs').is(':checked'))) {
            $alert.removeClass('d-none alert-success').addClass('alert-danger')
                .text('To confirm with a specialty, choose the specialty and at least one of MCS / FCS.');
            return;
        }
        if (share && !$('#aea_draft').val()) {
            $alert.removeClass('d-none alert-success').addClass('alert-danger').text('Choose the email to share, or select No.');
            return;
        }

        var data = $(this).serializeArray().filter(function (f) { return f.name !== 'aea_share'; });
        if (share) data.push({ name: 'draft_email_id', value: $('#aea_draft').val() });

        var $btn = $('#aeaSubmit').prop('disabled', true).text('Adding...');

        $.ajax({ url: '{{ route("examiners.from-associate") }}', method: 'POST', data: $.param(data) })
            .done(function (res) {
                var msg = res.message || 'Examiner added.';
                var failed = res.email && !res.email.sent;
                if (res.email) msg += ' ' + (res.email.message || '');
                $alert.removeClass('d-none alert-danger alert-warning alert-success')
                    .addClass(failed ? 'alert-warning' : 'alert-success').text(msg);
                setTimeout(function () {
                    window.location.href = '{{ url("admin/exams/view_examiner") }}/' + res.examiner_id;
                }, failed ? 3500 : 1200);
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to add examiner.';
                $alert.removeClass('d-none alert-success').addClass('alert-danger').text(msg);
                $btn.prop('disabled', false).text('Add as Examiner');
            });
    });
});
</script>
@endpush

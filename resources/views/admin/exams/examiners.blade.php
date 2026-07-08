@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6 d-flex align-items-center flex-wrap" style="gap:.5rem;">
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
                <div class="col-sm-6 text-right">
                    <a href="{{ url('admin/exams/visual_report') }}{{ $noYearSelected ? '' : '?year_id='.$selectedYearId }}" class="btn btn-sm btn-secondary mr-1">
                        <i class="fas fa-chart-pie mr-1"></i> Visual Report
                    </a>
                    <a href="{{ url('admin/exams/import') }}" class="btn btn-sm btn-warning mr-1">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </a>
                    <a href="{{ route('exams.upload.confirmation') }}" class="btn btn-sm btn-info mr-1">
                        <i class="fas fa-clipboard-check mr-1"></i> Upload Confirmation
                    </a>
                    <a href="{{ route('examiners.bulk.upload.docs') }}" class="btn btn-sm btn-outline-danger mr-1">
                        <i class="fas fa-upload mr-1"></i> Bulk CV/Photo
                    </a>
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
                            <table id="examinerstable" class="table table-bordered table-striped">
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
                                        <th style="width:160px;">Notes</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($getExaminers as $value)
                                    <tr class="examiner-row {{ $value->participated_last_year ? 'last-year-row' : '' }}"
                                        data-desig="{{ $value->examiner_designation ?? '' }}"
                                        data-role="{{ ucfirst($value->role_name ?? '') }}"
                                        data-status="{{ $value->status ?? 'Active' }}"
                                        data-programmes="{{ $value->specialty ?? '' }}">
                                        <td>
                                            <input type="checkbox" class="row-chk"
                                                   value="{{ $value->examin_id }}"
                                                   data-email="{{ $value->email }}"
                                                   data-name="{{ $value->examiner_name }}">
                                        </td>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><a href="{{ url('admin/exams/view_examiner/'.$value->examin_id) }}" style="color:#a02626;font-weight:500;text-decoration:none;">{{ $value->examiner_name }}</a></td>
                                        <td>{{ $value->email }}</td>
                                        <td>@if($value->country_id)<a href="{{ url('admin/countries/view/'.$value->country_id) }}" style="color:#a02626;font-weight:500;text-decoration:none;">{{ $value->country_name }}</a>@else{{ $value->country_name }}@endif</td>
                                        <td>{{ $value->examiner_id }}</td>
                                        <td>{{ $value->specialty ?? '—' }}</td>
                                        <td>
                                            @if(!empty($value->examiner_designation))
                                                <span class="badge badge-pill" style="background:#a02626;color:#fff;font-size:.75rem;">
                                                    {{ $value->examiner_designation }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($value->internal_notes)
                                                @php $notePreview = Str::limit($value->internal_notes, 60); @endphp
                                                <span class="text-muted" style="font-size:.8rem;cursor:default;"
                                                      data-toggle="tooltip" data-placement="left"
                                                      title="{{ e($value->internal_notes) }}">
                                                    {{ $notePreview }}
                                                </span>
                                            @else
                                                <span class="text-muted" style="font-size:.8rem;">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                                    type="button" data-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                    <form action="{{ url("admin/exams/view_examiner/{$value->examin_id}") }}"
                                                          method="POST" style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="from" value="{{ request()->path() }}">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fa fa-eye"></i> View
                                                        </button>
                                                    </form>
                                                    <a href="{{ url('admin/exams/edit_examiner/' . $value->examin_id) }}"
                                                       class="dropdown-item">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>
                                                    <a href="{{ url('admin/exams/delete/' . $value->ex_id) }}"
                                                       class="dropdown-item text-danger"
                                                       onclick="return confirm('Are you sure?')">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
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
                        <input type="text" class="form-control" value="{{ config('mail.from.address') }}" readonly
                               style="background:#f8f9fa; color:#6c757d;">
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
    .badge-primary { background-color: #0055a5; font-size: .72rem; }

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
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'examinerstable') return true;

        var $row = $(table.row(dataIndex).node());

        // Last-year participant filter
        if (filterMode === 'lastyear' && !$row.hasClass('last-year-row')) return false;

        // Programme — substring OR-match against data-programmes
        var chkProgramme = getChecked('filter-programme');
        if (chkProgramme.length) {
            var allProgs = ($row.data('programmes') || '').toLowerCase();
            var anyMatch = chkProgramme.some(function (p) { return allProgs.indexOf(p.toLowerCase()) !== -1; });
            if (!anyMatch) return false;
        }

        // Country — exact match against column 4
        var chkCountry = getChecked('filter-country');
        if (chkCountry.length && chkCountry.indexOf((data[4] || '').trim()) === -1) return false;

        // Designation — exact match from data-desig
        var chkDesig = getChecked('filter-designation');
        if (chkDesig.length && chkDesig.indexOf(($row.data('desig') || '').trim()) === -1) return false;

        // Status — exact match from data-status
        var chkStatus = getChecked('filter-status');
        if (chkStatus.length && chkStatus.indexOf($row.data('status') || 'Active') === -1) return false;

        // Role — case-insensitive from data-role
        var chkRole = getChecked('filter-role');
        if (chkRole.length) {
            var rowRole = ($row.data('role') || '').trim().toLowerCase();
            if (!chkRole.some(function (r) { return r.toLowerCase() === rowRole; })) return false;
        }

        return true;
    });

    // ── Checkbox logic ────────────────────────────────────────────────────────
    function getCheckedRows() {
        return $('#examinerstable tbody .row-chk:checked');
    }

    function updateEmailButton() {
        var n = getCheckedRows().length;
        $('#sel-count').text(n);
        $('#send-count').text(n);
        $('#btn-email-selected').prop('disabled', n === 0);
    }

    function syncSelectAll() {
        var visibleChks = $('#examinerstable tbody tr:visible .row-chk');
        var allChecked  = visibleChks.length > 0 && visibleChks.filter(':not(:checked)').length === 0;
        $('#chk-all').prop('checked', allChecked);
    }

    $('#chk-all').on('change', function () {
        var checked = this.checked;
        $('#examinerstable tbody tr:visible .row-chk').prop('checked', checked);
        updateEmailButton();
    });

    $('#examinerstable').on('change', '.row-chk', function () {
        updateEmailButton();
        syncSelectAll();
    });

    // Re-sync after DataTable page/search change
    table.on('draw', function () {
        updateEmailButton();
        syncSelectAll();
    });

    // ── Email modal ───────────────────────────────────────────────────────────
    $('#emailModal').on('show.bs.modal', function () {
        var pills  = '';
        var hidden = '';
        getCheckedRows().each(function () {
            var name  = $(this).data('name');
            var exmId = $(this).val();
            pills  += '<span class="recipient-pill"><i class="fas fa-user mr-1"></i>' + name + '</span>';
            hidden += '<input type="hidden" name="examiner_ids[]" value="' + exmId + '">';
        });
        $('#recipient-pills').html(pills || '<em class="text-muted">No recipients selected.</em>');
        $('#hidden-ids').html(hidden);
        $('#send-count').text(getCheckedRows().length);
    });
});
</script>
@endpush

@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="mb-0">Examiners — {{ $currentYear }}</h4>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ url('admin/exams/import') }}" class="btn btn-sm btn-warning mr-1">
                        <i class="fas fa-upload mr-1"></i> Upload
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

                        <div class="card-header d-flex align-items-center flex-wrap" style="gap:.5rem;">
                            {{-- Filter buttons --}}
                            <div class="btn-group btn-group-sm mr-auto" role="group">
                                <button class="btn btn-outline-secondary active" id="btn-all">
                                    All <span class="badge badge-secondary ml-1">{{ $getExaminers->count() }}</span>
                                </button>
                                <button class="btn btn-outline-primary" id="btn-lastyear">
                                    {{ $lastYear }} Participants
                                    <span class="badge badge-primary ml-1">{{ $getExaminers->where('participated_last_year', true)->count() }}</span>
                                </button>
                            </div>

                            {{-- Email selected button --}}
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
                                        <th>Exam Group</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($getExaminers as $value)
                                    <tr class="examiner-row {{ $value->participated_last_year ? 'last-year-row' : '' }}">
                                        <td>
                                            <input type="checkbox" class="row-chk"
                                                   value="{{ $value->examin_id }}"
                                                   data-email="{{ $value->email }}"
                                                   data-name="{{ $value->examiner_name }}">
                                        </td>
                                        <td>{{ $value->id }}</td>
                                        <td>
                                            {{ $value->examiner_name }}
                                            @if($value->participated_last_year)
                                                <span class="badge badge-primary ml-1" title="{{ $lastYear }} participant">{{ $lastYear }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $value->email }}</td>
                                        <td>{{ $value->country_name }}</td>
                                        <td>{{ $value->examiner_id }}</td>
                                        <td>{{ $value->group_name }}</td>
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
                        <input type="text" class="form-control" value="exams_asst@cosecsa.org" readonly
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

    // ── Filter buttons ────────────────────────────────────────────────────────
    var filterMode = 'all';

    $('#btn-all').on('click', function () {
        filterMode = 'all';
        $(this).addClass('active').siblings().removeClass('active');
        $('#examinerstable').removeClass('filter-lastyear');
        table.rows().every(function () {
            $(this.node()).show();
        });
        table.draw();
        syncSelectAll();
    });

    $('#btn-lastyear').on('click', function () {
        filterMode = 'lastyear';
        $(this).addClass('active').siblings().removeClass('active');
        $('#examinerstable').addClass('filter-lastyear');
        table.draw();
        // Deselect any hidden rows
        $('#examinerstable tbody tr.examiner-row:not(.last-year-row) .row-chk').prop('checked', false);
        updateEmailButton();
        syncSelectAll();
    });

    // Custom DataTable search to honour last-year filter
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, row) {
        if (filterMode === 'lastyear') {
            return $(table.row(dataIndex).node()).hasClass('last-year-row');
        }
        return true;
    });

    // ── Checkbox logic ────────────────────────────────────────────────────────
    function getChecked() {
        return $('#examinerstable tbody .row-chk:checked');
    }

    function updateEmailButton() {
        var n = getChecked().length;
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
        getChecked().each(function () {
            var name  = $(this).data('name');
            var exmId = $(this).val();
            pills  += '<span class="recipient-pill"><i class="fas fa-user mr-1"></i>' + name + '</span>';
            hidden += '<input type="hidden" name="examiner_ids[]" value="' + exmId + '">';
        });
        $('#recipient-pills').html(pills || '<em class="text-muted">No recipients selected.</em>');
        $('#hidden-ids').html(hidden);
        $('#send-count').text(getChecked().length);
    });
});
</script>
@endpush

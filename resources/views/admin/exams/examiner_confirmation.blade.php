@extends('layout.app')

@push('styles')
    <style>
        .action-icon {
            display: block;
            padding: 2px 0;
            color: #333;
            font-size: 14px;
            text-decoration: none;
        }

        .action-icon:hover {
            color: #a02626;
            text-decoration: none;
        }

        .popover {
            min-width: 100px;
        }

        .report-buttons {
            margin-bottom: 20px;
        }

        .report-btn {
            background-color: #a02626;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            font-size: 14px;
        }

        .report-btn:hover {
            background-color: #8b1f1f;
            color: white;
            text-decoration: none;
        }

        .report-btn i {
            margin-right: 5px;
        }

        .export-btn {
            background-color: #28a745;
        }

        .export-btn:hover {
            background-color: #218838;
        }

        .filter-bar {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 18px;
        }

        .filter-bar .filter-label {
            font-size: 11px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 4px;
            display: block;
        }

        .filter-bar select {
            font-size: 13px;
        }

        .filter-bar .btn-clear {
            background: #fff;
            border: 1px solid #ced4da;
            color: #495057;
            font-size: 13px;
        }

        .filter-bar .btn-clear:hover {
            background: #a02626;
            border-color: #a02626;
            color: #fff;
        }

        .filter-badge {
            font-size: 11px;
            background: #a02626;
            color: #fff;
            border-radius: 10px;
            padding: 1px 7px;
            margin-left: 4px;
            display: none;
        }

        .badge-source-self  { background:#1565c0; color:#fff; font-size:10px; padding:2px 7px; border-radius:10px; white-space:nowrap; }
        .badge-source-admin { background:#e65100; color:#fff; font-size:10px; padding:2px 7px; border-radius:10px; white-space:nowrap; }
        .badge-email-opened { background:#2e7d32; color:#fff; font-size:10px; padding:2px 7px; border-radius:10px; white-space:nowrap; }
        .badge-email-sent   { background:#f9a825; color:#333; font-size:10px; padding:2px 7px; border-radius:10px; white-space:nowrap; }
        .badge-email-none   { color:#aaa; font-size:12px; }

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
    </style>
@endpush

@section('content')
    <div class="wrapper">

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">

            <!-- Content Header (Page header) -->
            <section class="content-header">
            </section>

            <div class="col-md-12">
                @include('_message')
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-wrapper">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Confirmed Examiners - {{ now()->year }}</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <!-- Report Buttons -->
                                    <div class="report-buttons">
                                        <a href="{{ url('admin/exams/visual_report') }}" class="report-btn">
                                            <i class="fa fa-chart-bar"></i> Visual Report
                                        </a>
                                    </div>

                                    @php
                                        $specialties = $getExaminers->pluck('specialty')->filter()->unique()->sort()->values();
                                        $countries   = $getExaminers->pluck('country_name')->filter()->unique()->sort()->values();
                                    @endphp

                                    <!-- Filter Bar -->
                                    @php
                                    $confFilterDefs = [
                                        ['id'=>'filter-specialty',    'label'=>'Specialty',    'options'=>$specialties,  'optLabels'=>[]],
                                        ['id'=>'filter-availability', 'label'=>'Availability', 'options'=>collect(['MCS','FCS','MCS+FCS','Not Available']), 'optLabels'=>[]],
                                        ['id'=>'filter-shift',        'label'=>'MCS Shift',    'options'=>collect(['1','2','3','0']), 'optLabels'=>['1'=>'Morning','2'=>'Morning & Afternoon','3'=>'Afternoon','0'=>'No Shift']],
                                        ['id'=>'filter-participation','label'=>'Participation','options'=>collect(['Examiner','Observer','None']), 'optLabels'=>[]],
                                        ['id'=>'filter-country',      'label'=>'Country',      'options'=>$countries,    'optLabels'=>[]],
                                        ['id'=>'filter-source',       'label'=>'Source',       'options'=>collect(['self','admin']), 'optLabels'=>['self'=>'Self-submitted','admin'=>'Admin-entered']],
                                        ['id'=>'filter-email',        'label'=>'Email Status', 'options'=>collect(['opened','sent','none']), 'optLabels'=>['opened'=>'Opened','sent'=>'Sent (not opened)','none'=>'Not emailed']],
                                    ];
                                    @endphp
                                    <div class="filter-bar">
                                        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                                            @foreach($confFilterDefs as $fd)
                                            <div class="chk-filter-wrap" data-filter="{{ $fd['id'] }}">
                                                <button type="button" class="btn btn-sm btn-outline-secondary chk-filter-btn" data-filter="{{ $fd['id'] }}">
                                                    {{ $fd['label'] }}
                                                    <span class="badge badge-danger chk-badge ml-1" style="display:none;font-size:.65rem;"></span>
                                                    <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                                                </button>
                                                <div class="chk-filter-panel shadow" id="{{ $fd['id'] }}-panel" style="display:none;">
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
                                            <button id="btn-clear-filters" class="btn btn-clear btn-sm">
                                                <i class="fas fa-times mr-1"></i> Clear Filters
                                            </button>
                                        </div>
                                    </div>

                                    @php
                                        // Compact row data; the table is built client-side (custom.js, deferRender).
                                        $parseAvail = function ($raw) {
                                            if (empty($raw)) return [];
                                            $d = json_decode($raw, true);
                                            if (is_string($d)) return json_decode($d, true) ?: [];
                                            if (is_array($d)) return $d;
                                            return json_decode(str_replace('\\"', '"', $raw), true) ?: [];
                                        };
                                        $ecRows = collect($getExaminers)->values()->map(function ($v, $i) use ($parseAvail) {
                                            $avail = $parseAvail($v->exam_availability ?? null);
                                            $sorted = $avail; sort($sorted);
                                            $emailed = !empty($v->last_email_sent_at);
                                            return [
                                                'n' => $i + 1, 'id' => $v->id, 'name' => $v->examiner_name ?? null, 'email' => $v->email ?? null,
                                                'fellow' => (bool) ($v->is_fellow ?? false),
                                                'fno' => $v->matched_fellow_id_number ?? null, 'fid' => $v->matched_fellow_id ?? null,
                                                'cid' => $v->country_id ?? null, 'country' => $v->country_name ?? null,
                                                'spec' => $v->specialty ?? null,
                                                'avail' => array_values($avail),
                                                'availKey' => count($sorted) === 2 && in_array('FCS', $sorted) && in_array('MCS', $sorted) ? 'MCS+FCS' : implode('', $sorted),
                                                'shift' => (string) ($v->shift ?? '0'),
                                                'shiftName' => (!empty($v->shift) && $v->shift != 0) ? \App\Models\User::getShiftName($v->shift) : null,
                                                'part' => $v->participation_type ?? null,
                                                'src' => $v->history_source ?? '',
                                                'dsrc' => $v->history_source ?? ($emailed ? 'self' : null),
                                                'emailKey' => $v->last_email_opened_at ? 'opened' : ($emailed ? 'sent' : 'none'),
                                                'opened' => (bool) ($v->last_email_opened_at || $emailed),
                                                'emailTitle' => $v->last_email_opened_at ? 'Opened '.$v->last_email_opened_at : ($emailed ? 'Sent '.$v->last_email_sent_at : ''),
                                                'upd' => $v->history_updated_at ? \Carbon\Carbon::parse($v->history_updated_at)->setTimezone('Africa/Nairobi')->format('d M Y H:i') : null,
                                                'updSort' => $v->history_updated_at ?? '',
                                            ];
                                        });
                                    @endphp
                                    <script type="application/json" id="ecRows">{!! json_encode($ecRows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                                    <table id="examinerconfirmationtable" class="table table-bordered table-striped"
                                           data-app-url="{{ url('') }}"
                                           data-csrf="{{ csrf_token() }}"
                                           data-from="{{ request()->path() }}"
                                           data-query="{{ json_encode((object) request()->query()) }}"
                                           data-back="{{ request()->fullUrl() }}"
                                           data-reset-url="{{ route('examiner.reset.confirmation', '__ID__') }}">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Fellowship Status</th>
                                                <th>Country</th>
                                                <th>Specialty</th>
                                                <th>Availability</th>
                                                <th>Shift</th>
                                                <th>Participation</th>
                                                <th>Source</th>
                                                <th>Email Status</th>
                                                <th>Updated At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
{{-- ══ Delete Examiner Modal (soft / hard) ════════════════════════════════ --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#a02626;color:#fff;">
                <h5 class="modal-title" id="deleteModalTitle">Delete Examiner</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="deleteModalDesc" class="mb-3"></p>
                <div class="form-group mb-0">
                    <label class="font-weight-bold mb-2">Select delete type:</label>
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" id="typeSoft" name="deleteType" value="soft"
                               class="custom-control-input" checked>
                        <label class="custom-control-label" for="typeSoft">
                            <strong>Soft</strong> — Deactivate only — all data is kept but examiner is hidden.
                        </label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="typeHard" name="deleteType" value="hard"
                               class="custom-control-input">
                        <label class="custom-control-label text-danger" for="typeHard">
                            <strong>Hard</strong> — Permanently remove examiner + history, groups, shifts, attendance.
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="type" id="deleteTypeInput" value="soft">
                    <input type="hidden" name="back" value="{{ request()->fullUrl() }}">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-check mr-1"></i> Confirm Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .dropdown-menu .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #a02626;
    }

    .dropdown-menu .dropdown-item i {
        color: #5a6268;
        margin-right: 6px;
    }

    .dropdown-menu .dropdown-item:hover i {
        color: #a02626;
    }


    .paginate_button.active>.page-link {
        background-color: #a02626 !important;
        border-color: #a02626 !important;
        color: white;
    }

    .paginate_button>.page-link {
        color: #a02626;
    }

    .paginate_button>.page-link:focus,
    .paginate_button.active>.page-link:focus {
        box-shadow: none !important;
        outline: none !important;
    }

    /* ── Dark mode overrides for this page ── */
    body.dark-mode .filter-bar {
        background: #2d3748 !important;
        border-color: #4a5568 !important;
    }
    body.dark-mode .filter-bar .filter-label { color: #9ca3af !important; }
    body.dark-mode .filter-bar .btn-clear {
        background: #374151 !important;
        border-color: #4a5568 !important;
        color: #e0e0e0 !important;
    }
    body.dark-mode .action-icon { color: #d1d5db !important; }
    body.dark-mode .action-icon:hover { color: #f87171 !important; }
    body.dark-mode .dropdown-menu .dropdown-item:hover {
        background-color: #4a5568 !important;
        color: #e0e0e0 !important;
    }
    body.dark-mode .dropdown-menu .dropdown-item:hover i { color: #e0e0e0 !important; }
    body.dark-mode .chk-filter-panel {
        background: #374151 !important;
        border-color: #4a5568 !important;
    }
    body.dark-mode .chk-item:hover { background: #4a5568 !important; }
    body.dark-mode .chk-footer { border-top-color: #4a5568 !important; }
    body.dark-mode .chk-footer a { color: #9ca3af !important; }
</style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {

            var dt = $('#examinerconfirmationtable').DataTable();

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

            // ── Custom DataTable search extension ──────────────────────────────
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, row) {
                if (settings.nTable.id !== 'examinerconfirmationtable') return true;

                function chkMatch(filterId, rowVal) {
                    var checked = getChecked(filterId);
                    return !checked.length || checked.indexOf(String(rowVal)) !== -1;
                }

                if (!chkMatch('filter-specialty',    row.spec     || '')) return false;
                if (!chkMatch('filter-availability', row.availKey || '')) return false;
                if (!chkMatch('filter-shift',        row.shift))          return false;
                if (!chkMatch('filter-participation',row.part     || '')) return false;
                if (!chkMatch('filter-country',      row.country  || '')) return false;
                if (!chkMatch('filter-source',       row.src      || '')) return false;
                if (!chkMatch('filter-email',        row.emailKey || '')) return false;

                return true;
            });

            // ── Panel open/close ───────────────────────────────────────────────
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
                dt.draw();
            });

            // Select All / Clear per panel
            $(document).on('click', '.chk-select-all', function (e) {
                e.preventDefault();
                var $panel = $(this).closest('.chk-filter-panel');
                $panel.find('.chk-item:visible .chk-option').prop('checked', true);
                updateBadge($panel.closest('.chk-filter-wrap').data('filter'));
                dt.draw();
            });
            $(document).on('click', '.chk-clear', function (e) {
                e.preventDefault();
                var $panel   = $(this).closest('.chk-filter-panel');
                var filterId = $panel.closest('.chk-filter-wrap').data('filter');
                $panel.find('.chk-option').prop('checked', false);
                updateBadge(filterId);
                dt.draw();
            });

            // Clear all filters
            $('#btn-clear-filters').on('click', function () {
                $('.chk-option').prop('checked', false);
                $('.chk-badge').hide();
                dt.draw();
            });

            // ── Misc ───────────────────────────────────────────────────────────
            $('[data-toggle="popover"]').popover({ placement: 'right', trigger: 'focus' });

            $('input[name="deleteType"]').on('change', function () {
                $('#deleteTypeInput').val($(this).val());
            });
        });

        function showDeleteModal(id, name) {
            $('#deleteModalTitle').text('Delete Examiner — ' + name);
            $('#deleteModalDesc').html('Choose how to delete <strong>' + name + '</strong>.');
            $('#deleteForm').attr('action', '/admin/exams/examiner/' + id + '/destroy');
            $('#typeSoft').prop('checked', true);
            $('#deleteTypeInput').val('soft');
            $('#deleteModal').modal('show');
        }
    </script>
@endpush
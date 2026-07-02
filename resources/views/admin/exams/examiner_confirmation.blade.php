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
                                    <div class="filter-bar">
                                        <div class="row align-items-end g-2" style="row-gap:10px;">
                                            <div class="col-6 col-md-2">
                                                <label class="filter-label">Specialty</label>
                                                <select id="filter-specialty" class="form-control form-control-sm">
                                                    <option value="">All Specialties</option>
                                                    @foreach($specialties as $sp)
                                                        <option value="{{ $sp }}">{{ $sp }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="filter-label">Availability</label>
                                                <select id="filter-availability" class="form-control form-control-sm">
                                                    <option value="">All</option>
                                                    <option value="MCS">MCS</option>
                                                    <option value="FCS">FCS</option>
                                                    <option value="MCS+FCS">MCS &amp; FCS</option>
                                                    <option value="Not Available">Not Available</option>
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="filter-label">MCS Shift</label>
                                                <select id="filter-shift" class="form-control form-control-sm">
                                                    <option value="">All</option>
                                                    <option value="1">Morning</option>
                                                    <option value="2">Morning &amp; Afternoon</option>
                                                    <option value="3">Afternoon</option>
                                                    <option value="0">No Shift</option>
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="filter-label">Participation</label>
                                                <select id="filter-participation" class="form-control form-control-sm">
                                                    <option value="">All</option>
                                                    <option value="Examiner">Examiner</option>
                                                    <option value="Observer">Observer</option>
                                                    <option value="None">None</option>
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="filter-label">Country</label>
                                                <select id="filter-country" class="form-control form-control-sm">
                                                    <option value="">All Countries</option>
                                                    @foreach($countries as $c)
                                                        <option value="{{ $c }}">{{ $c }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="filter-label">Source</label>
                                                <select id="filter-source" class="form-control form-control-sm">
                                                    <option value="">All</option>
                                                    <option value="self">Self-submitted</option>
                                                    <option value="admin">Admin-entered</option>
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <label class="filter-label">Email Status</label>
                                                <select id="filter-email" class="form-control form-control-sm">
                                                    <option value="">All</option>
                                                    <option value="opened">Opened</option>
                                                    <option value="sent">Sent (not opened)</option>
                                                    <option value="none">Not emailed</option>
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2 d-flex align-items-end">
                                                <button id="btn-clear-filters" class="btn btn-clear btn-sm w-100">
                                                    <i class="fas fa-times mr-1"></i> Clear Filters
                                                    <span class="filter-badge" id="filter-count"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <table id="examinerconfirmationtable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Country</th>
                                                <th>Specialty</th>
                                                <th>Availability</th>
                                                <th>MCS Shift</th>
                                                <th>Participation</th>
                                                <th>Hospital</th>
                                                <th>Mobile Number</th>
                                                <th>Updated Date</th>
                                                <th>Source</th>
                                                <th>Email Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($getExaminers as $index => $value)
                                                @php
                                                    $rowAvail = [];
                                                    if (!empty($value->exam_availability)) {
                                                        $dec = json_decode($value->exam_availability, true);
                                                        if (is_string($dec)) $dec = json_decode($dec, true) ?: [];
                                                        $rowAvail = is_array($dec) ? $dec : [];
                                                    }
                                                    sort($rowAvail);
                                                    $availKey = count($rowAvail) === 2 && in_array('FCS',$rowAvail) && in_array('MCS',$rowAvail)
                                                        ? 'MCS+FCS'
                                                        : implode('', $rowAvail);
                                                @endphp
                                                <tr
                                                    data-specialty="{{ $value->specialty ?? '' }}"
                                                    data-availability="{{ $availKey }}"
                                                    data-shift="{{ $value->shift ?? '0' }}"
                                                    data-participation="{{ $value->participation_type ?? '' }}"
                                                    data-country="{{ $value->country_name ?? '' }}"
                                                    data-source="{{ $value->history_source ?? '' }}"
                                                    data-email="{{ $value->last_email_opened_at ? 'opened' : ($value->last_email_sent_at ? 'sent' : 'none') }}"
                                                >
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $value->examiner_name ?? '-' }}</td>
                                                    <td>{{ $value->email ?? '-' }}</td>
                                                    <td>{{ $value->country_name ?? '-' }}</td>
                                                    <td>{{ $value->specialty ?? '-' }}</td>
                                                    <td>
                                                        @php
                                                            $availability = [];

                                                            if (!empty($value->exam_availability)) {
                                                                $decoded = json_decode($value->exam_availability, true);

                                                                if (is_string($decoded)) {
                                                                    $availability = json_decode($decoded, true) ?: [];
                                                                } elseif (is_array($decoded)) {
                                                                    $availability = $decoded;
                                                                } else {
                                                                    $cleaned = str_replace(
                                                                        '\\"',
                                                                        '"',
                                                                        $value->exam_availability,
                                                                    );
                                                                    $availability = json_decode($cleaned, true) ?: [];
                                                                }
                                                            }
                                                        @endphp

                                                        @if (in_array('Not Available', $availability))
                                                            <span style="color: #a02626; font-weight: 600;">Not
                                                                Available</span>
                                                        @elseif(count($availability))
                                                            {{ implode(', ', $availability) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if ($value->shift)
                                                            {{ App\Models\User::getShiftName($value->shift) }}
                                                        @else
                                                            No shifts assigned
                                                        @endif
                                                    </td>
                                                    <td>{{ $value->participation_type ?? '-' }}</td>
                                                    <td>{{ $value->hospital_name ?? '-' }}</td>
                                                    <td>{{ $value->mobile ?? '-' }}</td>
                                                    <td>{{ $value->history_updated_at ?? '-'}}</td>
                                                    <td>
                                                        @if($value->history_source === 'self')
                                                            <span class="badge-source-self">Self</span>
                                                        @elseif($value->history_source === 'admin')
                                                            <span class="badge-source-admin">Admin</span>
                                                        @else
                                                            <span class="badge-email-none">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($value->last_email_opened_at)
                                                            <span class="badge-email-opened" title="Opened {{ $value->last_email_opened_at }}&#10;Opens: {{ $value->total_email_opens }}">
                                                                Opened
                                                            </span>
                                                        @elseif($value->last_email_sent_at)
                                                            <span class="badge-email-sent" title="Sent {{ $value->last_email_sent_at }}&#10;Not yet opened">
                                                                Sent
                                                            </span>
                                                        @else
                                                            <span class="badge-email-none">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                                                type="button" data-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                                <!-- View form -->
                                                                <form
                                                                    action="{{ url("admin/exams/view_examiner/{$value->id}") }}"
                                                                    method="POST" style="display:inline;">
                                                                    @csrf
                                                                    <input type="hidden" name="from"
                                                                        value="{{ request()->path() }}">
                                                                    @foreach (request()->query() as $key => $val)
                                                                        <input type="hidden" name="{{ $key }}"
                                                                            value="{{ $val }}">
                                                                    @endforeach
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fa fa-eye"></i> View
                                                                    </button>
                                                                </form>

                                                                <a href="{{ url("admin/exams/edit_examiner/$value->id") }}"
                                                                    class="dropdown-item">
                                                                    <i class="fa fa-edit"></i> Edit
                                                                </a>

                                                                <div class="dropdown-divider"></div>

                                                                {{-- Reset confirmation (direct POST, no soft/hard choice) --}}
                                                                <form method="POST"
                                                                      action="{{ route('examiner.reset.confirmation', $value->id) }}"
                                                                      onsubmit="return confirm('Reset the availability confirmation for {{ addslashes($value->examiner_name) }}? This will clear their submitted availability.')">
                                                                    @csrf
                                                                    <input type="hidden" name="type" value="soft">
                                                                    <input type="hidden" name="back" value="{{ request()->fullUrl() }}">
                                                                    <button type="submit" class="dropdown-item text-warning">
                                                                        <i class="fas fa-undo mr-1"></i> Reset Confirmation
                                                                    </button>
                                                                </form>

                                                                {{-- Delete examiner (soft/hard modal) --}}
                                                                <a href="#" class="dropdown-item text-danger"
                                                                   onclick="showDeleteModal({{ $value->id }}, '{{ addslashes($value->examiner_name) }}'); return false;">
                                                                    <i class="fa fa-trash mr-1"></i> Delete Examiner
                                                                </a>
                                                            </div>
                                                        </div>

                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
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
</style>
@endpush

@section('scripts')
    <script>
        $(document).ready(function () {

            // ── Active filter state ────────────────────────────────────────────
            var filters = {
                specialty:      '',
                availability:   '',
                shift:          '',
                participation:  '',
                country:        '',
                source:         '',
                email:          '',
            };

            // ── Custom DataTable search extension ──────────────────────────────
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'examinerconfirmationtable') return true;
                var $row = $(settings.nTable).DataTable().row(dataIndex).node();

                if (filters.specialty && $($row).data('specialty') !== filters.specialty)             return false;
                if (filters.availability && $($row).data('availability') !== filters.availability)     return false;
                if (filters.shift !== '' && String($($row).data('shift')) !== filters.shift)           return false;
                if (filters.participation && $($row).data('participation') !== filters.participation)   return false;
                if (filters.country && $($row).data('country') !== filters.country)                   return false;
                if (filters.source && $($row).data('source') !== filters.source)                       return false;
                if (filters.email && $($row).data('email') !== filters.email)                          return false;

                return true;
            });

            // ── Get existing DataTable instance (custom.js initialises it) ────
            var dt = $('#examinerconfirmationtable').DataTable();

            // ── Wire up filter selects ─────────────────────────────────────────
            function updateFilterBadge() {
                var active = Object.values(filters).filter(function (v) { return v !== ''; }).length;
                if (active > 0) {
                    $('#filter-count').text(active).show();
                } else {
                    $('#filter-count').hide();
                }
            }

            var filterIds = ['specialty', 'availability', 'shift', 'participation', 'country', 'source', 'email'];
            filterIds.forEach(function (key) {
                $('#filter-' + key).on('change', function () {
                    filters[key] = $(this).val();
                    dt.draw();
                    updateFilterBadge();
                });
            });

            // ── Clear all filters ──────────────────────────────────────────────
            $('#btn-clear-filters').on('click', function () {
                filterIds.forEach(function (k) { filters[k] = ''; });
                $('#filter-specialty, #filter-availability, #filter-shift, #filter-participation, #filter-country, #filter-source, #filter-email').val('');
                dt.draw();
                updateFilterBadge();
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
@endsection
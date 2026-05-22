@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-check mr-2" style="color:#a02626;"></i>
                        Examiner Attendance
                    </h4>
                </div>
                <div class="col-sm-6 text-right">
                    @if($dateFilter)
                    <button class="btn btn-sm btn-outline-danger mr-2"
                            onclick="confirmClearDate('{{ $dateFilter }}')">
                        <i class="fas fa-trash mr-1"></i> Clear All for {{ \Carbon\Carbon::parse($dateFilter)->format('d M Y') }}
                    </button>
                    @endif
                    <a href="{{ url('admin/exams/examiners') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Examiners
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="col-md-12">@include('_message')</div>

    <section class="content">
        <div class="container-fluid">

            {{-- Filter + summary row --}}
            <div class="row mb-3">
                <div class="col-md-5">
                    <div class="card card-outline mb-0" style="border-top:3px solid #a02626;">
                        <div class="card-body py-3">
                            <form method="GET" action="{{ url('admin/exams/attendance') }}" class="form-inline">
                                <label class="mr-2 font-weight-600" style="font-size:.9rem;">Filter by date:</label>
                                <input type="date" name="date" value="{{ $dateFilter }}"
                                       class="form-control form-control-sm mr-2"
                                       onchange="this.form.submit()">
                                @if($dateFilter)
                                    <a href="{{ url('admin/exams/attendance') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times mr-1"></i>Clear
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="info-box mb-0">
                        <span class="info-box-icon" style="background:#a02626;">
                            <i class="fas fa-users"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $dateFilter ? 'Checked In ('.\Carbon\Carbon::parse($dateFilter)->format('d M').')' : 'Total Records' }}</span>
                            <span class="info-box-number">{{ $totalRecords }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background:#a02626; color:#fff; padding:.6rem 1rem;">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-day mr-1"></i>
                        {{ $dateFilter ? \Carbon\Carbon::parse($dateFilter)->format('l, F j, Y') : 'All Attendance Records' }}
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($records->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3" style="opacity:.35;"></i>
                            <p class="mb-0">No attendance records{{ $dateFilter ? ' for this date' : '' }}.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0" id="attendanceTable">
                                <thead style="background:#f8f8f8;">
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th>Examiner</th>
                                        <th>Badge ID</th>
                                        <th>Specialty</th>
                                        <th>Country</th>
                                        <th>Group</th>
                                        <th>Shift</th>
                                        @if(!$dateFilter)
                                        <th>Date</th>
                                        @endif
                                        <th>Check-in Time</th>
                                        <th style="width:60px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $i => $rec)
                                    <tr>
                                        <td class="text-muted" style="font-size:.82rem;">{{ $i + 1 }}</td>
                                        <td style="font-weight:600;">{{ $rec->examiner_name }}</td>
                                        <td>
                                            <span class="badge badge-secondary" style="font-size:.78rem;letter-spacing:.03em;">
                                                {{ $rec->badge_id ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $rec->specialty ?? '—' }}</td>
                                        <td>{{ $rec->country_name ?? '—' }}</td>
                                        <td>{{ $rec->group_name ?? '—' }}</td>
                                        <td>
                                            @php
                                                $shiftLabels = [1 => 'Morning', 2 => 'Morning & Afternoon', 3 => 'Afternoon'];
                                                $shiftLabel  = $shiftLabels[$rec->shift] ?? ($rec->shift ?? '—');
                                            @endphp
                                            <span class="badge badge-info" style="font-size:.78rem;">
                                                {{ $shiftLabel }}
                                            </span>
                                        </td>
                                        @if(!$dateFilter)
                                        <td style="white-space:nowrap;">
                                            <a href="{{ url('admin/exams/attendance') }}?date={{ $rec->attendance_date }}"
                                               class="badge badge-light text-dark" style="font-size:.78rem;">
                                                {{ \Carbon\Carbon::parse($rec->attendance_date)->format('d M Y') }}
                                            </a>
                                        </td>
                                        @endif
                                        <td style="white-space:nowrap;">
                                            {{ \Carbon\Carbon::parse($rec->checked_in_at)->format('H:i:s') }}
                                        </td>
                                        <td class="text-center">
                                            <form method="POST"
                                                  action="{{ route('attendance.destroy.record', $rec->id) }}"
                                                  onsubmit="return confirm('Delete this attendance record?')">
                                                @csrf
                                                @if($dateFilter)
                                                <input type="hidden" name="date" value="{{ $dateFilter }}">
                                                @endif
                                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                                        title="Delete record" style="padding:2px 7px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @if(!$records->isEmpty())
                <div class="card-footer py-2 text-right" style="font-size:.83rem;color:#6c757d;">
                    {{ $totalRecords }} record{{ $totalRecords !== 1 ? 's' : '' }}
                    {{ $dateFilter ? 'on '.\Carbon\Carbon::parse($dateFilter)->format('d M Y') : 'total' }}
                    &nbsp;|&nbsp;
                    <a href="{{ url('admin/exams/attendance') }}{{ $dateFilter ? '?date='.$dateFilter.'&export=1' : '?export=1' }}" class="text-muted">
                        <i class="fas fa-download mr-1"></i>Export CSV
                    </a>
                </div>
                @endif
            </div>

            {{-- Quick-jump to dates with records --}}
            @if($availableDates->count())
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h6 class="mb-0" style="font-size:.88rem;color:#6c757d;">
                        <i class="fas fa-history mr-1"></i> Dates with records
                    </h6>
                </div>
                <div class="card-body py-2">
                    @foreach($availableDates as $d)
                        <a href="{{ url('admin/exams/attendance') }}?date={{ $d }}"
                           class="btn btn-sm {{ $d === $dateFilter ? 'btn-danger' : 'btn-outline-secondary' }} mr-1 mb-1"
                           style="font-size:.8rem;">
                            {{ \Carbon\Carbon::parse($d)->format('d M Y') }}
                        </a>
                    @endforeach
                    @if($dateFilter)
                    <a href="{{ url('admin/exams/attendance') }}"
                       class="btn btn-sm btn-outline-primary mr-1 mb-1" style="font-size:.8rem;">
                        <i class="fas fa-list mr-1"></i>All
                    </a>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </section>

{{-- Hidden clear-date form --}}
<form id="clearDateForm" method="POST" action="{{ route('attendance.destroy.date') }}" style="display:none;">
    @csrf
    <input type="hidden" name="date" id="clearDateInput" value="">
</form>

</div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        const val = $(this).val().toLowerCase();
        $('#attendanceTable tbody tr').each(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });
});

function confirmClearDate(date) {
    if (confirm('Delete ALL attendance records for ' + date + '? This cannot be undone.')) {
        $('#clearDateInput').val(date);
        $('#clearDateForm').submit();
    }
}
</script>
@endpush

@push('styles')
<style>
.info-box { min-height: 60px; }
.info-box .info-box-icon { font-size: 1.4rem; line-height: 60px; width: 60px; }
.info-box .info-box-content { padding: 8px 10px; }
.info-box .info-box-number { font-size: 1.5rem; }
.table th { font-size: .82rem; color: #6c757d; font-weight: 600; white-space: nowrap; }
.table td { font-size: .87rem; vertical-align: middle; }
</style>
@endpush

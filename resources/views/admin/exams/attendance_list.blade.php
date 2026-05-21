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

            {{-- Date filter + summary card --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card card-outline" style="border-top:3px solid #a02626;">
                        <div class="card-body py-3">
                            <form method="GET" action="{{ url('admin/exams/attendance') }}" class="form-inline">
                                <label class="mr-2 font-weight-600" style="font-size:.9rem;">Filter by date:</label>
                                <input type="date" name="date" value="{{ $dateFilter }}"
                                       class="form-control form-control-sm mr-2"
                                       onchange="this.form.submit()">
                                @if($dateFilter !== \Carbon\Carbon::today()->toDateString())
                                    <a href="{{ url('admin/exams/attendance') }}" class="btn btn-sm btn-outline-secondary">Today</a>
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
                            <span class="info-box-text">Checked In</span>
                            <span class="info-box-number">{{ $totalToday }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background:#a02626; color:#fff; padding:.6rem 1rem;">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-day mr-1"></i>
                        {{ \Carbon\Carbon::parse($dateFilter)->format('l, F j, Y') }}
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($records->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3" style="opacity:.35;"></i>
                            <p class="mb-0">No attendance records for this date.</p>
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
                                        <th>Check-in Time</th>
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
                                        <td style="white-space:nowrap;">
                                            {{ \Carbon\Carbon::parse($rec->checked_in_at)->format('H:i:s') }}
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
                    {{ $totalToday }} examiner{{ $totalToday !== 1 ? 's' : '' }} checked in on
                    {{ \Carbon\Carbon::parse($dateFilter)->format('d M Y') }}
                    &nbsp;|&nbsp;
                    <a href="{{ url('admin/exams/attendance') }}?date={{ $dateFilter }}&export=1" class="text-muted">
                        <i class="fas fa-download mr-1"></i>Export CSV
                    </a>
                </div>
                @endif
            </div>

            {{-- Quick-jump to other dates that have records --}}
            @if($availableDates->count() > 1)
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h6 class="mb-0" style="font-size:.88rem;color:#6c757d;">
                        <i class="fas fa-history mr-1"></i> Other dates with records
                    </h6>
                </div>
                <div class="card-body py-2">
                    @foreach($availableDates as $d)
                        @if($d !== $dateFilter)
                            <a href="{{ url('admin/exams/attendance') }}?date={{ $d }}"
                               class="btn btn-sm btn-outline-secondary mr-1 mb-1" style="font-size:.8rem;">
                                {{ \Carbon\Carbon::parse($d)->format('d M Y') }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </section>

</div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Simple client-side search
    $('#searchInput').on('keyup', function() {
        const val = $(this).val().toLowerCase();
        $('#attendanceTable tbody tr').each(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });
});
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

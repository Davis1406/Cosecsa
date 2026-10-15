@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <div class="col-md-12">
                @include('_message')
            </div>

            <section class="content">
                <div class="container-wrapper">

                    {{-- Marks saved on this device that have not reached the server yet (filled in by dist/js/examiner-offline.js) --}}
                    <div id="examiner-offline-pending"></div>

                    {{-- MCS RESULTS TABLE --}}
                    @if($showMcs && $mcsResults->isNotEmpty())
                        <div class="row">
                            <div class="col-12">
                                <div class="card" style="margin-top: 20px">
                                    <div class="card-header" style="background-color: #17a2b8; color: white;">
                                        <h3 class="card-title">MCS Results</h3>
                                    </div>

                                    <div class="card-body">
                                        <table id="mcsResultsTable" class="table table-bordered table-striped dt-responsive w-100">
                                            <thead>
                                            <tr>
                                                <th>Candidate ID</th>
                                                <th>Group</th>
                                                <th>Question 1</th>
                                                <th>Question 2</th>
                                                <th>Question 3</th>
                                                <th>Question 4</th>
                                                <th>Question 5</th>
                                                <th>Total Marks</th>
                                                <th>Overall Grade</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach ($mcsResults as $value)
                                                @php
                                                    $marks = json_decode($value->question_mark, true) ?? [];
                                                @endphp

                                                <tr>
                                                    <td>{{ $value->candidate_name }}</td>
                                                    <td>{{ $value->group_name }}</td>

                                                    @for ($i = 0; $i < 5; $i++)
                                                        <td>{{ $marks[$i] ?? '-' }}</td>
                                                    @endfor

                                                    <td><b>{{ $value->total }}</b></td>
                                                    <td>{{ $value->overall ?? '-' }}</td>

                                                    <td>
                                                        @if($value->is_test)
                                                        <span class="badge badge-secondary" title="Saved for a TEST group; review or delete it under Exams &gt; Test Results">Test mark</span>
                                                    @else
                                                        <div class="btn-group">
                                                            <a class="btn btn-info btn-sm"
                                                               href="{{ url('examiner/view_results/' . $value->candidate_id . '/' . $value->station_id) }}">
                                                                View
                                                            </a>
                                                            <a class="btn btn-warning btn-sm"
                                                               href="{{ url('examiner/resubmit/' . $value->candidate_id . '/' . $value->station_id) }}">
                                                                Resubmit
                                                            </a>
                                                        </div>
                                                    @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- GS RESULTS TABLE --}}
                    @if($showGs && $gsResults->isNotEmpty())
                        <div class="row">
                            <div class="col-12">
                                <div class="card" style="margin-top: 20px">
                                    <div class="card-header" style="background-color: #a02626; color: white;">
                                        <h3 class="card-title">General Surgery Results</h3>
                                    </div>

                                    <div class="card-body">
                                        <table id="gsResultsTable" class="table table-bordered table-striped dt-responsive w-100">
                                            <thead>
                                            <tr>
                                                <th>Candidate ID</th>
                                                <th>Group</th>
                                                <th>Question 1</th>
                                                <th>Question 2</th>
                                                <th>Question 3</th>
                                                <th>Question 4</th>
                                                <th>Question 5</th>
                                                <th>Total Marks</th>
                                                <th>Overall Grade</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach ($gsResults as $value)
                                                @php
                                                    $marks = json_decode($value->question_mark, true) ?? [];
                                                @endphp

                                                <tr>
                                                    <td>{{ $value->candidate_name }}</td>
                                                    <td>{{ $value->group_name }}</td>

                                                    @for ($i = 0; $i < 5; $i++)
                                                        <td>{{ $marks[$i] ?? '-' }}</td>
                                                    @endfor

                                                    <td><b>{{ $value->total }}</b></td>
                                                    <td>{{ $value->overall ?? '-' }}</td>

                                                    <td>
                                                        @if($value->is_test)
                                                        <span class="badge badge-secondary" title="Saved for a TEST group; review or delete it under Exams &gt; Test Results">Test mark</span>
                                                    @else
                                                        <div class="btn-group">
                                                            <a class="btn btn-info btn-sm"
                                                               href="{{ url('examiner/view_results/' . $value->candidate_id . '/' . $value->station_id) }}">
                                                                View
                                                            </a>
                                                            <a class="btn btn-warning btn-sm"
                                                               href="{{ url('examiner/resubmit/' . $value->candidate_id . '/' . $value->station_id) }}">
                                                                Resubmit
                                                            </a>
                                                        </div>
                                                    @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- FCS SPECIALTY RESULTS TABLE --}}
                    @if($showFcs && $fcsResults->isNotEmpty())
                        <div class="row">
                            <div class="col-12">
                                <div class="card" style="margin-top: 20px">
                                    <div class="card-header" style="background-color: #FEC503; color: #a02626;">
                                        <h3 class="card-title">FCS Specialty Results</h3>
                                    </div>

                                    <div class="card-body">
                                        <table id="fcsResultsTable" class="table table-bordered table-striped dt-responsive w-100">
                                            <thead>
                                            <tr>
                                                <th>Candidate ID</th>
                                                <th>Group</th>
                                                <th>Clinical Total</th>
                                                <th>Viva Total</th>
                                                <th>Overall Total</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>

                                            <tbody>
                                            @foreach ($fcsResults as $value)
                                                <tr>
                                                    <td>{{ $value->candidate_name }}</td>
                                                    <td>{{ $value->group_name }}</td>
                                                    <td><b>{{ $value->clinical_total }}</b></td>
                                                    <td><b>{{ $value->viva_total }}</b></td>
                                                    <td><b>{{ $value->overall_total }}</b></td>
                                                    <td>
                                                        @if($value->is_test)
                                                        <span class="badge badge-secondary" title="Saved for a TEST group; review or delete it under Exams &gt; Test Results">Test mark</span>
                                                    @else
                                                        <div class="btn-group">
                                                            <a class="btn btn-info btn-sm"
                                                               href="{{ url('examiner/view_fcs_results/' . $value->candidate_id) }}">
                                                                View
                                                            </a>
                                                            <a class="btn btn-warning btn-sm"
                                                               href="{{ url('examiner/fcs-resubmit/' . $value->candidate_id) }}">
                                                                Resubmit
                                                            </a>
                                                        </div>
                                                    @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- NO RESULTS MESSAGE --}}
                    @if(!$showMcs && !$showGs && !$showFcs)
                        <div class="row">
                            <div class="col-12">
                                <div class="card" style="margin-top: 20px">
                                    <div class="card-body text-center">
                                        <p class="text-muted">No results found.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </section>
        </div>
    </div>

    @include('examiner._offline')
@endsection

@push('scripts')
<script>
    // Collapse the least important columns first on narrow screens (they stay
    // one tap away under the row's + control). Lower number = kept visible longer.
    function initResultsTable(selector, priorities) {
        var columnDefs = Object.keys(priorities).map(function (target) {
            return { responsivePriority: priorities[target], targets: parseInt(target, 10) };
        });
        $(selector).DataTable({ responsive: true, autoWidth: false, columnDefs: columnDefs });
    }

    $(document).ready(function () {
        // Candidate, Actions, Total, Grade, Group, then the five question marks.
        var questionTable = { 0: 1, 9: 2, 7: 3, 8: 4, 1: 5, 2: 10, 3: 11, 4: 12, 5: 13, 6: 14 };
        @if($showMcs) initResultsTable('#mcsResultsTable', questionTable); @endif
        @if($showGs) initResultsTable('#gsResultsTable', questionTable); @endif
        @if($showFcs) initResultsTable('#fcsResultsTable', { 0: 1, 5: 2, 4: 3, 2: 4, 3: 5, 1: 6 }); @endif
    });
</script>
@endpush

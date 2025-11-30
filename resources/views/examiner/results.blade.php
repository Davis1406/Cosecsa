@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <div class="col-md-12">
                @include('_message')
            </div>

            <section class="content">
                <div class="container-wrapper">

                    {{-- MCS RESULTS TABLE --}}
                    @if($showMcs && $mcsResults->isNotEmpty())
                        <div class="row">
                            <div class="col-12">
                                <div class="card" style="margin-top: 20px">
                                    <div class="card-header" style="background-color: #17a2b8; color: white;">
                                        <h3 class="card-title">MCS Results</h3>
                                    </div>

                                    <div class="card-body">
                                        <table id="mcsResultsTable" class="table table-bordered table-striped">
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
                                        <table id="gsResultsTable" class="table table-bordered table-striped">
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
                                        <table id="fcsResultsTable" class="table table-bordered table-striped">
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

    <script>
        $(document).ready(function() {
            @if($showMcs)
            $('#mcsResultsTable').DataTable({
                "responsive": true,
                "autoWidth": false,
            });
            @endif

            @if($showGs)
            $('#gsResultsTable').DataTable({
                "responsive": true,
                "autoWidth": false,
            });
            @endif

            @if($showFcs)
            $('#fcsResultsTable').DataTable({
                "responsive": true,
                "autoWidth": false,
            });
            @endif
        });
    </script>
@endsection

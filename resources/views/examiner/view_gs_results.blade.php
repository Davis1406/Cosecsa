@extends('layout.app')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6" style="text-align: left">
                        <a href="{{ url('examiner/results') }}" class="btn btn-primary" style="background-color: #a02626; border-color:#a02626">
                            <span class="fas fa-arrow-left"></span> Back to Results List
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Candidate's GS Results</h3>
                </div>
                <div class="card-body">
                    @if ($candidateResult)
                        <table class="table table-bordered">
                            <tr>
                                <th>Group Name</th>
                                <td>Group {{ $candidateResult->group_name }}</td>
                            </tr>
                            <tr>
                                <th>Station ID</th>
                                <td>Station {{ $candidateResult->station_id }}</td>
                            </tr>
                            <tr>
                                <th>Candidate ID</th>
                                <td>{{ $candidateResult->candidate_name }}</td>
                            </tr>
                            <tr>
                                <th>Total Marks</th>
                                <td><b>{{ $candidateResult->total }}</b></td>
                            </tr>
                            <tr>
                                <th>Remarks</th>
                                <td style="word-wrap: break-word; white-space: normal; max-width: 200px;">
                                    {{ $candidateResult->remarks }}
                                </td>
                            </tr>
                        </table>

                        @php
                            $marks = json_decode($candidateResult->question_mark, true);
                            $case1Marks = array_slice($marks, 0, 3);
                            $case2Marks = array_slice($marks, 3);

                            $questionLabels = [
                                'Overall Professional Capacity and Patient Care',
                                'Knowledge and Judgement',
                                'Quality of Response'
                            ];
                        @endphp

                        <h4 class="mt-4">Detailed Results</h4>

                        <div class="row">
                            <!-- Case 1 -->
                            <div class="col-md-6">
                                <h5 class="mt-3" style="color: #a02626;">Case 1</h5>
                                <table class="table table-bordered">
                                    @foreach ($case1Marks as $index => $mark)
                                        <tr>
                                            <th>{{ $questionLabels[$index] ?? 'Question ' . ($index + 1) }}</th>
                                            <td>{{ $mark }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <th>Case 1 Total Marks</th>
                                        <td><b>{{ array_sum($case1Marks) }}</b></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Case 2 -->
                            <div class="col-md-6">
                                <h5 class="mt-3" style="color: #a02626;">Case 2</h5>
                                <table class="table table-bordered">
                                    @foreach ($case2Marks as $index => $mark)
                                        <tr>
                                            <th>{{ $questionLabels[$index] ?? 'Question ' . ($index + 1) }}</th>
                                            <td>{{ $mark }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <th>Case 2 Total Marks</th>
                                        <td><b>{{ array_sum($case2Marks) }}</b></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                    @else
                        <p>No result data found for this candidate.</p>
                    @endif
                </div>
            </div>
        </section>
    </div>

@endsection

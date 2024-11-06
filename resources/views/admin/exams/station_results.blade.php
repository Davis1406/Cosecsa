@extends('layout.app')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/exams/results') }}" class="btn btn-primary" style="background-color: #a02626; border-color:#a02626">
                        <span class="fas fa-arrow-left"></span> Back to Results List
                    </a>
                </div>
                <div class="col-sm-6" style="text-align: right">
                    <button onclick="window.print()" class="btn" style="background-color: #FEC503; border-color:#FEC503">
                        <span class="fas fa-print"></span> Print
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Candidate's Results</h3>
            </div>
            <div class="card-body">
                @if ($candidateResult)
                    <table class="table table-bordered" style="width: 100%; margin: 0 auto;">
                        <tr>
                            <th>Candidate ID</th>
                            <td>{{ $candidateResult->candidate_name }}</td>
                        </tr>
                        <tr>
                            <th>Examiner ID</th>
                            <td>{{ $candidateResult->examin_id }} - {{$candidateResult->examiner_name}}</td>
                        </tr>
                        <tr>
                            <th>Group Name</th>
                            <td>Group {{ $candidateResult->group_name }}</td>
                        </tr>
                        <tr>
                            <th>Station </th>
                            <td>Station {{ $candidateResult->station_id }}</td>
                        </tr>
                        @php
                            $marks = json_decode($candidateResult->question_mark, true); 
                        @endphp
                        @if(is_array($marks))
                            @foreach($marks as $index => $mark)
                                <tr>
                                    <th>Question {{ $index + 1 }}</th>
                                    <td>{{ $mark }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="2">No question marks available</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Total Marks</th>
                            <td><b>{{ $candidateResult->total }}</b></td>
                        </tr>
                        <tr>
                            <th>Overall Grade</th>
                            <td>{{ $candidateResult->overall }}</td>
                        </tr>
                        <tr>
                            <th>Remarks</th>
                            <td>{{ $candidateResult->remarks }}</td>
                        </tr>
                    </table>
                @else
                    <p>No result data found for this candidate.</p>
                @endif
            </div>
        </div>
    </section>
</div>

@endsection

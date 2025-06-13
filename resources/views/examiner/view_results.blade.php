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
                <h3 class="card-title">Candidate's Results</h3>
            </div>
            <div class="card-body">
                @if ($candidateResult)
                    <table class="table table-bordered">
                        <tr>
                            <th>Candidate ID</th>
                            <td>{{ $candidateResult->candidate_name }}</td>
                        </tr>
                        <tr>
                            <th>Group Name</th>
                            <td>Group {{ $candidateResult->group_name }}</td>
                        </tr>
                        <tr>
                            <th>Station</th>
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

                        @if ($candidateResult->source_table === 'mcs_results')
                            <tr>
                                <th>Overall Grade</th>
                                <td>{{ $candidateResult->overall }}</td>
                            </tr>
                        @endif
                        
                        <tr>
                            <th>Remarks</th>
                            <td style="word-wrap: break-word; white-space: normal; max-width: 200px;">
                                {{ $candidateResult->remarks }}
                            </td>
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

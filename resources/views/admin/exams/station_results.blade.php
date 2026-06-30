@extends('layout.app')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/exams/exam_results') }}" class="btn btn-primary" style="background-color: #a02626; border-color:#a02626">
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
                    {{-- Candidate + station header (shared across all examiners) --}}
                    <table class="table table-bordered mb-3" style="width:100%;">
                        <tr>
                            <th style="width:200px;">Candidate ID</th>
                            <td>{{ $candidateResult->candidate_name }}</td>
                        </tr>
                        <tr>
                            <th>Group</th>
                            <td>Group {{ $candidateResult->group_name }}</td>
                        </tr>
                        <tr>
                            <th>Station</th>
                            <td>Station {{ $candidateResult->station_id }}</td>
                        </tr>
                    </table>

                    {{-- One block per examiner --}}
                    @foreach ($allResults ?? [$candidateResult] as $i => $result)
                        <h6 class="mt-3 mb-1" style="color:#a02626;">
                            Examiner {{ $i + 1 }}: {{ $result->examin_id }} — {{ $result->examiner_name }}
                        </h6>
                        <table class="table table-bordered mb-3" style="width:100%;">
                            @php $marks = json_decode($result->question_mark, true); @endphp
                            @if(is_array($marks))
                                @foreach($marks as $index => $mark)
                                    <tr>
                                        <th style="width:200px;">Question {{ $index + 1 }}</th>
                                        <td>{{ $mark }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="2">No question marks available</td></tr>
                            @endif
                            <tr>
                                <th>Total Marks</th>
                                <td><b>{{ $result->total }}</b></td>
                            </tr>
                            <tr>
                                <th>Overall Grade</th>
                                <td>{{ $result->overall }}</td>
                            </tr>
                            <tr>
                                <th>Remarks</th>
                                <td>{{ $result->remarks }}</td>
                            </tr>
                        </table>
                    @endforeach
                @else
                    <p>No result data found for this candidate.</p>
                @endif
            </div>
        </div>
    </section>
</div>

@endsection

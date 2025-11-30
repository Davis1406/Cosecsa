@extends('layout.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6" style="text-align: left">
                        <a href="javascript:history.back()" class="btn btn-primary" style="background-color: #a02626; border-color:#a02626">
                            <span class="fas fa-arrow-left"></span> Back to Results
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="card">
                <div class="card-header" style="background-color: {{ $candidateResult->exam_format == 'clinical' ? '#a02626' : '#FEC503' }}; color: {{ $candidateResult->exam_format == 'clinical' ? 'white' : '#a02626' }};">
                    <h3 class="card-title">
                        <i class="fas fa-{{ $candidateResult->exam_format == 'clinical' ? 'stethoscope' : 'comments' }} mr-2"></i>
                        {{ ucfirst($candidateResult->exam_format) }} Station Results - Station {{ $candidateResult->s_id }}
                    </h3>
                </div>
                <div class="card-body">
                    @if ($candidateResult)
                        <!-- Candidate Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 40%">Candidate ID</th>
                                        <td>{{ $candidateResult->candidate_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Candidate Name</th>
                                        <td>{{ $candidateResult->fullname }}</td>
                                    </tr>
                                    <tr>
                                        <th>Group Name</th>
                                        <td>{{ $candidateResult->g_name }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 40%">Station ID</th>
                                        <td>Station {{ $candidateResult->s_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Exam Format</th>
                                        <td>
                                        <span class="badge badge-{{ $candidateResult->exam_format == 'clinical' ? 'danger' : 'warning' }}">
                                            {{ ucfirst($candidateResult->exam_format) }}
                                        </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Number of Examiners</th>
                                        <td><b>{{ $allResults->count() }}</b></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Results from All Examiners -->
                        <h4 class="mb-3">Results from All Examiners</h4>

                        @foreach($allResults as $index => $result)
                            <div class="card mb-3" style="border-left: 4px solid {{ $candidateResult->exam_format == 'clinical' ? '#a02626' : '#FEC503' }};">
                                <div class="card-header" style="background-color: #f8f9fa;">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-md mr-2"></i>
                                        Examiner {{ $index + 1 }}: {{ $result->examiner_name }} (ID: {{ $result->examiner_id }})
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="font-weight-bold">Group: {{ $result->g_name }}</h6>

                                            @php
                                                $marks = json_decode($result->question_mark, true) ?? [];
                                            @endphp

                                            <h6 class="mt-3 mb-2">Question Breakdown:</h6>
                                            <table class="table table-sm table-bordered">
                                                <thead class="thead-light">
                                                <tr>
                                                    <th>Question</th>
                                                    <th>Marks</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($marks as $qIndex => $mark)
                                                    <tr>
                                                        <td>Question {{ $qIndex + 1 }}</td>
                                                        <td><b>{{ $mark }}</b></td>
                                                    </tr>
                                                @endforeach
                                                <tr class="table-{{ $candidateResult->exam_format == 'clinical' ? 'danger' : 'warning' }}">
                                                    <th>Subtotal</th>
                                                    <th>{{ array_sum($marks) }}</th>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="info-box" style="background-color: {{ $candidateResult->exam_format == 'clinical' ? '#ffe6e6' : '#fff9e6' }};">
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Marks</span>
                                                    <span class="info-box-number" style="font-size: 2rem; color: {{ $candidateResult->exam_format == 'clinical' ? '#a02626' : '#FEC503' }};">
                                                    {{ $result->total }}
                                                </span>
                                                </div>
                                            </div>

                                            <h6 class="font-weight-bold mt-3">Examiner Remarks:</h6>
                                            <div class="alert alert-light" style="word-wrap: break-word; white-space: normal;">
                                                {{ $result->remarks ?? 'No remarks provided' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No result data found for this station.
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Add smooth animations
            $('.card').hide().fadeIn(600);
        });
    </script>
@endsection

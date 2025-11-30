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
                    <h3 class="card-title">Candidate Full Results (Clinical + Viva)</h3>
                </div>
                <div class="card-body">
                    @if($candidateResult)
                        <table class="table table-bordered mb-4">
                            <tr>
                                <th>Candidate ID</th>
                                <td>{{ $candidateResult->candidate_name }}</td>
                            </tr>
                            <tr>
                                <th>Group Name</th>
                                <td>{{ $candidateResult->group_name }}</td>
                            </tr>
                            <tr>
                                <th>Total Marks</th>
                                <td>{{ $candidateResult->overall_total }}</td>
                            </tr>
                        </table>

                        <div class="row">
                            <!-- Clinical Marks -->
                            <div class="col-md-6">
                                <h5 style="color:#a02626;">Clinical Marks</h5>
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Station ID</th>
                                        <th>Question</th>
                                        <th>Marks</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($candidateResult->clinical_records as $rec)
                                        @php
                                            $marks = json_decode($rec->question_mark, true) ?? [];
                                            $questionLabels = [
                                                0 => 'Overall Professional Capacity and Patient Care',
                                                1 => 'Knowledge and Judgement',
                                                2 => 'Quality of Response',
                                                3 => 'Bedside Manner'
                                            ];
                                        @endphp
                                        <tr>
                                            <td rowspan="{{ count($marks) + 2 }}" style="vertical-align: middle;">
                                                <b>{{ $rec->station_id }}</b>
                                            </td>
                                        </tr>
                                        @foreach($marks as $i => $m)
                                            <tr>
                                                <td>
                                                    @if(isset($questionLabels[$i]))
                                                        {{ $questionLabels[$i] }}
                                                    @else
                                                        Question {{ $i + 1 }}
                                                    @endif
                                                </td>
                                                <td><b>{{ $m }}</b></td>
                                            </tr>
                                        @endforeach
                                        <tr style="background-color: #f8f9fa;">
                                            <td><b>Total</b></td>
                                            <td><b>{{ $rec->total }}</b></td>
                                        </tr>
                                        @if($rec->remarks)
                                            <tr>
                                                <td colspan="3">
                                                    <b>Remarks:</b> {{ $rec->remarks }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                    <tfoot style="background-color: #a02626; color: white;">
                                    <tr>
                                        <th colspan="2">Clinical Total</th>
                                        <th><b>{{ $candidateResult->clinical_total }}</b></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Viva Marks -->
                            <div class="col-md-6">
                                <h5 style="color:#a02626;">Viva Marks</h5>
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Station ID</th>
                                        <th>Question</th>
                                        <th>Marks</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($candidateResult->viva_records as $rec)
                                        @php
                                            $marks = json_decode($rec->question_mark, true) ?? [];
                                        @endphp
                                        <tr>
                                            <td rowspan="{{ count($marks) + 2 }}" style="vertical-align: middle;">
                                                <b>{{ $rec->station_id }}</b>
                                            </td>
                                        </tr>
                                        @foreach($marks as $i => $m)
                                            <tr>
                                                <td>Question {{ $i + 1 }}</td>
                                                <td><b>{{ $m }}</b></td>
                                            </tr>
                                        @endforeach
                                        <tr style="background-color: #f8f9fa;">
                                            <td><b>Total</b></td>
                                            <td><b>{{ $rec->total }}</b></td>
                                        </tr>
                                        @if($rec->remarks)
                                            <tr>
                                                <td colspan="3">
                                                    <b>Remarks:</b> {{ $rec->remarks }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                    <tfoot style="background-color: #a02626; color: white;">
                                    <tr>
                                        <th colspan="2">Viva Total</th>
                                        <th><b>{{ $candidateResult->viva_total }}</b></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <a href="{{ url('examiner/fcs-resubmit/' . $candidateResult->candidate_id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Resubmit
                            </a>
                        </div>
                    @else
                        <p>No result data found for this candidate.</p>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <style>
        .table td, .table th {
            vertical-align: middle;
        }

        .table-bordered td, .table-bordered th {
            border: 1px solid #dee2e6;
        }
    </style>
@endsection

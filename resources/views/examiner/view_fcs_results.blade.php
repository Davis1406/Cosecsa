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
                                        <th>Question Marks</th>
                                        <th>Total</th>
                                        <th>Remarks</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($candidateResult->clinical_records as $rec)
                                        @php
                                            $marks = json_decode($rec->question_mark, true) ?? [];
                                        @endphp
                                        <tr>
                                            <td>{{ $rec->station_id }}</td>
                                            <td>
                                                @foreach($marks as $i => $m)
                                                    Q{{ $i+1 }}: {{ $m }}<br>
                                                @endforeach
                                            </td>
                                            <td><b>{{ $rec->total }}</b></td>
                                            <td style="word-wrap: break-word; white-space: normal; max-width: 150px;">
                                                {{ $rec->remarks ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th colspan="2">Clinical Total</th>
                                        <th colspan="2"><b>{{ $candidateResult->clinical_total }}</b></th>
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
                                        <th>Question Marks</th>
                                        <th>Total</th>
                                        <th>Remarks</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($candidateResult->viva_records as $rec)
                                        @php
                                            $marks = json_decode($rec->question_mark, true) ?? [];
                                        @endphp
                                        <tr>
                                            <td>{{ $rec->station_id }}</td>
                                            <td>
                                                @foreach($marks as $i => $m)
                                                    Q{{ $i+1 }}: {{ $m }}<br>
                                                @endforeach
                                            </td>
                                            <td><b>{{ $rec->total }}</b></td>
                                            <td style="word-wrap: break-word; white-space: normal; max-width: 150px;">
                                                {{ $rec->remarks ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th colspan="2">Viva Total</th>
                                        <th colspan="2"><b>{{ $candidateResult->viva_total }}</b></th>
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
@endsection

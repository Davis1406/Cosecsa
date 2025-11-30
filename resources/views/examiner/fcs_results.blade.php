@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <div class="col-md-12">
                @include('_message')
            </div>

            <section class="content">
                <div class="container-wrapper">
                    <div class="row">
                        <div class="col-12">
                            <div class="card" style="margin-top: 20px">
                                <div class="card-header">
                                    <h3 class="card-title">Candidates Results</h3>
                                </div>

                                <div class="card-body">
                                    <table id="resultstable" class="table table-bordered table-striped">
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
                                        @foreach ($getRecord as $value)
                                            <tr>
                                                <td>{{ $value->candidate_name }}</td>
                                                <td>{{ $value->group_name }}</td>
                                                <td><b>{{ $value->clinical_total }}</b></td>
                                                <td><b>{{ $value->viva_total }}</b></td>
                                                <td><b>{{ $value->overall_total }}</b></td>

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
                </div>
            </section>

        </div>
    </div>
@endsection


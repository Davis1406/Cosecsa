@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
            </section>

            <div class="col-md-12">
                @include('_message')
            </div>

            <section class="content">
                <div class="container-wrapper">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $programmeName }} Results - {{ now()->year }}</h3>
                                </div>

                                <div class="card-body">
                                    <table id="fcsresultstable" class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th rowspan="2">Candidate ID</th>
                                            <th rowspan="2">Name</th>
                                            <th rowspan="2">Group</th>
                                            <th colspan="8" class="text-center" style="background-color: #a02626; color: white;">Clinical Stations</th>
                                            <th colspan="8" class="text-center" style="background-color: #FEC503; color: #a02626;">Viva Stations</th>
                                            <th rowspan="2">Clinical Total</th>
                                            <th rowspan="2">Viva Total</th>
                                            <th rowspan="2">Overall Total</th>
                                        </tr>
                                        <tr>
                                            <!-- Clinical Station Headers -->
                                            @for ($i = 1; $i <= 8; $i++)
                                                <th style="background-color: #d94545; color: white;">S{{ $i }}</th>
                                            @endfor
                                            <!-- Viva Station Headers -->
                                            @for ($i = 1; $i <= 8; $i++)
                                                <th style="background-color: #ffd966; color: #a02626;">S{{ $i }}</th>
                                            @endfor
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($getResults as $value)
                                            <tr>
                                                <td>{{ $value->candidate_id }}</td>
                                                <td>{{ $value->fullname }}</td>
                                                <td>{{ $value->group_name }}</td>

                                                <!-- Clinical Stations -->
                                                @for ($i = 1; $i <= 8; $i++)
                                                    <td style="background-color: #ffe6e6;">
                                                        @if(isset($value->clinical_stations[$i]))
                                                            <a href="{{ url('admin/exams/fcs-station-results/' . $value->cnd_id . '/' . $i . '/clinical/' . $tableRoute) }}"
                                                               style="color: inherit; text-decoration: none;">
                                                                {{ $value->clinical_stations[$i] }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endfor

                                                <!-- Viva Stations -->
                                                @for ($i = 1; $i <= 8; $i++)
                                                    <td style="background-color: #fff9e6;">
                                                        @if(isset($value->viva_stations[$i]))
                                                            <a href="{{ url('admin/exams/fcs-station-results/' . $value->cnd_id . '/' . $i . '/viva/' . $tableRoute) }}"
                                                               style="color: inherit; text-decoration: none;">
                                                                {{ $value->viva_stations[$i] }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endfor

                                                <td><b style="color: #a02626;">{{ $value->clinical_total }}</b></td>
                                                <td><b style="color: #FEC503;">{{ $value->viva_total }}</b></td>
                                                <td><b>{{ $value->overall_total }}</b></td>
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

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
                                <h3 class="card-title">Candidates Results - {{ now()->year }}</h3>
                            </div>

                            <div class="card-body">
                                <table id="adminresultstable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Candidate ID</th>
                                            <th>Name</th>
                                            @for ($i = 1; $i <= 8; $i++)
                                                <th>Station {{ $i }}</th>
                                            @endfor
                                            <th>Total Marks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getResults as $value)
                                        <tr>
                                            <td>{{ $value->candidate_id }}</td>
                                            <td>{{ $value->fullname }}</td>
                                            @for ($i = 1; $i <= 8; $i++)
                                                <td>
                                                    @php
                                                        $station = collect($value->stations)->firstWhere('station_id', $i);
                                                    @endphp
                                                    @if ($station)
                                                        <a href="{{ url('admin/exams/station_results/' . $value->cnd_id . '/' . $station['station_id']) }}" 
                                                           style="color: inherit; text-decoration: none;">
                                                           {{ $station['total'] }}
                                                        </a>
                                                    @else
                                                        <!-- Display a placeholder if no data is available for this station -->
                                                        {{ ' ' }}
                                                    @endif
                                                </td>
                                            @endfor
                                            <td><b>{{ array_sum(array_column($value->stations, 'total')) }}</b></td> <!-- Sum of all station totals -->
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

@section('scripts')
<script>
    $(document).ready(function(){
        $('[data-toggle="popover"]').popover();
    });
</script>
@endsection

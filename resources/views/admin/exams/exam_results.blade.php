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
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
                                <h3 class="card-title mb-0">MCS Results — {{ $selectedYearName }}</h3>
                                <form method="GET" action="{{ url('admin/exams/exam_results') }}" class="d-flex align-items-center flex-shrink-0" style="gap:.4rem;">
                                    <select name="year_id" class="form-control form-control-sm flex-shrink-0" style="width:110px;">
                                        @foreach($allYears as $yr)
                                            <option value="{{ $yr->id }}" {{ $selectedYearId == $yr->id ? 'selected' : '' }}>
                                                {{ $yr->year_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-danger flex-shrink-0" style="white-space:nowrap;padding:4px 12px;font-size:.85rem;">
                                        Filter
                                    </button>
                                </form>
                            </div>

                            <div class="card-body">
                                {{-- Min total score filter --}}
                                <div class="mb-3 d-flex align-items-center" style="gap:.5rem;">
                                    <label class="mb-0 small font-weight-bold text-muted">Min Total Score:</label>
                                    <input type="number" id="mcsMinScore" class="form-control form-control-sm"
                                           min="0" placeholder="e.g. 150" style="width:110px;">
                                    <button id="mcsClearScore" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times mr-1"></i>Clear
                                    </button>
                                    <small class="text-muted ml-2" id="mcsFilteredCount"></small>
                                </div>
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
                                        @php $mcsTotal = array_sum(array_column($value->stations, 'total')); @endphp
                                        <tr data-total="{{ $mcsTotal }}">
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

@push('scripts')
<script>
$(document).ready(function () {

    function redraw() {
        var dt   = $('#adminresultstable').DataTable();
        dt.draw();
        var info = dt.page.info();
        $('#mcsFilteredCount').text(
            info.recordsDisplay < info.recordsTotal
                ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : ''
        );
    }

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'adminresultstable') return true;
        var min = parseInt($('#mcsMinScore').val(), 10);
        if (isNaN(min)) return true;
        var $row  = $($(settings.nTable).DataTable().row(dataIndex).node());
        return parseInt($row.data('total'), 10) >= min;
    });

    $('#mcsMinScore').on('input', redraw);
    $('#mcsClearScore').on('click', function () {
        $('#mcsMinScore').val('');
        redraw();
        $('#mcsFilteredCount').text('');
    });
});
</script>
@endpush

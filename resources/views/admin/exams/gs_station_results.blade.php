@extends('layout.app')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/exams/gs_results') }}" class="btn btn-primary" style="background-color: #a02626; border-color:#a02626">
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
        <div class="d-flex flex-wrap justify-content-between">
            <!-- Cards for Other Examiners' Results -->
            @if ($allResults && count($allResults) > 0)
                @foreach ($allResults as $result)
                    <div class="card" style="width: 48%; margin-bottom: 20px;">
                        <div class="card-header">
                            <h3 class="card-title">Examiner: {{ $result->examiner_name }}</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Examiner ID</th>
                                    <td>{{ $result->examiner_id }}</td>
                                </tr>
                                <tr>
                                    <th>Total Marks</th>
                                    <td>{{ $result->total }}</td>
                                </tr>
                                @php
                                    $marks = json_decode($result->question_mark, true);
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
                                    <th>Remarks</th>
                                    <td>{{ $result->remarks }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <p>No results available for other examiners on this station.</p>
            @endif
        </div>
    </section>
</div>

@endsection

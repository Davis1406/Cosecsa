@extends('layout.app')

@section('content')
<div class="content-wrapper">
    @include('learning.admin._head', ['title' => 'Examiner Training', 'subtitle' => $course['title']])
    <section class="content">
        <div class="container-fluid lms">
            @include('learning.partials.flash')
            @include('learning.admin._tabs')

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card card-pad mb-3 mb-md-0">
                        <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase;">Confirmed examiners</div>
                        <div style="font-size:30px; font-weight:800;">{{ count($learners) }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-pad">
                        <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase;">Course modules</div>
                        <div style="font-size:30px; font-weight:800;">{{ count($modules) }}</div>
                    </div>
                </div>
            </div>

            <div class="card card-pad">
                <h5 style="font-size:16px; font-weight:700; margin-bottom:16px;">Learner progress</h5>
                <div class="table-responsive">
                    <table class="tbl" id="learners-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Modules done</th>
                                <th>Progress</th>
                                <th>Quiz score</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($learners as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="muted">{{ $row['email'] }}</td>
                                    <td data-order="{{ $row['completed'] }}">{{ $row['completed'] }} / {{ $row['total'] }}</td>
                                    <td style="width:200px;" data-order="{{ $row['percent'] }}">
                                        <div class="progress-track"><div class="progress-fill" style="width: {{ $row['percent'] }}%;"></div></div>
                                    </td>
                                    <td data-order="{{ $row['quiz_score'] ?? -1 }}">
                                        @if($row['quiz_score'] !== null) {{ round($row['quiz_score']) }}% @else <span class="muted">—</span> @endif
                                    </td>
                                    <td><a href="{{ route('admin.exams.learning.user', $row['id']) }}" class="btn btn-light btn-sm">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
@endpush

@push('scripts')
<script>
    $(function () {
        $('#learners-table').DataTable({ pageLength: 25, order: [[3, 'desc']], columnDefs: [{ orderable: false, targets: 5 }] });
    });
</script>
@endpush

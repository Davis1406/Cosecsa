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
                        <div class="alert alert-warning d-flex align-items-center" style="gap:.5rem;">
                            <i class="fas fa-flask"></i>
                            <div>
                                <strong>This is test data, not real exam results.</strong>
                                It comes from the Examiner App's temporary test clone
                                (isolated <code>test_*</code> tables — see cosecsa-api's
                                <code>TESTING.md</code>), created 2026-08-17 and set to
                                auto-expire 2026-08-20. Nothing here affects real
                                candidates, examiners, or results.
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">{{ $header_title }}</h3>
                            </div>

                            <div class="card-body">
                                <table id="testresultstable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Specialty</th>
                                            <th>Candidate ID</th>
                                            <th>Group</th>
                                            <th>Examiner</th>
                                            <th>Station</th>
                                            <th>Format</th>
                                            <th>Total</th>
                                            <th>Overall</th>
                                            <th>Remarks</th>
                                            <th>Submitted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($results as $r)
                                        <tr>
                                            <td>{{ $r->specialty }}</td>
                                            <td>{{ $r->candidate_code }}</td>
                                            <td>{{ $r->group_name }}</td>
                                            <td>{{ $r->examiner_name }}</td>
                                            <td>{{ $r->station_id }}</td>
                                            <td>{{ $r->exam_format ?? '—' }}</td>
                                            <td>{{ $r->total }}</td>
                                            <td>{{ $r->overall ?? '—' }}</td>
                                            <td>{{ $r->remarks ?? '' }}</td>
                                            <td>{{ $r->created_at }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted">
                                                No test submissions yet. Have an examiner log into the
                                                test build of the Examiner App and mark a candidate.
                                            </td>
                                        </tr>
                                        @endforelse
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
    $('#testresultstable').DataTable({ order: [[9, 'desc']] });
});
</script>
@endpush

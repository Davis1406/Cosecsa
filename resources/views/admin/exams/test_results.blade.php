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
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">{{ $header_title }}</h3>
                                @if(!$results->isEmpty())
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteAll()">
                                    <i class="fas fa-trash-alt mr-1"></i>Delete All
                                </button>
                                @endif
                            </div>

                            <div class="card-body">
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    General Surgery and MCS have a single format (no Clinical/Viva
                                    split) by design. General Surgery is always run as Clinical, so
                                    its Format column reads <strong>Clinical</strong>; MCS has no
                                    equivalent real-world label so it reads <strong>N/A</strong> —
                                    both are expected, not missing data.
                                </p>
                                <div class="table-responsive">
                                    <table id="testresultstable" class="table table-bordered table-striped" style="min-width:900px;">
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
                                                <th style="max-width:220px;">Remarks</th>
                                                <th>Submitted</th>
                                                <th class="text-center" data-orderable="false">Delete</th>
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
                                                <td>
                                                    @if (is_null($r->exam_format) && $r->specialty === 'General Surgery')
                                                        Clinical
                                                    @elseif (is_null($r->exam_format))
                                                        <span class="text-muted">N/A</span>
                                                    @else
                                                        {{ ucfirst($r->exam_format) }}
                                                    @endif
                                                </td>
                                                <td>{{ $r->total }}</td>
                                                <td>{{ $r->overall ?? '—' }}</td>
                                                <td style="max-width:220px;white-space:normal;word-break:break-word;">{{ $r->remarks ?? '' }}</td>
                                                <td style="white-space:nowrap;">{{ $r->created_at }}</td>
                                                <td class="text-center">
                                                    <form method="POST"
                                                          action="{{ route('test_results.destroy.record', [$r->specialty, $r->id]) }}"
                                                          onsubmit="return confirm('Delete this test result ({{ addslashes($r->candidate_code) }}, {{ addslashes($r->specialty) }})?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete" style="padding:2px 7px;">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">
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
            </div>
        </section>
    </div>
</div>

{{-- Hidden delete-all form --}}
<form id="deleteAllForm" method="POST" action="{{ route('test_results.destroy.all') }}" style="display:none;">
    @csrf
</form>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#testresultstable').DataTable({ order: [[9, 'desc']], columnDefs: [{ orderable: false, targets: -1 }] });
});

function confirmDeleteAll() {
    if (confirm('Delete ALL test results? This clears every specialty in the Examiner App test clone and cannot be undone.')) {
        $('#deleteAllForm').submit();
    }
}
</script>
@endpush

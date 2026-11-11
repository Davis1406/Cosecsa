@extends('layout.app')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/exams/exam_results') }}" class="btn btn-primary" style="background-color: #a02626; border-color:#a02626">
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
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Candidate's Results</h3>
            </div>
            <div class="card-body">
                @if ($candidateResult)
                    {{-- Candidate + station header (shared across all examiners) --}}
                    <table class="table table-bordered mb-3" style="width:100%;">
                        <tr>
                            <th style="width:200px;">Candidate ID</th>
                            <td>{{ $candidateResult->candidate_name }}</td>
                        </tr>
                        <tr>
                            <th>Group</th>
                            <td>Group {{ $candidateResult->group_name }}</td>
                        </tr>
                        <tr>
                            <th>Station</th>
                            <td>Station {{ $candidateResult->station_id }}</td>
                        </tr>
                    </table>

                    {{-- One block per examiner --}}
                    @foreach ($allResults ?? [$candidateResult] as $i => $result)
                        <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 mb-1">
                            <h6 class="mb-0" style="color:#a02626;">
                                Examiner {{ $i + 1 }}: {{ $result->examin_id }} — {{ $result->examiner_name }}
                            </h6>
                            @if (!empty($result->result_id) && $stations->count() > 1)
                                <div class="form-inline d-print-none move-station" data-id="{{ $result->result_id }}" data-from="{{ $result->station_id }}">
                                    <label class="small text-muted mr-2 mb-0">Examined at a different station?</label>
                                    <select class="form-control form-control-sm mr-2 move-station-to">
                                        <option value="">Move to station…</option>
                                        @foreach ($stations as $st)
                                            @if ((int) $st->number !== (int) $result->station_id)
                                                <option value="{{ $st->number }}">{{ $st->name ?: 'Station ' . $st->number }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-sm btn-primary move-station-go" style="background-color:#a02626;border-color:#a02626;">
                                        <i class="fas fa-exchange-alt"></i> Move
                                    </button>
                                </div>
                            @endif
                        </div>
                        <table class="table table-bordered mb-3" style="width:100%;">
                            @php $marks = json_decode($result->question_mark, true); @endphp
                            @if(is_array($marks))
                                @foreach($marks as $index => $mark)
                                    <tr>
                                        <th style="width:200px;">Question {{ $index + 1 }}</th>
                                        <td>{{ $mark }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="2">No question marks available</td></tr>
                            @endif
                            <tr>
                                <th>Total Marks</th>
                                <td><b>{{ $result->total }}</b></td>
                            </tr>
                            <tr>
                                <th>Overall Grade</th>
                                <td>{{ $result->overall }}</td>
                            </tr>
                            <tr>
                                <th>Remarks</th>
                                <td>{{ $result->remarks }}</td>
                            </tr>
                        </table>
                    @endforeach
                @else
                    <p>No result data found for this candidate.</p>
                @endif
            </div>
        </div>
    </section>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    var moveUrl = @json(url('admin/exams/station_results/move'));
    var csrf = $('meta[name="csrf-token"]').attr('content') || @json(csrf_token());

    function send(box, to, confirm) {
        return $.ajax({
            url: moveUrl, method: 'POST', dataType: 'json', contentType: 'application/json',
            data: JSON.stringify({ result_ids: [box.data('id')], to_station: to, confirm: !!confirm }),
            headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' }
        });
    }
    function done(msg) {
        // the mark is no longer at this station: show where it went on the results page
        CosecsaAlert.alert(msg, { variant: 'success' }).then(function () { window.location.href = @json(url('admin/exams/exam_results')); });
    }
    function failed(x) {
        CosecsaAlert.alert((x.responseJSON && x.responseJSON.message) || 'Something went wrong. Nothing was moved.', { variant: 'warning' });
    }

    $('.move-station-go').on('click', function () {
        var box = $(this).closest('.move-station'), btn = $(this), to = parseInt(box.find('.move-station-to').val(), 10);
        if (!to) { failed({ responseJSON: { message: 'Choose the station to move this mark to.' } }); return; }
        CosecsaAlert.confirm('Move this examiner\'s marks for this candidate from Station ' + box.data('from') + ' to Station ' + to + '? The marks themselves do not change, and a copy of how they were is kept.', { variant: 'question', confirmText: 'Move' }).then(function (ok) {
            if (!ok) return;
            btn.prop('disabled', true);
            send(box, to, false).done(function (r) { done(r.message); }).fail(function (x) {
                if (x.status === 409 && x.responseJSON && x.responseJSON.needs_confirm) {
                    CosecsaAlert.confirm(x.responseJSON.message, { variant: 'warning' }).then(function (ok2) {
                        if (!ok2) { btn.prop('disabled', false); return; }
                        send(box, to, true).done(function (r) { done(r.message); }).fail(function (y) { btn.prop('disabled', false); failed(y); });
                    });
                } else { btn.prop('disabled', false); failed(x); }
            });
        });
    });
});
</script>
@endpush

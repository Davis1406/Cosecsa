@extends('layout.app')

@section('title', 'Capsule CRM Sync')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Capsule CRM Sync</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Capsule CRM</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            {{-- Comparison Cards --}}
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3 id="capsule-count"><i class="fas fa-spinner fa-spin"></i></h3>
                            <p>Contacts in Capsule CRM</p>
                        </div>
                        <div class="icon"><i class="fas fa-address-book"></i></div>
                        <a href="https://cosecsatrainees.capsulecrm.com/parties" target="_blank" class="small-box-footer">
                            View in Capsule <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ number_format($totalFellows) }}</h3>
                            <p>Fellows in MIS</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <a href="{{ url('admin/associates/fellows/list') }}" class="small-box-footer">
                            View Fellows <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary" id="diff-card">
                        <div class="inner">
                            <h3 id="diff-count"><i class="fas fa-spinner fa-spin"></i></h3>
                            <p>Difference</p>
                        </div>
                        <div class="icon"><i class="fas fa-not-equal"></i></div>
                        <span class="small-box-footer" id="diff-label">Loading…</span>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    @php $lastCompleted = $lastSync && in_array($lastSync->status ?? '', ['completed','failed']) ? $lastSync : null; @endphp
                    <div class="small-box {{ $lastCompleted ? 'bg-secondary' : 'bg-light' }}">
                        <div class="inner" style="{{ $lastCompleted ? '' : 'color:#555' }}">
                            <h3 style="font-size:1.3rem;">{{ $lastCompleted ? \Carbon\Carbon::parse($lastCompleted->synced_at)->diffForHumans() : 'Never' }}</h3>
                            <p>Last Sync</p>
                        </div>
                        <div class="icon"><i class="fas fa-history"></i></div>
                        <span class="small-box-footer">
                            @if($lastCompleted)
                                {{ number_format($lastCompleted->created) }} created &middot;
                                {{ number_format($lastCompleted->updated) }} updated &middot;
                                {{ number_format($lastCompleted->failed) }} failed
                            @else
                                No sync run yet
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-sync-alt mr-2"></i>Sync Fellows → Capsule CRM</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Syncs <strong>all {{ number_format($totalFellows) }} MIS fellows</strong>
                                ({{ number_format($withEmail) }} with email · {{ number_format($withoutEmail) }} without email)
                                into <a href="https://cosecsatrainees.capsulecrm.com/parties" target="_blank">Capsule CRM</a>.
                                Runs in the background — you can leave this page.
                            </p>
                            <ul class="text-muted mb-3" style="font-size:0.92em;">
                                <li>Fellows <strong>with email</strong>: matched by email address.</li>
                                <li>Fellows <strong>without email</strong>: matched by full name as fallback.</li>
                                <li>No match → new contact created. Existing → updated.</li>
                                <li>Tags assigned from MIS category + COSECSA region.</li>
                                <li>Rate-limited to ~4 req/s. Expect ~8–10 min for {{ number_format($totalFellows) }} records.</li>
                            </ul>

                            {{-- Progress (shown while running) --}}
                            <div id="progress-section" class="{{ $running ? '' : 'd-none' }} mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="font-weight-bold text-primary">Syncing in background…</span>
                                    <span id="progress-label">0 / 0</span>
                                </div>
                                <div class="progress" style="height:20px;">
                                    <div id="progress-bar"
                                         class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                         role="progressbar" style="width:0%">0%</div>
                                </div>
                                <div class="mt-2 small text-muted">
                                    <span id="prog-created">0</span> created &middot;
                                    <span id="prog-updated">0</span> updated &middot;
                                    <span id="prog-failed">0</span> failed
                                </div>
                            </div>

                            {{-- Result (shown after completion) --}}
                            <div id="result-section" class="d-none mb-3">
                                <div class="alert" id="result-alert">
                                    <strong id="result-title"></strong>
                                    <span id="result-detail"></span>
                                </div>
                            </div>

                            <button id="syncBtn" class="btn btn-primary btn-lg" {{ $running ? 'disabled' : '' }}>
                                <i class="fas fa-sync-alt mr-2"></i>
                                {{ $running ? 'Sync Running…' : 'Start Full Sync' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Tag Mapping</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped mb-0">
                                <thead><tr><th>MIS Category</th><th>Capsule Tag</th></tr></thead>
                                <tbody>
                                    <tr><td>Fellow by Examination</td><td><span class="badge badge-success">Fellow</span></td></tr>
                                    <tr><td>Foundation Fellow</td><td><span class="badge badge-success">Fellow</span></td></tr>
                                    <tr><td>Fellow by Election</td><td><span class="badge badge-success">Fellow</span></td></tr>
                                    <tr><td>Associate Fellow</td><td><span class="badge badge-info">Associate Fellow</span></td></tr>
                                    <tr><td>Overseas Fellow</td><td><span class="badge badge-primary">Overseas Fellow</span></td></tr>
                                    <tr><td>Honorary Fellow (ASEA)</td><td><span class="badge badge-secondary">Honorary Fellow</span></td></tr>
                                    <tr><td>Honorary Fellow (COSECSA)</td><td><span class="badge badge-secondary">Honorary Fellow</span></td></tr>
                                    <tr><td>Member</td><td><span class="badge badge-warning">member/fellow</span></td></tr>
                                    <tr><td><em>status = Deceased</em></td><td><span class="badge badge-dark">Deceased Fellow</span></td></tr>
                                    <tr><td><em>cosecsa_region</em></td><td><span class="badge badge-light border">Region tag</span></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
var misFellows = {{ $totalFellows }};
var pollTimer  = null;

// ── Load Capsule count lazily ──────────────────────────────────────────────
function loadCapsuleCount() {
    $.getJSON('{{ url("admin/capsule/count") }}', function(res) {
        var count = res.count;
        if (count === null || count === undefined) {
            $('#capsule-count').text('—');
            $('#diff-count').text('—');
            $('#diff-label').text('Could not reach Capsule API');
            return;
        }
        $('#capsule-count').text(count.toLocaleString());

        var diff = misFellows - count;
        var absDiff = Math.abs(diff);
        $('#diff-count').text(absDiff.toLocaleString());

        if (diff === 0) {
            $('#diff-card').removeClass('bg-secondary bg-warning').addClass('bg-success');
            $('#diff-label').text('In sync ✓');
        } else if (diff > 0) {
            $('#diff-card').removeClass('bg-secondary bg-success').addClass('bg-warning');
            $('#diff-label').text(absDiff.toLocaleString() + ' more in MIS → need sync');
        } else {
            $('#diff-card').removeClass('bg-secondary bg-success').addClass('bg-info');
            $('#diff-label').text(absDiff.toLocaleString() + ' more in Capsule');
        }
    }).fail(function() {
        $('#capsule-count').text('Error');
        $('#diff-count').text('—');
    });
}

// ── Poll sync status ───────────────────────────────────────────────────────
function pollStatus() {
    $.getJSON('{{ url("admin/capsule/status") }}', function(res) {
        if (res.status === 'running') {
            $('#syncBtn').prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin mr-2"></i>Sync Running…');
            $('#progress-section').removeClass('d-none');
            $('#result-section').addClass('d-none');

            var pct = res.percent || 0;
            $('#progress-bar').css('width', pct + '%').text(pct + '%');
            $('#progress-label').text(res.progress.toLocaleString() + ' / ' + res.total.toLocaleString());
            $('#prog-created').text(res.created.toLocaleString());
            $('#prog-updated').text(res.updated.toLocaleString());
            $('#prog-failed').text(res.failed.toLocaleString());

            pollTimer = setTimeout(pollStatus, 5000);
        } else {
            clearTimeout(pollTimer);
            $('#syncBtn').prop('disabled', false).html('<i class="fas fa-sync-alt mr-2"></i>Start Full Sync');
            $('#progress-section').addClass('d-none');

            if (res.status === 'completed') {
                $('#result-alert').removeClass('alert-danger').addClass('alert-success');
                $('#result-title').text('Sync complete. ');
                $('#result-detail').text(
                    res.total.toLocaleString() + ' processed: ' +
                    res.created.toLocaleString() + ' created, ' +
                    res.updated.toLocaleString() + ' updated, ' +
                    res.failed.toLocaleString() + ' failed.'
                );
                $('#result-section').removeClass('d-none');
                loadCapsuleCount(); // refresh count after sync
            }
        }
    });
}

$(function () {
    loadCapsuleCount();

    @if($running)
    // A sync was already running when the page loaded — start polling immediately
    pollStatus();
    @endif

    $('#syncBtn').on('click', function () {
        if (!confirm('Start full sync of all {{ $totalFellows }} fellows to Capsule CRM?\n\nThis runs in the background and may take ~10 minutes.')) return;

        $.ajax({
            url: '{{ url("admin/capsule/sync") }}',
            method: 'POST',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(res) {
                if (res.success) {
                    $('#progress-section').removeClass('d-none');
                    $('#result-section').addClass('d-none');
                    $('#syncBtn').prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin mr-2"></i>Sync Running…');
                    toastr.info('Sync started in the background. Progress will update below.');
                    setTimeout(pollStatus, 3000);
                } else {
                    toastr.warning(res.message || 'Could not start sync.');
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to start sync.';
                toastr.error(msg);
            }
        });
    });
});
</script>
@endsection

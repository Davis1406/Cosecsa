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
                {{-- Capsule count from local import --}}
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $capsuleTotal !== null ? number_format($capsuleTotal) : '—' }}</h3>
                            <p>Davis Fellows (Capsule)</p>
                        </div>
                        <div class="icon"><i class="fas fa-address-book"></i></div>
                        <a href="{{ url('admin/capsule/contacts') }}" class="small-box-footer">
                            View imported list <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                {{-- MIS count --}}
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

                {{-- Difference --}}
                <div class="col-lg-3 col-6">
                    @php
                        $diffClass = 'bg-secondary';
                        $diffLabel = 'Import Capsule list first';
                        if ($difference !== null) {
                            if ($difference === 0)  { $diffClass = 'bg-success'; $diffLabel = 'In sync ✓'; }
                            elseif ($difference > 0){ $diffClass = 'bg-warning'; $diffLabel = number_format(abs($difference)) . ' more in MIS'; }
                            else                    { $diffClass = 'bg-info';    $diffLabel = number_format(abs($difference)) . ' more in Capsule'; }
                        }
                    @endphp
                    <div class="small-box {{ $diffClass }}">
                        <div class="inner">
                            <h3>{{ $difference !== null ? number_format(abs($difference)) : '—' }}</h3>
                            <p>Difference</p>
                        </div>
                        <div class="icon"><i class="fas fa-not-equal"></i></div>
                        <a href="{{ url('admin/capsule/differences') }}" class="small-box-footer">
                            {{ $diffLabel }} — View details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                {{-- Last import --}}
                <div class="col-lg-3 col-6">
                    <div class="small-box {{ $lastImport ? 'bg-secondary' : 'bg-light' }}">
                        <div class="inner" style="{{ $lastImport ? '' : 'color:#555' }}">
                            <h3 style="font-size:1.3rem;">
                                {{ $lastImport ? \Carbon\Carbon::parse($lastImport)->diffForHumans() : 'Never' }}
                            </h3>
                            <p>Last Capsule Import</p>
                        </div>
                        <div class="icon"><i class="fas fa-cloud-download-alt"></i></div>
                        <span class="small-box-footer">
                            {{ $lastImport ? \Carbon\Carbon::parse($lastImport)->format('d M Y H:i') : 'No import yet' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Import Card --}}
                <div class="col-lg-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-cloud-download-alt mr-2"></i>Import Davis Fellows from Capsule</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Fetches all contacts in the <strong>Davis Fellows</strong> saved list from Capsule CRM
                                and stores them locally so the count is always instant.
                                Runs in the background — takes ~2 minutes.
                            </p>
                            <div id="import-result" class="d-none mb-3"></div>
                            <button id="importBtn" class="btn btn-primary">
                                <i class="fas fa-cloud-download-alt mr-2"></i>Import / Refresh Capsule List
                            </button>
                            @if($lastImport)
                            <a href="{{ url('admin/capsule/contacts') }}" class="btn btn-outline-primary ml-2">
                                <i class="fas fa-table mr-1"></i>View Imported Contacts
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sync to Capsule Card --}}
                <div class="col-lg-6">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-sync-alt mr-2"></i>Sync Fellows → Capsule CRM</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Pushes all <strong>{{ number_format($totalFellows) }} MIS fellows</strong>
                                ({{ number_format($withEmail) }} with email · {{ number_format($withoutEmail) }} without)
                                to Capsule CRM. Runs in the background (~10 min).
                            </p>

                            {{-- Progress --}}
                            <div id="progress-section" class="{{ $running ? '' : 'd-none' }} mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="font-weight-bold text-success">Syncing in background…</span>
                                    <span id="progress-label">0 / 0</span>
                                </div>
                                <div class="progress" style="height:20px;">
                                    <div id="progress-bar"
                                         class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                         role="progressbar" style="width:0%">0%</div>
                                </div>
                                <div class="mt-2 small text-muted">
                                    <span id="prog-created">0</span> created &middot;
                                    <span id="prog-updated">0</span> updated &middot;
                                    <span id="prog-failed">0</span> failed
                                </div>
                            </div>

                            <div id="result-section" class="d-none mb-3">
                                <div class="alert" id="result-alert">
                                    <strong id="result-title"></strong>
                                    <span id="result-detail"></span>
                                </div>
                            </div>

                            <button id="syncBtn" class="btn btn-success btn-lg" {{ $running ? 'disabled' : '' }}>
                                <i class="fas fa-sync-alt mr-2"></i>
                                {{ $running ? 'Sync Running…' : 'Start Full Sync' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tag mapping --}}
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Tag Mapping (MIS → Capsule)</h3>
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
                                    <tr><td>Honorary Fellow (ASEA/COSECSA)</td><td><span class="badge badge-secondary">Honorary Fellow</span></td></tr>
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
// ── Import Capsule contacts ──────────────────────────────────────────────────
$('#importBtn').on('click', function () {
    if (!confirm('Fetch the Davis Fellows list from Capsule CRM?\nThis runs in background (~2 min) and refreshes the local count.')) return;
    var btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Starting…');
    $.ajax({
        url: '{{ url("admin/capsule/import-contacts") }}',
        method: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(res) {
            $('#import-result').removeClass('d-none').html(
                '<div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i>' +
                (res.message || 'Import started.') + ' Reload this page in ~2 min to see updated count.</div>'
            );
            btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-2"></i>Import / Refresh Capsule List');
        },
        error: function() {
            toastr.error('Failed to start import.');
            btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-2"></i>Import / Refresh Capsule List');
        }
    });
});

// ── Sync progress polling ───────────────────────────────────────────────────
var pollTimer = null;

function pollStatus() {
    $.getJSON('{{ url("admin/capsule/status") }}', function(res) {
        if (res.status === 'running') {
            $('#syncBtn').prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin mr-2"></i>Sync Running…');
            $('#progress-section').removeClass('d-none');
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
                $('#result-detail').text(res.total.toLocaleString() + ' processed: ' + res.created.toLocaleString() + ' created, ' + res.updated.toLocaleString() + ' updated, ' + res.failed.toLocaleString() + ' failed.');
                $('#result-section').removeClass('d-none');
            }
        }
    });
}

$(function () {
    @if($running)
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
                    toastr.info('Sync started in the background.');
                    setTimeout(pollStatus, 3000);
                } else {
                    toastr.warning(res.message || 'Could not start sync.');
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON ? xhr.responseJSON.message : 'Failed to start sync.');
            }
        });
    });
});
</script>
@endsection

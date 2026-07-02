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
                            <h3>{{ $capsuleTotal !== null ? number_format($capsuleTotal) : '—' }}</h3>
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
                    @php
                        $diffAbs = $difference !== null ? abs($difference) : null;
                        $diffColor = ($difference === 0) ? 'bg-success' : 'bg-warning';
                        $diffLabel = $difference === null ? '—'
                            : ($difference === 0 ? 'In sync' : ($difference > 0 ? "+{$difference} in MIS" : abs($difference).' more in Capsule'));
                    @endphp
                    <div class="small-box {{ $diffColor }}">
                        <div class="inner">
                            <h3>{{ $diffAbs !== null ? number_format($diffAbs) : '—' }}</h3>
                            <p>Difference</p>
                        </div>
                        <div class="icon"><i class="fas fa-not-equal"></i></div>
                        <span class="small-box-footer">{{ $diffLabel }}</span>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box {{ $lastSync ? 'bg-secondary' : 'bg-light' }}">
                        <div class="inner" style="{{ $lastSync ? '' : 'color:#555' }}">
                            <h3 style="font-size:1.4rem;">{{ $lastSync ? \Carbon\Carbon::parse($lastSync->synced_at)->diffForHumans() : 'Never' }}</h3>
                            <p>Last Sync</p>
                        </div>
                        <div class="icon"><i class="fas fa-history"></i></div>
                        <span class="small-box-footer">
                            @if($lastSync)
                                {{ number_format($lastSync->created) }} created &middot;
                                {{ number_format($lastSync->updated) }} updated &middot;
                                {{ number_format($lastSync->failed) }} failed
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
                                This will sync <strong>all {{ number_format($totalFellows) }} MIS fellows</strong>
                                ({{ number_format($withEmail) }} with email · {{ number_format($withoutEmail) }} without)
                                into your Capsule CRM
                                (<a href="https://cosecsatrainees.capsulecrm.com/parties" target="_blank">{{ $capsuleTotal !== null ? number_format($capsuleTotal).' contacts currently' : 'view' }}</a>).
                            </p>
                            <ul class="text-muted mb-3" style="font-size:0.92em;">
                                <li>Fellows <strong>with email</strong>: matched in Capsule by email address.</li>
                                <li>Fellows <strong>without email</strong>: matched by full name (first + last) as fallback.</li>
                                <li>No match found → a <strong>new contact is created</strong>.</li>
                                <li>Tags assigned from MIS category + COSECSA region (see table →).</li>
                                <li>Rate-limited to ~4 req/s. Expect ~8–10 min for {{ number_format($totalFellows) }} records.</li>
                            </ul>

                            <button id="syncBtn" class="btn btn-primary btn-lg">
                                <i class="fas fa-sync-alt mr-2"></i>Start Full Sync
                            </button>
                            <span id="syncSpinner" class="ml-3 d-none">
                                <i class="fas fa-spinner fa-spin"></i> Syncing, please wait…
                            </span>
                        </div>
                    </div>

                    {{-- Progress / Result --}}
                    <div id="resultCard" class="card card-outline d-none">
                        <div class="card-header">
                            <h3 class="card-title" id="resultTitle">Sync Result</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-info" id="resTotal">—</span>
                                        <h5 class="description-header" id="resTotalN">—</h5>
                                        <span class="description-text">TOTAL</span>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-success"><i class="fas fa-arrow-up"></i></span>
                                        <h5 class="description-header" id="resCreated">—</h5>
                                        <span class="description-text">CREATED</span>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-warning"><i class="fas fa-sync-alt"></i></span>
                                        <h5 class="description-header" id="resUpdated">—</h5>
                                        <span class="description-text">UPDATED</span>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="description-block">
                                        <span class="description-percentage text-danger"><i class="fas fa-times"></i></span>
                                        <h5 class="description-header" id="resFailed">—</h5>
                                        <span class="description-text">FAILED</span>
                                    </div>
                                </div>
                            </div>
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
$(function () {
    $('#syncBtn').on('click', function () {
        if (!confirm('Start full sync of all fellows to Capsule CRM?\n\nThis may take several minutes.')) return;

        $('#syncBtn').prop('disabled', true);
        $('#syncSpinner').removeClass('d-none');
        $('#resultCard').addClass('d-none');

        $.ajax({
            url: '{{ url("admin/capsule/sync") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            timeout: 600000,
            success: function (res) {
                $('#resTotal').text('');
                $('#resTotalN').text(res.total);
                $('#resCreated').text(res.created);
                $('#resUpdated').text(res.updated);
                $('#resFailed').text(res.failed);
                $('#resultTitle').html('<i class="fas fa-check-circle text-success mr-2"></i>Sync Complete');
                $('#resultCard').removeClass('d-none card-danger').addClass('card-success');
                toastr.success('Sync complete: ' + res.created + ' created, ' + res.updated + ' updated, ' + res.failed + ' failed.');
            },
            error: function (xhr) {
                $('#resultTitle').html('<i class="fas fa-times-circle text-danger mr-2"></i>Sync Failed');
                $('#resultCard').removeClass('d-none card-success').addClass('card-danger');
                toastr.error('Sync failed. Check the server log for details.');
            },
            complete: function () {
                $('#syncBtn').prop('disabled', false);
                $('#syncSpinner').addClass('d-none');
            }
        });
    });
});
</script>
@endsection

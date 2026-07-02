@extends('layout.app')

@section('title', 'Capsule – Davis Fellows Contacts')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Davis Fellows – Capsule CRM</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('admin/capsule') }}">Capsule CRM</a></li>
                        <li class="breadcrumb-item active">Contacts</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-address-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total in Davis Fellows list</span>
                            <span class="info-box-number">{{ number_format($totalLocal) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Last imported</span>
                            <span class="info-box-number" style="font-size:1rem;">
                                {{ $lastImport ? \Carbon\Carbon::parse($lastImport)->format('d M Y H:i') : 'Never' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <a href="{{ url('admin/capsule') }}" class="btn btn-outline-secondary mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                    <button id="importBtn2" class="btn btn-primary">
                        <i class="fas fa-cloud-download-alt mr-1"></i> Refresh from Capsule
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i>
                        Imported Contacts
                        @if($search)
                            <small class="text-muted ml-2">filtered by "{{ $search }}"</small>
                        @endif
                    </h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ url('admin/capsule/contacts') }}" class="input-group input-group-sm" style="width:250px;">
                            <input type="text" name="q" class="form-control" placeholder="Search name / email / tag…" value="{{ $search }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                                @if($search)
                                <a href="{{ url('admin/capsule/contacts') }}" class="btn btn-default"><i class="fas fa-times"></i></a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($contacts->isEmpty())
                        <div class="p-4 text-muted text-center">
                            @if($search)
                                No contacts match "{{ $search }}".
                            @else
                                No contacts imported yet. Click <strong>Refresh from Capsule</strong> to import the Davis Fellows list.
                            @endif
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Organisation</th>
                                    <th>Tags</th>
                                    <th>Capsule</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contacts as $i => $c)
                                <tr>
                                    <td class="text-muted" style="font-size:0.85em;">{{ $contacts->firstItem() + $i }}</td>
                                    <td>{{ trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) ?: '—' }}</td>
                                    <td>
                                        @if($c->email)
                                            <a href="mailto:{{ $c->email }}">{{ $c->email }}</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $c->phone ?: '—' }}</td>
                                    <td>{{ $c->organisation ?: '—' }}</td>
                                    <td style="font-size:0.82em;">
                                        @foreach(array_filter(explode(', ', $c->tags ?? '')) as $tag)
                                            <span class="badge badge-secondary">{{ $tag }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($c->capsule_url)
                                            <a href="{{ $c->capsule_url }}" target="_blank" title="Open in Capsule">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                @if($contacts->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left text-muted small pt-1">
                        Showing {{ $contacts->firstItem() }}–{{ $contacts->lastItem() }} of {{ number_format($contacts->total()) }}
                    </div>
                    <div class="float-right">
                        {{ $contacts->links() }}
                    </div>
                </div>
                @endif
            </div>

        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
$('#importBtn2').on('click', function () {
    if (!confirm('Refresh the Davis Fellows list from Capsule CRM?\nRuns in background (~2 min).')) return;
    var btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Importing…');
    $.ajax({
        url: '{{ url("admin/capsule/import-contacts") }}',
        method: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(res) {
            toastr.info((res.message || 'Import started.') + ' Reload in ~2 minutes.');
            btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Refresh from Capsule');
        },
        error: function() {
            toastr.error('Failed to start import.');
            btn.prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Refresh from Capsule');
        }
    });
});
</script>
@endsection

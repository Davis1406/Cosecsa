@extends('layout.app')

@section('title', 'MIS Fellows Not in Capsule')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Fellows Not in Capsule CRM</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('admin/capsule') }}">Capsule CRM</a></li>
                        <li class="breadcrumb-item active">Differences</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fellows in MIS</span>
                            <span class="info-box-number">{{ number_format($totalMis) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-address-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Davis Fellows (Capsule)</span>
                            <span class="info-box-number">{{ number_format($totalCapsule) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Not in Capsule</span>
                            <span class="info-box-number">{{ number_format($fellows->total()) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    <a href="{{ url('admin/capsule') }}" class="btn btn-outline-secondary mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                    <a href="{{ url('admin/capsule') }}" class="btn btn-success">
                        <i class="fas fa-sync-alt mr-1"></i> Run Sync
                    </a>
                </div>
            </div>

            <div class="callout callout-warning">
                <h5><i class="fas fa-info-circle mr-1"></i>How matching works</h5>
                A MIS fellow is considered "in Capsule" if their <strong>email address</strong> matches a Capsule contact,
                or (if no email) their <strong>full name</strong> matches. The {{ number_format($fellows->total()) }} below
                match neither — run <strong>Start Full Sync</strong> on the dashboard to push them to Capsule.
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-times mr-2 text-warning"></i>
                        MIS Fellows with no Capsule match
                        @if($search)
                            <small class="text-muted ml-2">filtered by "{{ $search }}"</small>
                        @endif
                    </h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ url('admin/capsule/differences') }}" class="input-group input-group-sm" style="width:260px;">
                            <input type="text" name="q" class="form-control" placeholder="Search name / email / country…" value="{{ $search }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                                @if($search)
                                <a href="{{ url('admin/capsule/differences') }}" class="btn btn-default"><i class="fas fa-times"></i></a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($fellows->isEmpty())
                        <div class="p-4 text-center text-muted">
                            @if($search)
                                No unmatched fellows match "{{ $search }}".
                            @else
                                All MIS fellows are present in Capsule CRM.
                            @endif
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Email</th>
                                    <th>Country</th>
                                    <th>Year</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fellows as $i => $f)
                                <tr>
                                    <td class="text-muted" style="font-size:0.85em;">{{ $fellows->firstItem() + $i }}</td>
                                    <td>
                                        <a href="{{ url('admin/associates/fellows/view/' . $f->id) }}">
                                            {{ $f->firstname }} {{ $f->lastname }}
                                        </a>
                                    </td>
                                    <td><span class="badge badge-secondary" style="font-size:0.78em;">{{ $f->category_name }}</span></td>
                                    <td>
                                        @if($f->personal_email)
                                            <a href="mailto:{{ $f->personal_email }}">{{ $f->personal_email }}</a>
                                        @else
                                            <span class="badge badge-danger">No email</span>
                                        @endif
                                    </td>
                                    <td>{{ $f->country_name ?? '—' }}</td>
                                    <td>{{ $f->fellowship_year ?: '—' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $f->status === 'Active' ? 'success' : ($f->status === 'Deceased' ? 'dark' : 'secondary') }}">
                                            {{ $f->status ?: '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ url('admin/associates/fellows/view/' . $f->id) }}"
                                           class="btn btn-xs btn-outline-primary" title="View in MIS">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                @if($fellows->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left text-muted small pt-1">
                        Showing {{ $fellows->firstItem() }}–{{ $fellows->lastItem() }} of {{ number_format($fellows->total()) }}
                    </div>
                    <div class="float-right">
                        {{ $fellows->links() }}
                    </div>
                </div>
                @endif
            </div>

        </div>
    </section>
</div>
@endsection

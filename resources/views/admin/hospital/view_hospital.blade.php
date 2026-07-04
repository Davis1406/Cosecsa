@extends('layout.app')

@push('styles')
<style>
    .hosp-hero { background:#a02626; border-radius:10px; padding:22px 24px; color:#fff; margin-bottom:1.2rem; }
    .hosp-hero h4 { font-weight:700; margin:0 0 4px; }
    .hosp-hero .meta { font-size:.85rem; opacity:.85; }
    .hosp-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.75rem;
                  font-weight:600; background:rgba(255,255,255,.2); color:#fff; }

    .nav-tabs .nav-link          { color:#555; font-size:.87rem; }
    .nav-tabs .nav-link.active   { color:#a02626; border-bottom:2px solid #a02626; font-weight:600; }
    .tab-count { display:inline-block; background:#e8d5d5; color:#a02626; border-radius:10px;
                 font-size:.7rem; font-weight:700; padding:1px 7px; margin-left:4px; }
    .nav-tabs .nav-link.active .tab-count { background:#a02626; color:#fff; }

    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; }
    .info-item label { font-size:.68rem; color:#999; display:block; margin-bottom:2px; text-transform:uppercase; letter-spacing:.04em; }
    .info-item span  { font-size:.9rem; color:#222; font-weight:500; }

    .entity-table td, .entity-table th { vertical-align:middle; font-size:.875rem; }
    .entity-table thead th { background:#f8f0f0; color:#a02626; font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; border-bottom:2px solid #e8d5d5; }
    .dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:4px; }
    .dot-active, .dot-1 { background:#22c55e; }
    .dot-inactive, .dot-0 { background:#ef4444; }
    .dot-active_acc { background:#22c55e; }
    .dot-expired { background:#ef4444; }
    .entity-link { color:#a02626; font-weight:500; text-decoration:none; }
    .entity-link:hover { text-decoration:underline; }
    .empty-state { text-align:center; padding:32px; color:#aaa; font-size:.9rem; }
    .empty-state i { font-size:2rem; display:block; margin-bottom:8px; }

    body.dark-mode .hosp-hero { background:#7a1f1f; }
    body.dark-mode .info-item label { color:#9ca3af; }
    body.dark-mode .info-item span  { color:#e0e0e0; }
    body.dark-mode .entity-table thead th { background:#374151; color:#f87171; border-bottom-color:#4a5568; }
    body.dark-mode .entity-table td, body.dark-mode .entity-table th { border-color:#4a5568 !important; color:#e0e0e0 !important; }
    body.dark-mode .nav-tabs .nav-link { color:#9ca3af; }
    body.dark-mode .nav-tabs .nav-link.active { color:#f87171; border-bottom-color:#f87171; }
    body.dark-mode .tab-count { background:#4a5568; color:#e0e0e0; }
    body.dark-mode .nav-tabs .nav-link.active .tab-count { background:#f87171; color:#fff; }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                {{-- Breadcrumb --}}
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb" style="background:none;padding:0;font-size:.82rem;">
                        <li class="breadcrumb-item"><a href="{{ url('admin/hospital/list') }}" style="color:#a02626;">Hospitals</a></li>
                        <li class="breadcrumb-item active">{{ $hospital->name }}</li>
                    </ol>
                </nav>

                {{-- Hero card --}}
                <div class="hosp-hero">
                    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem;">
                        <div>
                            <h4><i class="fas fa-hospital-alt mr-2"></i>{{ $hospital->name }}</h4>
                            <div class="meta">
                                <i class="fas fa-globe-africa mr-1"></i>{{ $hospital->country_name }}
                                &nbsp;·&nbsp;
                                @php $typeMap = [1=>'Government',2=>'NGO / Faith-Based',3=>'Private',4=>'University Teaching']; @endphp
                                {{ $typeMap[$hospital->hospital_type] ?? 'Unknown' }}
                            </div>
                        </div>
                        <div class="d-flex" style="gap:.5rem;">
                            <span class="hosp-badge">
                                @if($hospital->status == 0)
                                    <i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;"></i> Active
                                @else
                                    <i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;color:#f87171;"></i> Inactive
                                @endif
                            </span>
                            <a href="{{ url('admin/hospital/edit_hospital/'.$hospital->id) }}"
                               class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Tab navigation --}}
                <ul class="nav nav-tabs mb-3" id="hospTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-prog"     data-toggle="tab" href="#pane-prog"     role="tab">
                            <i class="fas fa-stethoscope mr-1"></i>Programmes
                            <span class="tab-count">{{ count($programmes) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-trainers"  data-toggle="tab" href="#pane-trainers"  role="tab">
                            <i class="fas fa-user-tie mr-1"></i>Programme Directors
                            <span class="tab-count">{{ count($trainers) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-trainees"  data-toggle="tab" href="#pane-trainees"  role="tab">
                            <i class="fas fa-user-graduate mr-1"></i>Trainees
                            <span class="tab-count">{{ count($trainees) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-fellows"   data-toggle="tab" href="#pane-fellows"   role="tab">
                            <i class="fas fa-award mr-1"></i>Fellows
                            <span class="tab-count">{{ count($fellows) }}</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    {{-- ── Programmes tab ── --}}
                    <div class="tab-pane fade show active" id="pane-prog" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                @if($programmes->count())
                                <table class="table table-bordered table-striped entity-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Programme</th>
                                            <th>Accredited</th>
                                            <th>Expires</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($programmes as $i => $p)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>
                                                <a href="{{ url('admin/programmes/view/'.$p->programme_id) }}" class="entity-link">
                                                    {{ $p->programme_name }}
                                                </a>
                                            </td>
                                            <td>{{ $p->accredited_date ? \Carbon\Carbon::parse($p->accredited_date)->format('M Y') : '-' }}</td>
                                            <td>{{ $p->expiry_date    ? \Carbon\Carbon::parse($p->expiry_date)->format('M Y')    : '-' }}</td>
                                            <td>
                                                <span class="dot dot-{{ strtolower($p->status) === 'active' ? 'active_acc' : 'expired' }}"></span>
                                                {{ $p->status }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <div class="empty-state"><i class="fas fa-stethoscope"></i>No accredited programmes on record.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Programme Directors tab ── --}}
                    <div class="tab-pane fade" id="pane-trainers" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                @if($trainers->count())
                                <table class="table table-bordered table-striped entity-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Assistant PD</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trainers as $i => $t)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>
                                                <a href="{{ url('admin/associates/trainers/view/'.$t->trainer_id) }}" class="entity-link">
                                                    {{ $t->name }}
                                                </a>
                                            </td>
                                            <td>{{ $t->email ?: '-' }}</td>
                                            <td>{{ $t->phone_number ?: '-' }}</td>
                                            <td>{{ $t->assistant_pd ?: '-' }}</td>
                                            <td>
                                                <a href="{{ url('admin/associates/trainers/view/'.$t->trainer_id) }}" class="btn btn-xs btn-light border">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <div class="empty-state"><i class="fas fa-user-tie"></i>No programme directors linked to this hospital.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Trainees tab ── --}}
                    <div class="tab-pane fade" id="pane-trainees" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                @if($trainees->count())
                                <table class="table table-bordered table-striped entity-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Programme</th>
                                            <th>Admission Year</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trainees as $i => $t)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>
                                                <a href="{{ url('admin/associates/trainees/view/'.$t->trainee_id) }}" class="entity-link">
                                                    {{ $t->name }}
                                                </a>
                                            </td>
                                            <td>{{ $t->email ?: '-' }}</td>
                                            <td>
                                                <a href="{{ url('admin/programmes/view/'.$t->programme_id) }}" class="entity-link">
                                                    {{ $t->programme_name }}
                                                </a>
                                            </td>
                                            <td>{{ $t->admission_year ?: '-' }}</td>
                                            <td>
                                                <span class="dot dot-{{ strtolower($t->status??'')=='active'?'active':'inactive' }}"></span>
                                                {{ $t->status ?: '—' }}
                                            </td>
                                            <td>
                                                <a href="{{ url('admin/associates/trainees/view/'.$t->trainee_id) }}" class="btn btn-xs btn-light border">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <div class="empty-state"><i class="fas fa-user-graduate"></i>No trainees linked to this hospital.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Fellows tab ── --}}
                    <div class="tab-pane fade" id="pane-fellows" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                @if($fellows->count())
                                <table class="table table-bordered table-striped entity-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Programme</th>
                                            <th>Fellowship Year</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fellows as $i => $f)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>
                                                <a href="{{ url('admin/associates/fellows/view/'.$f->fellow_id) }}" class="entity-link">
                                                    {{ $f->name }}
                                                </a>
                                            </td>
                                            <td>{{ $f->email ?: '-' }}</td>
                                            <td>
                                                <a href="{{ url('admin/programmes/view/'.$f->programme_id) }}" class="entity-link">
                                                    {{ $f->programme_name }}
                                                </a>
                                            </td>
                                            <td>{{ $f->fellowship_year ?: '-' }}</td>
                                            <td>
                                                <a href="{{ url('admin/associates/fellows/view/'.$f->fellow_id) }}" class="btn btn-xs btn-light border">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <div class="empty-state"><i class="fas fa-award"></i>No fellows linked via this hospital's programmes.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>{{-- /.tab-content --}}

            </div>
        </section>
    </div>
</div>
@endsection

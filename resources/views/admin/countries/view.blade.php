@extends('layout.app')

@push('styles')
<style>
    .country-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px;
                    padding:22px 24px; color:#fff; margin-bottom:1.2rem; }
    .country-hero h4 { font-weight:700; margin:0; }
    .country-stats-row { display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.2rem; }
    .c-stat { background:#fff; border:1px solid #e9ecef; border-radius:8px; padding:12px 18px;
              display:flex; align-items:center; gap:12px; flex:1; min-width:130px; }
    .c-stat-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center;
                   justify-content:center; font-size:.95rem; flex-shrink:0; }
    .c-stat-lbl { font-size:.67rem; color:#999; text-transform:uppercase; letter-spacing:.04em; }
    .c-stat-val { font-size:1rem; font-weight:700; color:#222; }

    .nav-tabs .nav-link        { color:#555; font-size:.87rem; }
    .nav-tabs .nav-link.active { color:#a02626; border-bottom:2px solid #a02626; font-weight:600; }
    .tab-count { display:inline-block; background:#e8d5d5; color:#a02626; border-radius:10px;
                 font-size:.7rem; font-weight:700; padding:1px 7px; margin-left:4px; }
    .nav-tabs .nav-link.active .tab-count { background:#a02626; color:#fff; }

    .entity-table td, .entity-table th { vertical-align:middle; font-size:.875rem; }
    .entity-table thead th { background:#f8f0f0; color:#a02626; font-size:.75rem; text-transform:uppercase;
                             letter-spacing:.05em; border-bottom:2px solid #e8d5d5; }
    .dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:4px; }
    .dot-active, .dot-1, .dot-Active { background:#22c55e; }
    .dot-inactive, .dot-0, .dot-Inactive { background:#ef4444; }
    .entity-link { color:#a02626; font-weight:500; text-decoration:none; }
    .entity-link:hover { text-decoration:underline; }
    .empty-state { text-align:center; padding:32px; color:#aaa; font-size:.9rem; }
    .empty-state i { font-size:2rem; display:block; margin-bottom:8px; }

    body.dark-mode .c-stat { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .c-stat-lbl { color:#9ca3af !important; }
    body.dark-mode .c-stat-val { color:#e0e0e0 !important; }
    body.dark-mode .entity-table thead th { background:#374151 !important; color:#f87171 !important; border-bottom-color:#4a5568 !important; }
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
                        <li class="breadcrumb-item"><a href="{{ url('admin/countries/list') }}" style="color:#a02626;">Countries</a></li>
                        <li class="breadcrumb-item active">{{ $country->country_name }}</li>
                    </ol>
                </nav>

                {{-- Hero --}}
                <div class="country-hero">
                    <h4><i class="fas fa-flag mr-2"></i>{{ $country->country_name }}</h4>
                </div>

                {{-- Stat chips --}}
                <div class="country-stats-row">
                    @php $cStats = [
                        ['icon'=>'fas fa-hospital-alt','bg'=>'#f0d4d4','ic'=>'#a02626','lbl'=>'Hospitals',    'val'=>count($hospitals)],
                        ['icon'=>'fas fa-user-graduate','bg'=>'#e8f5e9','ic'=>'#388e3c','lbl'=>'Trainees',    'val'=>count($trainees)],
                        ['icon'=>'fas fa-award',        'bg'=>'#e8eaf6','ic'=>'#3949ab','lbl'=>'Fellows',     'val'=>count($fellows)],
                        ['icon'=>'fas fa-users',        'bg'=>'#fff8e1','ic'=>'#f9a825','lbl'=>'Members',     'val'=>count($members)],
                        ['icon'=>'fas fa-user-tie',     'bg'=>'#f3e5f5','ic'=>'#7b1fa2','lbl'=>'Prog. Directors', 'val'=>count($trainers)],
                        ['icon'=>'fas fa-id-badge',     'bg'=>'#e0f7fa','ic'=>'#00796b','lbl'=>'Country Reps','val'=>count($reps)],
                        ['icon'=>'fas fa-stethoscope',  'bg'=>'#fce8e8','ic'=>'#c62828','lbl'=>'Examiners',   'val'=>count($examiners)],
                    ]; @endphp
                    @foreach($cStats as $s)
                    <div class="c-stat">
                        <div class="c-stat-icon" style="background:{{ $s['bg'] }};color:{{ $s['ic'] }};"><i class="{{ $s['icon'] }}"></i></div>
                        <div>
                            <div class="c-stat-lbl">{{ $s['lbl'] }}</div>
                            <div class="c-stat-val">{{ $s['val'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-3" id="cTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#ct-hospitals"><i class="fas fa-hospital-alt mr-1"></i>Hospitals<span class="tab-count">{{ count($hospitals) }}</span></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ct-trainees"><i class="fas fa-user-graduate mr-1"></i>Trainees<span class="tab-count">{{ count($trainees) }}</span></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ct-fellows"><i class="fas fa-award mr-1"></i>Fellows<span class="tab-count">{{ count($fellows) }}</span></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ct-members"><i class="fas fa-users mr-1"></i>Members<span class="tab-count">{{ count($members) }}</span></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ct-trainers"><i class="fas fa-user-tie mr-1"></i>Prog. Directors<span class="tab-count">{{ count($trainers) }}</span></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ct-reps"><i class="fas fa-id-badge mr-1"></i>Country Reps<span class="tab-count">{{ count($reps) }}</span></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ct-examiners"><i class="fas fa-stethoscope mr-1"></i>Examiners<span class="tab-count">{{ count($examiners) }}</span></a></li>
                </ul>

                <div class="tab-content">

                    {{-- ── Hospitals ── --}}
                    <div class="tab-pane fade show active" id="ct-hospitals">
                        <div class="card"><div class="card-body p-0">
                            @if(count($hospitals))
                            <table class="table table-bordered table-striped entity-table mb-0">
                                <thead><tr><th>#</th><th>Hospital</th><th>Type</th><th>Status</th><th>Action</th></tr></thead>
                                <tbody>
                                    @php $typeMap=[1=>'Government',2=>'NGO / Faith-Based',3=>'Private',4=>'University Teaching']; @endphp
                                    @foreach($hospitals as $i => $h)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td><a href="{{ url('admin/hospital/view_hospital/'.$h->id) }}" class="entity-link">{{ $h->name }}</a></td>
                                        <td>{{ $typeMap[$h->hospital_type] ?? '—' }}</td>
                                        <td><span class="dot dot-{{ $h->status==0?'active':'inactive' }}"></span>{{ $h->status==0?'Active':'Inactive' }}</td>
                                        <td><a href="{{ url('admin/hospital/view_hospital/'.$h->id) }}" class="btn btn-xs btn-light border"><i class="fas fa-eye text-info"></i></a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else<div class="empty-state"><i class="fas fa-hospital-alt"></i>No hospitals in this country.</div>@endif
                        </div></div>
                    </div>

                    {{-- ── Trainees ── --}}
                    <div class="tab-pane fade" id="ct-trainees">
                        <div class="card"><div class="card-body p-0">
                            @if(count($trainees))
                            <table class="table table-bordered table-striped entity-table mb-0">
                                <thead><tr><th>#</th><th>Name</th><th>Programme</th><th>Hospital</th><th>Admission Year</th><th>Status</th><th>Action</th></tr></thead>
                                <tbody>
                                    @foreach($trainees as $i => $t)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td><a href="{{ url('admin/associates/trainees/view/'.$t->trainee_id) }}" class="entity-link">{{ $t->name }}</a></td>
                                        <td>@if($t->programme_id)<a href="{{ url('admin/programmes/view/'.$t->programme_id) }}" class="entity-link">{{ $t->programme_name }}</a>@else{{ $t->programme_name }}@endif</td>
                                        <td>@if($t->hospital_id)<a href="{{ url('admin/hospital/view_hospital/'.$t->hospital_id) }}" class="entity-link">{{ $t->hospital_name }}</a>@else{{ $t->hospital_name ?? '—' }}@endif</td>
                                        <td>{{ $t->admission_year ?: '—' }}</td>
                                        <td><span class="dot dot-{{ $t->status==1?'active':'inactive' }}"></span>{{ $t->status==1?'Active':'Inactive' }}</td>
                                        <td><a href="{{ url('admin/associates/trainees/view/'.$t->trainee_id) }}" class="btn btn-xs btn-light border"><i class="fas fa-eye text-info"></i></a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else<div class="empty-state"><i class="fas fa-user-graduate"></i>No trainees from this country.</div>@endif
                        </div></div>
                    </div>

                    {{-- ── Fellows ── --}}
                    <div class="tab-pane fade" id="ct-fellows">
                        <div class="card"><div class="card-body p-0">
                            @if(count($fellows))
                            <table class="table table-bordered table-striped entity-table mb-0">
                                <thead><tr><th>#</th><th>Name</th><th>Programme</th><th>Fellowship Year</th><th>Action</th></tr></thead>
                                <tbody>
                                    @foreach($fellows as $i => $f)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td><a href="{{ url('admin/associates/fellows/view/'.$f->fellow_id) }}" class="entity-link">{{ $f->name }}</a></td>
                                        <td>@if($f->programme_id)<a href="{{ url('admin/programmes/view/'.$f->programme_id) }}" class="entity-link">{{ $f->programme_name }}</a>@else{{ $f->programme_name ?? '—' }}@endif</td>
                                        <td>{{ $f->fellowship_year ?: '—' }}</td>
                                        <td><a href="{{ url('admin/associates/fellows/view/'.$f->fellow_id) }}" class="btn btn-xs btn-light border"><i class="fas fa-eye text-info"></i></a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else<div class="empty-state"><i class="fas fa-award"></i>No fellows from this country.</div>@endif
                        </div></div>
                    </div>

                    {{-- ── Members ── --}}
                    <div class="tab-pane fade" id="ct-members">
                        <div class="card"><div class="card-body p-0">
                            @if(count($members))
                            <table class="table table-bordered table-striped entity-table mb-0">
                                <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr></thead>
                                <tbody>
                                    @foreach($members as $i => $m)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td><a href="{{ url('admin/associates/members/view/'.$m->member_id) }}" class="entity-link">{{ $m->name }}</a></td>
                                        <td>{{ $m->email ?: '—' }}</td>
                                        <td>{{ $m->status ?: '—' }}</td>
                                        <td><a href="{{ url('admin/associates/members/view/'.$m->member_id) }}" class="btn btn-xs btn-light border"><i class="fas fa-eye text-info"></i></a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else<div class="empty-state"><i class="fas fa-users"></i>No members from this country.</div>@endif
                        </div></div>
                    </div>

                    {{-- ── Programme Directors ── --}}
                    <div class="tab-pane fade" id="ct-trainers">
                        <div class="card"><div class="card-body p-0">
                            @if(count($trainers))
                            <table class="table table-bordered table-striped entity-table mb-0">
                                <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Hospital</th><th>Action</th></tr></thead>
                                <tbody>
                                    @foreach($trainers as $i => $t)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td><a href="{{ url('admin/associates/trainers/view/'.$t->trainer_id) }}" class="entity-link">{{ $t->name }}</a></td>
                                        <td>{{ $t->email ?: '—' }}</td>
                                        <td><a href="{{ url('admin/hospital/view_hospital/'.$t->hospital_id) }}" class="entity-link">{{ $t->hospital_name }}</a></td>
                                        <td><a href="{{ url('admin/associates/trainers/view/'.$t->trainer_id) }}" class="btn btn-xs btn-light border"><i class="fas fa-eye text-info"></i></a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else<div class="empty-state"><i class="fas fa-user-tie"></i>No programme directors in this country.</div>@endif
                        </div></div>
                    </div>

                    {{-- ── Country Reps ── --}}
                    <div class="tab-pane fade" id="ct-reps">
                        <div class="card"><div class="card-body p-0">
                            @if(count($reps))
                            <table class="table table-bordered table-striped entity-table mb-0">
                                <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Action</th></tr></thead>
                                <tbody>
                                    @foreach($reps as $i => $r)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td><a href="{{ url('admin/associates/reps/view/'.$r->rep_id) }}" class="entity-link">{{ $r->name }}</a></td>
                                        <td>{{ $r->email ?: '—' }}</td>
                                        <td><a href="{{ url('admin/associates/reps/view/'.$r->rep_id) }}" class="btn btn-xs btn-light border"><i class="fas fa-eye text-info"></i></a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else<div class="empty-state"><i class="fas fa-id-badge"></i>No country representatives on record.</div>@endif
                        </div></div>
                    </div>

                    {{-- ── Examiners ── --}}
                    <div class="tab-pane fade" id="ct-examiners">
                        <div class="card"><div class="card-body p-0">
                            @if(count($examiners))
                            <table class="table table-bordered table-striped entity-table mb-0">
                                <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Action</th></tr></thead>
                                <tbody>
                                    @foreach($examiners as $i => $e)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td><a href="{{ url('admin/exams/view_examiner/'.$e->user_id) }}" class="entity-link">{{ $e->name }}</a></td>
                                        <td>{{ $e->email ?: '—' }}</td>
                                        <td>{{ $e->examiner_role ?: '—' }}</td>
                                        <td><a href="{{ url('admin/exams/view_examiner/'.$e->user_id) }}" class="btn btn-xs btn-light border"><i class="fas fa-eye text-info"></i></a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else<div class="empty-state"><i class="fas fa-stethoscope"></i>No examiners from this country.</div>@endif
                        </div></div>
                    </div>

                </div>{{-- /.tab-content --}}

            </div>
        </section>
    </div>
</div>
@endsection

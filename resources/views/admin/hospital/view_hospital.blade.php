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

    /* The inline-edit component hides its pencil until hover by default,
       which reads as "not editable" in a plain data table where nothing
       else hints a cell can be clicked. Keep it faintly visible at rest
       here so the Programme Directors table advertises itself. */
    #pane-pd .ie-pencil { opacity:.45; }
    #pane-pd .ie-pencil:hover { opacity:1 !important; }

    body.dark-mode .hosp-hero { background:#7a1f1f; }
    body.dark-mode .info-item label { color:#9ca3af; }
    body.dark-mode .info-item span  { color:#e0e0e0; }
    body.dark-mode .entity-table thead th { background:#374151; color:#f87171; border-bottom-color:#4a5568; }
    body.dark-mode .entity-table td, body.dark-mode .entity-table th { border-color:#4a5568 !important; color:#e0e0e0 !important; }
    body.dark-mode .nav-tabs .nav-link { color:#9ca3af; }
    body.dark-mode .nav-tabs .nav-link.active { color:#f87171; border-bottom-color:#f87171; }
    body.dark-mode .tab-count { background:#4a5568; color:#e0e0e0; }
    body.dark-mode .nav-tabs .nav-link.active .tab-count { background:#f87171; color:#fff; }

    /* ── Link colour override — no default Bootstrap blue anywhere on this page ── */
    a, .btn-link { color:#a02626; }
    a:hover, .btn-link:hover { color:#7a1f1f; }
    .btn-link:focus { box-shadow:none; }
    body.dark-mode a, body.dark-mode .btn-link { color:#f87171; }
    body.dark-mode a:hover, body.dark-mode .btn-link:hover { color:#fca5a5; }

    /* ── Modern minimal modals ── */
    .modal-modern .modal-content { border:none; border-radius:14px; box-shadow:0 20px 60px rgba(0,0,0,.18); overflow:hidden; }
    .modal-modern .modal-header {
        border-bottom:1px solid #f1e5e5; padding:18px 22px; align-items:center;
    }
    .modal-modern .modal-title { font-size:1rem; font-weight:700; color:#2a2a2a; display:flex; align-items:center; }
    .modal-modern .modal-title i { color:#a02626; margin-right:8px; font-size:.95em; }
    .modal-modern .modal-header .close { color:#bbb; text-shadow:none; font-weight:400; font-size:1.4rem; opacity:1; transition:color .15s; }
    .modal-modern .modal-header .close:hover { color:#a02626; }
    .modal-modern .modal-body { padding:20px 22px; }
    .modal-modern .modal-footer { border-top:1px solid #f1e5e5; padding:14px 22px; }
    .modal-modern label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#888; margin-bottom:5px; }
    .modal-modern .form-control {
        border:1px solid #e5e0e0; border-radius:8px; font-size:.88rem; padding:.5rem .7rem; height:auto;
        transition:border-color .15s, box-shadow .15s;
    }
    .modal-modern .form-control:focus { border-color:#c98d8d; box-shadow:0 0 0 3px rgba(160,38,38,.1); }
    .modal-modern .form-control[multiple] { padding:.4rem; }
    .modal-modern .form-control[multiple] option { padding:6px 8px; border-radius:5px; }
    .modal-modern small.text-muted { font-size:.72rem; }
    .modal-modern .btn { border-radius:8px; font-size:.85rem; font-weight:600; padding:.45rem 1rem; }
    .modal-modern .btn-danger { background:#a02626; border-color:#a02626; }
    .modal-modern .btn-danger:hover { background:#7a1f1f; border-color:#7a1f1f; }
    .modal-modern .btn-danger:disabled { background:#d5a3a3; border-color:#d5a3a3; }
    .modal-modern .btn-secondary { background:#f5f5f5; border-color:#f5f5f5; color:#555; }
    .modal-modern .btn-secondary:hover { background:#e9e9e9; border-color:#e9e9e9; color:#333; }
    .modal-modern .section-divider { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#c98d8d; margin:18px 0 8px; padding-top:14px; border-top:1px dashed #eee; }
    .modal-modern .fp-results { border:1px solid #e5e0e0; border-radius:8px; overflow:hidden; }
    .modal-modern .fp-results .list-group-item { border:none; border-bottom:1px solid #f1e5e5; }
    .modal-modern .fp-results .list-group-item:last-child { border-bottom:none; }
    .modal-modern .fp-new-box { background:#fbf7f7; border:1px solid #f1e5e5 !important; border-radius:10px; }
    .modal-modern .fp-selected { background:#f0faf0; border:1px solid #cdeecd; border-radius:8px; padding:6px 10px; color:#2f8a3e; font-weight:600; }
    .modal-modern .alert { border-radius:8px; font-size:.85rem; border:none; }

    body.dark-mode .modal-modern .modal-content { background:#1f2937; }
    body.dark-mode .modal-modern .modal-header,
    body.dark-mode .modal-modern .modal-footer { border-color:#374151; }
    body.dark-mode .modal-modern .modal-title { color:#e5e7eb; }
    body.dark-mode .modal-modern label { color:#9ca3af; }
    body.dark-mode .modal-modern .form-control { background:#111827; border-color:#374151; color:#e5e7eb; }
    body.dark-mode .modal-modern .form-control:focus { border-color:#f87171; box-shadow:0 0 0 3px rgba(248,113,113,.12); }
    body.dark-mode .modal-modern .btn-secondary { background:#374151; border-color:#374151; color:#e5e7eb; }
    body.dark-mode .modal-modern .btn-secondary:hover { background:#4b5563; border-color:#4b5563; }
    body.dark-mode .modal-modern .section-divider { border-color:#374151; color:#f87171; }
    body.dark-mode .modal-modern .fp-results { border-color:#374151; }
    body.dark-mode .modal-modern .fp-results .list-group-item { background:#1f2937; border-color:#374151; color:#e5e7eb; }
    body.dark-mode .modal-modern .fp-new-box { background:#111827; border-color:#374151 !important; }
    body.dark-mode .modal-modern .fp-selected { background:#0f2e17; border-color:#1d4d28; color:#4ade80; }
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
                                <span id="hospTypeDisplay">{{ $typeMap[$hospital->hospital_type] ?? 'Unknown' }}</span>
                                <a href="#" id="hospTypeEditBtn" title="Edit hospital type" style="color:inherit;">
                                    <i class="fas fa-pencil-alt ml-1" style="font-size:.75rem;"></i>
                                </a>
                                <span id="hospTypeEditWrap" style="display:none;">
                                    <select id="hospTypeSelect" class="form-control form-control-sm d-inline-block" style="width:auto;display:inline-block;">
                                        @foreach($typeMap as $val => $label)
                                            <option value="{{ $val }}" {{ $hospital->hospital_type == $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" id="hospTypeSaveBtn" class="btn btn-sm btn-light">Save</button>
                                    <button type="button" id="hospTypeCancelBtn" class="btn btn-sm btn-link text-white">Cancel</button>
                                </span>
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
                        <a class="nav-link" id="tab-pd"  data-toggle="tab" href="#pane-pd"  role="tab">
                            <i class="fas fa-user-tie mr-1"></i>Programme Directors
                            <span class="tab-count">{{ count($programmeDirectors) }}</span>
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
                    <li class="nav-item">
                        <a class="nav-link" id="tab-trainers"   data-toggle="tab" href="#pane-trainers"   role="tab">
                            <i class="fas fa-chalkboard-teacher mr-1"></i>Trainers
                            <span class="tab-count">{{ count($trainers) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-history"   data-toggle="tab" href="#pane-history"   role="tab">
                            <i class="fas fa-history mr-1"></i>Accreditation History
                            <span class="tab-count">{{ count($accreditationHistory ?? []) }}</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    {{-- ── Programmes tab ── --}}
                    <div class="tab-pane fade show active" id="pane-prog" role="tabpanel">
                        <div class="card">
                            <div class="card-header d-flex justify-content-end p-2">
                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#addProgModal">
                                    <i class="fas fa-plus mr-1"></i> Add Programme
                                </button>
                            </div>
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

                    {{-- ── Accreditation History tab ── --}}
                    <div class="tab-pane fade" id="pane-history" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                @if(count($accreditationHistory ?? []))
                                <table class="table table-bordered table-striped entity-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Programme</th>
                                            <th>Event</th>
                                            <th>Accredited</th>
                                            <th>Expires</th>
                                            <th>Status</th>
                                            <th>Recorded By</th>
                                            <th>Date Recorded</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($accreditationHistory as $i => $h)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>
                                                <a href="{{ url('admin/programmes/view/'.$h->programme_id) }}" class="entity-link">
                                                    {{ $h->programme_name }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($h->event_type === 'Initial Accreditation')
                                                    <span class="badge badge-primary">Initial Accreditation</span>
                                                @elseif($h->event_type === 'Reaccreditation')
                                                    <span class="badge badge-success">Reaccreditation</span>
                                                @else
                                                    <span class="badge badge-danger">Marked Expired</span>
                                                @endif
                                            </td>
                                            <td>{{ $h->accredited_date ? \Carbon\Carbon::parse($h->accredited_date)->format('M Y') : '-' }}</td>
                                            <td>{{ $h->expiry_date    ? \Carbon\Carbon::parse($h->expiry_date)->format('M Y')    : '-' }}</td>
                                            <td>
                                                <span class="dot dot-{{ strtolower($h->status) === 'active' ? 'active_acc' : 'expired' }}"></span>
                                                {{ $h->status }}
                                            </td>
                                            <td>{{ $h->recorded_by_name ?: '—' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($h->created_at)->format('d M Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <div class="empty-state"><i class="fas fa-history"></i>No accreditation history recorded yet.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Programme Directors tab ── --}}
                    <div class="tab-pane fade" id="pane-pd" role="tabpanel">
                        <div class="card">
                            <div class="card-header d-flex justify-content-end p-2">
                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#addPdModal">
                                    <i class="fas fa-plus mr-1"></i> Add Programme Director
                                </button>
                            </div>
                            <div class="card-body p-0">
                                @if($programmeDirectors->count())
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
                                        @foreach($programmeDirectors as $i => $t)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>
                                                <a href="{{ url('admin/associates/programme-directors/view/'.$t->programme_director_id) }}" class="entity-link">
                                                    {{ $t->name }}
                                                </a>
                                            </td>
                                            <td>{{ $t->email ?: '-' }}</td>
                                            <td>
                                                <span class="ie-field" data-ie="phone_number" data-ie-type="text"
                                                      data-ie-value="{{ $t->phone_number ?? '' }}"
                                                      data-ie-url="{{ url('admin/associates/programme-directors/'.$t->programme_director_id.'/quick-update') }}"
                                                      data-ie-csrf="{{ csrf_token() }}">
                                                    <span class="ie-value">{{ $t->phone_number ?: '—' }}</span>
                                                    <button class="ie-pencil" type="button" title="Edit phone number"><i class="fas fa-pen"></i></button>
                                                </span>
                                            </td>
                                            <td>{{ $t->assistant_pd ?: '-' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-xs btn-light border edit-pd-btn" title="Edit"
                                                        data-toggle="modal" data-target="#editPdModal"
                                                        data-pd-id="{{ $t->programme_director_id }}"
                                                        data-name="{{ $t->name }}"
                                                        data-email="{{ $t->email }}"
                                                        data-assistant-pd="{{ $t->assistant_pd }}"
                                                        data-assistant-email="{{ $t->assistant_email }}"
                                                        data-programme-id="{{ $t->programme_id }}"
                                                        data-mobile-no="{{ $t->mobile_no }}"
                                                        data-phone-number="{{ $t->phone_number }}">
                                                    <i class="fas fa-pen text-secondary"></i>
                                                </button>
                                                <a href="{{ url('admin/associates/programme-directors/view/'.$t->programme_director_id) }}" class="btn btn-xs btn-light border" title="View full profile">
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
                            <div class="card-header d-flex justify-content-end p-2">
                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#mapFellowModal">
                                    <i class="fas fa-plus mr-1"></i> Map Fellow
                                </button>
                            </div>
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
                                                @if(!empty($f->mapped))
                                                    <span class="badge badge-light border ml-1" title="Explicitly mapped to this hospital">mapped</span>
                                                @endif
                                            </td>
                                            <td>{{ $f->email ?: '-' }}</td>
                                            <td>
                                                @if($f->programme_id)
                                                <a href="{{ url('admin/programmes/view/'.$f->programme_id) }}" class="entity-link">
                                                    {{ $f->programme_name }}
                                                </a>
                                                @else - @endif
                                            </td>
                                            <td>{{ $f->fellowship_year ?: '-' }}</td>
                                            <td>
                                                <a href="{{ url('admin/associates/fellows/view/'.$f->fellow_id) }}" class="btn btn-xs btn-light border">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                @if(!empty($f->mapped))
                                                <button type="button" class="btn btn-xs btn-light border unmap-fellow-btn" data-fellow-id="{{ $f->fellow_id }}" title="Remove mapping">
                                                    <i class="fas fa-times text-danger"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <div class="empty-state"><i class="fas fa-award"></i>No fellows found for this country &amp; programme combination.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ── Trainers tab ── --}}
                    <div class="tab-pane fade" id="pane-trainers" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                @if(count($trainers))
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr><th>#</th><th>Name</th><th>Email</th><th>Specialty</th><th>ToT Years</th><th>Master Trainer</th><th>Action</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trainers as $i => $t)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td><a href="{{ url('admin/associates/trainers/view/'.$t->trainer_id) }}">{{ $t->name }}</a></td>
                                            <td>{{ $t->email ?: '—' }}</td>
                                            <td>{{ $t->specialty ?: '—' }}</td>
                                            <td>{{ $t->tot_years ?: '—' }}</td>
                                            <td>@if(!empty($t->is_master_trainer))<span class="badge badge-success">Yes</span>@else<span class="badge badge-light border">No</span>@endif</td>
                                            <td><a href="{{ url('admin/associates/trainers/view/'.$t->trainer_id) }}" class="btn btn-xs btn-light border"><i class="fas fa-eye text-info"></i></a></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <div class="text-center text-muted p-4">No trainers linked to this hospital yet.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>{{-- /.tab-content --}}

            </div>
        </section>
    </div>
</div>

{{-- ══ Add Programme modal ══ --}}
<div class="modal fade modal-modern" id="addProgModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="addProgForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-stethoscope"></i> Add Programme to {{ $hospital->name }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert d-none" id="addProgAlert" role="alert"></div>
                <div class="form-group">
                    <label>Programme(s) <span class="text-danger">*</span></label>
                    <select class="form-control" name="programme_id[]" multiple size="8" required>
                        @foreach($allProgrammes as $p)
                            @if(!$programmes->firstWhere('programme_id', $p->id))
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl/Cmd to select multiple. Already-accredited programmes aren't listed.</small>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status">
                        <option value="Active">Active</option>
                        <option value="Expired">Expired</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group col-6">
                        <label>Accredited Date</label>
                        <input type="month" class="form-control" name="accredited_date">
                    </div>
                    <div class="form-group col-6">
                        <label>Expiry Date</label>
                        <input type="month" class="form-control" name="expiry_date">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Add Programme(s)</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ Add Programme Director modal (search-or-create fellow) ══ --}}
<div class="modal fade modal-modern" id="addPdModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-tie"></i> Add Programme Director</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert d-none" id="pdAlert" role="alert"></div>

                <div class="form-group">
                    <label>Programme <span class="text-danger">*</span></label>
                    <select class="form-control" id="pd_programme_id" required>
                        <option value="">-- Select programme --</option>
                        @foreach($allProgrammes as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">All programmes are listed — not just ones already accredited here.</small>
                </div>

                @include('admin.hospital._fellow_picker', ['prefix' => 'pd'])

                <div class="section-divider">Assistant PD (optional)</div>
                <div class="form-group">
                    <label>Search Fellow for Assistant PD</label>
                    <input type="text" class="form-control" id="asstpd_search" placeholder="Type a name or email to fill the fields below...">
                    <div class="list-group mt-1 d-none fp-results" id="asstpd_results" style="max-height:160px; overflow-y:auto;"></div>
                    <small class="text-muted">Or just type the name/email directly — Assistant PD isn't a login account, so either works.</small>
                </div>
                <div class="form-row">
                    <div class="form-group col-7">
                        <label>Assistant PD Name</label>
                        <input type="text" class="form-control" id="pd_assistant_pd" placeholder="Full name">
                    </div>
                    <div class="form-group col-5">
                        <label>Assistant PD Email</label>
                        <input type="email" class="form-control" id="pd_assistant_email" placeholder="email@example.com">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="pdSubmitBtn" disabled>Add as PD</button>
            </div>
        </div>
    </div>
</div>

{{-- ══ Edit Programme Director modal ══ --}}
<div class="modal fade modal-modern" id="editPdModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit"></i> Edit Programme Director</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert d-none" id="editPdAlert" role="alert"></div>
                <input type="hidden" id="editpd_id">
                <input type="hidden" id="editpd_hospital_id">
                <input type="hidden" id="editpd_programme_id">
                <input type="hidden" id="editpd_mobile_no">
                <input type="hidden" id="editpd_phone_number">

                <div class="form-group">
                    <label>Search Fellow</label>
                    <input type="text" class="form-control" id="editpd_search" placeholder="Type a name or email to fill the fields below...">
                    <div class="list-group mt-1 d-none fp-results" id="editpd_results" style="max-height:160px; overflow-y:auto;"></div>
                    <small class="text-muted">Or just edit the name/email directly below.</small>
                </div>
                <div class="form-row">
                    <div class="form-group col-7">
                        <label>Name</label>
                        <input type="text" class="form-control" id="editpd_name" required>
                    </div>
                    <div class="form-group col-5">
                        <label>Email</label>
                        <input type="email" class="form-control" id="editpd_email" required>
                    </div>
                </div>

                <div class="section-divider">Assistant PD (optional)</div>
                <div class="form-group">
                    <label>Search Fellow for Assistant PD</label>
                    <input type="text" class="form-control" id="editasstpd_search" placeholder="Type a name or email to fill the fields below...">
                    <div class="list-group mt-1 d-none fp-results" id="editasstpd_results" style="max-height:160px; overflow-y:auto;"></div>
                    <small class="text-muted">Or just type the name/email directly — Assistant PD isn't a login account, so either works.</small>
                </div>
                <div class="form-row">
                    <div class="form-group col-7">
                        <label>Assistant PD Name</label>
                        <input type="text" class="form-control" id="editpd_assistant_pd" placeholder="Full name">
                    </div>
                    <div class="form-group col-5">
                        <label>Assistant PD Email</label>
                        <input type="email" class="form-control" id="editpd_assistant_email" placeholder="email@example.com">
                    </div>
                </div>
                <small class="text-muted">Phone number has its own quick-edit pencil in the table — no need to duplicate it here.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="editPdSubmitBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

{{-- ══ Map Fellow modal (search-or-create fellow) ══ --}}
<div class="modal fade modal-modern" id="mapFellowModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-award"></i> Map Fellow to {{ $hospital->name }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert d-none" id="mapAlert" role="alert"></div>
                @include('admin.hospital._fellow_picker', ['prefix' => 'map'])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="mapSubmitBtn" disabled>Map Fellow</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const HOSPITAL_ID = {{ $hospital->id }};
const CSRF_TOKEN = '{{ csrf_token() }}';
const SEARCH_URL = '{{ url("admin/associates/fellows/search") }}';
const QUICK_CREATE_URL = '{{ url("admin/associates/fellows/quick-create") }}';

// ── Inline hospital type edit ──
$('#hospTypeEditBtn').on('click', function (e) {
    e.preventDefault();
    $('#hospTypeDisplay, #hospTypeEditBtn').hide();
    $('#hospTypeEditWrap').show();
});
$('#hospTypeCancelBtn').on('click', function () {
    $('#hospTypeEditWrap').hide();
    $('#hospTypeDisplay, #hospTypeEditBtn').show();
});
$('#hospTypeSaveBtn').on('click', function () {
    var $btn = $(this).prop('disabled', true).text('Saving…');
    $.post('{{ url("admin/hospital/".$hospital->id."/quick-type") }}', {
        _token: CSRF_TOKEN,
        hospital_type: $('#hospTypeSelect').val(),
    })
        .done(function () { window.location.reload(); })
        .fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Could not update hospital type.');
            $btn.prop('disabled', false).text('Save');
        });
});

// ── Add Programme ──
$('#addProgForm').on('submit', function (e) {
    e.preventDefault();
    var $alert = $('#addProgAlert').addClass('d-none');
    $.post('{{ url("admin/hospital/".$hospital->id."/programmes") }}', $(this).serialize())
        .done(function (res) {
            $alert.removeClass('d-none alert-danger').addClass('alert-success').text(res.message || 'Programme(s) added.');
            setTimeout(function () { window.location.reload(); }, 900);
        })
        .fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to add programme(s).';
            $alert.removeClass('d-none alert-success').addClass('alert-danger').text(msg);
        });
});

// ── Shared fellow-picker wiring (used by both Add PD and Map Fellow modals) ──
function wireFellowPicker(prefix) {
    var $input   = $('#' + prefix + '_fp_search');
    var $results = $('#' + prefix + '_fp_results');
    var $selected = $('#' + prefix + '_fp_selected');
    var $newBox  = $('#' + prefix + '_fp_new');
    var timer = null;
    var chosen = null; // {fellow_id, name}

    $input.on('input', function () {
        clearTimeout(timer);
        var q = $(this).val().trim();
        chosen = null;
        updateSubmitState(prefix);
        if (q.length < 2) { $results.empty().addClass('d-none'); return; }
        timer = setTimeout(function () {
            $.get(SEARCH_URL, { q: q }).done(function (res) {
                var fellows = res.fellows || [];
                $results.empty();
                if (!fellows.length) {
                    $results.append('<div class="list-group-item text-muted small">No matching fellows.</div>');
                } else {
                    fellows.forEach(function (f) {
                        var email = f.personal_email || f.email || '';
                        var $item = $('<button type="button" class="list-group-item list-group-item-action py-1 px-2 small"></button>')
                            .text(f.name + (email ? ' — ' + email : ''))
                            .data('fellow', f);
                        $results.append($item);
                    });
                }
                $results.removeClass('d-none');
            });
        }, 300);
    });

    $results.on('click', '.list-group-item-action', function () {
        var f = $(this).data('fellow');
        chosen = { fellow_id: f.fellow_id, name: f.name };
        $input.val(f.name);
        $results.addClass('d-none').empty();
        $selected.removeClass('d-none').text('Selected: ' + f.name);
        $newBox.addClass('d-none');
        updateSubmitState(prefix);
    });

    $('#' + prefix + '_fp_not_listed').on('click', function () {
        $newBox.removeClass('d-none');
        $results.addClass('d-none').empty();
        chosen = null;
        $selected.addClass('d-none');
        updateSubmitState(prefix);
    });

    window['getChosenFellow_' + prefix] = function () { return chosen; };
    window['setChosenFellow_' + prefix] = function (f) { chosen = f; updateSubmitState(prefix); };
}

function updateSubmitState(prefix) {
    var chosen = window['getChosenFellow_' + prefix] ? window['getChosenFellow_' + prefix]() : null;
    var newBoxVisible = !$('#' + prefix + '_fp_new').hasClass('d-none');
    var $btn = prefix === 'pd' ? $('#pdSubmitBtn') : $('#mapSubmitBtn');
    $btn.prop('disabled', !(chosen || newBoxVisible));
}

wireFellowPicker('pd');
wireFellowPicker('map');

// ── Search fellows to auto-fill a name/email pair ── Shared by every
// "search a fellow, fill these fields" spot: the Edit PD modal's primary PD
// fields (editpd_*), and both modals' Assistant PD fields (asstpd_* /
// editasstpd_*) — same search-to-autofill pattern, just different targets.
function wireAssistantPdSearch(searchId, resultsId, nameFieldId, emailFieldId) {
    var timer = null;
    var $search = $('#' + searchId);
    var $results = $('#' + resultsId);
    $search.on('input', function () {
        clearTimeout(timer);
        var q = $(this).val().trim();
        if (q.length < 2) { $results.empty().addClass('d-none'); return; }
        timer = setTimeout(function () {
            $.get(SEARCH_URL, { q: q }).done(function (res) {
                var fellows = res.fellows || [];
                $results.empty();
                if (!fellows.length) {
                    $results.append('<div class="list-group-item text-muted small">No matching fellows.</div>');
                } else {
                    fellows.forEach(function (f) {
                        var email = f.personal_email || f.email || '';
                        $('<button type="button" class="list-group-item list-group-item-action py-1 px-2 small"></button>')
                            .text(f.name + (email ? ' — ' + email : ''))
                            .data('fellow', f)
                            .appendTo($results);
                    });
                }
                $results.removeClass('d-none');
            });
        }, 300);
    });
    $results.on('click', '.list-group-item-action', function () {
        var f = $(this).data('fellow');
        $('#' + nameFieldId).val(f.name);
        $('#' + emailFieldId).val(f.personal_email || f.email || '');
        $search.val(f.name);
        $results.addClass('d-none').empty();
    });
}
wireAssistantPdSearch('asstpd_search', 'asstpd_results', 'pd_assistant_pd', 'pd_assistant_email');
wireAssistantPdSearch('editasstpd_search', 'editasstpd_results', 'editpd_assistant_pd', 'editpd_assistant_email');
wireAssistantPdSearch('editpd_search', 'editpd_results', 'editpd_name', 'editpd_email');

// Re-enable submit while typing "add new fellow" fields
$('#pd_fp_new, #map_fp_new').on('input', function () {
    var prefix = $(this).attr('id').split('_')[0];
    updateSubmitState(prefix);
});

// Resolve to a fellow_id: either the chosen search result, or quick-create
// a new fellow record first, then continue.
function resolveFellowId(prefix, cb) {
    var chosen = window['getChosenFellow_' + prefix]();
    if (chosen) { cb(chosen.fellow_id); return; }

    var $box = $('#' + prefix + '_fp_new');
    var data = {
        firstname: $box.find('[name=firstname]').val(),
        lastname: $box.find('[name=lastname]').val(),
        email: $box.find('[name=email]').val(),
        personal_email: $box.find('[name=personal_email]').val(),
        gender: $box.find('[name=gender]').val(),
        country_id: $box.find('[name=country_id]').val(),
        _token: CSRF_TOKEN,
    };
    if (!data.firstname || !data.lastname || !data.email || !data.country_id) {
        alert('Please fill in first name, last name, email, and country for the new fellow.');
        cb(null);
        return;
    }
    $.post(QUICK_CREATE_URL, data)
        .done(function (res) { cb(res.fellow_id); })
        .fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to create fellow.');
            cb(null);
        });
}

$('#pdSubmitBtn').on('click', function () {
    var progId = $('#pd_programme_id').val();
    if (!progId) { $('#pdAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Please select a programme.'); return; }
    var $btn = $(this).prop('disabled', true);
    resolveFellowId('pd', function (fellowId) {
        if (!fellowId) { $btn.prop('disabled', false); return; }
        $.post('{{ url("admin/associates/fellows") }}/' + fellowId + '/add-role', {
            _token: CSRF_TOKEN, role_type: 4, hospital_id: HOSPITAL_ID, programme_id: progId,
            assistant_pd: $('#pd_assistant_pd').val(), assistant_email: $('#pd_assistant_email').val(),
        }).done(function (res) {
            $('#pdAlert').removeClass('d-none alert-danger').addClass('alert-success').text(res.message || 'Programme Director added.');
            setTimeout(function () { window.location.reload(); }, 900);
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to add Programme Director.';
            $('#pdAlert').removeClass('d-none alert-success').addClass('alert-danger').text(msg);
            $btn.prop('disabled', false);
        });
    });
});

$('.edit-pd-btn').on('click', function () {
    var $b = $(this);
    $('#editPdAlert').addClass('d-none');
    $('#editpd_id').val($b.data('pd-id'));
    $('#editpd_hospital_id').val(HOSPITAL_ID);
    $('#editpd_programme_id').val($b.data('programme-id'));
    $('#editpd_mobile_no').val($b.data('mobile-no'));
    $('#editpd_phone_number').val($b.data('phone-number'));
    $('#editpd_name').val($b.data('name'));
    $('#editpd_email').val($b.data('email'));
    $('#editpd_assistant_pd').val($b.data('assistant-pd'));
    $('#editpd_assistant_email').val($b.data('assistant-email'));
});

$('#editPdSubmitBtn').on('click', function () {
    var $btn = $(this).prop('disabled', true);
    var pdId = $('#editpd_id').val();
    var name = $('#editpd_name').val().trim();
    var email = $('#editpd_email').val().trim();
    if (!name || !email) {
        $('#editPdAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Name and email are required.');
        $btn.prop('disabled', false);
        return;
    }
    $.post('{{ url("admin/associates/programme-directors") }}/' + pdId + '/ajax-update', {
        _token: CSRF_TOKEN,
        name: name, email: email,
        hospital_id: $('#editpd_hospital_id').val(),
        programme_id: $('#editpd_programme_id').val(),
        mobile_no: $('#editpd_mobile_no').val(),
        phone_number: $('#editpd_phone_number').val(),
        assistant_pd: $('#editpd_assistant_pd').val(),
        assistant_email: $('#editpd_assistant_email').val(),
    }).done(function () {
        window.location.reload();
    }).fail(function (xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to save changes.';
        $('#editPdAlert').removeClass('d-none alert-success').addClass('alert-danger').text(msg);
        $btn.prop('disabled', false);
    });
});

$('#mapSubmitBtn').on('click', function () {
    var $btn = $(this).prop('disabled', true);
    resolveFellowId('map', function (fellowId) {
        if (!fellowId) { $btn.prop('disabled', false); return; }
        $.post('{{ url("admin/hospital/".$hospital->id."/fellows") }}', { _token: CSRF_TOKEN, fellow_id: fellowId })
            .done(function (res) {
                $('#mapAlert').removeClass('d-none alert-danger').addClass('alert-success').text(res.message || 'Fellow mapped.');
                setTimeout(function () { window.location.reload(); }, 900);
            }).fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to map fellow.';
                $('#mapAlert').removeClass('d-none alert-success').addClass('alert-danger').text(msg);
                $btn.prop('disabled', false);
            });
    });
});

$('.unmap-fellow-btn').on('click', function () {
    if (!confirm('Remove this fellow mapping from the hospital?')) return;
    var fellowId = $(this).data('fellow-id');
    $.ajax({
        url: '{{ url("admin/hospital/".$hospital->id."/fellows") }}/' + fellowId,
        method: 'DELETE',
        data: { _token: CSRF_TOKEN },
    }).done(function () { window.location.reload(); })
      .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to unmap fellow.'); });
});
</script>
@endpush
@endsection

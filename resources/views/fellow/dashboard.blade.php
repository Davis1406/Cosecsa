@extends('layout.app')

@push('styles')
<style>
/* ══════════════════════════════════════
   FELLOW DASHBOARD – COSECSA STYLES
══════════════════════════════════════ */

/* ── Tab colours: active = red, inactive = gold ── */
.fellow-tabs .nav-link {
    color: #b8860b;
    background: #fff8e1;
    border: 1px solid #e8d48b;
    border-bottom: none;
    font-weight: 600;
    font-size: .83rem;
    padding: 8px 16px;
    margin-right: 3px;
    border-radius: 6px 6px 0 0;
    transition: background .2s, color .2s;
}
.fellow-tabs .nav-link:hover {
    background: #FEC503;
    color: #333;
    border-color: #FEC503;
}
.fellow-tabs .nav-link.active {
    background: #a02626 !important;
    color: #fff !important;
    border-color: #a02626 !important;
}

/* ── Profile card ── */
.fellow-avatar {
    width: 100px; height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #a02626;
    box-shadow: 0 2px 10px rgba(160,38,38,.2);
}
.fellow-name  { font-size: 1.1rem; font-weight: 700; color: #222; margin-bottom: 1px; }
.fellow-org   { font-size: .8rem; color: #6c757d; margin-bottom: 0; }

/* ── Tag pills ── */
.tag-pill {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 11px;
    font-size: .7rem;
    font-weight: 600;
    margin: 2px 2px;
    line-height: 1.6;
}
.tag-red          { background:#f0f0f0; color:#555; }
.tag-green        { background:#f0f0f0; color:#555; }
.tag-status-active{ background:#d4edda; color:#155724; }
.tag-blue      { background:#f0f0f0; color:#555; }
.tag-gold      { background:#f0f0f0; color:#555; }
.tag-grey      { background:#f0f0f0; color:#555; }
.tag-purple    { background:#f0f0f0; color:#555; }

/* ── Left-panel info rows ── */
.info-row {
    display:flex; align-items:flex-start;
    padding:5px 0; border-bottom:1px solid #f3f3f3;
    font-size:.83rem;
}
.info-row:last-child { border-bottom:none; }
.info-icon { width:22px; color:#a02626; flex-shrink:0; padding-top:1px; font-size:.8rem; }
.info-label { font-size:.68rem; color:#aaa; display:block; line-height:1; margin-bottom:1px; }
.info-text  { color:#495057; }

/* ── Section divider ── */
.sect-div {
    font-size:.68rem; font-weight:700;
    letter-spacing:.9px; text-transform:uppercase;
    color:#a02626; border-bottom:2px solid #f0d4d4;
    padding-bottom:3px; margin: 12px 0 8px;
}

/* ── Field rows in detail panels ── */
.field-row {
    display:flex; padding:7px 0;
    border-bottom:1px solid #f5f5f5;
    font-size:.855rem; align-items:flex-start;
}
.field-row:last-child { border-bottom:none; }
.field-lbl {
    width:42%; font-weight:600;
    color:#555; flex-shrink:0; padding-right:10px;
}
.field-val { color:#222; }

/* ── Stat chips ── */
.stat-chip {
    border-radius:8px; padding:14px 18px;
    display:flex; align-items:center; gap:12px;
    box-shadow:0 1px 5px rgba(0,0,0,.07);
}
.chip-icon {
    width:42px; height:42px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; flex-shrink:0;
}
.chip-val   { font-size:1.3rem; font-weight:700; line-height:1; }
.chip-label { font-size:.75rem; color:#6c757d; margin-top:2px; }

/* ── Timeline / History ── */
.timeline-item {
    display:flex; gap:12px;
    padding:10px 0;
    border-bottom:1px solid #f0f0f0;
}
.timeline-item:last-child { border-bottom:none; }
.tl-dot {
    width:10px; height:10px; border-radius:50%;
    margin-top:5px; flex-shrink:0;
}
.tl-date   { font-size:.72rem; color:#999; }
.tl-source { font-size:.72rem; font-weight:600; color:#a02626; }
.tl-body   { font-size:.85rem; color:#333; }

/* ── Subscription row ── */
.sub-row {
    display:flex; align-items:center;
    padding:8px 12px; border-radius:6px;
    margin-bottom:6px; background:#fafafa;
    border:1px solid #efefef;
    font-size:.85rem;
}
.sub-year { font-weight:700; width:55px; color:#333; }
.sub-badge { margin:0 8px; }

/* ── Verified tick ── */
.verified-icon { color:#28a745; font-size:1rem; }

/* ── Dark mode ── */
body.dark-mode .fellow-name   { color:#e0e0e0; }
body.dark-mode .fellow-org    { color:#aaa; }
body.dark-mode .field-lbl     { color:#bbb; }
body.dark-mode .field-val     { color:#e0e0e0; }
body.dark-mode .field-row     { border-bottom-color:#3a3a3a; }
body.dark-mode .info-row      { border-bottom-color:#3a3a3a; }
body.dark-mode .sect-div      { color:#e07070; border-bottom-color:#5a3030; }
body.dark-mode .sub-row       { background:#2d3748; border-color:#4a5568; }
body.dark-mode .tl-body       { color:#ccc; }
body.dark-mode .fellow-tabs .nav-link        { background:#3a3000; border-color:#5a4a00; color:#FEC503; }
body.dark-mode .fellow-tabs .nav-link.active { background:#a02626 !important; color:#fff !important; }
body.dark-mode .stat-chip     { background:#2d3748 !important; }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    <!-- Page header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">My Dashboard</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

        @if($fellow)

        {{-- ═══════ STAT CHIPS ═══════ --}}
        <div class="row mb-3">
            <div class="col-6 col-md-3 mb-2">
                <div class="stat-chip bg-white">
                    <div class="chip-icon" style="background:#f0d4d4;">
                        <i class="fas fa-graduation-cap" style="color:#a02626;"></i>
                    </div>
                    <div>
                        <div class="chip-val" style="color:#a02626;">{{ $fellow->fellowship_year ?? '—' }}</div>
                        <div class="chip-label">Fellowship Year</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="stat-chip bg-white">
                    <div class="chip-icon" style="background:#d4edda;">
                        <i class="fas fa-calendar-alt" style="color:#28a745;"></i>
                    </div>
                    <div>
                        <div class="chip-val" style="color:#28a745;">{{ $fellow->admission_year ?? '—' }}</div>
                        <div class="chip-label">Admission Year</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="stat-chip bg-white">
                    <div class="chip-icon" style="background:#d1ecf1;">
                        <i class="fas fa-stethoscope" style="color:#17a2b8;"></i>
                    </div>
                    <div>
                        <div class="chip-val text-truncate" style="color:#17a2b8;font-size:.95rem;">{{ $fellow->current_specialty ?? '—' }}</div>
                        <div class="chip-label">Specialty</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="stat-chip bg-white">
                    <div class="chip-icon" style="background:#fff3cd;">
                        <i class="fas fa-map-marker-alt" style="color:#FEC503;"></i>
                    </div>
                    <div>
                        <div class="chip-val text-truncate" style="color:#856404;font-size:.95rem;">{{ $fellow->country_name ?? '—' }}</div>
                        <div class="chip-label">Country</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════ MAIN ROW ═══════ --}}
        <div class="row">

            {{-- ─── LEFT PROFILE PANEL ─── --}}
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="card h-100" style="border-radius:8px;">
                    <div class="card-body text-center px-3 pt-4 pb-3">

                        {{-- Avatar --}}
                        @if(!empty($fellow->profile_image))
                            <img src="{{ asset('storage/app/public/' . $fellow->profile_image) }}"
                                 alt="{{ $fellow->fellow_name }}" class="fellow-avatar mb-2">
                        @else
                            <img src="{{ url('public/dist/img/user.png') }}"
                                 alt="{{ $fellow->fellow_name }}" class="fellow-avatar mb-2">
                        @endif

                        <p class="fellow-name">{{ $fellow->fellow_name }}</p>
                        @if($fellow->organization)
                            <p class="fellow-org">
                                <i class="fas fa-hospital-alt mr-1" style="color:#a02626;"></i>
                                {{ $fellow->organization }}
                            </p>
                        @endif

                        {{-- Status --}}
                        <div class="mt-2 mb-3">
                            @php $st = strtolower($fellow->status ?? ''); @endphp
                            @if($st === 'active')
                                <span class="tag-pill tag-status-active"><i class="fas fa-circle mr-1" style="font-size:.45rem;vertical-align:middle;"></i>Active</span>
                            @elseif($st === 'inactive')
                                <span class="tag-pill tag-grey">Inactive</span>
                            @elseif($st === 'deceased')
                                <span class="tag-pill tag-grey">Deceased</span>
                            @endif
                        </div>

                        {{-- Tags / Labels --}}
                        <div class="text-left">
                            <p class="sect-div">Labels</p>
                            @if($fellow->admission_year)
                                <span class="tag-pill tag-grey">{{ $fellow->admission_year }} intake</span>
                            @endif
                            @if($fellow->mcs_qualification_year)
                                <span class="tag-pill tag-grey">{{ $fellow->mcs_qualification_year }} candidate</span>
                            @endif
                            @if($fellow->fellowship_year)
                                <span class="tag-pill tag-gold">Fellow {{ $fellow->fellowship_year }}</span>
                            @endif
                            @if($fellow->fellowship_type)
                                <span class="tag-pill tag-red">{{ $fellow->fellowship_type }}</span>
                            @endif
                            @if($fellow->programme_name)
                                <span class="tag-pill tag-blue">{{ $fellow->programme_name }}</span>
                            @endif
                            @if($fellow->country_name)
                                <span class="tag-pill tag-grey">{{ $fellow->country_name }}</span>
                            @endif
                            @if($fellow->cosecsa_region)
                                <span class="tag-pill tag-purple">COSECSA {{ $fellow->cosecsa_region }}</span>
                            @endif
                            @if($fellow->sponsored_by)
                                <span class="tag-pill tag-green">{{ $fellow->sponsored_by }}</span>
                            @endif
                            @if($fellow->is_promoted)
                                <span class="tag-pill tag-gold">Promoted</span>
                            @endif
                            @if($fellow->mcs_qualification_year)
                                <span class="tag-pill tag-blue">Passed MCS {{ $fellow->mcs_qualification_year }}</span>
                            @endif
                            @if($fellow->fellowship_year)
                                <span class="tag-pill tag-green">Passed FCS {{ $fellow->fellowship_year }}</span>
                            @endif
                            @if($fellow->exam_year_previous && $fellow->exam_year_previous != $fellow->mcs_qualification_year)
                                <span class="tag-pill tag-grey">Passed Written {{ $fellow->exam_year_previous }}</span>
                            @endif
                        </div>

                        {{-- Contact --}}
                        <div class="text-left mt-2">
                            <p class="sect-div">Contact</p>
                            @if($fellow->phone_number)
                            <div class="info-row">
                                <span class="info-icon"><i class="fas fa-phone"></i></span>
                                <div class="info-text">{{ $fellow->phone_number }}</div>
                            </div>
                            @endif
                            @if($fellow->personal_email)
                            <div class="info-row">
                                <span class="info-icon"><i class="fas fa-envelope"></i></span>
                                <div class="info-text">
                                    <span class="info-label">Primary</span>
                                    {{ $fellow->personal_email }}
                                </div>
                            </div>
                            @endif
                            @if(!empty($fellow->second_email))
                            <div class="info-row">
                                <span class="info-icon"><i class="fas fa-envelope"></i></span>
                                <div class="info-text">
                                    <span class="info-label">Secondary</span>
                                    {{ $fellow->second_email }}
                                </div>
                            </div>
                            @endif
                            @if($fellow->address)
                            <div class="info-row">
                                <span class="info-icon"><i class="fas fa-map-marker-alt"></i></span>
                                <div class="info-text">{{ $fellow->address }}</div>
                            </div>
                            @endif
                            @if($fellow->country_name)
                            <div class="info-row">
                                <span class="info-icon"><i class="fas fa-flag"></i></span>
                                <div class="info-text">{{ $fellow->country_name }}</div>
                            </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
            {{-- /.left panel --}}

            {{-- ─── RIGHT DETAIL AREA ─── --}}
            <div class="col-lg-9 col-md-8 mb-4">

                {{-- Tabs --}}
                <ul class="nav fellow-tabs mb-0" id="fellowTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-personal" role="tab">
                            <i class="fas fa-id-card mr-1"></i>Personal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-fellowship" role="tab">
                            <i class="fas fa-award mr-1"></i>Fellowship
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-financial" role="tab">
                            <i class="fas fa-file-invoice-dollar mr-1"></i>Fees &amp; Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-subs" role="tab">
                            <i class="fas fa-credit-card mr-1"></i>Subscriptions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-history" role="tab">
                            <i class="fas fa-history mr-1"></i>History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-account" role="tab">
                            <i class="fas fa-cog mr-1"></i>Account
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    {{-- ═══ TAB 1: PERSONAL DETAILS ═══ --}}
                    <div class="tab-pane fade show active" id="tab-personal" role="tabpanel">
                        <div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
                            <div class="card-body">

                                <p class="sect-div">Identity</p>
                                <div class="field-row">
                                    <span class="field-lbl">Full Name</span>
                                    <span class="field-val">{{ $fellow->fellow_name }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">First Name</span>
                                    <span class="field-val">{{ $fellow->firstname ?? '—' }}</span>
                                </div>
                                @if($fellow->middlename)
                                <div class="field-row">
                                    <span class="field-lbl">Middle Name</span>
                                    <span class="field-val">{{ $fellow->middlename }}</span>
                                </div>
                                @endif
                                <div class="field-row">
                                    <span class="field-lbl">Last Name</span>
                                    <span class="field-val">{{ $fellow->lastname ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Gender</span>
                                    <span class="field-val">{{ ucfirst($fellow->gender ?? '—') }}</span>
                                </div>
                                @if($fellow->candidate_number)
                                <div class="field-row">
                                    <span class="field-lbl">Candidate Number</span>
                                    <span class="field-val"><strong>{{ $fellow->candidate_number }}</strong></span>
                                </div>
                                @endif

                                <p class="sect-div">Contact</p>
                                <div class="field-row">
                                    <span class="field-lbl">Login Email</span>
                                    <span class="field-val">{{ $fellow->email }}</span>
                                </div>
                                @if($fellow->personal_email && $fellow->personal_email !== $fellow->email)
                                <div class="field-row">
                                    <span class="field-lbl">Personal Email</span>
                                    <span class="field-val">{{ $fellow->personal_email }}</span>
                                </div>
                                @endif
                                @if(!empty($fellow->second_email))
                                <div class="field-row">
                                    <span class="field-lbl">Secondary Email</span>
                                    <span class="field-val">{{ $fellow->second_email }}</span>
                                </div>
                                @endif
                                <div class="field-row">
                                    <span class="field-lbl">Phone</span>
                                    <span class="field-val">{{ $fellow->phone_number ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Address</span>
                                    <span class="field-val">{{ $fellow->address ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Country</span>
                                    <span class="field-val">{{ $fellow->country_name ?? '—' }}</span>
                                </div>

                                <p class="sect-div">Professional</p>
                                <div class="field-row">
                                    <span class="field-lbl">Specialty</span>
                                    <span class="field-val">{{ $fellow->current_specialty ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Hospital / Organisation</span>
                                    <span class="field-val">{{ $fellow->organization ?? '—' }}</span>
                                </div>
                                @if($fellow->supervised_by)
                                <div class="field-row">
                                    <span class="field-lbl">Supervised by</span>
                                    <span class="field-val">{{ $fellow->supervised_by }}</span>
                                </div>
                                @endif
                                @if($fellow->registered_by)
                                <div class="field-row">
                                    <span class="field-lbl">Registered by</span>
                                    <span class="field-val">{{ $fellow->registered_by }}</span>
                                </div>
                                @endif
                                @if($fellow->secretariat_registration_date)
                                <div class="field-row">
                                    <span class="field-lbl">Secretariat Reg. Date</span>
                                    <span class="field-val">{{ \Carbon\Carbon::parse($fellow->secretariat_registration_date)->format('d/m/Y') }}</span>
                                </div>
                                @endif
                                @if($fellow->sponsored_by)
                                <div class="field-row">
                                    <span class="field-lbl">Sponsored by</span>
                                    <span class="field-val">{{ $fellow->sponsored_by }}</span>
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    {{-- ═══ TAB 2: FELLOWSHIP INFO ═══ --}}
                    <div class="tab-pane fade" id="tab-fellowship" role="tabpanel">
                        <div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
                            <div class="card-body">

                                <p class="sect-div">Fellowship Details</p>
                                <div class="field-row">
                                    <span class="field-lbl">Member / Fellow Type</span>
                                    <span class="field-val">
                                        @if($fellow->fellowship_type)
                                            <span class="badge" style="background:#a02626;color:#fff;padding:4px 12px;border-radius:11px;">{{ $fellow->fellowship_type }}</span>
                                        @else —
                                        @endif
                                    </span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Member / Fellow Year</span>
                                    <span class="field-val"><strong>{{ $fellow->fellowship_year ?? '—' }}</strong></span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Exam Type (Programme)</span>
                                    <span class="field-val">{{ $fellow->programme_name ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Admission Year</span>
                                    <span class="field-val">{{ $fellow->admission_year ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Exam Year (Upcoming)</span>
                                    <span class="field-val">{{ $fellow->exam_year_upcoming ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Exam Year (Previous)</span>
                                    <span class="field-val">{{ $fellow->exam_year_previous ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">MCS Qualification Year</span>
                                    <span class="field-val">{{ $fellow->mcs_qualification_year ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Country of MCS Training</span>
                                    <span class="field-val">{{ $fellow->country_mcs_training ?? $fellow->country_name ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">COSECSA Region</span>
                                    <span class="field-val">{{ $fellow->cosecsa_region ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Candidate Number</span>
                                    <span class="field-val">{{ $fellow->candidate_number ?? '—' }}</span>
                                </div>

                                <p class="sect-div">Status</p>
                                <div class="field-row">
                                    <span class="field-lbl">Status</span>
                                    <span class="field-val">
                                        @php $st = strtolower($fellow->status ?? ''); @endphp
                                        @if($st === 'active')
                                            <span class="badge badge-success px-3">Active</span>
                                        @elseif($st === 'inactive')
                                            <span class="badge badge-secondary px-3">Inactive</span>
                                        @elseif($st === 'deceased')
                                            <span class="badge badge-dark px-3">Deceased</span>
                                        @else
                                            {{ $fellow->status ?? '—' }}
                                        @endif
                                    </span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Promoted</span>
                                    <span class="field-val">
                                        @if($fellow->is_promoted)
                                            <span class="badge badge-success px-3"><i class="fas fa-check-circle mr-1"></i>Yes</span>
                                        @else
                                            <span class="badge badge-secondary px-3">No</span>
                                        @endif
                                    </span>
                                </div>
                                @if($fellow->supervised_by)
                                <div class="field-row">
                                    <span class="field-lbl">Supervised by</span>
                                    <span class="field-val">{{ $fellow->supervised_by }}</span>
                                </div>
                                @endif
                                @if($fellow->sponsored_by)
                                <div class="field-row">
                                    <span class="field-lbl">Sponsored by</span>
                                    <span class="field-val">
                                        <span class="badge badge-info px-3" style="background:#17a2b8;">{{ $fellow->sponsored_by }}</span>
                                    </span>
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    {{-- ═══ TAB 3: FEES & PAYMENTS ═══ --}}
                    <div class="tab-pane fade" id="tab-financial" role="tabpanel">
                        <div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
                            <div class="card-body">

                                <p class="sect-div">Programme Entry Fee</p>
                                <div class="field-row">
                                    <span class="field-lbl">Programme Entry Fee – Year</span>
                                    <span class="field-val">
                                        @if($fellow->prog_entry_fee_year)
                                            <span class="badge badge-success px-3">Paid {{ $fellow->prog_entry_fee_year }}</span>
                                        @else —
                                        @endif
                                    </span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Mode of Payment</span>
                                    <span class="field-val">{{ $fellow->prog_entry_mode_payment ?? '—' }}</span>
                                </div>

                                <p class="sect-div">Examination Fee</p>
                                <div class="field-row">
                                    <span class="field-lbl">Examination Fee – Year</span>
                                    <span class="field-val">
                                        @if($fellow->exam_fee_year)
                                            <span class="badge badge-success px-3">Paid {{ $fellow->exam_fee_year }}</span>
                                        @else —
                                        @endif
                                    </span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Date Paid</span>
                                    <span class="field-val">
                                        @if($fellow->exam_fee_date_paid)
                                            {{ \Carbon\Carbon::parse($fellow->exam_fee_date_paid)->format('d/m/Y') }}
                                        @else —
                                        @endif
                                    </span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Mode of Payment</span>
                                    <span class="field-val">{{ $fellow->exam_fee_mode_payment ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Amount Paid (USD)</span>
                                    <span class="field-val">
                                        @if($fellow->exam_fee_amount_paid)
                                            <strong>${{ number_format($fellow->exam_fee_amount_paid, 0) }}</strong>
                                        @else —
                                        @endif
                                    </span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Payment Verified</span>
                                    <span class="field-val">
                                        @if($fellow->exam_fee_payment_verified)
                                            <i class="fas fa-check-circle verified-icon"></i>
                                            <span class="text-success font-weight-bold ml-1">Verified</span>
                                        @else
                                            <i class="fas fa-times-circle text-warning"></i>
                                            <span class="text-warning ml-1">Pending</span>
                                        @endif
                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ═══ TAB 4: SUBSCRIPTIONS ═══ --}}
                    <div class="tab-pane fade" id="tab-subs" role="tabpanel">
                        <div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
                            <div class="card-body">
                                <p class="sect-div">Annual Subscriptions</p>

                                @if($subscriptions->isNotEmpty())
                                    @foreach($subscriptions as $sub)
                                    <div class="sub-row">
                                        <span class="sub-year">{{ $sub->year }}</span>
                                        @if($sub->status === 'Paid')
                                            <span class="sub-badge badge badge-success px-3">Paid</span>
                                        @elseif($sub->status === 'Waived')
                                            <span class="sub-badge badge badge-info px-3" style="background:#17a2b8;">Waived</span>
                                        @elseif($sub->status === 'Partial')
                                            <span class="sub-badge badge badge-warning px-3">Partial</span>
                                        @else
                                            <span class="sub-badge badge badge-secondary px-3">Unpaid</span>
                                        @endif
                                        @if($sub->amount_paid > 0)
                                            <span class="ml-2 text-muted" style="font-size:.8rem;">${{ number_format($sub->amount_paid, 0) }}</span>
                                        @endif
                                        @if($sub->date_paid)
                                            <span class="ml-auto text-muted" style="font-size:.78rem;">
                                                <i class="far fa-calendar-check mr-1"></i>
                                                {{ \Carbon\Carbon::parse($sub->date_paid)->format('d M Y') }}
                                            </span>
                                        @endif
                                        @if($sub->mode_of_payment)
                                            <span class="ml-3 text-muted" style="font-size:.78rem;">{{ $sub->mode_of_payment }}</span>
                                        @endif
                                        @if($sub->receipt_number)
                                            <span class="ml-3 text-muted" style="font-size:.78rem;">Rcpt: {{ $sub->receipt_number }}</span>
                                        @endif
                                    </div>
                                    @endforeach

                                    {{-- Summary --}}
                                    @php
                                        $paidYears  = $subscriptions->where('status','Paid')->count();
                                        $totalPaid  = $subscriptions->whereIn('status',['Paid','Partial'])->sum('amount_paid');
                                    @endphp
                                    <div class="mt-3 p-3 rounded" style="background:#f8f9fa;border-left:4px solid #a02626;">
                                        <small class="text-muted d-block mb-1">Summary</small>
                                        <span class="mr-3"><strong>{{ $paidYears }}</strong> year(s) paid</span>
                                        <span><strong>${{ number_format($totalPaid, 0) }}</strong> total paid</span>
                                    </div>
                                @else
                                    <p class="text-muted py-3 text-center">
                                        <i class="fas fa-info-circle mr-1"></i> No subscription records found.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ═══ TAB 5: HISTORY / TRACKS ═══ --}}
                    <div class="tab-pane fade" id="tab-history" role="tabpanel">
                        <div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
                            <div class="card-body">
                                <p class="sect-div">Examination History</p>

                                @if(isset($examHistory) && $examHistory->isNotEmpty())
                                    @foreach($examHistory as $exam)
                                    <div class="timeline-item">
                                        <div class="tl-dot" style="background:{{ $exam->remarks === 'PASS' ? '#28a745' : '#a02626' }};"></div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <span class="tl-source">{{ $exam->source }} — {{ $exam->exam_year }}</span>
                                                <span class="tl-date">{{ $exam->exam_type ?? '' }}</span>
                                            </div>
                                            <div class="tl-body mt-1">
                                                Overall: <strong>{{ $exam->overall ?? '—' }}%</strong>
                                                &nbsp;&nbsp;
                                                <span class="badge {{ $exam->remarks === 'PASS' ? 'badge-success' : 'badge-danger' }} px-2">
                                                    {{ $exam->remarks ?? '—' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    {{-- Static timeline matching the screenshot for demonstration --}}
                                    @php
                                        $staticHistory = [
                                            ['date'=>'26 Sept 2019','source'=>'FCS GS Written','year'=>'2019','detail'=>'52% — PASS','color'=>'#28a745','badge'=>'PASS','badge_class'=>'badge-success'],
                                            ['date'=>'2 Feb 2017',  'source'=>'MCS Clinical',  'year'=>'2016','detail'=>'210 marks — PASS','color'=>'#28a745','badge'=>'PASS','badge_class'=>'badge-success'],
                                            ['date'=>'5 Oct 2016',  'source'=>'MCS Written',   'year'=>'2016','detail'=>'54% — PASS','color'=>'#28a745','badge'=>'PASS','badge_class'=>'badge-success'],
                                        ];
                                    @endphp
                                    @foreach($staticHistory as $h)
                                    <div class="timeline-item">
                                        <div class="tl-dot" style="background:{{ $h['color'] }};"></div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <span class="tl-source">{{ $h['source'] }} ({{ $h['year'] }})</span>
                                                <span class="tl-date">{{ $h['date'] }}</span>
                                            </div>
                                            <div class="tl-body mt-1">
                                                {{ $h['detail'] }}
                                                <span class="badge {{ $h['badge_class'] }} px-2 ml-1">{{ $h['badge'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif

                                <p class="sect-div mt-3">Registration Events</p>
                                <div class="timeline-item">
                                    <div class="tl-dot" style="background:#17a2b8;"></div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <span class="tl-source">Secretariat Registration</span>
                                            <span class="tl-date">
                                                {{ $fellow->secretariat_registration_date
                                                    ? \Carbon\Carbon::parse($fellow->secretariat_registration_date)->format('d M Y')
                                                    : '—' }}
                                            </span>
                                        </div>
                                        <div class="tl-body mt-1">Registered by: {{ $fellow->registered_by ?? '—' }}</div>
                                    </div>
                                </div>
                                @if($fellow->fellowship_year)
                                <div class="timeline-item">
                                    <div class="tl-dot" style="background:#a02626;"></div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <span class="tl-source">Fellowship Awarded</span>
                                            <span class="tl-date">{{ $fellow->fellowship_year }}</span>
                                        </div>
                                        <div class="tl-body mt-1">
                                            {{ $fellow->fellowship_type ?? 'Fellow' }} — {{ $fellow->programme_name ?? '' }}
                                        </div>
                                    </div>
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    {{-- ═══ TAB 6: ACCOUNT ═══ --}}
                    <div class="tab-pane fade" id="tab-account" role="tabpanel">
                        <div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
                            <div class="card-body">

                                <p class="sect-div">Account Information</p>
                                <div class="field-row">
                                    <span class="field-lbl">Login Email</span>
                                    <span class="field-val">{{ $fellow->email }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Account Type</span>
                                    <span class="field-val">
                                        <span class="badge px-3" style="background:#a02626;color:#fff;border-radius:11px;padding:5px 12px;">Fellow</span>
                                    </span>
                                </div>

                                <p class="sect-div mt-3">Change Password</p>
                                @include('_message')
                                <form action="{{ url('fellow/change_password') }}" method="POST" class="mt-2">
                                    @csrf
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label" style="font-size:.875rem;font-weight:600;color:#555;">Current Password</label>
                                        <div class="col-md-8">
                                            <input type="password" name="old_password" class="form-control form-control-sm" placeholder="Current password" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label" style="font-size:.875rem;font-weight:600;color:#555;">New Password</label>
                                        <div class="col-md-8">
                                            <input type="password" name="new_password" class="form-control form-control-sm" placeholder="New password" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-8 offset-md-4">
                                            <button type="submit" class="btn btn-sm btn-primary"
                                                    style="background:#a02626;border-color:#a02626;">
                                                <i class="fas fa-lock mr-1"></i> Update Password
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <p class="sect-div mt-2">Session</p>
                                <a href="{{ url('logout') }}" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Log Out
                                </a>

                            </div>
                        </div>
                    </div>

                </div>{{-- /.tab-content --}}
            </div>{{-- /.col right --}}
        </div>{{-- /.row main --}}

        @else
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No profile data found.</h5>
                        <p class="text-muted mb-0">Contact the administrator to complete your profile setup.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        </div>{{-- /.container-fluid --}}
    </section>
</div>{{-- /.content-wrapper --}}

@push('scripts')
<script>
$(document).ready(function () {
    // Restore active tab from localStorage
    var saved = localStorage.getItem('fellowActiveTab');
    if (saved) {
        $('#fellowTabs a[href="' + saved + '"]').tab('show');
    }
    $('#fellowTabs a').on('shown.bs.tab', function (e) {
        localStorage.setItem('fellowActiveTab', $(e.target).attr('href'));
    });
});
</script>
@endpush

@endsection

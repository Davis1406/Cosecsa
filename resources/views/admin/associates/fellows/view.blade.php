@extends('layout.app')

@push('styles')
<style>
/* ══════════════════════════════════════
   FELLOW ADMIN PROFILE – COSECSA STYLES
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
    cursor: default;
    background: #f0f0f0;
    color: #555;
}
.tag-red    { background:#f0f0f0; color:#555; }
.tag-green  { background:#f0f0f0; color:#555; }
.tag-grey   { background:#f0f0f0; color:#555; }
.tag-blue   { background:#f0f0f0; color:#555; }
.tag-gold   { background:#f0f0f0; color:#555; }
.tag-purple { background:#f0f0f0; color:#555; }
.tag-custom { background:#f0f0f0 !important; color:#555 !important; border: 1px solid #ddd !important; font-size: .68rem; }

/* ── Labels edit button ── */
.btn-labels-edit {
    font-size: .65rem;
    border-radius: 10px;
    background: #FEC503;
    border: 1px solid #e6b000;
    color: #333;
    transition: background .15s;
}
.btn-labels-edit:hover {
    background: #ffe566;
    border-color: #FEC503;
    color: #333;
}

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
    border-radius:6px; padding:12px 14px;
    display:flex; align-items:center; gap:12px;
    background:#fff; border:1px solid #e9ecef;
}
.chip-icon {
    width:36px; height:36px; border-radius:6px;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; flex-shrink:0;
    background:#f0d4d4; color:#a02626;
}
.chip-label { font-size:.65rem; color:#999; margin-bottom:1px; }
.chip-val   { font-size:.92rem; color:#222; }

/* ── Admin action bar ── */
.admin-action-bar {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 16px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

/* ── Labels edit panel ── */
#labelsEditPanel {
    display: none;
    background: #f8f9fa;
    border: 1px dashed #ced4da;
    border-radius: 6px;
    padding: 12px;
    margin-top: 8px;
}
.label-checkbox-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 3px 5px;
    font-size: .8rem;
}

/* ── Subscription table ── */
.subs-table th { font-size:.75rem; font-weight:700; color:#a02626; background:#fff5f5; }
.subs-table td { font-size:.82rem; }
.badge-paid     { background:#d4edda; color:#155724; }
.badge-unpaid   { background:#f8d7da; color:#721c24; }
.badge-partial  { background:#fff3cd; color:#856404; }
.badge-waived   { background:#e2e3e5; color:#383d41; }

/* ── History timeline ── */
.timeline-item { padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
.timeline-item:last-child { border-bottom: none; }
.tl-year  { font-size:.75rem; font-weight:700; color:#a02626; min-width:60px; }
.tl-label { font-size:.82rem; font-weight:600; }
.tl-sub   { font-size:.75rem; color:#6c757d; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0" style="font-size:1.2rem;">
                        <i class="fas fa-user-circle mr-2" style="color:#a02626;"></i>Fellow Profile
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
        @if ($fellow)

        {{-- Admin action bar --}}
        <div class="admin-action-bar">
            <div>
                <span class="font-weight-bold" style="color:#a02626; font-size:.9rem;">
                    <i class="fas fa-id-card mr-1"></i>
                    {{ trim(($fellow->firstname ?? '') . ' ' . ($fellow->lastname ?? '')) }}
                    @if($fellow->candidate_number)
                        <span class="text-muted font-weight-normal ml-2">({{ $fellow->candidate_number }})</span>
                    @endif
                </span>
            </div>
            <div class="d-flex flex-wrap" style="gap:6px;">
                <a href="{{ url('admin/associates/fellows/edit/' . $fellow->fellow_id) }}"
                   class="btn btn-sm btn-warning">
                    <i class="fas fa-edit mr-1"></i> Edit Fellow
                </a>
                <a href="{{ url('admin/associates/fellows/subscriptions/' . $fellow->fellow_id) }}"
                   class="btn btn-sm btn-info">
                    <i class="fas fa-receipt mr-1"></i> Subscriptions
                </a>
                <a href="{{ url('admin/associates/fellows/list') }}"
                   class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            {{-- ══ LEFT PANEL ══ --}}
            <div class="col-md-3">
                <div class="card" style="border-top:3px solid #a02626;">
                    <div class="card-body text-center pt-4 pb-2">
                        @if(!empty($fellow->profile_image))
                            <img src="{{ asset('storage/app/public/' . $fellow->profile_image) }}"
                                 alt="Profile" class="fellow-avatar mb-2">
                        @else
                            <div class="fellow-avatar d-flex align-items-center justify-content-center mx-auto mb-2"
                                 style="background:#f5e6e6; font-size:2.2rem; color:#a02626;">
                                <i class="fas fa-user-md"></i>
                            </div>
                        @endif
                        <p class="fellow-name mb-0">
                            {{ trim(($fellow->title ?? '') . ' ' . ($fellow->firstname ?? '') . ' ' . ($fellow->lastname ?? '')) }}
                        </p>
                        <p class="fellow-org">{{ $fellow->organization ?? '' }}</p>

                        {{-- Status pill --}}
                        <div class="mt-2 mb-2">
                            @php $st = $fellow->status ?? 'Unknown'; @endphp
                            <span class="badge badge-pill px-3 py-1"
                                  style="background:{{ $st=='Active'?'#d4edda':($st=='Deceased'?'#f8d7da':'#e2e3e5') }};
                                         color:{{ $st=='Active'?'#155724':($st=='Deceased'?'#721c24':'#383d41') }};
                                         font-size:.75rem;">
                                {{ $st }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        {{-- ── Labels / Tags ── --}}
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="sect-div mb-0" style="margin:0; border:none; font-size:.65rem;">LABELS</span>
                            <button type="button" class="btn btn-xs btn-labels-edit py-0 px-2"
                                    onclick="toggleLabelsEdit()">
                                <i class="fas fa-tag mr-1"></i>Edit
                            </button>
                        </div>

                        {{-- Static derived tags --}}
                        <div id="staticTags" class="mb-1">
                            @if($fellow->admission_year)
                                <span class="tag-pill tag-blue">Intake {{ $fellow->admission_year }}</span>
                            @endif
                            @if($fellow->mcs_qualification_year)
                                <span class="tag-pill tag-purple">Candidate {{ $fellow->mcs_qualification_year }}</span>
                            @endif
                            @if($fellow->fellowship_year)
                                <span class="tag-pill tag-red">Fellow {{ $fellow->fellowship_year }}</span>
                            @endif
                            @if($fellow->fellowship_type ?? null)
                                <span class="tag-pill tag-gold">{{ $fellow->fellowship_type }}</span>
                            @endif
                            @if($fellow->programme_name ?? null)
                                <span class="tag-pill tag-grey">{{ $fellow->programme_name }}</span>
                            @endif
                            @if($fellow->country_name ?? null)
                                <span class="tag-pill tag-green">{{ $fellow->country_name }}</span>
                            @endif
                            @if($fellow->cosecsa_region)
                                <span class="tag-pill tag-blue">{{ $fellow->cosecsa_region }}</span>
                            @endif
                            @if($fellow->sponsored_by)
                                <span class="tag-pill tag-purple">{{ $fellow->sponsored_by }}</span>
                            @endif
                            @if($fellow->is_promoted == '1')
                                <span class="tag-pill tag-green">MCS Passed</span>
                            @endif
                        </div>

                        {{-- Custom assigned labels --}}
                        <div id="customLabelsDisplay" class="mb-1">
                            @foreach($assignedLabels as $lbl)
                                <span class="tag-pill tag-custom">
                                    {{ $lbl->name }}
                                </span>
                            @endforeach
                        </div>

                        {{-- Inline labels edit panel --}}
                        <div id="labelsEditPanel">
                            <form action="{{ url('admin/associates/fellows/labels/' . $fellow->fellow_id) }}"
                                  method="POST" id="labelsForm">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    @forelse($allLabels as $lbl)
                                        <label class="label-checkbox-item">
                                            <input type="checkbox" name="labels[]"
                                                   value="{{ $lbl->id }}"
                                                   {{ in_array($lbl->id, $currentLabelIds) ? 'checked' : '' }}>
                                            <span class="tag-pill tag-custom py-0 mb-0">
                                                {{ $lbl->name }}
                                            </span>
                                        </label>
                                    @empty
                                        <p class="text-muted mb-1" style="font-size:.75rem;">
                                            No labels defined yet.
                                            <a href="{{ url('admin/settings/fellow-labels') }}">Manage labels →</a>
                                        </p>
                                    @endforelse
                                </div>
                                <div class="d-flex" style="gap:6px;">
                                    <button type="submit" class="btn btn-xs btn-success py-1 px-2" style="font-size:.72rem;">
                                        <i class="fas fa-save mr-1"></i>Save
                                    </button>
                                    <button type="button" class="btn btn-xs btn-secondary py-1 px-2" style="font-size:.72rem;"
                                            onclick="toggleLabelsEdit()">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <div class="sect-div mt-2">Contact</div>
                        @if($fellow->email ?? null)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-envelope"></i></span>
                            <span><span class="info-label">Login Email</span><span class="info-text">{{ $fellow->email }}</span></span>
                        </div>
                        @endif
                        @if($fellow->personal_email)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-envelope-open"></i></span>
                            <span><span class="info-label">Personal Email</span><span class="info-text">{{ $fellow->personal_email }}</span></span>
                        </div>
                        @endif
                        @if($fellow->phone_number)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-phone"></i></span>
                            <span><span class="info-label">Phone</span><span class="info-text">{{ $fellow->phone_number }}</span></span>
                        </div>
                        @endif
                        @if($fellow->address)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-map-marker-alt"></i></span>
                            <span><span class="info-label">Address</span><span class="info-text">{{ $fellow->address }}</span></span>
                        </div>
                        @endif
                        @if($fellow->country_name ?? null)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-flag"></i></span>
                            <span><span class="info-label">Country</span><span class="info-text">{{ $fellow->country_name }}</span></span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- /.left panel --}}

            {{-- ══ RIGHT PANEL ══ --}}
            <div class="col-md-9">
                {{-- Stat chips row --}}
                <div class="row mb-3">
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-graduation-cap"></i></div>
                            <div><div class="chip-label">Fellowship Year</div>
                                <strong class="chip-val">{{ $fellow->fellowship_year ?? '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-calendar-alt"></i></div>
                            <div><div class="chip-label">Intake Year</div>
                                <strong class="chip-val">{{ $fellow->admission_year ?? '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-receipt"></i></div>
                            <div><div class="chip-label">Subscriptions</div>
                                <strong class="chip-val">{{ $subscriptions->count() }}</strong></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-stethoscope"></i></div>
                            <div><div class="chip-label">Programme</div>
                                <strong class="chip-val" style="font-size:.78rem;">{{ $fellow->programme_name ?? '—' }}</strong></div>
                        </div>
                    </div>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs fellow-tabs" id="fellowTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-personal">Personal</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-fellowship">Fellowship</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-fees">Fees &amp; Payments</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-subs">Subscriptions</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-history">History</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-admin">Admin Notes</a></li>
                </ul>

                <div class="tab-content border border-top-0 p-3 bg-white" style="border-radius:0 0 6px 6px; min-height:320px;">

                    {{-- ── TAB: Personal ── --}}
                    <div class="tab-pane fade show active" id="tab-personal">
                        <p class="sect-div">Identity</p>
                        <div class="field-row"><span class="field-lbl">First Name</span><span class="field-val">{{ $fellow->firstname ?? '—' }}</span></div>
                        @if($fellow->middlename)
                        <div class="field-row"><span class="field-lbl">Middle Name</span><span class="field-val">{{ $fellow->middlename }}</span></div>
                        @endif
                        <div class="field-row"><span class="field-lbl">Last Name</span><span class="field-val">{{ $fellow->lastname ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Gender</span><span class="field-val">{{ ucfirst($fellow->gender ?? '—') }}</span></div>
                        @if($fellow->candidate_number)
                        <div class="field-row"><span class="field-lbl">Candidate Number</span><span class="field-val"><strong>{{ $fellow->candidate_number }}</strong></span></div>
                        @endif

                        <p class="sect-div">Contact</p>
                        <div class="field-row"><span class="field-lbl">Login Email</span><span class="field-val">{{ $fellow->email ?? '—' }}</span></div>
                        @if($fellow->personal_email && $fellow->personal_email !== ($fellow->email ?? ''))
                        <div class="field-row"><span class="field-lbl">Personal Email</span><span class="field-val">{{ $fellow->personal_email }}</span></div>
                        @endif
                        @if($fellow->second_email)
                        <div class="field-row"><span class="field-lbl">Secondary Email</span><span class="field-val">{{ $fellow->second_email }}</span></div>
                        @endif
                        <div class="field-row"><span class="field-lbl">Phone</span><span class="field-val">{{ $fellow->phone_number ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Address</span><span class="field-val">{{ $fellow->address ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Country</span><span class="field-val">{{ $fellow->country_name ?? '—' }}</span></div>
                        @if($fellow->cosecsa_region)
                        <div class="field-row"><span class="field-lbl">COSECSA Region</span><span class="field-val">{{ $fellow->cosecsa_region }}</span></div>
                        @endif

                        <p class="sect-div">Professional</p>
                        <div class="field-row"><span class="field-lbl">Specialty</span><span class="field-val">{{ $fellow->current_specialty ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Hospital / Organisation</span><span class="field-val">{{ $fellow->organization ?? '—' }}</span></div>
                        @if($fellow->supervised_by)
                        <div class="field-row"><span class="field-lbl">Supervised by</span><span class="field-val">{{ $fellow->supervised_by }}</span></div>
                        @endif
                        @if($fellow->registered_by)
                        <div class="field-row"><span class="field-lbl">Registered by</span><span class="field-val">{{ $fellow->registered_by }}</span></div>
                        @endif
                        @if($fellow->secretariat_registration_date)
                        <div class="field-row"><span class="field-lbl">Secretariat Reg. Date</span>
                            <span class="field-val">{{ \Carbon\Carbon::parse($fellow->secretariat_registration_date)->format('d/m/Y') }}</span></div>
                        @endif
                    </div>

                    {{-- ── TAB: Fellowship ── --}}
                    <div class="tab-pane fade" id="tab-fellowship">
                        <p class="sect-div">Fellowship Status</p>
                        <div class="field-row"><span class="field-lbl">Status</span>
                            <span class="field-val">
                                @php $st = $fellow->status ?? 'Unknown'; @endphp
                                <span class="badge" style="background:{{ $st=='Active'?'#d4edda':($st=='Deceased'?'#f8d7da':'#e2e3e5') }};
                                                                color:{{ $st=='Active'?'#155724':($st=='Deceased'?'#721c24':'#383d41') }};">{{ $st }}</span>
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Fellowship Type</span><span class="field-val">{{ $fellow->fellowship_type ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Fellowship Programme</span><span class="field-val">{{ $fellow->programme_name ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Promoted to Fellow</span>
                            <span class="field-val">
                                @if($fellow->is_promoted == '1')
                                    <span class="badge" style="background:#d4edda; color:#155724;">Yes</span>
                                @else
                                    <span class="badge" style="background:#e2e3e5; color:#383d41;">No</span>
                                @endif
                            </span>
                        </div>

                        <p class="sect-div">Academic Timeline</p>
                        <div class="field-row"><span class="field-lbl">Intake / Admission Year</span><span class="field-val">{{ $fellow->admission_year ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">MCS Qualification Year</span><span class="field-val">{{ $fellow->mcs_qualification_year ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Fellowship Year</span><span class="field-val">{{ $fellow->fellowship_year ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Candidate Number</span><span class="field-val">{{ $fellow->candidate_number ?? '—' }}</span></div>

                        <p class="sect-div">Training</p>
                        <div class="field-row"><span class="field-lbl">Supervised by</span><span class="field-val">{{ $fellow->supervised_by ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Country of MCS Training</span><span class="field-val">{{ $fellow->country_mcs_training ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Upcoming Exam Year</span><span class="field-val">{{ $fellow->exam_year_upcoming ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Previous Exam Year</span><span class="field-val">{{ $fellow->exam_year_previous ?? '—' }}</span></div>
                    </div>

                    {{-- ── TAB: Fees & Payments ── --}}
                    <div class="tab-pane fade" id="tab-fees">
                        <p class="sect-div">Programme Entry Fee</p>
                        <div class="field-row"><span class="field-lbl">Entry Fee Year</span><span class="field-val">{{ $fellow->prog_entry_fee_year ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Mode of Payment</span><span class="field-val">{{ $fellow->prog_entry_mode_payment ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Sponsored by</span><span class="field-val">{{ $fellow->sponsored_by ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Registered by</span><span class="field-val">{{ $fellow->registered_by ?? '—' }}</span></div>
                        @if($fellow->secretariat_registration_date)
                        <div class="field-row"><span class="field-lbl">Secretariat Reg. Date</span>
                            <span class="field-val">{{ \Carbon\Carbon::parse($fellow->secretariat_registration_date)->format('d M Y') }}</span></div>
                        @endif

                        <p class="sect-div">Examination Fee</p>
                        <div class="field-row"><span class="field-lbl">Exam Fee Year</span><span class="field-val">{{ $fellow->exam_fee_year ?? '—' }}</span></div>
                        @if($fellow->exam_fee_date_paid)
                        <div class="field-row"><span class="field-lbl">Date Paid</span>
                            <span class="field-val">{{ \Carbon\Carbon::parse($fellow->exam_fee_date_paid)->format('d M Y') }}</span></div>
                        @endif
                        <div class="field-row"><span class="field-lbl">Mode of Payment</span><span class="field-val">{{ $fellow->exam_fee_mode_payment ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Amount Paid</span>
                            <span class="field-val">
                                @if($fellow->exam_fee_amount_paid)
                                    <strong>USD {{ number_format($fellow->exam_fee_amount_paid, 2) }}</strong>
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Payment Verified</span>
                            <span class="field-val">
                                @if($fellow->exam_fee_payment_verified)
                                    <span class="badge" style="background:#d4edda; color:#155724;"><i class="fas fa-check mr-1"></i>Verified</span>
                                @else
                                    <span class="badge" style="background:#fff3cd; color:#856404;">Pending</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- ── TAB: Subscriptions ── --}}
                    <div class="tab-pane fade" id="tab-subs">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="sect-div mb-0" style="border:none;">Annual Dues</span>
                            <a href="{{ url('admin/associates/fellows/subscriptions/' . $fellow->fellow_id) }}"
                               class="btn btn-xs btn-outline-warning" style="font-size:.72rem; border-radius:10px;">
                                <i class="fas fa-plus mr-1"></i> Manage
                            </a>
                        </div>
                        @if($subscriptions->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover subs-table">
                                <thead><tr>
                                    <th>Year</th><th>Status</th><th>Amount Due</th><th>Amount Paid</th>
                                    <th>Date Paid</th><th>Mode</th>
                                </tr></thead>
                                <tbody>
                                @foreach($subscriptions as $sub)
                                <tr>
                                    <td><strong>{{ $sub->year }}</strong></td>
                                    <td>
                                        @php $bc = ['Paid'=>'badge-paid','Unpaid'=>'badge-unpaid','Partial'=>'badge-partial','Waived'=>'badge-waived'][$sub->status] ?? 'badge-waived'; @endphp
                                        <span class="badge {{ $bc }} px-2 py-1">{{ $sub->status }}</span>
                                    </td>
                                    <td>{{ $sub->amount_due ? 'USD '.$sub->amount_due : '—' }}</td>
                                    <td>{{ $sub->amount_paid ? 'USD '.$sub->amount_paid : '—' }}</td>
                                    <td>{{ $sub->date_paid ? \Carbon\Carbon::parse($sub->date_paid)->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $sub->mode_of_payment ?? '—' }}</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-receipt fa-2x mb-2"></i><br>No subscription records.
                        </div>
                        @endif
                    </div>

                    {{-- ── TAB: History ── --}}
                    <div class="tab-pane fade" id="tab-history">
                        <p class="sect-div">Examination History</p>
                        @if($examHistory && $examHistory->count())
                            @foreach($examHistory as $h)
                            <div class="timeline-item d-flex align-items-start gap-3">
                                <span class="tl-year">{{ $h->exam_year }}</span>
                                <div>
                                    <div class="tl-label">{{ $h->source ?? '' }} – {{ $h->exam_type ?? '' }}</div>
                                    <div class="tl-sub">
                                        Overall: <strong>{{ $h->overall ?? '—' }}</strong>
                                        @if($h->remarks) · {{ $h->remarks }} @endif
                                    </div>
                                </div>
                                <span class="ml-auto badge"
                                      style="background:{{ strtolower($h->remarks??'')==='pass'?'#d4edda':'#f8d7da' }};
                                             color:{{ strtolower($h->remarks??'')==='pass'?'#155724':'#721c24' }};">
                                    {{ $h->remarks ?? '' }}
                                </span>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-history fa-2x mb-2"></i><br>No exam history found.
                            </div>
                        @endif
                    </div>

                    {{-- ── TAB: Admin Notes ── --}}
                    <div class="tab-pane fade" id="tab-admin">
                        <p class="sect-div">Account & System Info</p>
                        <div class="field-row"><span class="field-lbl">User ID</span><span class="field-val">{{ $fellow->user_id ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Fellow Record ID</span><span class="field-val">{{ $fellow->fellow_id ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Login Email</span><span class="field-val">{{ $fellow->email ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Created At</span>
                            <span class="field-val">{{ $fellow->created_at ? \Carbon\Carbon::parse($fellow->created_at)->format('d M Y') : '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Last Updated</span>
                            <span class="field-val">{{ $fellow->updated_at ? \Carbon\Carbon::parse($fellow->updated_at)->format('d M Y H:i') : '—' }}</span></div>

                        <p class="sect-div mt-3">Quick Actions</p>
                        <div class="d-flex flex-wrap mt-2" style="gap:6px;">
                            <a href="{{ url('admin/associates/fellows/edit/' . $fellow->fellow_id) }}"
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit mr-1"></i>Edit Profile
                            </a>
                            <a href="{{ url('admin/associates/fellows/subscriptions/' . $fellow->fellow_id) }}"
                               class="btn btn-sm btn-info">
                                <i class="fas fa-receipt mr-1"></i>Manage Subscriptions
                            </a>
                            <a href="{{ url('admin/associates/fellows/list') }}"
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-list mr-1"></i>All Fellows
                            </a>
                        </div>
                    </div>

                </div>{{-- /.tab-content --}}
            </div>{{-- /.col right --}}
        </div>{{-- /.row main --}}

        @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Fellow not found.</h5>
                <a href="{{ url('admin/associates/fellows/list') }}" class="btn btn-sm btn-secondary mt-2">Back to List</a>
            </div>
        </div>
        @endif

        </div>{{-- /.container-fluid --}}
    </section>
</div>{{-- /.content-wrapper --}}

@push('scripts')
<script>
$(document).ready(function () {
    // Restore active tab
    var saved = localStorage.getItem('adminFellowViewTab');
    if (saved) { $('#fellowTabs a[href="' + saved + '"]').tab('show'); }
    $('#fellowTabs a').on('shown.bs.tab', function (e) {
        localStorage.setItem('adminFellowViewTab', $(e.target).attr('href'));
    });
});

function toggleLabelsEdit() {
    var panel = document.getElementById('labelsEditPanel');
    panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
}
</script>
@endpush

@endsection

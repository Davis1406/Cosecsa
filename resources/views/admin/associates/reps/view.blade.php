@extends('layout.app')

@push('styles')
<style>
/* ══════════════════════════════════════
   COUNTRY REP PROFILE – matches Fellow profile styling
══════════════════════════════════════ */
.rep-avatar {
    width: 100px; height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #a02626;
    box-shadow: 0 2px 10px rgba(160,38,38,.2);
}
.rep-name { font-size: 1.1rem; font-weight: 700; color: #222; margin-bottom: 1px; }
.rep-org  { font-size: .8rem; color: #6c757d; margin-bottom: 0; }

.tag-pill {
    display: inline-block; padding: 2px 9px; border-radius: 11px;
    font-size: .7rem; font-weight: 600; margin: 2px 2px; line-height: 1.6;
    cursor: default; background: #f0f0f0; color: #555;
}
.tag-red    { background:#f5e6e6; color:#a02626; }
.tag-green  { background:#eaf3de; color:#3b6d11; }
.tag-gold   { background:#fff8e1; color:#8a6d00; }
.tag-purple { background:#efe7fb; color:#5c3ba5; }
.tag-blue   { background:#e6f1fb; color:#185fa5; }

.sect-div {
    font-size:.68rem; font-weight:700; letter-spacing:.9px; text-transform:uppercase;
    color:#a02626; border-bottom:2px solid #f0d4d4; padding-bottom:3px; margin: 12px 0 8px;
}
.info-row { display:flex; align-items:flex-start; padding:5px 0; border-bottom:1px solid #f3f3f3; font-size:.83rem; }
.info-row:last-child { border-bottom:none; }
.info-icon { width:22px; color:#a02626; flex-shrink:0; padding-top:1px; font-size:.8rem; }
.info-label { font-size:.68rem; color:#aaa; display:block; line-height:1; margin-bottom:1px; }
.info-text  { color:#495057; }

.field-row { display:flex; padding:7px 0; border-bottom:1px solid #f5f5f5; font-size:.855rem; align-items:flex-start; }
.field-row:last-child { border-bottom:none; }
.field-lbl { width:42%; font-weight:600; color:#555; flex-shrink:0; padding-right:10px; }
.field-val { color:#222; }

.admin-action-bar {
    background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;
    padding: 10px 16px; margin-bottom: 14px;
    display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px;
}
body.dark-mode .admin-action-bar { background:#374151; border-color:#4a5568; }
body.dark-mode .info-row, body.dark-mode .field-row { border-color:#4a5568; }
body.dark-mode .info-text, body.dark-mode .field-val { color:#e0e0e0; }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 style="font-size:1.4rem;">
                            <i class="fas fa-flag mr-2" style="color:#a02626;"></i>Country Representative Profile
                        </h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if ($countryRep)

                {{-- Admin action bar --}}
                <div class="admin-action-bar">
                    <div>
                        <span class="font-weight-bold" style="color:#a02626; font-size:.9rem;">
                            <i class="fas fa-id-card mr-1"></i>{{ $countryRep->name }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap" style="gap:6px;">
                        <a href="{{ url('admin/associates/reps/edit/' . $countryRep->reps_id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        @if($linkedFellow)
                        <a href="{{ url('admin/associates/fellows/view/' . $linkedFellow->fellow_id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-user-graduate mr-1"></i> View Fellow Profile
                        </a>
                        @endif
                        <a href="{{ url('admin/associates/reps/list') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="row">
                    {{-- ══ LEFT PANEL ══ --}}
                    <div class="col-md-3">
                        <div class="card" style="border-top:3px solid #a02626;">
                            <div class="card-body text-center pt-4 pb-2">
                                @if(!empty($countryRep->profile_image))
                                    <img src="{{ asset('storage/' . $countryRep->profile_image) }}"
                                         alt="Profile" class="rep-avatar mb-2">
                                @else
                                    <div class="rep-avatar d-flex align-items-center justify-content-center mx-auto mb-2"
                                         style="background:#f5e6e6; font-size:2.2rem; color:#a02626;">
                                        <i class="fas fa-flag"></i>
                                    </div>
                                @endif
                                <p class="rep-name mb-0">{{ $countryRep->name }}</p>
                                <p class="rep-org">{{ $linkedFellow->programme_name ?? $linkedFellow->current_specialty ?? '' }}</p>

                                <div class="mt-2 mb-2">
                                    @php $pos = $countryRep->position ?? 'Country Representative'; @endphp
                                    <span class="badge badge-pill px-3 py-1"
                                          style="background:{{ $pos === 'WiSA chair' ? '#f0d4e8' : ($pos === 'Overseas Representative' ? '#d4e0f0' : '#d4edda') }};
                                                 color:{{ $pos === 'WiSA chair' ? '#7a2a5c' : ($pos === 'Overseas Representative' ? '#2a4d7a' : '#155724') }};
                                                 font-size:.75rem;">
                                        {{ $pos }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body pt-0">
                                <div class="mb-1">
                                    @if($countryRep->country_name)
                                        <a href="{{ url('admin/countries/view/'.$countryRep->country_id) }}" style="text-decoration:none;">
                                            <span class="tag-pill tag-green">{{ $countryRep->country_name }}</span>
                                        </a>
                                    @endif
                                    @if($linkedFellow->category_name ?? null)
                                        <span class="tag-pill tag-gold">{{ $linkedFellow->category_name }}</span>
                                    @endif
                                    @if($linkedFellow->admission_year ?? null)
                                        <span class="tag-pill tag-blue">Intake {{ $linkedFellow->admission_year }}</span>
                                    @endif
                                </div>

                                <div class="sect-div mt-2">Contact</div>
                                @if($countryRep->user_email)
                                <div class="info-row">
                                    <span class="info-icon"><i class="fas fa-envelope"></i></span>
                                    <span><span class="info-label">Login Email</span><span class="info-text">{{ $countryRep->user_email }}</span></span>
                                </div>
                                @endif
                                @if($countryRep->cosecsa_email)
                                <div class="info-row">
                                    <span class="info-icon"><i class="fas fa-envelope-open"></i></span>
                                    <span><span class="info-label">Cosecsa Email</span><span class="info-text">{{ $countryRep->cosecsa_email }}</span></span>
                                </div>
                                @endif
                                @if($countryRep->mobile_no)
                                <div class="info-row">
                                    <span class="info-icon"><i class="fas fa-phone"></i></span>
                                    <span><span class="info-label">Mobile</span><span class="info-text">{{ $countryRep->mobile_no }}</span></span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ══ RIGHT PANEL ══ --}}
                    <div class="col-md-9">
                        <div class="card" style="border-top:3px solid #a02626;">
                            <div class="card-body">
                                <p class="sect-div mt-0">Representative Details</p>
                                <div class="field-row">
                                    <span class="field-lbl">Full Name</span>
                                    <span class="field-val">{{ $countryRep->name }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Position</span>
                                    <span class="field-val">{{ $countryRep->position ?? 'Country Representative' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Country</span>
                                    <span class="field-val">
                                        @if($countryRep->country_id)
                                            <a href="{{ url('admin/countries/view/'.$countryRep->country_id) }}" style="color:#a02626;font-weight:500;text-decoration:none;">
                                                {{ $countryRep->country_name }}
                                            </a>
                                        @else {{ $countryRep->country_name ?: '—' }} @endif
                                    </span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Login Email</span>
                                    <span class="field-val">{{ $countryRep->user_email ?: '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Cosecsa Email</span>
                                    <span class="field-val">{{ $countryRep->cosecsa_email ?: '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Mobile Number</span>
                                    <span class="field-val">{{ $countryRep->mobile_no ?: '—' }}</span>
                                </div>

                                @if($linkedFellow)
                                <p class="sect-div">Fellow Profile</p>
                                <div class="field-row">
                                    <span class="field-lbl">Category</span>
                                    <span class="field-val">{{ $linkedFellow->category_name ?: '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Programme / Specialty</span>
                                    <span class="field-val">{{ $linkedFellow->programme_name ?? $linkedFellow->current_specialty ?? '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">Intake Year</span>
                                    <span class="field-val">{{ $linkedFellow->admission_year ?: '—' }}</span>
                                </div>
                                <div class="field-row">
                                    <span class="field-lbl">&nbsp;</span>
                                    <span class="field-val">
                                        <a href="{{ url('admin/associates/fellows/view/' . $linkedFellow->fellow_id) }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-user-graduate mr-1"></i>Open Full Fellow Profile
                                        </a>
                                    </span>
                                </div>
                                @else
                                <div class="text-center py-3 text-muted" style="font-size:.83rem;">
                                    <i class="fas fa-info-circle mr-1"></i>No linked fellow profile found for this account.
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @else
                <div class="text-center py-5 text-muted">
                    <p>No Country Representative data found.</p>
                </div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection

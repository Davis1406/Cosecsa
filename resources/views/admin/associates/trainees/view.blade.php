@extends('layout.app')

@push('styles')
<style>
/* ═══════════════════════════════════════
   TRAINEE ADMIN PROFILE – COSECSA STYLES
═══════════════════════════════════════ */

/* ── Tabs: active = red, inactive = gold ── */
.tp-tabs .nav-link {
    color: #b8860b; background: #fff8e1;
    border: 1px solid #e8d48b; border-bottom: none;
    font-weight: 600; font-size: .83rem; padding: 8px 16px;
    margin-right: 3px; border-radius: 6px 6px 0 0;
    transition: background .2s, color .2s;
}
.tp-tabs .nav-link:hover { background: #FEC503; color: #333; border-color: #FEC503; }
.tp-tabs .nav-link.active { background: #a02626 !important; color: #fff !important; border-color: #a02626 !important; }

/* ── Profile card ── */
.tp-avatar {
    width: 96px; height: 96px; border-radius: 50%;
    border: 3px solid #a02626;
    box-shadow: 0 2px 10px rgba(160,38,38,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 700; color: #fff;
    background: linear-gradient(135deg, #a02626, #c0392b);
    letter-spacing: 1px; text-transform: uppercase; flex-shrink: 0;
}
.tp-name  { font-weight: 700; color: #222; margin-bottom: 1px; }
.tp-sub   { font-size: .85rem; color: #6c757d; margin-bottom: 0; }

/* ── Tag pills ── */
.tag-pill {
    display: inline-block; padding: 2px 9px;
    border-radius: 11px; font-size: .7rem; font-weight: 600;
    margin: 2px 2px; line-height: 1.6; cursor: default;
    background: #f0f0f0; color: #555;
}
.tag-red    { background: #f8d7da; color: #721c24; }
.tag-green  { background: #d4edda; color: #155724; }
.tag-blue   { background: #cce5ff; color: #004085; }
.tag-gold   { background: #fff3cd; color: #856404; }
.tag-grey   { background: #e2e3e5; color: #383d41; }
.tag-purple { background: #e2d9f3; color: #6f42c1; }
/* Editable tag */
.tag-editable { cursor: pointer; transition: opacity .15s; }
.tag-editable:hover { opacity: .78; }
.tag-edit-icon { font-size:.6rem; opacity:.5; margin-right:2px; vertical-align:middle; }

/* ── Left-panel info rows ── */
.info-row {
    display: flex; align-items: flex-start;
    padding: 5px 0; border-bottom: 1px solid #f3f3f3;
}
.info-row:last-child { border-bottom: none; }
.info-icon { width: 22px; color: #a02626; flex-shrink: 0; padding-top: 2px; }
.info-label { font-size: .72rem; color: #aaa; display: block; line-height: 1; margin-bottom: 1px; }
.info-text  { color: #495057; }

/* ── Section divider ── */
.sect-div {
    font-size: .72rem; font-weight: 700;
    letter-spacing: .9px; text-transform: uppercase;
    color: #a02626; border-bottom: 2px solid #f0d4d4;
    padding-bottom: 3px; margin: 12px 0 8px;
}

/* ── Field rows in detail panels ── */
.field-row {
    display: flex; padding: 7px 0;
    border-bottom: 1px solid #f5f5f5; align-items: flex-start;
}
.field-row:last-child { border-bottom: none; }
.field-lbl { width: 42%; font-weight: 600; color: #555; flex-shrink: 0; padding-right: 10px; }
.field-val { color: #222; }

/* ── Stat chips ── */
.stat-chip {
    border-radius: 6px; padding: 12px 14px;
    display: flex; align-items: center; gap: 12px;
    background: #fff; border: 1px solid #e9ecef;
}
.chip-icon {
    width: 36px; height: 36px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
    background: #f0d4d4; color: #a02626;
}
.chip-label { font-size: .65rem; color: #999; margin-bottom: 1px; }
.chip-val   { font-size: .92rem; color: #222; }

/* ── Admin action bar ── */
.admin-action-bar {
    background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;
    padding: 10px 16px; margin-bottom: 16px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px;
}

/* ── Cross-profile link ── */
.profile-link-card {
    background: #fff8e1; border: 1px solid #fec503;
    border-radius: 8px; padding: 10px 14px;
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 14px; font-size: .83rem;
}
.profile-link-card .plink-icon { color: #856404; font-size: 1rem; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0" style="font-size:1.2rem;">
                        <i class="fas fa-user-circle mr-2" style="color:#a02626;"></i>Trainee Profile
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

        @if($trainee)
        @php
            $initials = collect(explode(' ', trim($trainee->name)))
                ->filter()->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');

            $statusClass = match(strtolower($trainee->status ?? '')) {
                'active'   => 'tag-green',
                'inactive' => 'tag-red',
                default    => 'tag-grey',
            };

            $invStatusClass = match(strtolower($trainee->invoice_status ?? '')) {
                'sent', 'paid' => 'tag-green',
                'pending'      => 'tag-gold',
                default        => 'tag-grey',
            };

            $sponsor = ($trainee->sponsor && $trainee->sponsor !== 'null') ? $trainee->sponsor : null;
            $amountFormatted = is_numeric($trainee->amount_paid) && $trainee->amount_paid > 0
                ? '$' . number_format((float)$trainee->amount_paid, 2) : null;
            $invAmtFormatted = is_numeric($trainee->invoice_amount ?? null) && $trainee->invoice_amount > 0
                ? '$' . number_format((float)$trainee->invoice_amount, 2) : null;

            // Linked candidate exam fee data
            $candAmountFormatted = null;
            if ($linkedCandidate && is_numeric($linkedCandidate->amount_paid ?? null) && $linkedCandidate->amount_paid > 0) {
                $candAmountFormatted = '$' . number_format((float)$linkedCandidate->amount_paid, 2);
            }
        @endphp

        {{-- Admin action bar --}}
        <div class="admin-action-bar">
            <div>
                <span class="font-weight-bold" style="color:#a02626; font-size:.9rem;">
                    <i class="fas fa-id-card mr-1"></i>{{ $trainee->name }}
                    @if($trainee->entry_number)
                        <span class="text-muted font-weight-normal ml-2">({{ $trainee->entry_number }})</span>
                    @endif
                </span>
            </div>
            <div class="d-flex flex-wrap" style="gap:6px;">
                <a href="{{ url('admin/associates/trainees/edit/' . $trainee->trainee_id) }}"
                   class="btn btn-sm btn-warning">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                @if($linkedCandidate)
                <a href="{{ url('admin/associates/candidates/view/' . ($linkedCandidate->candidates_id ?? $linkedCandidate->id)) }}"
                   class="btn btn-sm" style="background:#fff3cd; color:#856404; border:1px solid #fec503;">
                    <i class="fas fa-user-graduate mr-1"></i> View Candidate Profile
                </a>
                @endif
                <a href="{{ url('admin/associates/trainees/trainees') }}"
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
                        <div class="tp-avatar mx-auto mb-2">{{ $initials }}</div>
                        <p class="tp-name mb-0">{{ $trainee->name }}</p>
                        <p class="tp-sub">{{ $trainee->programme_name ?? '' }}</p>
                        <div class="mt-2 mb-2">
                            <span class="tag-pill {{ $statusClass }}">{{ $trainee->status ?: 'Unknown' }}</span>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <span class="sect-div d-block">Key Info</span>

                        {{-- Editable tag pills --}}
                        <div class="mb-2" id="keyInfoTags">

                            {{-- First Admission Year (read-only — from PEN) --}}
                            @if($firstAdmissionYear)
                                <span class="tag-pill tag-blue"
                                      title="First admission year derived from PEN {{ $trainee->entry_number }}">
                                    <i class="fas fa-lock" style="font-size:.55rem;opacity:.5;margin-right:2px;vertical-align:middle;"></i>
                                    First Intake {{ $firstAdmissionYear }}
                                </span>
                            @endif

                            {{-- Current Admission Year (editable) --}}
                            <span class="tag-pill tag-blue tag-editable"
                                  data-field="admission_year"
                                  data-value="{{ $trainee->admission_year }}"
                                  data-type="number"
                                  title="Click to edit current admission year">
                                <i class="fas fa-edit tag-edit-icon"></i>
                                Intake <span class="tag-label">{{ $trainee->admission_year ?: '—' }}</span>
                            </span>

                            {{-- Exam Year --}}
                            <span class="tag-pill tag-purple tag-editable"
                                  data-field="exam_year"
                                  data-value="{{ $trainee->exam_year }}"
                                  data-type="select"
                                  data-options="{{ json_encode($examYears) }}"
                                  title="Click to edit exam year">
                                <i class="fas fa-edit tag-edit-icon"></i>
                                Exam <span class="tag-label">{{ $trainee->exam_year ?: '—' }}</span>
                            </span>

                            {{-- Programme --}}
                            <span class="tag-pill tag-grey tag-editable"
                                  data-field="programme_id"
                                  data-value="{{ $trainee->programme_id ?? '' }}"
                                  data-type="select"
                                  data-options="{{ json_encode($programmes->pluck('name','id')) }}"
                                  title="Click to edit programme">
                                <i class="fas fa-edit tag-edit-icon"></i>
                                <span class="tag-label">{{ $trainee->programme_name ?: '—' }}</span>
                            </span>

                            {{-- Country --}}
                            <span class="tag-pill tag-green tag-editable"
                                  data-field="country_id"
                                  data-value="{{ $trainee->country_id ?? '' }}"
                                  data-type="select"
                                  data-options="{{ json_encode($countries->pluck('country_name','id')) }}"
                                  title="Click to edit country">
                                <i class="fas fa-edit tag-edit-icon"></i>
                                <span class="tag-label">{{ $trainee->country_name ?: '—' }}</span>
                            </span>

                            {{-- Status --}}
                            <span class="tag-pill {{ $statusClass }} tag-editable"
                                  data-field="status"
                                  data-value="{{ $trainee->status }}"
                                  data-type="select"
                                  data-options='{"Active":"Active","Inactive":"Inactive","Deferred":"Deferred"}'
                                  title="Click to edit status">
                                <i class="fas fa-edit tag-edit-icon"></i>
                                <span class="tag-label">{{ $trainee->status ?: 'Unknown' }}</span>
                            </span>

                            {{-- Invoice Status --}}
                            <span class="tag-pill {{ $invStatusClass }} tag-editable"
                                  data-field="invoice_status"
                                  data-value="{{ $trainee->invoice_status }}"
                                  data-type="select"
                                  data-options='{"Pending":"Pending","Sent":"Sent","Complete":"Complete"}'
                                  title="Click to edit invoice status">
                                <i class="fas fa-edit tag-edit-icon"></i>
                                Invoice: <span class="tag-label">{{ $trainee->invoice_status ?: 'Pending' }}</span>
                            </span>

                            {{-- Admission Letter Status --}}
                            <span class="tag-pill tag-grey tag-editable"
                                  data-field="admission_letter_status"
                                  data-value="{{ $trainee->admission_letter_status }}"
                                  data-type="select"
                                  data-options='{"Pending":"Pending","Sent":"Sent"}'
                                  title="Click to edit admission letter status">
                                <i class="fas fa-edit tag-edit-icon"></i>
                                Adm. Letter: <span class="tag-label">{{ $trainee->admission_letter_status ?: 'Pending' }}</span>
                            </span>

                            {{-- Also a Candidate (read-only) --}}
                            @if($linkedCandidate)
                                <span class="tag-pill tag-blue">Also a Candidate</span>
                            @endif
                        </div>

                        {{-- Inline editor popover --}}
                        <div id="tagEditorPopover" style="display:none;position:absolute;z-index:1050;
                             background:#fff;border:1px solid #ccc;border-radius:6px;
                             padding:10px 12px;box-shadow:0 4px 16px rgba(0,0,0,.15);min-width:200px;">
                            <div class="d-flex align-items-center mb-2">
                                <strong id="tagEditorLabel" style="font-size:.82rem;"></strong>
                                <button id="tagEditorClose" type="button"
                                        style="margin-left:auto;background:none;border:none;font-size:1rem;line-height:1;color:#999;cursor:pointer;">&times;</button>
                            </div>
                            <div id="tagEditorInput"></div>
                            <div class="mt-2 text-right">
                                <button id="tagEditorSave" class="btn btn-xs btn-danger" style="font-size:.78rem;">Save</button>
                            </div>
                        </div>

                        <div class="sect-div mt-2">Contact</div>
                        @if($trainee->personal_email)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-envelope"></i></span>
                            <span><span class="info-label">Personal Email</span>
                                  <span class="info-text">{{ $trainee->personal_email }}</span></span>
                        </div>
                        @endif
                        @if($trainee->user_email)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-user-lock"></i></span>
                            <span><span class="info-label">SFS Username</span>
                                  <span class="info-text">{{ $trainee->user_email }}</span></span>
                        </div>
                        @endif
                        @if($trainee->country_name)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-flag"></i></span>
                            <span><span class="info-label">Country</span>
                                  <span class="info-text">
                                      @if($trainee->country_id)<a href="{{ url('admin/countries/view/'.$trainee->country_id) }}" style="color:#a02626;">{{ $trainee->country_name }}</a>@else{{ $trainee->country_name }}@endif
                                  </span></span>
                        </div>
                        @endif
                        @if($trainee->hospital_name)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-hospital"></i></span>
                            <span><span class="info-label">Hospital</span>
                                  <span class="info-text">
                                      @if($trainee->hospital_id)<a href="{{ url('admin/hospital/view_hospital/'.$trainee->hospital_id) }}" style="color:#a02626;">{{ $trainee->hospital_name }}</a>@else{{ $trainee->hospital_name }}@endif
                                  </span></span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- /.left panel --}}

            {{-- ══ RIGHT PANEL ══ --}}
            <div class="col-md-9">
                {{-- Stat chips --}}
                <div class="row mb-3">
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-calendar-alt"></i></div>
                            <div><div class="chip-label">Admission Year</div>
                                <strong class="chip-val">{{ $trainee->admission_year ?? '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-graduation-cap"></i></div>
                            <div><div class="chip-label">Exam Year</div>
                                <strong class="chip-val">{{ ($trainee->exam_year && $trainee->exam_year > 0) ? $trainee->exam_year : '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-book-medical"></i></div>
                            <div><div class="chip-label">Study Year</div>
                                <strong class="chip-val" style="font-size:.8rem;">{{ $trainee->programme_year ?? '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-stethoscope"></i></div>
                            <div><div class="chip-label">Programme</div>
                                <strong class="chip-val" style="font-size:.78rem;">{{ $trainee->programme_name ?? '—' }}</strong></div>
                        </div>
                    </div>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs tp-tabs" id="tpTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-personal">Personal</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-training">Training</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-fees">Fees &amp; Payments</a></li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-results">
                            Exam Results
                            @if(isset($capsuleExamResults) && $capsuleExamResults->count())
                            <span class="badge badge-pill ml-1" style="background:#a02626;color:#fff;font-size:.65rem;">{{ $capsuleExamResults->count() }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-admin">Admin</a></li>
                </ul>

                <div class="tab-content border border-top-0 p-3 bg-white" style="border-radius:0 0 6px 6px; min-height:300px;">

                    {{-- ── TAB: Personal ── --}}
                    <div class="tab-pane fade show active" id="tab-personal">
                        <p class="sect-div">Identity</p>
                        <div class="field-row"><span class="field-lbl">First Name</span><span class="field-val">{{ $trainee->firstname ?? '—' }}</span></div>
                        @if($trainee->middlename)
                        <div class="field-row"><span class="field-lbl">Middle Name</span><span class="field-val">{{ $trainee->middlename }}</span></div>
                        @endif
                        <div class="field-row"><span class="field-lbl">Last Name</span><span class="field-val">{{ $trainee->lastname ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Gender</span><span class="field-val">{{ $trainee->gender ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Entry Number (PEN)</span><span class="field-val">{{ $trainee->entry_number ?? '—' }}</span></div>

                        <p class="sect-div">Contact</p>
                        <div class="field-row"><span class="field-lbl">Personal Email</span>
                            <span class="field-val">
                                @if($trainee->personal_email)
                                    <a href="mailto:{{ $trainee->personal_email }}">{{ $trainee->personal_email }}</a>
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Country</span><span class="field-val">@if($trainee->country_id ?? null)<a href="{{ url('admin/countries/view/'.$trainee->country_id) }}" style="color:#a02626;">{{ $trainee->country_name }}</a>@else{{ $trainee->country_name ?? '—' }}@endif</span></div>

                        <p class="sect-div">Correspondence</p>
                        <div class="field-row"><span class="field-lbl">Admission Letter</span>
                            <span class="field-val">
                                @php $als = $trainee->admission_letter_status ?? 'Pending'; @endphp
                                <span class="badge" style="background:{{ $als=='Sent' ? '#d4edda' : '#fff3cd' }}; color:{{ $als=='Sent' ? '#155724' : '#856404' }};">{{ $als }}</span>
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Invitation Letter</span>
                            <span class="field-val">
                                @php $ils = $trainee->invitation_letter_status ?? 'Pending'; @endphp
                                <span class="badge" style="background:{{ $ils=='Sent' ? '#d4edda' : '#fff3cd' }}; color:{{ $ils=='Sent' ? '#155724' : '#856404' }};">{{ $ils }}</span>
                            </span>
                        </div>
                    </div>

                    {{-- ── TAB: Training ── --}}
                    <div class="tab-pane fade" id="tab-training">
                        <p class="sect-div">Programme Details</p>
                        <div class="field-row"><span class="field-lbl">Programme</span><span class="field-val">@if($trainee->programme_id ?? null)<a href="{{ url('admin/programmes/view/'.$trainee->programme_id) }}" style="color:#a02626;">{{ $trainee->programme_name }}</a>@else{{ $trainee->programme_name ?? '—' }}@endif</span></div>
                        <div class="field-row"><span class="field-lbl">Hospital</span><span class="field-val">@if($trainee->hospital_id ?? null)<a href="{{ url('admin/hospital/view_hospital/'.$trainee->hospital_id) }}" style="color:#a02626;">{{ $trainee->hospital_name }}</a>@else{{ $trainee->hospital_name ?? '—' }}@endif</span></div>
                        <div class="field-row"><span class="field-lbl">Country</span><span class="field-val">@if($trainee->country_id ?? null)<a href="{{ url('admin/countries/view/'.$trainee->country_id) }}" style="color:#a02626;">{{ $trainee->country_name }}</a>@else{{ $trainee->country_name ?? '—' }}@endif</span></div>
                        <div class="field-row"><span class="field-lbl">Programme Duration</span>
                            <span class="field-val">
                                @if($trainee->programme_period)
                                    {{ $trainee->programme_period }} {{ $trainee->programme_period == 1 ? 'Year' : 'Years' }}
                                @else —
                                @endif
                            </span>
                        </div>

                        <p class="sect-div">Academic Timeline</p>
                        <div class="field-row"><span class="field-lbl">Admission Year</span><span class="field-val">{{ $trainee->admission_year ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Study Year</span><span class="field-val">{{ $trainee->programme_year ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Exam Year</span><span class="field-val">{{ ($trainee->exam_year && $trainee->exam_year > 0) ? $trainee->exam_year : '—' }}</span></div>

                        @if($linkedCandidate)
                        <p class="sect-div">Candidate Details</p>
                        <div class="field-row"><span class="field-lbl">Candidate Number</span>
                            <span class="field-val"><strong>{{ $linkedCandidate->candidate_id ?? '—' }}</strong></span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Exam Number</span>
                            <span class="field-val">{{ $linkedCandidate->exam_number ?? '—' }}</span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Exam Group</span>
                            <span class="field-val">{{ $linkedCandidate->group_name ?? '—' }}</span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Repeat Paper 1</span>
                            <span class="field-val">
                                @if(($linkedCandidate->repeat_paper_one ?? 'No') === 'Yes')
                                    <span class="badge badge-warning" style="color:#333;">Yes</span>
                                @else <span class="text-muted">No</span>
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Repeat Paper 2</span>
                            <span class="field-val">
                                @if(($linkedCandidate->repeat_paper_two ?? 'No') === 'Yes')
                                    <span class="badge badge-warning" style="color:#333;">Yes</span>
                                @else <span class="text-muted">No</span>
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">MMed Qualified</span>
                            <span class="field-val">
                                @if(($linkedCandidate->mmed ?? 'No') === 'Yes')
                                    <span class="badge badge-success">Yes</span>
                                @else <span class="text-muted">No</span>
                                @endif
                            </span>
                        </div>
                        @endif
                    </div>

                    {{-- ── TAB: Fees & Payments ── --}}
                    <div class="tab-pane fade" id="tab-fees">

                        <div class="text-right mb-2">
                            <a href="{{ url('admin/fees') }}?q={{ urlencode($trainee->name) }}&year=all" class="btn btn-xs btn-outline-danger">
                                <i class="fas fa-money-check-alt mr-1"></i>View in Fees Log
                            </a>
                        </div>

                        {{-- Programme Entry Fee (from trainees table) --}}
                        <p class="sect-div">Programme Entry Fee</p>
                        <div class="field-row"><span class="field-lbl">Invoice Number</span><span class="field-val">{{ $trainee->invoice_number ?: '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Invoice Date</span><span class="field-val">{{ $trainee->invoice_date ?: '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Invoice Amount</span>
                            <span class="field-val">
                                @if($invAmtFormatted)
                                    <strong>{{ $invAmtFormatted }}</strong>
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Invoice Status</span>
                            <span class="field-val">
                                @php $is = $trainee->invoice_status ?? 'Pending'; @endphp
                                <span class="badge" style="background:{{ in_array($is,['Sent','Paid']) ? '#d4edda' : '#fff3cd' }}; color:{{ in_array($is,['Sent','Paid']) ? '#155724' : '#856404' }};">{{ $is }}</span>
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Fee Paid</span>
                            <span class="field-val">
                                @if(($trainee->fee_paid ?? 'No') === 'Yes')
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>No</span>
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Amount Paid</span>
                            <span class="field-val">
                                @if($amountFormatted)
                                    <strong style="color:#a02626;">{{ $amountFormatted }}</strong>
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Mode of Payment</span><span class="field-val">{{ (!$trainee->mode_of_payment || preg_match('/^\d{4}-\d{2}-\d{2}/', $trainee->mode_of_payment)) ? '—' : $trainee->mode_of_payment }}</span></div>
                        <div class="field-row"><span class="field-lbl">Payment Date</span><span class="field-val">{{ $trainee->payment_date ?: '—' }}</span></div>
                        @if($sponsor)
                        <div class="field-row"><span class="field-lbl">Sponsor</span><span class="field-val">{{ $sponsor }}</span></div>
                        @endif

                        {{-- Examination Fee (from candidates table, if linked) --}}
                        <p class="sect-div mt-3">Examination Fee</p>
                        @if($linkedCandidate)
                            <div class="field-row"><span class="field-lbl">Invoice Number</span><span class="field-val">{{ $linkedCandidate->invoice_number ?? '—' }}</span></div>
                            <div class="field-row"><span class="field-lbl">Invoice Date</span>
                                <span class="field-val">
                                    @if(!empty($linkedCandidate->invoice_date))
                                        {{ \Carbon\Carbon::parse($linkedCandidate->invoice_date)->format('d M Y') }}
                                    @else —
                                    @endif
                                </span>
                            </div>
                            <div class="field-row"><span class="field-lbl">Invoice Amount</span>
                                <span class="field-val">
                                    @if(!empty($linkedCandidate->invoice_amount))
                                        <strong>${{ number_format($linkedCandidate->invoice_amount) }}</strong>
                                    @else —
                                    @endif
                                </span>
                            </div>
                            <div class="field-row"><span class="field-lbl">Invoice Status</span>
                                <span class="field-val">
                                    @php $cs = $linkedCandidate->invoice_status ?? 'Pending'; @endphp
                                    <span class="badge" style="background:{{ $cs==='Sent' ? '#cce5ff' : '#fff3cd' }}; color:{{ $cs==='Sent' ? '#004085' : '#856404' }};">{{ $cs }}</span>
                                </span>
                            </div>
                            <div class="field-row"><span class="field-lbl">Fee Paid</span>
                                <span class="field-val">
                                    @if(($linkedCandidate->fee_paid ?? 'No') === 'Yes')
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>No</span>
                                    @endif
                                </span>
                            </div>
                            <div class="field-row"><span class="field-lbl">Amount Paid</span>
                                <span class="field-val">
                                    @if($candAmountFormatted)
                                        <strong style="color:#a02626;">{{ $candAmountFormatted }}</strong>
                                    @else —
                                    @endif
                                </span>
                            </div>
                            <div class="field-row"><span class="field-lbl">Payment Date</span>
                                <span class="field-val">
                                    @if(!empty($linkedCandidate->payment_date))
                                        {{ \Carbon\Carbon::parse($linkedCandidate->payment_date)->format('d M Y') }}
                                    @else —
                                    @endif
                                </span>
                            </div>
                            <div class="field-row"><span class="field-lbl">Mode of Payment</span><span class="field-val">{{ (!$linkedCandidate->mode_of_payment || preg_match('/^\d{4}-\d{2}-\d{2}/', $linkedCandidate->mode_of_payment)) ? '—' : $linkedCandidate->mode_of_payment }}</span></div>
                            @if(!empty($linkedCandidate->sponsor))
                            <div class="field-row"><span class="field-lbl">Sponsor</span><span class="field-val">{{ $linkedCandidate->sponsor }}</span></div>
                            @endif
                        @else
                            <div class="text-center py-3 text-muted" style="font-size:.83rem;">
                                <i class="fas fa-info-circle mr-1"></i>No candidate exam fee record linked to this trainee.
                            </div>
                        @endif
                    </div>

                    {{-- ── TAB: Exam Results ── --}}
                    <div class="tab-pane fade" id="tab-results">
                        <p class="sect-div">Exam Results (from Capsule CRM)</p>
                        @if(isset($capsuleExamResults) && $capsuleExamResults->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" style="font-size:.85rem;">
                                <thead style="background:#fff5f5;">
                                    <tr>
                                        <th style="color:#a02626;font-weight:700;">Year</th>
                                        <th style="color:#a02626;font-weight:700;">Specialty</th>
                                        <th style="color:#a02626;font-weight:700;">Exam Type</th>
                                        <th style="color:#a02626;font-weight:700;">Score</th>
                                        <th style="color:#a02626;font-weight:700;">Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($capsuleExamResults as $r)
                                    @php
                                        $rRes = strtolower($r->result ?? '');
                                        $rBg  = $rRes === 'pass' ? '#d4edda' : ($rRes === 'fail' ? '#f8d7da' : '#e9ecef');
                                        $rClr = $rRes === 'pass' ? '#155724' : ($rRes === 'fail' ? '#721c24' : '#495057');
                                    @endphp
                                    <tr>
                                        <td>{{ $r->exam_year }}</td>
                                        <td>{{ $r->specialty ?? '(General)' }}</td>
                                        <td>{{ $r->exam_type ?? '-' }}</td>
                                        <td>{{ $r->score !== null ? number_format($r->score, 2) : '-' }}</td>
                                        <td>
                                            <span class="badge" style="background:{{ $rBg }};color:{{ $rClr }};font-size:.8rem;padding:3px 8px;">
                                                {{ strtoupper($r->result ?? '-') }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>No exam results found for this trainee.
                        </div>
                        @endif
                    </div>

                    {{-- ── TAB: Admin ── --}}
                    <div class="tab-pane fade" id="tab-admin">
                        <p class="sect-div">System Access</p>
                        <div class="field-row"><span class="field-lbl">SFS Username</span>
                            <span class="field-val"><code>{{ $trainee->user_email ?? '—' }}</code></span>
                        </div>
                        <div class="field-row"><span class="field-lbl">User ID</span><span class="field-val">{{ $trainee->user_id ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Trainee Record ID</span><span class="field-val">{{ $trainee->trainee_id ?? '—' }}</span></div>

                        @if($linkedCandidate)
                        <p class="sect-div mt-3">Linked Candidate Record</p>
                        <div class="field-row"><span class="field-lbl">Candidate Record ID</span>
                            <span class="field-val">{{ $linkedCandidate->candidates_id ?? $linkedCandidate->id ?? '—' }}</span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Exam Year (Candidate)</span>
                            <span class="field-val">{{ $linkedCandidate->exam_year ?? '—' }}</span>
                        </div>
                        @endif

                        <p class="sect-div mt-3">Quick Actions</p>
                        <div class="d-flex flex-wrap mt-2" style="gap:6px;">
                            <a href="{{ url('admin/associates/trainees/edit/' . $trainee->trainee_id) }}"
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit mr-1"></i>Edit Profile
                            </a>
                            @if($linkedCandidate)
                            <a href="{{ url('admin/associates/candidates/view/' . ($linkedCandidate->candidates_id ?? $linkedCandidate->id)) }}"
                               class="btn btn-sm" style="background:#fff3cd; color:#856404; border:1px solid #fec503;">
                                <i class="fas fa-user-graduate mr-1"></i>View Candidate Profile
                            </a>
                            @endif
                            <a href="{{ url('admin/associates/trainees/trainees') }}"
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-list mr-1"></i>All Trainees
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
                <h5 class="text-muted">Trainee not found.</h5>
                <a href="{{ url('admin/associates/trainees/trainees') }}" class="btn btn-sm btn-secondary mt-2">Back to List</a>
            </div>
        </div>
        @endif

        </div>{{-- /.container-fluid --}}
    </section>
</div>{{-- /.content-wrapper --}}

@push('scripts')
<script>
$(document).ready(function () {
    // ── Tab memory ───────────────────────────────────────────────────────────
    // A URL hash (e.g. from the Fees page) wins over the last-viewed tab.
    if (location.hash) {
        $('#tpTabs a[href="' + location.hash + '"]').tab('show');
    } else {
        var saved = localStorage.getItem('adminTraineeViewTab');
        if (saved) { $('#tpTabs a[href="' + saved + '"]').tab('show'); }
    }
    $('#tpTabs a').on('shown.bs.tab', function (e) {
        localStorage.setItem('adminTraineeViewTab', $(e.target).attr('href'));
    });

    // ── Editable tag pills ───────────────────────────────────────────────────
    var $pop      = $('#tagEditorPopover');
    var $activTag = null;

    function fieldLabel(field) {
        var map = {
            admission_year:'Admission Year', exam_year:'Exam Year',
            programme_id:'Programme', country_id:'Country',
            status:'Status', invoice_status:'Invoice Status',
            admission_letter_status:'Adm. Letter Status'
        };
        return map[field] || field;
    }

    // Build the inner input/select
    function buildInput($tag) {
        var type    = $tag.data('type');
        var current = $tag.data('value');
        var html    = '';

        if (type === 'select') {
            var opts  = $tag.data('options');
            // opts may be a plain object {val:label} or an array of values
            html = '<select id="tagEditorValue" class="form-control form-control-sm">';
            if ($.isArray(opts)) {
                $.each(opts, function(_, v){ html += '<option value="'+v+'"'+(v==current?' selected':'')+'>'+v+'</option>'; });
            } else {
                $.each(opts, function(v, lbl){ html += '<option value="'+v+'"'+(v==current?' selected':'')+'>'+lbl+'</option>'; });
            }
            html += '</select>';
        } else {
            html = '<input id="tagEditorValue" type="text" class="form-control form-control-sm" value="'+current+'">';
        }
        return html;
    }

    // Position popover below tag
    function showPopover($tag) {
        var field = $tag.data('field');
        $('#tagEditorLabel').text(fieldLabel(field));
        $('#tagEditorInput').html(buildInput($tag));

        var offset = $tag.offset();
        $pop.css({ top: offset.top + $tag.outerHeight() + 4, left: offset.left }).show();
        $('#tagEditorValue').focus();
        $activTag = $tag;
    }

    $('#keyInfoTags').on('click', '.tag-editable', function (e) {
        var $tag = $(this);
        if ($pop.is(':visible') && $activTag && $activTag.is($tag)) {
            $pop.hide(); $activTag = null; return;
        }
        showPopover($tag);
        e.stopPropagation();
    });

    $('#tagEditorClose').on('click', function () { $pop.hide(); $activTag = null; });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#tagEditorPopover, .tag-editable').length) {
            $pop.hide(); $activTag = null;
        }
    });

    // Save via AJAX
    $('#tagEditorSave').on('click', function () {
        if (!$activTag) return;
        var field   = $activTag.data('field');
        var newVal  = $('#tagEditorValue').val();
        var traineeId = {{ $trainee->trainee_id }};

        $.post('{{ url("admin/associates/trainees") }}/' + traineeId + '/quick-update', {
            _token: '{{ csrf_token() }}',
            field:  field,
            value:  newVal
        })
        .done(function (res) {
            // Update tag label
            $activTag.data('value', newVal);
            $activTag.find('.tag-label').text(res.label);
            $pop.hide(); $activTag = null;

            // Flash brief success highlight
            $activTag && $activTag.css('outline','2px solid #28a745');
            setTimeout(function(){ $activTag && $activTag.css('outline',''); }, 800);
        })
        .fail(function () {
            alert('Save failed. Please try again.');
        });
    });

    // Save on Enter key
    $('#tagEditorPopover').on('keydown', '#tagEditorValue', function (e) {
        if (e.key === 'Enter') $('#tagEditorSave').click();
        if (e.key === 'Escape') { $pop.hide(); $activTag = null; }
    });
});
</script>
@endpush

@endsection

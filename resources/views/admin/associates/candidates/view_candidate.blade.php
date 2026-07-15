@extends('layout.app')

@push('styles')
<style>
/* ═══════════════════════════════════════════
   CANDIDATE ADMIN PROFILE – COSECSA STYLES
═══════════════════════════════════════════ */
.cp-tabs .nav-link {
    color: #1a5276; background: #ebf5fb;
    border: 1px solid #aed6f1; border-bottom: none;
    font-weight: 600; font-size: .83rem; padding: 8px 16px;
    margin-right: 3px; border-radius: 6px 6px 0 0;
    transition: background .2s, color .2s;
}
.cp-tabs .nav-link:hover { background: #aed6f1; color: #1a5276; border-color: #aed6f1; }
.cp-tabs .nav-link.active { background: #2980b9 !important; color: #fff !important; border-color: #2980b9 !important; }

.cp-avatar {
    width: 96px; height: 96px; border-radius: 50%;
    border: 3px solid #2980b9;
    box-shadow: 0 2px 10px rgba(41,128,185,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 700; color: #fff;
    background: linear-gradient(135deg, #2980b9, #3498db);
    letter-spacing: 1px; text-transform: uppercase; flex-shrink: 0;
}
.cp-name  { font-weight: 700; color: #222; margin-bottom: 1px; }
.cp-sub   { font-size: .85rem; color: #6c757d; margin-bottom: 0; }

.tag-pill {
    display: inline-block; padding: 2px 9px;
    border-radius: 11px; font-size: .7rem; font-weight: 600;
    margin: 2px 2px; line-height: 1.6; cursor: default;
    background: #f0f0f0; color: #555;
}
.tag-green  { background: #d4edda; color: #155724; }
.tag-red    { background: #f8d7da; color: #721c24; }
.tag-blue   { background: #cce5ff; color: #004085; }
.tag-gold   { background: #fff3cd; color: #856404; }
.tag-grey   { background: #e2e3e5; color: #383d41; }
.tag-purple { background: #e2d9f3; color: #6f42c1; }
.tag-teal   { background: #d1ecf1; color: #0c5460; }

.info-row {
    display: flex; align-items: flex-start;
    padding: 5px 0; border-bottom: 1px solid #f3f3f3;
}
.info-row:last-child { border-bottom: none; }
.info-icon  { width: 22px; color: #2980b9; flex-shrink: 0; padding-top: 2px; }
.info-label { font-size: .72rem; color: #aaa; display: block; line-height: 1; margin-bottom: 1px; }
.info-text  { color: #495057; }

.sect-div {
    font-size: .72rem; font-weight: 700; letter-spacing: .9px; text-transform: uppercase;
    color: #2980b9; border-bottom: 2px solid #d0e8f8;
    padding-bottom: 3px; margin: 12px 0 8px;
}

.field-row {
    display: flex; padding: 7px 0;
    border-bottom: 1px solid #f5f5f5; align-items: flex-start;
}
.field-row:last-child { border-bottom: none; }
.field-lbl { width: 42%; font-weight: 600; color: #555; flex-shrink: 0; padding-right: 10px; }
.field-val { color: #222; }

.stat-chip {
    border-radius: 6px; padding: 12px 14px;
    display: flex; align-items: center; gap: 12px;
    background: #fff; border: 1px solid #e9ecef;
}
.chip-icon {
    width: 36px; height: 36px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0; background: #d6eaf8; color: #2980b9;
}
.chip-label { font-size: .65rem; color: #999; margin-bottom: 1px; }
.chip-val   { font-size: .92rem; color: #222; }

.admin-action-bar {
    background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;
    padding: 10px 16px; margin-bottom: 16px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px;
}
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0" style="font-size:1.2rem;">
                        <i class="fas fa-user-graduate mr-2" style="color:#2980b9;"></i>Candidate Profile
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

        @if($candidate)
        @php
            $initials = strtoupper(
                substr($candidate->firstname ?? $candidate->name ?? 'C', 0, 1) .
                substr($candidate->lastname ?? '', 0, 1)
            );
            if (strlen(trim($initials)) < 2) {
                $initials = strtoupper(substr($candidate->name ?? 'C', 0, 2));
            }
            $feePaid = $candidate->fee_paid ?? 'No';
            $prog    = $candidate->programme_name ?? '—';
            $sponsor = ($candidate->sponsor && $candidate->sponsor !== 'null') ? $candidate->sponsor : null;

            $candAmountFormatted = null;
            if (is_numeric($candidate->amount_paid ?? null) && $candidate->amount_paid > 0) {
                $candAmountFormatted = '$' . number_format((float)$candidate->amount_paid, 2);
            }
            $invAmountFormatted = null;
            if (is_numeric($candidate->invoice_amount ?? null) && $candidate->invoice_amount > 0) {
                $invAmountFormatted = '$' . number_format((float)$candidate->invoice_amount, 2);
            }

            // Trainee entry fee data
            $traineeAmountFormatted = null;
            if (isset($linkedTrainee) && is_numeric($linkedTrainee->amount_paid ?? null) && $linkedTrainee->amount_paid > 0) {
                $traineeAmountFormatted = '$' . number_format((float)$linkedTrainee->amount_paid, 2);
            }
        @endphp

        {{-- Admin action bar --}}
        <div class="admin-action-bar">
            <div>
                <span class="font-weight-bold" style="color:#2980b9; font-size:.9rem;">
                    <i class="fas fa-id-card mr-1"></i>
                    {{ $candidate->name ?? trim(($candidate->firstname ?? '') . ' ' . ($candidate->lastname ?? '')) }}
                    @if($candidate->entry_number)
                        <span class="text-muted font-weight-normal ml-2">({{ $candidate->entry_number }})</span>
                    @endif
                    @if($candidate->candidate_id)
                        <span class="text-muted font-weight-normal ml-1">· {{ $candidate->candidate_id }}</span>
                    @endif
                </span>
            </div>
            <div class="d-flex flex-wrap" style="gap:6px;">
                <a href="{{ url('admin/associates/candidates/edit/' . ($candidate->candidates_id ?? 0)) }}"
                   class="btn btn-sm btn-warning">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                @if(isset($linkedTrainee) && $linkedTrainee)
                <a href="{{ url('admin/associates/trainees/view/' . ($linkedTrainee->trainee_id ?? $linkedTrainee->id)) }}"
                   class="btn btn-sm" style="background:#f0d4d4; color:#721c24; border:1px solid #c0392b;">
                    <i class="fas fa-user-md mr-1"></i> View Trainee Profile
                </a>
                @endif
                <a href="{{ url('admin/associates/candidates/list') }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            {{-- ══ LEFT PANEL ══ --}}
            <div class="col-md-3">
                <div class="card" style="border-top:3px solid #2980b9;">
                    <div class="card-body text-center pt-4 pb-2">
                        <div class="cp-avatar mx-auto mb-2">{{ $initials }}</div>
                        <p class="cp-name mb-0">
                            {{ $candidate->name ?? trim(($candidate->firstname ?? '') . ' ' . ($candidate->lastname ?? '')) }}
                        </p>
                        <p class="cp-sub">{{ $prog }}</p>
                        <div class="mt-2 mb-2">
                            @if($feePaid === 'Yes')
                                <span class="tag-pill tag-green">Fee Paid</span>
                            @else
                                <span class="tag-pill tag-red">Fee Unpaid</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <span class="sect-div d-block">Key Info</span>
                        <div class="mb-2">
                            @if($candidate->exam_year)
                                <span class="tag-pill tag-blue">Exam {{ $candidate->exam_year }}</span>
                            @endif
                            @if($candidate->programme_name)
                                <a href="{{ url('admin/programmes/view/'.($candidate->programme_id ?? 0)) }}" style="text-decoration:none;">
                                    <span class="tag-pill tag-grey">{{ $candidate->programme_name }}</span>
                                </a>
                            @endif
                            @if($candidate->country_name)
                                <a href="{{ url('admin/countries/view/'.($candidate->country_id ?? 0)) }}" style="text-decoration:none;">
                                    <span class="tag-pill tag-teal">{{ $candidate->country_name }}</span>
                                </a>
                            @endif
                            @if($sponsor)
                                <span class="tag-pill tag-gold">{{ $sponsor }}</span>
                            @endif
                            @if(($candidate->repeat_paper_one ?? 'No') === 'Yes')
                                <span class="tag-pill tag-gold">Repeat P1</span>
                            @endif
                            @if(($candidate->repeat_paper_two ?? 'No') === 'Yes')
                                <span class="tag-pill tag-gold">Repeat P2</span>
                            @endif
                            @if(($candidate->mmed ?? 'No') === 'Yes')
                                <span class="tag-pill tag-purple">MMed</span>
                            @endif
                            @if(isset($linkedTrainee) && $linkedTrainee)
                                <span class="tag-pill tag-blue">Also a Trainee</span>
                            @endif
                            @if($candidate->group_name)
                                <span class="tag-pill tag-grey">{{ $candidate->group_name }}</span>
                            @endif
                        </div>

                        <div class="sect-div mt-2">Contact</div>
                        @if($candidate->personal_email)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-envelope"></i></span>
                            <span><span class="info-label">Personal Email</span>
                                  <span class="info-text">{{ $candidate->personal_email }}</span></span>
                        </div>
                        @endif
                        @if($candidate->user_email)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-user-lock"></i></span>
                            <span><span class="info-label">SFS Username</span>
                                  <span class="info-text">{{ $candidate->user_email }}</span></span>
                        </div>
                        @endif
                        @if($candidate->country_name)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-flag"></i></span>
                            <span><span class="info-label">Country</span>
                                  <span class="info-text">@if($candidate->country_id ?? null)<a href="{{ url('admin/countries/view/'.$candidate->country_id) }}" style="color:#a02626;">{{ $candidate->country_name }}</a>@else{{ $candidate->country_name }}@endif</span></span>
                        </div>
                        @endif
                        @if($candidate->hospital_name)
                        <div class="info-row">
                            <span class="info-icon"><i class="fas fa-hospital"></i></span>
                            <span><span class="info-label">Hospital</span>
                                  <span class="info-text">@if($candidate->hospital_id ?? null)<a href="{{ url('admin/hospital/view_hospital/'.$candidate->hospital_id) }}" style="color:#a02626;">{{ $candidate->hospital_name }}</a>@else{{ $candidate->hospital_name }}@endif</span></span>
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
                            <div class="chip-icon"><i class="fas fa-graduation-cap"></i></div>
                            <div><div class="chip-label">Exam Year</div>
                                <strong class="chip-val">{{ $candidate->exam_year ?? '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-id-badge"></i></div>
                            <div><div class="chip-label">Candidate No.</div>
                                <strong class="chip-val" style="font-size:.82rem;">{{ $candidate->candidate_id ?? '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-receipt"></i></div>
                            <div><div class="chip-label">Invoice Status</div>
                                <strong class="chip-val" style="font-size:.82rem;">{{ $candidate->invoice_status ?? '—' }}</strong></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="stat-chip">
                            <div class="chip-icon"><i class="fas fa-stethoscope"></i></div>
                            <div><div class="chip-label">Programme</div>
                                <strong class="chip-val" style="font-size:.75rem;">{{ $prog }}</strong></div>
                        </div>
                    </div>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs cp-tabs" id="cpTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#ctab-personal">Personal</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ctab-exam">Exam Info</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ctab-fees">Fees &amp; Payments</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ctab-admin">Admin</a></li>
                </ul>

                <div class="tab-content border border-top-0 p-3 bg-white" style="border-radius:0 0 6px 6px; min-height:300px;">

                    {{-- ── TAB: Personal ── --}}
                    <div class="tab-pane fade show active" id="ctab-personal">
                        <p class="sect-div">Identity</p>
                        <div class="field-row"><span class="field-lbl">First Name</span><span class="field-val">{{ $candidate->firstname ?? '—' }}</span></div>
                        @if(!empty($candidate->middlename))
                        <div class="field-row"><span class="field-lbl">Middle Name</span><span class="field-val">{{ $candidate->middlename }}</span></div>
                        @endif
                        <div class="field-row"><span class="field-lbl">Last Name</span><span class="field-val">{{ $candidate->lastname ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Gender</span>
                            <span class="field-val">
                                @if(($candidate->gender ?? '') === 'Female')
                                    <span class="badge badge-warning" style="color:#333;">Female</span>
                                @elseif(($candidate->gender ?? '') === 'Male')
                                    <span class="badge badge-info">Male</span>
                                @else —
                                @endif
                            </span>
                        </div>

                        <p class="sect-div">Contact</p>
                        <div class="field-row"><span class="field-lbl">Personal Email</span>
                            <span class="field-val">
                                @if(!empty($candidate->personal_email))
                                    <a href="mailto:{{ $candidate->personal_email }}">{{ $candidate->personal_email }}</a>
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">SFS Username</span><span class="field-val"><code>{{ $candidate->user_email ?? '—' }}</code></span></div>
                        <div class="field-row"><span class="field-lbl">Country</span><span class="field-val">@if($candidate->country_id ?? null)<a href="{{ url('admin/countries/view/'.$candidate->country_id) }}" style="color:#a02626;">{{ $candidate->country_name }}</a>@else{{ $candidate->country_name ?? '—' }}@endif</span></div>
                        <div class="field-row"><span class="field-lbl">Hospital</span><span class="field-val">@if($candidate->hospital_id ?? null)<a href="{{ url('admin/hospital/view_hospital/'.$candidate->hospital_id) }}" style="color:#a02626;">{{ $candidate->hospital_name }}</a>@else{{ $candidate->hospital_name ?? '—' }}@endif</span></div>
                    </div>

                    {{-- ── TAB: Exam Info ── --}}
                    <div class="tab-pane fade" id="ctab-exam">
                        <p class="sect-div">Identification</p>
                        <div class="field-row"><span class="field-lbl">Entry Number (PEN)</span><span class="field-val">{{ $candidate->entry_number ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Candidate Number</span>
                            <span class="field-val">
                                @if(!empty($candidate->candidate_id))
                                    <strong>{{ $candidate->candidate_id }}</strong>
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Exam Number</span><span class="field-val">{{ $candidate->exam_number ?? '—' }}</span></div>

                        <p class="sect-div">Programme &amp; Year</p>
                        <div class="field-row"><span class="field-lbl">Programme</span><span class="field-val">@if($candidate->programme_id ?? null)<a href="{{ url('admin/programmes/view/'.$candidate->programme_id) }}" style="color:#a02626;">{{ $candidate->programme_name }}</a>@else{{ $candidate->programme_name ?? '—' }}@endif</span></div>
                        <div class="field-row"><span class="field-lbl">Admission Year</span><span class="field-val">{{ $candidate->admission_year ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Exam Year</span><span class="field-val">{{ $candidate->exam_year ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Exam Group</span><span class="field-val">{{ $candidate->group_name ?? '—' }}</span></div>

                        <p class="sect-div">Exam Status</p>
                        <div class="field-row"><span class="field-lbl">Repeat Paper 1</span>
                            <span class="field-val">
                                @if(($candidate->repeat_paper_one ?? 'No') === 'Yes')
                                    <span class="badge badge-warning" style="color:#333;">Yes</span>
                                @else <span class="text-muted">No</span>
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Repeat Paper 2</span>
                            <span class="field-val">
                                @if(($candidate->repeat_paper_two ?? 'No') === 'Yes')
                                    <span class="badge badge-warning" style="color:#333;">Yes</span>
                                @else <span class="text-muted">No</span>
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">MMed Qualified</span>
                            <span class="field-val">
                                @if(($candidate->mmed ?? 'No') === 'Yes')
                                    <span class="badge badge-success">Yes</span>
                                @else <span class="text-muted">No</span>
                                @endif
                            </span>
                        </div>
                        @if(!empty($candidate->remarks))
                        <div class="field-row"><span class="field-lbl">Remarks</span><span class="field-val">{{ $candidate->remarks }}</span></div>
                        @endif
                    </div>

                    {{-- ── TAB: Fees & Payments ── --}}
                    <div class="tab-pane fade" id="ctab-fees">

                        {{-- Exam Fee (from candidates table) --}}
                        <p class="sect-div">Examination Fee</p>
                        <div class="field-row"><span class="field-lbl">Invoice Number</span><span class="field-val">{{ $candidate->invoice_number ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Invoice Date</span>
                            <span class="field-val">
                                @if(!empty($candidate->invoice_date))
                                    {{ \Carbon\Carbon::parse($candidate->invoice_date)->format('d M Y') }}
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Invoice Amount</span>
                            <span class="field-val">
                                @if($invAmountFormatted) <strong>{{ $invAmountFormatted }}</strong>
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Invoice Status</span>
                            <span class="field-val">
                                @php $cs = $candidate->invoice_status ?? 'Pending'; @endphp
                                <span class="badge" style="background:{{ $cs==='Sent' ? '#cce5ff' : '#fff3cd' }}; color:{{ $cs==='Sent' ? '#004085' : '#856404' }};">{{ $cs }}</span>
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Fee Paid</span>
                            <span class="field-val">
                                @if($feePaid === 'Yes')
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>No</span>
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Amount Paid</span>
                            <span class="field-val">
                                @if($candAmountFormatted) <strong style="color:#2980b9;">{{ $candAmountFormatted }}</strong>
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Payment Date</span>
                            <span class="field-val">
                                @if(!empty($candidate->payment_date))
                                    {{ \Carbon\Carbon::parse($candidate->payment_date)->format('d M Y') }}
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Mode of Payment</span><span class="field-val">{{ $candidate->mode_of_payment ?? '—' }}</span></div>
                        @if($sponsor)
                        <div class="field-row"><span class="field-lbl">Sponsor</span><span class="field-val">{{ $sponsor }}</span></div>
                        @endif

                        {{-- Programme Entry Fee (from trainees table, if linked) --}}
                        <p class="sect-div mt-3">Programme Entry Fee</p>
                        @if(isset($linkedTrainee) && $linkedTrainee)
                            <div class="field-row"><span class="field-lbl">Invoice Number</span><span class="field-val">{{ $linkedTrainee->invoice_number ?? '—' }}</span></div>
                            <div class="field-row"><span class="field-lbl">Invoice Date</span><span class="field-val">{{ $linkedTrainee->invoice_date ?? '—' }}</span></div>
                            <div class="field-row"><span class="field-lbl">Invoice Status</span>
                                <span class="field-val">
                                    @php $ts = $linkedTrainee->invoice_status ?? 'Pending'; @endphp
                                    <span class="badge" style="background:{{ in_array($ts,['Sent','Paid']) ? '#d4edda' : '#fff3cd' }}; color:{{ in_array($ts,['Sent','Paid']) ? '#155724' : '#856404' }};">{{ $ts }}</span>
                                </span>
                            </div>
                            <div class="field-row"><span class="field-lbl">Amount Paid</span>
                                <span class="field-val">
                                    @if($traineeAmountFormatted) <strong style="color:#a02626;">{{ $traineeAmountFormatted }}</strong>
                                    @else —
                                    @endif
                                </span>
                            </div>
                            <div class="field-row"><span class="field-lbl">Mode of Payment</span><span class="field-val">{{ $linkedTrainee->mode_of_payment ?? '—' }}</span></div>
                            <div class="field-row"><span class="field-lbl">Payment Date</span><span class="field-val">{{ $linkedTrainee->payment_date ?? '—' }}</span></div>
                            @if(!empty($linkedTrainee->sponsor) && $linkedTrainee->sponsor !== 'null')
                            <div class="field-row"><span class="field-lbl">Sponsor</span><span class="field-val">{{ $linkedTrainee->sponsor }}</span></div>
                            @endif
                        @else
                            <div class="text-center py-3 text-muted" style="font-size:.83rem;">
                                <i class="fas fa-info-circle mr-1"></i>No trainee programme entry fee record linked to this candidate.
                            </div>
                        @endif
                    </div>

                    {{-- ── TAB: Admin ── --}}
                    <div class="tab-pane fade" id="ctab-admin">
                        <p class="sect-div">System</p>
                        <div class="field-row"><span class="field-lbl">SFS Username</span><span class="field-val"><code>{{ $candidate->user_email ?? '—' }}</code></span></div>
                        <div class="field-row"><span class="field-lbl">User ID</span><span class="field-val">{{ $candidate->user_id ?? '—' }}</span></div>
                        <div class="field-row"><span class="field-lbl">Candidate Record ID</span><span class="field-val">{{ $candidate->candidates_id ?? '—' }}</span></div>

                        @if(isset($linkedTrainee) && $linkedTrainee)
                        <p class="sect-div mt-3">Linked Trainee Record</p>
                        <div class="field-row"><span class="field-lbl">Trainee Record ID</span>
                            <span class="field-val">{{ $linkedTrainee->trainee_id ?? $linkedTrainee->id ?? '—' }}</span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Status</span>
                            <span class="field-val">{{ $linkedTrainee->status ?? '—' }}</span>
                        </div>
                        <div class="field-row"><span class="field-lbl">Admission Year</span>
                            <span class="field-val">{{ $linkedTrainee->admission_year ?? '—' }}</span>
                        </div>
                        @endif

                        <p class="sect-div mt-3">Quick Actions</p>
                        <div class="d-flex flex-wrap mt-2" style="gap:6px;">
                            <a href="{{ url('admin/associates/candidates/edit/' . ($candidate->candidates_id ?? 0)) }}"
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit mr-1"></i>Edit Profile
                            </a>
                            @if(isset($linkedTrainee) && $linkedTrainee)
                            <a href="{{ url('admin/associates/trainees/view/' . ($linkedTrainee->trainee_id ?? $linkedTrainee->id)) }}"
                               class="btn btn-sm" style="background:#f0d4d4; color:#721c24; border:1px solid #c0392b;">
                                <i class="fas fa-user-md mr-1"></i>View Trainee Profile
                            </a>
                            @endif
                            <a href="{{ url('admin/associates/candidates/list') }}"
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-list mr-1"></i>All Candidates
                            </a>
                        </div>
                    </div>

                </div>{{-- /.tab-content --}}
            </div>{{-- /.col right --}}
        </div>{{-- /.row --}}

        @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Candidate not found.</h5>
                <a href="{{ url('admin/associates/candidates/list') }}" class="btn btn-sm btn-secondary mt-2">Back to List</a>
            </div>
        </div>
        @endif

        </div>{{-- /.container-fluid --}}
    </section>
</div>{{-- /.content-wrapper --}}

@push('scripts')
<script>
$(document).ready(function () {
    // A URL hash (e.g. from the Fees page) wins over the last-viewed tab.
    if (location.hash) {
        $('#cpTabs a[href="' + location.hash + '"]').tab('show');
    } else {
        var saved = localStorage.getItem('adminCandidateViewTab');
        if (saved) { $('#cpTabs a[href="' + saved + '"]').tab('show'); }
    }
    $('#cpTabs a').on('shown.bs.tab', function (e) {
        localStorage.setItem('adminCandidateViewTab', $(e.target).attr('href'));
    });
});
</script>
@endpush

@endsection

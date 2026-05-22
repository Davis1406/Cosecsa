@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    {{-- ── Page Header ────────────────────────────────────────────────────────── --}}
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="mb-0">
                        <i class="fas fa-user-circle mr-2" style="color:#a02626;"></i>
                        My Profile
                    </h4>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ url('examiner/edit_info/' . $examiner->examin_id) }}"
                       class="btn btn-sm btn-warning">
                        <i class="fas fa-edit mr-1"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="col-md-12">@include('_message')</div>

    <section class="content">
        <div class="container-fluid">

            {{-- ── Profile Hero ────────────────────────────────────────────────── --}}
            <div class="card mb-4 overflow-hidden">
                <div class="profile-hero-banner d-flex align-items-center flex-wrap p-4" style="gap:1.5rem;">

                    {{-- Avatar --}}
                    <div class="flex-shrink-0">
                        <img src="{{ !empty($examiner->passport_image)
                                    ? asset('storage/' . $examiner->passport_image)
                                    : asset('/public/dist/img/user.png') }}"
                             alt="{{ $examiner->examiner_name }}"
                             class="profile-photo">
                    </div>

                    {{-- Name & badges --}}
                    <div class="flex-grow-1">
                        <h2 class="profile-name mb-1">{{ $examiner->examiner_name }}</h2>
                        <div class="mb-2" style="gap:.4rem;display:flex;flex-wrap:wrap;">
                            @php
                                $roleLabel = match((int)($examiner->role_id ?? 0)) {
                                    1 => 'Examiner',
                                    2 => 'Observer',
                                    default => 'None'
                                };
                            @endphp
                            <span class="badge badge-light text-dark">
                                <i class="fas fa-user-md mr-1"></i> {{ $roleLabel }}
                            </span>
                            @if(!empty($examiner->specialty))
                                <span class="badge badge-light text-dark">
                                    <i class="fas fa-stethoscope mr-1"></i>{{ $examiner->specialty }}
                                </span>
                            @endif
                            @if(!empty($examiner->examiner_designation))
                                <span class="badge badge-light text-dark">
                                    <i class="fas fa-gavel mr-1"></i>{{ $examiner->examiner_designation }}
                                </span>
                            @endif
                        </div>
                        <div class="profile-meta d-flex flex-wrap" style="gap:.75rem;">
                            @if(!empty($examiner->country_name))
                                <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $examiner->country_name }}</span>
                            @endif
                            @if(!empty($examiner->examiner_id))
                                <span><i class="fas fa-id-badge mr-1"></i>{{ $examiner->examiner_id }}</span>
                            @endif
                            <span><i class="fas fa-envelope mr-1"></i>{{ $examiner->email }}</span>
                        </div>
                    </div>

                    {{-- QR Code --}}
                    @if(isset($qrCode))
                    <div class="text-center d-none d-md-block">
                        <div class="qr-wrapper">{!! $qrCode !!}</div>
                        <small class="d-block mt-1" style="color:rgba(255,255,255,.7);font-size:.72rem;">Scan QR</small>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Info Cards Row ──────────────────────────────────────────────── --}}
            <div class="row">

                {{-- Personal Details --}}
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header section-header">
                            <i class="fas fa-user mr-2"></i> Personal Details
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0 info-table">
                                <tr>
                                    <th><i class="fas fa-envelope text-muted mr-1"></i> Email</th>
                                    <td>{{ $examiner->email }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-phone text-muted mr-1"></i> Mobile</th>
                                    <td>{{ $examiner->mobile ?? '—' ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-globe text-muted mr-1"></i> Country</th>
                                    <td>{{ $examiner->country_name ?? '—' ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-venus-mars text-muted mr-1"></i> Gender</th>
                                    <td>{{ $examiner->gender ?? '—' ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-id-card text-muted mr-1"></i> Examiner ID</th>
                                    <td>{{ $examiner->examiner_id ?? '—' ?: '—' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Current Assignment --}}
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header section-header">
                            <i class="fas fa-tasks mr-2"></i> Current Assignment
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0 info-table">
                                <tr>
                                    <th><i class="fas fa-users text-muted mr-1"></i> Group</th>
                                    <td>
                                        @if(isset($examiner->groups) && $examiner->groups->isNotEmpty())
                                            @foreach($examiner->groups as $group)
                                                <span class="badge badge-pill mb-1"
                                                      style="background:#a02626;color:#fff;">
                                                    {{ $group->group_name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-clock text-muted mr-1"></i> Shifts</th>
                                    <td>
                                        @if(isset($examiner->shifts) && $examiner->shifts->isNotEmpty())
                                            @foreach($examiner->shifts as $shift)
                                                <span class="badge badge-pill badge-info mb-1">
                                                    {{ App\Models\User::getShiftName($shift->shift) }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No shifts assigned</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-user-tie text-muted mr-1"></i> Role</th>
                                    <td>{{ $roleLabel }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-stethoscope text-muted mr-1"></i> Specialty</th>
                                    <td>{{ $examiner->specialty ?? '—' ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-microscope text-muted mr-1"></i> Sub-Specialty</th>
                                    <td>{{ $examiner->subspecialty ?? '—' ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-hospital text-muted mr-1"></i> Hospital</th>
                                    <td>{{ $examiner->hospital_name ?? '—' ?: '—' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Documents & Actions --}}
                <div class="col-md-4 col-sm-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header section-header">
                            <i class="fas fa-folder-open mr-2"></i> Documents & Actions
                        </div>
                        <div class="card-body">

                            {{-- CV --}}
                            <small class="text-muted font-weight-bold text-uppercase"
                                   style="letter-spacing:.04em;">
                                <i class="fas fa-file-pdf mr-1"></i> Curriculum Vitae
                            </small>
                            <div class="mt-1 mb-3">
                                @if(!empty($examiner->curriculum_vitae))
                                    @php $cvName = basename($examiner->curriculum_vitae); @endphp
                                    <a href="{{ asset('storage/' . $examiner->curriculum_vitae) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-danger btn-block">
                                        <i class="fas fa-download mr-1"></i> {{ $cvName }}
                                    </a>
                                @else
                                    <span class="text-muted d-block mb-1" style="font-size:.85rem;">No CV uploaded</span>
                                @endif
                            </div>

                            <hr class="my-3">

                            {{-- History --}}
                            <small class="text-muted font-weight-bold text-uppercase"
                                   style="letter-spacing:.04em;">
                                <i class="fas fa-history mr-1"></i> Participation History
                            </small>
                            <div class="mt-1 mb-3">
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary btn-block"
                                        data-toggle="modal" data-target="#examinerHistoryModal">
                                    <i class="fas fa-calendar-alt mr-1"></i> View History & Availability
                                </button>
                            </div>

                            <hr class="my-3">

                            {{-- ID Badge --}}
                            <small class="text-muted font-weight-bold text-uppercase"
                                   style="letter-spacing:.04em;">
                                <i class="fas fa-id-card mr-1"></i> ID Badge
                            </small>
                            <div class="mt-1">
                                <button type="button"
                                        class="btn btn-sm btn-block text-white"
                                        style="background:#a02626;border-color:#a02626;"
                                        data-toggle="modal" data-target="#idBadgeModal">
                                    <i class="fas fa-id-card mr-1"></i> View ID Badge
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /row --}}

            {{-- ── Participation Summary ───────────────────────────────────────── --}}
            @php
                $hasHistory = !empty($examiner->history) || !empty($exYears);
                $mcsStatYears = [];
                $fcsStatYears = [];
                foreach ($yearProgrammes as $yr => $progs) {
                    foreach ($progs as $p) {
                        if (strtoupper($p) === 'MCS') { $mcsStatYears[] = $yr; }
                        elseif (stripos($p, 'FCS') !== false) { $fcsStatYears[] = $yr; }
                    }
                }
                sort($mcsStatYears); sort($fcsStatYears);
                $hasMCSHistory = !empty($mcsStatYears);
                $hasFCSHistory = !empty($fcsStatYears);
                $examinerRoleLabel = ($examiner->role_id == 1) ? 'Examiner' : 'Observer';
            @endphp

            @if($hasHistory || !empty($exYears))
            <div class="card mb-4">
                <div class="card-header section-header">
                    <i class="fas fa-history mr-2"></i> Participation Summary
                </div>
                <div class="card-body pb-2">

                    {{-- Quick-stat row --}}
                    <div class="row text-center mb-3">
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-icon mx-auto {{ $hasMCSHistory ? 'bg-success' : 'bg-light-muted' }}">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <small class="d-block mt-2 text-muted">MCS</small>
                            @if($hasMCSHistory)
                                <div style="font-size:.75rem;font-weight:600;line-height:1.4;margin-top:2px;">
                                    {{ implode(', ', $mcsStatYears) }}
                                </div>
                            @else
                                <strong class="text-muted">N/A</strong>
                            @endif
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-icon mx-auto {{ $hasFCSHistory ? 'bg-info' : 'bg-light-muted' }}">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <small class="d-block mt-2 text-muted">FCS</small>
                            @if($hasFCSHistory)
                                <div style="font-size:.75rem;font-weight:600;line-height:1.4;margin-top:2px;">
                                    {{ implode(', ', $fcsStatYears) }}
                                </div>
                            @else
                                <strong class="text-muted">N/A</strong>
                            @endif
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-icon mx-auto bg-light-muted">
                                <i class="fas fa-hospital"></i>
                            </div>
                            <small class="d-block mt-2 text-muted">Hospital</small>
                            <strong class="d-block text-truncate" style="font-size:.8rem;max-width:140px;margin:0 auto;">
                                {{ ($examiner->hospital_name ?? '') ?: '—' }}
                            </strong>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-icon mx-auto {{ count($exYears) > 0 ? 'bg-danger' : 'bg-light-muted' }}">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <small class="d-block mt-2 text-muted">Exam Years</small>
                            <strong>{{ count($exYears) > 0 ? count($exYears) . ' year(s)' : '—' }}</strong>
                        </div>
                    </div>

                    {{-- Year-by-year history --}}
                    @if(!empty($exYears))
                    <div style="border-top:1px solid #f0f0f0;padding-top:1rem;">
                        <p class="mb-2" style="font-size:.7rem;font-weight:700;text-transform:uppercase;
                                               letter-spacing:.08em;color:#a02626;">
                            <i class="fas fa-stream mr-1"></i> Examination History by Year
                        </p>
                        <table class="table table-sm table-borderless mb-0" style="font-size:.87rem;width:auto;">
                            <thead>
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <th style="width:70px;color:#6c757d;font-weight:600;padding-left:0;">Year</th>
                                    <th style="color:#6c757d;font-weight:600;white-space:nowrap;">Programme</th>
                                    <th style="color:#6c757d;font-weight:600;white-space:nowrap;padding-left:1rem;">Role</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach(array_reverse((array)$exYears) as $yr)
                                @php
                                    $progs    = array_values(array_unique($yearProgrammes[(string)$yr] ?? []));
                                    $rowCount = max(1, count($progs));
                                @endphp
                                @if(empty($progs))
                                <tr style="border-bottom:1px solid #f5f5f5;">
                                    <td style="padding-left:0;font-weight:700;color:#333;vertical-align:middle;width:60px;">{{ $yr }}</td>
                                    <td><span class="text-muted" style="font-style:italic;">No record</span></td>
                                    <td style="padding-left:1rem;"></td>
                                </tr>
                                @else
                                @foreach($progs as $pi => $prog)
                                @php $role = $yearRoles[(string)$yr][$prog] ?? $examinerRoleLabel; @endphp
                                <tr style="border-bottom:{{ $pi === count($progs)-1 ? '2px solid #e8e8e8' : '1px solid #f5f5f5' }};">
                                    @if($pi === 0)
                                    <td rowspan="{{ $rowCount }}"
                                        style="padding-left:0;font-weight:700;color:#333;vertical-align:middle;
                                               width:60px;border-right:2px solid #f0f0f0;padding-right:.75rem;">
                                        {{ $yr }}
                                    </td>
                                    @endif
                                    <td style="vertical-align:middle;padding-left:.75rem;">{{ $prog }}</td>
                                    <td style="vertical-align:middle;padding-left:1rem;white-space:nowrap;">
                                        <span class="badge badge-pill {{ $role === 'Examiner' ? 'badge-success' : 'badge-warning' }}"
                                              style="font-size:.72rem;">
                                            {{ $role }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                </div>
            </div>
            @endif

        </div>
    </section>
</div>
</div>

{{-- ══ ID Badge Modal ══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="idBadgeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:400px;">
        <div class="modal-content">
            <div class="modal-body p-3">
                <div class="id-badge-template mx-auto text-center d-flex flex-column justify-content-between"
                     style="width:350px;height:550px;border:2px solid #a02626;border-radius:15px;
                            padding:20px 20px 30px;position:relative;background:#fff;">
                    <div>
                        <div class="mb-3">
                            <img src="{{ asset('/public/dist/img/cosecsa_Logo.png') }}"
                                 alt="COSECSA Logo" style="width:100px;height:auto;">
                        </div>
                        <div class="mb-3">
                            <h5 style="color:#a02626;font-weight:700;margin-bottom:2px;">College of Surgeons of</h5>
                            <h5 style="color:#a02626;font-weight:700;margin-bottom:2px;">East Central and Southern Africa</h5>
                            <h4 style="color:#a02626;font-weight:700;margin-bottom:4px;">COSECSA</h4>
                            <h6 style="color:#a02626;font-weight:700;">EXAMINER IDENTIFICATION</h6>
                        </div>
                        <div class="mb-3">
                            <img src="{{ !empty($examiner->passport_image)
                                        ? asset('storage/' . $examiner->passport_image)
                                        : asset('/public/dist/img/user.png') }}"
                                 alt="{{ $examiner->examiner_name }}"
                                 class="rounded-circle"
                                 style="width:120px;height:120px;object-fit:cover;border:2px solid #a02626;">
                        </div>
                        <div class="text-center mb-2">
                            <h5 style="color:#a02626;font-weight:700;margin-bottom:0;">
                                {{ $examiner->examiner_name }}
                            </h5>
                            <p style="margin:0 0 2px;">Examiner — {{ $examiner->examiner_id }}</p>
                            <p style="margin:0 0 2px;">{{ $examiner->specialty ?? '' }}</p>
                        </div>
                    </div>
                    <div class="text-center">
                        @if(isset($qrCode))
                            <div style="width:70px;height:70px;margin:0 auto 8px;background:#fff;
                                        padding:3px;border-radius:4px;border:1px solid #eee;">
                                {!! $qrCode !!}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-sm btn-outline-secondary" onclick="printBadge()">
                        <i class="fas fa-print mr-1"></i> Print / Download
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ History Modal ═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="examinerHistoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header"
                 style="background:linear-gradient(135deg,#a02626 0%,#d63031 100%);color:#fff;">
                <h5 class="modal-title">
                    <i class="fas fa-history mr-2"></i> Participation History
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                @php
                    $hasHistory = !empty($examiner->history) && $examiner->history !== null;

                    // examination_years: set directly on $examiner by getExaminers() from
                    // $history->examination_years (already cast to array). Handle both cases.
                    $raw = $examiner->examination_years
                        ?? ($hasHistory ? $examiner->history->examination_years : null);
                    $modalYears = [];
                    if (!empty($raw)) {
                        if (is_array($raw)) {
                            $modalYears = $raw;
                        } else {
                            $d = json_decode($raw, true);
                            if (is_string($d)) { $d = json_decode($d, true); }
                            $modalYears = is_array($d) ? $d : [];
                        }
                    }

                    // exam_availability: same cast pattern
                    $selectedAvailability = [];
                    if ($hasHistory && !empty($examiner->history->exam_availability)) {
                        if (is_array($examiner->history->exam_availability)) {
                            $selectedAvailability = $examiner->history->exam_availability;
                        } else {
                            $av = json_decode($examiner->history->exam_availability, true);
                            if (is_string($av)) { $av = json_decode($av, true); }
                            $selectedAvailability = is_array($av) ? $av : [];
                        }
                    }
                    $hasMCS       = in_array('MCS', $selectedAvailability);
                    $hasFCS       = in_array('FCS', $selectedAvailability);
                    $notAvailable = in_array('Not Available', $selectedAvailability);
                @endphp

                <div class="row">

                    {{-- Examination Years --}}
                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-calendar-alt text-danger mr-1"></i> Examination Years
                                </h6>
                            </div>
                            <div class="card-body">
                                @if(!empty($modalYears))
                                    @foreach($modalYears as $yr)
                                        <span class="badge badge-pill mr-2 mb-2 px-3 py-2"
                                              style="background:#a02626;color:#fff;font-size:.85rem;">
                                            {{ $yr }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No examination years recorded</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Role & Institution --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-hospital text-warning mr-1"></i> Role & Institution
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <small class="text-muted">Participation Type</small>
                                    <div class="font-weight-bold">
                                        <i class="fas fa-user-tie text-primary mr-1"></i> {{ $roleLabel }}
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Hospital Type</small>
                                    <div class="font-weight-bold">
                                        {{ ($examiner->hospital_type ?? '') ?: 'Not specified' }}
                                    </div>
                                </div>
                                <div>
                                    <small class="text-muted">Hospital Name</small>
                                    <div class="font-weight-bold">
                                        {{ ($examiner->hospital_name ?? '') ?: 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Current Availability --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-calendar-check text-success mr-1"></i> Exam Availability
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($notAvailable)
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-times-circle text-danger mr-2"></i>
                                        <span class="font-weight-bold text-danger">Not Available</span>
                                    </div>
                                @elseif($hasMCS || $hasFCS)
                                    @if($hasMCS)
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                            <span class="font-weight-bold">MCS</span>
                                        </div>
                                    @endif
                                    @if($hasFCS)
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                            <span class="font-weight-bold">FCS</span>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">No availability recorded yet</span>
                                @endif

                                @if(isset($examiner->shift_id) && $examiner->shift_id)
                                    <hr class="my-2">
                                    <div>
                                        <small class="text-muted">MCS Shift Preference</small>
                                        <div class="font-weight-bold">
                                            <i class="fas fa-clock text-warning mr-1"></i>
                                            {{ App\Models\User::getShiftName($examiner->shift_id) }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer" style="border-top:none;padding:.6rem 1rem;">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ── Force light mode ─────────────────────────────────────────────────────── */
.content-wrapper,
.content-wrapper *:not(.profile-hero-banner):not(.profile-hero-banner *) {
    color-scheme: light;
}
.card       { background-color:#fff !important; color:#212529 !important; }
.card-body  { background-color:#fff !important; color:#212529 !important; }
.section-header {
    background:#f8f0f0 !important; color:#a02626 !important;
    border-bottom-color:#f0dada !important;
    font-weight:600; font-size:.875rem;
}
.info-table th { background:#fafafa !important; color:#555 !important; font-weight:600; width:38%; }
.info-table td { background:#fff !important; color:#333 !important; }
.table th, .table td { color:#212529 !important; border-color:#dee2e6 !important; }
.text-muted { color:#6c757d !important; }
.text-primary { color:#a02626 !important; }
.badge-primary { background-color:#a02626 !important; }

/* ── Profile Hero ─────────────────────────────────────────────────────────── */
.profile-hero-banner {
    background: linear-gradient(135deg, #a02626 0%, #6e1a1a 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
}
.profile-hero-banner::before {
    content:''; position:absolute;
    top:-80px; right:-80px;
    width:240px; height:240px;
    background:rgba(255,255,255,.06);
    border-radius:50%; pointer-events:none;
}
.profile-hero-banner::after {
    content:''; position:absolute;
    bottom:-50px; left:-30px;
    width:160px; height:160px;
    background:rgba(255,255,255,.04);
    border-radius:50%; pointer-events:none;
}
.profile-photo {
    width:100px; height:100px;
    object-fit:cover;
    border-radius:50%;
    border:3px solid rgba(255,255,255,.55);
    box-shadow:0 4px 16px rgba(0,0,0,.35);
}
.profile-name {
    font-size:1.5rem; font-weight:700;
    color:#fff; margin:0;
}
.profile-meta {
    font-size:.875rem;
    color:rgba(255,255,255,.82);
}
.qr-wrapper {
    background:#fff; padding:6px;
    border-radius:6px; display:inline-block;
}
.qr-wrapper svg { display:block; }

/* ── Modal overrides ─────────────────────────────────────────────────────── */
.modal-header { border-bottom:none; }
.modal-footer { border-top:none; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
window.printBadge = function () {
    var element = document.querySelector('.id-badge-template');
    html2canvas(element, { scale:2, backgroundColor:'#ffffff', logging:false, useCORS:true })
        .then(function(canvas) {
            var margin = 20;
            var nc = document.createElement('canvas');
            nc.width  = canvas.width  + margin * 2;
            nc.height = canvas.height + margin * 2;
            var ctx = nc.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, nc.width, nc.height);
            ctx.drawImage(canvas, margin, margin);
            var link = document.createElement('a');
            link.href     = nc.toDataURL('image/png');
            link.download = 'examiner-badge-' + new Date().toISOString().slice(0, 10) + '.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
};
</script>
@endpush

@endsection

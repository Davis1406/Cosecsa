@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    {{-- ── Page Header ────────────────────────────────────────────────────────── --}}
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <a href="{{ $backUrl ?? url('admin/exams/examiners') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Examiners
                    </a>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ url('admin/exams/edit_examiner/' . $examiner->examin_id) }}"
                       class="btn btn-sm btn-warning mr-1">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <button type="button" class="btn btn-sm btn-danger"
                            data-toggle="modal" data-target="#idBadgeModal">
                        <i class="fas fa-id-card mr-1"></i> ID Badge
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            {{-- ── Profile Hero ────────────────────────────────────────────────── --}}
            <div class="card mb-4 overflow-hidden">
                <div class="profile-hero-banner d-flex align-items-center flex-wrap p-4" style="gap:1.5rem;">
                    {{-- Avatar --}}
                    <div class="flex-shrink-0">
                        <img src="{{ !empty($examiner->passport_image)
                                    ? asset('storage/app/public/' . $examiner->passport_image)
                                    : asset('/public/dist/img/user.png') }}"
                             alt="{{ $examiner->examiner_name }}"
                             class="profile-photo">
                    </div>

                    {{-- Details --}}
                    <div class="flex-grow-1">
                        <h2 class="profile-name mb-1">{{ $examiner->examiner_name }}</h2>
                        <div class="mb-2" style="gap:.4rem;display:flex;flex-wrap:wrap;">
                            <span class="badge badge-light text-dark">
                                <i class="fas fa-user-md mr-1"></i>
                                {{ $examiner->role_id == 1 ? 'Examiner' : 'Observer' }}
                            </span>
                            @if($examiner->specialty)
                                <span class="badge badge-light text-dark">
                                    <i class="fas fa-stethoscope mr-1"></i>{{ $examiner->specialty }}
                                </span>
                            @endif
                        </div>
                        <div class="profile-meta d-flex flex-wrap" style="gap:.75rem;">
                            @if($examiner->country_name)
                                <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $examiner->country_name }}</span>
                            @endif
                            @if($examiner->examiner_id)
                                <span><i class="fas fa-id-badge mr-1"></i>{{ $examiner->examiner_id }}</span>
                            @endif
                            <span><i class="fas fa-envelope mr-1"></i>{{ $examiner->email }}</span>
                        </div>
                    </div>

                    {{-- QR --}}
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
                                    <td>{{ $examiner->mobile ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-globe text-muted mr-1"></i> Country</th>
                                    <td>{{ $examiner->country_name ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-venus-mars text-muted mr-1"></i> Gender</th>
                                    <td>{{ $examiner->gender ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-id-card text-muted mr-1"></i> Examiner ID</th>
                                    <td>{{ $examiner->examiner_id ?: '—' }}</td>
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
                                    <th><i class="fas fa-users text-muted mr-1"></i> Groups</th>
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
                                    <td>{{ $examiner->role_id == 1 ? 'Examiner' : 'Observer' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-stethoscope text-muted mr-1"></i> Specialty</th>
                                    <td>{{ $examiner->specialty ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-microscope text-muted mr-1"></i> Sub-Specialty</th>
                                    <td>{{ $examiner->subspecialty ?: '—' }}</td>
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
                                @if($examiner->curriculum_vitae)
                                    @php $cvName = basename($examiner->curriculum_vitae); @endphp
                                    <a href="{{ asset('storage/app/public/' . $examiner->curriculum_vitae) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-danger btn-block">
                                        <i class="fas fa-download mr-1"></i> {{ $cvName }}
                                    </a>
                                @else
                                    <span class="text-muted">No CV uploaded</span>
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
                                    <i class="fas fa-id-card mr-1"></i> Generate ID Badge
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Participation Summary (only if history record exists) ──────── --}}
            @php
                $hasHistory = !is_null($examiner->history);

                // Safely decode examination_years — guard against non-array JSON (string/int)
                $exYears = [];
                if ($hasHistory && !empty($examiner->examination_years)) {
                    $decoded = json_decode($examiner->examination_years, true);
                    $exYears = is_array($decoded) ? $decoded : [];
                }
            @endphp

            @if($hasHistory)
            <div class="card mb-4">
                <div class="card-header section-header">
                    <i class="fas fa-history mr-2"></i> Participation Summary
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-icon mx-auto {{ ($examiner->virtual_mcs_participated ?? '') == 'Yes' ? 'bg-success' : 'bg-light-muted' }}">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <small class="d-block mt-2 text-muted">Virtual MCS</small>
                            <strong>{{ ($examiner->virtual_mcs_participated ?? '') == 'Yes' ? 'Participated' : 'N/A' }}</strong>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-icon mx-auto {{ ($examiner->fcs_participated ?? '') == 'Yes' ? 'bg-info' : 'bg-light-muted' }}">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <small class="d-block mt-2 text-muted">FCS</small>
                            <strong>{{ ($examiner->fcs_participated ?? '') == 'Yes' ? 'Participated' : 'N/A' }}</strong>
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
                            <div class="stat-icon mx-auto bg-light-muted">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <small class="d-block mt-2 text-muted">Exam Years</small>
                            <strong>{{ count($exYears) > 0 ? count($exYears) . ' year(s)' : '—' }}</strong>
                        </div>
                    </div>

                    @if(!empty($exYears))
                    <div class="text-center mt-1">
                        @foreach($exYears as $yr)
                            <span class="badge badge-secondary mr-1 mb-1"
                                  style="font-size:.78rem;padding:4px 10px;">{{ $yr }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </section>
</div>
</div>
@endsection

{{-- ══ ID Badge Modal ═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="idBadgeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-body p-3">
                <div class="id-badge-template mx-auto text-center d-flex flex-column justify-content-between"
                     style="width:350px;height:550px;border:2px solid #a02626;border-radius:15px;
                            padding:20px 20px 30px;position:relative;background:#fff;">
                    <div>
                        <div class="mb-3">
                            <img src="{{ asset('/public/dist/img/Cosecsa_Logo.png') }}"
                                 alt="COSECSA Logo" style="width:100px;height:auto;">
                        </div>
                        <div class="mb-3">
                            <h5 style="color:#a02626;font-weight:bold;margin-bottom:2px;">College of Surgeons of</h5>
                            <h5 style="color:#a02626;font-weight:bold;margin-bottom:2px;">East Central and Southern Africa</h5>
                            <h4 style="color:#a02626;font-weight:bold;margin-bottom:4px;">COSECSA</h4>
                            <h6 style="color:#a02626;font-weight:bold;">EXAMINER IDENTIFICATION</h6>
                        </div>
                        <div class="mb-3">
                            <img src="{{ !empty($examiner->passport_image)
                                        ? asset('storage/app/public/'.$examiner->passport_image)
                                        : asset('/public/dist/img/user.png') }}"
                                 alt="{{ $examiner->examiner_name }}"
                                 class="rounded-circle"
                                 style="width:120px;height:120px;object-fit:cover;border:2px solid #a02626;">
                        </div>
                        <div class="mb-2">
                            <h5 style="color:#a02626;font-weight:bold;margin-bottom:0;">
                                {{ $examiner->examiner_name }}
                            </h5>
                            <p style="margin:0 0 2px;">Examiner — {{ $examiner->examiner_id ?? 'N/A' }}</p>
                            <p style="margin:0 0 2px;">{{ $examiner->specialty ?? 'General' }}</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div style="width:70px;height:70px;margin:0 auto 8px;background:#fff;padding:3px;border-radius:4px;border:1px solid #eee;">
                            @if(isset($qrCode))
                                {!! $qrCode !!}
                            @else
                                <div style="width:64px;height:64px;background:#f0f0f0;border:1px solid #ccc;"></div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-center mt-2">
                    <button class="btn btn-sm btn-outline-secondary" onclick="printBadge()">
                        <i class="fas fa-print mr-1"></i> Print / Download
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ Examiner History Modal ════════════════════════════════════════════════ --}}
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
                    $hasHistory = !is_null($examiner->history);

                    // Safely decode examination_years
                    $modalYears = [];
                    if ($hasHistory && !empty($examiner->examination_years)) {
                        $d = json_decode($examiner->examination_years, true);
                        $modalYears = is_array($d) ? $d : [];
                    }

                    // Safely decode exam_availability
                    $selectedAvailability = [];
                    if ($hasHistory && !empty($examiner->history->exam_availability)) {
                        $av = json_decode($examiner->history->exam_availability, true);
                        $selectedAvailability = is_array($av) ? $av : [];
                    }
                    $hasMCS       = in_array('MCS', $selectedAvailability);
                    $hasFCS       = in_array('FCS', $selectedAvailability);
                    $notAvailable = in_array('Not Available', $selectedAvailability);
                @endphp

                <div class="row">

                    {{-- Participation Overview --}}
                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-check text-primary mr-1"></i> Participation Overview
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="icon-circle bg-success text-white mr-3">
                                                <i class="fas fa-laptop"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">Virtual MCS Participation</small>
                                                <div class="font-weight-bold">
                                                    @if($hasHistory && ($examiner->virtual_mcs_participated ?? '') == 'Yes')
                                                        <span class="badge badge-success">Yes</span>
                                                    @else
                                                        <span class="badge badge-secondary">No</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="icon-circle bg-info text-white mr-3">
                                                <i class="fas fa-stethoscope"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">FCS Participation</small>
                                                <div class="font-weight-bold">
                                                    @if($hasHistory && ($examiner->fcs_participated ?? '') == 'Yes')
                                                        <span class="badge badge-info">Yes</span>
                                                    @else
                                                        <span class="badge badge-secondary">No</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                        <i class="fas fa-user-tie text-primary mr-1"></i>
                                        {{ $examiner->role_id == 1 ? 'Examiner' : 'Observer' }}
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Hospital Type</small>
                                    <div class="font-weight-bold">
                                        {{ $hasHistory ? (($examiner->hospital_type ?? '') ?: 'Not specified') : '—' }}
                                    </div>
                                </div>
                                <div>
                                    <small class="text-muted">Hospital Name</small>
                                    <div class="font-weight-bold">
                                        {{ $hasHistory ? (($examiner->hospital_name ?? '') ?: 'Not specified') : '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Examination Years --}}
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-calendar-alt text-danger mr-1"></i> Examination Years
                                </h6>
                            </div>
                            <div class="card-body">
                                @if(!empty($modalYears))
                                    @foreach($modalYears as $yr)
                                        <span class="badge badge-secondary mr-1 mb-1 px-3 py-2">{{ $yr }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No examination years recorded</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Exam Availability --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-calendar-check text-success mr-1"></i> Exam Availability
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        @if($notAvailable)
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-times-circle text-danger mr-2"></i>
                                                <span class="font-weight-bold text-danger">Not Available</span>
                                            </div>
                                        @elseif($hasMCS || $hasFCS)
                                            @if($hasMCS)
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                    <span class="font-weight-bold">MCS (12–13 Nov)</span>
                                                </div>
                                            @endif
                                            @if($hasFCS)
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                    <span class="font-weight-bold">FCS (1–2 December)</span>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted">No availability recorded</span>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">MCS Shift Preference</small>
                                        <div class="font-weight-bold">
                                            <i class="fas fa-clock text-warning mr-1"></i>
                                            @if(isset($examiner->shift_id) && $examiner->shift_id)
                                                {{ App\Models\User::getShiftName($examiner->shift_id) }}
                                            @else
                                                Not specified
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ── Profile Hero ─────────────────────────────────────────────────────────── */
.profile-hero-banner {
    background: linear-gradient(135deg, #a02626 0%, #6e1a1a 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
}
.profile-hero-banner::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 240px; height: 240px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
    pointer-events: none;
}
.profile-hero-banner::after {
    content: '';
    position: absolute;
    bottom: -50px; left: -30px;
    width: 160px; height: 160px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
    pointer-events: none;
}
.profile-photo {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,.55);
    box-shadow: 0 4px 16px rgba(0,0,0,.35);
}
.profile-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
}
.profile-meta {
    font-size: .875rem;
    color: rgba(255,255,255,.82);
}
.qr-wrapper {
    background: #fff;
    padding: 6px;
    border-radius: 6px;
    display: inline-block;
}
.qr-wrapper svg { display: block; }

/* ── Section header ───────────────────────────────────────────────────────── */
.section-header {
    background: #f8f0f0;
    border-bottom: 1px solid #f0dada;
    color: #a02626;
    font-weight: 600;
    font-size: .875rem;
}

/* ── Info table ───────────────────────────────────────────────────────────── */
.info-table th {
    width: 44%;
    font-weight: 600;
    color: #555;
    font-size: .78rem;
    padding: 9px 12px;
    white-space: nowrap;
    background: #fafafa;
    vertical-align: middle;
}
.info-table td {
    font-size: .85rem;
    padding: 9px 12px;
    color: #333;
    vertical-align: middle;
}

/* ── Stat boxes (participation summary) ───────────────────────────────────── */
.stat-icon {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #fff;
}
.stat-icon.bg-light-muted {
    background: #e9ecef;
    color: #adb5bd;
}

/* ── History modal icon circles ──────────────────────────────────────────── */
.icon-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

/* ── ID Badge print ──────────────────────────────────────────────────────── */
.id-badge-template {
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
}
@media print {
    body { margin: 20px !important; background: white !important; }
    .id-badge-template {
        width: 100% !important;
        height: auto !important;
        margin: 0 auto !important;
        border: 2px solid #a02626 !important;
        box-shadow: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
window.printBadge = function () {
    var element = document.querySelector('.id-badge-template');
    html2canvas(element, { scale: 2, backgroundColor: '#ffffff', useCORS: true, logging: false })
        .then(function (canvas) {
            var margin = 20;
            var nc = document.createElement('canvas');
            nc.width  = canvas.width  + margin * 2;
            nc.height = canvas.height + margin * 2;
            var ctx = nc.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, nc.width, nc.height);
            ctx.drawImage(canvas, margin, margin);
            var link = document.createElement('a');
            link.href = nc.toDataURL('image/png');
            link.download = 'id-badge-{{ Str::slug($examiner->examiner_name) }}.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
};
</script>
@endpush

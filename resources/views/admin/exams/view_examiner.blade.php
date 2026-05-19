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
                                    ? asset('storage/' . $examiner->passport_image)
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
                                    <a href="{{ asset('storage/' . $examiner->curriculum_vitae) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-danger btn-block">
                                        <i class="fas fa-download mr-1"></i> {{ $cvName }}
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary btn-block mt-1"
                                            data-toggle="modal" data-target="#uploadCvModal">
                                        <i class="fas fa-sync-alt mr-1"></i> Replace CV
                                    </button>
                                @else
                                    <span class="text-muted d-block mb-1">No CV uploaded</span>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary btn-block"
                                            data-toggle="modal" data-target="#uploadCvModal">
                                        <i class="fas fa-upload mr-1"></i> Upload CV
                                    </button>
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

                // examination_years is double-encoded in DB (json_encode called on save
                // despite the Eloquent array cast, so raw value is a JSON string wrapping
                // another JSON array). Decode twice to get the actual array.
                $exYears = [];
                if ($hasHistory && !empty($examiner->examination_years)) {
                    $d = json_decode($examiner->examination_years, true);
                    if (is_string($d)) { $d = json_decode($d, true); }
                    $exYears = is_array($d) ? $d : [];
                }
            @endphp

            @if($hasHistory)
            @php
                // Derive MCS / FCS participation from yearProgrammes (authoritative source)
                // rather than the old boolean fcs_participated / virtual_mcs_participated fields.
                $hasMCSHistory = collect($yearProgrammes)->flatten()
                    ->contains(fn($p) => str_contains(strtoupper($p), 'MCS'));
                $hasFCSHistory = collect($yearProgrammes)->flatten()
                    ->contains(fn($p) => str_contains(strtoupper($p), 'FCS'));
            @endphp
            <div class="card mb-4">
                <div class="card-header section-header">
                    <button type="button"
                            class="btn btn-sm float-right"
                            data-toggle="modal"
                            data-target="#manageParticipationModal"
                            style="background:#a02626;color:#fff;font-size:.75rem;
                                   padding:3px 10px;border:none;border-radius:4px;
                                   font-weight:600;letter-spacing:.02em;margin-top:-1px;">
                        <i class="fas fa-pen mr-1"></i> Manage Participation
                    </button>
                    <i class="fas fa-history mr-2"></i> Participation Summary
                </div>
                <div class="card-body pb-2">

                    {{-- ── Quick-stat row ─────────────────────────────────── --}}
                    <div class="row text-center mb-3">
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-icon mx-auto {{ $hasMCSHistory ? 'bg-success' : 'bg-light-muted' }}">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <small class="d-block mt-2 text-muted">MCS</small>
                            <strong>{{ $hasMCSHistory ? 'Participated' : 'N/A' }}</strong>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="stat-icon mx-auto {{ $hasFCSHistory ? 'bg-info' : 'bg-light-muted' }}">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <small class="d-block mt-2 text-muted">FCS</small>
                            <strong>{{ $hasFCSHistory ? 'Participated' : 'N/A' }}</strong>
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

                    {{-- ── Year-by-year history ────────────────────────────── --}}
                    @if(!empty($exYears))
                    <div style="border-top:1px solid #f0f0f0;padding-top:1rem;">
                        <p class="mb-2" style="font-size:.7rem;font-weight:700;text-transform:uppercase;
                                               letter-spacing:.08em;color:#a02626;">
                            <i class="fas fa-stream mr-1"></i> Examination History by Year
                        </p>

                        <table class="table table-sm table-borderless mb-0" style="font-size:.87rem;">
                            <thead>
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <th style="width:80px;color:#6c757d;font-weight:600;padding-left:0;">Year</th>
                                    <th style="color:#6c757d;font-weight:600;">Programme</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach(array_reverse((array)$exYears) as $yr)
                                @php $progs = $yearProgrammes[(string)$yr] ?? []; @endphp
                                <tr style="border-bottom:1px solid #f9f9f9;">
                                    <td style="padding-left:0;font-weight:700;color:#333;vertical-align:middle;">
                                        {{ $yr }}
                                    </td>
                                    <td style="vertical-align:middle;">
                                        @if(!empty($progs))
                                            {{ implode(', ', $progs) }}
                                        @else
                                            <span class="text-muted" style="font-style:italic;">No record</span>
                                        @endif
                                    </td>
                                </tr>
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
@endsection

{{-- ══ Manage Participation Modal ════════════════════════════════════════════ --}}
<div class="modal fade" id="manageParticipationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#a02626;color:#fff;padding:.75rem 1rem;">
                <h5 class="modal-title mb-0">
                    <i class="fas fa-history mr-2"></i>
                    Manage Participation History — {{ $examiner->examiner_name }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.9;">
                    <span>&times;</span>
                </button>
            </div>

            <form method="POST"
                  action="{{ route('exams.manage.participation', $examiner->examin_id) }}">
                @csrf
                <input type="hidden" name="from" value="{{ $backUrl }}">

                <div class="modal-body" style="padding:1rem 1.25rem;">
                    @php
                        // Pre-select: years the examiner already has
                        $modalSelectedYears = array_map('strval', (array)$exYears);
                        // Pre-select per year: use $yearProgrammes (already an array per year)
                    @endphp

                    <p class="text-muted mb-3" style="font-size:.85rem;">
                        Check the years this examiner participated in, then tick every programme
                        they examined in that year. Unticking a year removes its record.
                    </p>

                    <div class="modal-year-list">
                        @foreach(array_reverse($examYears) as $yr)
                        @php
                            $isChecked    = in_array((string)$yr, $modalSelectedYears);
                            $checkedProgs = array_values(array_filter((array)($yearProgrammes[(string)$yr] ?? [])));
                            $mpCount      = count($checkedProgs);
                        @endphp
                        <div class="modal-year-row" id="modal_row_{{ $yr }}">
                            {{-- Year checkbox --}}
                            <div class="modal-year-check">
                                <input type="checkbox"
                                       class="modal-year-cb"
                                       name="examination_years[]"
                                       id="modal_yr_{{ $yr }}"
                                       value="{{ $yr }}"
                                       {{ $isChecked ? 'checked' : '' }}>
                                <label for="modal_yr_{{ $yr }}" class="modal-yr-label">{{ $yr }}</label>
                            </div>
                            {{-- Programme dropdown --}}
                            <div class="modal-prog-col" id="modal_prog_{{ $yr }}"
                                 style="{{ $isChecked ? '' : 'display:none;' }}">
                                <div class="prog-dropdown-wrap">
                                    <button type="button"
                                            class="btn btn-sm prog-dd-btn"
                                            data-prog-menu="prog_dd_{{ $yr }}">
                                        <i class="fas fa-list-ul mr-1"></i>
                                        <span class="prog-dd-label">
                                            @if($mpCount===0) Select programme(s)
                                            @elseif($mpCount===1) {{ $checkedProgs[0] }}
                                            @else {{ $mpCount }} programmes
                                            @endif
                                        </span>
                                        <i class="fas fa-caret-down ml-1" style="font-size:10px;"></i>
                                    </button>
                                    <div class="prog-dd-menu" id="prog_dd_{{ $yr }}">
                                        @foreach($programmeOptions as $prog)
                                        <label class="prog-dd-option">
                                            <input class="prog-dd-cb" type="checkbox"
                                                   name="year_programme[{{ $yr }}][]"
                                                   value="{{ $prog }}"
                                                   {{ in_array($prog, $checkedProgs) ? 'checked' : '' }}>
                                            <span>{{ $prog }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer" style="padding:.6rem 1rem;">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                                        ? asset('storage/'.$examiner->passport_image)
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

                    // Double-decode examination_years (double-encoded in DB)
                    $modalYears = [];
                    if ($hasHistory && !empty($examiner->examination_years)) {
                        $d = json_decode($examiner->examination_years, true);
                        if (is_string($d)) { $d = json_decode($d, true); }
                        $modalYears = is_array($d) ? $d : [];
                    }

                    // Double-decode exam_availability (same double-encoding issue)
                    $selectedAvailability = [];
                    if ($hasHistory && !empty($examiner->history->exam_availability)) {
                        $av = json_decode($examiner->history->exam_availability, true);
                        if (is_string($av)) { $av = json_decode($av, true); }
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
                                    <i class="fas fa-calendar-check text-success mr-1"></i>
                                    {{ $currentYearName }} Exam Availability
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
                                                    <span class="font-weight-bold">MCS — {{ $currentYearName }}</span>
                                                </div>
                                            @endif
                                            @if($hasFCS)
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                    <span class="font-weight-bold">FCS — {{ $currentYearName }}</span>
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

{{-- ══ CV Upload Modal ═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="uploadCvModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#a02626;color:#fff;padding:.75rem 1rem;">
                <h5 class="modal-title mb-0">
                    <i class="fas fa-upload mr-2"></i> Upload Curriculum Vitae
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.9;">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST"
                  action="{{ route('examiner.upload.cv', $examiner->examin_id) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Select CV File <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="cvFileInput"
                                   name="curriculum_vitae" accept=".pdf,.doc,.docx" required>
                            <label class="custom-file-label" for="cvFileInput">Choose file (PDF, DOC, DOCX — max 10 MB)</label>
                        </div>
                        @error('curriculum_vitae')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer" style="padding:.6rem 1rem;">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ── Force light-mode on this page regardless of OS/browser dark mode ──────
   color-scheme:light tells the browser NOT to apply automatic dark mode
   to this subtree. Explicit overrides catch AdminLTE's .dark-mode class.   */
.content-wrapper,
.content-wrapper *:not(.profile-hero-banner):not(.profile-hero-banner *) {
    color-scheme: light;
}

/* Cards & headers always light */
.card                { background-color: #fff !important; color: #212529 !important; }
.card-body           { background-color: #fff !important; color: #212529 !important; }
.card-footer         { background-color: #f8f9fa !important; color: #495057 !important; }
.section-header      { background: #f8f0f0 !important; color: #a02626 !important;
                       border-bottom-color: #f0dada !important; }

/* Info tables */
.info-table th       { background: #fafafa !important; color: #555 !important; }
.info-table td       { background: #fff !important; color: #333 !important; }
.table               { color: #212529 !important; }
.table th, .table td { color: #212529 !important; border-color: #dee2e6 !important; }

/* Stat icons & participation summary */
.stat-icon           { background-color: #e9ecef; }
.text-muted          { color: #6c757d !important; }

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

/* ── Manage Participation Modal ─────────────────────────────────────────── */
.modal-year-list { display:flex; flex-direction:column; }

.modal-year-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 7px 0;
    border-bottom: 1px solid #f4f4f4;
}
.modal-year-row:last-child { border-bottom: none; }

.modal-year-check {
    display: flex;
    align-items: center;
    gap: 7px;
    min-width: 70px;
    flex-shrink: 0;
}
.modal-yr-label {
    font-weight: 700;
    font-size: .88rem;
    color: #333;
    margin: 0;
    cursor: pointer;
    line-height: 1;
}

.modal-prog-col { flex: 1; }

/* ── Shared programme dropdown (modal + edit form) ── */
.prog-dropdown-wrap { position: relative; display: inline-block; }

.prog-dd-btn {
    background: #fff;
    border: 1px solid #d0d7de;
    color: #405867;
    font-size: 12px;
    padding: 3px 10px;
    border-radius: 4px;
    white-space: nowrap;
    max-width: 280px;
    text-align: left;
}
.prog-dd-btn:hover, .prog-dd-btn:focus {
    border-color: #a02626;
    color: #a02626;
    box-shadow: none;
    outline: none;
}

/* Menu — positioned by JS with position:fixed, so it escapes any overflow parent */
.prog-dd-menu {
    display: none;
    position: fixed;
    background: #fff;
    border: 1px solid #e2e2e2;
    border-radius: 5px;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    min-width: 230px;
    max-height: 240px;
    overflow-y: auto;
    padding: 4px;
    z-index: 99999;
}

.prog-dd-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    margin: 0;
    font-size: 13px;
    font-weight: 500;
    color: #405867;
    cursor: pointer;
    border-radius: 3px;
    user-select: none;
}
.prog-dd-option:hover { background: #fdf0f0; color: #a02626; }
.prog-dd-cb {
    width: 14px; height: 14px;
    accent-color: #a02626;
    cursor: pointer;
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

// ── CV upload modal — custom file label ──────────────────────────────────
$('#cvFileInput').on('change', function () {
    var name = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').text(name || 'Choose file');
});

// Re-open CV modal on validation error
@if($errors->has('curriculum_vitae'))
    $(document).ready(function () { $('#uploadCvModal').modal('show'); });
@endif

// ── Programme dropdown (works inside modals via position:fixed) ───────────

function progDdLabel($btn, $menu) {
    var $checked = $menu.find('.prog-dd-cb:checked');
    var label = $checked.length === 0 ? 'Select programme(s)'
              : $checked.length === 1  ? $checked.first().val()
              : $checked.length + ' programmes';
    $btn.find('.prog-dd-label').text(label);
}

$(document).on('click', '.prog-dd-btn', function (e) {
    e.preventDefault();
    var menuId = $(this).data('prog-menu');
    var $menu  = $('#' + menuId);
    var isOpen = $menu.is(':visible');

    // Close all open programme menus
    $('.prog-dd-menu:visible').hide();

    if (!isOpen) {
        var r = this.getBoundingClientRect();
        $menu.css({
            top:      (r.bottom + 2) + 'px',
            left:     r.left + 'px',
            minWidth: Math.max(r.width, 230) + 'px'
        }).show();
    }
});

// Close menus only when clicking outside any programme dropdown wrapper
$(document).on('click', function (e) {
    if (!$(e.target).closest('.prog-dropdown-wrap').length) {
        $('.prog-dd-menu:visible').hide();
    }
});

// Update label when a programme checkbox changes
$(document).on('change', '.prog-dd-cb', function () {
    var $menu = $(this).closest('.prog-dd-menu');
    var $btn  = $('[data-prog-menu="' + $menu.attr('id') + '"]');
    progDdLabel($btn, $menu);
});

// ── Manage Participation modal — year checkbox ────────────────────────────
$(document).on('change', '.modal-year-cb', function () {
    var yr   = $(this).val();
    var $col = $('#modal_prog_' + yr);
    if ($(this).is(':checked')) {
        $col.show();
    } else {
        $col.hide();
        $col.find('.prog-dd-cb').prop('checked', false);
        $col.find('.prog-dd-label').text('Select programme(s)');
    }
});
</script>
@endpush

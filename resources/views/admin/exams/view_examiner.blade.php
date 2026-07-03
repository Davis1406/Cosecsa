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
                    {{-- Reset Confirmation: direct POST with browser confirm --}}
                    <form method="POST"
                          action="{{ route('examiner.reset.confirmation', $examiner->examin_id) }}"
                          style="display:inline;"
                          onsubmit="return confirm('Reset the availability confirmation for {{ addslashes($examiner->examiner_name) }}? This will clear their submitted availability.')">
                        @csrf
                        <input type="hidden" name="type" value="soft">
                        <input type="hidden" name="back" value="{{ $backUrl ?? url('admin/exams/examiners') }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary mr-1"
                                title="Clear this examiner's availability submission">
                            <i class="fas fa-undo mr-1"></i> Reset Confirmation
                        </button>
                    </form>
                    <button type="button" class="btn btn-sm btn-outline-danger mr-1"
                            onclick="showVEDeleteModal()"
                            title="Soft or hard delete this examiner">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
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
                    {{-- Avatar (click to update photo) --}}
                    <div class="flex-shrink-0">
                        <div class="position-relative" style="display:inline-block;cursor:pointer;"
                             data-toggle="modal" data-target="#uploadPhotoModal"
                             title="Click to update profile photo">
                            <img src="{{ !empty($examiner->passport_image)
                                        ? asset('storage/' . $examiner->passport_image)
                                        : asset('/public/dist/img/user.png') }}"
                                 alt="{{ $examiner->examiner_name }}"
                                 class="profile-photo">
                            <div style="position:absolute;bottom:6px;right:6px;background:rgba(0,0,0,.5);
                                        border-radius:50%;width:28px;height:28px;
                                        display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-camera" style="color:#fff;font-size:12px;"></i>
                            </div>
                        </div>
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
                                <tr>
                                    <th><i class="fas fa-circle text-muted mr-1"></i> Status</th>
                                    <td>
                                        @php $status = $examiner->status ?? 'Active'; @endphp
                                        @if($status === 'Active')
                                            <span class="badge badge-pill badge-success">Active</span>
                                        @elseif($status === 'Inactive')
                                            <span class="badge badge-pill badge-warning">Inactive</span>
                                        @elseif($status === 'Deceased')
                                            <span class="badge badge-pill badge-secondary">Deceased</span>
                                        @else
                                            <span class="badge badge-pill badge-light">{{ $status }}</span>
                                        @endif
                                    </td>
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
                                    <th><i class="fas fa-gavel text-muted mr-1"></i> Designation</th>
                                    <td>
                                        @if(!empty($examiner->examiner_designation))
                                            <span class="badge badge-pill"
                                                  style="background:#a02626;color:#fff;font-size:.78rem;padding:.3em .7em;">
                                                {{ $examiner->examiner_designation }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-stethoscope text-muted mr-1"></i> Specialty</th>
                                    <td>{{ $examiner->specialty ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-microscope text-muted mr-1"></i> Sub-Specialty</th>
                                    <td>{{ $examiner->subspecialty ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th style="vertical-align:top;padding-top:.5rem;">
                                        <i class="fas fa-sticky-note text-muted mr-1"></i> Notes
                                    </th>
                                    <td>
                                        <div class="d-flex align-items-start justify-content-between" style="gap:.5rem;">
                                            <div style="flex:1;">
                                                @if(!empty($examiner->internal_notes))
                                                    <span style="font-size:.82rem;color:#555;white-space:pre-wrap;word-break:break-word;">{{ Str::limit($examiner->internal_notes, 120) }}</span>
                                                    @if(strlen($examiner->internal_notes) > 120)
                                                        <a href="#memoCard" onclick="document.getElementById('memoCard').scrollIntoView({behavior:'smooth'});return false;"
                                                           style="font-size:.75rem;"> more…</a>
                                                    @endif
                                                @else
                                                    <span class="text-muted" style="font-size:.82rem;">No notes</span>
                                                @endif
                                            </div>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary flex-shrink-0 memo-open-btn"
                                                    style="font-size:.7rem;padding:1px 7px;white-space:nowrap;">
                                                <i class="fas fa-pencil-alt mr-1"></i>
                                                {{ empty($examiner->internal_notes) ? 'Add' : 'Edit' }}
                                            </button>
                                        </div>
                                    </td>
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

                            {{-- Confirmation Email --}}
                            <small class="text-muted font-weight-bold text-uppercase"
                                   style="letter-spacing:.04em;">
                                <i class="fas fa-envelope mr-1"></i> Confirmation Email
                            </small>
                            <div class="mt-1 mb-3">
                                @if($examiner->email_confirmed)
                                    <div class="alert alert-success py-2 px-3 mb-2" style="font-size:.82rem;">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Examiner has confirmed availability.
                                    </div>
                                    <form method="POST"
                                          action="{{ route('examiner.send.confirmation', $examiner->examin_id) }}"
                                          onsubmit="return confirm('Resend confirmation email to {{ addslashes($examiner->email) }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary btn-block">
                                            <i class="fas fa-redo mr-1"></i> Resend Email
                                        </button>
                                    </form>
                                @elseif($examiner->last_email_sent_at)
                                    <div class="alert alert-warning py-2 px-3 mb-2" style="font-size:.82rem;">
                                        <i class="fas fa-clock mr-1"></i>
                                        Email sent {{ \Carbon\Carbon::parse($examiner->last_email_sent_at)->diffForHumans() }} — not yet confirmed.
                                    </div>
                                    <form method="POST"
                                          action="{{ route('examiner.send.confirmation', $examiner->examin_id) }}"
                                          onsubmit="return confirm('Resend confirmation email to {{ addslashes($examiner->email) }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning btn-block">
                                            <i class="fas fa-paper-plane mr-1"></i> Resend Email
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted d-block mb-2" style="font-size:.82rem;">No confirmation email sent yet.</span>
                                    <form method="POST"
                                          action="{{ route('examiner.send.confirmation', $examiner->examin_id) }}"
                                          onsubmit="return confirm('Send confirmation email to {{ addslashes($examiner->email) }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-block text-white"
                                                style="background:#a02626;border-color:#a02626;">
                                            <i class="fas fa-paper-plane mr-1"></i> Send Confirmation
                                        </button>
                                    </form>
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

            {{-- ── Internal Memo (hidden when saved; revealed by Edit button) ─── --}}
            <div class="card mb-4" id="memoCard" style="display:none;">
                <div class="card-header section-header d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-sticky-note mr-2"></i> Internal Memo</span>
                    <small class="text-muted font-weight-normal" style="font-size:.72rem;">
                        Admins only — not shared with examiner
                    </small>
                </div>
                <div class="card-body">
                    <form id="memoForm"
                          method="POST"
                          action="{{ route('examiner.save.memo', $examiner->examin_id) }}">
                        @csrf
                        <div class="form-group mb-2">
                            <textarea id="memoTextarea" name="internal_notes"
                                      class="form-control"
                                      rows="4"
                                      maxlength="5000"
                                      placeholder="Add a private note — e.g. dietary requirements, accommodation, past issues, availability notes…"
                                      style="resize:vertical;font-size:.875rem;">{{ old('internal_notes', $examiner->internal_notes ?? '') }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">
                                    <span id="memoUsed">{{ strlen($examiner->internal_notes ?? '') }}</span>/5000 characters
                                </small>
                                <small id="memoSavedIndicator" class="text-success" style="display:none;">
                                    <i class="fas fa-check mr-1"></i> Saved
                                </small>
                            </div>
                        </div>
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="submit" id="memoSaveBtn" class="btn btn-sm btn-danger">
                                <i class="fas fa-save mr-1"></i> Save Memo
                            </button>
                            <button type="button" id="memoCancelBtn" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── Participation Summary ─────────────────────────────────────── --}}
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
                // Derive MCS / FCS years from yearProgrammes (authoritative source)
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
                $examinerRoleLabel = $examiner->role_id == 1 ? 'Examiner' : 'Observer';
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

                    {{-- ── Year-by-year history ────────────────────────────── --}}
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
                                    $progs = array_values(array_unique($yearProgrammes[(string)$yr] ?? []));
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
            @else
            {{-- No history yet — show card with just the Manage Participation button --}}
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
                <div class="card-body text-muted" style="font-size:.875rem;padding:1rem 1.25rem;">
                    No participation history recorded yet. Use <strong>Manage Participation</strong> to add years and programmes.
                </div>
            </div>
            @endif

        </div>

        {{-- ── Candidates Examined ─────────────────────────────────────────── --}}
        @if($candidatesExamined->isNotEmpty())
        <div class="row mt-3">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between"
                         style="background:linear-gradient(135deg,#a02626,#c73333);color:#fff;border-radius:6px 6px 0 0;cursor:pointer;"
                         data-toggle="collapse" data-target="#candidatesExaminedPanel" aria-expanded="true">
                        <span style="font-weight:600;font-size:.95rem;">
                            <i class="fas fa-users mr-2"></i>
                            Candidates Examined
                            <span class="badge badge-light ml-2" style="color:#a02626;">{{ $candidatesExamined->count() }}</span>
                        </span>
                        <i class="fas fa-chevron-down" style="transition:transform .2s;" id="cep-chevron"></i>
                    </div>
                    <div class="collapse show" id="candidatesExaminedPanel">
                        <div class="card-body p-0">
                            {{-- Year filter tabs --}}
                            @php
                                $ceYears = $candidatesExamined->pluck('exam_year_display')->unique()->filter()->sort()->reverse()->values();
                            @endphp
                            <div class="p-3 pb-0 d-flex align-items-center flex-wrap" style="gap:.4rem;border-bottom:1px solid #f0f0f0;">
                                <button class="btn btn-sm btn-danger ce-year-btn active" data-year="all">All</button>
                                @foreach($ceYears as $ceYr)
                                <button class="btn btn-sm btn-outline-secondary ce-year-btn" data-year="{{ $ceYr }}">{{ $ceYr }}</button>
                                @endforeach
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" id="candidatesExaminedTable">
                                    <thead style="background:#f8f8f8;">
                                        <tr>
                                            <th style="width:40px;">#</th>
                                            <th>Candidate Name</th>
                                            <th>Candidate No.</th>
                                            <th>Programme</th>
                                            <th>Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($candidatesExamined as $ci => $cand)
                                        <tr class="ce-row ce-clickable"
                                            data-year="{{ $cand->exam_year_display }}"
                                            data-candidate-id="{{ $cand->candidate_id }}"
                                            data-examiner-id="{{ $examiner->examin_id }}"
                                            style="cursor:pointer;"
                                            title="Click to view results">
                                            <td class="text-muted" style="font-size:.8rem;">{{ $ci + 1 }}</td>
                                            <td style="font-weight:500;">
                                                {{ $cand->candidate_name ?: '—' }}
                                                <i class="fas fa-external-link-alt ml-1 text-muted" style="font-size:.7rem;"></i>
                                            </td>
                                            <td><code>{{ $cand->candidate_no ?? '—' }}</code></td>
                                            <td>
                                                <span class="badge badge-pill"
                                                      style="background:{{ str_contains($cand->programme,'MCS') ? '#007bff' : (str_contains($cand->programme,'GS') || str_contains($cand->programme,'General') ? '#28a745' : '#a02626') }};color:#fff;font-size:.72rem;">
                                                    {{ $cand->programme }}
                                                </span>
                                            </td>
                                            <td>{{ $cand->exam_year_display }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </section>
</div>
</div>

@push('scripts')
<script>
$(function(){
    // Chevron toggle animation
    $('#candidatesExaminedPanel').on('show.bs.collapse', function(){
        $('#cep-chevron').css('transform','rotate(0deg)');
    }).on('hide.bs.collapse', function(){
        $('#cep-chevron').css('transform','rotate(-90deg)');
    });

    // Year filter buttons
    $(document).on('click', '.ce-year-btn', function(){
        $('.ce-year-btn').removeClass('active btn-danger').addClass('btn-outline-secondary');
        $(this).addClass('active btn-danger').removeClass('btn-outline-secondary');
        var yr = $(this).data('year');
        if (yr === 'all') {
            $('.ce-row').show();
        } else {
            $('.ce-row').hide().filter('[data-year="'+yr+'"]').show();
        }
        var i = 1;
        $('.ce-row:visible td:first-child').each(function(){ $(this).text(i++); });
    });

    // Delete/reset modal
    $('input[name="veDeleteType"]').on('change', function(){
        $('#veDeleteTypeInput').val($(this).val());
    });

    // Click candidate row → load results modal
    $(document).on('click', '.ce-clickable', function(){
        var candidateId = $(this).data('candidate-id');
        var examinerId  = $(this).data('examiner-id');
        var url = '/admin/exams/examiner/' + examinerId + '/candidate/' + candidateId + '/results';

        $('#candidateResultsBody').html(
            '<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</td></tr>'
        );
        $('#candidateResultsModalLabel').text('Loading…');
        $('#candidateResultsModal').modal('show');

        $.getJSON(url, function(data){
            var cand = data.candidate;
            var name = cand ? (cand.name || '—') : '—';
            var no   = cand ? (cand.candidate_no || '') : '';
            $('#candidateResultsModalLabel').html(
                '<i class="fas fa-user-graduate mr-2"></i>' + name +
                (no ? ' <small class="ml-1 text-white-50">(' + no + ')</small>' : '')
            );

            if (!data.results || data.results.length === 0) {
                $('#candidateResultsBody').html(
                    '<tr><td colspan="5" class="text-center text-muted py-4">No results recorded for this candidate.</td></tr>'
                );
                return;
            }

            var progColors = { 'MCS':'#007bff', 'FCS General Surgery':'#28a745' };
            function progColor(p){ return progColors[p] || '#a02626'; }

            var html = '';
            var prevProg = null;
            $.each(data.results, function(i, r){
                var qmarks = '';
                if (r.question_mark && typeof r.question_mark === 'object') {
                    var parts = [];
                    $.each(r.question_mark, function(k, v){ parts.push('<span class="badge badge-secondary mr-1">Q' + (parseInt(k)+1) + ': ' + v + '</span>'); });
                    qmarks = parts.join('');
                } else if (r.question_mark) {
                    qmarks = r.question_mark;
                }

                var progBadge = '<span class="badge badge-pill" style="background:' + progColor(r.programme) + ';color:#fff;font-size:.7rem;">' + r.programme + '</span>';
                var format = r.exam_format ? '<span class="badge badge-outline-secondary" style="border:1px solid #aaa;font-size:.68rem;">' + r.exam_format + '</span>' : '';
                var station = r.station_id ? 'Station ' + r.station_id : '—';
                var total   = r.total !== null ? r.total : (r.overall !== null ? r.overall : '—');
                var remarks = r.remarks ? '<small class="text-muted d-block mt-1">' + r.remarks + '</small>' : '';

                html += '<tr>';
                html += '<td>' + progBadge + ' ' + format + '</td>';
                html += '<td>' + station + '</td>';
                html += '<td>' + (qmarks || '<span class="text-muted">—</span>') + remarks + '</td>';
                html += '<td><strong>' + total + '</strong></td>';
                html += '<td>' + (r.exam_year || '—') + '</td>';
                html += '</tr>';
            });
            $('#candidateResultsBody').html(html);
        }).fail(function(){
            $('#candidateResultsBody').html(
                '<tr><td colspan="5" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle mr-1"></i>Failed to load results.</td></tr>'
            );
        });
    });
});

function showVEDeleteModal() {
    var id   = {{ $examiner->examin_id }};
    var name = '{{ addslashes($examiner->examiner_name) }}';
    $('#veDeleteModalTitle').text('Delete Examiner — ' + name);
    $('#veDeleteModalDesc').html('Choose how to delete <strong>' + name + '</strong>.');
    $('#veDeleteForm').attr('action', '/admin/exams/examiner/' + id + '/destroy');
    $('#veTypeSoft').prop('checked', true);
    $('#veDeleteTypeInput').val('soft');
    $('#veDeleteModal').modal('show');
}
</script>
@endpush

{{-- ══ Delete / Reset Modal ════════════════════════════════════════════════════ --}}
<div class="modal fade" id="veDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#a02626;color:#fff;">
                <h5 class="modal-title" id="veDeleteModalTitle">Confirm Action</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="veDeleteModalDesc" class="mb-3"></p>
                <div class="form-group mb-0">
                    <label class="font-weight-bold mb-2">Select action type:</label>
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" id="veTypeSoft" name="veDeleteType" value="soft"
                               class="custom-control-input" checked>
                        <label class="custom-control-label" for="veTypeSoft">
                            <strong>Soft</strong> — Deactivate only — all data is kept but examiner is hidden.
                        </label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="veTypeHard" name="veDeleteType" value="hard"
                               class="custom-control-input">
                        <label class="custom-control-label text-danger" for="veTypeHard">
                            <strong>Hard</strong> — Permanently remove examiner + history, groups, shifts, attendance.
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <form id="veDeleteForm" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="type" id="veDeleteTypeInput" value="soft">
                    <input type="hidden" name="back" value="{{ url('admin/exams/examiners') }}">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-check mr-1"></i> Confirm Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══ Candidate Results Modal ════════════════════════════════════════════════ --}}
<div class="modal fade" id="candidateResultsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#a02626;color:#fff;padding:.75rem 1rem;">
                <h5 class="modal-title mb-0" id="candidateResultsModalLabel">
                    <i class="fas fa-user-graduate mr-2"></i> Candidate Results
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.9;">&times;</button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead style="background:#f8f8f8;font-size:.82rem;">
                            <tr>
                                <th style="width:180px;">Programme</th>
                                <th style="width:100px;">Station</th>
                                <th>Question Marks</th>
                                <th style="width:70px;">Total</th>
                                <th style="width:70px;">Year</th>
                            </tr>
                        </thead>
                        <tbody id="candidateResultsBody">
                            <tr><td colspan="5" class="text-center py-4 text-muted">Select a candidate to view results.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

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

                    <div class="mp-year-list">
                        @foreach(array_reverse($examYears) as $yr)
                        @php
                            $isChecked    = in_array((string)$yr, $modalSelectedYears);
                            $checkedProgs = array_values(array_filter((array)($yearProgrammes[(string)$yr] ?? [])));
                        @endphp
                        <div class="mp-year-block {{ $isChecked ? 'mp-year-active' : '' }}" id="mp_block_{{ $yr }}">

                            {{-- Year header row --}}
                            <label class="mp-year-hdr">
                                <input type="checkbox"
                                       class="mp-year-cb"
                                       name="examination_years[]"
                                       value="{{ $yr }}"
                                       {{ $isChecked ? 'checked' : '' }}>
                                <span class="mp-yr-num">{{ $yr }}</span>
                                @if($isChecked && count($checkedProgs))
                                <span class="mp-yr-pill">{{ count($checkedProgs) }} programme{{ count($checkedProgs) > 1 ? 's' : '' }}</span>
                                @endif
                            </label>

                            {{-- Per-programme list with role toggles --}}
                            <div class="mp-prog-panel" style="{{ $isChecked ? '' : 'display:none;' }}">
                                @foreach($programmeOptions as $prog)
                                @php
                                    $isProgChecked = in_array($prog, $checkedProgs);
                                    $progRole      = $yearRoles[(string)$yr][$prog] ?? 'Examiner';
                                @endphp
                                <div class="mp-prog-row {{ $isProgChecked ? 'mp-prog-on' : '' }}">
                                    <label class="mp-prog-label">
                                        <input type="checkbox"
                                               class="mp-prog-cb"
                                               name="year_programme[{{ $yr }}][]"
                                               value="{{ $prog }}"
                                               data-yr="{{ $yr }}"
                                               {{ $isProgChecked ? 'checked' : '' }}>
                                        <span class="mp-prog-name">{{ $prog }}</span>
                                    </label>
                                    <div class="mp-role-wrap" style="{{ $isProgChecked ? '' : 'opacity:.3;pointer-events:none;' }}">
                                        <label class="mp-role-btn {{ $progRole === 'Examiner' ? 'mp-role-e-on' : '' }}">
                                            <input type="radio"
                                                   name="year_role[{{ $yr }}][{{ $prog }}]"
                                                   value="Examiner"
                                                   {{ $progRole === 'Examiner' ? 'checked' : '' }}>
                                            <i class="fas fa-user-check"></i> Examiner
                                        </label>
                                        <label class="mp-role-btn {{ $progRole === 'Observer' ? 'mp-role-o-on' : '' }}">
                                            <input type="radio"
                                                   name="year_role[{{ $yr }}][{{ $prog }}]"
                                                   value="Observer"
                                                   {{ $progRole === 'Observer' ? 'checked' : '' }}>
                                            <i class="fas fa-eye"></i> Observer
                                        </label>
                                    </div>
                                </div>
                                @endforeach
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
                                @php
                                    // Extract specific years for MCS and FCS from yearProgrammes
                                    $mcsYears = [];
                                    $fcsYears = [];
                                    foreach ($yearProgrammes as $yr => $progs) {
                                        foreach ($progs as $prog) {
                                            if (strtoupper($prog) === 'MCS') {
                                                $mcsYears[] = $yr;
                                            } elseif (stripos($prog, 'FCS') !== false) {
                                                $fcsYears[] = $yr;
                                            }
                                        }
                                    }
                                    sort($mcsYears);
                                    sort($fcsYears);
                                @endphp
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="icon-circle bg-success text-white mr-3 flex-shrink-0">
                                                <i class="fas fa-laptop"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">Virtual MCS Participation</small>
                                                <div class="font-weight-bold">
                                                    @if(!empty($mcsYears))
                                                        @foreach($mcsYears as $mcsYr)
                                                            <span class="badge badge-success mr-1">{{ $mcsYr }}</span>
                                                        @endforeach
                                                    @elseif($hasHistory && ($examiner->virtual_mcs_participated ?? '') == 'Yes')
                                                        <span class="badge badge-success">Yes</span>
                                                    @else
                                                        <span class="badge badge-secondary">No record</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="icon-circle bg-info text-white mr-3 flex-shrink-0">
                                                <i class="fas fa-stethoscope"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">FCS Participation</small>
                                                <div class="font-weight-bold">
                                                    @if(!empty($fcsYears))
                                                        @foreach($fcsYears as $fcsYr)
                                                            <span class="badge badge-info mr-1">{{ $fcsYr }}</span>
                                                        @endforeach
                                                    @elseif($hasHistory && ($examiner->fcs_participated ?? '') == 'Yes')
                                                        <span class="badge badge-info">Yes</span>
                                                    @else
                                                        <span class="badge badge-secondary">No record</span>
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

{{-- ══ Upload Photo Modal ════════════════════════════════════════════════════ --}}
<div class="modal fade" id="uploadPhotoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#a02626;color:#fff;padding:.75rem 1rem;">
                <h5 class="modal-title mb-0">
                    <i class="fas fa-camera mr-2"></i> Update Profile Photo
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.9;">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST"
                  action="{{ route('examiner.upload.photo', $examiner->examin_id) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="photoPreviewImg"
                             src="{{ !empty($examiner->passport_image) ? asset('storage/' . $examiner->passport_image) : asset('/public/dist/img/user.png') }}"
                             alt="Current photo"
                             style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:3px solid #a02626;">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Select New Photo <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="photoFileInput"
                                   name="passport_image" accept="image/*" required>
                            <label class="custom-file-label" for="photoFileInput">
                                Choose image (JPG, PNG — max 5 MB)
                            </label>
                        </div>
                        @error('passport_image')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer" style="padding:.6rem 1rem;">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-upload mr-1"></i> Update Photo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

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

/* ── Manage Participation Modal — new per-programme role design ─────────── */
.mp-year-list    { display:flex; flex-direction:column; gap:4px; }

.mp-year-block {
    border: 1px solid #eee;
    border-radius: 6px;
    overflow: hidden;
}
.mp-year-block.mp-year-active { border-color: #f0dada; }

.mp-year-hdr {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    margin: 0;
    cursor: pointer;
    background: #fafafa;
    font-weight: 700;
    font-size: .88rem;
    color: #333;
    user-select: none;
}
.mp-year-block.mp-year-active .mp-year-hdr { background: #fdf4f4; }
.mp-year-hdr input[type=checkbox] { accent-color: #a02626; width:15px; height:15px; cursor:pointer; }
.mp-yr-num  { font-size: .95rem; }
.mp-yr-pill {
    font-size: .7rem; font-weight: 600;
    background: #a02626; color: #fff;
    padding: 1px 8px; border-radius: 10px;
    margin-left: auto;
}

/* Programme panel */
.mp-prog-panel {
    border-top: 1px solid #f0e8e8;
    padding: 4px 0;
    max-height: 240px;
    overflow-y: auto;
}

.mp-prog-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 12px;
    gap: 10px;
    border-bottom: 1px solid #fafafa;
    transition: background .1s;
}
.mp-prog-row:last-child { border-bottom: none; }
.mp-prog-row.mp-prog-on { background: #fffbf5; }
.mp-prog-row:hover { background: #fef8f8; }

.mp-prog-label {
    display: flex; align-items: center; gap: 8px;
    margin: 0; cursor: pointer; font-size: .83rem; font-weight: 500; color: #444;
    flex: 1;
}
.mp-prog-label input[type=checkbox] {
    accent-color: #a02626; width:14px; height:14px; cursor:pointer; flex-shrink:0;
}
.mp-prog-name { line-height: 1.3; }

/* Role toggle buttons */
.mp-role-wrap  { display: flex; gap: 4px; flex-shrink: 0; }

.mp-role-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 4px; font-size: .75rem; font-weight: 600;
    cursor: pointer; margin: 0; border: 1px solid #ddd; background: #f8f9fa;
    color: #666; transition: all .15s; user-select: none; white-space: nowrap;
}
.mp-role-btn input[type=radio] { display: none; }
.mp-role-btn:hover          { border-color: #999; color: #333; }
.mp-role-e-on               { background: #d4edda; border-color: #28a745; color: #155724; }
.mp-role-o-on               { background: #fff3cd; border-color: #ffc107; color: #856404; }

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

// ── Photo upload modal — preview + label ─────────────────────────────────
$('#photoFileInput').on('change', function () {
    var name = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').text(name || 'Choose image');
    var file = this.files[0];
    if (file && file.type.startsWith('image/')) {
        var reader = new FileReader();
        reader.onload = function (e) { $('#photoPreviewImg').attr('src', e.target.result); };
        reader.readAsDataURL(file);
    }
});

// Re-open photo modal on validation error
@if($errors->has('passport_image'))
    $(document).ready(function () { $('#uploadPhotoModal').modal('show'); });
@endif

// ── Internal Memo ─────────────────────────────────────────────────────────
(function () {
    var $card   = $('#memoCard');
    var $ta     = $('#memoTextarea');
    var $used   = $('#memoUsed');
    var $saved  = $('#memoSavedIndicator');
    var $btn    = $('#memoSaveBtn');
    var autoTimer = null;

    // Cancel — hide the card again
    $('#memoCancelBtn').on('click', function () {
        clearTimeout(autoTimer);
        $card.slideUp(200);
    });

    // Character counter + auto-save
    $ta.on('input', function () {
        $used.text($ta.val().length);
        $saved.hide();
        clearTimeout(autoTimer);
        autoTimer = setTimeout(function () { submitMemo(true); }, 2000);
    });

    // Manual save
    $('#memoForm').on('submit', function (e) {
        e.preventDefault();
        clearTimeout(autoTimer);
        submitMemo(false);
    });

    function refreshNotePreview(txt) {
        var preview = txt.length > 120 ? txt.substring(0, 120) + '…' : txt;
        var $noteCell = $('.info-table tr').filter(function () {
            return $(this).find('.fa-sticky-note').length > 0;
        }).find('td');
        if ($noteCell.length) {
            var escapedPreview = $('<div>').text(preview).html();
            var $inner = txt
                ? $('<span style="font-size:.82rem;color:#555;white-space:pre-wrap;word-break:break-word;">' + escapedPreview + '</span>')
                : $('<span class="text-muted" style="font-size:.82rem;">No notes</span>');
            // Keep the Edit/Add button
            var $editRowBtn = $noteCell.find('button').clone(true);
            $noteCell.find('div:first').empty().append($inner);
            // Update button label
            $noteCell.find('button').html('<i class="fas fa-pencil-alt mr-1"></i>' + (txt ? 'Edit' : 'Add'));
        }
    }

    function submitMemo(silent) {
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving…');
        var txt = $ta.val();
        $.ajax({
            url:  '{{ route("examiner.save.memo", $examiner->examin_id) }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', internal_notes: txt },
            success: function () {
                refreshNotePreview(txt);
                // Hide the memo card after save
                $card.slideUp(200);
                $saved.show();
                if (!silent) { setTimeout(function () { $saved.fadeOut(); }, 3000); }
            },
            error: function () {
                if (!silent) alert('Could not save memo. Please try again.');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Memo');
            }
        });
    }
})();

// ── Open memo card when Edit/Add clicked from Current Assignment Notes row ──
$(document).on('click', '.memo-open-btn', function () {
    var $card = $('#memoCard');
    $card.slideDown(200, function () {
        $card[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        $('#memoTextarea').focus();
    });
});

// ── Manage Participation Modal — per-programme role UI ────────────────────

// Helper: refresh the pill badge on a year header
function mpUpdatePill($block) {
    var n    = $block.find('.mp-prog-cb:checked').length;
    var $hdr = $block.find('.mp-year-hdr');
    var $pill = $hdr.find('.mp-yr-pill');
    if (n > 0) {
        var txt = n + ' programme' + (n > 1 ? 's' : '');
        if ($pill.length) { $pill.text(txt); } else { $hdr.append('<span class="mp-yr-pill">' + txt + '</span>'); }
    } else {
        $pill.remove();
    }
}

// Year checkbox: expand/collapse the programme panel
$(document).on('change', '.mp-year-cb', function () {
    var $block = $(this).closest('.mp-year-block');
    var $panel = $block.find('.mp-prog-panel');
    if ($(this).is(':checked')) {
        $block.addClass('mp-year-active');
        $panel.slideDown(150);
    } else {
        $block.removeClass('mp-year-active');
        $panel.slideUp(150, function () {
            $panel.find('.mp-prog-cb').prop('checked', false);
            $panel.find('.mp-prog-row').removeClass('mp-prog-on');
            $panel.find('.mp-role-wrap').css({ opacity: '.3', 'pointer-events': 'none' });
        });
        mpUpdatePill($block);
    }
});

// Programme checkbox: enable/disable its role buttons + update pill
$(document).on('change', '.mp-prog-cb', function () {
    var $row  = $(this).closest('.mp-prog-row');
    var $wrap = $row.find('.mp-role-wrap');
    if ($(this).is(':checked')) {
        $row.addClass('mp-prog-on');
        $wrap.css({ opacity: '1', 'pointer-events': 'auto' });
    } else {
        $row.removeClass('mp-prog-on');
        $wrap.css({ opacity: '.3', 'pointer-events': 'none' });
    }
    mpUpdatePill($(this).closest('.mp-year-block'));
});

// Role radio change: swap the active highlight class
$(document).on('change', '.mp-role-btn input[type=radio]', function () {
    var $wrap = $(this).closest('.mp-role-wrap');
    $wrap.find('.mp-role-btn').removeClass('mp-role-e-on mp-role-o-on');
    var $lbl = $(this).closest('.mp-role-btn');
    $lbl.addClass($(this).val() === 'Examiner' ? 'mp-role-e-on' : 'mp-role-o-on');
});
</script>
@endpush

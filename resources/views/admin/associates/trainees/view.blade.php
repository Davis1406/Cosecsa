@extends('layout.app')

@push('styles')
<style>
    #trainee-profile-root { font-family: 'Source Sans Pro', sans-serif; font-size: 14px; }

    .tp-hero {
        background: linear-gradient(135deg, #a02626 0%, #7a1c1c 100%);
        color: #fff;
        border-radius: 10px;
        padding: 28px 30px 22px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        gap: 24px;
        box-shadow: 0 4px 18px rgba(160,38,38,.25);
        flex-wrap: wrap;
    }
    .tp-avatar {
        width: 82px; height: 82px; border-radius: 50%;
        background: rgba(255,255,255,.18);
        border: 3px solid rgba(255,255,255,.5);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 700; color: #fff; flex-shrink: 0;
        text-transform: uppercase; letter-spacing: 1px;
    }
    .tp-hero-info { flex: 1; min-width: 200px; }
    .tp-hero-info h2 { margin: 0 0 4px; font-size: 1.55rem; font-weight: 700; }
    .tp-hero-info .entry { font-size: .95rem; opacity: .85; margin-bottom: 10px; }
    .tp-hero-actions { display: flex; gap: 8px; flex-shrink: 0; align-items: center; }

    .tp-badge {
        display: inline-block; padding: 3px 12px; border-radius: 20px;
        font-size: .78rem; font-weight: 600; letter-spacing: .3px;
    }
    .tp-badge-active   { background: #d4edda; color: #155724; }
    .tp-badge-inactive { background: #f8d7da; color: #721c24; }
    .tp-badge-pending  { background: #fff3cd; color: #856404; }
    .tp-badge-sent     { background: #d4edda; color: #155724; }
    .tp-badge-grey     { background: #e2e3e5; color: #383d41; }

    .tp-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 8px rgba(0,0,0,.08);
        margin-bottom: 18px;
        overflow: hidden;
    }
    .tp-card-header {
        background: #f8f9fa;
        border-bottom: 2px solid #a02626;
        padding: 11px 18px;
        display: flex; align-items: center; gap: 9px;
    }
    .tp-card-header .hicon { color: #a02626; font-size: 1rem; width: 18px; text-align: center; }
    .tp-card-header h6 { margin: 0; font-weight: 700; font-size: .85rem; color: #333; text-transform: uppercase; letter-spacing: .5px; }
    .tp-card-body { padding: 18px 20px; }

    .tp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 24px; }
    .tp-grid-3 { grid-template-columns: 1fr 1fr 1fr; }
    .tp-grid-1 { grid-template-columns: 1fr; }

    .tp-field label { display: block; font-size: .7rem; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
    .tp-field .val { font-size: .92rem; color: #222; font-weight: 500; word-break: break-word; }
    .tp-field .val.empty { color: #ccc; font-style: italic; }
    .tp-field .val a { color: #a02626; }
    .tp-field .val a:hover { color: #7a1c1c; }

    .tp-amount { font-size: 1.3rem; font-weight: 700; color: #a02626; }
    .tp-divider { border: none; border-top: 1px solid #eee; margin: 14px 0; }
    .tp-mono { font-family: monospace; background: #f4f4f4; padding: 2px 8px; border-radius: 4px; font-size: .88rem; }

    .tp-btn-edit {
        background: #FEC503; border: none; color: #333;
        padding: 7px 18px; border-radius: 5px; font-weight: 600;
        font-size: .85rem; cursor: pointer; display: inline-flex;
        align-items: center; gap: 6px; text-decoration: none;
    }
    .tp-btn-edit:hover { background: #e0ad00; color: #111; text-decoration: none; }
    .tp-btn-back {
        background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.45);
        color: #fff; padding: 7px 16px; border-radius: 5px; font-weight: 600;
        font-size: .85rem; display: inline-flex; align-items: center;
        gap: 6px; text-decoration: none;
    }
    .tp-btn-back:hover { background: rgba(255,255,255,.28); color: #fff; text-decoration: none; }

    @@media (max-width: 768px) {
        .tp-grid   { grid-template-columns: 1fr; }
        .tp-grid-3 { grid-template-columns: 1fr 1fr; }
    }
    @@media (max-width: 480px) {
        .tp-grid-3 { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content" style="padding: 20px;">

    @if($trainee)

    @php
        $initials = collect(explode(' ', trim($trainee->name)))
            ->filter()->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');

        $statusClass = match(strtolower($trainee->status ?? '')) {
            'active'   => 'tp-badge-active',
            'inactive' => 'tp-badge-inactive',
            'pending'  => 'tp-badge-pending',
            default    => 'tp-badge-grey',
        };

        $admLetterClass = match(strtolower($trainee->admission_letter_status ?? '')) {
            'sent'    => 'tp-badge-active',
            'pending' => 'tp-badge-pending',
            default   => 'tp-badge-grey',
        };

        $invLetterClass = match(strtolower($trainee->invitation_letter_status ?? '')) {
            'sent'    => 'tp-badge-active',
            'pending' => 'tp-badge-pending',
            default   => 'tp-badge-grey',
        };

        $invStatusClass = match(strtolower($trainee->invoice_status ?? '')) {
            'paid'    => 'tp-badge-active',
            'unpaid'  => 'tp-badge-inactive',
            'pending' => 'tp-badge-pending',
            default   => 'tp-badge-grey',
        };

        $sponsor = ($trainee->sponsor && $trainee->sponsor !== 'null') ? $trainee->sponsor : null;
        $amountFormatted = is_numeric($trainee->amount_paid)
            ? '$' . number_format((float)$trainee->amount_paid, 2)
            : null;

        $genderIcon = match(strtolower($trainee->gender ?? '')) {
            'male'   => 'fa-mars',
            'female' => 'fa-venus',
            default  => 'fa-genderless',
        };
    @endphp

    {{-- ── Hero ── --}}
    <div class="tp-hero">
        <div class="tp-avatar">{{ $initials }}</div>
        <div class="tp-hero-info">
            <h2>{{ $trainee->name }}</h2>
            <div class="entry">
                <i class="fas fa-id-badge mr-1"></i>{{ $trainee->entry_number ?: 'No entry number' }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <i class="fas fa-graduation-cap mr-1"></i>{{ $trainee->programme_name ?: '—' }}
            </div>
            <span class="tp-badge {{ $statusClass }}">{{ $trainee->status ?: 'Unknown' }}</span>
        </div>
        <div class="tp-hero-actions">
            <a href="{{ url('admin/associates/trainees/trainees') }}" class="tp-btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="{{ url('admin/associates/trainees/edit/' . $trainee->trainee_id) }}" class="tp-btn-edit">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    {{-- ── Row 1 ── --}}
    <div class="row">
        {{-- Personal --}}
        <div class="col-md-6">
            <div class="tp-card">
                <div class="tp-card-header">
                    <i class="fas fa-user hicon"></i><h6>Personal Information</h6>
                </div>
                <div class="tp-card-body">
                    <div class="tp-grid">
                        <div class="tp-field">
                            <label>Full Name</label>
                            <div class="val">{{ $trainee->name ?: '—' }}</div>
                        </div>
                        <div class="tp-field">
                            <label>Gender</label>
                            <div class="val">
                                @if($trainee->gender)
                                    <i class="fas {{ $genderIcon }} mr-1"></i>{{ $trainee->gender }}
                                @else
                                    <span class="empty">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="tp-field">
                            <label>Personal Email</label>
                            <div class="val">
                                @if($trainee->personal_email)
                                    <a href="mailto:{{ $trainee->personal_email }}">{{ $trainee->personal_email }}</a>
                                @else
                                    <span class="empty">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="tp-field">
                            <label>Country</label>
                            <div class="val">
                                @if($trainee->country_name)
                                    <i class="fas fa-globe-africa mr-1" style="color:#a02626"></i>{{ $trainee->country_name }}
                                @else
                                    <span class="empty">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Training --}}
        <div class="col-md-6">
            <div class="tp-card">
                <div class="tp-card-header">
                    <i class="fas fa-hospital hicon"></i><h6>Training Information</h6>
                </div>
                <div class="tp-card-body">
                    <div class="tp-grid">
                        <div class="tp-field">
                            <label>Programme</label>
                            <div class="val">{{ $trainee->programme_name ?: '—' }}</div>
                        </div>
                        <div class="tp-field">
                            <label>Hospital</label>
                            <div class="val">
                                @if($trainee->hospital_name)
                                    <i class="fas fa-hospital-alt mr-1" style="color:#a02626"></i>{{ $trainee->hospital_name }}
                                @else
                                    <span class="empty">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="tp-field">
                            <label>Admission Year</label>
                            <div class="val">{{ $trainee->admission_year ?: '—' }}</div>
                        </div>
                        <div class="tp-field">
                            <label>Study Year</label>
                            <div class="val">{{ $trainee->programme_year ?: '—' }}</div>
                        </div>
                        <div class="tp-field">
                            <label>Exam Year</label>
                            <div class="val">{{ $trainee->exam_year ?: '—' }}</div>
                        </div>
                        <div class="tp-field">
                            <label>Programme Duration</label>
                            <div class="val">
                                @if($trainee->programme_period)
                                    <i class="fas fa-clock mr-1" style="color:#888"></i>
                                    {{ $trainee->programme_period }} {{ $trainee->programme_period == 1 ? 'Year' : 'Years' }}
                                @else
                                    <span class="empty">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 2 ── --}}
    <div class="row">
        {{-- Correspondence + System Access --}}
        <div class="col-md-5">
            <div class="tp-card">
                <div class="tp-card-header">
                    <i class="fas fa-envelope-open-text hicon"></i><h6>Correspondence</h6>
                </div>
                <div class="tp-card-body">
                    <div class="tp-grid">
                        <div class="tp-field">
                            <label>Admission Letter</label>
                            <div class="val">
                                <span class="tp-badge {{ $admLetterClass }}">{{ $trainee->admission_letter_status ?: '—' }}</span>
                            </div>
                        </div>
                        <div class="tp-field">
                            <label>Invitation Letter</label>
                            <div class="val">
                                <span class="tp-badge {{ $invLetterClass }}">{{ $trainee->invitation_letter_status ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <hr class="tp-divider">

                    <div class="tp-card-header" style="margin: 0 -20px 14px; border-bottom: 1px solid #eee; border-top: none; background: #fafafa;">
                        <i class="fas fa-user-lock hicon"></i><h6>System Access</h6>
                    </div>
                    <div class="tp-field">
                        <label>SFS Username</label>
                        <div class="val">
                            @if($trainee->user_email)
                                <span class="tp-mono">{{ $trainee->user_email }}</span>
                            @else
                                <span class="empty">—</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment --}}
        <div class="col-md-7">
            <div class="tp-card">
                <div class="tp-card-header">
                    <i class="fas fa-file-invoice-dollar hicon"></i><h6>Payment &amp; Invoice</h6>
                </div>
                <div class="tp-card-body">
                    <div class="tp-grid tp-grid-3">
                        <div class="tp-field">
                            <label>Invoice Number</label>
                            <div class="val">{{ $trainee->invoice_number ?: '—' }}</div>
                        </div>
                        <div class="tp-field">
                            <label>Invoice Date</label>
                            <div class="val">{{ $trainee->invoice_date ?: '—' }}</div>
                        </div>
                        <div class="tp-field">
                            <label>Invoice Status</label>
                            <div class="val">
                                <span class="tp-badge {{ $invStatusClass }}">{{ $trainee->invoice_status ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <hr class="tp-divider">

                    <div class="tp-grid tp-grid-3">
                        <div class="tp-field">
                            <label>Amount Paid</label>
                            <div class="val">
                                @if($amountFormatted)
                                    <span class="tp-amount">{{ $amountFormatted }}</span>
                                @else
                                    <span class="empty">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="tp-field">
                            <label>Mode of Payment</label>
                            <div class="val">
                                @if($trainee->mode_of_payment)
                                    <i class="fas fa-credit-card mr-1" style="color:#888"></i>{{ $trainee->mode_of_payment }}
                                @else
                                    <span class="empty">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="tp-field">
                            <label>Date Paid</label>
                            <div class="val">{{ $trainee->payment_date ?: '—' }}</div>
                        </div>
                    </div>

                    @if($sponsor)
                    <hr class="tp-divider">
                    <div class="tp-field">
                        <label>Sponsor</label>
                        <div class="val">
                            <i class="fas fa-building mr-1" style="color:#888"></i>{{ $sponsor }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @else
    <div class="alert alert-warning">Trainee not found.</div>
    @endif

    </section>
</div>
@endsection

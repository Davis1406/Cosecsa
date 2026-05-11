@extends('layout.app')

@section('content')
<div class="wrapper">
  <div class="content-wrapper">

    {{-- ── Header / breadcrumb ──────────────────────────────────── --}}
    <section class="content-header py-2">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-sm-6">
            <h5 class="m-0" style="color:#a02626;">
              <i class="fas fa-user-graduate mr-2"></i>Candidate Profile
            </h5>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/associates/candidates/edit/' . ($candidate->candidates_id ?? 0)) }}"
               class="btn btn-sm btn-warning mr-1">
              <i class="fas fa-edit mr-1"></i>Edit
            </a>
            <a href="{{ url('admin/associates/candidates/list') }}"
               class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-arrow-left mr-1"></i>Back
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content pt-0">
      <div class="container-fluid">

        @if($candidate)

        {{-- ── Hero Bar ─────────────────────────────────────────── --}}
        @php
          $initials = strtoupper(substr($candidate->firstname ?? $candidate->name ?? 'C', 0, 1)
                    . substr($candidate->lastname ?? '', 0, 1));
          if (strlen($initials) < 2) $initials = strtoupper(substr($candidate->name ?? 'C', 0, 2));
          $prog = $candidate->programme_name ?? 'Unknown Programme';
          $isFcs = Str::startsWith($prog, 'FCS');
          $feePaid = $candidate->fee_paid ?? 'No';
        @endphp

        <div class="row mb-3">
          <div class="col-12">
            <div class="card shadow-sm" style="border-left:5px solid #a02626; border-radius:8px;">
              <div class="card-body py-3 px-4">
                <div class="d-flex align-items-center flex-wrap">
                  {{-- Avatar --}}
                  <div class="mr-4 mb-2" style="flex-shrink:0;">
                    <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#a02626,#c0392b);
                                display:flex;align-items:center;justify-content:center;
                                font-size:1.6rem;font-weight:700;color:#fff;letter-spacing:1px;">
                      {{ $initials }}
                    </div>
                  </div>
                  {{-- Info --}}
                  <div class="flex-grow-1">
                    <h4 class="mb-1 font-weight-bold" style="color:#2c3e50;">
                      {{ $candidate->name ?? ($candidate->firstname . ' ' . $candidate->lastname) }}
                    </h4>
                    <div class="d-flex flex-wrap align-items-center">
                      @if(!empty($candidate->entry_number))
                      <span class="badge badge-secondary mr-2 mb-1" style="font-size:.8rem;">
                        <i class="fas fa-id-card mr-1"></i>{{ $candidate->entry_number }}
                      </span>
                      @endif
                      <span class="badge mr-2 mb-1" style="font-size:.8rem; background:{{ $isFcs ? '#2980b9' : '#7f8c8d' }}; color:#fff;">
                        {{ $prog }}
                      </span>
                      <span class="badge mr-2 mb-1" style="font-size:.8rem; background:{{ ($candidate->exam_year ?? '') == date('Y') ? '#27ae60' : '#95a5a6' }}; color:#fff;">
                        Exam {{ $candidate->exam_year ?? '—' }}
                      </span>
                      @if($feePaid === 'Yes')
                        <span class="badge badge-success mb-1" style="font-size:.8rem;">
                          <i class="fas fa-check-circle mr-1"></i>Fee Paid
                        </span>
                      @else
                        <span class="badge badge-danger mb-1" style="font-size:.8rem;">
                          <i class="fas fa-times-circle mr-1"></i>Fee Unpaid
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- ── Cards Row ────────────────────────────────────────── --}}
        <div class="row">

          {{-- Personal Info --}}
          <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="border-radius:8px;">
              <div class="card-header py-2 px-3" style="background:#fff;border-bottom:2px solid #a02626;">
                <h6 class="mb-0 font-weight-bold" style="color:#a02626;">
                  <i class="fas fa-user mr-2"></i>Personal Information
                </h6>
              </div>
              <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:.88rem;">
                  <tr>
                    <th class="pl-3 text-muted" style="width:38%;border-top:none;font-weight:600;">Full Name</th>
                    <td style="border-top:none;">{{ $candidate->name ?? '-' }}</td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Gender</th>
                    <td>
                      @if(($candidate->gender ?? '') === 'Female')
                        <span class="badge badge-warning" style="color:#333;">Female</span>
                      @elseif(($candidate->gender ?? '') === 'Male')
                        <span class="badge badge-info">Male</span>
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Personal Email</th>
                    <td>
                      @if(!empty($candidate->personal_email))
                        <a href="mailto:{{ $candidate->personal_email }}">{{ $candidate->personal_email }}</a>
                      @else —
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">SFS Username</th>
                    <td>{{ $candidate->user_email ?? '—' }}</td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Country</th>
                    <td>{{ $candidate->country_name ?? '—' }}</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>

          {{-- Exam Info --}}
          <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="border-radius:8px;">
              <div class="card-header py-2 px-3" style="background:#fff;border-bottom:2px solid #2980b9;">
                <h6 class="mb-0 font-weight-bold" style="color:#2980b9;">
                  <i class="fas fa-clipboard-list mr-2"></i>Exam Information
                </h6>
              </div>
              <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:.88rem;">
                  <tr>
                    <th class="pl-3 text-muted" style="width:38%;border-top:none;font-weight:600;">PEN</th>
                    <td style="border-top:none;">{{ $candidate->entry_number ?? '—' }}</td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Programme</th>
                    <td>{{ $candidate->programme_name ?? '—' }}</td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Hospital</th>
                    <td>{{ $candidate->hospital_name ?? '—' }}</td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Exam Year</th>
                    <td>{{ $candidate->exam_year ?? '—' }}</td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Repeat P1</th>
                    <td>
                      @if(($candidate->repeat_paper_one ?? 'No') === 'Yes')
                        <span class="badge badge-warning" style="color:#333;">Yes</span>
                      @else
                        <span class="text-muted">No</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Repeat P2</th>
                    <td>
                      @if(($candidate->repeat_paper_two ?? 'No') === 'Yes')
                        <span class="badge badge-warning" style="color:#333;">Yes</span>
                      @else
                        <span class="text-muted">No</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">MMed Qualified</th>
                    <td>
                      @if(($candidate->mmed ?? 'No') === 'Yes')
                        <span class="badge badge-success">Yes</span>
                      @else
                        <span class="text-muted">No</span>
                      @endif
                    </td>
                  </tr>
                  @if(!empty($candidate->group_name))
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Group</th>
                    <td>{{ $candidate->group_name }}</td>
                  </tr>
                  @endif
                </table>
              </div>
            </div>
          </div>

          {{-- Payment Info --}}
          <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="border-radius:8px;">
              <div class="card-header py-2 px-3" style="background:#fff;border-bottom:2px solid #27ae60;">
                <h6 class="mb-0 font-weight-bold" style="color:#27ae60;">
                  <i class="fas fa-receipt mr-2"></i>Payment Information
                </h6>
              </div>
              <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:.88rem;">
                  <tr>
                    <th class="pl-3 text-muted" style="width:38%;border-top:none;font-weight:600;">Invoice #</th>
                    <td style="border-top:none;">{{ $candidate->invoice_number ?? '—' }}</td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Invoice Date</th>
                    <td>{{ !empty($candidate->invoice_date) ? \Carbon\Carbon::parse($candidate->invoice_date)->format('d M Y') : '—' }}</td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Invoice Amount</th>
                    <td>
                      @if(!empty($candidate->invoice_amount))
                        <strong>${{ number_format($candidate->invoice_amount) }}</strong>
                      @else —
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Invoice Status</th>
                    <td>
                      @if(($candidate->invoice_status ?? '') === 'Sent')
                        <span class="badge badge-info">Sent</span>
                      @else
                        <span class="badge badge-secondary">Pending</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Fee Paid</th>
                    <td>
                      @if($feePaid === 'Yes')
                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Yes</span>
                      @else
                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>No</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Amount Paid</th>
                    <td>
                      @if(!empty($candidate->amount_paid))
                        <strong>${{ number_format($candidate->amount_paid) }}</strong>
                      @else —
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Payment Date</th>
                    <td>{{ !empty($candidate->payment_date) ? \Carbon\Carbon::parse($candidate->payment_date)->format('d M Y') : '—' }}</td>
                  </tr>
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Mode of Payment</th>
                    <td>{{ $candidate->mode_of_payment ?? '—' }}</td>
                  </tr>
                  @if(!empty($candidate->sponsor))
                  <tr>
                    <th class="pl-3 text-muted" style="font-weight:600;">Sponsor</th>
                    <td>{{ $candidate->sponsor }}</td>
                  </tr>
                  @endif
                </table>
              </div>
            </div>
          </div>

          {{-- Remarks --}}
          @if(!empty($candidate->remarks))
          <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100" style="border-radius:8px;">
              <div class="card-header py-2 px-3" style="background:#fff;border-bottom:2px solid #f39c12;">
                <h6 class="mb-0 font-weight-bold" style="color:#f39c12;">
                  <i class="fas fa-comment-alt mr-2"></i>Remarks / Notes
                </h6>
              </div>
              <div class="card-body">
                <p class="mb-0" style="font-size:.88rem; color:#555;">{{ $candidate->remarks }}</p>
              </div>
            </div>
          </div>
          @endif

        </div>{{-- end cards row --}}

        @else
        <div class="alert alert-warning">Candidate not found.</div>
        @endif

      </div>
    </section>
  </div>
</div>
@endsection

@push('styles')
<style>
  .table th, .table td { padding: .5rem .75rem; }
  .card { border-radius: 8px; }
  .card-header { border-radius: 8px 8px 0 0 !important; }
</style>
@endpush

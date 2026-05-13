@extends('layout.app')

@push('styles')
<style>
  /* Fix textarea being clipped by the 48px height rule in wizard.css */
  .multi_step_form #msform fieldset textarea.form-control {
    height: auto;
    min-height: 80px;
    line-height: 1.5;
    padding-top: 10px;
    padding-bottom: 10px;
  }
  /* Step counter labels */
  .multi_step_form #msform #progressbar li:nth-child(1):before { content: "1"; font-family: inherit; font-size: 20px; font-weight: 700; }
  .multi_step_form #msform #progressbar li:nth-child(2):before { content: "2"; font-family: inherit; font-size: 20px; font-weight: 700; }
  .multi_step_form #msform #progressbar li:nth-child(3):before { content: "3"; font-family: inherit; font-size: 20px; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="wrapper">
  <div class="content-wrapper">
    <section class="content">
      <section class="multi_step_form">
        <form id="msform" method="POST" action="{{ url('admin/associates/candidates/add') }}">
          @csrf

          <div class="tittle">
            <h2>Add New Candidate</h2>
          </div>

          <ul id="progressbar">
            <li class="active">Personal Info</li>
            <li>Exam Details</li>
            <li>Payment</li>
          </ul>

          {{-- ── Step 1: Personal Information ────────────────────────────────── --}}
          <fieldset>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label>First Name <span class="text-danger">*</span></label>
                <input type="text" name="firstname" class="form-control" placeholder="First name" required>
              </div>
              <div class="form-group col-md-4">
                <label>Middle Name</label>
                <input type="text" name="middlename" class="form-control" placeholder="Middle name">
              </div>
              <div class="form-group col-md-4">
                <label>Surname <span class="text-danger">*</span></label>
                <input type="text" name="lastname" class="form-control" placeholder="Last name" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Personal Email <span class="text-danger">*</span></label>
                <input type="email" name="personal_email" class="form-control" placeholder="Personal email" required>
              </div>
              <div class="form-group col-md-6">
                <label>Gender</label>
                <select name="gender" class="form-control">
                  <option value="" disabled selected>Select Gender</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>SFS Login Email</label>
                <input type="text" name="email" class="form-control" placeholder="System login email (optional)">
              </div>
              <div class="form-group col-md-6">
                <label>SFS Password</label>
                <input type="text" name="password" class="form-control" placeholder="System password (optional)">
              </div>
            </div>

            {{-- No Back on step 1 --}}
            <button type="button" class="next action-button">Continue &rarr;</button>
          </fieldset>

          {{-- ── Step 2: Exam Details ─────────────────────────────────────────── --}}
          <fieldset>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Programme / Exam Type <span class="text-danger">*</span></label>
                <select name="programme_id" class="form-control" required>
                  <option value="" disabled selected>Select Programme</option>
                  @foreach($getProgramme as $programme)
                    <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-6">
                <label>Hospital <span class="text-danger">*</span></label>
                <select name="hospital_id" class="form-control" required>
                  <option value="" disabled selected>Select Hospital</option>
                  @foreach($getHospital as $hospital)
                    <option value="{{ $hospital->id }}">{{ $hospital->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Country <span class="text-danger">*</span></label>
                <select name="country_id" class="form-control" required>
                  <option value="" disabled selected>Select Country</option>
                  @foreach($getCountry as $country)
                    <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-6">
                <label>PE Number <span class="text-danger">*</span></label>
                <input type="text" name="entry_number" class="form-control" placeholder="e.g. TZ/2026/01" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Candidate Number</label>
                <input type="text" name="candidate_id" class="form-control" placeholder="e.g. MCS077 (set by Examination Officer)">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Exam Number</label>
                <input type="text" name="exam_number" class="form-control" placeholder="Exam number (if assigned)">
              </div>
              <div class="form-group col-md-6">
                <label>MMed Qualification</label>
                <select name="mmed" class="form-control">
                  <option value="No">No</option>
                  <option value="Yes">Yes</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-4">
                <label>Repeat Paper I</label>
                <select name="repeat_paper_one" class="form-control">
                  <option value="No">No</option>
                  <option value="Yes">Yes</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label>Repeat Paper II</label>
                <select name="repeat_paper_two" class="form-control">
                  <option value="No">No</option>
                  <option value="Yes">Yes</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label>Admission Year</label>
                <select name="admission_year" class="form-control">
                  @for($y = 2020; $y <= 2035; $y++)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                  @endfor
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Exam Year</label>
                <select name="exam_year" class="form-control">
                  @for($y = 2024; $y <= 2035; $y++)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-group col-md-6">
                <label>Sponsor</label>
                <input type="text" name="sponsor" class="form-control" placeholder="Sponsoring organisation">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-12">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="2" placeholder="Any additional notes…"></textarea>
              </div>
            </div>

            <button type="button" class="previous action-button previous_button">&larr; Back</button>
            <button type="button" class="next action-button">Continue &rarr;</button>
          </fieldset>

          {{-- ── Step 3: Payment Records ──────────────────────────────────────── --}}
          <fieldset>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control" placeholder="e.g. INV/EF/2026/001">
              </div>
              <div class="form-group col-md-6">
                <label>Invoice Date</label>
                <input type="date" name="invoice_date" class="form-control">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Invoice Amount (USD)</label>
                <input type="number" name="invoice_amount" class="form-control" placeholder="e.g. 800" min="0">
              </div>
              <div class="form-group col-md-6">
                <label>Invoice Status</label>
                <select name="invoice_status" class="form-control">
                  <option value="Pending">Pending</option>
                  <option value="Sent">Sent</option>
                  <option value="Complete">Complete</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-4">
                <label>Fee Paid</label>
                <select name="fee_paid" class="form-control">
                  <option value="No">No</option>
                  <option value="Yes">Yes</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label>Amount Paid (USD)</label>
                <input type="number" name="amount_paid" class="form-control" placeholder="e.g. 800" min="0">
              </div>
              <div class="form-group col-md-4">
                <label>Payment Date</label>
                <input type="date" name="payment_date" class="form-control">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-12">
                <label>Mode of Payment</label>
                <input type="text" name="mode_of_payment" class="form-control" placeholder="e.g. Bank Transfer, Cheque">
              </div>
            </div>

            <button type="button" class="previous action-button previous_button">&larr; Back</button>
            <button type="submit" class="action-button">
              <i class="fas fa-save mr-1"></i> Save Candidate
            </button>
          </fieldset>

        </form>
      </section>
    </section>
  </div>
</div>
@endsection

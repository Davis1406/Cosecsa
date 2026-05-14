@extends('layout.app')

@push('styles')
<style>
  .multi_step_form #msform fieldset textarea.form-control {
    height: auto; min-height: 80px; line-height: 1.5; padding-top: 10px; padding-bottom: 10px;
  }
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
                <form id="msform" method="POST" action="{{ url('admin/associates/candidates/edit/'.$candidate->candidates_id) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="tittle">
                        <h2>Edit Candidate</h2>
                    </div>
                    <ul id="progressbar">
                        <li class="active">Personal Information</li>
                        <li>Candidate Details</li>
                        <li>Payment Records</li>
                    </ul>
                    <!-- Step 1 -->
                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>First Name</label>
                                <input type="text" name="firstname" class="form-control" value="{{ $candidate->firstname }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Middle Name</label>
                                <input type="text" name="middlename" class="form-control" value="{{ $candidate->middlename }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Surname</label>
                                <input type="text" name="lastname" class="form-control" value="{{ $candidate->lastname }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="personal_email" class="form-control" value="{{ $candidate->personal_email }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>SFS Username</label>
                                <input type="text" name="email" class="form-control" value="{{ $candidate->user_email }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>SFS Password <small class="text-muted">(leave blank to keep current)</small></label>
                                <input type="password" name="password" class="form-control" value="" autocomplete="new-password" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="">Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Male" {{ $candidate->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $candidate->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Associate Type</label>
                                <select name="user_type" class="form-control" required>
                                    <option value="" disabled>Select Type...</option>
                                    <option value="2" {{ $candidate->user_type == 2 ? 'selected' : '' }}>Trainee</option>
                                    <option value="3" {{ $candidate->user_type == 3 ? 'selected' : '' }}>Candidate</option>
                                    <option value="4" {{ $candidate->user_type == 4 ? 'selected' : '' }}>Programme Director</option>
                                    <option value="5" {{ $candidate->user_type == 5 ? 'selected' : '' }}>Trainer</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group col-md-">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="upload" name="profile_image">
                                <label class="custom-file-label" for="upload">
                                    <i class="ion-android-cloud-outline"></i>Upload Profile Image
                                </label>
                            </div>
                        </div>

                        {{-- No Back on step 1 --}}
                        <button type="button" class="next action-button">Continue &rarr;</button>
                    </fieldset>

                    <!-- Step 2 -->
                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Programme Name</label>
                                <select name="programme_id" class="form-control" required>
                                    <option value="" disabled>Select Programme</option>
                                    @foreach($getProgramme as $programme)
                                        <option value="{{ $programme->id }}" {{ $candidate->programme_id == $programme->id ? 'selected' : '' }}>{{ $programme->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Hospital Name</label>
                                <select name="hospital_id" class="form-control" required>
                                    <option value="" disabled>Select Hospital</option>
                                    @foreach($getHospital as $hospital)
                                        <option value="{{ $hospital->id }}" {{ $candidate->hospital_id == $hospital->id ? 'selected' : '' }}>{{ $hospital->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Country</label>
                                <select name="country_id" class="form-control" required>
                                    <option value="" disabled>Select Country</option>
                                    @foreach($getCountry as $country)
                                        <option value="{{ $country->id }}" {{ $candidate->country_id == $country->id ? 'selected' : '' }}>{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>PE Number</label>
                                <input type="text" name="entry_number" class="form-control" value="{{ $candidate->entry_number }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Candidate Number</label>
                                <input type="text" name="candidate_id" class="form-control"
                                       value="{{ $candidate->candidate_id }}"
                                       placeholder="e.g. MCS077 (set by Examination Officer)">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Exam Number</label>
                                <input type="text" name="exam_number" class="form-control"
                                       value="{{ $candidate->exam_number }}"
                                       placeholder="Exam number (if assigned)">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Repeating Paper I</label>
                                <select name="repeat_paper_one" class="form-control">
                                    <option value="No"  {{ ($candidate->repeat_paper_one ?? 'No') == 'No'  ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ ($candidate->repeat_paper_one ?? 'No') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Repeating Paper II</label>
                                <select name="repeat_paper_two" class="form-control">
                                    <option value="No"  {{ ($candidate->repeat_paper_two ?? 'No') == 'No'  ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ ($candidate->repeat_paper_two ?? 'No') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>MMed Qualification</label>
                                <select name="mmed" class="form-control">
                                    <option value="No"  {{ ($candidate->mmed ?? 'No') == 'No'  ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ ($candidate->mmed ?? 'No') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Admission Year</label>
                                <select name="admission_year" class="form-control">
                                    @for($y = 2010; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $candidate->admission_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Exam Year</label>
                                <select name="exam_year" class="form-control">
                                    @for($y = 2024; $y <= date('Y') + 2; $y++)
                                    <option value="{{ $y }}" {{ $candidate->exam_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <button type="button" class="previous action-button previous_button">&larr; Back</button>
                        <button type="button" class="next action-button">Continue &rarr;</button>
                    </fieldset>

                    <fieldset>
                        {{-- Invoice Info --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Invoice Number</label>
                                <input type="text" name="invoice_number" class="form-control" value="{{ $candidate->invoice_number }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Invoice Date</label>
                                <input type="date" name="invoice_date" class="form-control" value="{{ $candidate->invoice_date }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Invoice Amount (USD)</label>
                                <input type="number" name="invoice_amount" class="form-control" step="0.01" min="0"
                                       value="{{ $candidate->invoice_amount }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Invoice Status</label>
                                <select name="invoice_status" class="form-control">
                                    <option value="Pending"  {{ ($candidate->invoice_status ?? '') == 'Pending'  ? 'selected' : '' }}>Pending</option>
                                    <option value="Sent"     {{ ($candidate->invoice_status ?? '') == 'Sent'     ? 'selected' : '' }}>Sent</option>
                                    <option value="Complete" {{ ($candidate->invoice_status ?? '') == 'Complete' ? 'selected' : '' }}>Complete</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Sponsor</label>
                                <input type="text" name="sponsor" class="form-control" value="{{ $candidate->sponsor }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Fee Paid</label>
                                <select name="fee_paid" class="form-control">
                                    <option value="No"  {{ ($candidate->fee_paid ?? 'No') == 'No'  ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ ($candidate->fee_paid ?? 'No') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </div>

                        {{-- Payment Details --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Amount Paid (USD)</label>
                                <input type="number" name="amount_paid" class="form-control" step="0.01" min="0"
                                       value="{{ $candidate->amount_paid }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Date Paid</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ $candidate->payment_date }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Mode of Payment</label>
                                <select name="mode_of_payment" class="form-control">
                                    <option value="">— Select Mode —</option>
                                    <option value="Country Rep"     {{ ($candidate->mode_of_payment ?? '') == 'Country Rep'     ? 'selected' : '' }}>Country Rep</option>
                                    <option value="Bank Transfer"   {{ ($candidate->mode_of_payment ?? '') == 'Bank Transfer'   ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="Online Payment"  {{ ($candidate->mode_of_payment ?? '') == 'Online Payment'  ? 'selected' : '' }}>Online Payment</option>
                                    <option value="Sponsor"         {{ ($candidate->mode_of_payment ?? '') == 'Sponsor'         ? 'selected' : '' }}>Sponsor</option>
                                    <option value="Other"           {{ ($candidate->mode_of_payment ?? '') == 'Other'           ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Remarks / Notes</label>
                                <textarea name="remarks" class="form-control" rows="3"
                                          placeholder="Any additional notes…">{{ $candidate->remarks }}</textarea>
                            </div>
                        </div>

                        <button type="button" class="previous action-button previous_button">&larr; Back</button>
                        <button type="submit" class="action-button"><i class="fas fa-save mr-1"></i> Save Changes</button>
                    </fieldset>
                </form>
            </section>
        </section>
    </div>
</div>
@endsection

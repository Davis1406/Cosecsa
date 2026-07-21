@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content">
            <section class="multi_step_form">
                <form id="msform" method="POST" action="{{ url('admin/associates/trainees/edit/'.$trainee->trainee_id) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="tittle">
                        <h2>Edit Trainee</h2>
                    </div>
                    <ul id="progressbar">
                        <li class="active">Personal Information</li>
                        <li>Admission Details</li>
                        <li>Payment Records</li>
                    </ul>

                    {{-- ── Step 1: Personal Information ── --}}
                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>First Name <span class="text-danger">*</span></label>
                                <input type="text" name="firstname" class="form-control" value="{{ $trainee->firstname }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Middle Name</label>
                                <input type="text" name="middlename" class="form-control" value="{{ $trainee->middlename }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Surname <span class="text-danger">*</span></label>
                                <input type="text" name="lastname" class="form-control" value="{{ $trainee->lastname }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Personal Email</label>
                                <input type="email" name="personal_email" class="form-control" value="{{ $trainee->personal_email }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="">Select Gender</option>
                                    <option value="Male"   {{ $trainee->gender == 'Male'   ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $trainee->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>SFS Username</label>
                                <input type="text" name="email" class="form-control" value="{{ $trainee->user_email }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>SFS Password <small class="text-muted">(leave blank to keep current)</small></label>
                                <input type="password" name="password" class="form-control" value="" autocomplete="new-password" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Associate Type</label>
                                <select name="user_type" class="form-control">
                                    <option value="2" {{ $trainee->user_type == 2 ? 'selected' : '' }}>Trainee</option>
                                    <option value="3" {{ $trainee->user_type == 3 ? 'selected' : '' }}>Programme Director</option>
                                    <option value="4" {{ $trainee->user_type == 4 ? 'selected' : '' }}>Trainer</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Profile Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="upload" name="profile_image" accept="image/*">
                                    <label class="custom-file-label" for="upload">Choose image…</label>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="action-button previous_button">Back</button>
                        <button type="button" class="next action-button">Continue</button>
                    </fieldset>

                    {{-- ── Step 2: Admission Details ── --}}
                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Programme <span class="text-danger">*</span></label>
                                <select name="programme_id" id="programme_id" class="form-control" required>
                                    <option value="">Select Programme</option>
                                    @foreach($getProgramme as $programme)
                                        <option value="{{ $programme->id }}" {{ $trainee->programme_id == $programme->id ? 'selected' : '' }}>{{ $programme->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Hospital <span class="text-danger">*</span></label>
                                <select name="hospital_id" class="form-control" required>
                                    <option value="">Select Hospital</option>
                                    @foreach($getHospital as $hospital)
                                        <option value="{{ $hospital->id }}" {{ $trainee->hospital_id == $hospital->id ? 'selected' : '' }}>{{ $hospital->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Country <span class="text-danger">*</span></label>
                                <select name="country_id" class="form-control" required>
                                    <option value="">Select Country</option>
                                    @foreach($getCountry as $country)
                                        <option value="{{ $country->id }}" {{ $trainee->country_id == $country->id ? 'selected' : '' }}>{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>PE / Entry Number</label>
                                <input type="text" name="entry_number" class="form-control" value="{{ $trainee->entry_number }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Admission Year</label>
                                <select name="admission_year" class="form-control">
                                    <option value="">Select Year</option>
                                    @for($y = 2009; $y <= 2030; $y++)
                                        <option value="{{ $y }}" {{ $trainee->admission_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Exam Year</label>
                                <select name="exam_year" class="form-control">
                                    <option value="">Select Year</option>
                                    @for($y = 2020; $y <= 2035; $y++)
                                        <option value="{{ $y }}" {{ $trainee->exam_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Programme Duration</label>
                                <select name="programme_period" class="form-control">
                                    <option value="1" {{ $trainee->programme_period == 1 ? 'selected' : '' }}>1 Year</option>
                                    <option value="2" {{ $trainee->programme_period == 2 ? 'selected' : '' }}>2 Years</option>
                                    <option value="3" {{ $trainee->programme_period == 3 ? 'selected' : '' }}>3 Years</option>
                                    <option value="4" {{ $trainee->programme_period == 4 ? 'selected' : '' }}>4 Years</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Study Year (Current)</label>
                                <select name="training_year" id="training_year" class="form-control">
                                    <option value="">Select Study Year</option>
                                    @foreach($getStudyYear as $sy)
                                        <option value="{{ $sy->id }}"
                                            data-prog="{{ $sy->programme_id }}"
                                            {{ $trainee->training_year == $sy->id ? 'selected' : '' }}>
                                            {{ $sy->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Trainee Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active"   {{ $trainee->status == 'Active'   ? 'selected' : '' }}>Active</option>
                                    <option value="Enrolled" {{ $trainee->status == 'Enrolled' ? 'selected' : '' }}>Enrolled</option>
                                    <option value="Approved" {{ $trainee->status == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Graduated"{{ $trainee->status == 'Graduated'? 'selected' : '' }}>Graduated</option>
                                    <option value="Deffered" {{ $trainee->status == 'Deffered' ? 'selected' : '' }}>Deferred</option>
                                    <option value="Inactive" {{ $trainee->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Admission Letter Status</label>
                                <select name="admission_letter_status" class="form-control">
                                    <option value="Pending" {{ $trainee->admission_letter_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Sent"    {{ $trainee->admission_letter_status == 'Sent'    ? 'selected' : '' }}>Sent</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Invitation Letter Status</label>
                                <select name="invitation_letter_status" class="form-control">
                                    <option value="Pending" {{ $trainee->invitation_letter_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Sent"    {{ $trainee->invitation_letter_status == 'Sent'    ? 'selected' : '' }}>Sent</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>SFS / e-Logbook Username</label>
                                <input type="text" name="sfs_username" class="form-control" value="{{ $trainee->sfs_username }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>SFS / e-Logbook Password</label>
                                <input type="text" name="sfs_password" class="form-control" value="{{ $trainee->sfs_password }}">
                                <small class="text-muted">Used to merge into the Invitation Letter — not the trainee's MIS login.</small>
                            </div>
                        </div>

                        <button type="button" class="action-button previous previous_button">Back</button>
                        <button type="button" class="next action-button">Continue</button>
                    </fieldset>

                    {{-- ── Step 3: Payment Records ── --}}
                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Invoice Number</label>
                                <input type="text" name="invoice_number" class="form-control" value="{{ $trainee->invoice_number }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Invoice Date</label>
                                <input type="date" name="invoice_date" class="form-control" value="{{ $trainee->invoice_date }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Invoice Status</label>
                                <select name="invoice_status" class="form-control">
                                    <option value="Pending" {{ $trainee->invoice_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Sent"    {{ $trainee->invoice_status == 'Sent'    ? 'selected' : '' }}>Sent</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sponsor</label>
                                <input type="text" name="sponsor" class="form-control" value="{{ $trainee->sponsor }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Mode of Payment</label>
                                <select name="mode_of_payment" class="form-control">
                                    <option value="">Select Mode</option>
                                    <option value="Country Rep"           {{ $trainee->mode_of_payment == 'Country Rep'           ? 'selected' : '' }}>Country Rep</option>
                                    <option value="Bank transfer"         {{ $trainee->mode_of_payment == 'Bank transfer'         ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="Online Payment System" {{ $trainee->mode_of_payment == 'Online Payment System' ? 'selected' : '' }}>Online Payment System</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Amount Paid (USD)</label>
                                <input type="number" step="0.01" name="amount_paid" class="form-control" value="{{ $trainee->amount_paid }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Date Paid</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ $trainee->payment_date }}">
                            </div>
                        </div>

                        <button type="button" class="action-button previous previous_button">Back</button>
                        <button type="submit" class="action-button">Save Changes</button>
                    </fieldset>
                </form>
            </section>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Filter study year options based on selected programme
    function filterStudyYears() {
        var progId = String($('#programme_id').val());
        var current = String($('#training_year').val());
        $('#training_year option').each(function () {
            var opt = $(this);
            if (!opt.val()) return; // keep blank option
            var optProg = String(opt.data('prog'));
            if (!progId || optProg === progId) {
                opt.show();
            } else {
                opt.hide();
                if (opt.is(':selected')) opt.prop('selected', false);
            }
        });
        // restore pre-selected if still visible
        if (current) {
            var target = $('#training_year option[value="' + current + '"]');
            if (target.is(':visible')) target.prop('selected', true);
        }
    }

    filterStudyYears(); // on load
    $('#programme_id').on('change', filterStudyYears);

    // Custom file label
    $('#upload').on('change', function () {
        var name = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').text(name || 'Choose image…');
    });
});
</script>
@endpush

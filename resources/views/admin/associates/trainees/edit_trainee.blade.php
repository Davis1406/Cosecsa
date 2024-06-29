@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content">
            <section class="multi_step_form">
                <form id="msform" method="POST" action="{{ url('admin/associates/trainees/edit/'.$trainee->trainee_id) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <!-- Tittle -->
                    <div class="tittle">
                        <h2>Edit Trainee</h2>
                    </div>
                    <!-- progressbar -->
                    <ul id="progressbar">
                        <li class="active">Personal Information</li>
                        <li>Admission Details</li>
                        <li>Payment Records</li>
                    </ul>
                    <!-- fieldsets -->
                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>First Name</label>
                                <input type="text" name="firstname" class="form-control" value="{{ $trainee->firstname }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Middle Name</label>
                                <input type="text" name="middlename" class="form-control" value="{{ $trainee->middlename }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Surname</label>
                                <input type="text" name="lastname" class="form-control" value="{{ $trainee->lastname }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="personal_email" class="form-control" value="{{ $trainee->personal_email }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>SFS Username</label>
                                <input type="text" name="email" class="form-control" value="{{ $trainee->user_email }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>SFS Password</label>
                                <input type="text" name="password" class="form-control" value="{{ $trainee->user_password }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="">Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Male" {{ $trainee->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $trainee->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Associate Type</label>
                                <select name="user_type" class="form-control" required>
                                    <option value="" disabled>Select Type...</option>
                                    <option value="2" {{ $trainee->user_type == 2 ? 'selected' : '' }}>Trainee</option>
                                    <option value="3" {{ $trainee->user_type == 3 ? 'selected' : '' }}>Programme Director</option>
                                    <option value="4" {{ $trainee->user_type == 4 ? 'selected' : '' }}>Trainer</option>
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

                        <button type="button" class="action-button previous_button">Back</button>
                        <button type="button" class="next action-button">Continue</button>
                    </fieldset>

                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Programme Name</label>
                                <select name="programme_id" class="form-control" required>
                                    <option value="" disabled>Select Programme</option>
                                    @foreach($getProgramme as $programme)
                                        <option value="{{ $programme->id }}" {{ $trainee->programme_id == $programme->id ? 'selected' : '' }}>{{ $programme->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Hospital Name</label>
                                <select name="hospital_id" class="form-control" required>
                                    <option value="" disabled>Select Hospital</option>
                                    @foreach($getHospital as $hospital)
                                        <option value="{{ $hospital->id }}" {{ $trainee->hospital_id == $hospital->id ? 'selected' : '' }}>{{ $hospital->name }}</option>
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
                                        <option value="{{ $country->id }}" {{ $trainee->country_id == $country->id ? 'selected' : '' }}>{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>PE Number</label>
                                <input type="text" name="entry_number" class="form-control" value="{{ $trainee->entry_number }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Admission Letter Status</label>
                                <select name="admission_letter_status" class="form-control">
                                    <option value="0" {{ $trainee->admission_letter_status == 0 ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ $trainee->admission_letter_status == 1 ? 'selected' : '' }}>Sent</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Invitation Letter Status</label>
                                <select name="invitation_letter_status" class="form-control">
                                    <option value="0" {{ $trainee->invitation_letter_status == 0 ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ $trainee->invitation_letter_status == 1 ? 'selected' : '' }}>Sent</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Admission Year</label>
                                <select name="admission_year" class="form-control">
                                    <option value="2024" {{ $trainee->admission_year == 2024 ? 'selected' : '' }}>2024</option>
                                    <option value="2025" {{ $trainee->admission_year == 2025 ? 'selected' : '' }}>2025</option>
                                    <option value="2026" {{ $trainee->exam_year == 2026 ? 'selected' : '' }}>2026</option>
                                    <option value="2027" {{ $trainee->exam_year == 2027 ? 'selected' : '' }}>2027</option>
                                    <option value="2028" {{ $trainee->exam_year == 2028 ? 'selected' : '' }}>2028</option>
                                    <option value="2029" {{ $trainee->exam_year == 2029 ? 'selected' : '' }}>2029</option>
                                    <option value="2030" {{ $trainee->exam_year == 2030 ? 'selected' : '' }}>2030</option>
                                    <option value="2031" {{ $trainee->exam_year == 2031 ? 'selected' : '' }}>2031</option>
                                    <option value="2032" {{ $trainee->exam_year == 2032 ? 'selected' : '' }}>2032</option>
                                    <option value="2033" {{ $trainee->exam_year == 2033 ? 'selected' : '' }}>2033</option>
                                    <option value="2034" {{ $trainee->exam_year == 2034 ? 'selected' : '' }}>2034</option>
                                    <option value="2035" {{ $trainee->exam_year == 2035 ? 'selected' : '' }}>2035</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Exam Year</label>
                                <select name="exam_year" class="form-control">
                                    <option value="2024" {{ $trainee->exam_year == 2024 ? 'selected' : '' }}>2024</option>
                                    <option value="2025" {{ $trainee->exam_year == 2025 ? 'selected' : '' }}>2025</option>
                                    <option value="2026" {{ $trainee->exam_year == 2026 ? 'selected' : '' }}>2026</option>
                                    <option value="2027" {{ $trainee->exam_year == 2027 ? 'selected' : '' }}>2027</option>
                                    <option value="2028" {{ $trainee->exam_year == 2028 ? 'selected' : '' }}>2028</option>
                                    <option value="2029" {{ $trainee->exam_year == 2029 ? 'selected' : '' }}>2029</option>
                                    <option value="2030" {{ $trainee->exam_year == 2030 ? 'selected' : '' }}>2030</option>
                                    <option value="2031" {{ $trainee->exam_year == 2031 ? 'selected' : '' }}>2031</option>
                                    <option value="2032" {{ $trainee->exam_year == 2032 ? 'selected' : '' }}>2032</option>
                                    <option value="2033" {{ $trainee->exam_year == 2033 ? 'selected' : '' }}>2033</option>
                                    <option value="2034" {{ $trainee->exam_year == 2034 ? 'selected' : '' }}>2034</option>
                                    <option value="2035" {{ $trainee->exam_year == 2035 ? 'selected' : '' }}>2035</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Programme Duration</label>
                                <select name="programme_period" class="form-control">
                                    <option value="1" {{ $trainee->programme_period == 1 ? 'selected' : '' }}>1 Year</option>
                                    <option value="2" {{ $trainee->programme_period == 2 ? 'selected' : '' }}>2 Years</option>
                                    <option value="2" {{ $trainee->programme_period == 3 ? 'selected' : '' }}>3 Years</option>
                                    <option value="2" {{ $trainee->programme_period == 4 ? 'selected' : '' }}>4 Years</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Trainee Status</label>
                                <input type="text" name="status" class="form-control" value="{{ $trainee->status }}">
                            </div>
                        </div>

                        <button type="button" class="action-button previous previous_button">Back</button>
                        <button type="button" class="next action-button">Continue</button>
                    </fieldset>

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
                                <select class="product_select" name="invoice_status">
                                    <option value="" disabled>Select Status</option>
                                    <option value="1" {{ $trainee->invoice_status == 1 ? 'selected' : '' }}>Pending</option>
                                    <option value="2" {{ $trainee->invoice_status == 2 ? 'selected' : '' }}>Sent</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sponsor</label>
                                <input type="text" name="sponsor" class="form-control" value="{{ $trainee->sponsor }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Mode of Payment</label>
                            <select class="product_select" name="mode_of_payment">
                                <option value="" disabled>Select Mode</option>
                                <option value="Country Rep" {{ $trainee->mode_of_payment == 'Country Rep' ? 'selected' : '' }}>Country Rep</option>
                                <option value="Bank transfer" {{ $trainee->mode_of_payment == 'Bank transfer' ? 'selected' : '' }}>Bank transfer</option>
                                <option value="Online Payment" {{ $trainee->mode_of_payment == 'Online Payment' ? 'selected' : '' }}>Online Payment</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Amount Paid (USD)</label>
                                <input type="text" name="amount_paid" class="form-control" value="{{ $trainee->amount_paid }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Date Paid</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ $trainee->payment_date }}">
                            </div>
                        </div>

                        <button type="button" class="action-button previous previous_button">Back</button>
                        <button type="submit" class="action-button">Submit</button>
                    </fieldset>
                </form>
            </section>
        </section>
    </div>
</div>

<script>
    $(function () {
        bsCustomFileInput.init();
    });
</script>

@endsection

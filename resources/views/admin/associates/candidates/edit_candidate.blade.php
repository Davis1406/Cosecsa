@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content">
            <section class="multi_step_form">
                <form id="msform" method="POST" action="{{ url('admin/associates/candidates/edit/'.$candidate->candidate_id) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <!-- Tittle -->
                    <div class="tittle">
                        <h2>Edit Candidate</h2>
                    </div>
                    <!-- progressbar -->
                    <ul id="progressbar">
                        <li class="active">Personal Information</li>
                        <li> Candidate Details</li>
                        <li>Payment Records</li>
                    </ul>
                    <!-- fieldsets -->
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
                                <label>SFS Password</label>
                                <input type="text" name="password" class="form-control" value="{{ $candidate->user_password }}">
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
                                <label>Repeating Paper One</label>
                                <select name="repeat_paper_one" class="form-control">
                                    <option value="" disabled>Select Status...</option>
                                    <option value="No" {{ $candidate->repeat_paper_one == 'No' ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ $candidate->repeat_paper_one == 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Repeating Paper Two</label>
                                <select name="repeat_paper_two" class="form-control">
                                    <option value="" disabled>Select Status...</option>
                                    <option value="No" {{ $candidate->repeat_paper_two == 'No' ? 'selected' : '' }}></option>
                                    <option value="Yes" {{ $candidate->repeat_paper_two == 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Admission Year</label>
                                <select name="admission_year" class="form-control">
                                    <option value="2012" {{ $candidate->admission_year == 2012 ? 'selected' : '' }}>2012</option>
                                    <option value="2013" {{ $candidate->admission_year == 2013 ? 'selected' : '' }}>2013</option>
                                    <option value="2014" {{ $candidate->admission_year == 2014 ? 'selected' : '' }}>2014</option>
                                    <option value="2015" {{ $candidate->admission_year == 2015 ? 'selected' : '' }}>2015</option>
                                    <option value="2016" {{ $candidate->admission_year == 2016 ? 'selected' : '' }}>2016</option>
                                    <option value="2017" {{ $candidate->admission_year == 2017 ? 'selected' : '' }}>2017</option>
                                    <option value="2018" {{ $candidate->admission_year == 2018 ? 'selected' : '' }}>2018</option>
                                    <option value="2019" {{ $candidate->admission_year == 2019 ? 'selected' : '' }}>2019</option>
                                    <option value="2020" {{ $candidate->admission_year == 2020 ? 'selected' : '' }}>2020</option>
                                    <option value="2021" {{ $candidate->admission_year == 2021 ? 'selected' : '' }}>2021</option>
                                    <option value="2022" {{ $candidate->admission_year == 2022 ? 'selected' : '' }}>2022</option>
                                    <option value="2023" {{ $candidate->admission_year == 2023 ? 'selected' : '' }}>2023</option>
                                    <option value="2024" {{ $candidate->admission_year == 2024 ? 'selected' : '' }}>2024</option>
                                    <option value="2025" {{ $candidate->admission_year == 2025 ? 'selected' : '' }}>2025</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Exam Year</label>
                                <select name="exam_year" class="form-control">
                                    <option value="2024" {{ $candidate->exam_year == 2024 ? 'selected' : '' }}>2024</option>
                                    <option value="2025" {{ $candidate->exam_year == 2025 ? 'selected' : '' }}>2025</option>
                                    <option value="2026" {{ $candidate->exam_year == 2026 ? 'selected' : '' }}>2026</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Mmed Qualification</label>
                                <select name="mmed" class="form-control">
                                    <option value="" disabled>Select Status...</option>
                                    <option value="No" {{ $candidate->mmed == 'No' ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ $candidate->mmed == 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </div>

                        <button type="button" class="action-button previous previous_button">Back</button>
                        <button type="button" class="next action-button">Continue</button>
                    </fieldset>

                    <fieldset>
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
                                <label>Invoice Status</label>
                                <select class="product_select" name="invoice_status">
                                    <option value="" disabled>Select Status</option>
                                    <option value="Pending" {{ $candidate->invoice_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Sent" {{ $candidate->invoice_status == 'Sent' ? 'selected' : '' }}>Sent</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sponsor</label>
                                <input type="text" name="sponsor" class="form-control" value="{{ $candidate->sponsor }}">
                            </div>
                        </div>

                        {{-- <div class="form-group">
                            <label>Mode of Payment</label>
                            <select class="product_select" name="mode_of_payment">
                                <option value="" disabled>Select Mode</option>
                                <option value="Country Rep" {{ $trainee->mode_of_payment == 'Country Rep' ? 'selected' : '' }}>Country Rep</option>
                                <option value="Bank transfer" {{ $trainee->mode_of_payment == 'Bank transfer' ? 'selected' : '' }}>Bank transfer</option>
                                <option value="Online Payment" {{ $trainee->mode_of_payment == 'Online Payment' ? 'selected' : '' }}>Online Payment</option>
                            </select>
                        </div> --}}

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Amount Paid (USD)</label>
                                <input type="text" name="amount_paid" class="form-control" value="{{ $candidate->amount_paid }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Date Paid</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ $candidate->payment_date }}">
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

@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content">
            <section class="multi_step_form">
                <form id="msform" method="POST" action="{{ url('admin/associates/members/add') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    
                    <div class="tittle">
                        <h2>Add A Member</h2>
                    </div>
                    <ul id="progressbar">
                        <li class="active">Personal Information</li>
                        <li>Additional Details</li>
                    </ul>
                    
                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>First Name</label>
                                <input type="text" name="firstname" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Middle Name</label>
                                <input type="text" name="middlename" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Last Name</label>
                                <input type="text" name="lastname" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Gender</label>
                                <input type="text" name="gender" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Member Type</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="" disabled>Select Type...</option>
                                    <option value="1">Member</option>
                                    <option value="2">Member Specialist</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group col-md-12">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="upload" name="profile_image">
                                <label class="custom-file-label" for="upload">
                                    <i class="ion-android-cloud-outline"></i> Upload Profile Image
                                </label>
                            </div>
                        </div>

                        <button type="button" class="action-button previous_button">Back</button>
                        <button type="button" class="next action-button">Continue</button>
                    </fieldset>

                    <fieldset>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Member Status</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="" disabled>Select Status...</option>
                                    <option value="1">Active</option>
                                    <option value="2">Inactive</option>
                                    <option value="2">Deceased</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Personal Email</label>
                                <input type="email" name="personal_email" class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Phone Number</label>
                                <input type="mobile" name="phone_number" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Country</label>
                                <select name="country_id" class="form-control" required>
                                    <option value="" disabled selected>Select Country</option>
                                    @foreach($getCountry as $country)
                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Membership Year</label>
                                <input type="text" name="membership_year" class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Admission Year</label>
                                <input type="text" name="admission_year" class="form-control">
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

<style>
    #progressbar {
        margin-bottom: 30px;
        overflow: hidden;
    }
    #progressbar li {
        list-style-type: none;
        color: #99a2a8;
        font-size: 9px;
        width: calc(100%/2) !important;
        float: left;
        position: relative;
        font: 500 13px/1 $roboto;
    }
    fieldset {
        border: 0;
        padding: 20px 105px 0;
    }
</style>

<script>
    $(function () {
        bsCustomFileInput.init();

        $('.next').click(function() {
            var currentFieldset = $(this).closest('fieldset');
            var isValid = true;
            currentFieldset.find('input, select').each(function() {
                if (!this.checkValidity()) {
                    isValid = false;
                }
            });

            if (isValid) {
                currentFieldset.hide();
                currentFieldset.next('fieldset').show();
            }
        });

        $('.previous').click(function() {
            var currentFieldset = $(this).closest('fieldset');
            currentFieldset.hide();
            currentFieldset.prev('fieldset').show();
        });
    });
</script>
@endsection

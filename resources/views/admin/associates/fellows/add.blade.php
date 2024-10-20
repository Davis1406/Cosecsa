@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content">
            <section class="multi_step_form">
                <form id="msform" method="POST" action="{{ url('admin/associates/fellows/add') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    
                    <div class="tittle">
                        <h2>Add A Fellow</h2>
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
                                <select name="gender" class="form-control">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option> 
                                </select>
                            </div>   
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank if not changing">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Fellowship Type</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="" disabled>Select Type...</option>
                                    <option value="5">Fellow by Examination</option>
                                    <option value="6">Foundation Fellow</option>
                                    <option value="7">Fellow By Election</option>
                                    <option value="8">Honorary Fellow (ASEA)</option>
                                    <option value="9">Overseas Fellow</option>
                                    <option value="10">Honorary Fellow (COSECSA)</option>

                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Fellowship Programme</label>
                                <select name="programme_id" class="form-control" required>
                                    <option value="" disabled>Select Programme...</option>
                                    <option value="1">FCS Cardiothoracic Surgery</option>
                                    <option value="2">FCS General Surgery</option>
                                    <option value="2">FCS Neurosurgery</option>
                                    <option value="2">FCS Orthopaedic Surgery</option>
                                    <option value="2">FCS Otorhinolaryngology</option>
                                    <option value="2">FCS Paediatric Orthopaedic Surgery</option>
                                    <option value="2">FCS Paediatric Surgery</option>
                                    <option value="2">FCS Plastic Surgery</option>
                                    <option value="2">FCS Urologic Surgery</option>
                                    <option value="1">None</option>
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
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Personal Email</label>
                                <input type="text" name="personal_email" class="form-control">
                            </div>
                        </div>
         
                        <div class="form-row">
                             <div class="form-group col-md-6">
                                 <label>Country</label>
                                 <select name="country_id" class="form-control" required>
                                    <option value="">Select Country</option>
                                    @foreach($getCountry as $country)
                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    @endforeach
                                 </select>
                             </div>
                              <div class="form-group col-md-6">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option> 
                                        <option value="Deceased">Deceased</option> 

                                    </select>
                               </div>
                         </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Fellowship Year</label>
                                <input type="text" name="fellowship_year" class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Admission Year</label>
                                <input type="text" name="admission_year" class="form-control">
                            </div>
                        </div>


                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Current Specialty</label>
                                <input type="text" name="current_specialty" class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Current Hospital</label>
                                <input type="text" name="organization" class="form-control">
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

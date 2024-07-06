@extends('layout.app')

@section('content')
<div class="wrapper">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
 
        <!-- Main content -->
        <section class="content">
            <!-- Multi step form -->
            <section class="multi_step_form">
                <form id="msform" method="POST" action="{{ url('admin/associates/trainers/add') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <!-- Tittle -->
                    <div class="tittle">
                        <h2>Add New Programme Director</h2>
                    </div>
                    <!-- progressbar -->
                    <ul id="progressbar">
                        <li class="active">Personal Information</li>
                        <li>Additional Details</li>
                    </ul>
                    <!-- fieldsets -->
                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" placeholder="" required>
                            </div>
                        </div>


                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" placeholder="" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Associate Type</label>
                                    <select name="user_type" class="form-control" required>
                                        <option value="" disabled selected>Select Type...</option>
                                        <option value="2">Trainee</option>
                                        <option value="3">Candidate</option>
                                        <option value="4">Programme Director</option>
                                        <option value="5">Country Representative</option>
                                    </select>
                                </div>
                          </div>


                        <div class="input-group col-md-12">
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
                                <label>Hospital Name</label>
                                <select name="hospital_id" class="form-control" required>
                                    <option value="" disabled selected>Select Hospital</option>
                                    @foreach($getHospital as $hospital)
                                        <option value="{{ $hospital->id }}">{{ $hospital->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                             
                            <div class="form-group col-md-6">
                                <label>Assistant PD Name</label>
                                <input type="text" name="assistant_pd" class="form-control" placeholder="">
                            </div>

                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label>Assistant PD Email</label>
                                <input type="text" name="assistant_email" class="form-control" placeholder="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Mobile Number</label>
                                <input type="text" name="mobile_no" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        <button type="button" class="action-button previous previous_button">Back</button>
                        <button type="submit" class="action-button">Submit</button>
                    </fieldset>

                </form>
            </section>
            <!-- End Multi step form -->
        </section>
        <!-- /.content -->
    </div>
</div>

      <style>
            #progressbar {
               margin-bottom: 30px;
              overflow: hidden;  
             li {
                  list-style-type: none;
                  color: #99a2a8; 
                  font-size: 9px;
                  width: calc(100%/2) !important;
                  float: left;
                  position: relative; 
                  font: 500 13px/1 $roboto;     
                }
            }
            fieldset{
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
                if ($(this).prop('required') && !$(this).val()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            if (isValid) {
                currentFieldset.hide();
                currentFieldset.next().show();
                updateProgressBar();
            }
        });

        $('.previous').click(function() {
            var currentFieldset = $(this).closest('fieldset');
            currentFieldset.hide();
            currentFieldset.prev().show();
            updateProgressBar();
        });

        function updateProgressBar() {
            var activeIndex = $('fieldset:visible').index();
            $('#progressbar li').removeClass('active');
            $('#progressbar li').eq(activeIndex).addClass('active');
        }

        $('fieldset:first').show();
        $('fieldset').not(':first').hide();
    });

</script>

@endsection

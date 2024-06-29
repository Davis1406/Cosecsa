@extends('layout.app')

@section('content')
<div class="wrapper">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
 
        <!-- Main content -->
        <section class="content">
            <!-- Multi step form -->
            <section class="multi_step_form">
                <form id="msform" method="POST" action="{{ url('admin/associates/candidates/add') }}">
                    {{ csrf_field() }}
                    <!-- Tittle -->
                    <div class="tittle">
                        <h2>Add New Candidate</h2>
                    </div>
                    <!-- progressbar -->
                    <ul id="progressbar">
                        <li class="active">Personal Information</li>
                        <li>Exam Details</li>
                        <li>Payment Records</li>
                    </ul>
                    <!-- fieldsets -->
                    <fieldset>
                        <div class="form-row">
                             <div class="form-group col-md-6">
                                 <label>First Name</label>
                                 <input type="text" name="firstname" class="form-control" placeholder="">
                             </div>
                             <div class="form-group col-md-6">
                                 <label>Middle Name</label>
                                 <input type="text"  name="middlename" class="form-control" placeholder="">
                             </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Surname</label>
                                <input type="text" name="lastname" class="form-control" placeholder="" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="personal_email" class="form-control" placeholder="" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>SFS Username</label>
                                <input type="text"  name="email" class="form-control" placeholder="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>SFS Password</label>
                                <input type="text"  name="password" class="form-control" placeholder="">
                            </div>
                       </div>

                     <div class="form-row">

                        <div class="form-group col-md-6">
                            <label for="">Gender</label>
                            <select name="gender" class="form-control">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label>Associate Type</label>
                            <select name="user_type" class="form-control" required>
                                <option value="" disabled selected>Select Type...</option>
                                <option value="2">Trainee</option>
                                <option value="3">Candidate</option>
                                <option value="4">Programme Director</option>
                                <option value="5">Trainer</option>
                            </select>
                        </div>
                      
                     </div>

                     <div class="input-group col-md-6" hidden>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="upload" name="profile_image" >
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
                                <label>Exam Type</label>
                                <select name="programme_id" class="form-control" required>
                                    <option value="" disabled selected>Select Exam</option>
                                    @foreach($getProgramme as $programme)
                                        <option value="{{ $programme->id }}">{{ $programme->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>Hospital Name</label>
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
                                <label>Country</label>
                                <select name="country_id" class="form-control" required>
                                    <option value="" disabled selected>Select Country</option>
                                    @foreach($getCountry as $country)
                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>PE Number</label>
                                <input type="text"  name="entry_number" class="form-control" placeholder="TZ/2000/01" required>
                            </div>

                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label>Repeat P1</label>
                                <select name="repeat_P1" class="form-control">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Repeat P2</label>
                                <select name="repeat_P2" class="form-control">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                </select>
                            </div>

                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label>Admission Year</label>
                                <select name="admission_year" class="form-control">
                                    <option value="2024">2024</option>
                                    <option value="2025">2025</option>
                                    <option value="2026">2026</option>
                                    <option value="2027">2027</option>
                                    <option value="2028">2028</option>
                                    <option value="2029">2029</option>
                                    <option value="2030">2030</option>
                                    <option value="2031">2031</option>
                                    <option value="2032">2032</option>
                                    <option value="2033">2033</option>
                                    <option value="2034">2034</option>
                                    <option value="2035">2035</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Exam Year</label>
                                <select name="exam_year" class="form-control">
                                    <option value="2024">2024</option>
                                    <option value="2025">2025</option>
                                    <option value="2026">2026</option>
                                    <option value="2027">2027</option>
                                    <option value="2028">2028</option>
                                    <option value="2029">2029</option>
                                    <option value="2030">2030</option>
                                    <option value="2031">2031</option>
                                    <option value="2032">2032</option>
                                    <option value="2033">2033</option>
                                    <option value="2034">2034</option>
                                    <option value="2035">2035</option>
                                </select>
                            </div>

                        </div>
                                   
                        <div class="form-row">

                            <div class="form-group col-md-12">
                                <label>Mmed Qualification</label>
                                <select name="mmed" class="form-control">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
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
                                <input type="text" name="invoice_number" class="form-control" placeholder="PE/2024/20">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Invoice Date</label>
                                <input type="date"  name="invoice_date" class="form-control" placeholder="">
                            </div>
                       </div>



                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Invoice Status</label>
                              <select class="product_select" name="invoice_status">
                                 <option value="" disabled selected>Select Status</option>
                                 <option value="1">pending</option>
                                 <option value="2">Sent</option>
                             </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Sponsor</label>
                                <input type="text"  name="sponsor" class="form-control" placeholder="">
                            </div>
                       </div>


                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Amount paid <span>USD</span></label>
                                <input type="text" name="amount_paid" class="form-control" placeholder="">
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

<script>
    $(function () {
        bsCustomFileInput.init();
    });

    
</script>




@endsection


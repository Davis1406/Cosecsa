@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content">
            <section class="multi_step_form">
                <form id="msform" method="POST" action="{{ route('examiner.update', ['id' => $examiner->id]) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    
                    <div class="tittle">
                        <h2>Edit Examiner</h2>
                    </div>
                    <ul id="progressbar">
                        <li class="active">Personal Information</li>
                        <li>Examiner Details</li>
                    </ul>
                    
                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $examiner->examiner_name }}" required>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label>Email</label>
                                <input type="text" name="email" class="form-control" value="{{ $examiner->email }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank if not changing">
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label>Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="" disabled>Select Gender...</option>
                                    <option value="" {{ $examiner->gender == '' ? 'selected' : '' }}> - </option>
                                    <option value="Male" {{ $examiner->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $examiner->gender == 'Female' ? 'selected' : '' }}>Female</option> 
                                </select>
                            </div>   
                        </div>
                
                        <div class="form-row">
                            <div class="input-group col-md-12 col-sm-12">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="upload" name="passport_image">
                                    <label class="custom-file-label" for="upload">
                                        <i class="ion-android-cloud-outline"></i> Upload Profile Image
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="action-button previous_button">Back</button>
                        <button type="button" class="next action-button">Continue</button>
                    </fieldset>

                    <fieldset>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label>Examiner ID</label>
                                <input type="text" name="examiner_id" class="form-control" value="{{ $examiner->examiner_id}}">
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label>Group Name</label>
                                <select name="group_id" class="form-control">
                                    @for ($i = 1; $i <= 15; $i++)
                                        <option value="{{ $i }}" {{ $examiner->group_id == $i ? 'selected' : '' }}>
                                            {{ $groups->firstWhere('id', $i)->group_name ?? 'Group ' . $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>                            
                            
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label>Examiner Specialty</label>
                                <input type="text" name="examinership_year" class="form-control" value="{{ $examiner->specialty }}">
                            </div>

                            <div class="form-group col-md-6 col-sm-12">
                                <label>Shift</label>
                                <select name="shift" class="form-control">
                                    <option value="" disabled>Select Shift...</option>
                                    <option value="Morning" {{ $examiner->shift == 'Morning' ? 'selected' : '' }}>Morning</option>
                                    <option value="Morning & Afternoon" {{ $examiner->shift == 'Morning & Afternoon' ? 'selected' : '' }}>Morning & Afternoon</option> 
                                    <option value="Afternoon" {{ $examiner->shift == 'Afternoon' ? 'selected' : '' }}>Afternoon</option> 
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label>Country</label>
                                <select name="country_id" class="form-control" required>
                                   <option value="">Select Country</option>
                                   @foreach($getCountry as $country)
                                       <option value="{{ $country->id }}" {{ $examiner->country_id == $country->id ? 'selected' : '' }}>{{ $country->country_name }}</option>
                                   @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6 col-sm-12">
                                <label>Phone Number</label>
                                <input type="text" name="mobile" class="form-control" value="{{ $examiner->mobile }}">
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
        padding: 20px 10px;
    }
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .form-group {
        flex: 1 1 100%;
    }
    .col-md-6 {
        flex: 1 1 45%;
    }
    .col-md-12 {
        flex: 1 1 100%;
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

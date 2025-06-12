@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                <section class="multi_step_form">
                    <form id="msform" method="POST" action="{{ route('examiner.update', ['id' => $examiner->id]) }}"
                        enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="tittle">
                            <h2>Edit Examiner</h2>
                        </div>
                        <ul id="progressbar">
                            <li class="active">Personal Information</li>
                            <li>Examiner Details</li>
                            <li>Examiner History</li>
                        </ul>

                        <!-- Personal Information Fieldset -->
                        <fieldset>
                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ $examiner->examiner_name }}" required>
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Email</label>
                                    <input type="text" name="email" class="form-control"
                                        value="{{ $examiner->email }}">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="Leave blank if not changing">
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Gender</label>
                                    <select name="gender" class="form-control">
                                        <option value="" disabled>Select Gender...</option>
                                        <option value="" {{ $examiner->gender == '' ? 'selected' : '' }}> - </option>
                                        <option value="Male" {{ $examiner->gender == 'Male' ? 'selected' : '' }}>Male
                                        </option>
                                        <option value="Female" {{ $examiner->gender == 'Female' ? 'selected' : '' }}>Female
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="input-group col-md-6 col-sm-12 mb-3">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="passport_upload"
                                            name="passport_image" accept="image/*">
                                        <label class="custom-file-label" for="passport_upload">
                                            <i class="ion-android-cloud-outline"></i> Upload Profile Image (JPG/PNG)
                                        </label>
                                    </div>
                                </div>
                                <div class="input-group col-md-6 col-sm-12 mb-3">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="cv_upload"
                                            name="curriculum_vitae" accept=".pdf,.doc,.docx">
                                        <label class="custom-file-label" for="cv_upload">
                                            <i class="ion-android-cloud-outline"></i> Upload CV (PDF/DOC)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="action-button previous_button">Back</button>
                            <button type="button" class="next action-button">Continue</button>
                        </fieldset>

                        <!-- Examiner Details Fieldset -->
                        <fieldset>
                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Examiner ID</label>
                                    <input type="text" name="examiner_id" class="form-control"
                                        value="{{ $examiner->examiner_id }}">
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Group Name</label>
                                    <select name="group_id" class="form-control">
                                        @for ($i = 1; $i <= 15; $i++)
                                            <option value="{{ $i }}"
                                                {{ $examiner->group_id == $i ? 'selected' : '' }}>
                                                {{ $groups->firstWhere('id', $i)->group_name ?? 'Group ' . $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Examiner Specialty</label>
                                    <input type="text" name="specialty" class="form-control"
                                        value="{{ $examiner->specialty }}">
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Examiner Sub-Specialty</label>
                                    <input type="text" name="subspecialty" class="form-control"
                                        value="{{ $examiner->specialty }}">
                                </div>

                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Country</label>
                                    <select name="country_id" class="form-control" required>
                                        <option value="">Select Country</option>
                                        @foreach ($getCountry as $country)
                                            <option value="{{ $country->id }}"
                                                {{ $examiner->country_id == $country->id ? 'selected' : '' }}>
                                                {{ $country->country_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Phone Number</label>
                                    <input type="text" name="mobile" class="form-control"
                                        value="{{ $examiner->mobile }}">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>2025 Exam Availability</label>
                                    <div class="checkbox-group exam-availability-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="exam_availability[]"
                                                id="mcs_2025" value="MCS">
                                            <label class="form-check-label" for="mcs_2025">
                                                MCS (12-13 Nov)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="exam_availability[]"
                                                id="fcs_2025" value="FCS">
                                            <label class="form-check-label" for="fcs_2025">
                                                FCS (1–2 December)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Shift (For MCS)</label>
                                    <select name="shift" class="form-control">
                                        <option value="" disabled
                                            {{ optional($examiner->shift)->shift ? '' : 'selected' }}>Select Shift...
                                        </option>
                                        <option value="1"
                                            {{ optional($examiner->shift)->shift == 1 ? 'selected' : '' }}>Morning
                                        </option>
                                        <option value="2"
                                            {{ optional($examiner->shift)->shift == 2 ? 'selected' : '' }}>Morning &
                                            Afternoon</option>
                                        <option value="3"
                                            {{ optional($examiner->shift)->shift == 3 ? 'selected' : '' }}>Afternoon
                                        </option>
                                    </select>
                                </div>

                            </div>

                            <button type="button" class="action-button previous previous_button">Back</button>
                            <button type="button" class="next action-button">Continue</button>
                        </fieldset>

                        <!-- Examiner History Fieldset -->
                        <fieldset>
                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Have you participated in the virtual MCS examination before?</label>
                                    <div class="radio-group">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                name="virtual_mcs_participated" id="virtual_mcs_yes" value="Yes">
                                            <label class="form-check-label" for="virtual_mcs_yes">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                name="virtual_mcs_participated" id="virtual_mcs_no" value="No">
                                            <label class="form-check-label" for="virtual_mcs_no">No</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Have you participated in the FCS examination in the past?</label>
                                    <div class="radio-group">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="fcs_participated"
                                                id="fcs_yes" value="Yes">
                                            <label class="form-check-label" for="fcs_yes">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="fcs_participated"
                                                id="fcs_no" value="No">
                                            <label class="form-check-label" for="fcs_no">No</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Did you participate as an examiner or observer?</label>
                                    <div class="radio-group">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="participation_type"
                                                id="examiner" value="Examiner">
                                            <label class="form-check-label" for="examiner">Examiner</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="participation_type"
                                                id="observer" value="Observer">
                                            <label class="form-check-label" for="observer">Observer</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Hospital Organization Type</label>
                                    <div class="radio-group">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="hospital_type"
                                                id="teaching_hospital" value="Teaching Hospital">
                                            <label class="form-check-label" for="teaching_hospital">Teaching
                                                Hospital</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="hospital_type"
                                                id="non_teaching" value="Non Teaching">
                                            <label class="form-check-label" for="non_teaching">Non Teaching</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Hospital Name</label>
                                    <input type="text" name="hospital_name" class="form-control"
                                        placeholder="Enter hospital name">
                                </div>

                                <div class="form-group col-md-6 col-sm-12">
                                    <label>Years you have Examined/Observed (2020-2024)</label>
                                    <div class="checkbox-group">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="examination_years[]"
                                                id="year_2020" value="2020">
                                            <label class="form-check-label" for="year_2020">2020</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="examination_years[]"
                                                id="year_2021" value="2021">
                                            <label class="form-check-label" for="year_2021">2021</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="examination_years[]"
                                                id="year_2022" value="2022">
                                            <label class="form-check-label" for="year_2022">2022</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="examination_years[]"
                                                id="year_2023" value="2023">
                                            <label class="form-check-label" for="year_2023">2023</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="examination_years[]"
                                                id="year_2024" value="2024">
                                            <label class="form-check-label" for="year_2024">2024</label>
                                        </div>
                                    </div>
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
            width: calc(100%/3) !important;
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

        .radio-group,
        .checkbox-group {
            margin-top: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            /* spacing between radio/check options */
        }

        .form-check-inline {
            display: flex;
            align-items: center;
            margin-right: 15px;
            margin-bottom: 10px;
        }

        .form-check-input {
            margin-right: 5px;
            /* space between input and label */
        }

        .form-check-label {
            font-weight: normal;
            margin-bottom: 0;
        }

        .exam-availability-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
    </style>


    <script>
        $(function() {
            bsCustomFileInput.init();

            // File input change handlers
            $('#passport_upload').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html('<i class="ion-android-cloud-outline"></i> ' +
                    fileName);
            });

            $('#cv_upload').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html('<i class="ion-android-cloud-outline"></i> ' +
                    fileName);
            });

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

                    // Update progress bar
                    var currentIndex = currentFieldset.index('fieldset');
                    $('#progressbar li').removeClass('active');
                    $('#progressbar li').eq(currentIndex + 1).addClass('active');
                }
            });

            $('.previous').click(function() {
                var currentFieldset = $(this).closest('fieldset');
                currentFieldset.hide();
                currentFieldset.prev('fieldset').show();

                // Update progress bar
                var currentIndex = currentFieldset.index('fieldset');
                $('#progressbar li').removeClass('active');
                $('#progressbar li').eq(currentIndex - 1).addClass('active');
            });
        });
    </script>
@endsection

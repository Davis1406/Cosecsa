@extends('layout.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="form-container">
                    <div class="form-header">
                        <h2>Edit Profile</h2>
                    </div>

                    <div class="progress-container">
                        <div class="progress-bar-container">
                            <div class="progress-line">
                                <div class="progress-line-fill" id="progressFill"></div>
                            </div>
                            <div class="progress-steps">
                                <div class="progress-step active" data-step="1">
                                    <i class="fas fa-user"></i>
                                    <div class="step-label active">Personal Info</div>
                                </div>
                                <div class="progress-step" data-step="2">
                                    <i class="fas fa-id-card"></i>
                                    <div class="step-label">Examiner Details</div>
                                </div>
                                <div class="progress-step" data-step="3">
                                    <i class="fas fa-history"></i>
                                    <div class="step-label">Examiner History</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="examinerForm" method="POST"
                        action="{{ route('examiner.selfUpdate', ['id' => $examiner->id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-content">
                            <!-- Step 1: Personal Information -->
                            <div class="form-step active" data-step="1">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Full Name *</label>
                                            <input type="text" name="name" id="name" class="form-control"
                                                value="{{ $examiner->examiner_name }}" required>
                                            <div class="error-message">Please enter a valid name</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" name="email" id="email" class="form-control"
                                                value="{{ $examiner->email }}">
                                            <div class="error-message">Please enter a valid email</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" name="password" id="password" class="form-control"
                                                placeholder="Will be able to update this during exams" disabled>
                                            <div class="error-message">Password must be at least 6 characters</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gender">Gender</label>
                                            <select name="gender" id="gender" class="form-control">
                                                <option value="">Select Gender</option>
                                                <option value="Male" {{ $examiner->gender == 'Male' ? 'selected' : '' }}>
                                                    Male</option>
                                                <option value="Female"
                                                    {{ $examiner->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                            <div class="error-message">Please select a gender</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Profile Image</label>
                                            <label for="passport_upload"
                                                class="custom-file-upload {{ $examiner->passport_image ? 'has-file' : '' }}">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span id="passportLabel">
                                                    @if ($examiner->passport_image)
                                                        {{ basename($examiner->passport_image) }}
                                                    @else
                                                        Upload Profile Image (JPG/PNG)
                                                    @endif
                                                </span>
                                                <input type="file" id="passport_upload" name="passport_image"
                                                    accept="image/*">
                                            </label>

                                            @if ($examiner->passport_image)
                                                <small class="text-muted">
                                                    Current: {{ basename($examiner->passport_image) }}
                                                </small>
                                            @endif
                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Curriculum Vitae</label>
                                            <label for="cv_upload"
                                                class="custom-file-upload {{ $examiner->curriculum_vitae ? 'has-file' : '' }}">
                                                <i class="fas fa-file-upload"></i>
                                                <span id="cvLabel">
                                                    @if ($examiner->curriculum_vitae)
                                                        {{ basename($examiner->curriculum_vitae) }}
                                                    @else
                                                        Upload CV (PDF/DOC)
                                                    @endif
                                                </span>
                                                <input type="file" id="cv_upload" name="curriculum_vitae"
                                                    accept=".pdf,.doc,.docx">
                                            </label>
                                            @if ($examiner->curriculum_vitae)
                                                <small class="text-muted">Current:
                                                    {{ basename($examiner->curriculum_vitae) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Examiner Details -->
                            <div class="form-step" data-step="2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="examiner_id">Examiner ID</label>
                                            <input type="text" name="examiner_id" id="examiner_id"
                                                class="form-control" value="{{ $examiner->examiner_id }}" disabled>
                                            <div class="error-message">Please enter examiner ID</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="group_id">Group Name</label>
                                            <select name="group_id" id="group_id" class="form-control" disabled>
                                                @for ($i = 1; $i <= 15; $i++)
                                                    <option value="{{ $i }}"
                                                        {{ $examiner->group_id == $i ? 'selected' : '' }}>
                                                        {{ $groups->firstWhere('id', $i)->group_name ?? 'Group ' . $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                            <div class="error-message">Please select a group</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="specialty">Examiner Specialty</label>
                                            <input type="text" name="specialty" id="specialty" class="form-control"
                                                value="{{ $examiner->specialty }}">
                                            <div class="error-message">Please enter specialty</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="subspecialty">Sub - Specialty</label>
                                            <input type="text" name="subspecialty" id="subspecialty"
                                                class="form-control" value="{{ $examiner->subspecialty }}">
                                            <div class="error-message">Please enter Sub specialty</div>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="country_id">Country *</label>
                                            <select name="country_id" id="country_id" class="form-control" required>
                                                <option value="">Select Country</option>
                                                @foreach ($getCountry as $country)
                                                    <option value="{{ $country->id }}"
                                                        {{ $examiner->country_id == $country->id ? 'selected' : '' }}>
                                                        {{ $country->country_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="error-message">Please select a country</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mobile">Phone Number</label>
                                            <input type="tel" name="mobile" id="mobile" class="form-control"
                                                value="{{ $examiner->mobile }}">
                                            <div class="error-message">Please enter a valid phone number</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label>2025 Exam Availability</label>
                                        <div class="checkbox-group exam-availability-group">
                                            @php
                                                $selectedAvailability = [];
                                                if ($examiner->history && $examiner->history->exam_availability) {
                                                    if (is_string($examiner->history->exam_availability)) {
                                                        $selectedAvailability =
                                                            json_decode($examiner->history->exam_availability, true) ?:
                                                            [];
                                                    } elseif (is_array($examiner->history->exam_availability)) {
                                                        $selectedAvailability = $examiner->history->exam_availability;
                                                    }
                                                }
                                            @endphp

                                            <div class="form-check">
                                                <input class="form-check-input exam-option" type="checkbox"
                                                    name="exam_availability[]" id="mcs_2025" value="MCS"
                                                    {{ in_array('MCS', $selectedAvailability) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="mcs_2025">
                                                    MCS (12–13 Nov)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input exam-option" type="checkbox"
                                                    name="exam_availability[]" id="fcs_2025" value="FCS"
                                                    {{ in_array('FCS', $selectedAvailability) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="fcs_2025">
                                                    FCS (1–2 December)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    name="exam_availability[]" id="not_available" value="Not Available"
                                                    {{ in_array('Not Available', $selectedAvailability) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="not_available">
                                                    Not Available
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 col-sm-12">
                                        <label>Shift (For MCS)</label>
                                        <select name="shift" class="form-control">
                                            <option value="" disabled {{ !$examiner->shift_id ? 'selected' : '' }}>
                                                Select Shift...
                                            </option>
                                            <option value="1" {{ $examiner->shift_id == 1 ? 'selected' : '' }}>
                                                Morning
                                            </option>
                                            <option value="2" {{ $examiner->shift_id == 2 ? 'selected' : '' }}>
                                                Morning & Afternoon
                                            </option>
                                            <option value="3" {{ $examiner->shift_id == 3 ? 'selected' : '' }}>
                                                Afternoon
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Examiner History -->
                            <div class="form-step" data-step="3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Have you participated in the virtual MCS examination before?</label>
                                            <div class="radio-group">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="virtual_mcs_participated" id="virtual_mcs_yes"
                                                        value="Yes"
                                                        {{ $examiner->virtual_mcs_participated == 'Yes' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="virtual_mcs_yes">Yes</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="virtual_mcs_participated" id="virtual_mcs_no"
                                                        value="No"
                                                        {{ $examiner->virtual_mcs_participated == 'No' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="virtual_mcs_no">No</label>
                                                </div>
                                            </div>
                                            <div class="error-message">Please select an option</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Have you participated in the FCS examination in the past?</label>
                                            <div class="radio-group">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="fcs_participated" id="fcs_yes" value="Yes"
                                                        {{ $examiner->fcs_participated == 'Yes' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="fcs_yes">Yes</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="fcs_participated" id="fcs_no" value="No"
                                                        {{ $examiner->fcs_participated == 'No' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="fcs_no">No</label>
                                                </div>
                                            </div>
                                            <div class="error-message">Please select an option</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Did you participate as an examiner or observer?</label>
                                            <div class="radio-group">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="participation_type" id="examiner" value="Examiner"
                                                        {{ $examiner->role_id == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="examiner">Examiner</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="participation_type" id="observer" value="Observer"
                                                        {{ $examiner->role_id == 2 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="observer">Observer</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="participation_type" id="none" value="None"
                                                        {{ $examiner->role_id == 3 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="none">None</label>
                                                </div>
                                            </div>
                                            <div class="error-message">Please select participation type</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hospital Organization Type</label>
                                            <div class="radio-group">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="hospital_type"
                                                        id="teaching_hospital" value="Teaching Hospital"
                                                        {{ $examiner->hospital_type == 'Teaching Hospital' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="teaching_hospital">Teaching
                                                        Hospital</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="hospital_type"
                                                        id="non_teaching" value="Non Teaching"
                                                        {{ $examiner->hospital_type == 'Non Teaching' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="non_teaching">Non
                                                        Teaching</label>
                                                </div>
                                            </div>
                                            <div class="error-message">Please select hospital type</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="hospital_name">Hospital Name</label>
                                            <input type="text" name="hospital_name" id="hospital_name"
                                                class="form-control" placeholder="Enter hospital name"
                                                value="{{ $examiner->hospital_name }}">
                                            <div class="error-message">Please enter hospital name</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Years you have Examined/Observed (2020-2024)</label>
                                            <div class="checkbox-group">
                                                @php
                                                    // Convert examination_years from database to array
                                                    $selectedYears = [];
                                                    if ($examiner->examination_years) {
                                                        if (is_string($examiner->examination_years)) {
                                                            // If it's a JSON string, decode it
                                                            $selectedYears =
                                                                json_decode($examiner->examination_years, true) ?: [];
                                                        } elseif (is_array($examiner->examination_years)) {
                                                            $selectedYears = $examiner->examination_years;
                                                        }
                                                    }
                                                @endphp

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="examination_years[]" id="year_2020" value="2020"
                                                        {{ in_array('2020', $selectedYears) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="year_2020">2020</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="examination_years[]" id="year_2021" value="2021"
                                                        {{ in_array('2021', $selectedYears) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="year_2021">2021</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="examination_years[]" id="year_2022" value="2022"
                                                        {{ in_array('2022', $selectedYears) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="year_2022">2022</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="examination_years[]" id="year_2023" value="2023"
                                                        {{ in_array('2023', $selectedYears) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="year_2023">2023</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="examination_years[]" id="year_2024" value="2024"
                                                        {{ in_array('2024', $selectedYears) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="year_2024">2024</label>
                                                </div>
                                            </div>
                                            <div class="error-message">Please select examination years</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" id="prevBtn" class="btn btn-custom btn-secondary-custom"
                                style="display: none;">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <div></div>
                            <button type="button" id="nextBtn" class="btn btn-custom btn-primary-custom">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" id="submitBtn" class="btn btn-custom btn-primary-custom"
                                style="display: none;">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('styles')
    <style>
        .form-container {
            max-width: 80%;
            margin: 0 auto 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 25px rgba(160, 38, 38, 0.1);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #a02626 0%, #d63031 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .form-header h2 {
            margin: 0;
            font-size: 32px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .progress-container {
            padding: 40px 50px 20px;
            background: white;
        }

        .progress-bar-container {
            position: relative;
            margin-bottom: 60px;
        }

        .progress-line {
            height: 4px;
            background: #eaf0f4;
            border-radius: 2px;
            position: relative;
            overflow: hidden;
        }

        .progress-line-fill {
            height: 100%;
            background: #a02626;
            width: 0%;
            transition: width 0.4s ease;
            border-radius: 2px;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: -18px;
        }

        .progress-step {
            background: white;
            border: 4px solid #eaf0f4;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #99a2a8;
            transition: all 0.3s ease;
            position: relative;
            font-size: 18px;
        }

        .progress-step.active {
            border-color: #a02626 !important;
            color: #a02626 !important;
            background: white !important;
        }

        .progress-step.completed {
            border-color: #a02626 !important;
            background: #a02626 !important;
            color: white !important;
        }

        .step-label {
            position: absolute;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 13px;
            font-weight: 500;
            color: #99a2a8;
            white-space: nowrap;
        }

        .step-label.active {
            color: #a02626 !important;
            font-weight: 600 !important;
        }

        .form-content {
            padding: 20px 50px 40px;
        }

        .form-step {
            display: none !important;
        }

        .form-step.active {
            display: block !important;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 25px;
            padding: 0 10px;
        }

        .form-group label {
            font-weight: 500;
            color: #405867;
            margin-bottom: 10px;
            display: block;
            font-size: 15px;
        }

        .form-control {
            border: 1px solid #d8e1e7 !important;
            border-radius: 3px !important;
            padding: 12px 20px !important;
            font-size: 15px !important;
            transition: all 0.3s ease !important;
            background: white !important;
            height: 48px !important;
            width: 100% !important;
            color: #5f6771 !important;
        }

        .form-control:focus {
            border-color: #a02626 !important;
            box-shadow: 0 0 0 0.2rem rgba(160, 38, 38, 0.15) !important;
            outline: none !important;
        }

        .form-control:hover {
            border-color: #a02626 !important;
        }

        .custom-file-upload {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            height: 48px !important;
            padding: 0 20px !important;
            border: 1px solid #99a2a8 !important;
            border-radius: 5px !important;
            text-align: center !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            background: white !important;
            color: #5f6771 !important;
            font-weight: 500 !important;
            font-size: 14px !important;
        }

        .custom-file-upload:hover {
            border-color: #a02626 !important;
            background: #a02626 !important;
            color: white !important;
        }

        .custom-file-upload.has-file {
            border-color: #a02626 !important;
            background: #fff5f5 !important;
            color: #a02626 !important;
        }

        .custom-file-upload i {
            font-size: 16px;
            margin-right: 10px;
        }

        .custom-file-upload input[type="file"] {
            display: none;
        }

        .text-muted {
            color: #6c757d !important;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px 50px;
            background: #f6f9fb;
            border-top: 1px solid #eaf0f4;
            margin: 0;
        }

        .btn-custom {
            padding: 12px 30px !important;
            border: none !important;
            border-radius: 5px !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            min-width: 130px !important;
            height: 45px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }

        .btn-primary-custom {
            background: #a02626 !important;
            color: white !important;
            border: 1px solid #a02626 !important;
        }

        .btn-primary-custom:hover {
            background: #8b2020 !important;
            border-color: #8b2020 !important;
            transform: translateY(-1px) !important;
        }

        .btn-secondary-custom {
            background: transparent !important;
            color: #99a2a8 !important;
            border: 1px solid #99a2a8 !important;
        }

        .btn-secondary-custom:hover {
            background: #405867 !important;
            border-color: #405867 !important;
            color: white !important;
            transform: translateY(-1px) !important;
        }

        .btn-custom:disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
        }

        .error-message {
            color: #a02626;
            font-size: 13px;
            margin-top: 8px;
            margin-left: 10px;
            display: none;
        }

        .form-control.error {
            border-color: #a02626 !important;
            background: #fff5f5 !important;
        }

        /* Updated CSS for checkbox and radio button alignment */
        .radio-group,
        .checkbox-group {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-start;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            min-height: auto;
        }

        .form-check-inline {
            display: flex;
            align-items: center;
            margin-right: 20px;
            margin-bottom: 10px;
            min-height: auto;
        }

        .form-check-input {
            margin-right: 8px;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            vertical-align: middle;
            flex-shrink: 0;
            width: 16px;
            height: 16px;
        }

        .form-check-label {
            font-weight: 500;
            color: #405867;
            margin-bottom: 0 !important;
            margin-top: 0 !important;
            font-size: 14px;
            cursor: pointer;
            line-height: 1.2;
            display: flex;
            align-items: center;
        }

        .exam-availability-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .exam-availability-group .form-check {
            margin-bottom: 5px;
            width: 100%;
            align-items: center;
        }

        /* Additional fixes for better alignment */
        .form-check-input[type="checkbox"],
        .form-check-input[type="radio"] {
            position: relative;
            top: 0;
            transform: none;
        }

        /* Bootstrap override - if you're using Bootstrap */
        .form-check-input:not(:disabled):not(:checked):hover {
            border-color: #a02626;
        }

        .form-check-input:checked {
            background-color: #a02626;
            border-color: #a02626;
        }

        /* Force alignment override */
        .form-check {
            display: flex !important;
            align-items: center !important;
        }

        .form-check-input {
            margin-top: 0 !important;
            position: static !important;
        }

        @media (max-width: 1080px) {
            .form-container {
                max-width: 100%;
                border-radius: 5px;
            }

            .form-header {
                padding: 30px 20px;
            }

            .form-header h2 {
                font-size: 26px;
            }

            .progress-container {
                padding: 30px 20px 20px;
            }

            .form-content {
                padding: 20px 20px 30px;
            }

            .form-actions {
                padding: 25px 20px;
                flex-direction: column;
                gap: 15px;
            }

            .btn-custom {
                width: 100% !important;
                margin: 0 !important;
            }

            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 10px;
            }

            .form-group {
                padding: 0;
                margin-bottom: 20px;
            }

            /* Mobile specific checkbox/radio fixes */
            .form-check-inline {
                margin-right: 15px;
                margin-bottom: 8px;
            }

            .form-check-input {
                width: 18px;
                height: 18px;
                margin-right: 10px;
            }

            .form-check-label {
                font-size: 15px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let currentStep = 1;
            const totalSteps = 3;

            // Initialize
            updateUI();

            // File upload handlers
            $('#passport_upload').on('change', function() {
                const fileName = this.files[0]?.name || 'Upload Profile Image (JPG/PNG)';
                $('#passportLabel').text(fileName);
                $(this).parent().toggleClass('has-file', this.files.length > 0);
            });

            $('#cv_upload').on('change', function() {
                const fileName = this.files[0]?.name || 'Upload CV (PDF/DOC)';
                $('#cvLabel').text(fileName);
                $(this).parent().toggleClass('has-file', this.files.length > 0);
            });

            // Next button
            $(document).on('click', '#nextBtn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Next button clicked, current step:', currentStep);

                if (validateCurrentStep()) {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        updateUI();
                        console.log('Moved to step:', currentStep);
                    }
                } else {
                    console.log('Validation failed for step:', currentStep);
                }
            });

            // Previous button
            $(document).on('click', '#prevBtn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Previous button clicked, current step:', currentStep);

                if (currentStep > 1) {
                    currentStep--;
                    updateUI();
                    console.log('Moved to step:', currentStep);
                }
            });

            // Form submission
            $('#examinerForm').on('submit', function(e) {
                e.preventDefault();

                if (validateCurrentStep()) {
                    $('#submitBtn').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin"></i> Updating...');

                    // Submit the form normally after a brief delay
                    setTimeout(() => {
                        this.submit();
                    }, 500);
                }
            });

            function updateUI() {
                console.log('Updating UI for step:', currentStep);

                // Hide all steps first
                $('.form-step').removeClass('active').hide();

                // Show current step with slight delay for animation
                setTimeout(() => {
                    $(`.form-step[data-step="${currentStep}"]`).addClass('active').show();
                }, 50);

                // Update progress bar
                const progressPercent = ((currentStep - 1) / (totalSteps - 1)) * 100;
                $('#progressFill').css('width', progressPercent + '%');

                // Update progress steps
                $('.progress-step, .step-label').removeClass('active completed');

                for (let i = 1; i <= totalSteps; i++) {
                    const step = $(`.progress-step[data-step="${i}"]`);
                    const label = step.find('.step-label');

                    if (i < currentStep) {
                        step.addClass('completed');
                        step.find('i').removeClass().addClass('fas fa-check');
                    } else if (i === currentStep) {
                        step.addClass('active');
                        label.addClass('active');
                        if (i === 1) step.find('i').removeClass().addClass('fas fa-user');
                        if (i === 2) step.find('i').removeClass().addClass('fas fa-id-card');
                        if (i === 3) step.find('i').removeClass().addClass('fas fa-history');

                    } else {
                        if (i === 1) step.find('i').removeClass().addClass('fas fa-user');
                        if (i === 2) step.find('i').removeClass().addClass('fas fa-id-card');
                        if (i === 3) step.find('i').removeClass().addClass('fas fa-history');

                    }
                }

                // Update buttons
                $('#prevBtn').toggle(currentStep > 1);
                $('#nextBtn').toggle(currentStep < totalSteps);
                $('#submitBtn').toggle(currentStep === totalSteps);

                console.log('UI updated - Prev button visible:', currentStep > 1, 'Next button visible:',
                    currentStep < totalSteps);
            }

            function validateCurrentStep() {
                let isValid = true;
                const currentStepElement = $(`.form-step[data-step="${currentStep}"]`);

                // Clear previous errors
                currentStepElement.find('.form-control').removeClass('error');
                currentStepElement.find('.error-message').hide();

                // Validate required fields
                currentStepElement.find('input[required], select[required]').each(function() {
                    const field = $(this);
                    const value = field.val() ? field.val().trim() : '';

                    if (!value) {
                        showFieldError(field, 'This field is required');
                        isValid = false;
                    }
                });

                // Validate email if provided
                const email = currentStepElement.find('input[type="email"]');
                if (email.length && email.val()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email.val())) {
                        showFieldError(email, 'Please enter a valid email address');
                        isValid = false;
                    }
                }

                // Validate password if provided
                const password = currentStepElement.find('input[type="password"]');
                if (password.length && password.val() && password.val().length < 6) {
                    showFieldError(password, 'Password must be at least 6 characters');
                    isValid = false;
                }

                console.log('Validation result for step', currentStep, ':', isValid);
                return isValid;
            }

            function showFieldError(field, message) {
                field.addClass('error');
                field.siblings('.error-message').text(message).show();
            }

            // Real-time validation
            $('input, select').on('blur', function() {
                const field = $(this);
                field.removeClass('error');
                field.siblings('.error-message').hide();

                if (field.prop('required') && !field.val().trim()) {
                    showFieldError(field, 'This field is required');
                }
            });

        });

        // Add this to your existing script section in the blade file

        // File size validation function
        function validateFileSize(file, maxSizeMB, fileType) {
            const maxSize = maxSizeMB * 1024 * 1024; // Convert MB to bytes

            if (file.size > maxSize) {
                return {
                    valid: false,
                    message: `${fileType} file size must be less than ${maxSizeMB}MB. Current size: ${(file.size / (1024 * 1024)).toFixed(2)}MB`
                };
            }

            return {
                valid: true,
                message: ''
            };
        }

        // Show alert function
        function showFileAlert(message, type = 'error') {
            // Remove existing alerts
            $('.file-alert').remove();

            const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
            const iconClass = type === 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle';

            const alertHtml = `
        <div class="alert ${alertClass} file-alert" style="margin-top: 10px; padding: 10px 15px; border-radius: 5px; display: flex; align-items: center;">
            <i class="fas ${iconClass}" style="margin-right: 10px;"></i>
            <span>${message}</span>
        </div>
    `;
            // Find the closest form-group and append the alert
            return alertHtml;
        }

        // Update the file upload handlers in your existing script
        $('#passport_upload').on('change', function() {
            const file = this.files[0];
            const $formGroup = $(this).closest('.form-group');

            // Remove previous alerts
            $formGroup.find('.file-alert').remove();

            if (file) {
                // Validate file size (1MB for images)
                const validation = validateFileSize(file, 1, 'Image');

                if (!validation.valid) {
                    // Show error alert
                    $formGroup.append(showFileAlert(validation.message, 'error'));

                    // Reset the file input
                    $(this).val('');
                    $('#passportLabel').text('Upload Profile Image (JPG/PNG)');
                    $(this).parent().removeClass('has-file');
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    $formGroup.append(showFileAlert('Please select a valid image file (JPG, JPEG, PNG)', 'error'));
                    $(this).val('');
                    $('#passportLabel').text('Upload Profile Image (JPG/PNG)');
                    $(this).parent().removeClass('has-file');
                    return;
                }

                // File is valid
                const fileName = file.name;
                $('#passportLabel').text(fileName);
                $(this).parent().addClass('has-file');
                $formGroup.append(showFileAlert('Image uploaded successfully!', 'success'));
            } else {
                $('#passportLabel').text('Upload Profile Image (JPG/PNG)');
                $(this).parent().removeClass('has-file');
            }
        });

        $('#cv_upload').on('change', function() {
            const file = this.files[0];
            const $formGroup = $(this).closest('.form-group');

            // Remove previous alerts
            $formGroup.find('.file-alert').remove();

            if (file) {
                // Validate file size (3MB for CV)
                const validation = validateFileSize(file, 3, 'CV');

                if (!validation.valid) {
                    // Show error alert
                    $formGroup.append(showFileAlert(validation.message, 'error'));

                    // Reset the file input
                    $(this).val('');
                    $('#cvLabel').text('Upload CV (PDF/DOC)');
                    $(this).parent().removeClass('has-file');
                    return;
                }

                // Validate file type
                const allowedTypes = ['application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ];
                if (!allowedTypes.includes(file.type)) {
                    $formGroup.append(showFileAlert('Please select a valid CV file (PDF, DOC, DOCX)', 'error'));
                    $(this).val('');
                    $('#cvLabel').text('Upload CV (PDF/DOC)');
                    $(this).parent().removeClass('has-file');
                    return;
                }

                // File is valid
                const fileName = file.name;
                $('#cvLabel').text(fileName);
                $(this).parent().addClass('has-file');
                $formGroup.append(showFileAlert('CV uploaded successfully!', 'success'));
            } else {
                $('#cvLabel').text('Upload CV (PDF/DOC)');
                $(this).parent().removeClass('has-file');
            }
        });

        // Add validation before form submission
        $('#examinerForm').on('submit', function(e) {
            let hasFileErrors = false;

            // Check if there are any file error alerts
            $('.file-alert.alert-danger').each(function() {
                hasFileErrors = true;
            });

            if (hasFileErrors) {
                e.preventDefault();
                alert('Please fix the file upload errors before submitting the form.');
                return false;
            }

            // Continue with your existing form submission logic
            if (validateCurrentStep()) {
                $('#submitBtn').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Updating...');

                // Submit the form normally after a brief delay
                setTimeout(() => {
                    this.submit();
                }, 500);
            } else {
                e.preventDefault();
            }
        });

        // Handle mutually exclusive selection with "Not Available"
        function toggleExamAvailabilityLogic() {
            const notAvailableChecked = $('#not_available').is(':checked');
            $('.exam-option').prop('disabled', notAvailableChecked);
        }

        $(document).ready(function() {
            toggleExamAvailabilityLogic();

            $('#not_available').on('change', function() {
                toggleExamAvailabilityLogic();
            });
        });
    </script>
@endpush

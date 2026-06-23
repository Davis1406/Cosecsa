@extends('layout.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="form-container">
                    <div class="form-header">
                        <h2>Edit Examiner</h2>
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
                    <form id="examinerForm" method="POST" action="{{ route('examiner.update', ['id' => $examiner->id]) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="back_url" value="{{ $backUrl ?? '' }}">
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
                                                placeholder="Leave blank if not changing">
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
                                                class="form-control" value="{{ $examiner->examiner_id }}">
                                            <div class="error-message">Please enter examiner ID</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="group_id">Group Name</label>
                                            <select name="group_id" id="group_id" class="form-control">
                                                <option value="" {{ empty($examiner->group_id) ? 'selected' : '' }}>
                                                    — None —
                                                </option>
                                                @foreach ($groups as $group)
                                                    <option value="{{ $group->id }}"
                                                        {{ $examiner->group_id == $group->id ? 'selected' : '' }}>
                                                        {{ $group->group_name }}
                                                    </option>
                                                @endforeach
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
                                            <label for="participation_type">Main Role</label>
                                            <select name="participation_type" id="participation_type" class="form-control">
                                                <option value="Examiner" {{ ($examiner->role_id ?? 1) == 1 ? 'selected' : '' }}>Examiner</option>
                                                <option value="Observer" {{ ($examiner->role_id ?? 1) == 2 ? 'selected' : '' }}>Observer</option>
                                                <option value="None"     {{ ($examiner->role_id ?? 1) == 3 ? 'selected' : '' }}>None</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="examiner_designation">Additional Designation</label>
                                            <select name="examiner_designation" id="examiner_designation" class="form-control">
                                                <option value="">— None —</option>
                                                @foreach($designationOptions as $desig)
                                                <option value="{{ $desig }}"
                                                    {{ ($examiner->examiner_designation ?? '') == $desig ? 'selected' : '' }}>
                                                    {{ $desig }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">
                                                <a href="{{ route('admin.designations') }}" target="_blank">Manage options ↗</a>
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="Active"   {{ ($examiner->status ?? 'Active') == 'Active'   ? 'selected' : '' }}>Active</option>
                                                <option value="Inactive" {{ ($examiner->status ?? '') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                                <option value="Deceased" {{ ($examiner->status ?? '') == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                                            </select>
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
                                    <!-- Exam Availability -->
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label>{{ $currentYearName }} Exam Availability</label>
                                        <div class="checkbox-group exam-availability-group">
                                            @php
                                                // exam_availability is double-encoded in DB — decode twice
                                                $selectedAvailability = [];
                                                if (!empty($examiner->history->exam_availability)) {
                                                    $av = json_decode($examiner->history->exam_availability, true);
                                                    if (is_string($av)) { $av = json_decode($av, true); }
                                                    $selectedAvailability = is_array($av) ? $av : [];
                                                }
                                            @endphp

                                            <div class="form-check">
                                                <input class="form-check-input exam-option" type="checkbox"
                                                    name="exam_availability[]" id="avail_mcs" value="MCS"
                                                    {{ in_array('MCS', $selectedAvailability) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="avail_mcs">MCS</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input exam-option" type="checkbox"
                                                    name="exam_availability[]" id="avail_fcs" value="FCS"
                                                    {{ in_array('FCS', $selectedAvailability) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="avail_fcs">FCS</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    name="exam_availability[]" id="avail_not_available" value="Not Available"
                                                    {{ in_array('Not Available', $selectedAvailability) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="avail_not_available">Not Available</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Shift -->
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label>Shift (For MCS)</label>
                                        <select name="shift" class="form-control">
                                            <option value="" {{ is_null($examiner->shift_id) ? 'selected' : '' }}>
                                                Select Shift...</option>
                                            <option value="1" {{ $examiner->shift_id == 1 ? 'selected' : '' }}>
                                                Morning</option>
                                            <option value="2" {{ $examiner->shift_id == 2 ? 'selected' : '' }}>
                                                Morning & Afternoon</option>
                                            <option value="3" {{ $examiner->shift_id == 3 ? 'selected' : '' }}>
                                                Afternoon</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Examiner History -->
                            <div class="form-step" data-step="3">

                                {{-- MCS / FCS past participation --}}
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
                                        </div>
                                    </div>
                                </div>

                                {{-- Hospital --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hospital Organization Type</label>
                                            <div class="radio-group">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="hospital_type"
                                                        id="teaching_hospital" value="Teaching Hospital"
                                                        {{ $examiner->hospital_type == 'Teaching Hospital' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="teaching_hospital">Teaching Hospital</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="hospital_type"
                                                        id="non_teaching" value="Non Teaching"
                                                        {{ $examiner->hospital_type == 'Non Teaching' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="non_teaching">Non Teaching</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="hospital_name">Hospital Name</label>
                                            <input type="text" name="hospital_name" id="hospital_name"
                                                class="form-control" placeholder="Enter hospital name"
                                                value="{{ $examiner->hospital_name }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- Years & Programmes with per-programme role toggle --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Years Examined &amp; Programme (2020–{{ last($examYears) }})</label>
                                            <p style="font-size:.8rem;color:#6c757d;margin-bottom:.5rem;">
                                                Check the years this examiner participated, then tick each programme and their role (Examiner or Observer).
                                            </p>
                                            @php
                                                $selectedYears = [];
                                                if (!empty($examiner->examination_years)) {
                                                    $ey = json_decode($examiner->examination_years, true);
                                                    if (is_string($ey)) { $ey = json_decode($ey, true); }
                                                    $selectedYears = is_array($ey) ? array_map('strval', $ey) : [];
                                                }
                                            @endphp

                                            <div class="mp-year-list">
                                                @foreach($examYears as $yr)
                                                @php
                                                    $isChecked    = in_array((string)$yr, $selectedYears);
                                                    $checkedProgs = array_values(array_filter((array)($yearParticipations[(string)$yr] ?? [])));
                                                @endphp
                                                <div class="mp-year-block {{ $isChecked ? 'mp-year-active' : '' }}">
                                                    <label class="mp-year-hdr">
                                                        <input type="checkbox"
                                                               class="mp-year-cb"
                                                               name="examination_years[]"
                                                               value="{{ $yr }}"
                                                               {{ $isChecked ? 'checked' : '' }}>
                                                        <span class="mp-yr-num">{{ $yr }}</span>
                                                        @if($isChecked && count($checkedProgs))
                                                        <span class="mp-yr-pill">{{ count($checkedProgs) }} programme{{ count($checkedProgs) > 1 ? 's' : '' }}</span>
                                                        @endif
                                                    </label>
                                                    <div class="mp-prog-panel" style="{{ $isChecked ? '' : 'display:none;' }}">
                                                        @foreach($programmeOptions as $prog)
                                                        @php
                                                            $isProgChecked = in_array($prog, $checkedProgs);
                                                            $progRole      = $yearRoles[(string)$yr][$prog] ?? 'Examiner';
                                                        @endphp
                                                        <div class="mp-prog-row {{ $isProgChecked ? 'mp-prog-on' : '' }}">
                                                            <label class="mp-prog-label">
                                                                <input type="checkbox"
                                                                       class="mp-prog-cb"
                                                                       name="year_programme[{{ $yr }}][]"
                                                                       value="{{ $prog }}"
                                                                       {{ $isProgChecked ? 'checked' : '' }}>
                                                                <span class="mp-prog-name">{{ $prog }}</span>
                                                            </label>
                                                            <div class="mp-role-wrap" style="{{ $isProgChecked ? '' : 'opacity:.3;pointer-events:none;' }}">
                                                                <label class="mp-role-btn {{ $progRole === 'Examiner' ? 'mp-role-e-on' : '' }}">
                                                                    <input type="radio"
                                                                           name="year_role[{{ $yr }}][{{ $prog }}]"
                                                                           value="Examiner"
                                                                           {{ $progRole === 'Examiner' ? 'checked' : '' }}>
                                                                    <i class="fas fa-user-check"></i> Examiner
                                                                </label>
                                                                <label class="mp-role-btn {{ $progRole === 'Observer' ? 'mp-role-o-on' : '' }}">
                                                                    <input type="radio"
                                                                           name="year_role[{{ $yr }}][{{ $prog }}]"
                                                                           value="Observer"
                                                                           {{ $progRole === 'Observer' ? 'checked' : '' }}>
                                                                    <i class="fas fa-eye"></i> Observer
                                                                </label>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
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

        /* Year + Programme rows */
        .year-programme-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 8px;
        }

        .year-programme-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 0;
        }

        .year-check-col {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 68px;
            flex-shrink: 0;
        }

        .year-label {
            font-size: 14px;
            font-weight: 600;
            color: #405867;
            margin: 0;
            cursor: pointer;
        }

        /* ── Year/Programme inline role UI (same design as Manage Participation modal) ── */
        .mp-year-list    { display:flex; flex-direction:column; gap:4px; }
        .mp-year-block   { border:1px solid #eee; border-radius:6px; overflow:hidden; }
        .mp-year-block.mp-year-active { border-color:#f0dada; }
        .mp-year-hdr {
            display:flex; align-items:center; gap:10px; padding:8px 12px;
            margin:0; cursor:pointer; background:#fafafa;
            font-weight:700; font-size:.88rem; color:#333; user-select:none;
        }
        .mp-year-block.mp-year-active .mp-year-hdr { background:#fdf4f4; }
        .mp-year-hdr input[type=checkbox] { accent-color:#a02626; width:15px; height:15px; cursor:pointer; }
        .mp-yr-num  { font-size:.95rem; }
        .mp-yr-pill {
            font-size:.7rem; font-weight:600; background:#a02626; color:#fff;
            padding:1px 8px; border-radius:10px; margin-left:auto;
        }
        .mp-prog-panel  { border-top:1px solid #f0e8e8; padding:4px 0; }
        .mp-prog-row {
            display:flex; align-items:center; justify-content:space-between;
            padding:5px 12px; gap:10px; border-bottom:1px solid #fafafa; transition:background .1s;
        }
        .mp-prog-row:last-child { border-bottom:none; }
        .mp-prog-row.mp-prog-on { background:#fffbf5; }
        .mp-prog-row:hover { background:#fef8f8; }
        .mp-prog-label  { display:flex; align-items:center; gap:8px; margin:0; cursor:pointer; font-size:.83rem; font-weight:500; color:#444; flex:1; }
        .mp-prog-label input[type=checkbox] { accent-color:#a02626; width:14px; height:14px; cursor:pointer; flex-shrink:0; }
        .mp-prog-name   { line-height:1.3; }
        .mp-role-wrap   { display:flex; gap:4px; flex-shrink:0; }
        .mp-role-btn {
            display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:4px;
            font-size:.75rem; font-weight:600; cursor:pointer; margin:0; border:1px solid #ddd;
            background:#f8f9fa; color:#666; transition:all .15s; user-select:none; white-space:nowrap;
        }
        .mp-role-btn input[type=radio] { display:none; }
        .mp-role-btn:hover  { border-color:#999; color:#333; }
        .mp-role-e-on       { background:#d4edda; border-color:#28a745; color:#155724; }
        .mp-role-o-on       { background:#fff3cd; border-color:#ffc107; color:#856404; }

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


        /* Add this to your existing styles section */

        /* File upload alert styles */
        .file-alert {
            font-size: 13px;
            border: none;
            margin-bottom: 0;
            animation: slideDown 0.3s ease;
        }

        .alert-danger {
            background-color: #fff5f5;
            color: #a02626;
            border-left: 4px solid #a02626;
        }

        .alert-success {
            background-color: #f0f9f0;
            color: #28a745;
            border-left: 4px solid #28a745;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Make file upload containers more spacious for alerts */
        .custom-file-upload+.file-alert {
            margin-top: 8px;
        }

        /* Responsive adjustments */
        @media (max-width: 1080px) {
            .file-alert {
                font-size: 12px;
                padding: 8px 12px;
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

            // Exam Availability: "Not Available" disables MCS/FCS options
            function toggleExamOptions() {
                const isNotAvailable = $('#avail_not_available').is(':checked');
                $('.exam-option').prop('disabled', isNotAvailable);
                if (isNotAvailable) { $('.exam-option').prop('checked', false); }
            }

            $('#avail_not_available').on('change', toggleExamOptions);
            toggleExamOptions();

            // Year checkbox ↔ Programme dropdown toggle
            // Year checkbox toggle (show/hide programme dropdown)
            // ── Year/Programme inline role UI ─────────────────────────────
            function mpUpdatePill($block) {
                var n = $block.find('.mp-prog-cb:checked').length;
                var $hdr = $block.find('.mp-year-hdr');
                var $pill = $hdr.find('.mp-yr-pill');
                if (n > 0) {
                    var txt = n + ' programme' + (n > 1 ? 's' : '');
                    if ($pill.length) { $pill.text(txt); } else { $hdr.append('<span class="mp-yr-pill">' + txt + '</span>'); }
                } else { $pill.remove(); }
            }

            // Year checkbox: show/hide programme panel
            $(document).on('change', '.mp-year-cb', function() {
                var $block = $(this).closest('.mp-year-block');
                var $panel = $block.find('.mp-prog-panel');
                if ($(this).is(':checked')) {
                    $block.addClass('mp-year-active');
                    $panel.slideDown(150);
                } else {
                    $block.removeClass('mp-year-active');
                    $panel.slideUp(150, function() {
                        $panel.find('.mp-prog-cb').prop('checked', false);
                        $panel.find('.mp-prog-row').removeClass('mp-prog-on');
                        $panel.find('.mp-role-wrap').css({ opacity: '.3', 'pointer-events': 'none' });
                    });
                    mpUpdatePill($block);
                }
            });

            // Programme checkbox: enable/disable role buttons
            $(document).on('change', '.mp-prog-cb', function() {
                var $row = $(this).closest('.mp-prog-row');
                var $wrap = $row.find('.mp-role-wrap');
                if ($(this).is(':checked')) {
                    $row.addClass('mp-prog-on');
                    $wrap.css({ opacity: '1', 'pointer-events': 'auto' });
                } else {
                    $row.removeClass('mp-prog-on');
                    $wrap.css({ opacity: '.3', 'pointer-events': 'none' });
                }
                mpUpdatePill($(this).closest('.mp-year-block'));
            });

            // Role radio: swap active highlight
            $(document).on('change', '.mp-role-btn input[type=radio]', function() {
                var $wrap = $(this).closest('.mp-role-wrap');
                $wrap.find('.mp-role-btn').removeClass('mp-role-e-on mp-role-o-on');
                $(this).closest('.mp-role-btn').addClass($(this).val() === 'Examiner' ? 'mp-role-e-on' : 'mp-role-o-on');
            });

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

                if (validateCurrentStep()) {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        updateUI();
                    }
                }
            });

            // Previous button
            $(document).on('click', '#prevBtn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (currentStep > 1) {
                    currentStep--;
                    updateUI();
                }
            });

            // Form submission
            $('#examinerForm').on('submit', function(e) {
                e.preventDefault();

                if (validateCurrentStep()) {
                    $('#submitBtn').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin"></i> Updating...');
                    setTimeout(() => {
                        this.submit();
                    }, 500);
                }
            });

            function updateUI() {
                $('.form-step').removeClass('active').hide();
                setTimeout(() => {
                    $(`.form-step[data-step="${currentStep}"]`).addClass('active').show();
                }, 50);

                const progressPercent = ((currentStep - 1) / (totalSteps - 1)) * 100;
                $('#progressFill').css('width', progressPercent + '%');

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
                        step.find('i').removeClass().addClass(getStepIcon(i));
                    } else {
                        step.find('i').removeClass().addClass(getStepIcon(i));
                    }
                }

                $('#prevBtn').toggle(currentStep > 1);
                $('#nextBtn').toggle(currentStep < totalSteps);
                $('#submitBtn').toggle(currentStep === totalSteps);
            }

            function getStepIcon(step) {
                return step === 1 ? 'fas fa-user' :
                    step === 2 ? 'fas fa-id-card' :
                    'fas fa-history';
            }

            function validateCurrentStep() {
                let isValid = true;
                const currentStepElement = $(`.form-step[data-step="${currentStep}"]`);

                currentStepElement.find('.form-control').removeClass('error');
                currentStepElement.find('.error-message').hide();

                currentStepElement.find('input[required], select[required]').each(function() {
                    const field = $(this);
                    const value = field.val() ? field.val().trim() : '';

                    if (!value) {
                        showFieldError(field, 'This field is required');
                        isValid = false;
                    }
                });

                const email = currentStepElement.find('input[type="email"]');
                if (email.length && email.val()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email.val())) {
                        showFieldError(email, 'Please enter a valid email address');
                        isValid = false;
                    }
                }

                const password = currentStepElement.find('input[type="password"]');
                if (password.length && password.val() && password.val().length < 6) {
                    showFieldError(password, 'Password must be at least 6 characters');
                    isValid = false;
                }

                return isValid;
            }

            function showFieldError(field, message) {
                field.addClass('error');
                field.siblings('.error-message').text(message).show();
            }

            $('input, select').on('blur', function() {
                const field = $(this);
                field.removeClass('error');
                field.siblings('.error-message').hide();

                if (field.prop('required') && !field.val().trim()) {
                    showFieldError(field, 'This field is required');
                }
            });
        });
    </script>
@endpush

@extends('layout.app')
@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Member</div>
    <div class="ms2-page-sub">Update personal information and membership details.</div>

    <div class="ms2-card">

        {{-- ── Stepper ──────────────────────────────────────────── --}}
        <div class="ms2-stepper">
            <div class="ms2-step active" id="step-1">
                <div class="ms2-step-circle">1</div>
                <div class="ms2-step-label">Personal Information</div>
            </div>
            <div class="ms2-line" id="line-1-2"></div>
            <div class="ms2-step" id="step-2">
                <div class="ms2-step-circle">2</div>
                <div class="ms2-step-label">Additional Details</div>
            </div>
        </div>

        <form id="ms2form" method="POST"
              action="{{ url('admin/associates/members/edit/' . $member->members_id) }}"
              enctype="multipart/form-data">
            @csrf

            {{-- ══ Step 1: Personal Information ══════════════════ --}}
            <div class="ms2-fieldset active" id="fieldset-1">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">First Name <span class="req">*</span></label>
                            <input type="text" name="firstname" class="ms2-input"
                                   value="{{ $member->firstname }}" required>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Middle Name</label>
                            <input type="text" name="middlename" class="ms2-input"
                                   value="{{ $member->middlename }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Last Name <span class="req">*</span></label>
                            <input type="text" name="lastname" class="ms2-input"
                                   value="{{ $member->lastname }}" required>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Gender <span class="req">*</span></label>
                            <select name="gender" class="ms2-input" required>
                                <option value="Male"   {{ $member->gender=='Male'   ? 'selected':'' }}>Male</option>
                                <option value="Female" {{ $member->gender=='Female' ? 'selected':'' }}>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Email <span class="req">*</span></label>
                            <div class="ms2-input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="personal_email" class="ms2-input"
                                       value="{{ $member->personal_email }}" required placeholder="member@email.com">
                            </div>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Password <span style="font-weight:400;opacity:.6;">(blank = keep current)</span></label>
                            <div class="ms2-input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" class="ms2-input" placeholder="New password">
                            </div>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Member Type <span class="req">*</span></label>
                            <select name="category_id" class="ms2-input" required>
                                <option value="" disabled>Select Type…</option>
                                <option value="1" {{ $member->category_id == 1 ? 'selected' : '' }}>Member</option>
                                <option value="2" {{ $member->category_id == 2 ? 'selected' : '' }}>Member Specialist</option>
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Profile Image</label>
                            <label class="ms2-file-row" id="photoLabel">
                                <i class="fas fa-camera"></i>
                                <span id="photoName">{{ $member->profile_image ? 'Replace photo…' : 'Choose photo…' }}</span>
                                <input type="file" name="profile_image" accept="image/*" style="display:none"
                                       onchange="document.getElementById('photoName').textContent = this.files[0]?.name || 'Choose photo…'">
                            </label>
                        </div>
                    </div>

                </div>
                <div class="ms2-footer">
                    <span></span>
                    <button type="button" class="ms2-btn-next" onclick="goToStep(2)">
                        Continue <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>

            {{-- ══ Step 2: Additional Details ══════════════════ --}}
            <div class="ms2-fieldset" id="fieldset-2">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Phone Number</label>
                            <div class="ms2-input-group">
                                <i class="fas fa-phone"></i>
                                <input type="text" name="phone_number" class="ms2-input"
                                       value="{{ $member->phone_number }}" placeholder="+000 000 000 000">
                            </div>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Country <span class="req">*</span></label>
                            <select name="country_id" class="ms2-input" required>
                                <option value="">Select Country</option>
                                @foreach($getCountry as $country)
                                    <option value="{{ $country->id }}" {{ $member->country_id == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Membership Year</label>
                            <input type="text" name="membership_year" class="ms2-input"
                                   value="{{ $member->membership_year }}" placeholder="e.g. 2020">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Admission Year</label>
                            <input type="text" name="admission_year" class="ms2-input"
                                   value="{{ $member->admission_year }}" placeholder="e.g. 2019">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Member ID</label>
                            <input type="text" name="member_id_number" class="ms2-input"
                                   value="{{ $member->member_id_number }}" placeholder="Unique College Member ID">
                        </div>
                    </div>

                </div>
                <div class="ms2-footer">
                    <button type="button" class="ms2-btn-back" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </button>
                    <button type="submit" class="ms2-btn-submit">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>
</div>

<script>
function goToStep(n) {
    var total = 2;
    for (var i = 1; i <= total; i++) {
        var fs   = document.getElementById('fieldset-' + i);
        var step = document.getElementById('step-' + i);
        if (fs)   fs.classList.toggle('active', i === n);
        if (step) step.classList.toggle('active', i === n);
    }
    for (var j = 1; j < total; j++) {
        var line = document.getElementById('line-' + j + '-' + (j + 1));
        if (line) line.style.background = j < n ? '#a02626' : '';
    }
}
</script>
@endsection

@extends('layout.app')
@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Programme Director</div>
    <div class="ms2-page-sub">Update personal information and hospital assignment for this programme director.</div>

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
                <div class="ms2-step-label">Hospital &amp; Assistant</div>
            </div>
        </div>

        {{-- ── Form ────────────────────────────────────────────── --}}
        <form id="ms2form" method="POST"
              action="{{ url('admin/associates/trainers/edit/' . $trainer->trainer_id) }}"
              enctype="multipart/form-data">
            @csrf

            {{-- ══ Step 1: Personal Information ══════════════════ --}}
            <div class="ms2-fieldset active" id="fieldset-1">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Full Name <span class="req">*</span></label>
                            <div class="ms2-input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" name="name" class="ms2-input"
                                       value="{{ $trainer->name }}" required placeholder="Full name">
                            </div>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Email <span class="req">*</span></label>
                            <div class="ms2-input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" class="ms2-input"
                                       value="{{ $trainer->user_email }}" required placeholder="Login email">
                            </div>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Password <span style="font-weight:400;opacity:.6;">(blank = keep current)</span></label>
                            <div class="ms2-input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" class="ms2-input" placeholder="New password">
                            </div>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Phone Number <span class="req">*</span></label>
                            <div class="ms2-input-group">
                                <i class="fas fa-phone"></i>
                                <input type="text" name="phone_number" class="ms2-input"
                                       value="{{ $trainer->phone_number }}" required placeholder="+000 000 000 000">
                            </div>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Associate Type <span class="req">*</span></label>
                            <select name="user_type" class="ms2-input" required>
                                <option value="" disabled>Select Type…</option>
                                <option value="2" {{ $trainer->user_type == 2 ? 'selected' : '' }}>Trainee</option>
                                <option value="3" {{ $trainer->user_type == 3 ? 'selected' : '' }}>Candidate</option>
                                <option value="4" {{ $trainer->user_type == 4 ? 'selected' : '' }}>Programme Director</option>
                                <option value="5" {{ $trainer->user_type == 5 ? 'selected' : '' }}>Country Representative</option>
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Profile Image</label>
                            <label class="ms2-file-row" id="photoLabel">
                                <i class="fas fa-camera"></i>
                                <span id="photoName">{{ $trainer->profile_image ? 'Replace photo…' : 'Choose photo…' }}</span>
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

            {{-- ══ Step 2: Hospital & Assistant ══════════════════ --}}
            <div class="ms2-fieldset" id="fieldset-2">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Hospital <span class="req">*</span></label>
                            <select name="hospital_id" class="ms2-input" required>
                                <option value="" disabled>Select Hospital…</option>
                                @foreach($getHospital as $hospital)
                                    <option value="{{ $hospital->id }}" {{ $hospital->id == $trainer->hospital_id ? 'selected' : '' }}>
                                        {{ $hospital->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Assistant PD Name</label>
                            <input type="text" name="assistant_pd" class="ms2-input"
                                   value="{{ $trainer->assistant_pd }}" placeholder="Assistant PD full name">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Assistant PD Email</label>
                            <div class="ms2-input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="assistant_email" class="ms2-input"
                                       value="{{ $trainer->assistant_email }}" placeholder="assistant@hospital.org">
                            </div>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Mobile Number</label>
                            <div class="ms2-input-group">
                                <i class="fas fa-mobile-alt"></i>
                                <input type="text" name="mobile_no" class="ms2-input"
                                       value="{{ $trainer->mobile_no }}" placeholder="+000 000 000 000">
                            </div>
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

@extends('layout.app')
@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Country Representative</div>
    <div class="ms2-page-sub">Update personal and contact information for this country representative.</div>

    <div class="ms2-card">
        <form id="ms2form" method="POST"
              action="{{ url('admin/associates/reps/edit/' . $countryRep->reps_id) }}"
              enctype="multipart/form-data">
            @csrf
            <div class="ms2-body">

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Full Name <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" class="ms2-input"
                                   value="{{ $countryRep->name }}" required placeholder="Full name">
                        </div>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Email <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="ms2-input"
                                   value="{{ $countryRep->user_email }}" required placeholder="Login email">
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
                            <input type="text" name="mobile_no" class="ms2-input"
                                   value="{{ $countryRep->mobile_no }}" required placeholder="+000 000 000 000">
                        </div>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Country <span class="req">*</span></label>
                        <select name="country_id" class="ms2-input" required>
                            <option value="" disabled>Select Country…</option>
                            @foreach($getCountry as $country)
                                <option value="{{ $country->id }}" {{ $country->id == $countryRep->country_id ? 'selected' : '' }}>
                                    {{ $country->country_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">COSECSA Email <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="cosecsa_email" class="ms2-input"
                                   value="{{ $countryRep->cosecsa_email }}" required placeholder="rep@cosecsamis.org">
                        </div>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Associate Type <span class="req">*</span></label>
                        <select name="user_type" class="ms2-input" required>
                            <option value="" disabled>Select Type…</option>
                            <option value="2" {{ $countryRep->user_type == 2 ? 'selected' : '' }}>Trainee</option>
                            <option value="3" {{ $countryRep->user_type == 3 ? 'selected' : '' }}>Candidate</option>
                            <option value="4" {{ $countryRep->user_type == 4 ? 'selected' : '' }}>Programme Director</option>
                            <option value="5" {{ $countryRep->user_type == 5 ? 'selected' : '' }}>Country Representative</option>
                        </select>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Profile Image</label>
                        <label class="ms2-file-row" id="photoLabel">
                            <i class="fas fa-camera"></i>
                            <span id="photoName">{{ $countryRep->profile_image ? 'Replace photo…' : 'Choose photo…' }}</span>
                            <input type="file" name="profile_image" accept="image/*" style="display:none"
                                   onchange="document.getElementById('photoName').textContent = this.files[0]?.name || 'Choose photo…'">
                        </label>
                    </div>
                </div>

            </div>
            <div class="ms2-footer">
                <a href="{{ url('admin/associates/reps') }}" class="ms2-btn-back">Cancel</a>
                <button type="submit" class="ms2-btn-submit">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
</div>
@endsection

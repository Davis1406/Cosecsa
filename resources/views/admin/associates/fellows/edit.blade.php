@extends('layout.app')
@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Fellow</div>
    <div class="ms2-page-sub">Update fellowship, academic, contact and financial records.</div>

    <div class="ms2-card">

        {{-- ── Stepper ──────────────────────────────────────────── --}}
        <div class="ms2-stepper">
            <div class="ms2-step active" id="step-1">
                <div class="ms2-step-circle">1</div>
                <div class="ms2-step-label">Personal Info</div>
            </div>
            <div class="ms2-line" id="line-1-2"></div>
            <div class="ms2-step" id="step-2">
                <div class="ms2-step-circle">2</div>
                <div class="ms2-step-label">Fellowship</div>
            </div>
            <div class="ms2-line" id="line-2-3"></div>
            <div class="ms2-step" id="step-3">
                <div class="ms2-step-circle">3</div>
                <div class="ms2-step-label">Contact</div>
            </div>
            <div class="ms2-line" id="line-3-4"></div>
            <div class="ms2-step" id="step-4">
                <div class="ms2-step-circle">4</div>
                <div class="ms2-step-label">Fees</div>
            </div>
        </div>

        <form id="ms2form" method="POST"
              action="{{ url('admin/associates/fellows/edit/' . $fellow->fellow_id) }}"
              enctype="multipart/form-data">
            @csrf

            {{-- ══ Step 1: Personal Information ══════════════════ --}}
            <div class="ms2-fieldset active" id="fieldset-1">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">First Name <span class="req">*</span></label>
                            <input type="text" name="firstname" class="ms2-input"
                                   value="{{ $fellow->firstname }}" required>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Middle Name</label>
                            <input type="text" name="middlename" class="ms2-input"
                                   value="{{ $fellow->middlename }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Last Name <span class="req">*</span></label>
                            <input type="text" name="lastname" class="ms2-input"
                                   value="{{ $fellow->lastname }}" required>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Gender</label>
                            <select name="gender" class="ms2-input">
                                <option value="Male"   {{ $fellow->gender=='Male'   ? 'selected':'' }}>Male</option>
                                <option value="Female" {{ $fellow->gender=='Female' ? 'selected':'' }}>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Candidate Number</label>
                            <input type="text" name="candidate_number" class="ms2-input"
                                   value="{{ $fellow->candidate_number }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Status</label>
                            <select name="status" class="ms2-input">
                                <option value="Active"   {{ $fellow->status=='Active'   ? 'selected':'' }}>Active</option>
                                <option value="Inactive" {{ $fellow->status=='Inactive' ? 'selected':'' }}>Inactive</option>
                                <option value="Deceased" {{ $fellow->status=='Deceased' ? 'selected':'' }}>Deceased</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Login Email <span class="req">*</span></label>
                            <div class="ms2-input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" class="ms2-input"
                                       value="{{ $fellow->email }}" required placeholder="Login email">
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
                        <div class="ms2-col" style="flex:1 1 100%;">
                            <label class="ms2-label">Profile Photo
                                @if($fellow->profile_image)
                                    <img src="{{ \App\Support\ApiAsset::url($fellow->profile_image) }}"
                                         style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid #a02626;vertical-align:middle;margin-left:8px;">
                                @endif
                            </label>
                            <label class="ms2-file-row" id="photoLabel">
                                <i class="fas fa-camera"></i>
                                <span id="photoName">{{ $fellow->profile_image ? 'Replace photo…' : 'Choose photo…' }}</span>
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

            {{-- ══ Step 2: Fellowship & Academic ══════════════════ --}}
            <div class="ms2-fieldset" id="fieldset-2">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Fellowship Type</label>
                            <select name="category_id" class="ms2-input">
                                <option value="" {{ empty($fellow->category_id) ? 'selected' : '' }}>(Not yet confirmed)</option>
                                <option value="1"  {{ $fellow->category_id==1  ? 'selected':'' }}>Member</option>
                                <option value="2"  {{ $fellow->category_id==2  ? 'selected':'' }}>Associate Fellow</option>
                                <option value="3"  {{ $fellow->category_id==3  ? 'selected':'' }}>Affiliate Member</option>
                                <option value="4"  {{ $fellow->category_id==4  ? 'selected':'' }}>Associate Member</option>
                                <option value="5"  {{ $fellow->category_id==5  ? 'selected':'' }}>Fellow by Examination</option>
                                <option value="6"  {{ $fellow->category_id==6  ? 'selected':'' }}>Foundation Fellow</option>
                                <option value="7"  {{ $fellow->category_id==7  ? 'selected':'' }}>Fellow By Election</option>
                                <option value="8"  {{ $fellow->category_id==8  ? 'selected':'' }}>Honorary Fellow (ASEA)</option>
                                <option value="9"  {{ $fellow->category_id==9  ? 'selected':'' }}>Overseas Fellow</option>
                                <option value="10" {{ $fellow->category_id==10 ? 'selected':'' }}>Honorary Fellow (COSECSA)</option>
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Fellowship Programme</label>
                            <select name="programme_id" class="ms2-input">
                                <option value="">— None —</option>
                                @foreach(\App\Models\Programme::orderBy('name')->get() as $prog)
                                    <option value="{{ $prog->id }}" {{ $fellow->programme_id==$prog->id ? 'selected':'' }}>
                                        {{ $prog->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Promoted to Fellow?</label>
                            <select name="is_promoted" class="ms2-input">
                                <option value="0" {{ $fellow->is_promoted=='0' ? 'selected':'' }}>No</option>
                                <option value="1" {{ $fellow->is_promoted=='1' ? 'selected':'' }}>Yes</option>
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Fellow ID</label>
                            <input type="text" name="fellow_id_number" class="ms2-input"
                                   value="{{ $fellow->fellow_id_number }}" placeholder="Unique College Fellow ID">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Intake / Admission Year</label>
                            <input type="text" name="admission_year" class="ms2-input"
                                   value="{{ $fellow->admission_year }}" placeholder="e.g. 2015">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">MCS Qualification Year</label>
                            <input type="text" name="mcs_qualification_year" class="ms2-input"
                                   value="{{ $fellow->mcs_qualification_year }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Fellowship Year</label>
                            <input type="text" name="fellowship_year" class="ms2-input"
                                   value="{{ $fellow->fellowship_year }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Country of MCS Training</label>
                            <input type="text" name="country_mcs_training" class="ms2-input"
                                   value="{{ $fellow->country_mcs_training }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Supervised By</label>
                            <input type="text" name="supervised_by" class="ms2-input"
                                   value="{{ $fellow->supervised_by }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Registered By</label>
                            <input type="text" name="registered_by" class="ms2-input"
                                   value="{{ $fellow->registered_by }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Secretariat Reg. Date</label>
                            <input type="date" name="secretariat_registration_date" class="ms2-input"
                                   value="{{ $fellow->secretariat_registration_date }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Upcoming Exam Year</label>
                            <input type="text" name="exam_year_upcoming" class="ms2-input"
                                   value="{{ $fellow->exam_year_upcoming }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Previous Exam Year</label>
                            <input type="text" name="exam_year_previous" class="ms2-input"
                                   value="{{ $fellow->exam_year_previous }}">
                        </div>
                    </div>

                </div>
                <div class="ms2-footer">
                    <button type="button" class="ms2-btn-back" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </button>
                    <button type="button" class="ms2-btn-next" onclick="goToStep(3)">
                        Continue <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>

            {{-- ══ Step 3: Contact & Professional ══════════════════ --}}
            <div class="ms2-fieldset" id="fieldset-3">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Phone Number</label>
                            <div class="ms2-input-group">
                                <i class="fas fa-phone"></i>
                                <input type="text" name="phone_number" class="ms2-input"
                                       value="{{ $fellow->phone_number }}">
                            </div>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Personal Email</label>
                            <div class="ms2-input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="personal_email" class="ms2-input"
                                       value="{{ $fellow->personal_email }}">
                            </div>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Secondary Email</label>
                            <div class="ms2-input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="second_email" class="ms2-input"
                                       value="{{ $fellow->second_email }}">
                            </div>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Country <span class="req">*</span></label>
                            <select name="country_id" class="ms2-input" required>
                                <option value="">Select Country</option>
                                @foreach($getCountry as $country)
                                    <option value="{{ $country->id }}"
                                        {{ $fellow->country_id==$country->id ? 'selected':'' }}>
                                        {{ $country->country_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">COSECSA Region</label>
                            <select name="cosecsa_region" class="ms2-input">
                                <option value="">— Select —</option>
                                @foreach(['Eastern Africa','Central Africa','Southern Africa','West Africa','North Africa'] as $r)
                                    <option value="{{ $r }}" {{ $fellow->cosecsa_region==$r ? 'selected':'' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Address</label>
                            <input type="text" name="address" class="ms2-input"
                                   value="{{ $fellow->address }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Current Specialty</label>
                            <select name="current_specialty" class="ms2-input select2-tags" data-placeholder="Search or type a specialty…">
                                <option value=""></option>
                                @php $specialtyOpts = \App\Models\Programme::orderBy('name')->pluck('name'); @endphp
                                @if($fellow->current_specialty && !$specialtyOpts->contains($fellow->current_specialty))
                                    {{-- Existing free-text value that doesn't match a standard programme name — keep it selectable. --}}
                                    <option value="{{ $fellow->current_specialty }}" selected>{{ $fellow->current_specialty }}</option>
                                @endif
                                @foreach($specialtyOpts as $s)
                                    <option value="{{ $s }}" {{ $fellow->current_specialty==$s ? 'selected':'' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Current Hospital / Organisation</label>
                            <select name="organization" class="ms2-input select2-tags" data-placeholder="Search or type a hospital / organisation…">
                                <option value=""></option>
                                @php $hospitalOpts = ($getHospital ?? collect())->pluck('name'); @endphp
                                @if($fellow->organization && !$hospitalOpts->contains($fellow->organization))
                                    {{-- Existing free-text value that doesn't match a real hospital record — keep it selectable. --}}
                                    <option value="{{ $fellow->organization }}" selected>{{ $fellow->organization }}</option>
                                @endif
                                @foreach($hospitalOpts as $h)
                                    <option value="{{ $h }}" {{ $fellow->organization==$h ? 'selected':'' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
                <div class="ms2-footer">
                    <button type="button" class="ms2-btn-back" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </button>
                    <button type="button" class="ms2-btn-next" onclick="goToStep(4)">
                        Continue <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>

            {{-- ══ Step 4: Fees & Finance ══════════════════ --}}
            <div class="ms2-fieldset" id="fieldset-4">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Sponsored By</label>
                            <input type="text" name="sponsored_by" class="ms2-input"
                                   value="{{ $fellow->sponsored_by }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Prog. Entry Fee Year</label>
                            <input type="text" name="prog_entry_fee_year" class="ms2-input"
                                   value="{{ $fellow->prog_entry_fee_year }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Entry Mode of Payment</label>
                            <select name="prog_entry_mode_payment" class="ms2-input">
                                <option value="">— Select —</option>
                                @foreach(['Bank Transfer','Cheque','Cash','Online','Waived'] as $m)
                                    <option value="{{ $m }}" {{ $fellow->prog_entry_mode_payment==$m ? 'selected':'' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Exam Fee Year</label>
                            <input type="text" name="exam_fee_year" class="ms2-input"
                                   value="{{ $fellow->exam_fee_year }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Exam Fee Date Paid</label>
                            <input type="date" name="exam_fee_date_paid" class="ms2-input"
                                   value="{{ $fellow->exam_fee_date_paid }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Exam Fee Amount (USD)</label>
                            <input type="text" name="exam_fee_amount_paid" class="ms2-input"
                                   value="{{ $fellow->exam_fee_amount_paid }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Exam Fee Mode</label>
                            <select name="exam_fee_mode_payment" class="ms2-input">
                                <option value="">— Select —</option>
                                @foreach(['Bank Transfer','Cheque','Cash','Online','Waived'] as $m)
                                    <option value="{{ $m }}" {{ $fellow->exam_fee_mode_payment==$m ? 'selected':'' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Exam Fee Verified</label>
                            <select name="exam_fee_payment_verified" class="ms2-input">
                                <option value="0" {{ !$fellow->exam_fee_payment_verified ? 'selected':'' }}>No / Pending</option>
                                <option value="1" {{ $fellow->exam_fee_payment_verified  ? 'selected':'' }}>Yes – Verified</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="ms2-footer">
                    <button type="button" class="ms2-btn-back" onclick="goToStep(3)">
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
    var total = 4;
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

// Searchable + free-text dropdowns for Current Specialty / Current Hospital.
// tags:true lets staff either pick an existing option or type a value that
// isn't in the list yet (both fields hold a lot of pre-existing free text
// that doesn't cleanly match the standard programme/hospital name lists).
// width:'100%' is needed because step 3 starts hidden (display:none via the
// stepper) — select2 can't measure a hidden element's width on init.
$(function () {
    $('.select2-tags').select2({
        tags: true,
        width: '100%',
        placeholder: function () { return $(this).data('placeholder') || ''; },
        allowClear: true
    });
});
</script>
@endsection

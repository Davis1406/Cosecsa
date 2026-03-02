@extends('layout.app')

@push('styles')
<style>
.form-label { font-size: .83rem; font-weight: 600; color: #444; margin-bottom: 3px; }
.req { color: #a02626; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-size:1.2rem;">
                        <i class="fas fa-user-edit mr-2" style="color:#a02626;"></i>
                        Edit Fellow — {{ trim(($fellow->firstname ?? '') . ' ' . ($fellow->lastname ?? '')) }}
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ url('admin/associates/fellows/view/' . $fellow->fellow_id) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Profile
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ url('admin/associates/fellows/edit/' . $fellow->fellow_id) }}" enctype="multipart/form-data">
                {{ csrf_field() }}

                {{-- ── SECTION 1: Personal ── --}}
                <div class="card mb-3">
                    <div class="card-header py-2" style="background:#fafafa; border-bottom:2px solid #f0d4d4;">
                        <h3 class="card-title" style="color:#a02626; font-size:.9rem;">
                            <i class="fas fa-user mr-2"></i>1. Personal Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">First Name <span class="req">*</span></label>
                                <input type="text" name="firstname" class="form-control form-control-sm"
                                       value="{{ $fellow->firstname }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middlename" class="form-control form-control-sm"
                                       value="{{ $fellow->middlename }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Last Name <span class="req">*</span></label>
                                <input type="text" name="lastname" class="form-control form-control-sm"
                                       value="{{ $fellow->lastname }}" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-control form-control-sm">
                                    <option value="Male"   {{ $fellow->gender=='Male'   ? 'selected':'' }}>Male</option>
                                    <option value="Female" {{ $fellow->gender=='Female' ? 'selected':'' }}>Female</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Candidate Number</label>
                                <input type="text" name="candidate_number" class="form-control form-control-sm"
                                       value="{{ $fellow->candidate_number }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Login Email <span class="req">*</span></label>
                                <input type="email" name="email" class="form-control form-control-sm"
                                       value="{{ $fellow->email }}" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control form-control-sm"
                                       placeholder="Leave blank to keep">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control form-control-sm">
                                    <option value="Active"   {{ $fellow->status=='Active'   ? 'selected':'' }}>Active</option>
                                    <option value="Inactive" {{ $fellow->status=='Inactive' ? 'selected':'' }}>Inactive</option>
                                    <option value="Deceased" {{ $fellow->status=='Deceased' ? 'selected':'' }}>Deceased</option>
                                </select>
                            </div>
                            <div class="form-group col-md-5">
                                <label class="form-label">Profile Photo
                                    @if($fellow->profile_image)
                                        <img src="{{ asset('storage/app/public/'.$fellow->profile_image) }}"
                                             style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid #ccc;vertical-align:middle;margin-left:6px;">
                                    @endif
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="profilePhoto" name="profile_image" accept="image/*">
                                    <label class="custom-file-label" for="profilePhoto" style="font-size:.83rem;">
                                        {{ $fellow->profile_image ? 'Replace photo…' : 'Choose photo…' }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── SECTION 2: Fellowship & Academic ── --}}
                <div class="card mb-3">
                    <div class="card-header py-2" style="background:#fafafa; border-bottom:2px solid #f0d4d4;">
                        <h3 class="card-title" style="color:#a02626; font-size:.9rem;">
                            <i class="fas fa-graduation-cap mr-2"></i>2. Fellowship &amp; Academic
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Fellowship Type <span class="req">*</span></label>
                                <select name="category_id" class="form-control form-control-sm" required>
                                    <option value="" disabled>Select…</option>
                                    <option value="5"  {{ $fellow->category_id==5  ? 'selected':'' }}>Fellow by Examination</option>
                                    <option value="6"  {{ $fellow->category_id==6  ? 'selected':'' }}>Foundation Fellow</option>
                                    <option value="7"  {{ $fellow->category_id==7  ? 'selected':'' }}>Fellow By Election</option>
                                    <option value="8"  {{ $fellow->category_id==8  ? 'selected':'' }}>Honorary Fellow (ASEA)</option>
                                    <option value="9"  {{ $fellow->category_id==9  ? 'selected':'' }}>Overseas Fellow</option>
                                    <option value="10" {{ $fellow->category_id==10 ? 'selected':'' }}>Honorary Fellow (COSECSA)</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Fellowship Programme</label>
                                <select name="programme_id" class="form-control form-control-sm">
                                    <option value="">— None —</option>
                                    @foreach(\App\Models\Programme::orderBy('name')->get() as $prog)
                                        <option value="{{ $prog->id }}" {{ $fellow->programme_id==$prog->id ? 'selected':'' }}>
                                            {{ $prog->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Promoted to Fellow?</label>
                                <select name="is_promoted" class="form-control form-control-sm">
                                    <option value="0" {{ $fellow->is_promoted=='0' ? 'selected':'' }}>No</option>
                                    <option value="1" {{ $fellow->is_promoted=='1' ? 'selected':'' }}>Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="form-label">Intake / Admission Year</label>
                                <input type="text" name="admission_year" class="form-control form-control-sm"
                                       value="{{ $fellow->admission_year }}" placeholder="e.g. 2015">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">MCS Qualification Year</label>
                                <input type="text" name="mcs_qualification_year" class="form-control form-control-sm"
                                       value="{{ $fellow->mcs_qualification_year }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Fellowship Year</label>
                                <input type="text" name="fellowship_year" class="form-control form-control-sm"
                                       value="{{ $fellow->fellowship_year }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Country of MCS Training</label>
                                <input type="text" name="country_mcs_training" class="form-control form-control-sm"
                                       value="{{ $fellow->country_mcs_training }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Supervised by</label>
                                <input type="text" name="supervised_by" class="form-control form-control-sm"
                                       value="{{ $fellow->supervised_by }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Registered by</label>
                                <input type="text" name="registered_by" class="form-control form-control-sm"
                                       value="{{ $fellow->registered_by }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Secretariat Reg. Date</label>
                                <input type="date" name="secretariat_registration_date" class="form-control form-control-sm"
                                       value="{{ $fellow->secretariat_registration_date }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="form-label">Upcoming Exam Year</label>
                                <input type="text" name="exam_year_upcoming" class="form-control form-control-sm"
                                       value="{{ $fellow->exam_year_upcoming }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Previous Exam Year</label>
                                <input type="text" name="exam_year_previous" class="form-control form-control-sm"
                                       value="{{ $fellow->exam_year_previous }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── SECTION 3: Contact & Professional ── --}}
                <div class="card mb-3">
                    <div class="card-header py-2" style="background:#fafafa; border-bottom:2px solid #f0d4d4;">
                        <h3 class="card-title" style="color:#a02626; font-size:.9rem;">
                            <i class="fas fa-address-card mr-2"></i>3. Contact &amp; Professional
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control form-control-sm"
                                       value="{{ $fellow->phone_number }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Personal Email</label>
                                <input type="email" name="personal_email" class="form-control form-control-sm"
                                       value="{{ $fellow->personal_email }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Secondary Email</label>
                                <input type="email" name="second_email" class="form-control form-control-sm"
                                       value="{{ $fellow->second_email }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Country <span class="req">*</span></label>
                                <select name="country_id" class="form-control form-control-sm" required>
                                    <option value="">Select Country</option>
                                    @foreach($getCountry as $country)
                                        <option value="{{ $country->id }}"
                                            {{ $fellow->country_id==$country->id ? 'selected':'' }}>
                                            {{ $country->country_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">COSECSA Region</label>
                                <select name="cosecsa_region" class="form-control form-control-sm">
                                    <option value="">— Select —</option>
                                    @foreach(['Eastern Africa','Central Africa','Southern Africa','West Africa','North Africa'] as $r)
                                        <option value="{{ $r }}" {{ $fellow->cosecsa_region==$r ? 'selected':'' }}>{{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control form-control-sm"
                                       value="{{ $fellow->address }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Current Specialty</label>
                                <input type="text" name="current_specialty" class="form-control form-control-sm"
                                       value="{{ $fellow->current_specialty }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">Current Hospital / Organisation</label>
                                <input type="text" name="organization" class="form-control form-control-sm"
                                       value="{{ $fellow->organization }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── SECTION 4: Fees & Finance ── --}}
                <div class="card mb-3">
                    <div class="card-header py-2" style="background:#fafafa; border-bottom:2px solid #f0d4d4;">
                        <h3 class="card-title" style="color:#a02626; font-size:.9rem;">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>4. Fees &amp; Finance
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Sponsored by</label>
                                <input type="text" name="sponsored_by" class="form-control form-control-sm"
                                       value="{{ $fellow->sponsored_by }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Prog. Entry Fee Year</label>
                                <input type="text" name="prog_entry_fee_year" class="form-control form-control-sm"
                                       value="{{ $fellow->prog_entry_fee_year }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Entry Mode of Payment</label>
                                <select name="prog_entry_mode_payment" class="form-control form-control-sm">
                                    <option value="">— Select —</option>
                                    @foreach(['Bank Transfer','Cheque','Cash','Online','Waived'] as $m)
                                        <option value="{{ $m }}" {{ $fellow->prog_entry_mode_payment==$m ? 'selected':'' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="form-label">Exam Fee Year</label>
                                <input type="text" name="exam_fee_year" class="form-control form-control-sm"
                                       value="{{ $fellow->exam_fee_year }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Exam Fee Date Paid</label>
                                <input type="date" name="exam_fee_date_paid" class="form-control form-control-sm"
                                       value="{{ $fellow->exam_fee_date_paid }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Exam Fee Amount (USD)</label>
                                <input type="text" name="exam_fee_amount_paid" class="form-control form-control-sm"
                                       value="{{ $fellow->exam_fee_amount_paid }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Exam Fee Mode</label>
                                <select name="exam_fee_mode_payment" class="form-control form-control-sm">
                                    <option value="">— Select —</option>
                                    @foreach(['Bank Transfer','Cheque','Cash','Online','Waived'] as $m)
                                        <option value="{{ $m }}" {{ $fellow->exam_fee_mode_payment==$m ? 'selected':'' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Exam Fee Verified</label>
                                <select name="exam_fee_payment_verified" class="form-control form-control-sm">
                                    <option value="0" {{ !$fellow->exam_fee_payment_verified ? 'selected':'' }}>No / Pending</option>
                                    <option value="1" {{ $fellow->exam_fee_payment_verified  ? 'selected':'' }}>Yes – Verified</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end mb-4">
                    <a href="{{ url('admin/associates/fellows/view/' . $fellow->fellow_id) }}" class="btn btn-secondary mr-2">Cancel</a>
                    <button type="submit" class="btn btn-danger" style="background:#a02626; border-color:#a02626;">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>

            </form>
        </div>
    </section>
</div>

<script>
$(function () { bsCustomFileInput.init(); });
</script>
@endsection

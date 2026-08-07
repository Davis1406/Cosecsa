@extends('layout.app')

@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Candidate</div>
    <div class="ms2-page-sub">Update personal information and candidate details.</div>

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
                <div class="ms2-step-label">Candidate Details</div>
            </div>
            <div class="ms2-line" id="line-2-3"></div>
            <div class="ms2-step" id="step-3">
                <div class="ms2-step-circle">3</div>
                <div class="ms2-step-label">Payment Records</div>
            </div>
        </div>

        <form id="ms2form" method="POST"
              action="{{ url('admin/associates/candidates/edit/'.$candidate->candidates_id) }}"
              enctype="multipart/form-data">
            @csrf

            {{-- ══ Step 1: Personal Information ══════════════════ --}}
            <div class="ms2-fieldset active" id="fieldset-1">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">First Name <span class="req">*</span></label>
                            <input type="text" name="firstname" class="ms2-input"
                                   value="{{ $candidate->firstname }}" required>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Middle Name</label>
                            <input type="text" name="middlename" class="ms2-input"
                                   value="{{ $candidate->middlename }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Surname <span class="req">*</span></label>
                            <input type="text" name="lastname" class="ms2-input"
                                   value="{{ $candidate->lastname }}" required>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Email</label>
                            <input type="email" name="personal_email" class="ms2-input"
                                   value="{{ $candidate->personal_email }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">SFS Username</label>
                            <div class="ms2-input-group">
                                <i class="ms2-icon fas fa-user"></i>
                                <input type="text" name="email" class="ms2-input"
                                       value="{{ $candidate->user_email }}">
                            </div>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">
                                SFS Password
                                <span class="hint">(leave blank to keep current)</span>
                            </label>
                            <div class="ms2-input-group">
                                <i class="ms2-icon fas fa-lock"></i>
                                <input type="password" name="password" class="ms2-input"
                                       autocomplete="new-password" placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Gender</label>
                            <select name="gender" class="ms2-input">
                                <option value="">Select Gender</option>
                                <option value="Male"   {{ $candidate->gender == 'Male'   ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $candidate->gender == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Associate Type</label>
                            <select name="user_type" class="ms2-input" required>
                                <option value="">Select Type</option>
                                <option value="2" {{ $candidate->user_type == 2 ? 'selected' : '' }}>Trainee</option>
                                <option value="3" {{ $candidate->user_type == 3 ? 'selected' : '' }}>Candidate</option>
                                <option value="4" {{ $candidate->user_type == 4 ? 'selected' : '' }}>Programme Director</option>
                                <option value="5" {{ $candidate->user_type == 5 ? 'selected' : '' }}>Trainer</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Profile Image</label>
                            <div class="ms2-file-row">
                                <label class="ms2-file-name" id="file-label" for="upload">
                                    <i class="fas fa-image"></i> Upload Profile Image
                                </label>
                                <input type="file" id="upload" name="profile_image" accept="image/*">
                                <label class="ms2-file-btn" for="upload">Browse</label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="ms2-footer">
                    <button type="button" class="ms2-btn-back" style="visibility:hidden">Back</button>
                    <button type="button" class="ms2-btn-next" data-next="2">
                        Continue <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>{{-- /fieldset-1 --}}

            {{-- ══ Step 2: Candidate Details ══════════════════════ --}}
            <div class="ms2-fieldset" id="fieldset-2">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Programme <span class="req">*</span></label>
                            <select name="programme_id" class="ms2-input" required>
                                <option value="">Select Programme</option>
                                @foreach($getProgramme as $programme)
                                    <option value="{{ $programme->id }}"
                                        {{ $candidate->programme_id == $programme->id ? 'selected' : '' }}>
                                        {{ $programme->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Hospital <span class="req">*</span></label>
                            <select name="hospital_id" class="ms2-input" required>
                                <option value="">Select Hospital</option>
                                @foreach($getHospital as $hospital)
                                    <option value="{{ $hospital->id }}"
                                        {{ $candidate->hospital_id == $hospital->id ? 'selected' : '' }}>
                                        {{ $hospital->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Country <span class="req">*</span></label>
                            <select name="country_id" class="ms2-input" required>
                                <option value="">Select Country</option>
                                @foreach($getCountry as $country)
                                    <option value="{{ $country->id }}"
                                        {{ $candidate->country_id == $country->id ? 'selected' : '' }}>
                                        {{ $country->country_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">PE Number</label>
                            <input type="text" name="entry_number" class="ms2-input"
                                   value="{{ $candidate->entry_number }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Candidate Number</label>
                            <input type="text" name="candidate_id" class="ms2-input"
                                   value="{{ $candidate->candidate_id }}"
                                   placeholder="e.g. MCS077 (set by Examination Officer)">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Exam Number</label>
                            <input type="text" name="exam_number" class="ms2-input"
                                   value="{{ $candidate->exam_number }}"
                                   placeholder="Exam number (if assigned)">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col ms2-col-3">
                            <label class="ms2-label">Repeating Paper I</label>
                            <select name="repeat_paper_one" class="ms2-input">
                                <option value="No"  {{ ($candidate->repeat_paper_one ?? 'No') == 'No'  ? 'selected' : '' }}>No</option>
                                <option value="Yes" {{ ($candidate->repeat_paper_one ?? 'No') == 'Yes' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                        <div class="ms2-col ms2-col-3">
                            <label class="ms2-label">Repeating Paper II</label>
                            <select name="repeat_paper_two" class="ms2-input">
                                <option value="No"  {{ ($candidate->repeat_paper_two ?? 'No') == 'No'  ? 'selected' : '' }}>No</option>
                                <option value="Yes" {{ ($candidate->repeat_paper_two ?? 'No') == 'Yes' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                        <div class="ms2-col ms2-col-3">
                            <label class="ms2-label">MMed Qualification</label>
                            <select name="mmed" class="ms2-input">
                                <option value="No"  {{ ($candidate->mmed ?? 'No') == 'No'  ? 'selected' : '' }}>No</option>
                                <option value="Yes" {{ ($candidate->mmed ?? 'No') == 'Yes' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Admission Year</label>
                            <select name="admission_year" class="ms2-input">
                                <option value="">Select Year</option>
                                @for($y = 2010; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $candidate->admission_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Exam Year</label>
                            <select name="exam_year" class="ms2-input">
                                <option value="">Select Year</option>
                                @for($y = 2024; $y <= date('Y') + 2; $y++)
                                    <option value="{{ $y }}" {{ $candidate->exam_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                </div>
                <div class="ms2-footer">
                    <button type="button" class="ms2-btn-back" data-prev="1">Back</button>
                    <button type="button" class="ms2-btn-next" data-next="3">
                        Continue <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>{{-- /fieldset-2 --}}

            {{-- ══ Step 3: Payment Records ════════════════════════ --}}
            <div class="ms2-fieldset" id="fieldset-3">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Invoice Number</label>
                            <input type="text" name="invoice_number" class="ms2-input"
                                   value="{{ $candidate->invoice_number }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Invoice Date</label>
                            <input type="date" name="invoice_date" class="ms2-input"
                                   value="{{ $candidate->invoice_date }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Invoice Amount (USD)</label>
                            <input type="number" step="0.01" min="0" name="invoice_amount" class="ms2-input"
                                   value="{{ $candidate->invoice_amount }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Invoice Status</label>
                            <select name="invoice_status" class="ms2-input">
                                <option value="Pending"  {{ ($candidate->invoice_status ?? '') == 'Pending'  ? 'selected' : '' }}>Pending</option>
                                <option value="Sent"     {{ ($candidate->invoice_status ?? '') == 'Sent'     ? 'selected' : '' }}>Sent</option>
                                <option value="Complete" {{ ($candidate->invoice_status ?? '') == 'Complete' ? 'selected' : '' }}>Complete</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Sponsor</label>
                            <input type="text" name="sponsor" class="ms2-input"
                                   value="{{ $candidate->sponsor }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Fee Paid</label>
                            <select name="fee_paid" class="ms2-input">
                                <option value="No"  {{ ($candidate->fee_paid ?? 'No') == 'No'  ? 'selected' : '' }}>No</option>
                                <option value="Yes" {{ ($candidate->fee_paid ?? 'No') == 'Yes' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Amount Paid (USD)</label>
                            <input type="number" step="0.01" min="0" name="amount_paid" class="ms2-input"
                                   value="{{ $candidate->amount_paid }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Date Paid</label>
                            <input type="date" name="payment_date" class="ms2-input"
                                   value="{{ $candidate->payment_date }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Mode of Payment</label>
                            <select name="mode_of_payment" class="ms2-input">
                                <option value="">— Select Mode —</option>
                                <option value="Country Rep"   {{ ($candidate->mode_of_payment ?? '') == 'Country Rep'   ? 'selected' : '' }}>Country Rep</option>
                                <option value="Bank Transfer" {{ ($candidate->mode_of_payment ?? '') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Online Payment"{{ ($candidate->mode_of_payment ?? '') == 'Online Payment'? 'selected' : '' }}>Online Payment</option>
                                <option value="Sponsor"       {{ ($candidate->mode_of_payment ?? '') == 'Sponsor'       ? 'selected' : '' }}>Sponsor</option>
                                <option value="Other"         {{ ($candidate->mode_of_payment ?? '') == 'Other'         ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="ms2-col">{{-- spacer --}}</div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Remarks / Notes</label>
                            <textarea name="remarks" class="ms2-input"
                                      placeholder="Any additional notes…">{{ $candidate->remarks }}</textarea>
                        </div>
                    </div>

                </div>
                <div class="ms2-footer">
                    <button type="button" class="ms2-btn-back" data-prev="2">Back</button>
                    <button type="submit" class="ms2-btn-submit">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>{{-- /fieldset-3 --}}

        </form>
    </div>{{-- /.ms2-card --}}
</div>{{-- /.ms2-wrap --}}
</div>{{-- /.content-wrapper --}}
@endsection

@push('scripts')
<script>
$(function () {

    /* ── Stepper navigation ─────────────────────────────────────── */
    function goToStep(n) {
        $('.ms2-fieldset').removeClass('active');
        $('#fieldset-' + n).addClass('active');

        $('.ms2-step').each(function (i) {
            var s = i + 1;
            $(this).removeClass('active done');
            if (s < n)  $(this).addClass('done');
            if (s === n) $(this).addClass('active');
        });

        $('#line-1-2').toggleClass('done', n > 1);
        $('#line-2-3').toggleClass('done', n > 2);

        $('html, body').animate({ scrollTop: $('.ms2-card').offset().top - 20 }, 300);
    }

    $(document).on('click', '.ms2-btn-next', function () {
        goToStep(parseInt($(this).data('next')));
    });
    $(document).on('click', '.ms2-btn-back', function () {
        goToStep(parseInt($(this).data('prev')));
    });

    /* ── File input label ───────────────────────────────────────── */
    $('#upload').on('change', function () {
        var name = $(this).val().split('\\').pop();
        if (name) {
            $('#file-label').html('<i class="fas fa-check-circle" style="color:#28a745"></i> ' + name);
        } else {
            $('#file-label').html('<i class="fas fa-image"></i> Upload Profile Image');
        }
    });

});
</script>
@endpush

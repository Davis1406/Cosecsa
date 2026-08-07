@extends('layout.app')


@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Trainee</div>
    <div class="ms2-page-sub">Update personal information and associate details for the trainee.</div>

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
                <div class="ms2-step-label">Admission Details</div>
            </div>
            <div class="ms2-line" id="line-2-3"></div>
            <div class="ms2-step" id="step-3">
                <div class="ms2-step-circle">3</div>
                <div class="ms2-step-label">Payment Records</div>
            </div>
        </div>

        {{-- ── Form ────────────────────────────────────────────── --}}
        <form id="ms2form" method="POST"
              action="{{ url('admin/associates/trainees/edit/'.$trainee->trainee_id) }}"
              enctype="multipart/form-data">
            @csrf

            {{-- ══ Step 1: Personal Information ══════════════════ --}}
            <div class="ms2-fieldset active" id="fieldset-1">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">First Name <span class="req">*</span></label>
                            <input type="text" name="firstname" class="ms2-input"
                                   value="{{ $trainee->firstname }}" required>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Middle Name</label>
                            <input type="text" name="middlename" class="ms2-input"
                                   value="{{ $trainee->middlename }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Surname <span class="req">*</span></label>
                            <input type="text" name="lastname" class="ms2-input"
                                   value="{{ $trainee->lastname }}" required>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Personal Email</label>
                            <input type="email" name="personal_email" class="ms2-input"
                                   value="{{ $trainee->personal_email }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Gender</label>
                            <select name="gender" class="ms2-input">
                                <option value="">Select Gender</option>
                                <option value="Male"   {{ $trainee->gender == 'Male'   ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $trainee->gender == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">SFS Username</label>
                            <div class="ms2-input-group">
                                <i class="ms2-icon fas fa-user"></i>
                                <input type="text" name="email" class="ms2-input"
                                       value="{{ $trainee->user_email }}">
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
                            <label class="ms2-label">Associate Type</label>
                            <select name="user_type" class="ms2-input">
                                <option value="2" {{ $trainee->user_type == 2 ? 'selected' : '' }}>Trainee</option>
                                <option value="3" {{ $trainee->user_type == 3 ? 'selected' : '' }}>Programme Director</option>
                                <option value="4" {{ $trainee->user_type == 4 ? 'selected' : '' }}>Trainer</option>
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Profile Image</label>
                            <div class="ms2-file-row">
                                <label class="ms2-file-name" id="file-label" for="upload">
                                    <i class="fas fa-image"></i> Choose image…
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

            {{-- ══ Step 2: Admission Details ══════════════════════ --}}
            <div class="ms2-fieldset" id="fieldset-2">
                <div class="ms2-body">

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Programme <span class="req">*</span></label>
                            <select name="programme_id" id="programme_id" class="ms2-input" required>
                                <option value="">Select Programme</option>
                                @foreach($getProgramme as $programme)
                                    <option value="{{ $programme->id }}"
                                        {{ $trainee->programme_id == $programme->id ? 'selected' : '' }}>
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
                                        {{ $trainee->hospital_id == $hospital->id ? 'selected' : '' }}>
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
                                        {{ $trainee->country_id == $country->id ? 'selected' : '' }}>
                                        {{ $country->country_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">PE / Entry Number</label>
                            <input type="text" name="entry_number" class="ms2-input"
                                   value="{{ $trainee->entry_number }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col ms2-col-3">
                            <label class="ms2-label">Admission Year</label>
                            <select name="admission_year" class="ms2-input">
                                <option value="">Select Year</option>
                                @for($y = 2009; $y <= 2030; $y++)
                                    <option value="{{ $y }}" {{ $trainee->admission_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="ms2-col ms2-col-3">
                            <label class="ms2-label">Exam Year</label>
                            <select name="exam_year" class="ms2-input">
                                <option value="">Select Year</option>
                                @for($y = 2020; $y <= 2035; $y++)
                                    <option value="{{ $y }}" {{ $trainee->exam_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="ms2-col ms2-col-3">
                            <label class="ms2-label">Programme Duration</label>
                            <select name="programme_period" class="ms2-input">
                                <option value="1" {{ $trainee->programme_period == 1 ? 'selected' : '' }}>1 Year</option>
                                <option value="2" {{ $trainee->programme_period == 2 ? 'selected' : '' }}>2 Years</option>
                                <option value="3" {{ $trainee->programme_period == 3 ? 'selected' : '' }}>3 Years</option>
                                <option value="4" {{ $trainee->programme_period == 4 ? 'selected' : '' }}>4 Years</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Study Year (Current)</label>
                            <select name="training_year" id="training_year" class="ms2-input">
                                <option value="">Select Study Year</option>
                                @foreach($getStudyYear as $sy)
                                    <option value="{{ $sy->id }}" data-prog="{{ $sy->programme_id }}"
                                        {{ $trainee->training_year == $sy->id ? 'selected' : '' }}>
                                        {{ $sy->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Trainee Status</label>
                            <select name="status" class="ms2-input">
                                <option value="Active"    {{ $trainee->status == 'Active'    ? 'selected' : '' }}>Active</option>
                                <option value="Enrolled"  {{ $trainee->status == 'Enrolled'  ? 'selected' : '' }}>Enrolled</option>
                                <option value="Approved"  {{ $trainee->status == 'Approved'  ? 'selected' : '' }}>Approved</option>
                                <option value="Graduated" {{ $trainee->status == 'Graduated' ? 'selected' : '' }}>Graduated</option>
                                <option value="Deffered"  {{ $trainee->status == 'Deffered'  ? 'selected' : '' }}>Deferred</option>
                                <option value="Inactive"  {{ $trainee->status == 'Inactive'  ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Admission Letter Status</label>
                            <select name="admission_letter_status" class="ms2-input">
                                <option value="Pending" {{ $trainee->admission_letter_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Sent"    {{ $trainee->admission_letter_status == 'Sent'    ? 'selected' : '' }}>Sent</option>
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Invitation Letter Status</label>
                            <select name="invitation_letter_status" class="ms2-input">
                                <option value="Pending" {{ $trainee->invitation_letter_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Sent"    {{ $trainee->invitation_letter_status == 'Sent'    ? 'selected' : '' }}>Sent</option>
                            </select>
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">SFS / e-Logbook Username</label>
                            <input type="text" name="sfs_username" class="ms2-input"
                                   value="{{ $trainee->sfs_username }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">SFS / e-Logbook Password</label>
                            <input type="text" name="sfs_password" class="ms2-input"
                                   value="{{ $trainee->sfs_password }}">
                            <span class="ms2-hint-text">
                                Used to merge into the Invitation Letter — not the trainee's MIS login.
                            </span>
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
                                   value="{{ $trainee->invoice_number }}">
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Invoice Date</label>
                            <input type="date" name="invoice_date" class="ms2-input"
                                   value="{{ $trainee->invoice_date }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Invoice Status</label>
                            <select name="invoice_status" class="ms2-input">
                                <option value="Pending" {{ $trainee->invoice_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Sent"    {{ $trainee->invoice_status == 'Sent'    ? 'selected' : '' }}>Sent</option>
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Sponsor</label>
                            <input type="text" name="sponsor" class="ms2-input"
                                   value="{{ $trainee->sponsor }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Mode of Payment</label>
                            <select name="mode_of_payment" class="ms2-input">
                                <option value="">Select Mode</option>
                                <option value="Country Rep"           {{ $trainee->mode_of_payment == 'Country Rep'           ? 'selected' : '' }}>Country Rep</option>
                                <option value="Bank transfer"         {{ $trainee->mode_of_payment == 'Bank transfer'         ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Online Payment System" {{ $trainee->mode_of_payment == 'Online Payment System' ? 'selected' : '' }}>Online Payment System</option>
                            </select>
                        </div>
                        <div class="ms2-col">
                            <label class="ms2-label">Amount Paid (USD)</label>
                            <input type="number" step="0.01" name="amount_paid" class="ms2-input"
                                   value="{{ $trainee->amount_paid }}">
                        </div>
                    </div>

                    <div class="ms2-row">
                        <div class="ms2-col">
                            <label class="ms2-label">Date Paid</label>
                            <input type="date" name="payment_date" class="ms2-input"
                                   value="{{ $trainee->payment_date }}">
                        </div>
                        <div class="ms2-col">{{-- spacer --}}</div>
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

        // Update connecting lines
        $('#line-1-2').toggleClass('done', n > 1);
        $('#line-2-3').toggleClass('done', n > 2);

        // Smooth scroll to top of card
        $('html, body').animate({ scrollTop: $('.ms2-card').offset().top - 20 }, 300);
    }

    $(document).on('click', '.ms2-btn-next', function () {
        goToStep(parseInt($(this).data('next')));
    });
    $(document).on('click', '.ms2-btn-back', function () {
        goToStep(parseInt($(this).data('prev')));
    });

    /* ── Study year — filter by programme ──────────────────────── */
    function filterStudyYears() {
        var progId  = String($('#programme_id').val());
        var current = String($('#training_year').val());

        $('#training_year option').each(function () {
            var opt = $(this);
            if (!opt.val()) return;
            var match = !progId || String(opt.data('prog')) === progId;
            opt.prop('hidden', !match);
            if (!match && opt.is(':selected')) opt.prop('selected', false);
        });

        if (current) {
            var $t = $('#training_year option[value="' + current + '"]');
            if (!$t.prop('hidden')) $t.prop('selected', true);
        }
    }
    filterStudyYears();
    $('#programme_id').on('change', filterStudyYears);

    /* ── File input label update ────────────────────────────────── */
    $('#upload').on('change', function () {
        var name = $(this).val().split('\\').pop();
        if (name) {
            $('#file-label').html('<i class="fas fa-check-circle" style="color:#28a745"></i> ' + name);
        } else {
            $('#file-label').html('<i class="fas fa-image"></i> Choose image…');
        }
    });

});
</script>
@endpush

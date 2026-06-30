<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ $year }} Examination Availability | COSECSA</title>
    <link rel="stylesheet" href="{{ url('public/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="icon" href="{{ url('public/dist/img/Cosecsa_Logo.png') }}">
    <style>
        body.availability-page {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            background: #f4f6f9;
            padding: 2rem 0;
        }
        .availability-box {
            width: 520px;
            margin: 0 auto;
        }
        @media (max-width: 576px) {
            .availability-box { width: 95%; }
            body.availability-page { padding: 1rem 0; }
        }

        /* Card */
        .card.card-cosecsa {
            border-top: 4px solid #a02626;
            box-shadow: 0 2px 20px rgba(0,0,0,.1);
            border-radius: .5rem;
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            text-align: center;
            padding: 1.5rem 1rem 1rem;
            border-radius: calc(.5rem - 1px) calc(.5rem - 1px) 0 0;
        }
        .card-header img { max-width: 72px; height: auto; margin-bottom: .6rem; }
        .card-header h4 { color: #a02626; font-weight: 700; margin: 0; font-size: 1.15rem; }
        .card-header p  { color: #6c757d; font-size: .84rem; margin: .2rem 0 0; }

        /* Primary button */
        .btn-cosecsa {
            background-color: #a02626;
            border-color: #a02626;
            color: #fff;
            height: 46px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: .375rem;
        }
        .btn-cosecsa:hover, .btn-cosecsa:focus {
            background-color: #870f0f;
            border-color: #870f0f;
            color: #FEC503;
        }

        /* Select2 override */
        .select2-container--bootstrap4 .select2-selection--single {
            height: 45px !important;
            line-height: 45px !important;
            font-size: 1rem;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 43px !important;
            padding-left: .75rem;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 43px !important;
        }
        .select2-container--bootstrap4 .select2-results__option--highlighted {
            background-color: #a02626 !important;
        }

        /* Availability checkbox cards */
        .avail-options { display: flex; flex-direction: column; gap: .55rem; }
        .avail-label {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .75rem 1rem;
            border: 2px solid #dee2e6;
            border-radius: .4rem;
            cursor: pointer;
            transition: border-color .12s, background .12s;
            font-size: .94rem;
            margin-bottom: 0;
        }
        .avail-label:hover { border-color: #a02626; background: #fdf5f5; }
        .avail-label.selected { border-color: #a02626; background: #fdf5f5; }
        .avail-label input[type="checkbox"] { width: 18px; height: 18px; accent-color: #a02626; flex-shrink: 0; }
        .avail-icon { font-size: 1.1rem; width: 22px; text-align: center; flex-shrink: 0; }
        .avail-icon.mcs       { color: #0055a5; }
        .avail-icon.fcs       { color: #28a745; }
        .avail-icon.tentative { color: #e67e00; }
        .avail-icon.none      { color: #dc3545; }
        .avail-label.tentative-label { border-style: dashed; }

        /* Shift section */
        #shift-section {
            background: #eef4ff;
            border: 1px solid #b8d0f5;
            border-radius: .4rem;
            padding: .9rem 1rem;
            margin-top: .6rem;
        }
        #shift-section .shift-label { font-weight: 600; color: #0055a5; margin-bottom: .5rem; font-size: .9rem; }
        .shift-options { display: flex; gap: .5rem; flex-wrap: wrap; }
        .shift-btn {
            flex: 1;
            min-width: 100px;
            padding: .5rem .5rem;
            border: 2px solid #b8d0f5;
            border-radius: .375rem;
            text-align: center;
            cursor: pointer;
            font-size: .88rem;
            font-weight: 500;
            color: #0055a5;
            background: #fff;
            transition: all .12s;
        }
        .shift-btn:hover { border-color: #0055a5; background: #dce8fb; }
        .shift-btn.active { border-color: #0055a5; background: #0055a5; color: #fff; }
        .shift-btn input[type="radio"] { display: none; }

        /* Tentative sub-options */
        #tentative-sub {
            background: #fff8ee;
            border: 1px dashed #e67e00;
            border-radius: .4rem;
            padding: .7rem .9rem;
            margin-top: .45rem;
        }
        .tentative-sub-label {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .55rem .7rem;
            border: 1px dashed #e6a040;
            border-radius: .35rem;
            cursor: pointer;
            font-size: .91rem;
            margin-bottom: .4rem;
            background: #fff;
            transition: border-color .12s, background .12s;
        }
        .tentative-sub-label:last-child { margin-bottom: 0; }
        .tentative-sub-label:hover { border-color: #e67e00; background: #fff3e0; }
        .tentative-sub-label.selected { border-color: #e67e00; background: #fff3e0; }
        .tentative-sub-label input[type="checkbox"] { width: 16px; height: 16px; accent-color: #e67e00; flex-shrink: 0; }
        .tentative-toggle-row {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .75rem 1rem;
            border: 2px dashed #dee2e6;
            border-radius: .4rem;
            cursor: pointer;
            transition: border-color .12s, background .12s;
            font-size: .94rem;
            user-select: none;
        }
        .tentative-toggle-row:hover { border-color: #e67e00; background: #fff8ee; }
        .tentative-toggle-row.has-selection { border-color: #e67e00; background: #fff8ee; }
        .tentative-arrow { margin-left: auto; color: #e67e00; transition: transform .15s; }
        .tentative-arrow.open { transform: rotate(180deg); }

        .form-group label { font-weight: 600; color: #343a40; margin-bottom: .4rem; }
        footer.page-footer { text-align: center; color: #adb5bd; font-size: .78rem; margin-top: 1.25rem; padding-bottom: 1rem; }
    </style>
</head>
<body class="availability-page">

<div class="availability-box">
    <div class="card card-cosecsa">

        {{-- Header --}}
        <div class="card-header">
            <img src="{{ url('public/dist/img/Cosecsa_Logo.png') }}" alt="COSECSA Logo">
            <h4>COSECSA {{ $year }} Examination</h4>
            <p>Examiner Availability Confirmation</p>
        </div>

        <div class="card-body pt-3 pb-4">

            {{-- Alerts --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if(!session('success'))
            <p class="text-muted mb-3" style="font-size:.88rem;">
                Select your name from the list and indicate your availability for the
                <strong>{{ $year }}</strong> COSECSA examination.
            </p>

            <form method="POST" action="{{ route('examiner.availability.submit') }}" id="availForm">
                @csrf

                {{-- Examiner select --}}
                <div class="form-group">
                    <label for="exm_id"><i class="fas fa-user-md mr-1"></i> Select Your Name</label>
                    <select name="exm_id"
                            id="exm_id"
                            class="form-control @error('exm_id') is-invalid @enderror"
                            required>
                        <option value="">— Search by name or examiner ID —</option>
                        @foreach($examiners as $ex)
                            <option value="{{ $ex->exm_id }}"
                                {{ old('exm_id') == $ex->exm_id ? 'selected' : '' }}>
                                {{ $ex->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('exm_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Availability --}}
                <div class="form-group">
                    <label><i class="fas fa-calendar-check mr-1"></i> Examination Availability <span class="text-danger">*</span></label>
                    <small class="form-text text-muted mb-2 d-block">Select all that apply. Choose <em>Tentative</em> if you are not yet certain.</small>

                    <div class="avail-options">
                        {{-- MCS --}}
                        <div>
                            <label class="avail-label" id="label-MCS">
                                <input type="checkbox" name="exam_availability[]" value="MCS" id="chk-mcs"
                                       {{ in_array('MCS', old('exam_availability', [])) ? 'checked' : '' }}>
                                <span class="avail-icon mcs"><i class="fas fa-stethoscope"></i></span>
                                <div>
                                    <strong>MCS</strong>
                                    <span class="text-muted" style="font-size:.85rem;"> — Membership of the College of Surgeons</span>
                                </div>
                            </label>

                            {{-- Shift section (visible only when MCS is checked) --}}
                            <div id="shift-section" style="{{ in_array('MCS', old('exam_availability', [])) ? '' : 'display:none;' }}">
                                <div class="shift-label">
                                    <i class="fas fa-clock mr-1"></i> MCS Shift Preference
                                </div>
                                <div class="shift-options">
                                    @php $oldShift = old('mcs_shift', ''); @endphp
                                    <label class="shift-btn {{ $oldShift == '1' ? 'active' : '' }}">
                                        <input type="radio" name="mcs_shift" value="1" {{ $oldShift == '1' ? 'checked' : '' }}>
                                        <i class="fas fa-sun mr-1"></i> Morning
                                    </label>
                                    <label class="shift-btn {{ $oldShift == '2' ? 'active' : '' }}">
                                        <input type="radio" name="mcs_shift" value="2" {{ $oldShift == '2' ? 'checked' : '' }}>
                                        <i class="fas fa-cloud-sun mr-1"></i> Both
                                    </label>
                                    <label class="shift-btn {{ $oldShift == '3' ? 'active' : '' }}">
                                        <input type="radio" name="mcs_shift" value="3" {{ $oldShift == '3' ? 'checked' : '' }}>
                                        <i class="fas fa-moon mr-1"></i> Afternoon
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- FCS --}}
                        <label class="avail-label" id="label-FCS">
                            <input type="checkbox" name="exam_availability[]" value="FCS" id="chk-fcs"
                                   {{ in_array('FCS', old('exam_availability', [])) ? 'checked' : '' }}>
                            <span class="avail-icon fcs"><i class="fas fa-user-md"></i></span>
                            <div>
                                <strong>FCS</strong>
                                <span class="text-muted" style="font-size:.85rem;"> — Fellowship of the College of Surgeons</span>
                            </div>
                        </label>

                        {{-- Tentative (expandable) --}}
                        @php
                            $oldAvail     = old('exam_availability', []);
                            $tentFcsOld   = in_array('Tentative FCS', $oldAvail);
                            $tentMcsOld   = in_array('Tentative MCS', $oldAvail);
                            $tentOldAny   = $tentFcsOld || $tentMcsOld || in_array('Tentative', $oldAvail);
                        @endphp
                        <div>
                            <div class="tentative-toggle-row {{ $tentOldAny ? 'has-selection' : '' }}" id="tentative-toggle">
                                <span class="avail-icon tentative"><i class="fas fa-question-circle"></i></span>
                                <div>
                                    <strong>Tentative</strong>
                                    <span class="text-muted" style="font-size:.85rem;"> — I may be available but cannot confirm yet</span>
                                </div>
                                <i class="fas fa-chevron-down tentative-arrow {{ $tentOldAny ? 'open' : '' }}" id="tentative-arrow"></i>
                            </div>

                            <div id="tentative-sub" style="{{ $tentOldAny ? '' : 'display:none;' }}">
                                <label class="tentative-sub-label {{ $tentFcsOld ? 'selected' : '' }}" id="label-TentFCS">
                                    <input type="checkbox" name="exam_availability[]" value="Tentative FCS"
                                           id="chk-tentative-fcs" {{ $tentFcsOld ? 'checked' : '' }}>
                                    <span class="avail-icon fcs"><i class="fas fa-user-md"></i></span>
                                    <div>
                                        <strong>Tentative for FCS</strong>
                                        <span class="text-muted" style="font-size:.83rem;"> — Fellowship, not yet confirmed</span>
                                    </div>
                                </label>
                                <label class="tentative-sub-label {{ $tentMcsOld ? 'selected' : '' }}" id="label-TentMCS">
                                    <input type="checkbox" name="exam_availability[]" value="Tentative MCS"
                                           id="chk-tentative-mcs" {{ $tentMcsOld ? 'checked' : '' }}>
                                    <span class="avail-icon mcs"><i class="fas fa-stethoscope"></i></span>
                                    <div>
                                        <strong>Tentative for MCS</strong>
                                        <span class="text-muted" style="font-size:.83rem;"> — Membership, not yet confirmed</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Not Available --}}
                        <label class="avail-label" id="label-NotAvailable">
                            <input type="checkbox" name="exam_availability[]" value="Not Available" id="chk-not-available"
                                   {{ in_array('Not Available', old('exam_availability', [])) ? 'checked' : '' }}>
                            <span class="avail-icon none"><i class="fas fa-times-circle"></i></span>
                            <div>
                                <strong>Not Available</strong>
                                <span class="text-muted" style="font-size:.85rem;"> — I am unable to participate this year</span>
                            </div>
                        </label>
                    </div>
                    @error('exam_availability')
                        <div class="text-danger mt-1" style="font-size:.875rem;">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-cosecsa btn-block mt-3">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Availability
                </button>
            </form>

            @else
            <div class="text-center mt-2">
                <a href="{{ route('examiner.availability.form') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo mr-1"></i> Submit for another examiner
                </a>
            </div>
            @endif
        </div>
    </div>

    <footer class="page-footer">
        &copy; {{ date('Y') }} College of Surgeons of East, Central and Southern Africa (COSECSA)
    </footer>
</div>

{{-- JS --}}
<script src="{{ url('public/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ url('public/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ url('public/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {

    // ── Select2 searchable dropdown ───────────────────────────────────────────
    $('#exm_id').select2({
        theme: 'bootstrap4',
        placeholder: '— Search by name or examiner ID —',
        allowClear: false,
        width: '100%'
    });

    // ── Availability logic ────────────────────────────────────────────────────
    const chkMCS        = document.getElementById('chk-mcs');
    const chkFCS        = document.getElementById('chk-fcs');
    const chkTentFCS    = document.getElementById('chk-tentative-fcs');
    const chkTentMCS    = document.getElementById('chk-tentative-mcs');
    const chkNone       = document.getElementById('chk-not-available');
    const shiftSection  = document.getElementById('shift-section');
    const tentSub       = document.getElementById('tentative-sub');
    const tentToggle    = document.getElementById('tentative-toggle');
    const tentArrow     = document.getElementById('tentative-arrow');

    // ── Tentative accordion toggle ────────────────────────────────────────────
    tentToggle.addEventListener('click', function () {
        const isOpen = tentSub.style.display !== 'none';
        tentSub.style.display = isOpen ? 'none' : '';
        tentArrow.classList.toggle('open', !isOpen);
        if (isOpen) {
            // Collapsing: uncheck any tentative sub-options
            chkTentFCS.checked = false;
            chkTentMCS.checked = false;
            syncTentativeHighlight();
        }
        syncTentativeToggle();
        // Opening: uncheck Not Available
        if (!isOpen && chkNone.checked) {
            chkNone.checked = false;
            syncHighlight(chkNone);
        }
    });

    // ── Sync helpers ──────────────────────────────────────────────────────────
    function syncHighlight(chk) {
        const lbl = chk.closest('.avail-label');
        if (lbl) lbl.classList.toggle('selected', chk.checked);
    }

    function syncTentativeHighlight() {
        document.getElementById('label-TentFCS').classList.toggle('selected', chkTentFCS.checked);
        document.getElementById('label-TentMCS').classList.toggle('selected', chkTentMCS.checked);
    }

    function syncTentativeToggle() {
        const anyTent = chkTentFCS.checked || chkTentMCS.checked;
        tentToggle.classList.toggle('has-selection', anyTent);
    }

    function toggleShift() {
        if (chkMCS.checked) {
            shiftSection.style.display = '';
        } else {
            shiftSection.style.display = 'none';
            document.querySelectorAll('input[name="mcs_shift"]').forEach(r => r.checked = false);
            document.querySelectorAll('.shift-btn').forEach(b => b.classList.remove('active'));
        }
    }

    // ── Not Available: clears everything ─────────────────────────────────────
    chkNone.addEventListener('change', function () {
        if (this.checked) {
            chkMCS.checked = false; toggleShift();
            chkFCS.checked = false;
            chkTentFCS.checked = false;
            chkTentMCS.checked = false;
            tentSub.style.display = 'none';
            tentArrow.classList.remove('open');
            syncTentativeHighlight();
            syncTentativeToggle();
        }
        syncHighlight(chkMCS);
        syncHighlight(chkFCS);
        syncHighlight(this);
    });

    // ── MCS (confirmed): uncheck Tentative MCS (same exam conflict) ───────────
    chkMCS.addEventListener('change', function () {
        if (this.checked) {
            chkNone.checked = false; syncHighlight(chkNone);
            chkTentMCS.checked = false;
            syncTentativeHighlight();
            syncTentativeToggle();
        }
        toggleShift();
        syncHighlight(this);
    });

    // ── FCS (confirmed): uncheck Tentative FCS (same exam conflict) ───────────
    chkFCS.addEventListener('change', function () {
        if (this.checked) {
            chkNone.checked = false; syncHighlight(chkNone);
            chkTentFCS.checked = false;
            syncTentativeHighlight();
            syncTentativeToggle();
        }
        syncHighlight(this);
    });

    // ── Tentative FCS: uncheck confirmed FCS (same exam conflict) ────────────
    chkTentFCS.addEventListener('change', function () {
        if (this.checked) {
            chkFCS.checked = false; syncHighlight(chkFCS);
            chkNone.checked = false; syncHighlight(chkNone);
        }
        syncTentativeHighlight();
        syncTentativeToggle();
    });

    // ── Tentative MCS: uncheck confirmed MCS (same exam conflict) ────────────
    chkTentMCS.addEventListener('change', function () {
        if (this.checked) {
            chkMCS.checked = false; toggleShift();
            syncHighlight(chkMCS);
            chkNone.checked = false; syncHighlight(chkNone);
        }
        syncTentativeHighlight();
        syncTentativeToggle();
    });

    // Shift button highlight
    document.querySelectorAll('.shift-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.shift-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Init
    syncHighlight(chkMCS);
    syncHighlight(chkFCS);
    syncHighlight(chkNone);
    syncTentativeHighlight();
    syncTentativeToggle();
    toggleShift();
});
</script>
</body>
</html>

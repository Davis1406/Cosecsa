<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ $year }} Examination Availability | COSECSA</title>
    <link rel="stylesheet" href="{{ url('public/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/dist/css/adminlte.min.css') }}">
    <link rel="icon" href="{{ url('public/dist/img/Cosecsa_Logo.png') }}">
    <style>
        body.availability-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: #f4f6f9;
        }
        .availability-box {
            width: 480px;
            margin: 5% auto;
        }
        @media (max-width: 576px) {
            .availability-box { width: 95%; margin: 4% auto; }
        }
        .card.card-outline.card-cosecsa {
            border-top: 4px solid #a02626;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            text-align: center;
            padding: 1.5rem 1rem 1rem;
        }
        .card-header img { max-width: 75px; height: auto; margin-bottom: .75rem; }
        .card-header h4 { color: #a02626; font-weight: 700; margin: 0; font-size: 1.1rem; }
        .card-header p  { color: #6c757d; font-size: .85rem; margin: .25rem 0 0; }

        .btn-cosecsa {
            background-color: #a02626;
            border-color: #a02626;
            color: #fff;
            height: 46px;
            font-size: 1rem;
            font-weight: 600;
        }
        .btn-cosecsa:hover {
            background-color: #870f0f;
            border-color: #870f0f;
            color: #FEC503;
        }

        /* Availability checkbox cards */
        .avail-options { display: flex; flex-direction: column; gap: .6rem; }
        .avail-label {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem 1rem;
            border: 2px solid #dee2e6;
            border-radius: .375rem;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            font-size: .95rem;
        }
        .avail-label:hover { border-color: #a02626; background: #fdf5f5; }
        .avail-label input[type="checkbox"] { width: 18px; height: 18px; accent-color: #a02626; flex-shrink: 0; }
        .avail-label.selected { border-color: #a02626; background: #fdf5f5; }
        .avail-icon { font-size: 1.1rem; width: 22px; text-align: center; }
        .avail-icon.mcs  { color: #0055a5; }
        .avail-icon.fcs  { color: #28a745; }
        .avail-icon.none { color: #dc3545; }

        .form-group label { font-weight: 600; color: #343a40; }
        footer.page-footer { text-align: center; color: #adb5bd; font-size: .78rem; margin-top: 1.5rem; }
    </style>
</head>
<body class="availability-page">

<div class="availability-box">
    <div class="card card-outline card-cosecsa">

        {{-- Header --}}
        <div class="card-header">
            <img src="{{ url('public/dist/img/Cosecsa_Logo.png') }}" alt="COSECSA Logo">
            <h4>COSECSA {{ $year }} Examination</h4>
            <p>Examiner Availability Confirmation</p>
        </div>

        <div class="card-body pt-3">

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
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!session('success'))
            <p class="text-muted mb-3" style="font-size:.9rem;">
                Please enter your registered COSECSA email address and indicate your availability
                for the <strong>{{ $year }}</strong> examination.
            </p>

            <form method="POST" action="{{ route('examiner.availability.submit') }}">
                @csrf

                {{-- Email --}}
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope mr-1"></i> Registered Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="e.g. jsmith@cosecsa.org"
                           value="{{ old('email') }}"
                           required
                           style="height:45px; font-size:1rem;">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Availability --}}
                <div class="form-group">
                    <label><i class="fas fa-calendar-check mr-1"></i> Examination Availability <span class="text-danger">*</span></label>
                    <small class="form-text text-muted mb-2 d-block">Select all examinations you are available to participate in.</small>
                    <div class="avail-options" id="availOptions">
                        <label class="avail-label" id="label-MCS">
                            <input type="checkbox" name="exam_availability[]" value="MCS"
                                   {{ in_array('MCS', old('exam_availability', [])) ? 'checked' : '' }}>
                            <span class="avail-icon mcs"><i class="fas fa-stethoscope"></i></span>
                            <div>
                                <strong>MCS</strong> — Membership of the College of Surgeons
                            </div>
                        </label>

                        <label class="avail-label" id="label-FCS">
                            <input type="checkbox" name="exam_availability[]" value="FCS"
                                   {{ in_array('FCS', old('exam_availability', [])) ? 'checked' : '' }}>
                            <span class="avail-icon fcs"><i class="fas fa-user-md"></i></span>
                            <div>
                                <strong>FCS</strong> — Fellowship of the College of Surgeons
                            </div>
                        </label>

                        <label class="avail-label" id="label-NotAvailable">
                            <input type="checkbox" name="exam_availability[]" value="Not Available" id="chk-not-available"
                                   {{ in_array('Not Available', old('exam_availability', [])) ? 'checked' : '' }}>
                            <span class="avail-icon none"><i class="fas fa-times-circle"></i></span>
                            <div>
                                <strong>Not Available</strong> — I am unable to participate this year
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
            {{-- Post-submit: offer to submit for another examiner --}}
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const notAvailChk = document.getElementById('chk-not-available');
    const allChks = document.querySelectorAll('input[name="exam_availability[]"]');
    const labels = document.querySelectorAll('.avail-label');

    // Highlight selected labels
    function syncHighlight() {
        allChks.forEach(function(chk) {
            const lbl = chk.closest('.avail-label');
            if (chk.checked) lbl.classList.add('selected');
            else lbl.classList.remove('selected');
        });
    }

    // "Not Available" is mutually exclusive with MCS/FCS
    notAvailChk.addEventListener('change', function () {
        if (this.checked) {
            allChks.forEach(function(chk) {
                if (chk !== notAvailChk) chk.checked = false;
            });
        }
        syncHighlight();
    });

    allChks.forEach(function(chk) {
        if (chk === notAvailChk) return;
        chk.addEventListener('change', function () {
            if (this.checked) notAvailChk.checked = false;
            syncHighlight();
        });
    });

    syncHighlight();
});
</script>
</body>
</html>

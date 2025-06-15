@extends('layout.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6" style="text-align: left">
                        <a href="{{ url('admin/exams/examiners') }}" class="btn btn-primary"
                            style="background-color: #a02626; border-color:#a02626">
                            <span class="fas fa-arrow-left"></span> Examiners List
                        </a>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- Main content -->
        <section class="content">
            <!-- general form elements -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Examiners Details</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#idBadgeModal"
                            style="background-color: #a02626; border-color:#a02626">
                            Generate ID Badge
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        @if ($examiner)
                            <div class="col-md-3">
                                <div class="text-center">
                                    <img src="{{ !empty($examiner->passport_image) ? asset('storage/app/public/' . $examiner->passport_image) : asset('/public/dist/img/user.png') }}"
                                        alt="{{ $examiner->examiner_name }}" class="img-fluid img-thumbnail"
                                        style="width: 50%; height: 50%;">
                                    <h5 class="mt-2">{{ $examiner->examiner_name }}</h5>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Full Name</th>
                                        <td>{{ $examiner->examiner_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $examiner->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Country</th>
                                        <td>{{ $examiner->country_name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Gender</th>
                                        <td>{{ $examiner->gender ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile Number</th>
                                        <td>{{ $examiner->mobile ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Examiner ID</th>
                                        <td>{{ $examiner->examiner_id ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Assigned Groups</th>
                                        <td>
                                            @if (isset($examiner->groups) && $examiner->groups->isNotEmpty())
                                                @foreach ($examiner->groups as $group)
                                                    <span>
                                                        Group {{ $group->group_name }}
                                                    </span>
                                                @endforeach
                                            @elseif($examiner->group_name)
                                                <span class="badge badge-primary" style="background-color: #a02626;">
                                                    {{ $examiner->group_name }}
                                                    @if ($examiner->group_id)
                                                        <small>(ID: {{ $examiner->group_id }})</small>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Assigned Shifts</th>
                                        <td>
                                            @if (isset($examiner->shifts) && $examiner->shifts->isNotEmpty())
                                                @foreach ($examiner->shifts as $shift)
                                                    {{ App\Models\User::getShiftName($shift->shift) }}
                                                @endforeach
                                            @elseif($examiner->shift)
                                                {{ App\Models\User::getShiftName($examiner->shift) }}
                                            @else
                                                No shifts assigned
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Examiner Role</th>
                                        <td>{{ $examiner->role_id == 1 ? 'Examiner' : 'Observer' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Current Specialty</th>
                                        <td>{{ $examiner->specialty ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Sub Specialty</th>
                                        <td>{{ $examiner->subspecialty ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Curriculum Vitae</th>
                                        <td>
                                            @if ($examiner->curriculum_vitae)
                                                @php
                                                    $fileName = basename($examiner->curriculum_vitae);
                                                    $filePath = asset(
                                                        'storage/app/public/' . $examiner->curriculum_vitae,
                                                    );
                                                @endphp
                                                <a href="{{ $filePath }}" target="_blank"
                                                    class="btn btn-sm btn-primary"
                                                    style="background-color: #a02626; border-color:#a02626">
                                                    <i class="fas fa-download"></i> Download CV ({{ $fileName }})
                                                </a>
                                            @else
                                                <span class="text-muted">No CV uploaded</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Participation History</th>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                                data-target="#examinerHistoryModal"
                                                style="background-color: #a02626; border-color: #a02626;">
                                                <i class="fas fa-history"></i> View History
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        @else
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No Examiner Data found.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- ID Badge Modal -->
    <div class="modal fade" id="idBadgeModal" tabindex="-1" role="dialog" aria-labelledby="idBadgeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 400px;">
            <div class="modal-content">
                <div class="modal-body p-3">
                    <div class="id-badge-template mx-auto text-center d-flex flex-column justify-content-between"
                        style="width: 350px; height: 550px; border: 2px solid #a02626; border-radius: 15px; padding: 20px 20px 30px 20px; position: relative; background-color: #fff;">
                        <!-- Top Section -->
                        <div>
                            <!-- Logo -->
                            <div class="mb-3">
                                <img src="{{ asset('/public/dist/img/Cosecsa_Logo.png') }}" alt="COSECSA Logo"
                                    style="width: 100px; height: auto;">
                            </div>

                            <!-- Header -->
                            <div class="mb-3">
                                <h5 style="color: #a02626; font-weight: bold; margin-bottom: 2px;">College of Surgeons of
                                </h5>
                                <h5 style="color: #a02626; font-weight: bold; margin-bottom: 2px;">East Central and Southern
                                    Africa</h5>
                                <h4 style="color: #a02626; font-weight: bold; margin-bottom: 4px;">COSECSA</h4>
                                <h6 style="color: #a02626; font-weight: bold;">EXAMINER IDENTIFICATION</h6>
                            </div>

                            <!-- Profile -->
                            <div class="mb-3">
                                <img src="{{ !empty($examiner->passport_image) ? asset('storage/app/public/' . $examiner->passport_image) : asset('/public/dist/img/user.png') }}"
                                    alt="{{ $examiner->examiner_name }}" class="rounded-circle"
                                    style="width: 120px; height: 120px; object-fit: cover; border: 2px solid #a02626;">
                            </div>

                            <!-- Details -->
                            <div class="text-center mb-2">
                                <h5 style="color: #a02626; font-weight: bold; margin-bottom: 0px;">
                                    {{ $examiner->examiner_name }}</h5>
                                <p style="margin: 0 0 2px;">Examiner - {{ $examiner->examiner_id ?? 'N/A' }}</p>
                                <p style="margin: 0 0 2px;">{{ $examiner->specialty ?? 'General' }}</p>
                            </div>
                        </div>
                        <!-- QR Code Section -->
                        <div class="text-center">
                            <div style="width:55px; height:55px; margin:0 auto; background:white; margin-bottom: 10px;">
                                @if (isset($qrCode))
                                    {!! $qrCode !!}
                                @else
                                    <div style="width:55px; height:55px; background:#f0f0f0; border:1px solid #ccc;"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Action Buttons -->
                    <div class="text-center mt-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="printBadge()">Print</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

       <!-- Examiner History Modal -->
    <div class="modal fade" id="examinerHistoryModal" tabindex="-1" role="dialog"
        aria-labelledby="examinerHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #a02626 0%, #d63031 100%); color: white;">
                    <h5 class="modal-title" id="examinerHistoryModalLabel">
                        <i class="fas fa-history"></i> Examiner Participation History
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Participation Summary -->
                        <div class="col-12 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user-check text-primary"></i> Participation
                                        Overview</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="icon-circle bg-success text-white mr-3">
                                                    <i class="fas fa-laptop"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Virtual MCS Participation</small>
                                                    <div class="font-weight-bold">
                                                        @if ($examiner->virtual_mcs_participated == 'Yes')
                                                            <span class="badge badge-success">Yes</span>
                                                        @else
                                                            <span class="badge badge-secondary">No</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="icon-circle bg-info text-white mr-3">
                                                    <i class="fas fa-stethoscope"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">FCS Participation</small>
                                                    <div class="font-weight-bold">
                                                        @if ($examiner->fcs_participated == 'Yes')
                                                            <span class="badge badge-info">Yes</span>
                                                        @else
                                                            <span class="badge badge-secondary">No</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role and Hospital Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user-md text-warning"></i> Role & Institution</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Participation Type</small>
                                        <div class="font-weight-bold">
                                            <i class="fas fa-user-tie text-primary mr-1"></i>
                                            {{ $examiner->role_id == 1 ? 'Examiner' : 'Observer' }}
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Hospital Type</small>
                                        <div class="font-weight-bold">
                                            <i class="fas fa-hospital text-success mr-1"></i>
                                            {{ $examiner->hospital_type ?: 'Not specified' }}
                                        </div>
                                    </div>
                                    <div>
                                        <small class="text-muted">Hospital Name</small>
                                        <div class="font-weight-bold">
                                            <i class="fas fa-building text-info mr-1"></i>
                                            {{ $examiner->hospital_name ?: 'Not specified' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Examination Years -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-calendar-alt text-danger"></i> Examination Years
                                        (2020-2024)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="examination-years">
                                        @php
                                            $selectedYears = [];
                                            if ($examiner->examination_years) {
                                                if (is_string($examiner->examination_years)) {
                                                    $selectedYears =
                                                        json_decode($examiner->examination_years, true) ?: [];
                                                } elseif (is_array($examiner->examination_years)) {
                                                    $selectedYears = $examiner->examination_years;
                                                }
                                            }
                                        @endphp

                                        <div class="year-badge-container">
                                            @if (!empty($selectedYears))
                                                @foreach ($selectedYears as $year)
                                                    <span
                                                        class="badge badge-primary mr-2 mb-2 px-3 py-2">{{ $year }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No examination years recorded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2025 Exam Availability -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-calendar-check text-success"></i> 2025 Exam
                                        Availability</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="availability-items">
                                                @php
                                                    $selectedAvailability = [];
                                                    if (
                                                        isset($examiner->history) &&
                                                        $examiner->history->exam_availability
                                                    ) {
                                                        if (is_string($examiner->history->exam_availability)) {
                                                            $selectedAvailability =
                                                                json_decode(
                                                                    $examiner->history->exam_availability,
                                                                    true,
                                                                ) ?:
                                                                [];
                                                        } elseif (is_array($examiner->history->exam_availability)) {
                                                            $selectedAvailability =
                                                                $examiner->history->exam_availability;
                                                        }
                                                    }
                                                @endphp

                                                @if (in_array('MCS', $selectedAvailability))
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                                        <span class="font-weight-bold">MCS (12-13 Nov)</span>
                                                    </div>
                                                @endif

                                                @if (in_array('FCS', $selectedAvailability))
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                                        <span class="font-weight-bold">FCS (1–2 December)</span>
                                                    </div>
                                                @endif

                                                @if (empty($selectedAvailability))
                                                    <span class="text-muted">No availability recorded</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="shift-info">
                                                <small class="text-muted">MCS Shift Preference</small>
                                                <div class="font-weight-bold">
                                                    <i class="fas fa-clock text-warning mr-1"></i>
                                                    {{ isset($examiner->shift_id) ? App\Models\User::getShiftName($examiner->shift_id) : 'Not specified' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
        <script>
            window.printBadge = function() {
                const element = document.querySelector('.id-badge-template');
                const options = {
                    scale: 2, // Higher quality
                    backgroundColor: '#ffffff',
                    logging: false,
                    useCORS: true
                };

                html2canvas(element, options).then(canvas => {
                    // Create a new canvas with white margins
                    const margin = 20;
                    const newCanvas = document.createElement('canvas');
                    newCanvas.width = canvas.width + margin * 2;
                    newCanvas.height = canvas.height + margin * 2;
                    const ctx = newCanvas.getContext('2d');

                    // Fill with white background
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, newCanvas.width, newCanvas.height);

                    // Draw original canvas with margin
                    ctx.drawImage(canvas, margin, margin);

                    // Convert to image and download
                    const image = newCanvas.toDataURL('image/png');
                    const link = document.createElement('a');
                    link.href = image;
                    link.download = 'id-badge-' + new Date().toISOString().slice(0, 10) + '.png';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
            }
        </script>
    @endpush

    @push('styles')
        <style>
            .id-badge-template {
                background-color: #fff;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .badge {
                margin-right: 5px;
                margin-bottom: 5px;
            }

            @media print {
                body {
                    margin: 20px !important;
                    background: white !important;
                }

                .id-badge-template {
                    position: relative;
                    width: 100% !important;
                    height: auto !important;
                    margin: 0 auto !important;
                    page-break-after: always;
                    border: none !important;
                    box-shadow: none !important;
                }

                .modal-content {
                    border: none !important;
                    box-shadow: none !important;
                }
            }
        </style>
    @endpush
@endsection

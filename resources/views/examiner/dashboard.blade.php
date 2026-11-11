{{-- Updated examiner dashboard with new routes --}}

@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')

                {{-- ═══════ ROLE SWITCHER (multi-role users only) ═══════ --}}
                @php
                    $roleNames = [1=>'Admin',2=>'Trainee',4=>'Programme Director',5=>'Country Rep',7=>'Fellow',8=>'Member',9=>'Examiner'];
                    $roleIcons = [1=>'fa-shield-alt',2=>'fa-user-graduate',4=>'fa-chalkboard-teacher',5=>'fa-globe-africa',7=>'fa-award',8=>'fa-id-badge',9=>'fa-user-md'];
                @endphp
                @if(!empty($userRoles) && count($userRoles) > 1)
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card mb-0" style="border-left:4px solid #a02626;border-radius:8px;">
                            <div class="card-body py-2 px-3 d-flex align-items-center flex-wrap gap-2">
                                <span style="font-size:.8rem;font-weight:700;color:#a02626;letter-spacing:.5px;text-transform:uppercase;margin-right:10px;">
                                    <i class="fas fa-exchange-alt mr-1"></i>Switch Profile
                                </span>
                                @foreach($userRoles as $r)
                                    @if($r != $activeRole)
                                    <form action="{{ route('switch.role') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="role" value="{{ $r }}">
                                        <button type="submit" class="btn btn-sm mr-1"
                                            style="background:#fff8e1;border:1px solid #e8d48b;color:#856404;font-size:.78rem;border-radius:20px;padding:3px 12px;">
                                            <i class="fas {{ $roleIcons[$r] ?? 'fa-user' }} mr-1"></i>
                                            {{ $roleNames[$r] ?? 'Role '.$r }}
                                        </button>
                                    </form>
                                    @endif
                                @endforeach
                                <span class="ml-auto" style="font-size:.75rem;color:#999;">
                                    Currently: <strong style="color:#a02626;">{{ $roleNames[$activeRole] ?? 'Examiner' }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <section class="multi_step_form">
                    <form id="msform" method="POST" action="{{ route('examiner.add') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <fieldset style="border: 2.5px solid #a02626; padding: 20px; border-radius: 10px; max-width: 600px; margin: auto;">
                            <legend style="font-size: 1.5rem; font-weight: bold; color: #a02626;">Examiner Portal</legend>

                            <!-- Welcome Section -->
                            <div class="text-center my-4">
                                <h3>Dear {{ auth()->user()->name }},</h3>
                                <p>Welcome to the COSECSA marking portal. Please choose the EXAM TYPE you want to evaluate:</p>
                            </div>

                            <!-- Offline readiness (filled in by dist/js/examiner-offline.js) -->
                            <div id="examiner-offline-status"></div>

                            <!-- Exam Type Selection Dropdown: the current exam (set under Exam Groups & Stations in the admin; MCS for now) is already selected -->
                            @php
                                $examOptions = [
                                    'mcs' => 'MCS', 'gs' => 'FCS General Surgery', 'cardiothoracic' => 'FCS Cardiothoracic', 'urology' => 'FCS Urology',
                                    'paediatric' => 'FCS Paediatric', 'orthopaedic' => 'FCS Orthopaedic', 'ent' => 'FCS ENT',
                                    'plastic_surgery' => 'FCS Plastic Surgery', 'neurosurgery' => 'FCS Neurosurgery', 'paediatric_orthopaedics' => 'FCS Paediatric Orthopaedics',
                                ];
                            @endphp
                            <div class="form-group">
                                <label for="exam_type" style="font-weight: bold;">Select Exam Type</label>
                                <select id="exam_type" name="exam_type" class="form-control" style="border-color: #FEC503;">
                                    <option value="">-- Select Exam Type --</option>
                                    @foreach($examOptions as $key => $label)
                                        <option value="{{ url($key === 'mcs' ? 'examiner/examiner_form' : ($key === 'gs' ? 'examiner/general_surgery' : 'examiner/'.$key)) }}" {{ ($currentExam['key'] ?? 'mcs') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if(!empty($mcsState) && !$mcsState['open'])
                                <div class="alert alert-warning" style="margin-top:12px;">
                                    <i class="fas fa-hourglass-half mr-1"></i>
                                    <strong>MCS marking has not opened yet.</strong> The exams officer starts it a few minutes before the exam
                                    @if(!empty($mcsState['scheduled_for'])) (the exam starts at <strong>{{ \Carbon\Carbon::parse($mcsState['scheduled_for'])->format('H:i') }}</strong>)@endif.
                                    If you choose MCS you will wait on a screen that opens the form by itself when it starts.
                                </div>
                            @endif

                            <!-- Submit Button -->
                            <div class="text-center mt-4">
                                <button type="button" class="btn"
                                        style="background-color: #FEC503; color: black; border-color: #FEC503;"
                                        onclick="proceedToNextPage()">
                                    Proceed <span class="fas fa-arrow-right"></span>
                                </button>
                            </div>
                        </fieldset>
                    </form>
                </section>
            </section>
        </div>
    </div>

    <script>
        function proceedToNextPage() {
            const examType = document.getElementById('exam_type').value;
            if (examType) {
                window.location.href = examType;
            } else {
                alert('Please select an exam type to proceed.');
            }
        }
    </script>
    @include('examiner._offline')
@endsection

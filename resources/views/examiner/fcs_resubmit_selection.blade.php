{{-- resources/views/examiner/fcs_resubmit_selection.blade.php --}}

@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')
                <section class="multi_step_form">
                    <fieldset class="exam-fieldset">
                        <legend>
                            Resubmit FCS {{ $exam_name }}
                        </legend>

                        <!-- Welcome Section -->
                        <div class="text-center my-4">
                            <h3>Select Examination Format for Resubmission</h3>
                            <p class="text-muted">Please choose whether to resubmit Clinical or Viva examination for {{ $exam_name }}</p>
                        </div>

                        <!-- Exam Format Selection -->
                        <div class="row justify-content-center mt-5">
                            <div class="col-md-5 mb-3">
                                <a href="{{ route('examiner.fcs.resubmit.form', ['candidate_id' => $candidate->id, 'exam_format' => 'clinical']) }}" class="btn btn-block exam-type-btn clinical-btn">
                                    <i class="fas fa-stethoscope fa-3x mb-3"></i>
                                    <h4>Clinical</h4>
                                    <p class="mb-0">Resubmit Clinical cases</p>
                                </a>
                            </div>

                            <div class="col-md-5 mb-3">
                                <a href="{{ route('examiner.fcs.resubmit.form', ['candidate_id' => $candidate->id, 'exam_format' => 'viva']) }}" class="btn btn-block exam-type-btn viva-btn">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <h4>Viva</h4>
                                    <p class="mb-0">Resubmit Viva examination</p>
                                </a>
                            </div>
                        </div>

                        <!-- Back Button -->
                        <div class="text-center mt-4">
                            <a href="{{ url('examiner/results') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Results List
                            </a>
                        </div>
                    </fieldset>
                </section>
            </section>
        </div>
    </div>

    <style>
        /* Center the fieldset vertically and horizontally */
        .multi_step_form {
            width: 100%;
            height: 100vh;
            display: block;
        }

        .exam-fieldset {
            border: 2.5px solid #a02626;
            padding: 30px;
            border-radius: 10px;
            max-width: 700px;
            margin: 0 auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .exam-fieldset legend {
            font-size: 1.5rem;
            font-weight: bold;
            color: #a02626;
        }

        .exam-type-btn {
            padding: 40px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-align: center;
            color: white;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 250px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .exam-type-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            color: white;
        }

        .clinical-btn {
            background-color: #a02626;
        }

        .clinical-btn:hover {
            background-color: #FEC503;
        }

        .viva-btn {
            background-color: #FEC503;
            color: #a02626;
        }

        .viva-btn:hover {
            background-color: #a02626;
            color: #FEC503;
        }

        .exam-type-btn i {
            opacity: 0.9;
        }

        .exam-type-btn h4 {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .exam-type-btn p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
@endsection

{{-- resources/views/examiner/exam_type_selection.blade.php --}}

@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')
                <section class="multi_step_form">
                    <fieldset class="exam-fieldset">
                        <legend>
                            FCS {{ $exam_name }}
                        </legend>

                        <!-- Welcome Section -->
                        <div class="text-center my-4">
                            <h3>Select Examination Format</h3>
                            <p class="text-muted">Please choose between Clinical or Viva examination for {{ $exam_name }}</p>
                        </div>

                        <!-- Exam Format Selection -->
                        <div class="row justify-content-center mt-5">
                            <div class="col-md-5 mb-3">
                                <a href="{{ route('examiner.' . $exam_type . '.clinical') }}" class="btn btn-block exam-type-btn clinical-btn">
                                    <i class="fas fa-stethoscope fa-3x mb-3"></i>
                                    <h4>Clinical</h4>
                                    <p class="mb-0">Evaluate clinical cases</p>
                                </a>
                            </div>

                            <div class="col-md-5 mb-3">
                                <a href="{{ route('examiner.' . $exam_type . '.viva') }}" class="btn btn-block exam-type-btn viva-btn">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <h4>Viva</h4>
                                    <p class="mb-0">Conduct oral examination</p>
                                </a>
                            </div>
                        </div>

                        <!-- Back Button -->
                        <div class="text-center mt-4">
                            <a href="{{ url('examiner/dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </fieldset>
                </section>
            </section>
        </div>
    </div>

    <style>
        /* Allow mobile devices to scroll, avoid forced full-page height */
        .multi_step_form {
            width: 100%;
            padding: 20px 10px;
        }

        /* Fieldset styling */
        .exam-fieldset {
            border: 2.5px solid #a02626;
            padding: 25px;
            border-radius: 10px;
            max-width: 700px;
            margin: 30px auto;
            background: #fff;
        }

        .exam-fieldset legend {
            font-size: 1.4rem;
            font-weight: bold;
            color: #a02626;
            text-align: center;
            width: 100%;
        }

        /* Exam type button styling */
        .exam-type-btn {
            padding: 30px 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-align: center;
            color: white !important;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 180px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 1rem;
        }

        .exam-type-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            color: white !important;
        }

        .clinical-btn {
            background-color: #a02626;
        }

        .clinical-btn:hover {
            background-color: #FEC503;
            color: #a02626 !important;
        }

        .viva-btn {
            background-color: #FEC503;
            color: #a02626 !important;
        }

        .viva-btn:hover {
            background-color: #a02626;
            color: #FEC503 !important;
        }

        .exam-type-btn h4 {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 1.2rem;
        }

        .exam-type-btn p {
            font-size: 0.85rem;
            margin: 0;
        }

        /* ICON RESPONSIVENESS */
        .exam-type-btn i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        /* =======================
           MOBILE RESPONSIVENESS
           ======================= */

        @media (max-width: 768px) {
            .exam-fieldset {
                padding: 20px 15px;
            }

            .exam-type-btn {
                min-height: 160px;
                padding: 20px 10px;
            }

            .exam-type-btn i {
                font-size: 2rem;
            }

            .exam-type-btn h4 {
                font-size: 1.1rem;
            }

            .exam-fieldset legend {
                font-size: 1.2rem;
            }

            .row .col-md-5 {
                max-width: 90%;
            }
        }

        @media (max-width: 480px) {
            .exam-fieldset {
                border-width: 2px;
                margin: 15px auto;
            }

            .exam-type-btn {
                min-height: 150px;
                padding: 18px 10px;
            }

            .exam-type-btn i {
                font-size: 1.8rem;
            }

            .exam-type-btn h4 {
                font-size: 1rem;
            }

            .exam-type-btn p {
                font-size: 0.75rem;
            }
        }
    </style>

@endsection

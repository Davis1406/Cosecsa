@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')
                <section class="multi_step_form">

                    <form id="msform" method="POST"
                          action="{{ route('examiner.update_evaluation.fcs', ['candidate_id' => $candidate->candidates_id]) }}"
                          enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="exam_type" value="{{ $exam_type }}">
                        <input type="hidden" name="form_type" value="{{ $form_type }}">
                        <input type="hidden" name="exam_format" value="{{ $form_type }}">

                        <div class="tittle">
                            <h2>Resubmit {{ $exam_name }} - {{ ucfirst($form_type) }} Evaluation</h2>
                        </div>

                        <!-- ================= GROUP + CANDIDATE + STATION (Read-only) ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-4 col-sm-12">
                                <label>Group</label>
                                <input type="text" class="form-control" value="Group {{ $candidate->g_name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="group_id" value="{{ $candidate->g_id }}">
                            </div>

                            <div class="form-group col-md-4 col-sm-12">
                                <label>Candidate</label>
                                <input type="text" class="form-control" value="{{ $candidate->candidate_id ?? 'N/A' }}" readonly>
                                <input type="hidden" name="candidate_id" value="{{ $candidate->candidates_id }}">
                            </div>

                            <div class="form-group col-md-4 col-sm-12">
                                <label>Station</label>
                                <input type="text" class="form-control" value="Station {{ $candidate->station_id }}" readonly>
                                <input type="hidden" name="station_id" value="{{ $candidate->station_id }}">
                            </div>
                        </div>

                        <!-- ================= CASE MARKING SECTION ================= -->
                        <div class="form-row" style="border: 1px solid #a02626; padding: 15px; border-radius: 5px; position: relative;">

                            @php
                                $clinicalLabels = [
                                    'Overall Professional Capacity and Patient Care:',
                                    'Knowledge and Judgement:',
                                    'Quality of Response:',
                                    'Bedside Manner:'
                                ];
                            @endphp

                            @for ($case = 1; $case <= $cases_count; $case++)
                                <div class="col-md-{{ $cases_count == 2 ? '6' : ($cases_count == 4 ? '3' : '12') }}"
                                     style="padding: {{ $cases_count > 2 ? '10px' : '0 20px' }};">

                                    <h5 class="case-heading" style="text-align: center; color: #a02626;" data-station="{{ $candidate->station_id }}">
                                        Case {{ $cases_count == 1 ? $candidate->station_id : $case }}
                                    </h5>

                                    <div id="case-container-{{ $case }}">
                                        @foreach($candidate->question_mark as $index => $mark)
                                            <div class="form-group question-block">
                                                @php
                                                    // Determine label for single-case clinical
                                                    if ($form_type == 'clinical' && $cases_count == 1) {
                                                        $label = $clinicalLabels[$index] ?? "Question " . ($index + 1) . ":";
                                                    } else {
                                                        $label = "Question " . ($index + 1) . ":";
                                                    }
                                                @endphp
                                                <label>{{ $label }}</label>
                                                <div class="input-group">
                                                    <select name="question_marks_case{{ $case }}[]"
                                                            class="form-control question-mark"
                                                            required onchange="updateTotalMarks()">
                                                        <option value="">Select Mark</option>
                                                        <option value="2" {{ $mark == 2 ? 'selected' : '' }}>2</option>
                                                        <option value="4" {{ $mark == 4 ? 'selected' : '' }}>4</option>
                                                        <option value="6" {{ $mark == 6 ? 'selected' : '' }}>6</option>
                                                        <option value="8" {{ $mark == 8 ? 'selected' : '' }}>8</option>
                                                        <option value="10" {{ $mark == 10 ? 'selected' : '' }}>10</option>
                                                    </select>
                                                    @if($index >= 3)
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-danger" onclick="removeQuestion(this, {{ $case }})">X</button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm mb-3 add-question-btn"
                                            onclick="addCaseQuestion({{ $case }})"
                                            style="color:black; background-color: #FEC503; border-color: #FEC503; {{ ($cases_count == 1 && $form_type == 'clinical' && count($candidate->question_mark) >= 4) ? 'display: none;' : '' }}">
                                        + Add Question
                                    </button>

                                </div>

                                @if($case < $cases_count && $cases_count == 2)
                                    <div class="separator-lg"></div>
                                @endif
                            @endfor
                        </div>

                        <!-- ================= TOTAL MARKS ================= -->
                        <div class="form-row justify-content-center">
                            <div class="form-group col-md-9 col-sm-12">
                                <label>Overall Marks</label>
                                <input type="number" name="total_marks" id="total_marks" class="form-control"
                                       value="{{ $candidate->total }}"
                                       placeholder="Total marks will be calculated automatically" readonly>
                            </div>
                        </div>

                        <!-- ================= EXAMINER REMARKS ================= -->
                        <div class="form-row justify-content-center">
                            <div class="form-group col-md-9 col-sm-12">
                                <label>Examiner Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks">{{ $candidate->remarks }}</textarea>
                            </div>
                        </div>

                        <div class="text-center">
                            <a href="{{ url('examiner/results') }}" class="btn btn-secondary mr-2">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="action-button">Update Evaluation</button>
                        </div>
                    </form>

                </section>
            </section>
        </div>
    </div>

    <!-- ================= CSS ================= -->
    <style>
        .form-row { margin: 0 10px 10px 10px !important; }
        h5 { font-weight: bold; margin-bottom: 15px; }
        .form-group label { font-weight: bold; }

        .separator-lg {
            display: none;
            border-left: 3px dotted #a02626;
            height: 75%;
            position: absolute;
            left: 50%;
            top: 12.5%;
        }

        @media (min-width: 768px) {
            .separator-lg {
                display: block;
            }
        }
    </style>

    <!-- ================= JS ================= -->
    <script>
        const casesCount = {{ $cases_count }};
        const formType = '{{ $form_type }}';
        const isSingleCaseClinical = (casesCount === 1 && formType === 'clinical');
        const clinicalLabels = [
            'Overall Professional Capacity and Patient Care:',
            'Knowledge and Judgement:',
            'Quality of Response:',
            'Bedside Manner:'
        ];

        document.addEventListener('DOMContentLoaded', function() {
            updateTotalMarks();
        });

        function addCaseQuestion(caseNumber) {
            const container = document.getElementById(`case-container-${caseNumber}`);
            const count = container.querySelectorAll(".question-block").length;

            if (isSingleCaseClinical && count >= 4) return;

            const questionNumber = count + 1;
            let labelText = `Question ${questionNumber}:`;
            if (isSingleCaseClinical && questionNumber <= 4) {
                labelText = clinicalLabels[questionNumber - 1];
            }

            let newField = document.createElement("div");
            newField.classList.add("form-group", "question-block", "mt-2");

            newField.innerHTML = `
            <label>${labelText}</label>
            <div class="input-group">
                <select name="question_marks_case${caseNumber}[]" class="form-control question-mark"
                        required onchange="updateTotalMarks()">
                    <option value="">Select Mark</option>
                    <option value="2">2</option>
                    <option value="4">4</option>
                    <option value="6">6</option>
                    <option value="8">8</option>
                    <option value="10">10</option>
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="removeQuestion(this, ${caseNumber})">X</button>
                </div>
            </div>
        `;

            container.appendChild(newField);

            // Hide add button if limit reached
            if (isSingleCaseClinical && questionNumber >= 4) {
                const addButton = container.nextElementSibling;
                if (addButton && addButton.classList.contains('add-question-btn')) {
                    addButton.style.display = 'none';
                }
            }
        }

        function removeQuestion(button, caseNumber) {
            const questionBlock = button.closest('.question-block');
            questionBlock.remove();
            updateTotalMarks();

            if (isSingleCaseClinical) {
                const container = document.getElementById(`case-container-${caseNumber}`);
                const count = container.querySelectorAll(".question-block").length;
                const addButton = container.nextElementSibling;
                if (count < 4 && addButton && addButton.classList.contains('add-question-btn')) {
                    addButton.style.display = 'inline-block';
                }
            }
        }

        function updateTotalMarks() {
            let total = 0;
            document.querySelectorAll('.question-mark').forEach(function(select) {
                let value = parseInt(select.value) || 0;
                if (select.value !== "") total += value;
            });
            document.getElementById('total_marks').value = total;
        }
    </script>
@endsection

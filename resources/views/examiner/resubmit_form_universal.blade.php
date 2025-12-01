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
                                        @if($form_type == 'viva' && $cases_count == 2)
                                            @if($case == 1)
                                                Knowledge & Judgement
                                            @else
                                                Quality of Response
                                            @endif
                                        @else
                                            Case {{ $cases_count == 1 ? $candidate->station_id : $case }}
                                        @endif
                                    </h5>

                                    <div id="case-container-{{ $case }}">
                                        @php
                                            // Calculate the starting index for this case's questions
                                            $questionsPerCase = $cases_count == 1 ? count($candidate->question_mark) : (count($candidate->question_mark) / $cases_count);
                                            $startIndex = ($case - 1) * $questionsPerCase;
                                        @endphp

                                        @for($i = 0; $i < $questionsPerCase; $i++)
                                            @php
                                                $questionIndex = $startIndex + $i;
                                                if ($questionIndex >= count($candidate->question_mark)) break;

                                                // Determine label
                                                if ($form_type == 'clinical') {
                                                    if ($cases_count == 1) {
                                                        // Single case: use clinical labels as-is
                                                        $label = $clinicalLabels[$i];
                                                    } else {
                                                        // Multiple cases: append case number to clinical labels
                                                        $label = str_replace(':', '', $clinicalLabels[$i]) . ':' . $case;
                                                    }
                                                } else {
                                                    $label = "Question " . ($i + 1) . ":";
                                                }
                                            @endphp

                                            <div class="form-group question-block">
                                                <label>{{ $label }}</label>
                                                <div class="input-group">
                                                    <select name="question_marks[]"
                                                            class="form-control question-mark"
                                                            required onchange="updateTotalMarks()">
                                                        <option value="">Select Mark</option>
                                                        @if($exam_type == 'urology' && $form_type == 'viva')
                                                            <option value="0" {{ $candidate->question_mark[$questionIndex] == 0 ? 'selected' : '' }}>0</option>
                                                        @endif
                                                        <option value="2" {{ $candidate->question_mark[$questionIndex] == 2 ? 'selected' : '' }}>2</option>
                                                        <option value="4" {{ $candidate->question_mark[$questionIndex] == 4 ? 'selected' : '' }}>4</option>
                                                        <option value="6" {{ $candidate->question_mark[$questionIndex] == 6 ? 'selected' : '' }}>6</option>
                                                        <option value="8" {{ $candidate->question_mark[$questionIndex] == 8 ? 'selected' : '' }}>8</option>
                                                        <option value="10" {{ $candidate->question_mark[$questionIndex] == 10 ? 'selected' : '' }}>10</option>
                                                    </select>
                                                    @if($i >= 3)
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-danger" onclick="removeQuestion(this, {{ $case }})">X</button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endfor
                                    </div>

                                    <!-- ALWAYS SHOW ADD QUESTION BUTTON (like original form) -->
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm mb-3 add-question-btn"
                                            onclick="addCaseQuestion({{ $case }})"
                                            style="color:black; background-color: #FEC503; border-color: #FEC503;">
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
        const examType = '{{ $exam_type }}';
        const isClinical = formType === 'clinical';
        const isUrologyViva = (examType === 'urology' && formType === 'viva');
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

            // For clinical exams, limit to 4 questions per case (like original form)
            if (isClinical && count >= 4) {
                alert('Maximum of 4 questions allowed for clinical exams.');
                return;
            }

            const questionNumber = count + 1;

            // Determine label based on clinical type and case count
            let labelText;
            if (isClinical) {
                if (casesCount === 1) {
                    // Single case: use clinical labels as-is
                    labelText = clinicalLabels[count];
                } else {
                    // Multiple cases: append case number (remove existing colon first)
                    const baseLabel = clinicalLabels[count].replace(':', '');
                    labelText = baseLabel + ':' + caseNumber;
                }
            } else {
                labelText = `Question ${questionNumber}:`;
            }

            // Build mark options - add 0 for urology viva
            let markOptions = '<option value="">Select Mark</option>';
            if (isUrologyViva) {
                markOptions += '<option value="0">0</option>';
            }
            markOptions += `
                <option value="2">2</option>
                <option value="4">4</option>
                <option value="6">6</option>
                <option value="8">8</option>
                <option value="10">10</option>
            `;

            let newField = document.createElement("div");
            newField.classList.add("form-group", "question-block", "mt-2");

            newField.innerHTML = `
            <label>${labelText}</label>
            <div class="input-group">
                <select name="question_marks[]" class="form-control question-mark"
                        required onchange="updateTotalMarks()">
                    ${markOptions}
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="removeQuestion(this, ${caseNumber})">X</button>
                </div>
            </div>
        `;

            container.appendChild(newField);

            // Hide add button if clinical exam and limit reached (like original form)
            if (isClinical && questionNumber >= 4) {
                const addButton = container.nextElementSibling;
                if (addButton && addButton.classList.contains('add-question-btn')) {
                    addButton.style.display = 'none';
                }
            }

            updateTotalMarks();
        }

        function removeQuestion(button, caseNumber) {
            const questionBlock = button.closest('.question-block');
            const container = document.getElementById(`case-container-${caseNumber}`);
            const questionBlocks = container.querySelectorAll(".question-block");

            questionBlock.remove();

            // For clinical exams, re-label remaining questions
            if (isClinical) {
                questionBlocks.forEach((block, index) => {
                    let newLabel;
                    if (casesCount === 1) {
                        // Single case: use clinical labels as-is
                        newLabel = clinicalLabels[index];
                    } else {
                        // Multiple cases: append case number
                        const baseLabel = clinicalLabels[index].replace(':', '');
                        newLabel = baseLabel + ':' + caseNumber;
                    }

                    const label = block.querySelector('label');
                    if (label) {
                        label.textContent = newLabel;
                    }
                });
            }

            updateTotalMarks();

            // Show add button again if we're below the limit (like original form)
            if (isClinical) {
                const currentCount = container.querySelectorAll(".question-block").length;
                const addButton = container.nextElementSibling;
                if (currentCount < 4 && addButton && addButton.classList.contains('add-question-btn')) {
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

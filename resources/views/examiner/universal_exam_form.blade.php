@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')
                <section class="multi_step_form">

                    <form id="msform" method="POST" action="{{ route('examiner.submit.exam') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="exam_type" value="{{ $exam_type }}">
                        <input type="hidden" name="form_type" value="{{ $form_type }}">

                        <div class="tittle">
                            <h2>FCS {{ $exam_name }} - {{ ucfirst($form_type) }} Evaluation Form</h2>
                        </div>

                        <!-- ================= GROUP + CANDIDATE + STATION ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Group</label>
                                <select name="group_id" id="group_id" class="form-control" required onchange="fetchExamCandidates(this.value)">
                                    <option value="">Select Group...</option>
                                    @foreach ($groups as $group)
                                        @if ($group->id <= 10)
                                            <option value="{{ $group->id }}">Group {{ $group->group_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Candidate</label>
                                <select name="candidate_id" id="candidate_id" class="form-control select2" required>
                                    <option value="">Choose a Candidate...</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Station</label>
                                <select name="station_id" id="station_id" class="form-control" required onchange="updateCaseNumbers()">
                                    <option value="">Choose a Station...</option>
                                    @for ($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}">Station {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- ================= CASE MARKING SECTION ================= -->
                        <div class="form-row" style="border: 1px solid #a02626; padding: 15px; border-radius: 5px; position: relative;">

                            @for ($case = 1; $case <= $cases_count; $case++)
                                <div class="col-md-{{ $cases_count == 2 ? '6' : ($cases_count == 4 ? '3' : '12') }}"
                                     style="padding: {{ $cases_count > 2 ? '10px' : '0 20px' }};">

                                    <h5 class="case-heading" style="text-align: center; color: #a02626;" data-case-index="{{ $case }}">
                                        @if($form_type == 'viva' && $cases_count == 2)
                                            @if($case == 1)
                                                Knowledge & Judgement
                                            @else
                                                Quality of Response
                                            @endif
                                        @else
                                            Case {{ $case }}
                                        @endif
                                    </h5>

                                    @php
                                        $defaultQuestions = 3;
                                        $allowEmpty = ($exam_type == 'urology' && $form_type == 'viva');
                                    @endphp

                                    <div id="case-container-{{ $case }}">
                                        @for ($q = 1; $q <= $defaultQuestions; $q++)
                                            <div class="form-group question-block">
                                                <label>
                                                    @if($form_type == 'clinical')
                                                        @if($q == 1)
                                                            Overall Professional Capacity and Patient Care:
                                                        @elseif($q == 2)
                                                            Knowledge and Judgement:
                                                        @elseif($q == 3)
                                                            Quality of Response:
                                                        @elseif($q == 4)
                                                            Bedside Manner:
                                                        @endif
                                                    @else
                                                        Question {{ $q }}:
                                                    @endif
                                                </label>
                                                <select name="question_marks_case{{ $case }}[]"
                                                        class="form-control question-mark"
                                                        @if(!$allowEmpty) required @endif
                                                        onchange="updateTotalMarks()">
                                                    <option value="">Select Mark</option>
                                                    @if($allowEmpty)
                                                        <option value="0">0</option>
                                                    @endif
                                                    <option value="2">2</option>
                                                    <option value="4">4</option>
                                                    <option value="6">6</option>
                                                    <option value="8">8</option>
                                                    <option value="10">10</option>
                                                </select>
                                            </div>
                                        @endfor
                                    </div>

                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm mb-3 add-question-btn"
                                            onclick="addCaseQuestion({{ $case }})"
                                            style="color:black; background-color: #FEC503; border-color: #FEC503;">
                                        + Add Question
                                    </button>

                                </div>
                            @endfor
                        </div>

                        <!-- ================= TOTAL MARKS ================= -->
                        <div class="form-row justify-content-center">
                            <div class="form-group col-md-9 col-sm-12">
                                <label>Overall Marks</label>
                                <input type="number" name="total_marks" id="total_marks" class="form-control"
                                       placeholder="Total marks will be calculated automatically" readonly>
                            </div>
                        </div>

                        <!-- ================= EXAMINER REMARKS ================= -->
                        <div class="form-row">
                            <div class="form-group col-md-12 col-sm-12">
                                <label>Examiner Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="action-button">Submit</button>
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
    </style>

    <!-- ================= JS ================= -->
    <script>
        const casesCount = {{ $cases_count }};
        const formType = '{{ $form_type }}';
        const examType = '{{ $exam_type }}';
        const isClinical = (formType === 'clinical');
        const allowEmpty = (examType === 'urology' && formType === 'viva');

        const clinicalQuestionLabels = [
            'Overall Professional Capacity and Patient Care:',
            'Knowledge and Judgement:',
            'Quality of Response:',
            'Bedside Manner:'
        ];

        function updateCaseNumbers() {
            if (casesCount !== 1 || !isClinical) return;

            const selectedStation = document.getElementById('station_id').value;

            if (selectedStation) {
                document.querySelectorAll('.case-heading').forEach(function(heading) {
                    heading.textContent = 'Case ' + selectedStation;
                });
            }
        }

        function addCaseQuestion(caseNumber) {
            const container = document.getElementById(`case-container-${caseNumber}`);
            const count = container.querySelectorAll(".question-block").length;

            if (isClinical && count >= 4) {
                alert('Maximum of 4 questions allowed for clinical exams.');
                return;
            }

            const questionNumber = count + 1;

            let labelText = `Question ${questionNumber}:`;
            if (isClinical && questionNumber <= 4) {
                labelText = clinicalQuestionLabels[questionNumber - 1];
            }

            let requiredAttr = allowEmpty ? "" : "required";

            let markOptions = '<option value="">Select Mark</option>';
            if (allowEmpty) markOptions += '<option value="0">0</option>';
            markOptions += `
                <option value="2">2</option>
                <option value="4">4</option>
                <option value="6">6</option>
                <option value="8">8</option>
                <option value="10">10</option>
            `;

            const newField = document.createElement("div");
            newField.classList.add("form-group", "question-block", "mt-2");
            newField.innerHTML = `
                <label>${labelText}</label>
                <div class="input-group">
                    <select name="question_marks_case${caseNumber}[]" class="form-control question-mark"
                            ${requiredAttr} onchange="updateTotalMarks()">
                        ${markOptions}
                    </select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger" onclick="removeQuestion(this, ${caseNumber})">X</button>
                    </div>
                </div>
            `;

            container.appendChild(newField);
        }

        function removeQuestion(button, caseNumber) {
            button.closest('.question-block').remove();
            updateTotalMarks();
        }

        function updateTotalMarks() {
            let total = 0;

            document.querySelectorAll('.question-mark').forEach(function(select) {
                let value = parseInt(select.value) || 0;
                if (select.value !== "") total += value;
            });

            document.getElementById('total_marks').value = total;
        }

        function fetchExamCandidates(groupId) {
            if (!groupId) return;

            const examType = '{{ $exam_type }}';

            fetch(`{{ url('/get-exam-candidates') }}/${examType}/${groupId}`)
                .then(response => response.json())
                .then(data => {
                    let candidateSelect = document.getElementById('candidate_id');
                    candidateSelect.innerHTML = '<option value="">Choose a Candidate...</option>';

                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    if (data.length === 0) {
                        candidateSelect.innerHTML += '<option value="">No candidates found</option>';
                        return;
                    }

                    data.forEach(candidate => {
                        candidateSelect.innerHTML += `<option value="${candidate.candidates_id}">${candidate.candidate_id || candidate.name}</option>`;
                    });

                    $('#candidate_id').select2({
                        placeholder: "Choose a Candidate...",
                        allowClear: true
                    });
                })
                .catch(() => alert('Error loading candidates. Please try again.'));
        }
    </script>
@endsection

@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')
                <section class="multi_step_form">

                    <form id="msform" data-offline-submit method="POST" action="{{ route('examiner.submit.exam') }}" enctype="multipart/form-data">
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
                                        @if ($group->id <= 10 || str_starts_with(strtoupper($group->group_name), 'TEST-'))   {{-- real groups A-J, plus TEST- sandbox groups --}}
                                            <option value="{{ $group->id }}">Group {{ $group->group_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Candidate</label>
                                <select name="candidate_id" id="candidate_id" class="form-control select2">
                                    <option value="">Choose a Candidate...</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Station</label>
                                <select name="station_id" id="station_id" class="form-control" required onchange="updateCaseNumbers()">
                                    <option value="">Choose a Station...</option>
                                    @foreach ($stations as $station)
                                        <option value="{{ $station->number }}">{{ $station->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- ================= CASE MARKING SECTION ================= -->
                        @php
                            // the cases, questions and marks of this form (Exams Settings > Form labels)
                            $nCases = count($formConfig['cases']);
                            $colWidth = $nCases == 1 ? 12 : ($nCases == 2 ? 6 : ($nCases == 4 ? 3 : 4));
                        @endphp
                        <div class="form-row" style="border: 1px solid #a02626; padding: 15px; border-radius: 5px; position: relative;">

                            @foreach ($formConfig['cases'] as $caseIndex => $caseCfg)
                                @php $case = $caseIndex + 1; @endphp
                                <div class="col-md-{{ $colWidth }}" style="padding: {{ $nCases > 2 ? '10px' : '0 20px' }};">

                                    <h5 class="case-heading" style="text-align: center; color: #a02626;" data-case-index="{{ $case }}" data-custom="{{ \App\Services\ExamFormConfig::hasCustomTitle($formConfig, $case) ? 1 : 0 }}">{{ \App\Services\ExamFormConfig::caseTitle($formConfig, $case) }}</h5>

                                    <div id="case-container-{{ $case }}">
                                        @for ($q = 1; $q <= count($caseCfg['questions']); $q++)
                                            <div class="form-group question-block">
                                                <label>{{ \App\Services\ExamFormConfig::questionLabel($formConfig, $case, $q) }}:</label>
                                                <select name="question_marks_case{{ $case }}[]"
                                                        class="form-control question-mark"
                                                        required onchange="updateTotalMarks()">
                                                    <option value="">Select Mark</option>
                                                    @foreach ($formConfig['marks'] as $m)
                                                        <option value="{{ $m }}">{{ $m }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endfor
                                    </div>

                                    @if ($formConfig['allow_extra'])
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm mb-3 add-question-btn"
                                                onclick="addCaseQuestion({{ $case }})"
                                                style="color:black; background-color: #FEC503; border-color: #FEC503;">
                                            + Add Question
                                        </button>
                                    @endif

                                </div>
                            @endforeach
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
        const formType = '{{ $form_type }}';
        const examType = '{{ $exam_type }}';
        const isClinical = (formType === 'clinical');
        const casesCount = {{ count($formConfig['cases']) }};

        // The layout, wording and marks of this form (Exams Settings > Form labels)
        const formCfg = @json($formConfig);
        function questionLabel(caseNo, n) {
            const list = ((formCfg.cases[caseNo - 1] || {}).questions) || [];
            if (n <= list.length) return ((list[n - 1] || '').trim() || 'Question ' + n) + ':';
            return ((formCfg.legacy_labels || [])[n - 1] || 'Question ' + n) + ':';   // a question the examiner added
        }
        function maxQuestions(caseNo) {
            const list = ((formCfg.cases[caseNo - 1] || {}).questions) || [];
            return isClinical ? Math.max(4, list.length) : 99;
        }

        // Update case numbers based on selected station (only for single-case clinical)
        function updateCaseNumbers() {
            if (casesCount !== 1 || !isClinical) return;

            const stationSelect = document.getElementById('station_id');
            const selectedStation = stationSelect.value;

            if (selectedStation) {
                document.querySelectorAll('.case-heading').forEach(function(heading) {
                    if (heading.dataset.custom === '1') return;   // a heading the exams office named stays as named
                    heading.textContent = 'Case ' + selectedStation;
                });
            }
        }

        function addCaseQuestion(caseNumber) {
            const container = document.getElementById(`case-container-${caseNumber}`);
            const count = container.querySelectorAll(".question-block").length;

            if (count >= maxQuestions(caseNumber)) {
                alert('Maximum of ' + maxQuestions(caseNumber) + ' questions allowed for this case.');
                return;
            }

            const questionNumber = count + 1;
            let newField = document.createElement("div");
            newField.classList.add("form-group", "question-block", "mt-2");

            let markOptions = '<option value="">Select Mark</option>';
            formCfg.marks.forEach(function (m) { markOptions += `<option value="${m}">${m}</option>`; });

            newField.innerHTML = `
                <label>${questionLabel(caseNumber, questionNumber)}</label>
                <div class="input-group">
                    <select name="question_marks_case${caseNumber}[]" class="form-control question-mark"
                            required onchange="updateTotalMarks()">
                        ${markOptions}
                    </select>

                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger" onclick="removeQuestion(this, ${caseNumber})">X</button>
                    </div>
                </div>
            `;

            container.appendChild(newField);
            toggleAddButton(caseNumber);
        }

        function removeQuestion(button, caseNumber) {
            const container = document.getElementById(`case-container-${caseNumber}`);
            if (container.querySelectorAll(".question-block").length <= 1) {
                alert('A case needs at least one question.');
                return;
            }
            button.closest('.question-block').remove();
            container.querySelectorAll(".question-block").forEach(function (block, index) {   // keep the numbering of the labels in step
                const label = block.querySelector('label');
                if (label) label.textContent = questionLabel(caseNumber, index + 1);
            });
            updateTotalMarks();
            toggleAddButton(caseNumber);
        }

        // The Add Question button goes away at the limit and comes back below it
        function toggleAddButton(caseNumber) {
            const container = document.getElementById(`case-container-${caseNumber}`);
            const addButton = container.nextElementSibling;
            if (addButton && addButton.classList.contains('add-question-btn')) {
                addButton.style.display = container.querySelectorAll(".question-block").length >= maxQuestions(caseNumber) ? 'none' : 'inline-block';
            }
        }

        function updateTotalMarks() {
            let total = 0;
            document.querySelectorAll('.question-mark').forEach(function(select) {
                let value = parseFloat(select.value) || 0;
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
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    // The candidate picker (tiles / list, highlights who is already examined) fills the select.
                    CandidatePicker.load(data);
                    if (data.length === 0) alert('No candidates found in this group.');
                })
                .catch(error => {
                    alert('Error loading candidates. Please try again.');
                });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Update total marks on page load
            updateTotalMarks();
        });
    </script>
    @include('examiner._marking_flow', ['kind' => $exam_type, 'format' => $form_type, 'assignedGroups' => []])
    @include('examiner._confirm_submit')
    @include('examiner._offline')
@endsection

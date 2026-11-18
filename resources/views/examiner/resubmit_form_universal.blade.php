@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')
                <section class="multi_step_form">

                    <form id="msform" data-offline-submit data-offline-kind="update" data-offline-redirect="{{ url('examiner/results') }}" method="POST"
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
                                <input type="text" class="form-control" value="{{ $candidate->station_name ?? 'Station ' . $candidate->station_id }}" readonly>
                                <input type="hidden" name="station_id" value="{{ $candidate->station_id }}">
                            </div>
                        </div>

                        <!-- ================= CASE MARKING SECTION ================= -->
                        @php
                            // $caseLayout: how many questions each case had when these marks were saved
                            $nCases = count($caseLayout);
                            $colWidth = $nCases == 1 ? 12 : ($nCases == 2 ? 6 : ($nCases == 4 ? 3 : 4));
                            $savedMarks = $candidate->question_mark;
                            $offset = 0;
                        @endphp
                        <div class="form-row" style="border: 1px solid #a02626; padding: 15px; border-radius: 5px; position: relative;">

                            @foreach ($caseLayout as $caseIndex => $questionCount)
                                @php $case = $caseIndex + 1; $configured = count($formConfig['cases'][$caseIndex]['questions'] ?? []); @endphp
                                <div class="col-md-{{ $colWidth }}" style="padding: {{ $nCases > 2 ? '10px' : '0 20px' }};">

                                    @php
                                        $customTitle = \App\Services\ExamFormConfig::hasCustomTitle($formConfig, $case);
                                        // a one-case Clinical form with no heading set has always been called after the station
                                        $heading = ($customTitle || $nCases > 1) ? \App\Services\ExamFormConfig::caseTitle($formConfig, $case) : 'Case ' . $candidate->station_id;
                                    @endphp
                                    <h5 class="case-heading" style="text-align: center; color: #a02626;" data-station="{{ $candidate->station_id }}" data-custom="{{ $customTitle ? 1 : 0 }}">{{ $heading }}</h5>

                                    <div id="case-container-{{ $case }}">
                                        @for ($i = 0; $i < $questionCount; $i++)
                                            @php
                                                $saved = $savedMarks[$offset + $i] ?? null;
                                                // the marks to pick from; a saved mark outside the current scale stays selectable
                                                $marks = $formConfig['marks'];
                                                if (is_numeric($saved) && $saved > 0 && ! in_array((float) $saved, array_map('floatval', $marks), true)) { $marks[] = $saved + 0; sort($marks); }
                                            @endphp
                                            <div class="form-group question-block">
                                                <label>{{ \App\Services\ExamFormConfig::questionLabel($formConfig, $case, $i + 1) }}:</label>
                                                <div class="input-group">
                                                    <select name="question_marks[]"
                                                            class="form-control question-mark"
                                                            required onchange="updateTotalMarks()">
                                                        <option value="">Select Mark</option>
                                                        @foreach ($marks as $m)
                                                            <option value="{{ $m }}" {{ is_numeric($saved) && (float) $saved === (float) $m ? 'selected' : '' }}>{{ $m }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if($i >= $configured)
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-danger" onclick="removeQuestion(this, {{ $case }})">X</button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endfor
                                        @php $offset += $questionCount; @endphp
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

                                @if($caseIndex < $nCases - 1 && $nCases == 2)
                                    <div class="separator-lg"></div>
                                @endif
                            @endforeach
                        </div>
                        {{-- how many questions each case has now (kept up to date by the page) --}}
                        <input type="hidden" name="case_layout" id="case_layout" value="{{ json_encode($caseLayout) }}">

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
        const formType = '{{ $form_type }}';
        const examType = '{{ $exam_type }}';
        const isClinical = formType === 'clinical';
        const casesCount = {{ count($caseLayout) }};

        // The layout, wording and marks of this form (Exams Settings > Form labels)
        const formCfg = @json($formConfig);
        function questionLabel(caseNo, n) {
            const list = ((formCfg.cases[caseNo - 1] || {}).questions) || [];
            if (n <= list.length) return ((list[n - 1] || '').trim() || 'Question ' + n) + ':';
            return ((formCfg.legacy_labels || [])[n - 1] || 'Question ' + n) + ':';   // a question that was added while marking
        }
        function maxQuestions(caseNo) {
            const list = ((formCfg.cases[caseNo - 1] || {}).questions) || [];
            return isClinical ? Math.max(4, list.length) : 99;
        }
        function markOptionsHtml() {
            let html = '<option value="">Select Mark</option>';
            formCfg.marks.forEach(function (m) { html += `<option value="${m}">${m}</option>`; });
            return html;
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateTotalMarks();
        });

        function addCaseQuestion(caseNumber) {
            const container = document.getElementById(`case-container-${caseNumber}`);
            const count = container.querySelectorAll(".question-block").length;

            if (count >= maxQuestions(caseNumber)) {
                alert('Maximum of ' + maxQuestions(caseNumber) + ' questions allowed for this case.');
                return;
            }

            let newField = document.createElement("div");
            newField.classList.add("form-group", "question-block", "mt-2");
            newField.innerHTML = `
            <label>${questionLabel(caseNumber, count + 1)}</label>
            <div class="input-group">
                <select name="question_marks[]" class="form-control question-mark"
                        required onchange="updateTotalMarks()">
                    ${markOptionsHtml()}
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="removeQuestion(this, ${caseNumber})">X</button>
                </div>
            </div>
        `;

            container.appendChild(newField);
            toggleAddButton(caseNumber);
            updateTotalMarks();
        }

        function removeQuestion(button, caseNumber) {
            const container = document.getElementById(`case-container-${caseNumber}`);
            if (container.querySelectorAll(".question-block").length <= 1) {
                alert('A case needs at least one question.');
                return;
            }
            button.closest('.question-block').remove();
            container.querySelectorAll(".question-block").forEach((block, index) => {   // keep the numbering of the labels in step
                const label = block.querySelector('label');
                if (label) label.textContent = questionLabel(caseNumber, index + 1);
            });
            updateTotalMarks();
            toggleAddButton(caseNumber);
        }

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
            // tell the server how many questions each case has now
            const layout = [];
            for (let c = 1; c <= casesCount; c++) {
                const box = document.getElementById('case-container-' + c);
                layout.push(box ? box.querySelectorAll('.question-block').length : 0);
            }
            document.getElementById('case_layout').value = JSON.stringify(layout);
        }
    </script>
    @include('examiner._offline')
@endsection

@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')
                @if (!empty($practiceOnly))
                    <div class="alert alert-warning" style="max-width:900px;margin:10px auto;">
                        <strong>Practice mode.</strong> Only the TEST groups are shown and nothing you save counts.
                        Real marking opens when the exams officer starts the exam.
                        <a href="{{ url('examiner/examiner_form') }}" class="alert-link">Check whether it has started</a>
                    </div>
                @endif
                <section class="multi_step_form">
                    <form id="msform" data-offline-submit method="POST" action="{{ route('examiner.add') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="tittle">
                            <h2>Candidate Evaluation Form</h2>
                        </div>

                        <!-- Group Selection Section -->
                        <div class="form-row">
                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Group</label>
                                <select name="group_id" id="group_id" class="form-control" required
                                    onchange="fetchCandidates(this.value)">
                                    <option value="">Select Group...</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->id }}">Group {{ $group->group_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Candidate Selection based on Group -->
                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Candidate</label>
                                <select name="candidate_id" id="candidate_id" class="form-control">
                                    <option value="">Choose a Candidate...</option>
                                    <!-- Options populated dynamically by fetchCandidates -->
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Station</label>
                                <select name="station_id" class="form-control" required>
                                    <option value="">Choose a Station...</option>
                                    @foreach ($stations as $station)
                                        <option value="{{ $station->number }}" data-max="{{ $station->max_total }}">{{ $station->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Dynamic Question Marks Fields -->
                        <div class="form-row justify-content-center" id="question-fields">
                            <div class="form-group col-md-9 col-sm-12">
                                <label>Enter Marks for Questions,</label>
                                <label for="question_marks_0">Question 1:</label>
                                <input type="number" name="question_marks[]" id="question_marks_0"
                                    class="form-control mb-2 question-mark" placeholder="Enter mark for question" required
                                    oninput="updateTotalMarks()" step="0.5" min="0">
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mb-3" onclick="addQuestionField()"
                            style="color:black; background-color: #FEC503; border-color: #FEC503;">+ Add Question</button>

                        <!-- Overall Marks and Grade Section -->
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label>Overall Marks</label>
                                <input type="number" name="total_marks" id="total_marks" class="form-control"
                                    placeholder="Total marks will be calculated automatically" readonly>
                            </div>

                            <div class="form-group col-md-6 col-sm-12">
                                <label>Grade</label>
                                <select name="overall" class="form-control" required>
                                    <option value="">Select Grade...</option>
                                    <option value="pass">Pass</option>
                                    <option value="borderline">Borderline</option>
                                    <option value="fail">Fail</option>
                                </select>
                            </div>
                        </div>

                        <!-- Examiner Remarks Section -->
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

    <style>
        .form-row {
            margin: 0 10px 0 10px !important;
        }
    </style>

    <script>
        function addQuestionField() {
            const questionFields = document.getElementById('question-fields');
            const currentCount = questionFields.querySelectorAll('input[name="question_marks[]"]').length;
            const newField = document.createElement('div');
            newField.classList.add('form-group', 'col-md-9', 'col-sm-12', 'mb-2');

            newField.innerHTML = `
        <label for="question_marks_${currentCount}">Question ${currentCount + 1}:</label>
        <div class="input-group">
            <input type="number" name="question_marks[]" id="question_marks_${currentCount}" class="form-control question-mark" placeholder="Enter mark for question" required oninput="updateTotalMarks()" step="0.1" min="0">
            ${currentCount > 0 ? `
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="removeQuestionField(this)">X</button>
                </div>` : ''}
        </div>
    `;

            questionFields.appendChild(newField);
        }


        function removeQuestionField(button) {
            const fieldGroup = button.closest('.form-group');
            fieldGroup.remove();
            updateTotalMarks();
        }

        // The most marks a candidate can get at the chosen station (set under Exam Groups & Stations; 20 by default).
        function stationMax() {
            const sel = document.querySelector('select[name="station_id"]');
            const opt = sel && sel.options[sel.selectedIndex];
            if (!opt || !opt.value) return 20;
            const raw = opt.getAttribute('data-max');
            return raw === null ? 20 : (raw === '' ? null : parseFloat(raw));
        }

        function updateTotalMarks() {
            let total = 0;
            document.querySelectorAll('.question-mark').forEach(function(input) {

                if (!/^\d+(\.\d{0,1})?$/.test(input.value)) {
                    input.value = parseFloat(input.value).toFixed(1);
                }

                total += parseFloat(input.value) || 0;
            });
            document.getElementById('total_marks').value = total.toFixed(1);

            const submitButton = document.querySelector('.action-button');
            const max = stationMax();
            if (max !== null && total > max) {
                alert('The total marks should not exceed ' + max + ' per station.');
                document.getElementById('total_marks').value = 0;
                submitButton.disabled = true;
            } else {
                submitButton.disabled = false;
            }
        }

        // Add event listener to prevent form submission if total marks exceed 20
        document.getElementById('msform').addEventListener('submit', function(event) {
            const total = parseFloat(document.getElementById('total_marks').value) || 0;
            const max = stationMax();
            if (max !== null && total > max) {
                event.preventDefault();
                alert('Cannot submit form. The total marks should be less than or equal to ' + max + ' per station.');
            }
        });


        function fetchCandidates(groupId) {
            if (!groupId) return;

            console.log('Fetching candidates for group:', groupId);

            fetch(`{{ url('/get-mcs-candidates') }}/${groupId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response is not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received candidates:', data);

                    // The candidate picker (tiles / list, highlights who is already examined) fills the select.
                    CandidatePicker.load(data);
                    if (data.length === 0) alert('No candidates found in this group.');
                })
                .catch(error => {
                    console.error('Error fetching candidates:', error);
                    alert('Error loading candidates. Please try again.');
                });
        }

    </script>
    @include('examiner._marking_flow', ['kind' => 'mcs', 'format' => null, 'assignedGroups' => $examinerGroupIds ?? [], 'stationForGroup' => $stationForGroup ?? []])
    @include('examiner._offline')
@endsection

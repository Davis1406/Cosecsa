@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')
                <section class="multi_step_form">
                    <form id="msform" method="POST"
                        action="{{ route('candidateform.update', ['candidate_id' => $candidate->candidates_id, 'station_id' => $candidate->station_id]) }}"
                        enctype="multipart/form-data" onsubmit="return validateTotalMarks()">
                        {{ csrf_field() }}

                        <div class="tittle">
                            <h2>Candidate Evaluation Form</h2>
                        </div>

                        <!-- Group Display Section (Readonly) -->
                        <div class="form-row">
                            <div class="form-group col-md-4 col-sm-12">
                                <label>Group</label>
                                <input type="text" name="group_id_display" class="form-control"
                                    value="Group {{ $candidate->g_name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="group_id" value="{{ $candidate->g_id }}">
                            </div>

                            <!-- Candidate Display Section (Readonly) -->
                            <div class="form-group col-md-4 col-sm-12">
                                <label>Candidate</label>
                                <input type="text" name="candidate_id_display" class="form-control"
                                    value="{{ $candidate->candidate_name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="candidate_id" value="{{ $candidate->candidates_id }}">
                            </div>

                            <!-- Station Display Section (Readonly) -->
                            <div class="form-group col-md-4 col-sm-12">
                                <label>Station</label>
                                <input type="text" name="station_id_display" class="form-control"
                                    value="Station {{ $candidate->station_id }}" readonly>
                                <input type="hidden" name="station_id" value="{{ $candidate->station_id }}">
                            </div>
                        </div>

                        <!-- Dynamic Question Marks Fields -->
                        <div class="form-row"
                            style="border: 1px solid #a02626; padding: 15px; border-radius: 5px; position: relative;">
                            <!-- Left Column (Case 1) -->
                            <div class="col-md-6" style="padding-right: 20px;">
                                <h5 style="text-align: center; color: #a02626;">Case 1</h5>
                                @foreach ($candidate->question_mark as $index => $mark)
                                    @if ($index < 3)
                                        <!-- Displaying only first 4 questions for Case 1 -->
                                        <div class="form-group">
                                            <label for="question_marks_case1_{{ $index }}">Question
                                                {{ $index + 1 }}:</label>
                                            <div class="input-group">
                                                <!-- Dropdown for selecting marks -->
                                                <select name="question_marks[]"
                                                    id="question_marks_case1_{{ $index }}"
                                                    class="form-control question-mark" required
                                                    oninput="updateTotalMarks()">
                                                    <option value="2" {{ $mark == 2 ? 'selected' : '' }}>2</option>
                                                    <option value="4" {{ $mark == 4 ? 'selected' : '' }}>4</option>
                                                    <option value="6" {{ $mark == 6 ? 'selected' : '' }}>6</option>
                                                    <option value="8" {{ $mark == 8 ? 'selected' : '' }}>8</option>
                                                    <option value="10" {{ $mark == 10 ? 'selected' : '' }}>10</option>
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <!-- Separator -->
                            <div class="separator-lg"></div>

                            <!-- Right Column (Case 2) -->
                            <div class="col-md-6" style="padding-left: 20px;">
                                <h5 style="text-align: center; color: #a02626;">Case 2</h5>
                                @foreach ($candidate->question_mark as $index => $mark)
                                    @if ($index >= 3)
                                        <!-- Displaying next 4 questions for Case 2 -->
                                        <div class="form-group">
                                            <label for="question_marks_case2_{{ $index }}">Question
                                                {{ $index + 1 }}:</label>
                                            <div class="input-group">
                                                <!-- Dropdown for selecting marks -->
                                                <select name="question_marks[]"
                                                    id="question_marks_case2_{{ $index }}"
                                                    class="form-control question-mark" required
                                                    oninput="updateTotalMarks()">
                                                    <option value="2" {{ $mark == 2 ? 'selected' : '' }}>2</option>
                                                    <option value="4" {{ $mark == 4 ? 'selected' : '' }}>4</option>
                                                    <option value="6" {{ $mark == 6 ? 'selected' : '' }}>6</option>
                                                    <option value="8" {{ $mark == 8 ? 'selected' : '' }}>8</option>
                                                    <option value="10" {{ $mark == 10 ? 'selected' : '' }}>10</option>
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Overall Marks and Grade Section -->
                        <div class="form-row justify-content-center">
                            <div class="form-group col-md-9 col-sm-12">
                                <label>Overall Marks</label>
                                <input type="number" name="total_marks" id="total_marks" class="form-control"
                                    value="{{ $candidate->total }}"
                                    placeholder="Total marks will be calculated automatically" readonly>
                            </div>
                        </div>

                        <!-- Examiner Remarks Section -->
                        <div class="form-row justify-content-center">
                            <div class="form-group col-md-9 col-sm-12">
                                <label>Examiner Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks">{{ $candidate->remarks }}</textarea>
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

        .separator-lg {
            display: none;
            border-left: 3px dotted #a02626;
            height: 75%;
            position: absolute;
            left: 50%;
            top: 12.5%;
        }

        /* Show separator for screens larger than 992px */
        @media (min-width: 768px) {
            .separator-lg {
                display: block;
            }
        }

        .form-group label {
            font-weight: bold;
        }

        .select2-results__option:hover {
            background-color: #a02626 !important;
            color: #ffffff !important;
        }
    </style>

    <script>
function updateTotalMarks() {
    let total = 0;
    document.querySelectorAll('.question-mark').forEach(function(input) {
        if (!/^\d+$/.test(input.value)) {
            input.value = Math.round(parseFloat(input.value)) || 0; // Convert to integer if not already
        }
        total += parseInt(input.value) || 0; // Ensure addition is integer-based
    });
    
    document.getElementById('total_marks').value = total; // Display as an integer

    const submitButton = document.querySelector('.action-button');
    if (total > 80) {
        alert('The total marks should not exceed 80 per station.');
        document.getElementById('total_marks').value = 0;
        submitButton.disabled = true;
    } else {
        submitButton.disabled = false;
    }
}


        document.getElementById('msform').addEventListener('submit', function(event) {
            const total = parseFloat(document.getElementById('total_marks').value) || 0;
            if (total > 80) {
                event.preventDefault(); // Prevent form submission
                alert('Cannot submit form. The total marks should be less than or equal to 80 per station.');
            }
        });
    </script>
@endsection

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
                            <h2>Resubmit GS Evaluation Form</h2>
                        </div>

                        <!-- Group Display Section (Readonly) -->
                        <div class="form-row">
                            <div class="form-group col-md-4 col-sm-12">
                                <label>Group</label>
                                <input type="text" name="group_id_display" class="form-control"
                                       value="Group {{ $candidate->g_id ?? 'N/A' }}" readonly>
                                <input type="hidden" name="group_id" value="{{ $candidate->group_table_id ?? $candidate->g_id }}">
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

                        <!-- GS Form: Two-case structure (3 questions each) -->
                        <div class="form-row"
                             style="border: 1px solid #a02626; padding: 15px; border-radius: 5px; position: relative;">

                            <!-- Left Column (Case 1 - Questions 1-3) -->
                            <div class="col-md-6" style="padding-right: 20px;">
                                <h5 style="text-align: center; color: #a02626;">Case 1</h5>

                                @php
                                    // GS always has 6 questions, first 3 go to Case 1
                                    $case1Questions = array_slice($candidate->question_mark, 0, 3);
                                @endphp

                                @foreach ($case1Questions as $index => $mark)
                                    <div class="form-group question-block">
                                        <label for="question_marks_case1_{{ $index }}">Question {{ $index + 1 }}:</label>
                                        <div class="input-group">
                                            <select name="question_marks[]"
                                                    id="question_marks_case1_{{ $index }}"
                                                    class="form-control question-mark" required
                                                    onchange="updateTotalMarks()">
                                                <option value="">Select Mark</option>
                                                <option value="2" {{ $mark == 2 ? 'selected' : '' }}>2</option>
                                                <option value="4" {{ $mark == 4 ? 'selected' : '' }}>4</option>
                                                <option value="6" {{ $mark == 6 ? 'selected' : '' }}>6</option>
                                                <option value="8" {{ $mark == 8 ? 'selected' : '' }}>8</option>
                                                <option value="10" {{ $mark == 10 ? 'selected' : '' }}>10</option>
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Separator -->
                            <div class="separator-lg"></div>

                            <!-- Right Column (Case 2 - Questions 4-6) -->
                            <div class="col-md-6" style="padding-left: 20px;">
                                <h5 style="text-align: center; color: #a02626;">Case 2</h5>

                                @php
                                    // Last 3 questions go to Case 2
                                    $case2Questions = array_slice($candidate->question_mark, 3, 3);
                                @endphp

                                @foreach ($case2Questions as $index => $mark)
                                    <div class="form-group question-block">
                                        <label for="question_marks_case2_{{ $index }}">Question {{ $index + 1 }}:</label>
                                        <div class="input-group">
                                            <select name="question_marks[]"
                                                    id="question_marks_case2_{{ $index }}"
                                                    class="form-control question-mark" required
                                                    onchange="updateTotalMarks()">
                                                <option value="">Select Mark</option>
                                                <option value="2" {{ $mark == 2 ? 'selected' : '' }}>2</option>
                                                <option value="4" {{ $mark == 4 ? 'selected' : '' }}>4</option>
                                                <option value="6" {{ $mark == 6 ? 'selected' : '' }}>6</option>
                                                <option value="8" {{ $mark == 8 ? 'selected' : '' }}>8</option>
                                                <option value="10" {{ $mark == 10 ? 'selected' : '' }}>10</option>
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Overall Marks Section -->
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

        @media (min-width: 768px) {
            .separator-lg {
                display: block;
            }
        }

        .form-group label {
            font-weight: bold;
        }

        h5 {
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>

    <script>
        function updateTotalMarks() {
            let total = 0;
            document.querySelectorAll('.question-mark').forEach(function(select) {
                const value = parseInt(select.value) || 0;
                if (select.value !== "") total += value;
            });

            document.getElementById('total_marks').value = total;

            const submitButton = document.querySelector('.action-button');
            if (total > 60) {
                alert('The total marks should not exceed 60 for GS (6 questions × 10 marks max).');
                submitButton.disabled = true;
            } else {
                submitButton.disabled = false;
            }
        }

        function validateTotalMarks() {
            const total = parseFloat(document.getElementById('total_marks').value) || 0;
            if (total > 60) {
                alert('Cannot submit form. The total marks should be less than or equal to 60.');
                return false;
            }
            return true;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateTotalMarks();
        });
    </script>
@endsection

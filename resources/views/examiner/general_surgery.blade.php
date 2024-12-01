@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')
                <section class="multi_step_form">
                    <form id="msform" method="POST" action="{{ route('gs.add') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="tittle">
                            <h2>Candidate Evaluation Form GS</h2>
                        </div>

                        <!-- Group Selection Section -->
                        <div class="form-row">
                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Group</label>
                                <select name="group_id" id="group_id" class="form-control" required onchange="fetchCandidates(this.value)">
                                    <option value="">Select Group...</option>
                                    @foreach ($groups as $group)
                                        @if ($group->id <= 11) <!-- Limit groups to those with id <= 11 -->
                                            <option value="{{ $group->id }}">Group {{ $group->group_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            

                            <!-- Candidate Selection based on Group -->
                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Candidate</label>
                                <select name="candidate_id" id="candidate_id" class="form-control select2" required>
                                    <option value="">Choose a Candidate...</option>
                                    <!-- Options populated dynamically by fetchCandidates -->
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-12">
                                <label>Select Station</label>
                                <select name="station_id" class="form-control" required>
                                    <option value="">Choose a Station...</option>
                                    @for ($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}">Station {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="form-row" style="border: 1px solid #a02626; padding: 15px; border-radius: 5px; position: relative;">
                            <!-- Left Column (Case 1) -->
                            <div class="col-md-6" style="padding-right: 20px;">
                                <h5 style="text-align: center; color: #a02626;">Case 1</h5>
                                @for ($i = 1; $i <= 3; $i++)
                                    <div class="form-group">
                                        <label for="question_marks_case1_{{ $i }}">Question {{ $i }}:</label>
                                        <select name="question_marks[]" id="question_marks_case1_{{ $i }}"
                                                class="form-control question-mark" required onchange="updateTotalMarks()">
                                            <option value="">Select Mark</option>
                                            <option value="2">2</option>
                                            <option value="4">4</option>
                                            <option value="6">6</option>
                                            <option value="8">8</option>
                                            <option value="10">10</option>
                                        </select>
                                    </div>
                                @endfor
                            </div>
                        
                            <!-- Separator -->
                            <div class="separator-lg"></div>
                        
                            <!-- Right Column (Case 2) -->
                            <div class="col-md-6" style="padding-left: 20px;">
                                <h5 style="text-align: center; color: #a02626;">Case 2</h5>
                                @for ($i = 1; $i <= 3; $i++)
                                    <div class="form-group">
                                        <label for="question_marks_case2_{{ $i }}">Question {{ $i }}:</label>
                                        <select name="question_marks[]" id="question_marks_case2_{{ $i }}"
                                                class="form-control question-mark" required onchange="updateTotalMarks()">
                                            <option value="">Select Mark</option>
                                            <option value="2">2</option>
                                            <option value="4">4</option>
                                            <option value="6">6</option>
                                            <option value="8">8</option>
                                            <option value="10">10</option>
                                        </select>
                                    </div>
                                @endfor
                            </div>
                        </div>
                        
                        <!-- Overall Marks and Grade Section -->
                        <div class="form-row justify-content-center">
                            <div class="form-group col-md-9 col-sm-12">
                                <label>Overall Marks</label>
                                <input type="number" name="total_marks" id="total_marks" class="form-control"
                                    placeholder="Total marks will be calculated automatically" readonly>
                            </div>

                            @if (isset($record->overall))
                                <td>{{ $record->overall }}</td>
                            @endif

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
            margin: 0 10px 10px 10px !important;
        }

        h5 {
            font-weight: bold;
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
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

        /* Change the hover color of dropdown items */
        .select2-results__option:hover {
            background-color: #a02626 !important;
            /* Set the desired hover background color */
            color: #ffffff !important;
            /* Optional: Change the text color to white for better visibility */
        }

        /* Change the selected item background color */
        .select2-results__option[aria-selected="true"] {
            background-color: #841818 !important;
            /* Darker color when item is selected */
            color: #ffffff !important;
            /* Ensure text remains visible */
        }

        /* Change the placeholder text color to black */
        .select2-selection__placeholder {
            color: black !important;
        }
    </style>

    <script>

function updateTotalMarks() {
    let total = 0;

    // Loop through all dropdowns with the class 'question-mark'
    document.querySelectorAll('.question-mark').forEach(function(select) {
        // Parse the value or default to 0 if the field is not selected
        let value = parseInt(select.value) || 0; 

        // Add the parsed value to the total if it exists
        if (select.value !== "") {
            total += value;
        }
    });

    // Update the total marks input field
    document.getElementById('total_marks').value = total;
}

        function fetchCandidates() {
            fetch('/cosecsa/get-candidates')
                .then(response => response.json())
                .then(data => {
                    let candidateSelect = document.getElementById('candidate_id');
                    candidateSelect.innerHTML = '<option value="">Choose a Candidate...</option>';

                    // Populate the dropdown options dynamically
                    data.forEach(candidate => {
                        candidateSelect.innerHTML +=
                            `<option value="${candidate.cand_id}">${candidate.c_id}</option>`;
                    });

                    // Reinitialize Select2 after populating the options
                    $('#candidate_id').select2({
                        placeholder: "Select a candidate",
                        allowClear: true
                    });
                })
                .catch(error => console.error('Error fetching candidates:', error));
        }
    </script>
@endsection

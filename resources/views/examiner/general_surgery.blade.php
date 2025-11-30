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
                                        @if ($group->id <= 11)
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

                                @php
                                    $case1Labels = [
                                        'Overall Professional Capacity and Patient Care:',
                                        'Knowledge and Judgement:',
                                        'Quality of Response:'
                                    ];
                                @endphp

                                @for ($i = 0; $i < 3; $i++)
                                    <div class="form-group">
                                        <label for="question_marks_case1_{{ $i }}">{{ $case1Labels[$i] }}</label>
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

                                @php
                                    $case2Labels = [
                                        'Overall Professional Capacity and Patient Care:',
                                        'Knowledge and Judgement:',
                                        'Quality of Response:'
                                    ];
                                @endphp

                                @for ($i = 0; $i < 3; $i++)
                                    <div class="form-group">
                                        <label for="question_marks_case2_{{ $i }}">{{ $case2Labels[$i] }}</label>
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

                        <!-- Overall Marks Section -->
                        <div class="form-row justify-content-center">
                            <div class="form-group col-md-9 col-sm-12">
                                <label>Overall Marks</label>
                                <input type="number" name="total_marks" id="total_marks" class="form-control"
                                       placeholder="Total marks will be calculated automatically" readonly>
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

        @media (min-width: 768px) {
            .separator-lg {
                display: block;
            }
        }

        .select2-results__option:hover {
            background-color: #a02626 !important;
            color: #ffffff !important;
        }

        .select2-results__option[aria-selected="true"] {
            background-color: #841818 !important;
            color: #ffffff !important;
        }

        .select2-selection__placeholder {
            color: black !important;
        }
    </style>

    <script>
        function updateTotalMarks() {
            let total = 0;
            document.querySelectorAll('.question-mark').forEach(function(select) {
                let value = parseInt(select.value) || 0;
                if (select.value !== "") {
                    total += value;
                }
            });
            document.getElementById('total_marks').value = total;
        }

        function fetchCandidates(groupId) {
            if (!groupId) return;

            fetch(`{{ url('/get-gs-candidates') }}/${groupId}`)
                .then(response => response.json())
                .then(data => {
                    let candidateSelect = document.getElementById('candidate_id');
                    candidateSelect.innerHTML = '<option value="">Choose a Candidate...</option>';
                    if (data.length === 0) {
                        candidateSelect.innerHTML += '<option value="">No candidates found</option>';
                        return;
                    }

                    data.forEach(candidate => {
                        candidateSelect.innerHTML +=
                            `<option value="${candidate.candidates_id}">${candidate.candidate_id || candidate.name}</option>`;
                    });

                    $('#candidate_id').select2({
                        placeholder: "Choose a Candidate...",
                        allowClear: true
                    });
                })
                .catch(error => {
                    console.error('Error fetching candidates:', error);
                    alert('Error loading candidates. Please try again.');
                });
        }
    </script>
@endsection

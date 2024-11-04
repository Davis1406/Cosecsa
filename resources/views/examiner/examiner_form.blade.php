@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content">
            @include('_message')
            <section class="multi_step_form">
                <form id="msform" method="POST" action="{{ route('examiner.add') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    
                    <div class="tittle">
                        <h2>Candidate Evaluation Form</h2>
                    </div>

                    <!-- Group Selection Section -->
                    <div class="form-row">
                        <div class="form-group col-md-4 col-sm-12">
                            <label>Select Group</label>
                            <select name="group_id" id="group_id" class="form-control" required onchange="fetchCandidates(this.value)">
                                <option value="">Select Group...</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">Group {{ $group->group_name }}</option>
                                @endforeach
                            </select>                             
                        </div>

                        <!-- Candidate Selection based on Group -->
                        <div class="form-group col-md-4 col-sm-12">
                            <label>Select Candidate</label>
                            <select name="candidate_id" id="candidate_id" class="form-control" required>
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

                    <!-- Dynamic Question Marks Fields -->
                    <div class="form-row justify-content-center" id="question-fields">
                        <div class="form-group col-md-9 col-sm-12">
                            <label>Enter Marks for Questions, </label>
                            <label for="question_marks_0">Question 1:</label>
                            <input type="number" name="question_marks[]" id="question_marks_0" class="form-control mb-2 question-mark" placeholder="Enter mark for question" required oninput="updateTotalMarks()" step="0.01">
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-3" onclick="addQuestionField()" style="color:black; background-color: #FEC503; border-color: #FEC503;">+ Add Question</button>

                    <!-- Overall Marks and Grade Section -->
                    <div class="form-row">
                        <div class="form-group col-md-6 col-sm-12">
                            <label>Overall Marks</label>
                            <input type="number" name="total_marks" id="total_marks" class="form-control" placeholder="Total marks will be calculated automatically" readonly>
                        </div>

                        <div class="form-group col-md-6 col-sm-12">
                            <label>Grade</label>
                            <select name="grade" class="form-control" required>
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
        const currentCount = questionFields.querySelectorAll('input[name="question_marks[]"]').length; // Count current fields
        const newField = document.createElement('div');
        newField.classList.add('form-group', 'col-md-9', 'col-sm-12', 'mb-2');
        
        newField.innerHTML = `
            <label for="question_marks_${currentCount}">Question ${currentCount + 1}:</label>
            <div class="input-group">
                <input type="number" name="question_marks[]" id="question_marks_${currentCount}" class="form-control question-mark" placeholder="Enter mark for question" required oninput="updateTotalMarks()" step="0.01">
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
        updateTotalMarks(); // Update the total marks after removal
    }

    function updateTotalMarks() {
        let total = 0;
        document.querySelectorAll('.question-mark').forEach(function(input) {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('total_marks').value = total;
    }

    function fetchCandidates(groupId) {
        if (!groupId) return; 
        fetch(`/cosecsa/get-candidates/${groupId}`) 
            .then(response => {
                if (!response.ok) { 
                    throw new Error('Network response is not ok ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                let candidateSelect = document.getElementById('candidate_id');
                candidateSelect.innerHTML = '<option value="">Choose a Candidate...</option>';
                data.forEach(candidate => {
                    candidateSelect.innerHTML += `<option value="${candidate.cand_id}">${candidate.c_id}</option>`;
                });
            })
            .catch(error => console.error('Error fetching candidates:', error));
    }
</script>

@endsection

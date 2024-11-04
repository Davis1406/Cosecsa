@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content">
            @include('_message')
            <section class="multi_step_form">
                <form id="msform" method="POST" action="{{ route('candidateform.update', ['id' => $candidate->candidates_id]) }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    
                    <div class="tittle">
                        <h2>Candidate Evaluation Form</h2>
                    </div>

                    <!-- Group Display Section (Readonly) -->
                    <div class="form-row">
                        <div class="form-group col-md-4 col-sm-12">
                            <label>Group</label>
                            <input type="text" name="group_id_display" class="form-control" value="Group {{ $candidate->group_name ?? 'N/A' }}" readonly>
                            <input type="hidden" name="group_id" value="{{ $candidate->g_id }}">
                        </div>

                        <!-- Candidate Display Section (Readonly) -->
                        <div class="form-group col-md-4 col-sm-12">
                            <label>Candidate</label>
                            <input type="text" name="candidate_id_display" class="form-control" value="{{ $candidate->candidate_name ?? 'N/A' }}" readonly>
                            <input type="hidden" name="candidate_id" value="{{ $candidate->candidates_id }}">
                        </div>
                        
                        <!-- Station Display Section (Readonly) -->
                        <div class="form-group col-md-4 col-sm-12">
                            <label>Station</label>
                            <input type="text" name="station_id_display" class="form-control" value="Station {{ $candidate->station_id }}" readonly>
                            <input type="hidden" name="station_id" value="{{ $candidate->station_id }}">
                        </div>
                    </div>

                    <!-- Dynamic Question Marks Fields -->
                    <div class="form-row justify-content-center" id="question-fields">
                        <div class="form-group col-md-9 col-sm-12">
                             <label>Enter Marks for Questions,</label>
                             @foreach($candidate->question_mark as $index => $mark)  <!-- Include index -->
                                  <label for="question_marks_{{ $index }}">Question {{ $index + 1 }}:</label> <!-- Display index + 1 -->
                                  <div class="input-group mb-2">
                                      <input type="number" name="question_marks[]" id="question_marks_{{ $index }}" class="form-control question-mark" value="{{ $mark }}" placeholder="Enter mark for question" required oninput="updateTotalMarks()" step="0.01">
                                  </div>
                              @endforeach
                        </div>
                    </div>
               
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-3" onclick="addQuestionField()" style="color:black; background-color: #FEC503; border-color: #FEC503;">+ Add Question</button>

                    <!-- Overall Marks and Grade Section -->
                    <div class="form-row">
                        <div class="form-group col-md-6 col-sm-12">
                            <label>Overall Marks</label>
                            <input type="number" name="total_marks" id="total_marks" class="form-control" value="{{ $candidate->total }}" placeholder="Total marks will be calculated automatically" readonly>
                        </div>

                        <div class="form-group col-md-6 col-sm-12">
                            <label>Grade</label>
                            <select name="grade" class="form-control" required>
                                <option value="">Select Grade...</option>
                                <option value="pass" {{ $candidate->overall == 'pass' ? 'selected' : '' }}>Pass</option>
                                <option value="borderline" {{ $candidate->overall == 'borderline' ? 'selected' : '' }}>Borderline</option>
                                <option value="fail" {{ $candidate->overall == 'fail' ? 'selected' : '' }}>Fail</option>
                            </select>
                        </div>
                    </div>

                    <!-- Examiner Remarks Section -->
                    <div class="form-row">
                        <div class="form-group col-md-12 col-sm-12">
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

    .form-row{
    
        margin:0 10px 0 10px !important;
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
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="removeQuestionField(this)">X</button>
                </div>
            </div>
        `;
        questionFields.appendChild(newField);
    }

    function removeQuestionField(button) {
        // Remove the parent div of the button
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
</script>
@endsection

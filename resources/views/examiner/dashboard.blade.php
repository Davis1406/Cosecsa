{{-- Updated examiner dashboard with new routes --}}

@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content">
                @include('_message')
                <section class="multi_step_form">
                    <form id="msform" method="POST" action="{{ route('examiner.add') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <fieldset style="border: 2.5px solid #a02626; padding: 20px; border-radius: 10px; max-width: 600px; margin: auto;">
                            <legend style="font-size: 1.5rem; font-weight: bold; color: #a02626;">Examiner Portal</legend>

                            <!-- Welcome Section -->
                            <div class="text-center my-4">
                                <h3>Dear {{ auth()->user()->name }},</h3>
                                <p>Welcome to the COSECSA marking portal. Please choose the EXAM TYPE you want to evaluate:</p>
                            </div>

                            <!-- Exam Type Selection Dropdown -->
                            <div class="form-group">
                                <label for="exam_type" style="font-weight: bold;">Select Exam Type</label>
                                <select id="exam_type" name="exam_type" class="form-control" style="border-color: #FEC503;"
                                        onchange="navigateToExamPage(this.value)">
                                    <option value="">-- Select Exam Type --</option>
                                    <option value="{{ url('examiner/examiner_form') }}">MCS</option>
                                    <option value="{{ url('examiner/general_surgery') }}">FCS General Surgery</option>
                                    <option value="{{ url('examiner/cardiothoracic') }}">FCS Cardiothoracic</option>
                                    <option value="{{ url('examiner/urology') }}">FCS Urology</option>
                                    <option value="{{ url('examiner/paediatric') }}">FCS Paediatric</option>
                                    <option value="{{ url('examiner/orthopaedic') }}">FCS Orthopaedic</option>
                                    <option value="{{ url('examiner/ent') }}">FCS ENT</option>
                                    <option value="{{ url('examiner/plastic_surgery') }}">FCS Plastic Surgery</option>
                                    <option value="{{ url('examiner/neurosurgery') }}">FCS Neurosurgery</option>
                                    <option value="{{ url('examiner/paediatric_orthopaedics') }}">FCS Paediatric Orthopaedics</option>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center mt-4">
                                <button type="button" class="btn"
                                        style="background-color: #FEC503; color: black; border-color: #FEC503;"
                                        onclick="proceedToNextPage()">
                                    Proceed <span class="fas fa-arrow-right"></span>
                                </button>
                            </div>
                        </fieldset>
                    </form>
                </section>
            </section>
        </div>
    </div>

    <script>
        function proceedToNextPage() {
            const examType = document.getElementById('exam_type').value;
            if (examType) {
                window.location.href = examType;
            } else {
                alert('Please select an exam type to proceed.');
            }
        }
    </script>
@endsection

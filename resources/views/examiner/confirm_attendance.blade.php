@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $header_title }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header" style="background-color: #a02626; color: white;">
                            <h3 class="card-title">
                                <i class="fas fa-qrcode mr-2"></i>
                                QR Code Scanned Successfully
                            </h3>
                        </div>
                        
                        <div class="card-body">
                            <!-- Display Success Message -->
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Success!</strong> {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            <!-- Display Info Message -->
                            @if(session('info'))
                                <div class="alert alert-info alert-dismissible fade show">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Info!</strong> {{ session('info') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            <!-- Display Error Message -->
                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Error!</strong> {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <!-- Display Already Registered Warning (only when there's an attempt to register again) -->
                            @if(session('already_registered'))
                                <div class="alert alert-warning alert-dismissible fade show">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Already Registered!</strong> 
                                    This examiner's attendance was already recorded today.
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            
                            <!-- Examiner Details -->
                            <div class="row mb-4">
                                <div class="col-md-4 text-center">
                                    @if($examiner->passport_image)
                                        <img src="{{ asset('storage/app/public/' . $examiner->passport_image) }}" 
                                             alt="{{ $examiner->examiner_name }}" 
                                             class="img-fluid rounded-circle mb-3"
                                             style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #a02626;">
                                    @else
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto" 
                                             style="width: 150px; height: 150px;">
                                            <i class="fas fa-user fa-4x text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="col-md-8">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{{ $examiner->examiner_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Examiner ID:</strong></td>
                                            <td>{{ $examiner->examiner_id ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Specialty:</strong></td>
                                            <td>{{ $examiner->specialty ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Country:</strong></td>
                                            <td>{{ $examiner->country_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Group:</strong></td>
                                            <td>{{ $examiner->group_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Current Time:</strong></td>
                                            <td id="currentTime">{{ now()->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="text-center">
                                @if(!$already_registered && !session('success') && !session('already_registered'))
                                    <!-- Form Method -->
                                    <form method="POST" action="{{ url('admin/exams/confirm-attendance-registration', $examiner->examin_id) }}" style="display: inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-lg mr-3" id="registerBtn">
                                            <i class="fas fa-user-check mr-2"></i>
                                            Register Attendance
                                        </button>
                                    </form>
                                @elseif($already_registered && !session('already_registered'))
                                    <!-- Show status when already registered but no attempt was made -->
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Status:</strong> This examiner has already been registered for today's attendance.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Live current time update
    setInterval(function() {
        $('#currentTime').text(new Date().toLocaleString());
    }, 1000);

    // Form submission handler with loading state
    $('form').on('submit', function(e) {
        const btn = $('#registerBtn');
        const originalHtml = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Registering...');
        
        // Allow form to submit normally
        return true;
    });

    // Auto-hide alerts after 10 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 10000);
});
</script>
@endpush

@push('styles')
<style>
.card-header {
    border-bottom: 3px solid #dee2e6;
}
.table td {
    padding: 0.5rem 0.75rem;
    vertical-align: middle;
}
.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1.1rem;
    margin-bottom: 10px;
}
/* Responsive button layout */
@media (max-width: 768px) {
    .btn-lg {
        display: block;
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>
@endpush
<ul class="nav nav-pills lms-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ request()->is('admin/exams/learning') || request()->is('admin/exams/learning/users/*') ? 'active' : '' }}" href="{{ route('admin.exams.learning') }}">Learner progress</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->is('admin/exams/learning/course') || request()->is('admin/exams/learning/modules/*') || request()->is('admin/exams/learning/blocks/*') ? 'active' : '' }}" href="{{ route('admin.exams.learning.course') }}">Course content</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->is('admin/exams/learning/certificate') ? 'active' : '' }}" href="{{ route('admin.exams.learning.certificate') }}">Certificate</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->is('admin/exams/learning/videos') ? 'active' : '' }}" href="{{ route('admin.exams.learning.videos') }}">Videos</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('admin.exams.learning.preview') }}" id="lmsPreviewLink">Preview course</a></li>
    <li class="nav-item ml-auto"><a class="nav-link" href="{{ route('admin.exams.learning.print', ['auto' => 1]) }}" target="_blank" rel="noopener" title="The whole course as one document, with the designs and a COSECSA watermark. Choose Save as PDF in the print window."><i class="fas fa-file-pdf mr-1"></i> Download course PDF</a></li>
</ul>

@include('learning.admin._preview-modal')

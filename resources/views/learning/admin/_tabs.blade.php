<ul class="nav nav-pills lms-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ request()->is('admin/exams/learning') || request()->is('admin/exams/learning/users/*') ? 'active' : '' }}" href="{{ route('admin.exams.learning') }}">Learner progress</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->is('admin/exams/learning/course') || request()->is('admin/exams/learning/modules/*') || request()->is('admin/exams/learning/blocks/*') ? 'active' : '' }}" href="{{ route('admin.exams.learning.course') }}">Course content</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->is('admin/exams/learning/videos') ? 'active' : '' }}" href="{{ route('admin.exams.learning.videos') }}">Videos</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('admin.exams.learning.preview') }}">Preview course</a></li>
</ul>

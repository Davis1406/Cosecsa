{{--
  COSECSA examiner course: shared layout.
  Extends your existing MIS layout. In config/course.php set 'parent_layout', 'content_section',
  'styles_stack' and 'scripts_stack' to match your layout (see README-IMPLEMENT.md).
--}}
@extends(config('course.parent_layout', 'layouts.app'))

@push(config('course.styles_stack', 'styles'))
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Raleway:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="{{ asset('css/course/cosecsa-course.css') }}">
<link rel="stylesheet" href="{{ asset('css/course/modules/'.$slug.'.css') }}">
@endpush

@section(config('course.content_section', 'content'))
<div class="cs-course mod-{{ $mod }}" data-progress-key="{{ $key ?? '' }}" data-img="{{ asset('images/course') }}/" @if(!empty($lite)) data-lite @endif
     @if(!empty($saveUrl)) data-save-url="{{ $saveUrl }}" data-seen="{{ json_encode(array_values($topics_read ?? [])) }}" data-csrf="{{ csrf_token() }}" @endif>
  @include('course.partials.icons')
  @yield('course-body')
</div>
@endsection

@push(config('course.scripts_stack', 'scripts'))
<script src="{{ asset('js/course/cosecsa-course.js') }}"></script>
@if(!empty($moduleJs))<script src="{{ asset('js/course/modules/'.$slug.'.js') }}"></script>@endif
@endpush

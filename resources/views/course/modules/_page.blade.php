{{--
  A coded course page rendered from its saved page model (data) instead of its own markup.
  The model is edited in Admin > Examiner Training > Course content (see App\Support\CoursePage).
  $pageMeta is the page's layout settings (the @extends line of its own view).
--}}
@extends('course.layout', $pageMeta + ['wrapStyle' => \App\Support\CoursePage::wrapStyle($page)])

@section('title', strip_tags($page['hero']['title'] ?? 'Examiner course'))

@section('course-body')
{!! \App\Support\CoursePage::render($page, ! empty($cwMark)) !!}
@endsection

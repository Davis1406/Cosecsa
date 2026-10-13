@extends('layout.app')

@section('content')
<div class="content-wrapper">
    @include('learning.admin._head', ['title' => 'Edit block', 'subtitle' => $course['title'].' / '.$module['title']])
    <section class="content">
    <div class="container-fluid lms">
    @include('learning.partials.flash')
    @include('learning.admin._tabs')

    <div class="flex-between editor-head">
        <div>
            <span class="badge-gray">{{ $block['type'] }}</span>
            @if($block['variant'])
                <span class="badge-gray">{{ $block['variant'] }}</span>
            @endif
        </div>
        <a href="{{ route('admin.exams.learning.module', $module['id']) }}" class="btn btn-light btn-sm">← Back to module</a>
    </div>

    @if(($blockObject->type ?? '') === 'MULTIPLE_CHOICE' && ($module['type'] ?? '') === 'quiz')
        @include('learning.admin._quiz-editor', [
            'block' => $blockObject,
            'module' => $module,
            'cancelUrl' => route('admin.exams.learning.module', $module['id']),
        ])
    @else
        @include('learning.admin._block-editor', [
            'block' => $blockObject,
            'fields' => $fields,
            'images' => $images,
            'module' => $module,
            'cancelUrl' => route('admin.exams.learning.module', $module['id']),
        ])
    @endif
    </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
@include('learning.admin._block-editor-styles')
@include('learning.admin._quiz-editor-styles')
@endpush

@push('scripts')
@include('learning.admin._block-editor-script')
<script>
    const qeForm = document.querySelector('[data-quiz-editor]');
    if (!qeForm) LmsBlockEditor(document.querySelector('[data-block-editor]'), { globalSave: true });
</script>
@endpush
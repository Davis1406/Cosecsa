@extends('layout.app')

@section('content')
<div class="content-wrapper">
    @include('learning.admin._head', ['title' => $module['title'], 'subtitle' => $course['title']])
    <section class="content">
        <div class="container-fluid lms">
            @include('learning.partials.flash')
            @include('learning.admin._tabs')

            <div class="mb-3">
                <a href="{{ route('admin.exams.learning.course') }}" class="btn btn-light btn-sm">← All modules</a>
                <a href="{{ route('admin.exams.learning.preview.module', $module['slug']) }}" class="btn btn-light btn-sm" data-lms-preview>Preview as examiner</a>
            </div>

            @if($module['type'] === 'quiz')
                <div class="alert alert-info">Quiz questions are part of the course import and are not editable here.</div>
            @endif

            <div class="card card-pad">
                <div class="flex-between mb-3">
                    <h5 style="font-size:16px; font-weight:700; margin:0;">Blocks ({{ count($blocks) }})</h5>
                    <span class="muted" style="font-size:12.5px;">Edit any block to open the live preview editor</span>
                </div>
                <div class="table-responsive">
                    <table class="tbl">
                        <thead>
                            <tr><th style="width:50px;">#</th><th>Type</th><th>Variant</th><th>Content</th><th style="width:110px;"></th></tr>
                        </thead>
                        <tbody>
                            @foreach($blocks as $block)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><span class="badge-gray">{{ $block['type'] }}</span></td>
                                    <td class="muted">{{ $block['variant'] ?? '—' }}</td>
                                    <td class="muted" style="font-size:12.5px; max-width:360px;">{{ $block['summary'] }}</td>
                                    <td>
                                        @unless($module['type'] === 'quiz')
                                            <a href="{{ route('admin.exams.learning.block', $block['id']) }}" class="btn btn-light btn-sm">Edit</a>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
@endpush

@extends('layout.app')

@section('content')
<div class="content-wrapper">
    @include('learning.admin._head', ['title' => 'Examiner Training', 'subtitle' => $course['title']])
    <section class="content">
        <div class="container-fluid lms">
            @include('learning.partials.flash')
            @include('learning.admin._tabs')

            <div class="card card-pad mb-3">
                <h5 style="font-size:16px; font-weight:700; margin-bottom:4px;">Course logo</h5>
                <p class="muted" style="font-size:13px; margin-bottom:16px;">Shown beside the course title in the module sidebar.</p>
                <div class="d-flex flex-wrap" style="gap:20px; align-items:flex-start;">
                    <div class="logo-preview">
                        @if($course['logo_url'])
                            <img src="{{ $course['logo_url'] }}" alt="Current logo">
                        @else
                            <div class="logo-preview-empty">No logo</div>
                        @endif
                    </div>
                    <div style="flex:1; min-width:240px;">
                        <form method="POST" action="{{ route('admin.exams.learning.logo') }}" enctype="multipart/form-data" class="d-flex flex-wrap" style="gap:8px;">
                            @csrf
                            <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="form-control" style="max-width:300px;" required>
                            <button type="submit" class="btn btn-primary btn-sm">Upload logo</button>
                        </form>
                        <div class="muted" style="font-size:12px; margin-top:6px;">PNG, JPG, SVG or WebP · up to 2MB</div>
                        @if($course['logo_url'])
                            <form method="POST" action="{{ route('admin.exams.learning.logo.remove') }}" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-light btn-sm">Remove logo</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card card-pad">
                <h5 style="font-size:16px; font-weight:700; margin-bottom:16px;">Modules ({{ count($modules) }})</h5>
                <div class="course-module-list">
                    @foreach($modules as $module)
                        <div class="course-module-row">
                            <span class="course-module-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="course-module-info">
                                <div class="course-module-title">{{ $module['title'] }}</div>
                                <div class="muted" style="font-size:12.5px;">
                                    {{ ucfirst($module['type']) }} · {{ $module['blocks_count'] }} blocks
                                    @if($module['duration_minutes']) · ~{{ $module['duration_minutes'] }} min @endif
                                </div>
                            </div>
                            @if($module['type'] === 'content')
                                <span class="badge-brand">Content</span>
                            @else
                                <span class="badge-gray">{{ ucfirst($module['type']) }}</span>
                            @endif
                            <a href="{{ route('admin.exams.learning.preview.module', $module['slug']) }}" class="btn btn-light btn-sm">Preview</a>
                            @if($module['type'] === 'quiz')
                                <a href="{{ route('admin.exams.learning.module', $module['id']) }}" class="btn btn-light btn-sm">View</a>
                            @else
                                <a href="{{ route('admin.exams.learning.module', $module['id']) }}" class="btn btn-primary btn-sm">Edit content →</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
<style>
    .lms .logo-preview {
        width: 120px; height: 120px; min-width: 120px; border-radius: 14px; border: 1.5px dashed #cbd5e1; background: #f8fafc;
        display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 10px;
    }
    .lms .logo-preview img { width: 100%; height: 100%; object-fit: contain; }
    .lms .logo-preview-empty { font-size: 12.5px; color: #94a3b8; font-weight: 600; }
    .lms .course-module-list { display: flex; flex-direction: column; gap: 8px; }
    .lms .course-module-row {
        display: flex; align-items: center; gap: 14px; border: 1px solid #eef1f5; border-radius: 12px; padding: 14px 16px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .lms .course-module-row:hover { border-color: var(--brand); box-shadow: 0 3px 12px rgba(16,24,40,.07); }
    .lms .course-module-num {
        width: 34px; height: 34px; min-width: 34px; border-radius: 9px; background: rgba(153,6,10,.1); color: var(--brand);
        display: flex; align-items: center; justify-content: center; font-size: 12.5px; font-weight: 800;
    }
    .lms .course-module-info { flex: 1; min-width: 0; }
    .lms .course-module-title { font-size: 14.5px; font-weight: 600; color: #1e293b; }
</style>
@endpush

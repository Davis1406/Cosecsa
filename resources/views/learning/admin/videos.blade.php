@extends('layout.app')

@section('content')
<div class="content-wrapper">
    @include('learning.admin._head', ['title' => 'Examiner Training', 'subtitle' => 'Course videos'])
    <section class="content">
        <div class="container-fluid lms">
            @include('learning.partials.flash')
            @include('learning.admin._tabs')

            <div class="card card-pad mb-3">
                <p class="muted mb-0" style="font-size:13px;">
                    Upload the video for each course video block. Uploaded videos appear in the course straight away.
                    Files above the server upload limit can be linked on the API server with
                    <code>php artisan lms:import-videos &lt;folder&gt;</code>.
                </p>
            </div>

            <div class="video-admin-list">
                @foreach($blocks as $block)
                    <div class="card card-pad">
                        <div class="flex-between">
                            <div>
                                <strong>{{ $block['label'] }}</strong>
                                <div class="muted" style="font-size:12.5px;">{{ $block['module'] }}</div>
                            </div>
                            @if($block['video_url'])
                                <span class="badge-green">✓ Uploaded</span>
                            @else
                                <span class="badge-red">Awaiting upload</span>
                            @endif
                        </div>
                        <div class="va-frame mt-2">
                            @if($block['video_url'])
                                <video controls preload="metadata" src="{{ $block['video_url'] }}"></video>
                            @else
                                <span class="muted">No video yet</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.exams.learning.videos.upload') }}" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <input type="hidden" name="block_id" value="{{ $block['id'] }}">
                            <div class="va-form">
                                <input type="file" name="video" accept="video/mp4,video/quicktime,video/webm,video/x-m4v" class="form-control" required>
                                <input type="text" name="video_label" value="{{ $block['label'] }}" placeholder="Label (optional)" class="form-control">
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
<style>
    /* Square video tiles, two per row (one on phones) — matches the course player. */
    .lms .video-admin-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; align-items: start; max-width: 860px; }
    .lms .va-frame { aspect-ratio: 1 / 1; background: #0f172a; border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
    .lms .va-frame video { width: 100%; height: 100%; object-fit: contain; display: block; }
    .lms .va-frame .muted { color: #94a3b8; font-size: 13px; }
    .lms .va-form { display: flex; flex-direction: column; gap: 8px; }
    @media (max-width: 640px) { .lms .video-admin-list { grid-template-columns: minmax(0, 1fr); } }
</style>
@endpush

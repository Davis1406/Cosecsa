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
                        @if($block['video_url'])
                            <div class="mt-2">
                                <video controls preload="metadata" src="{{ $block['video_url'] }}" style="width:100%; max-height:240px; border-radius:8px; background:#000;"></video>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('admin.exams.learning.videos.upload') }}" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <input type="hidden" name="block_id" value="{{ $block['id'] }}">
                            <div class="d-flex flex-wrap" style="gap:8px;">
                                <input type="file" name="video" accept="video/mp4,video/quicktime,video/webm,video/x-m4v" class="form-control" style="max-width:340px;" required>
                                <input type="text" name="video_label" value="{{ $block['label'] }}" placeholder="Label (optional)" class="form-control" style="max-width:260px;">
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
<style>.lms .video-admin-list { display: flex; flex-direction: column; gap: 16px; }</style>
@endpush

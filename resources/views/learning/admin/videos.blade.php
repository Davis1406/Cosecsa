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

            <div class="card card-pad mb-3 va-add">
                <h5 style="font-size:16px; font-weight:700; margin-bottom:4px;">Add a video</h5>
                <p class="muted" style="font-size:12.5px;">Adds a new video to a module. It appears in the course straight away, where you place it.</p>
                <form method="POST" action="{{ route('admin.exams.learning.videos.add') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="va-label" for="va-module">Module</label>
                            <select name="module_id" id="va-module" class="form-control" required>
                                @foreach($modules as $m)
                                    <option value="{{ $m['id'] }}" @selected(old('module_id') == $m['id'])>{{ $m['title'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="va-label" for="va-after">Position</label>
                            <select name="after_block_id" id="va-after" class="form-control">
                                <option value="">At the end of the module</option>
                                @foreach($modules as $m)
                                    @foreach($m['blocks'] as $b)
                                        <option value="{{ $b['id'] }}" data-module="{{ $m['id'] }}">After {{ $loop->iteration }}. {{ $b['label'] }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="va-label" for="va-label">Label</label>
                            <input type="text" name="video_label" id="va-label" value="{{ old('video_label') }}" placeholder="e.g. Marking sheet walkthrough" class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="va-label" for="va-caption">Caption <span class="muted">(optional, shown under the video)</span></label>
                            <input type="text" name="caption" id="va-caption" value="{{ old('caption') }}" class="form-control" maxlength="500">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="va-label" for="va-file">Video file <span class="muted">(MP4, MOV, WebM)</span></label>
                            <input type="file" name="video" id="va-file" accept="video/mp4,video/quicktime,video/webm,video/x-m4v" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Add video</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="video-admin-list">
                @foreach($blocks as $block)
                    <div class="card card-pad">
                        <div class="flex-between">
                            <div>
                                <strong>{{ $block['label'] }}</strong>
                                <div class="muted" style="font-size:12.5px;">{{ $block['module'] }}</div>
                            </div>
                            @unless($block['video_url'])
                                <span class="badge-red">Awaiting upload</span>
                            @endunless
                        </div>
                        <div class="va-frame mt-2">
                            @if($block['video_url'])
                                <video controls preload="none" data-src="{{ $block['video_url'] }}"></video>
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
    .lms .va-add { max-width: 860px; }
    .lms .va-label { font-size: 12.5px; font-weight: 600; margin-bottom: 4px; }
</style>
@endpush

@push('scripts')
<script>
    // Position choices follow the selected module.
    (function () {
        var module = document.getElementById('va-module'), after = document.getElementById('va-after');
        if (!module) return;
        function sync() {
            after.querySelectorAll('option[data-module]').forEach(function (o) {
                var show = o.dataset.module === module.value;
                o.hidden = !show; o.disabled = !show;
            });
            if (after.selectedOptions[0] && after.selectedOptions[0].disabled) after.value = '';
        }
        module.addEventListener('change', sync);
        sync();
    })();
</script>
<script>
    // Load the videos one at a time. Twelve <video preload> tags at once fill the
    // browser's per-host connection limit and none of them finish loading.
    (async function () {
        for (const video of document.querySelectorAll('.va-frame video[data-src]')) {
            video.preload = 'metadata';
            video.src = video.dataset.src;
            await new Promise(function (done) {
                video.addEventListener('loadeddata', done, { once: true });
                video.addEventListener('error', done, { once: true });
                setTimeout(done, 8000);
            });
        }
    })();
</script>
@endpush

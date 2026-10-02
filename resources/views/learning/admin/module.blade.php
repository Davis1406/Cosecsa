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
                <div class="alert alert-info mb-3">Quiz questions are part of the course import and are not editable here.</div>
            @endif

            <div class="flex-between mb-3">
                <h5 style="font-size:16px; font-weight:700; margin:0;">Blocks ({{ count($blocks) }})</h5>
                <span class="muted" style="font-size:12.5px;">
                    @if($module['type'] === 'quiz')
                        Questions are read-only.
                    @else
                        Click a block to edit it inline, or use the arrows to reorder.
                    @endif
                </span>
            </div>

            <div class="module-blocks">
                @foreach($blocks as $block)
                    @php
                        $blockObject = (object) [
                            'id' => $block['id'],
                            'type' => $block['type'],
                            'family' => $block['family'],
                            'variant' => $block['variant'],
                            'payload' => $block['payload'],
                        ];
                    @endphp
                    <div class="lms-block" data-block-id="{{ $block['id'] }}">
                        <div class="lms-block-bar">
                            <span class="badge-gray">{{ $block['type'] }}</span>
                            @if($block['variant'])<span class="badge-gray">{{ $block['variant'] }}</span>@endif
                            <span class="flex-spacer"></span>
                            @unless($module['type'] === 'quiz')
                                <button type="button" class="btn btn-light btn-sm lms-block-move" data-move="up"
                                        data-move-url="{{ route('admin.exams.learning.block.move', $block['id']) }}"
                                        title="Move up" @disabled($loop->first)>↑</button>
                                <button type="button" class="btn btn-light btn-sm lms-block-move" data-move="down"
                                        data-move-url="{{ route('admin.exams.learning.block.move', $block['id']) }}"
                                        title="Move down" @disabled($loop->last)>↓</button>
                                <button type="button" class="btn btn-light btn-sm lms-block-edit">Edit</button>
                            @endunless
                        </div>
                        <div class="lms-block-preview">
                            @include('learning.blocks._dispatch', ['block' => $blockObject])
                        </div>
                        @unless($module['type'] === 'quiz')
                            <div class="lms-block-editor" hidden>
                                @include('learning.admin._block-editor', [
                                    'block' => $blockObject,
                                    'fields' => $block['fields'],
                                    'images' => $block['images'],
                                    'module' => $module,
                                    'cancelUrl' => null,
                                ])
                            </div>
                        @endunless
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
@include('learning.admin._block-editor-styles')
<style>
    .module-blocks { display: flex; flex-direction: column; gap: 14px; max-width: 1000px; }
    .lms-block {
        border: 1px solid #e6e9ef; border-radius: 14px; background: #fff;
        overflow: hidden; transition: border-color .15s ease, box-shadow .15s ease;
    }
    .lms-block:hover { border-color: rgba(153,6,10,.35); box-shadow: 0 4px 14px rgba(16,24,40,.06); }
    .lms-block.is-editing { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(153,6,10,.12); }
    .lms-block-bar {
        display: flex; align-items: center; gap: 8px; padding: 10px 14px;
        border-bottom: 1px solid #eef1f5; background: #fafbfc;
    }
    .lms-block-bar .flex-spacer { flex: 1; }
    .lms-block-bar .btn { border-radius: 6px; }
    .lms-block-preview { padding: 22px 26px; }
    .lms-block-editor { padding: 18px; border-top: 1px solid #eef1f5; background: #f8fafc; }
    .lms-block-editor .editor-fields { max-height: none; }
    .lms-block-editor .preview-frame { max-height: 420px; }
    .lms-block-preview .block-reveal { cursor: default; }
    @media (max-width: 900px) { .lms-block-bar { flex-wrap: wrap; } }
</style>
@endpush

@push('scripts')
@include('learning.admin._block-editor-script')
<script>
    (function () {
        const blocks = [...document.querySelectorAll('.lms-block')];

        // Blocks with entrance animations start hidden — reveal them here so
        // the editing view never shows blank content.
        blocks.forEach(b => b.querySelectorAll('[data-animate="1"]').forEach(el => el.classList.add('is-visible')));

        // Clicking a block or its Edit button opens its inline editor.
        blocks.forEach(block => {
            const editor = block.querySelector('.lms-block-editor');
            if (!editor) return;
            const toggle = (open) => {
                editor.hidden = !open;
                block.classList.toggle('is-editing', open);
                if (open) {
                    const form = editor.querySelector('[data-block-editor]');
                    LmsBlockEditor(form);
                    form.querySelector('.editable-field')?.focus();
                }
            };

            block.querySelector('.lms-block-edit').addEventListener('click', () => toggle(editor.hidden));
            block.querySelector('.lms-block-preview').addEventListener('click', () => {
                if (!block.classList.contains('is-editing')) toggle(true);
            });
        });

        // Reorder buttons post to the move endpoint, then reload to re-render.
        document.querySelectorAll('.lms-block-move').forEach(btn => {
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                const fd = new FormData();
                fd.append('direction', btn.dataset.move);
                const token = document.querySelector('input[name="_token"]')?.value || '';
                fd.append('_token', token);
                try {
                    await fetch(btn.dataset.moveUrl, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': token } });
                } catch (e) {}
                window.location.reload();
            });
        });
    })();
</script>
@endpush
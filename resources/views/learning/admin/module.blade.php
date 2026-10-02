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
                <div class="d-flex align-items-center" style="gap:12px;">
                    <span class="muted" style="font-size:12.5px;">
                        @if($module['type'] === 'quiz')
                            Questions are read-only.
                        @else
                            Click a block to edit it inline, or use the arrows to reorder.
                        @endif
                    </span>
                    @unless($module['type'] === 'quiz')
                        <button type="button" class="btn btn-primary btn-sm" data-add-block-open>
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" style="vertical-align:-1px; margin-right:4px;"><path d="M12 5v14M5 12h14"/></svg>
                            Add block
                        </button>
                    @endunless
                </div>
            </div>

            <div class="module-blocks" data-focus-block="{{ session('focus_block') }}">
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
                    <div class="lms-block" data-block-id="{{ $block['id'] }}" style="--i: {{ $loop->index }}">
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
                                <form method="POST" action="{{ route('admin.exams.learning.block.duplicate', $block['id']) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-light btn-sm" title="Duplicate this block">
                                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px; margin-right:3px;"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
                                        Duplicate
                                    </button>
                                </form>
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
                    @unless($module['type'] === 'quiz')
                        <button type="button" class="lms-block-insert" data-add-block-open data-after-id="{{ $block['id'] }}" title="Add a block after this one">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                            <span>Add block</span>
                        </button>
                    @endunless
                @endforeach
                @unless($module['type'] === 'quiz')
                    <button type="button" class="lms-block-insert lms-block-insert-end" data-add-block-open>
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        <span>Add block</span>
                    </button>
                @endunless
            </div>

            @unless($module['type'] === 'quiz')
                {{-- WordPress-style "Add block" picker --}}
                <form id="add-block-form" method="POST" action="{{ route('admin.exams.learning.block.store') }}">
                    @csrf
                    <input type="hidden" name="module_id" value="{{ $module['id'] }}">
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="after_block_id" value="">
                </form>

                <div class="modal fade" id="addBlockModal" tabindex="-1" role="dialog" aria-labelledby="addBlockModalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header py-2">
                                <h5 class="modal-title" id="addBlockModalTitle">Add block</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                @if(empty($blockTypes))
                                    <p class="muted" style="margin:0;">No block types are available right now.</p>
                                @else
                                    @php
                                        $typeIcons = [
                                            'paragraph' => '¶', 'heading' => 'H', 'heading_paragraph' => 'H¶', 'quote' => '❝', 'impact' => '!',
                                            'list_bullets' => '•', 'list_numbered' => '№', 'checklist' => '✓',
                                            'image' => '▨', 'image_text' => '▣', 'video' => '▶', 'divider' => '─',
                                            'flashcard' => '⇄', 'labeledgraphic' => '◉', 'process' => '➤',
                                        ];
                                    @endphp
                                    @foreach($blockTypes as $group => $types)
                                        <div class="block-type-group">
                                            <div class="block-type-group-label">{{ $group }}</div>
                                            <div class="block-type-grid">
                                                @foreach($types as $t)
                                                    <button type="button" class="block-type-tile" data-type="{{ $t['id'] }}" title="{{ $t['hint'] ?? '' }}">
                                                        <span class="block-type-icon">{{ $typeIcons[$t['id']] ?? '+' }}</span>
                                                        <span class="block-type-label">{{ $t['label'] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endunless
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
@include('learning.admin._block-editor-styles')
<style>
    .module-blocks { display: flex; flex-direction: column; gap: 6px; max-width: 1000px; }
    .lms-block {
        position: relative; border: 1px solid #e6e9ef; border-radius: 14px; background: #fff;
        overflow: hidden;
        transition: border-color .25s ease, box-shadow .25s ease, transform .25s cubic-bezier(.22,1,.36,1);
        animation: blockIn .35s cubic-bezier(.22,1,.36,1) both;
        animation-delay: calc(var(--i, 0) * 45ms);
    }
    @keyframes blockIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
    .lms-block::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; z-index: 2;
        background: linear-gradient(180deg, var(--brand), #FEC503);
        transform: scaleY(0); transform-origin: top; transition: transform .3s cubic-bezier(.22,1,.36,1);
    }
    .lms-block:hover { border-color: rgba(153,6,10,.4); box-shadow: 0 6px 18px rgba(16,24,40,.10); transform: translateY(-2px); }
    .lms-block:hover::before { transform: scaleY(1); }
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

    /* ── Add-block insert between blocks ────────────────────────────────── */
    .lms-block-insert {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        width: 100%; padding: 6px; margin: 2px 0;
        border: 1.5px dashed #cbd5e1; border-radius: 10px; background: transparent;
        color: #94a3b8; font-size: 12.5px; font-weight: 600; cursor: pointer;
        opacity: .35; transition: opacity .2s ease, color .2s ease, border-color .2s ease, background .2s ease;
    }
    .lms-block-insert:hover {
        opacity: 1; color: var(--brand); border-color: rgba(153,6,10,.5); background: rgba(153,6,10,.04);
    }
    .lms-block-insert-end { margin-top: 6px; }

    /* ── Add-block picker ───────────────────────────────────────────────── */
    .block-type-group { margin-bottom: 22px; }
    .block-type-group:last-child { margin-bottom: 0; }
    .block-type-group-label {
        font-family: 'Source Sans Pro', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        font-size: 14px; font-weight: 700; color: #334155;
        margin-bottom: 12px;
    }
    .block-type-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    @media (max-width: 640px) { .block-type-grid { grid-template-columns: 1fr; } }
    .block-type-tile {
        display: flex; align-items: center; gap: 14px; padding: 14px 16px;
        border: 1.5px solid #e2e8f0; border-radius: 10px; background: #fff; cursor: pointer;
        text-align: left;
        font-family: 'Source Sans Pro', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .block-type-tile:hover {
        border-color: var(--brand); transform: translateY(-1px);
        box-shadow: 0 5px 14px rgba(16,24,40,.10); background: rgba(153,6,10,.03);
    }
    .block-type-icon {
        flex: 0 0 44px; width: 44px; height: 44px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: #f4f6f9; color: #a02626;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 22px; font-weight: 700;
    }
    .block-type-label {
        font-size: 16px; font-weight: 600; color: #212529; line-height: 1.3;
    }

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

        // ── Add block ───────────────────────────────────────────────────────
        const addForm = document.getElementById('add-block-form');
        const addModal = document.getElementById('addBlockModal');
        if (addForm && addModal) {
            let afterId = '';
            document.querySelectorAll('[data-add-block-open]').forEach(btn => {
                btn.addEventListener('click', () => {
                    afterId = btn.dataset.afterId || '';
                    $('#addBlockModal').modal('show');
                });
            });
            addModal.querySelectorAll('.block-type-tile').forEach(tile => {
                tile.addEventListener('click', () => {
                    addForm.querySelector('[name="type"]').value = tile.dataset.type;
                    addForm.querySelector('[name="after_block_id"]').value = afterId;
                    addForm.submit();
                });
            });
        }

        // ── Open a freshly-added block's editor (focus_block flash) ───────
        const wrapper = document.querySelector('.module-blocks');
        const focusId = wrapper && wrapper.dataset.focusBlock;
        if (focusId) {
            const host = document.querySelector('.lms-block[data-block-id="' + focusId + '"]');
            if (host) {
                const editor = host.querySelector('.lms-block-editor');
                if (editor) {
                    editor.hidden = false;
                    host.classList.add('is-editing');
                    const form = editor.querySelector('[data-block-editor]');
                    LmsBlockEditor(form);
                    host.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    form.querySelector('.editable-field')?.focus();
                }
            }
        }
    })();
</script>
@endpush
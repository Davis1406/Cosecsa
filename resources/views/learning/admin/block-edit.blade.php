@extends('layout.app')

@php
    $icons = [
        'bold' => '<path d="M7 5h6a3.5 3.5 0 0 1 0 7H7zM7 12h7a3.5 3.5 0 0 1 0 7H7z"/>',
        'italic' => '<path d="M14 5h-4M14 19h-4M14 5l-4 14"/>',
        'underline' => '<path d="M7 4v7a5 5 0 0 0 10 0V4M5 20h14"/>',
        'strike' => '<path d="M5 12h14M16 7a4 3 0 0 0-8 0c0 4 8 3 8 7a4 3 0 0 1-8 0"/>',
        'sup' => '<path d="M4 18l7-9M4 9l7 9M15 9a2 2 0 1 1 4 0c0 1.5-4 3-4 4h4"/>',
        'sub' => '<path d="M4 15l7-9M4 6l7 9M15 16a2 2 0 1 1 4 0c0 1.5-4 3-4 4h4"/>',
        'ul' => '<path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4.5" cy="6" r="1"/><circle cx="4.5" cy="12" r="1"/><circle cx="4.5" cy="18" r="1"/>',
        'ol' => '<path d="M10 6h10M10 12h10M10 18h10M4 5l1.5-1v5M3.5 14.5a1.5 1.5 0 1 1 2.5 1L3.5 19h3"/>',
        'outdent' => '<path d="M21 6H11M21 12H11M21 18H11M7 9l-3 3 3 3"/>',
        'indent' => '<path d="M21 6H11M21 12H11M21 18H11M3 9l3 3-3 3"/>',
        'left' => '<path d="M4 6h16M4 10h10M4 14h16M4 18h10"/>',
        'center' => '<path d="M4 6h16M7 10h10M4 14h16M7 18h10"/>',
        'right' => '<path d="M4 6h16M10 10h10M4 14h16M10 18h10"/>',
        'justify' => '<path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
        'link' => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
        'hr' => '<path d="M3 12h18M7 7h10M7 17h10" opacity=".35"/><path d="M3 12h18"/>',
        'clear' => '<path d="M6 5h12M12 5l-3 14M4 20l16-16"/>',
        'undo' => '<path d="M9 14L4 9l5-5M4 9h11a5 5 0 0 1 0 10h-3"/>',
        'redo' => '<path d="M15 14l5-5-5-5M20 9H9a5 5 0 0 0 0 10h3"/>',
        'color' => '<path d="M6 18L12 4l6 14M8.5 12.5h7"/>',
        'highlight' => '<path d="M9 15l-4 4h6l1-1M14 4l6 6-8 8-6-6z"/>',
    ];
    $tb = fn ($name) => '<svg viewBox="0 0 24 24" aria-hidden="true">'.$icons[$name].'</svg>';
    $textColors = ['#99060a' => 'COSECSA maroon', '#b7791f' => 'Gold', '#111111' => 'Black', '#475569' => 'Slate', '#047857' => 'Green', '#1d4ed8' => 'Blue', '#b91c1c' => 'Red'];
    $highlights = ['#fef08a' => 'Yellow', '#fde2e1' => 'Rose', '#dcfce7' => 'Mint', '#dbeafe' => 'Sky', '#f1f5f9' => 'Grey'];
@endphp

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

    @if(empty($fields) && empty($images))
        <div class="alert alert-info">
            This block has no editable text or images ({{ $block['type'] }} / {{ $block['variant'] ?? '—' }}).
        </div>
    @endif

    <form id="block-editor-form" method="POST" action="{{ route('admin.exams.learning.block.update', $block['id']) }}"
          data-preview-url="{{ route('admin.exams.learning.block.preview', $block['id']) }}"
          data-upload-url="{{ route('admin.exams.learning.block.image', $block['id']) }}">
        @csrf
        <div class="editor-split">
            <section class="editor-pane card">
                <div class="editor-pane-head">Content <span class="muted" style="font-weight:400;">— type and watch the preview update</span></div>

                @if(!empty($fields))
                    {{-- Formatting toolbar — acts on whichever text field has focus --}}
                    <div class="rt-toolbar" id="rt-toolbar" role="toolbar" aria-label="Text formatting" data-disabled="1">
                        <div class="rt-group">
                            <select class="rt-select" data-rt-block title="Paragraph style" aria-label="Paragraph style">
                                <option value="p">Paragraph</option>
                                <option value="h2">Heading</option>
                                <option value="h3">Subheading</option>
                                <option value="h4">Small heading</option>
                                <option value="blockquote">Quote</option>
                            </select>
                            <select class="rt-select" data-rt-size title="Text size" aria-label="Text size">
                                <option value="">Size</option>
                                <option value="13px">Small</option>
                                <option value="normal">Normal</option>
                                <option value="18px">Large</option>
                                <option value="22px">X-Large</option>
                                <option value="28px">Huge</option>
                            </select>
                        </div>
                        <div class="rt-group">
                            <button type="button" class="rt-btn" data-cmd="bold" data-state title="Bold (Ctrl/⌘+B)">{!! $tb('bold') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="italic" data-state title="Italic (Ctrl/⌘+I)">{!! $tb('italic') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="underline" data-state title="Underline (Ctrl/⌘+U)">{!! $tb('underline') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="strikeThrough" data-state title="Strikethrough">{!! $tb('strike') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="superscript" data-state title="Superscript">{!! $tb('sup') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="subscript" data-state title="Subscript">{!! $tb('sub') !!}</button>
                        </div>
                        <div class="rt-group">
                            <div class="rt-pop-wrap">
                                <button type="button" class="rt-btn rt-btn-color" data-rt-pop="color" title="Text colour">{!! $tb('color') !!}<span class="rt-swatch-bar" data-color-bar></span></button>
                                <div class="rt-pop" data-pop="color" hidden>
                                    <div class="rt-pop-title">Text colour</div>
                                    <div class="rt-swatches">
                                        @foreach($textColors as $hex => $name)
                                            <button type="button" class="rt-swatch" style="background: {{ $hex }}" data-fore="{{ $hex }}" title="{{ $name }}"></button>
                                        @endforeach
                                        <label class="rt-swatch rt-swatch-custom" title="Custom colour"><input type="color" data-fore-custom value="#99060a"></label>
                                    </div>
                                    <button type="button" class="rt-pop-link" data-fore="inherit">Reset colour</button>
                                </div>
                            </div>
                            <div class="rt-pop-wrap">
                                <button type="button" class="rt-btn" data-rt-pop="highlight" title="Highlight">{!! $tb('highlight') !!}</button>
                                <div class="rt-pop" data-pop="highlight" hidden>
                                    <div class="rt-pop-title">Highlight</div>
                                    <div class="rt-swatches">
                                        @foreach($highlights as $hex => $name)
                                            <button type="button" class="rt-swatch" style="background: {{ $hex }}" data-hilite="{{ $hex }}" title="{{ $name }}"></button>
                                        @endforeach
                                    </div>
                                    <button type="button" class="rt-pop-link" data-hilite="transparent">No highlight</button>
                                </div>
                            </div>
                        </div>
                        <div class="rt-group">
                            <button type="button" class="rt-btn" data-cmd="insertUnorderedList" data-state title="Bulleted list">{!! $tb('ul') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="insertOrderedList" data-state title="Numbered list">{!! $tb('ol') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="outdent" title="Decrease indent">{!! $tb('outdent') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="indent" title="Increase indent">{!! $tb('indent') !!}</button>
                        </div>
                        <div class="rt-group">
                            <button type="button" class="rt-btn" data-cmd="justifyLeft" data-state title="Align left">{!! $tb('left') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="justifyCenter" data-state title="Align centre">{!! $tb('center') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="justifyRight" data-state title="Align right">{!! $tb('right') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="justifyFull" data-state title="Justify">{!! $tb('justify') !!}</button>
                        </div>
                        <div class="rt-group">
                            <button type="button" class="rt-btn" data-rt-link title="Link (Ctrl/⌘+K)">{!! $tb('link') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="insertHorizontalRule" title="Divider line">{!! $tb('hr') !!}</button>
                            <button type="button" class="rt-btn" data-rt-clear title="Clear formatting">{!! $tb('clear') !!}</button>
                        </div>
                        <div class="rt-group">
                            <button type="button" class="rt-btn" data-cmd="undo" title="Undo (Ctrl/⌘+Z)">{!! $tb('undo') !!}</button>
                            <button type="button" class="rt-btn" data-cmd="redo" title="Redo (Ctrl/⌘+Shift+Z)">{!! $tb('redo') !!}</button>
                        </div>

                        <div class="rt-linkbar" data-linkbar hidden>
                            <input type="url" class="form-control" placeholder="https://… or mailto:…" data-link-url aria-label="Link address">
                            <label class="rt-check"><input type="checkbox" data-link-blank checked> New tab</label>
                            <button type="button" class="btn btn-primary btn-sm" data-link-apply>Apply</button>
                            <button type="button" class="btn btn-light btn-sm" data-link-remove>Remove link</button>
                        </div>
                    </div>
                @endif

                <div class="editor-fields">
                    @foreach($fields as $field)
                        <div class="editor-field" data-path="{{ $field['path'] }}">
                            <label class="editor-label">{{ $field['label'] }}</label>
                            <div class="editable-field" contenteditable="true" spellcheck="true" data-placeholder="Empty"></div>
                            <input type="hidden" name="fields[{{ implode('][', explode('.', $field['path'])) }}]" value="{{ $field['value'] }}" data-text-input>
                        </div>
                    @endforeach

                    @if(!empty($images))
                        <div class="editor-images-divider">
                            <div class="editor-label">Images</div>
                            <p class="muted" style="font-size:12px;">Replace an image, or change its size, alignment, corners, border, shadow and opacity.</p>
                        </div>
                        @foreach($images as $image)
                            @php $d = $image['display']; $in = 'fields['.implode('][', explode('.', $image['path'])).']'; @endphp
                            <div class="editor-field image-field" data-path="{{ $image['path'] }}">
                                <label class="editor-label">{{ $image['label'] }}</label>
                                <div class="image-field-row">
                                    <div class="image-field-preview">
                                        @if($image['url'])
                                            <img src="{{ $image['url'] }}" alt="" data-preview-img>
                                        @else
                                            <span class="muted" style="font-size:12px;">No image</span>
                                        @endif
                                    </div>
                                    <div class="image-field-controls">
                                        <label class="upload-btn">
                                            <input type="file" class="image-upload" accept="image/png,image/jpeg,image/svg+xml,image/webp,image/gif">
                                            <span>Replace image…</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="img-settings">
                                    @if($image['sizable'])
                                        <div class="setting">
                                            <span class="setting-label">Size</span>
                                            <input type="range" min="10" max="100" step="5" name="{{ $in }}[display][width]" value="{{ $d['width'] }}" data-setting="width" data-unit="%">
                                            <output>{{ $d['width'] }}%</output>
                                        </div>
                                        <div class="setting">
                                            <span class="setting-label">Align</span>
                                            <div class="seg" role="radiogroup" aria-label="Alignment">
                                                @foreach(['left' => 'Left', 'center' => 'Centre', 'right' => 'Right'] as $val => $lbl)
                                                    <label><input type="radio" name="{{ $in }}[display][align]" value="{{ $val }}" data-setting="align" @checked($d['align'] === $val)><span>{{ $lbl }}</span></label>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="setting">
                                            <span class="setting-label">Corners</span>
                                            <input type="range" min="0" max="48" step="1" name="{{ $in }}[display][radius]" value="{{ $d['radius'] }}" data-setting="radius" data-unit="px">
                                            <output>{{ $d['radius'] }}px</output>
                                        </div>
                                        <div class="setting">
                                            <span class="setting-label">Border</span>
                                            <input type="range" min="0" max="12" step="1" name="{{ $in }}[display][border]" value="{{ $d['border'] }}" data-setting="border" data-unit="px">
                                            <output>{{ $d['border'] }}px</output>
                                            <input type="color" class="setting-color" name="{{ $in }}[display][border_color]" value="{{ $d['border_color'] }}" data-setting="border_color" title="Border colour">
                                        </div>
                                        <div class="setting">
                                            <span class="setting-label">Shadow</span>
                                            <select class="form-control setting-select" name="{{ $in }}[display][shadow]" data-setting="shadow">
                                                @foreach(['none' => 'None', 'soft' => 'Soft', 'strong' => 'Strong', 'lifted' => 'Lifted'] as $val => $lbl)
                                                    <option value="{{ $val }}" @selected($d['shadow'] === $val)>{{ $lbl }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div class="setting">
                                        <span class="setting-label">Opacity</span>
                                        <input type="range" min="0" max="100" name="{{ $in }}[opacity]" value="{{ $image['opacity'] }}" data-setting="opacity" data-unit="%">
                                        <output>{{ $image['opacity'] }}%</output>
                                    </div>
                                    @if($image['sizable'])
                                        <button type="button" class="rt-pop-link setting-reset" data-image-reset>Reset to defaults</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>

            <section class="editor-pane card">
                <div class="editor-pane-head flex-between">
                    <span>Live preview</span>
                    <span id="sync-status" class="sync-status" data-state="synced">Synced</span>
                </div>
                <div class="preview-frame">
                    <div class="preview-inner" id="preview-inner">@include('learning.blocks._dispatch', ['block' => $blockObject])</div>
                </div>
            </section>
        </div>

        <div class="editor-footer">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('admin.exams.learning.module', $module['id']) }}" class="btn btn-light">Cancel</a>
            <span class="muted" style="font-size:12.5px;">Ctrl/⌘ + S to save</span>
        </div>
    </form>
    </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
<style>
    .editor-head { margin-bottom: 20px; }
    .editor-split {
        display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 20px; align-items: stretch;
    }
    @media (max-width: 900px) { .editor-split { grid-template-columns: 1fr; } }
    .editor-pane { display: flex; flex-direction: column; overflow: hidden; }
    .editor-pane:hover { transform: none; }
    .editor-pane-head {
        padding: 13px 18px; border-bottom: 1px solid #eef1f5;
        font-size: 13px; font-weight: 700; color: #1e293b;
    }
    .editor-fields { padding: 18px; overflow-y: auto; overflow-x: hidden; max-height: calc(100vh - 360px); }
    .editor-field { margin-bottom: 18px; min-width: 0; }
    .editor-field:last-child { margin-bottom: 0; }
    .editor-images-divider { margin: 26px 0 16px; padding-top: 20px; border-top: 1px solid #eef1f5; }
    .editor-fields > .editor-images-divider:first-child { margin-top: 0; padding-top: 0; border-top: 0; }
    .editor-label {
        display: block; font-size: 11.5px; font-weight: 700; color: #475569;
        text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px;
    }

    /* ── Formatting toolbar ─────────────────────────────────────────────── */
    .rt-toolbar {
        display: flex; flex-wrap: wrap; gap: 4px 6px; align-items: center;
        padding: 8px 12px; border-bottom: 1px solid #eef1f5; background: #fafbfc;
        transition: opacity .15s ease;
    }
    .rt-toolbar[data-disabled="1"] .rt-group { opacity: .45; }
    .rt-group { display: flex; align-items: center; gap: 2px; padding-right: 6px; border-right: 1px solid #e6e9ef; }
    .rt-group:last-of-type { border-right: 0; }
    .rt-btn {
        width: 30px; height: 30px; border: 0; border-radius: 7px; background: transparent; color: #334155;
        display: inline-flex; align-items: center; justify-content: center; cursor: pointer; position: relative;
        transition: background .12s ease, color .12s ease;
    }
    .rt-btn svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .rt-btn:hover { background: #eef1f5; }
    .rt-btn.is-active { background: rgba(153,6,10,.1); color: var(--brand); }
    .rt-btn-color svg { margin-top: -4px; }
    .rt-swatch-bar { position: absolute; left: 7px; right: 7px; bottom: 5px; height: 3px; border-radius: 2px; background: #99060a; }
    .rt-select {
        height: 30px; border: 1px solid #e2e8f0; border-radius: 7px; background: #fff;
        font-size: 12.5px; font-weight: 600; color: #334155; padding: 0 6px; cursor: pointer;
    }
    .rt-pop-wrap { position: relative; }
    .rt-pop {
        position: absolute; top: 36px; left: 0; z-index: 30; width: 200px;
        background: #fff; border: 1px solid #e6e9ef; border-radius: 12px; padding: 12px;
        box-shadow: 0 12px 32px rgba(16,24,40,.16);
    }
    .rt-pop-title { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
    .rt-swatches { display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px; }
    .rt-swatch {
        width: 24px; height: 24px; border-radius: 6px; border: 1px solid rgba(0,0,0,.12); cursor: pointer; padding: 0;
        transition: transform .1s ease;
    }
    .rt-swatch:hover { transform: scale(1.12); }
    .rt-swatch-custom {
        background: conic-gradient(red, yellow, lime, aqua, blue, magenta, red); overflow: hidden; position: relative;
    }
    .rt-swatch-custom input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .rt-pop-link { margin-top: 10px; border: 0; background: none; color: #64748b; font-size: 12px; font-weight: 600; cursor: pointer; padding: 0; }
    .rt-pop-link:hover { color: var(--brand); }
    .rt-linkbar[hidden] { display: none; }
    .rt-linkbar { flex-basis: 100%; display: flex; align-items: center; gap: 8px; padding-top: 6px; }
    .rt-linkbar .form-control { padding: 6px 10px; font-size: 13px; }
    .rt-check { display: flex; align-items: center; gap: 5px; font-size: 12px; color: #475569; white-space: nowrap; }

    .editable-field {
        border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px 12px;
        font-size: 14.5px; line-height: 1.65; color: #1e293b; background: #fff;
        min-height: 46px; transition: border-color .15s ease, box-shadow .15s ease;
        overflow-wrap: anywhere; word-break: break-word; overflow-x: hidden;
        max-width: 100%;
    }
    .editable-field :where(p, ol, ul, li, h1, h2, h3, h4, h5, blockquote, div, span, a, strong, em) {
        max-width: 100%; overflow-wrap: anywhere; word-break: break-word;
    }
    .editable-field ol, .editable-field ul { padding-left: 22px; }
    .editable-field p { margin: 0 0 10px; }
    .editable-field p:last-child, .editable-field li:last-child { margin-bottom: 0; }
    .editable-field h2 { font-size: 20px; margin: 6px 0; }
    .editable-field h3 { font-size: 17px; margin: 6px 0; }
    .editable-field h4 { font-size: 14px; margin: 6px 0; text-transform: uppercase; letter-spacing: .4px; color: var(--brand); }
    .editable-field blockquote { border-left: 3px solid var(--brand); padding: 4px 12px; margin: 6px 0; color: #475569; font-style: italic; }
    .editable-field a { color: var(--brand); text-decoration: underline; }
    .editable-field hr { border: 0; border-top: 1px solid #e2e8f0; margin: 10px 0; }
    .editable-field:focus {
        outline: none; border-color: var(--brand);
        box-shadow: 0 0 0 3px rgba(153,6,10,.12);
    }
    .editable-field:empty::before { content: attr(data-placeholder); color: #94a3b8; }

    /* ── Image settings ─────────────────────────────────────────────────── */
    .image-field { padding: 14px; border: 1px solid #eef1f5; border-radius: 12px; background: #fcfcfd; }
    .image-field-row { display: flex; gap: 16px; align-items: center; }
    .image-field-preview {
        width: 132px; height: 92px; min-width: 132px; border-radius: 10px;
        border: 1.5px dashed #cbd5e1; background: #f8fafc;
        display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 6px;
    }
    .image-field-preview img { width: 100%; height: 100%; object-fit: contain; }
    .image-field-controls { flex: 1; }
    .upload-btn {
        display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
        padding: 8px 14px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff;
        font-size: 13px; font-weight: 600; color: #334155; transition: border-color .15s ease;
    }
    .upload-btn:hover { border-color: var(--brand); color: var(--brand); }
    .upload-btn input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .img-settings { display: grid; gap: 10px; margin-top: 14px; }
    .setting { display: flex; align-items: center; gap: 10px; }
    .setting-label { width: 64px; font-size: 12px; font-weight: 700; color: #475569; }
    .setting input[type="range"] { flex: 1; accent-color: var(--brand); }
    .setting output { width: 44px; text-align: right; font-size: 12px; font-weight: 600; color: #64748b; font-variant-numeric: tabular-nums; }
    .setting-color { width: 32px; height: 26px; padding: 0; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; cursor: pointer; }
    .setting-select { flex: 1; padding: 6px 10px; font-size: 13px; }
    .seg { display: inline-flex; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; }
    .seg label { position: relative; }
    .seg input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
    .seg span { display: block; padding: 5px 12px; font-size: 12px; font-weight: 600; color: #475569; border-right: 1px solid #e2e8f0; }
    .seg label:last-child span { border-right: 0; }
    .seg input:checked + span { background: var(--brand); color: #fff; }
    .seg input:focus-visible + span { box-shadow: inset 0 0 0 2px rgba(153,6,10,.35); }
    .setting-reset { justify-self: start; margin-top: 2px; }

    .preview-frame { background: #f0f2f5; padding: 22px; flex: 1; overflow-y: auto; max-height: calc(100vh - 320px); }
    .preview-inner {
        background: #fff; border-radius: 12px; padding: 26px 30px;
        box-shadow: 0 1px 4px rgba(16,24,40,.08);
        max-width: 760px; margin: 0 auto;
    }
    .preview-inner .block { margin-bottom: 26px; }
    .sync-status { font-size: 11.5px; font-weight: 700; }
    .sync-status[data-state="synced"] { color: #059669; }
    .sync-status[data-state="syncing"] { color: #d97706; }
    .sync-status[data-state="error"] { color: #dc2626; }
    .editor-footer { margin-top: 20px; display: flex; align-items: center; gap: 16px; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('block-editor-form');
        if (!form) return;

        const preview = document.getElementById('preview-inner');
        const status = document.getElementById('sync-status');
        const csrf = form.querySelector('input[name="_token"]').value;
        const editables = [...form.querySelectorAll('.editable-field')];
        let timer = null;

        const setStatus = (text, state) => {
            status.textContent = text;
            status.dataset.state = state;
        };

        // Copy every rich-text field into its hidden input, then post the whole form.
        const collect = () => {
            editables.forEach(editable => {
                editable.closest('.editor-field').querySelector('[data-text-input]').value = editable.innerHTML;
            });
            return new FormData(form);
        };

        const markPreviewVisible = () => {
            preview.querySelectorAll('[data-animate="1"]').forEach(el => el.classList.add('is-visible'));
        };

        const bindPreviewInteractions = () => {
            preview.querySelectorAll('.flip-card').forEach(card => {
                if (card.dataset.bound) return;
                card.dataset.bound = '1';
                card.addEventListener('click', () => card.classList.toggle('flipped'));
            });

            preview.querySelectorAll('[data-process]').forEach(proc => {
                if (proc.dataset.bound) return;
                proc.dataset.bound = '1';
                const steps = proc.querySelectorAll('.process-step');
                const dots = proc.querySelectorAll('.process-dots span');
                let cur = 0;
                const show = i => {
                    cur = Math.max(0, Math.min(steps.length - 1, i));
                    steps.forEach((s, idx) => s.classList.toggle('active', idx === cur));
                    dots.forEach((d, idx) => d.classList.toggle('active', idx === cur));
                };
                proc.querySelector('[data-process-prev]')?.addEventListener('click', () => show(cur - 1));
                proc.querySelector('[data-process-next]')?.addEventListener('click', () => show(cur + 1));
            });
        };

        const sync = async () => {
            setStatus('Syncing…', 'syncing');
            try {
                const res = await fetch(form.dataset.previewUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'text/html' },
                    body: collect(),
                });
                if (!res.ok) throw new Error('bad response');
                preview.innerHTML = await res.text();
                markPreviewVisible();
                bindPreviewInteractions();
                setStatus('Synced', 'synced');
            } catch (e) {
                setStatus('Sync failed', 'error');
            }
        };
        const scheduleSync = (delay = 220) => {
            clearTimeout(timer);
            timer = setTimeout(sync, delay);
        };

        form.addEventListener('submit', collect);
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                collect();
                form.submit();
            }
        });

        // ── Rich text editing ───────────────────────────────────────────────
        const toolbar = document.getElementById('rt-toolbar');
        const ALLOWED = new Set(['P','BR','STRONG','B','EM','I','U','S','STRIKE','DEL','SUB','SUP','SPAN','DIV','H2','H3','H4','UL','OL','LI','A','BLOCKQUOTE','HR']);
        const DROP = 'script,style,meta,link,title,xml,iframe,object,embed,svg,template';
        let active = null;
        let savedRange = null;

        // Tidy pasted HTML (Word, Google Docs, web pages): keep structure and
        // basic emphasis, drop classes/inline styles/unknown tags.
        const cleanPaste = (html) => {
            const tpl = document.createElement('template');
            tpl.innerHTML = html;
            tpl.content.querySelectorAll(DROP).forEach(n => n.remove());
            const walk = (node) => {
                [...node.childNodes].forEach(child => {
                    if (child.nodeType === Node.COMMENT_NODE) { child.remove(); return; }
                    if (child.nodeType !== Node.ELEMENT_NODE) return;
                    walk(child);
                    if (!ALLOWED.has(child.tagName)) { child.replaceWith(...child.childNodes); return; }
                    [...child.attributes].forEach(a => { if (a.name !== 'href') child.removeAttribute(a.name); });
                });
            };
            walk(tpl.content);
            return tpl.innerHTML;
        };

        const saveSelection = () => {
            const sel = window.getSelection();
            if (sel.rangeCount && active && active.contains(sel.anchorNode)) savedRange = sel.getRangeAt(0).cloneRange();
        };
        const restoreSelection = () => {
            if (!active) return false;
            active.focus();
            if (savedRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(savedRange);
            }
            return true;
        };
        const exec = (cmd, value = null, css = false) => {
            if (!restoreSelection()) return;
            document.execCommand('styleWithCSS', false, css);
            document.execCommand(cmd, false, value);
            afterEdit();
        };
        const afterEdit = () => {
            if (!active) return;
            // Normalise links the toolbar created
            active.querySelectorAll('a[target="_blank"]').forEach(a => a.rel = 'noopener noreferrer');
            saveSelection();
            refreshState();
            scheduleSync();
        };

        const refreshState = () => {
            if (!toolbar) return;
            toolbar.dataset.disabled = active ? '0' : '1';
            toolbar.querySelectorAll('[data-state]').forEach(btn => {
                let on = false;
                try { on = active && document.queryCommandState(btn.dataset.cmd); } catch (e) {}
                btn.classList.toggle('is-active', !!on);
            });
            const block = toolbar.querySelector('[data-rt-block]');
            if (block && active) {
                let v = '';
                try { v = (document.queryCommandValue('formatBlock') || '').toLowerCase(); } catch (e) {}
                block.value = ['h2','h3','h4','blockquote'].includes(v) ? v : 'p';
            }
        };

        editables.forEach(editable => {
            editable.innerHTML = editable.closest('.editor-field').querySelector('[data-text-input]').value;

            editable.addEventListener('focus', () => { active = editable; refreshState(); });
            editable.addEventListener('input', () => { saveSelection(); scheduleSync(); });
            editable.addEventListener('keyup', () => { saveSelection(); refreshState(); });
            editable.addEventListener('mouseup', () => { saveSelection(); refreshState(); });
            editable.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openLinkBar(); }
            });
            editable.addEventListener('paste', (e) => {
                const html = e.clipboardData.getData('text/html');
                const text = e.clipboardData.getData('text/plain');
                e.preventDefault();
                document.execCommand('styleWithCSS', false, false);
                html ? document.execCommand('insertHTML', false, cleanPaste(html))
                     : document.execCommand('insertText', false, text);
            });
        });

        if (toolbar) {
            // Keep the text selection when pressing toolbar buttons
            toolbar.addEventListener('mousedown', (e) => {
                if (e.target.closest('button') && !e.target.closest('.rt-linkbar')) e.preventDefault();
            });

            toolbar.querySelectorAll('[data-cmd]').forEach(btn => {
                btn.addEventListener('click', () => exec(btn.dataset.cmd));
            });

            toolbar.querySelector('[data-rt-block]').addEventListener('change', (e) => {
                exec('formatBlock', '<' + e.target.value + '>');
            });

            toolbar.querySelector('[data-rt-size]').addEventListener('change', (e) => {
                const size = e.target.value;
                e.target.value = '';
                if (!size || !restoreSelection()) return;
                // execCommand only knows size 1–7, so mark the selection with
                // size 7 and swap those <font> tags for real pixel sizes.
                document.execCommand('styleWithCSS', false, false);
                document.execCommand('fontSize', false, '7');
                active.querySelectorAll('font[size="7"]').forEach(font => {
                    const span = document.createElement('span');
                    if (size !== 'normal') span.style.fontSize = size;
                    font.querySelectorAll('[style]').forEach(n => n.style.fontSize = '');
                    span.append(...font.childNodes);
                    font.replaceWith(span);
                });
                afterEdit();
            });

            // Colour popovers
            const closePops = () => toolbar.querySelectorAll('[data-pop]').forEach(p => p.hidden = true);
            toolbar.querySelectorAll('[data-rt-pop]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const pop = toolbar.querySelector('[data-pop="' + btn.dataset.rtPop + '"]');
                    const wasHidden = pop.hidden;
                    closePops();
                    pop.hidden = !wasHidden;
                });
            });
            document.addEventListener('click', (e) => { if (!e.target.closest('.rt-pop-wrap')) closePops(); });

            const colorBar = toolbar.querySelector('[data-color-bar]');
            const applyFore = (hex) => {
                exec('foreColor', hex === 'inherit' ? '#1e293b' : hex, true);
                if (hex !== 'inherit' && colorBar) colorBar.style.background = hex;
                closePops();
            };
            toolbar.querySelectorAll('[data-fore]').forEach(b => b.addEventListener('click', () => applyFore(b.dataset.fore)));
            toolbar.querySelector('[data-fore-custom]').addEventListener('change', (e) => applyFore(e.target.value));
            toolbar.querySelectorAll('[data-hilite]').forEach(b => b.addEventListener('click', () => {
                exec('hiliteColor', b.dataset.hilite, true);
                closePops();
            }));

            toolbar.querySelector('[data-rt-clear]').addEventListener('click', () => {
                exec('removeFormat');
                exec('formatBlock', '<p>');
            });

            // Links
            const linkbar = toolbar.querySelector('[data-linkbar]');
            const linkUrl = toolbar.querySelector('[data-link-url]');
            const linkBlank = toolbar.querySelector('[data-link-blank]');
            function openLinkBar() {
                if (!active) return;
                saveSelection();
                const a = window.getSelection().anchorNode?.parentElement?.closest('a');
                linkUrl.value = a ? a.getAttribute('href') : '';
                linkBlank.checked = a ? a.target === '_blank' : true;
                linkbar.hidden = false;
                linkUrl.focus();
            }
            const applyLink = () => {
                const url = linkUrl.value.trim();
                if (!url) return;
                if (!/^(https?:\/\/|mailto:|tel:|\/|#)/i.test(url)) {
                    linkUrl.setCustomValidity('Start with https://, mailto: or tel:');
                    linkUrl.reportValidity();
                    return;
                }
                exec('createLink', url);
                const sel = window.getSelection();
                const a = sel.anchorNode?.parentElement?.closest('a') || active.querySelector('a[href="' + CSS.escape(url) + '"]');
                if (a) {
                    if (linkBlank.checked) { a.target = '_blank'; a.rel = 'noopener noreferrer'; }
                    else { a.removeAttribute('target'); a.removeAttribute('rel'); }
                }
                linkbar.hidden = true;
                afterEdit();
            };
            toolbar.querySelector('[data-rt-link]').addEventListener('click', openLinkBar);
            toolbar.querySelector('[data-link-apply]').addEventListener('click', applyLink);
            toolbar.querySelector('[data-link-remove]').addEventListener('click', () => { exec('unlink'); linkbar.hidden = true; });
            linkUrl.addEventListener('input', () => linkUrl.setCustomValidity(''));
            linkUrl.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); applyLink(); }
                if (e.key === 'Escape') { linkbar.hidden = true; restoreSelection(); }
            });

            document.addEventListener('selectionchange', () => {
                if (active && active.contains(window.getSelection().anchorNode)) refreshState();
            });
        }

        // ── Image upload ────────────────────────────────────────────────────
        form.querySelectorAll('.image-upload').forEach(input => {
            input.addEventListener('change', async () => {
                if (!input.files.length) return;
                const field = input.closest('.image-field');
                const fd = new FormData();
                fd.append('image', input.files[0]);
                fd.append('path', field.dataset.path);
                setStatus('Uploading…', 'syncing');
                try {
                    const res = await fetch(form.dataset.uploadUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf },
                        body: fd,
                    });
                    if (!res.ok) throw new Error('upload failed');
                    const data = await res.json();
                    const thumb = field.querySelector('[data-preview-img]');
                    if (thumb) {
                        thumb.src = data.url;
                    } else {
                        field.querySelector('.image-field-preview').innerHTML =
                            '<img src="' + data.url + '" alt="" data-preview-img>';
                    }
                    sync();
                } catch (e) {
                    setStatus('Upload failed', 'error');
                } finally {
                    input.value = '';
                }
            });
        });

        // ── Image display settings ──────────────────────────────────────────
        form.querySelectorAll('.image-field').forEach(field => {
            const controls = [...field.querySelectorAll('[data-setting]')];
            const showValue = (input) => {
                const out = input.parentElement.querySelector('output');
                if (out && input.type === 'range') out.textContent = input.value + (input.dataset.unit || '');
            };
            controls.forEach(input => {
                input.addEventListener('input', () => { showValue(input); scheduleSync(120); });
                input.addEventListener('change', () => { showValue(input); scheduleSync(60); });
            });

            field.querySelector('[data-image-reset]')?.addEventListener('click', () => {
                const defaults = { width: 100, align: 'center', radius: 12, border: 0, border_color: '#e2e8f0', shadow: 'none' };
                controls.forEach(input => {
                    const key = input.dataset.setting;
                    if (!(key in defaults)) return;
                    if (input.type === 'radio') input.checked = input.value === defaults[key];
                    else input.value = defaults[key];
                    showValue(input);
                });
                scheduleSync(0);
            });
        });

        markPreviewVisible();
        bindPreviewInteractions();
    })();
</script>
@endpush

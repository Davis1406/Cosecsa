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

@if(empty($fields) && empty($images))
    <div class="alert alert-info">
        This block has no editable text or images ({{ $block->type }} / {{ $block->variant ?? '—' }}).
    </div>
@endif

<form method="POST" action="{{ route('admin.exams.learning.block.update', $block->id) }}"
      data-block-editor
      data-preview-url="{{ route('admin.exams.learning.block.preview', $block->id) }}"
      data-upload-url="{{ route('admin.exams.learning.block.image', $block->id) }}"
      data-editor-url="{{ route('admin.exams.learning.block.editor', $block->id) }}"
      data-list-url="{{ route('admin.exams.learning.block.list-items', $block->id) }}">
    @csrf
    <div class="editor-split">
        <section class="editor-pane card">
            <div class="editor-pane-head">Content <span class="muted" style="font-weight:400;">— type and watch the preview update</span></div>

            @if(!empty($fields))
                {{-- Formatting toolbar — acts on whichever text field has focus --}}
                <div class="rt-toolbar" role="toolbar" aria-label="Text formatting" data-disabled="1">
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
                    @php
                        $isListItem = $block->type === 'list' && preg_match('/^items\.(\d+)(\.|$)/', $field['path'], $listMatch);
                        $listIndex = $isListItem ? (int) $listMatch[1] : null;
                    @endphp
                    <div class="editor-field {{ $isListItem ? 'list-item-field' : '' }}" data-path="{{ $field['path'] }}" @if($isListItem) data-list-index="{{ $listIndex }}" @endif>
                        @if($isListItem)
                            <div class="flex-between list-item-head">
                                <label class="editor-label" style="margin-bottom:0;">{{ $field['label'] }}</label>
                                <button type="button" class="list-item-remove" data-list-remove title="Remove this item">Remove</button>
                            </div>
                        @else
                            <label class="editor-label">{{ $field['label'] }}</label>
                        @endif
                        <div class="editable-field" contenteditable="true" spellcheck="true" data-placeholder="Empty"></div>
                        <input type="hidden" name="fields[{{ implode('][', explode('.', $field['path'])) }}]" value="{{ $field['value'] }}" data-text-input>
                    </div>
                @endforeach

                @if($block->type === 'list')
                    <button type="button" class="btn btn-light btn-sm list-item-add" data-list-add>
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" style="vertical-align:-1px; margin-right:4px;"><path d="M12 5v14M5 12h14"/></svg>
                        Add item
                    </button>
                @endif

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
                <span class="sync-status" data-state="synced">Synced</span>
            </div>
            <div class="preview-frame">
                <div class="preview-inner">@include('learning.blocks._dispatch', ['block' => $block])</div>
            </div>
        </section>
    </div>

    <div class="editor-footer">
        <button type="submit" class="btn btn-primary">Save changes</button>
        @if(!empty($cancelUrl))
            <a href="{{ $cancelUrl }}" class="btn btn-light" data-editor-cancel>Cancel</a>
        @else
            <button type="button" class="btn btn-light" data-editor-cancel>Cancel</button>
        @endif
        <span class="muted" style="font-size:12.5px;">Ctrl/⌘ + S to save</span>
    </div>
</form>
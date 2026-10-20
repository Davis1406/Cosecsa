@extends('layout.app')

{{--
  Wording editor for a coded course page (final design pack). The page is designed in code, so the admin edits the
  WORDING of every heading, paragraph, list item, note and table cell here; the examiner page shows the new wording.
  Topics, pictures and diagrams are part of the page design and are not edited here.
--}}
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

            <div class="cw-split">
            <div class="cw-left">
            <div class="alert alert-info mb-2" style="font-size:13px;">
                <strong>Edit the wording.</strong> Click a block, change the text and press <strong>Save</strong> on it: the page on the right
                updates as you type, and examiners see it once saved. <strong>Reset to original</strong> brings back the page's own wording.
                Topic cards, pictures and animations are part of the design and are not edited here.
            </div>

            <div class="cw-bar">
                <input type="search" id="cwSearch" class="form-control form-control-sm" placeholder="Find wording on this page…" autocomplete="off">
                <div class="cw-tools" role="toolbar" aria-label="Text formatting">
                    <button type="button" class="btn btn-light btn-sm" data-cmd="bold" title="Bold"><b>B</b></button>
                    <button type="button" class="btn btn-light btn-sm" data-cmd="italic" title="Italic"><i>I</i></button>
                    <button type="button" class="btn btn-light btn-sm" data-cmd="link" title="Make a link">Link</button>
                    <button type="button" class="btn btn-light btn-sm" data-cmd="unlink" title="Remove link">Unlink</button>
                </div>
                <span class="muted" id="cwCount" style="font-size:12.5px;">{{ $totalFields }} pieces of wording · {{ $totalEdited }} edited</span>
            </div>

            @foreach($groups as $topic => $g)
                <details class="cw-group" @if($loop->first) open @endif data-topic="{{ $topic }}">
                    <summary>
                        <span class="cw-gt">{{ $topic < 0 ? 'Banner' : 'Topic '.($topic + 1).': '.\Illuminate\Support\Str::limit($g['title'], 70) }}</span>
                        <span class="badge-gray cw-n">{{ count($g['fields']) }}</span>
                        <span class="badge-brand cw-e" @if(! $g['edited']) hidden @endif>{{ $g['edited'] }} edited</span>
                    </summary>
                    <div class="cw-fields">
                        @foreach($g['fields'] as $f)
                            <div class="cw-row" data-key="{{ $f['key'] }}" data-original="{{ e($f['html']) }}" data-search="{{ strtolower($f['text'].' '.$f['kind']) }}">
                                <div class="cw-kind">{{ $f['kind'] }}</div>
                                <div class="cw-main">
                                    <div class="cw-field {{ $f['tag'] === 'h1' || $f['tag'] === 'h2' || $f['tag'] === 'h3' || $f['kind'] === 'Title' ? 'cw-strong' : '' }}" contenteditable="true" spellcheck="true"
                                         data-saved="{{ e($f['current']) }}">{!! $f['current'] !!}</div>
                                    <div class="cw-actions">
                                        <button type="button" class="btn btn-primary btn-sm cw-save" disabled>Save</button>
                                        <button type="button" class="btn btn-light btn-sm cw-reset" @if(! $f['overridden']) hidden @endif>Reset to original</button>
                                        <span class="cw-status muted">@if($f['overridden'])Edited{{ $f['by'] ? ' by '.$f['by'] : '' }}@endif</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endforeach

            @if($stale->count())
                <details class="cw-group">
                    <summary><span class="cw-gt">Edits that no longer match the page</span> <span class="badge-gray cw-n">{{ $stale->count() }}</span></summary>
                    <div class="p-3" style="font-size:13px;">
                        <p class="muted">The page's own wording changed after these edits were made, so they are not shown to examiners any more. They are kept here for reference.</p>
                        @foreach($stale as $s)
                            <div class="cw-row" data-key="{{ $s['key'] }}">
                                <div class="cw-kind">Not shown</div>
                                <div class="cw-main">
                                    <div class="muted" style="font-size:12px;">Was: {{ $s['original'] }}</div>
                                    <div>{!! $s['html'] !!}</div>
                                    <div class="cw-actions"><button type="button" class="btn btn-light btn-sm cw-reset">Remove this edit</button></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
            </div>{{-- /cw-left --}}

            <div class="cw-right">
                <div class="cw-pv-head">
                    <span>Live preview</span>
                    <span class="muted">Click wording in the preview to edit it</span>
                </div>
                <iframe id="cwFrame" class="cw-frame" src="{{ route('admin.exams.learning.module.frame', $module['id']) }}" title="Live preview of the page"></iframe>
            </div>
            </div>{{-- /cw-split --}}
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
<style>
    .cw-split { display: flex; gap: 16px; align-items: flex-start; }
    .cw-left { flex: 0 0 46%; max-width: 46%; min-width: 0; height: calc(100vh - 230px); min-height: 480px; overflow-y: auto; padding-right: 6px; }
    .cw-right { flex: 1; min-width: 0; position: sticky; top: 8px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; overflow: hidden; }
    .cw-pv-head { display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; font-size: 13px; font-weight: 700; border-bottom: 1px solid #eef0f3; background: #f8fafc; }
    .cw-pv-head .muted { font-weight: 400; font-size: 12px; }
    .cw-frame { display: block; width: 100%; height: calc(100vh - 275px); min-height: 440px; border: 0; background: #f6f5ef; }
    @media (max-width: 1100px) { .cw-split { flex-direction: column; } .cw-left { flex: none; max-width: none; width: 100%; height: auto; overflow: visible; } .cw-right { position: static; width: 100%; } .cw-frame { height: 70vh; } }
    .cw-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; position: sticky; top: 0; z-index: 20; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 8px 10px; margin-bottom: 10px; }
    .cw-bar input { max-width: 200px; }
    .cw-tools { display: flex; gap: 4px; }
    .cw-group { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 10px; }
    .cw-group > summary { cursor: pointer; padding: 11px 14px; display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14.5px; list-style: none; }
    .cw-group > summary::-webkit-details-marker { display: none; }
    .cw-group > summary::before { content: '▸'; color: #94a3b8; transition: transform .15s; }
    .cw-group[open] > summary::before { transform: rotate(90deg); }
    .cw-gt { flex: 1; }
    .cw-fields { border-top: 1px solid #eef0f3; }
    .cw-row { display: flex; flex-direction: column; gap: 4px; padding: 10px 14px; border-bottom: 1px solid #f3f4f6; }
    .cw-row.active { background: #fffdf5; box-shadow: inset 3px 0 0 var(--brand, #99060A); }
    .cw-row:last-child { border-bottom: 0; }
    .cw-row[hidden] { display: none; }
    .cw-kind { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; }
    .cw-main { flex: 1; min-width: 0; }
    .cw-field { border: 1px solid #dbe1e8; border-radius: 8px; padding: 7px 10px; font-size: 14.5px; line-height: 1.5; background: #fff; min-height: 34px; outline: none; }
    .cw-field:focus { border-color: var(--brand, #99060A); box-shadow: 0 0 0 3px rgba(153,6,10,.1); }
    .cw-field.cw-strong { font-weight: 700; }
    .cw-field.dirty { background: #fffbeb; border-color: #f59e0b; }
    .cw-actions { display: flex; align-items: center; gap: 8px; margin-top: 6px; min-height: 28px; }
    .cw-status { font-size: 12px; }
    .cw-status.ok { color: #15803d; }
    .cw-status.err { color: #b91c1c; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var saveUrl = @json(route('admin.exams.learning.text.save', $module['id']));
    var resetUrl = @json(route('admin.exams.learning.text.reset', $module['id']));
    var token = @json(csrf_token());
    var $ = function (s, r) { return (r || document).querySelector(s); };
    var all = function (s, r) { return [].slice.call((r || document).querySelectorAll(s)); };
    var total = {{ $totalFields }}, edited = {{ $totalEdited }};

    function post(url, body) {
        return fetch(url, { method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
            body: JSON.stringify(body) }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); });
    }
    function norm(h) { return String(h || '').replace(/\s+/g, ' ').replace(/&nbsp;/g, ' ').trim(); }
    function esc(h) { var d = document.createElement('div'); d.textContent = h; return d.innerHTML; }
    function unesc(h) { var d = document.createElement('textarea'); d.innerHTML = h; return d.value; }
    // The browser's own serialisation of some HTML (entities, tags), so the page's wording and what the field holds compare equal.
    function canon(h) { var d = document.createElement('div'); d.innerHTML = h; return d.innerHTML; }
    function count() { $('#cwCount').textContent = total + ' pieces of wording · ' + edited + ' edited'; }
    function groupBadge(row) {
        var g = row.closest('.cw-group'), n = all('.cw-reset:not([hidden])', g).filter(function (b) { return b.closest('.cw-fields'); }).length, b = $('.cw-e', g);
        if (b) { b.textContent = n + ' edited'; b.hidden = n === 0; }
    }

    // ----- live preview: the page on the right shows what is being typed -----
    var frame = $('#cwFrame');
    function toFrame(msg) { try { frame.contentWindow.postMessage(msg, location.origin); } catch (e) {} }
    var activeRow = null;
    function setActive(row) { if (activeRow) activeRow.classList.remove('active'); activeRow = row; if (row) row.classList.add('active'); }
    document.addEventListener('focusin', function (e) {
        var f = e.target; if (!f.classList || !f.classList.contains('cw-field')) return;
        var row = f.closest('.cw-row'); setActive(row); toFrame({ type: 'cw-focus', key: row.dataset.key });
    });
    // clicking wording in the preview jumps to its field
    window.addEventListener('message', function (e) {
        if (e.origin !== location.origin || !e.data || e.data.type !== 'cw-click') return;
        var row = document.querySelector('.cw-row[data-key="' + e.data.key + '"]'); if (!row) return;
        var g = row.closest('.cw-group'); if (g) { g.hidden = false; g.open = true; }
        row.hidden = false; row.scrollIntoView({ block: 'center', behavior: 'smooth' });
        var f = $('.cw-field', row); if (f) f.focus();
    });

    // typing: Enter is a line break only with Shift; paste is plain text
    document.addEventListener('keydown', function (e) {
        if (e.target.classList && e.target.classList.contains('cw-field') && e.key === 'Enter') {
            e.preventDefault(); if (e.shiftKey) document.execCommand('insertHTML', false, '<br>');
        }
    });
    document.addEventListener('paste', function (e) {
        if (e.target.classList && e.target.classList.contains('cw-field')) {
            e.preventDefault(); document.execCommand('insertText', false, (e.clipboardData || window.clipboardData).getData('text/plain'));
        }
    });
    document.addEventListener('input', function (e) {
        var f = e.target; if (!f.classList || !f.classList.contains('cw-field')) return;
        toFrame({ type: 'cw-set', key: f.closest('.cw-row').dataset.key, html: f.innerHTML });
        var dirty = norm(f.innerHTML) !== norm(canon(unesc(f.dataset.saved)));
        f.classList.toggle('dirty', dirty);
        var row = f.closest('.cw-row'); $('.cw-save', row).disabled = !dirty;
        var st = $('.cw-status', row); st.className = 'cw-status muted'; st.textContent = dirty ? 'Not saved yet' : (st.dataset.was || '');
    });

    // formatting toolbar (acts on the field being edited)
    all('[data-cmd]').forEach(function (b) {
        b.addEventListener('mousedown', function (e) { e.preventDefault(); });   // keep the selection in the field
        b.addEventListener('click', function () {
            var a = document.activeElement; if (!a || !a.classList || !a.classList.contains('cw-field')) return;
            var c = b.dataset.cmd;
            if (c === 'link') { var u = window.prompt('Link address (https://…)'); if (u) document.execCommand('createLink', false, u); }
            else document.execCommand(c, false, null);
            a.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    document.addEventListener('click', function (e) {
        var row = e.target.closest('.cw-row'); if (!row) return;
        var st = $('.cw-status', row), f = $('.cw-field', row);
        if (e.target.classList.contains('cw-save')) {
            var btn = e.target; btn.disabled = true; st.className = 'cw-status muted'; st.textContent = 'Saving…';
            post(saveUrl, { key: row.dataset.key, original: canon(unesc(row.dataset.original)), html: f.innerHTML }).then(function (r) {
                if (!r.ok) { btn.disabled = false; st.className = 'cw-status err'; st.textContent = (r.j && r.j.message) || 'Could not save.'; return; }
                f.innerHTML = r.j.html; f.dataset.saved = esc(r.j.html); f.classList.remove('dirty');
                var rb = $('.cw-reset', row), was = rb && !rb.hidden;
                if (rb) rb.hidden = !r.j.overridden;
                if (r.j.overridden && !was) edited++; if (!r.j.overridden && was) edited--;
                st.dataset.was = r.j.overridden ? 'Edited just now' : ''; st.className = 'cw-status ok'; st.textContent = r.j.overridden ? 'Saved. Examiners now see this.' : 'Same as the original, nothing kept.';
                groupBadge(row); count();
            }).catch(function () { btn.disabled = false; st.className = 'cw-status err'; st.textContent = 'Could not save. Check your connection.'; });
        }
        if (e.target.classList.contains('cw-reset')) {
            if (!f) { post(resetUrl, { key: row.dataset.key }).then(function () { row.remove(); }); return; }
            post(resetUrl, { key: row.dataset.key }).then(function (r) {
                if (!r.ok) { st.className = 'cw-status err'; st.textContent = 'Could not reset.'; return; }
                f.innerHTML = unesc(row.dataset.original); f.dataset.saved = row.dataset.original; f.classList.remove('dirty');
                toFrame({ type: 'cw-set', key: row.dataset.key, html: f.innerHTML });
                $('.cw-save', row).disabled = true; e.target.hidden = true; edited--; st.dataset.was = ''; st.className = 'cw-status ok'; st.textContent = 'Back to the original wording.';
                groupBadge(row); count();
            });
        }
    });

    // search
    $('#cwSearch').addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        all('.cw-group').forEach(function (g) {
            var any = false;
            all('.cw-row', g).forEach(function (r) {
                var hit = !q || (r.dataset.search || '').indexOf(q) > -1;
                r.hidden = !hit; if (hit) any = true;
            });
            g.hidden = !any; if (q && any) g.open = true;
        });
    });

    window.addEventListener('beforeunload', function (e) {
        if (all('.cw-field.dirty').length) { e.preventDefault(); e.returnValue = ''; }
    });
})();
</script>
@endpush

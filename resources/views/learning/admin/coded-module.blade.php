@extends('layout.app')

{{--
  Page editor for a coded course page (final design pack). The page is held as data (App\Support\CoursePage):
  banner, topic cards (icon, colour, title, subtitle) and blocks (paragraphs, lists, tables, pictures, notes, designed blocks).
  Left: the blocks. Right: the live preview of the page as examiners will see it.
--}}
@section('content')
<div class="content-wrapper">
    @include('learning.admin._head', ['title' => $module['title'], 'subtitle' => $course['title']])
    <section class="content">
        <div class="container-fluid lms">
            @include('learning.partials.flash')
            @include('learning.admin._tabs')
            @include('course.partials.icons')

            <div class="cw-top">
                <a href="{{ route('admin.exams.learning.course') }}" class="btn btn-light btn-sm">← All modules</a>
                <a href="{{ route('admin.exams.learning.preview.module', $module['slug']) }}" class="btn btn-light btn-sm" data-lms-preview>Preview as examiner</a>
                <span style="flex:1"></span>
                <span id="cwState" class="muted" style="font-size:12.5px;">@if($updatedBy)Last saved by {{ $updatedBy }}@endif</span>
                <button type="button" class="btn btn-light btn-sm" id="cwRevert" disabled>Discard changes</button>
                <button type="button" class="btn btn-outline btn-sm" id="cwReset" title="Throw away every change and go back to the page as it was first designed">Back to original design</button>
                <button type="button" class="btn btn-primary btn-sm" id="cwSave" disabled>Save page</button>
            </div>

            @if($seeded)
                <div class="alert alert-success" style="font-size:13px;">This page has been opened for editing for the first time. Nothing has changed for examiners: you are looking at its original design as editable blocks.</div>
            @endif

            <div class="cw-split">
                <div class="cw-left" id="cwPanel"><div class="muted p-3">Loading…</div></div>
                <div class="cw-right">
                    <div class="cw-pv-head"><span>Live preview</span><span class="muted">Click anything in the preview to edit it</span></div>
                    <iframe id="cwFrame" class="cw-frame" title="Live preview of the page"></iframe>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- video picker: the videos already on the course, or a new upload --}}
<div class="modal fade" id="cwVideoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add a video</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <div class="cw-tabs"><button type="button" class="on" data-vtab="list">Choose from the video list</button><button type="button" data-vtab="upload">Upload a new video</button></div>
                <div id="cwVList"><div class="muted p-3">Loading the videos…</div></div>
                <div id="cwVUp" style="display:none">
                    <div class="cw-lab">Video file (MP4, MOV, WebM or M4V, up to 150 MB)</div>
                    <input type="file" id="cwVFile" accept="video/mp4,video/quicktime,video/webm,video/x-m4v,.mp4,.mov,.webm,.m4v" class="form-control-file">
                    <div class="cw-lab">Name (shown under the video)</div>
                    <input type="text" id="cwVLabel" class="form-control form-control-sm" maxlength="255" placeholder="e.g. Example: a nervous candidate">
                    <div class="progress mt-3" style="height:10px;display:none" id="cwVProg"><div class="progress-bar" style="width:0"></div></div>
                    <div class="cw-note mt-2" id="cwVMsg"></div>
                    <button type="button" class="btn btn-primary btn-sm mt-3" id="cwVGo">Upload and use this video</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- icon picker --}}
<div class="modal fade" id="cwIconModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Choose an icon <small class="text-muted" id="cwIconCount"></small></h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <input type="search" id="cwIconSearch" class="form-control form-control-sm mb-2" placeholder="Search icons (heart, document, clock, medical...)" autocomplete="off">
                <div class="cw-icons" id="cwIcons"></div>
                <div class="cw-note" id="cwIconNone" style="display:none;padding:12px 0">No icon has that name.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
<style>
    .cw-top { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 12px; }
    .cw-split { display: flex; gap: 16px; align-items: flex-start; }
    .cw-left { flex: 0 0 48%; max-width: 48%; min-width: 0; height: calc(100vh - 250px); min-height: 480px; overflow-y: auto; padding-right: 6px; }
    .cw-right { flex: 1; min-width: 0; position: sticky; top: 8px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; overflow: hidden; }
    .cw-pv-head { display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; font-size: 13px; font-weight: 700; border-bottom: 1px solid #eef0f3; background: #f8fafc; }
    .cw-pv-head .muted { font-weight: 400; font-size: 12px; }
    .cw-frame { display: block; width: 100%; height: calc(100vh - 295px); min-height: 440px; border: 0; background: #f6f5ef; }
    @media (max-width: 1100px) { .cw-split { flex-direction: column; } .cw-left { flex: none; max-width: none; width: 100%; height: auto; overflow: visible; } .cw-right { position: static; width: 100%; } .cw-frame { height: 70vh; } }

    .cw-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 10px; }
    .cw-card > summary { cursor: pointer; padding: 10px 12px; display: flex; align-items: center; gap: 10px; list-style: none; }
    .cw-card > summary::-webkit-details-marker { display: none; }
    .cw-card > summary::before { content: '▸'; color: #94a3b8; transition: transform .15s; }
    .cw-card[open] > summary::before { transform: rotate(90deg); }
    .cw-card-b { padding: 4px 12px 12px; border-top: 1px solid #eef0f3; }
    .cw-tt { flex: 1; font-weight: 700; font-size: 14.5px; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cw-tools { display: flex; gap: 4px; flex: none; }
    .cw-tools .btn, .cw-mini { padding: 2px 8px; font-size: 12px; line-height: 1.6; }
    .cw-lab { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; margin: 10px 0 3px; }
    .cw-ce { border: 1px solid #dbe1e8; border-radius: 8px; padding: 6px 10px; font-size: 14px; line-height: 1.5; background: #fff; min-height: 32px; outline: none; }
    .cw-ce:focus { border-color: var(--brand, #99060A); box-shadow: 0 0 0 3px rgba(153,6,10,.1); }
    textarea.cw-raw { width: 100%; min-height: 160px; font-family: ui-monospace, Menlo, monospace; font-size: 12px; border: 1px solid #dbe1e8; border-radius: 8px; padding: 8px; }
    .cw-row2 { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .cw-icbtn { width: 44px; height: 44px; border-radius: 10px; border: 1px solid #dbe1e8; background: #fff; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; flex: none; padding: 0; }
    .cw-icbtn svg, .cw-icons svg { width: 24px; height: 24px; fill: none; stroke: #334155; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .cw-icons { display: grid; grid-template-columns: repeat(auto-fill, minmax(54px, 1fr)); gap: 8px; max-height: 56vh; overflow-y: auto; padding: 2px; }
    .cw-icons button { height: 54px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; }
    .cw-icons button:hover, .cw-icons button.on { border-color: var(--brand, #99060A); background: #fff5f5; }
    input[type=color].cw-col { width: 38px; height: 32px; padding: 0; border: 1px solid #dbe1e8; border-radius: 8px; background: #fff; cursor: pointer; }
    .cw-blk { border: 1px solid #e5e7eb; border-radius: 8px; margin-top: 8px; background: #fcfcfd; }
    .cw-blk.active { border-color: var(--brand, #99060A); box-shadow: 0 0 0 2px rgba(153,6,10,.12); }
    .cw-bh { display: flex; align-items: center; gap: 8px; padding: 6px 8px; cursor: pointer; }
    .cw-bt { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #64748b; background: #eef2f7; border-radius: 6px; padding: 2px 7px; flex: none; }
    .cw-bs { flex: 1; font-size: 13px; color: #475569; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cw-bb { padding: 4px 10px 10px; border-top: 1px solid #eef0f3; }
    .cw-add { margin-top: 10px; }
    .cw-grid { overflow-x: auto; margin-top: 6px; }
    .cw-grid table { border-collapse: collapse; }
    .cw-grid td { border: 1px solid #dbe1e8; padding: 0; vertical-align: top; }
    .cw-grid .cw-ce { border: 0; border-radius: 0; min-width: 110px; min-height: 30px; box-shadow: none; font-size: 13px; }
    .cw-grid th { font-size: 11px; color: #94a3b8; font-weight: 600; padding: 2px 4px; text-align: center; white-space: nowrap; }
    .cw-li { display: flex; gap: 6px; align-items: flex-start; margin-bottom: 5px; }
    .cw-li .cw-ce { flex: 1; }
    .cw-thumb { max-width: 220px; max-height: 140px; border-radius: 8px; border: 1px solid #e2e8f0; display: block; margin-bottom: 6px; }
    .cw-note { font-size: 12px; color: #64748b; }
    .cw-vprev { width: 100%; max-width: 360px; border-radius: 8px; background: #000; display: block; margin-bottom: 6px; }
    .cw-tabs { display: flex; gap: 6px; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
    .cw-tabs button { border: 0; background: none; padding: 8px 12px; font-weight: 600; font-size: 13px; color: #64748b; border-bottom: 2px solid transparent; cursor: pointer; }
    .cw-tabs button.on { color: var(--brand, #99060A); border-bottom-color: var(--brand, #99060A); }
    .cw-vgrid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px; max-height: 56vh; overflow-y: auto; padding: 2px; }
    .cw-vcard { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fff; display: flex; flex-direction: column; }
    .cw-vcard video { width: 100%; aspect-ratio: 16/9; background: #000; display: block; }
    .cw-vcard .t { padding: 8px 10px 4px; font-weight: 700; font-size: 13px; line-height: 1.3; }
    .cw-vcard .m { padding: 0 10px 8px; font-size: 11.5px; color: #64748b; }
    .cw-vcard button { margin: auto 10px 10px; }
    .cw-dirty { color: #b45309; font-weight: 700; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var TOKEN = String.fromCharCode(123, 123) + 'img' + String.fromCharCode(125, 125);
    var IMG_BASE = @json(asset('images/course'));
    var ICONS = @json($icons);
    var DEFAULTS = @json($defaults);
    var PALETTE = ['#1d6b48', '#87201f', '#8a6100', '#1f6f8b', '#5a4a9a', '#b3541e', '#2d4f9e'];
    var URLS = {
        save: @json(route('admin.exams.learning.page.save', $module['id'])),
        reset: @json(route('admin.exams.learning.page.reset', $module['id'])),
        preview: @json(route('admin.exams.learning.module.preview', $module['id'])),
        image: @json(route('admin.exams.learning.page.image', $module['id'])),
        videos: @json(route('admin.exams.learning.video-library')),
        videoUpload: @json(route('admin.exams.learning.video-library.upload'))
    };
    var CSRF = @json(csrf_token());
    var S = { model: @json($model), saved: null, dirty: false, openT: {}, openB: {}, focus: null, timer: null, iconFor: 0, videoFor: null, videoCache: null };
    var panel = document.getElementById('cwPanel'), frame = document.getElementById('cwFrame');
    var $ = function (s, r) { return (r || document).querySelector(s); };
    var all = function (s, r) { return [].slice.call((r || document).querySelectorAll(s)); };
    S.saved = JSON.stringify(S.model);

    // ---------- helpers ----------
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
    function attr(s) { return esc(s).replace(/"/g, '&quot;'); }
    function root(html) { var t = document.createElement('template'); t.innerHTML = String(html || '').trim(); return t.content.firstElementChild; }
    function plain(html) { var d = document.createElement('div'); d.innerHTML = html || ''; return (d.textContent || '').replace(/\s+/g, ' ').trim(); }
    function post(url, body, isForm) {
        var opt = { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF } };
        if (isForm) { opt.body = body; } else { opt.headers['Content-Type'] = 'application/json'; opt.body = JSON.stringify(body); }
        return fetch(url, opt).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); });
    }
    function maxBlockNo() {
        var m = 0; S.model.topics.forEach(function (t) { t.blocks.forEach(function (b) { var n = parseInt(String(b.id).slice(1), 10); if (n > m) m = n; }); });
        return m;
    }
    function nextId() { return 'b' + (maxBlockNo() + 1); }
    function setPath(o, path, v) { var p = path.split('.'), last = p.pop(); p.forEach(function (k) { o = o[k]; }); o[last] = v; }
    function dirty() {
        S.dirty = JSON.stringify(S.model) !== S.saved;
        $('#cwSave').disabled = !S.dirty; $('#cwRevert').disabled = !S.dirty;
        if (S.dirty) $('#cwState').innerHTML = '<span class="cw-dirty">Unsaved changes</span>';
    }
    function changed() { dirty(); clearTimeout(S.timer); S.timer = setTimeout(refreshPreview, 450); }

    // ---------- live preview ----------
    function refreshPreview() {
        var st = null;
        try {
            var w = frame.contentWindow;
            if (w && w.document && w.document.querySelector('.cs-course')) {
                st = { y: w.scrollY, open: [].slice.call(w.document.querySelectorAll('.topic.open')).map(function (t) { return t.getAttribute('data-tid'); }) };
            }
        } catch (e) {}
        fetch(URLS.preview, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ model: S.model }) })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                frame.onload = function () {
                    try {
                        var w = frame.contentWindow;
                        if (st) {
                            st.open.forEach(function (tid) { var t = w.document.querySelector('[data-tid="' + tid + '"]'); if (t && !t.classList.contains('open')) t.querySelector('.th').click(); });
                            w.scrollTo(0, st.y);
                        }
                        if (S.focus) { w.postMessage(S.focus, location.origin); S.focus = null; }
                    } catch (e) {}
                };
                frame.srcdoc = html;
            });
    }
    function focusPreview(o) { S.focus = Object.assign({ type: 'cw-focus' }, o); try { frame.contentWindow.postMessage(S.focus, location.origin); } catch (e) {} }
    window.addEventListener('message', function (e) {
        if (e.origin !== location.origin || !e.data || e.data.type !== 'cw-click') return;
        if (e.data.tid) S.openT[e.data.tid] = true;
        if (e.data.bid) S.openB[e.data.bid] = true;
        renderAll();
        var el = e.data.bid ? $('[data-bid="' + e.data.bid + '"]', panel) : $('details[data-tid="' + e.data.tid + '"]', panel);
        if (el) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
    });

    // ---------- block markup builders ----------
    var TEXT_TYPES = { paragraph: 'Paragraph', lead: 'Lead text', heading: 'Heading', note: 'Small note', callout: 'Highlighted note' };
    function newText(type, inner) {
        inner = inner == null ? 'New text' : inner;
        return { paragraph: '<p>' + inner + '</p>', lead: '<p class="cs-lead">' + inner + '</p>', heading: '<h3>' + inner + '</h3>', note: '<p class="note">' + inner + '</p>', callout: '<div class="callout">' + inner + '</div>' }[type];
    }
    var LIST_KINDS = { ticks: ['ul', 'ticks', 'Ticks'], crosses: ['ul', 'crosses', 'Crosses'], pts: ['ul', 'pts', 'Bullets'], nums: ['ol', 'nums', 'Numbered'], steps: ['ol', 'steps', 'Steps'], plainul: ['ul', '', 'Plain bullets'], plainol: ['ol', '', 'Plain numbers'] };
    function listKind(el) {
        var c = el.getAttribute('class') || '', t = el.tagName.toLowerCase();
        for (var k in LIST_KINDS) { if (LIST_KINDS[k][0] === t && LIST_KINDS[k][1] === c) return k; }
        return t === 'ol' ? 'plainol' : 'plainul';
    }
    function buildList(kind, items) {
        var k = LIST_KINDS[kind] || LIST_KINDS.ticks;
        return '<' + k[0] + (k[1] ? ' class="' + k[1] + '"' : '') + '>' + items.map(function (i) { return '<li>' + i + '</li>'; }).join('') + '</' + k[0] + '>';
    }
    function newBlock(type) {
        var html = {
            paragraph: newText('paragraph'), lead: newText('lead'), heading: newText('heading', 'New heading'), note: newText('note'), callout: newText('callout', 'Highlighted note'),
            list: buildList('ticks', ['First point', 'Second point']),
            table: '<div class="scroll cs-card"><table><tr><th></th><th>Column 1</th><th>Column 2</th></tr><tr><td>Row 1</td><td>Cell</td><td>Cell</td></tr></table></div>',
            image: '<img class="pic" alt="" src="' + TOKEN + '/cosecsa-crest.png">'
        }[type];
        return { id: nextId(), type: type, html: html };
    }
    // table helpers: cells stay as they are, thead/tbody wrappers are dropped
    function tableOf(html) { var r = root(html); return { r: r, t: r.tagName === 'TABLE' ? r : r.querySelector('table') }; }
    function tableOut(o) {
        var rows = [].slice.call(o.t.querySelectorAll('tr'));
        [].slice.call(o.t.querySelectorAll('thead,tbody,tfoot')).forEach(function (e) { e.remove(); });
        rows.forEach(function (r) { o.t.appendChild(r); });
        return o.r.outerHTML;
    }
    // wording lines of a designed block: leaf elements holding text
    var INL = ['B', 'STRONG', 'I', 'EM', 'U', 'A', 'BR', 'SUP', 'SUB', 'SPAN'];
    function lines(r) {
        return [].slice.call(r.querySelectorAll('*')).filter(function (e) {
            if (e.closest('svg,script,style,button,select')) return false;
            if (![].slice.call(e.children).every(function (c) { return INL.indexOf(c.tagName) > -1; })) return false;
            return /[A-Za-zÀ-ɏ]/.test(e.textContent) && INL.indexOf(e.tagName) === -1;
        });
    }

    // ---------- videos ----------
    function videoSrc(r) { var v = r && r.querySelector ? r.querySelector('video') : null; return v ? (v.getAttribute('src') || '') : ''; }
    function hasVideo(r) { return !!videoSrc(r); }
    function videoHtml(url, caption) {
        return '<figure class="cs-video"><video controls preload="metadata" playsinline src="' + attr(url) + '"></video><figcaption>' + (caption || '') + '</figcaption></figure>';
    }
    function fmtSize(n) { return n > 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB'; }
    function openVideos(target) {
        S.videoFor = target; showVTab('list'); $('#cwVMsg').textContent = ''; $('#cwVFile').value = ''; $('#cwVLabel').value = '';
        loadVideos(true);
        if (window.jQuery) window.jQuery('#cwVideoModal').modal('show');
    }
    function showVTab(t) {
        all('#cwVideoModal [data-vtab]').forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-vtab') === t); });
        $('#cwVList').style.display = t === 'list' ? '' : 'none'; $('#cwVUp').style.display = t === 'upload' ? '' : 'none';
    }
    function loadVideos(force) {
        var box = $('#cwVList');
        if (S.videoCache && !force) { drawVideos(); return; }
        box.innerHTML = '<div class="muted p-3">Loading the videos…</div>';
        fetch(URLS.videos, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); })
            .then(function (j) { S.videoCache = j.videos || []; drawVideos(); })
            .catch(function () { box.innerHTML = '<div class="muted p-3">The video list could not be loaded.</div>'; });
    }
    function drawVideos() {
        var box = $('#cwVList'), v = S.videoCache || [];
        if (!v.length) { box.innerHTML = '<div class="muted p-3">There are no videos yet. Use the "Upload a new video" tab.</div>'; return; }
        box.innerHTML = '<div class="cw-vgrid">' + v.map(function (x, i) {
            return '<div class="cw-vcard"><video preload="metadata" muted src="' + attr(x.url) + '#t=0.5" controls></video><div class="t">' + esc(x.label) + '</div>' +
                '<div class="m">' + esc((x.module ? x.module + ' · ' : '') + fmtSize(x.size || 0)) + '</div><button type="button" class="btn btn-primary btn-sm" data-vuse="' + i + '">Use this video</button></div>';
        }).join('') + '</div>';
    }
    function useVideo(item) {
        var t = S.videoFor; if (!t) return;
        if (t.bid) {
            var f = find(t.bid); if (!f) return;
            var r = root(f.b.html), cap = r ? ((r.querySelector('figcaption') || r.querySelector('small') || {}).innerHTML || '') : '';
            f.b.html = videoHtml(item.url, cap || esc(item.label)); f.b.type = 'video';
            S.openB[f.b.id] = true;
        } else {
            var nb = { id: nextId(), type: 'video', html: videoHtml(item.url, esc(item.label)) };
            S.model.topics[t.ti].blocks.push(nb); S.openT[S.model.topics[t.ti].id] = true; S.openB[nb.id] = true; S.focus = { type: 'cw-focus', bid: nb.id };
        }
        if (window.jQuery) window.jQuery('#cwVideoModal').modal('hide');
        changed(); renderAll();
    }
    function find(bid) { var r = null; S.model.topics.forEach(function (t, ti) { t.blocks.forEach(function (b, bi) { if (b.id === bid) r = { t: t, ti: ti, b: b, bi: bi }; }); }); return r; }
    $('#cwVideoModal').addEventListener('click', function (e) {
        var tab = e.target.closest('[data-vtab]'); if (tab) { showVTab(tab.getAttribute('data-vtab')); return; }
        var use = e.target.closest('[data-vuse]'); if (use) { useVideo(S.videoCache[+use.getAttribute('data-vuse')]); return; }
        if (e.target.id === 'cwVGo') uploadVideo();
    });
    function uploadVideo() {
        var file = $('#cwVFile').files[0], msg = $('#cwVMsg'), bar = $('#cwVProg'), go = $('#cwVGo');
        if (!file) { msg.textContent = 'Choose a video file first.'; return; }
        if (file.size > 150 * 1048576) { msg.textContent = 'That video is ' + fmtSize(file.size) + '. The limit is 150 MB.'; return; }
        var fd = new FormData(); fd.append('video', file); fd.append('video_label', $('#cwVLabel').value || file.name.replace(/\.[^.]+$/, ''));
        var xhr = new XMLHttpRequest(); xhr.open('POST', URLS.videoUpload); xhr.setRequestHeader('X-CSRF-TOKEN', CSRF); xhr.setRequestHeader('Accept', 'application/json'); xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        go.disabled = true; bar.style.display = ''; msg.textContent = 'Uploading…';
        xhr.upload.onprogress = function (e) { if (e.lengthComputable) { bar.firstElementChild.style.width = Math.round(e.loaded / e.total * 100) + '%'; msg.textContent = 'Uploading… ' + Math.round(e.loaded / e.total * 100) + '%'; } };
        xhr.onload = function () {
            go.disabled = false; var j = {}; try { j = JSON.parse(xhr.responseText); } catch (e) {}
            if (xhr.status >= 200 && xhr.status < 300 && j.video) { bar.style.display = 'none'; bar.firstElementChild.style.width = '0'; S.videoCache = null; useVideo(j.video); }
            else { msg.textContent = (j && j.message) || ('The upload failed (' + xhr.status + '). Check the file type and size, then try again.'); }
        };
        xhr.onerror = function () { go.disabled = false; msg.textContent = 'The upload failed. Check your connection and try again.'; };
        xhr.send(fd);
    }

    // ---------- rendering the editor ----------
    function toolButtons(kind, ti, bi) {
        var d = ' data-ti="' + ti + '" data-bi="' + (bi == null ? '' : bi) + '"';
        return '<span class="cw-tools">' +
            '<button type="button" class="btn btn-light btn-sm" data-act="' + kind + '-up"' + d + ' title="Move up">↑</button>' +
            '<button type="button" class="btn btn-light btn-sm" data-act="' + kind + '-down"' + d + ' title="Move down">↓</button>' +
            '<button type="button" class="btn btn-light btn-sm" data-act="' + kind + '-dup"' + d + ' title="Duplicate">⧉</button>' +
            '<button type="button" class="btn btn-light btn-sm" data-act="' + kind + '-del"' + d + ' title="Delete" style="color:#b91c1c">🗑</button></span>';
    }
    function ce(path, html) { return '<div class="cw-ce" contenteditable="true" spellcheck="true" data-path="' + path + '">' + (html || '') + '</div>'; }
    function colorRow(label, path, value, def) {
        return '<div class="cw-row2" style="margin-top:8px"><span class="cw-lab" style="margin:0;min-width:92px">' + label + '</span><input type="color" class="cw-col" data-color="' + path + '" value="' + (value || def) + '">' +
            '<button type="button" class="btn btn-light cw-mini" data-act="color-auto" data-path="' + path + '">' + (value ? 'Back to automatic' : 'Automatic') + '</button></div>';
    }
    function renderBlockBody(b, ti, bi) {
        var key = 'data-ti="' + ti + '" data-bi="' + bi + '"';
        if (TEXT_TYPES[b.type]) {
            var r = root(b.html), sel = Object.keys(TEXT_TYPES).map(function (k) { return '<option value="' + k + '"' + (k === b.type ? ' selected' : '') + '>' + TEXT_TYPES[k] + '</option>'; }).join('');
            return '<div class="cw-row2" style="margin:6px 0"><span class="cw-lab" style="margin:0">Style</span><select class="form-control form-control-sm" style="width:auto" data-act="blk-type" ' + key + '>' + sel + '</select></div>' +
                '<div class="cw-ce" contenteditable="true" spellcheck="true" data-blk="text" ' + key + '>' + (r ? r.innerHTML : '') + '</div>';
        }
        if (b.type === 'list') {
            var l = root(b.html), kind = listKind(l), opts = Object.keys(LIST_KINDS).map(function (k) { return '<option value="' + k + '"' + (k === kind ? ' selected' : '') + '>' + LIST_KINDS[k][2] + '</option>'; }).join('');
            var items = [].slice.call(l.children).map(function (li, ii) {
                var d = ' ' + key + ' data-ii="' + ii + '"';
                return '<div class="cw-li"><div class="cw-ce" contenteditable="true" data-blk="li"' + d + '>' + li.innerHTML + '</div>' +
                    '<span class="cw-tools"><button type="button" class="btn btn-light btn-sm" data-act="li-up"' + d + '>↑</button><button type="button" class="btn btn-light btn-sm" data-act="li-down"' + d + '>↓</button><button type="button" class="btn btn-light btn-sm" style="color:#b91c1c" data-act="li-del"' + d + '>×</button></span></div>';
            }).join('');
            return '<div class="cw-row2" style="margin:6px 0"><span class="cw-lab" style="margin:0">List style</span><select class="form-control form-control-sm" style="width:auto" data-act="list-kind" ' + key + '>' + opts + '</select></div>' + items +
                '<button type="button" class="btn btn-light cw-mini" data-act="li-add" ' + key + '>+ Add item</button>';
        }
        if (b.type === 'table') {
            var o = tableOf(b.html), rows = [].slice.call(o.t.querySelectorAll('tr')), cols = rows.reduce(function (m, r2) { return Math.max(m, r2.cells.length); }, 0);
            var head = '<tr><th></th>' + Array.apply(null, Array(cols)).map(function (_, c) { return '<th><button type="button" class="btn btn-light cw-mini" style="color:#b91c1c" data-act="col-del" ' + key + ' data-c="' + c + '" title="Remove this column">× col</button></th>'; }).join('') + '</tr>';
            var body = rows.map(function (tr, r3) {
                return '<tr><th><button type="button" class="btn btn-light cw-mini" style="color:#b91c1c" data-act="row-del" ' + key + ' data-r="' + r3 + '" title="Remove this row">× row</button></th>' +
                    [].slice.call(tr.cells).map(function (td, c) { return '<td><div class="cw-ce" contenteditable="true" data-blk="cell" ' + key + ' data-r="' + r3 + '" data-c="' + c + '">' + td.innerHTML + '</div></td>'; }).join('') + '</tr>';
            }).join('');
            return '<div class="cw-grid"><table>' + head + body + '</table></div><div class="cw-row2" style="margin-top:6px"><button type="button" class="btn btn-light cw-mini" data-act="row-add" ' + key + '>+ Add row</button><button type="button" class="btn btn-light cw-mini" data-act="col-add" ' + key + '>+ Add column</button></div>' +
                '<div class="cw-note" style="margin-top:4px">Bold and links work inside a cell. Cell colours and widths come from the page design.</div>';
        }
        if (b.type === 'image') {
            var im = root(b.html), img = im.tagName === 'IMG' ? im : im.querySelector('img'), cap = im.tagName === 'FIGURE' ? im.querySelector('figcaption') : null;
            var src = img ? (img.getAttribute('src') || '').replace(TOKEN, IMG_BASE) : '';
            return '<img class="cw-thumb" src="' + attr(src) + '" alt="">' +
                '<div class="cw-lab">Description for screen readers</div><input type="text" class="form-control form-control-sm" data-blk="alt" ' + key + ' value="' + attr(img ? img.getAttribute('alt') : '') + '">' +
                (cap ? '<div class="cw-lab">Caption</div><div class="cw-ce" contenteditable="true" data-blk="caption" ' + key + '>' + cap.innerHTML + '</div>' : '') +
                '<div class="cw-row2" style="margin-top:8px"><label class="btn btn-light cw-mini" style="margin:0">Replace picture<input type="file" accept="image/*" hidden data-act="img-up" ' + key + '></label><span class="cw-note">JPG, PNG, GIF or WebP, up to 4 MB.</span></div>';
        }
        if (b.type === 'video') {
            var vr = root(b.html), vsrc = videoSrc(vr), vcap = vr ? ((vr.querySelector('figcaption') || vr.querySelector('small') || {}).innerHTML || '') : '';
            return (vsrc ? '<video class="cw-vprev" controls preload="metadata" src="' + attr(vsrc) + '"></video>' : '<div class="cw-note" style="margin:6px 0">No video attached yet: this is a placeholder with a play button. Choose a video below.</div>') +
                '<div class="cw-lab">Caption</div><div class="cw-ce" contenteditable="true" data-blk="vcap" ' + key + '>' + vcap + '</div>' +
                '<div class="cw-row2" style="margin-top:8px"><button type="button" class="btn btn-light cw-mini" data-act="vid-pick" ' + key + '>' + (vsrc ? 'Change video' : 'Choose or upload a video') + '</button></div>';
        }
        var sr = root(b.html), ls = sr ? lines(sr) : [];
        return '<div class="cw-note" style="margin:6px 0">A designed block (cards, tabs, video, animation...). Its look stays as designed; you can reword it, move it, copy it or remove it.</div>' +
            (ls.length ? ls.map(function (e, li) { return '<div class="cw-ce" style="margin-bottom:5px" contenteditable="true" data-blk="line" ' + key + ' data-li="' + li + '">' + e.innerHTML + '</div>'; }).join('') : '<div class="muted">No wording in this block.</div>') +
            '<details style="margin-top:8px"><summary class="cw-note" style="cursor:pointer">Edit the block\'s HTML (advanced)</summary><textarea class="cw-raw" data-blk="raw" ' + key + '>' + esc(b.html) + '</textarea><div class="cw-note">Changes apply when you click outside the box.</div></details>';
    }
    function summary(b) {
        if (b.type === 'image') return 'Picture';
        if (b.type === 'video') { var vr = root(b.html), vc = vr ? (vr.querySelector('figcaption,small') || {}).textContent : ''; return (hasVideo(vr) ? 'Video · ' : 'Video placeholder · ') + (vc || ''); }
        if (b.type === 'table') { var t = tableOf(b.html).t; return 'Table · ' + (t ? t.querySelectorAll('tr').length : 0) + ' rows'; }
        if (b.type === 'list') { var l = root(b.html); return LIST_KINDS[listKind(l)][2] + ' · ' + l.children.length + ' items'; }
        var s = plain(b.html); return s.length > 90 ? s.slice(0, 90) + '…' : (s || '(empty)');
    }
    var TYPE_LABEL = { heading: 'Heading', paragraph: 'Text', lead: 'Lead', note: 'Note', callout: 'Callout', list: 'List', table: 'Table', image: 'Picture', video: 'Video', special: 'Designed' };

    function renderAll() {
        var keepY = panel.scrollTop, m = S.model, h = '';
        h += '<details class="cw-card" data-ban' + (S.openT.__ban ? ' open' : '') + '><summary><span class="cw-tt">Banner and page colours</span></summary><div class="cw-card-b">' +
            '<div class="cw-lab">Small label above the title</div>' + ce('hero.k', m.hero.k) +
            '<div class="cw-lab">Page title</div>' + ce('hero.title', m.hero.title) +
            '<div class="cw-lab">Intro text</div>' + ce('hero.lead', m.hero.lead) +
            '<div class="cw-lab">Banner colours (it fades from the first to the second)</div>' +
            colorRow('From', 'colors.hero1', m.colors.hero1, DEFAULTS.hero1) + colorRow('To', 'colors.hero2', m.colors.hero2, DEFAULTS.hero2) + '</div></details>';
        m.topics.forEach(function (t, ti) {
            h += '<details class="cw-card" data-tid="' + t.id + '"' + (S.openT[t.id] ? ' open' : '') + '><summary>' +
                '<button type="button" class="cw-icbtn" data-act="icon" data-ti="' + ti + '" title="Change icon"><svg viewBox="0 0 24 24"><use href="#' + t.icon + '"/></svg></button>' +
                '<span class="cw-tt">Topic ' + (ti + 1) + ': ' + esc(plain(t.title) || 'Untitled') + '</span>' + toolButtons('topic', ti) + '</summary><div class="cw-card-b">' +
                '<div class="cw-lab">Topic title</div>' + ce('topics.' + ti + '.title', t.title) +
                '<div class="cw-lab">Subtitle</div>' + ce('topics.' + ti + '.subtitle', t.subtitle) +
                colorRow('Accent colour', 'topics.' + ti + '.color', t.color, PALETTE[ti % 7]) +
                '<div class="cw-lab">Blocks</div>';
            t.blocks.forEach(function (b, bi) {
                var o = S.openB[b.id];
                h += '<div class="cw-blk' + (o ? ' active' : '') + '" data-bid="' + b.id + '"><div class="cw-bh" data-act="blk-toggle" data-bid="' + b.id + '"><span class="cw-bt">' + TYPE_LABEL[b.type] + '</span><span class="cw-bs">' + esc(summary(b)) + '</span>' + toolButtons('blk', ti, bi) + '</div>' +
                    (o ? '<div class="cw-bb">' + renderBlockBody(b, ti, bi) + '</div>' : '') + '</div>';
            });
            h += '<div class="cw-add"><select class="form-control form-control-sm" style="width:auto;display:inline-block" data-act="blk-add" data-ti="' + ti + '"><option value="">+ Add a block…</option>' +
                '<option value="paragraph">Text</option><option value="lead">Lead text</option><option value="heading">Heading</option><option value="list">List</option><option value="callout">Highlighted note</option><option value="note">Small note</option><option value="table">Table</option><option value="image">Picture</option><option value="video">Video</option></select></div></div></details>';
        });
        h += '<div style="margin:6px 0 12px"><button type="button" class="btn btn-light btn-sm" data-act="topic-add">+ Add a topic card</button></div>';
        h += '<details class="cw-card"><summary><span class="cw-tt">"You have read all topics" card</span></summary><div class="cw-card-b">' +
            '<div class="cw-lab">Title</div>' + ce('done.title', m.done.title) + '<div class="cw-lab">Text</div>' + ce('done.text', m.done.text) +
            (m.done.extra ? '<details style="margin-top:8px"><summary class="cw-note" style="cursor:pointer">Extra content on this card (advanced)</summary><textarea class="cw-raw" data-path="done.extra" data-rawpath="1">' + esc(m.done.extra) + '</textarea></details>' : '') +
            '<div class="cw-note" style="margin-top:6px">Examiners see this card once every topic card has been opened. If you add or remove topic cards, check the number in the title.</div></div></details>';
        panel.innerHTML = h; panel.scrollTop = keepY;
    }

    // ---------- edits ----------
    function sel(t) { var ti = +t.getAttribute('data-ti'), bi = +t.getAttribute('data-bi'); return { ti: ti, bi: bi, t: S.model.topics[ti], b: S.model.topics[ti] && S.model.topics[ti].blocks[bi] }; }
    function clone(o) { return JSON.parse(JSON.stringify(o)); }
    function move(arr, i, d) { var j = i + d; if (j < 0 || j >= arr.length) return false; var x = arr.splice(i, 1)[0]; arr.splice(j, 0, x); return true; }

    panel.addEventListener('input', function (e) {
        var t = e.target, p = t.getAttribute('data-path');
        if (p) {
            setPath(S.model, p, t.getAttribute('data-rawpath') ? t.value : t.innerHTML);
            var mm = /^topics\.(\d+)\.title$/.exec(p);
            if (mm) { var tt = $('.cw-tt', t.closest('details')); if (tt) tt.textContent = 'Topic ' + (+mm[1] + 1) + ': ' + (plain(t.innerHTML) || 'Untitled'); }
            changed(); return;
        }
        if (t.type === 'color') { setPath(S.model, t.getAttribute('data-color'), t.value); changed(); return; }
        var k = t.getAttribute('data-blk'); if (!k || k === 'raw') return;
        var s = sel(t); if (!s.b) return;
        var r = root(s.b.html);
        if (k === 'text') { r.innerHTML = t.innerHTML; s.b.html = r.outerHTML; }
        else if (k === 'li') { var items = all('[data-blk="li"]', t.closest('.cw-bb')).map(function (x) { return x.innerHTML; }); s.b.html = buildList(listKind(r), items); }
        else if (k === 'cell') { var o = tableOf(s.b.html), cell = o.t.querySelectorAll('tr')[+t.getAttribute('data-r')].cells[+t.getAttribute('data-c')]; cell.innerHTML = t.innerHTML; s.b.html = tableOut(o); }
        else if (k === 'line') { var ls = lines(r); ls[+t.getAttribute('data-li')].innerHTML = t.innerHTML; s.b.html = r.outerHTML; }
        else if (k === 'alt') { var img = r.tagName === 'IMG' ? r : r.querySelector('img'); img.setAttribute('alt', t.value); s.b.html = r.outerHTML; }
        else if (k === 'caption') { r.querySelector('figcaption').innerHTML = t.innerHTML; s.b.html = r.outerHTML; }
        else if (k === 'vcap') { var cn = r.querySelector('figcaption') || r.querySelector('small'); if (cn) { cn.innerHTML = t.innerHTML; s.b.html = r.outerHTML; } }
        var sm = $('[data-bid="' + s.b.id + '"] .cw-bs', panel); if (sm) sm.textContent = summary(s.b);
        changed();
    });
    panel.addEventListener('change', function (e) {
        var t = e.target, a = t.getAttribute('data-act'), k = t.getAttribute('data-blk'), s, r;
        if (k === 'raw') { s = sel(t); s.b.html = t.value; changed(); renderAll(); return; }
        if (a === 'blk-type') { s = sel(t); r = root(s.b.html); s.b.html = newText(t.value, r ? r.innerHTML : ''); s.b.type = t.value; changed(); renderAll(); }
        if (a === 'list-kind') { s = sel(t); var l = root(s.b.html); s.b.html = buildList(t.value, [].slice.call(l.children).map(function (li) { return li.innerHTML; })); changed(); renderAll(); }
        if (a === 'blk-add' && t.value === 'video') { s = sel(t); t.value = ''; openVideos({ ti: s.ti, bid: null }); return; }
        if (a === 'blk-add' && t.value) { s = sel(t); var nb = newBlock(t.value); s.t.blocks.push(nb); S.openT[s.t.id] = true; S.openB[nb.id] = true; changed(); renderAll(); focusPreview({ bid: nb.id }); }
        if (a === 'img-up' && t.files[0]) {
            s = sel(t); var fd = new FormData(); fd.append('image', t.files[0]);
            post(URLS.image, fd, true).then(function (r2) {
                if (!r2.ok) { alert((r2.j && r2.j.message) || 'The picture could not be uploaded.'); return; }
                var im = root(s.b.html), img = im.tagName === 'IMG' ? im : im.querySelector('img'); img.setAttribute('src', r2.j.src); s.b.html = im.outerHTML; changed(); renderAll();
            });
        }
    });
    panel.addEventListener('click', function (e) {
        var t = e.target.closest('[data-act]'); if (!t) return;
        var a = t.getAttribute('data-act'), s;
        if (a === 'blk-toggle') { if (e.target.closest('.cw-tools')) return; var id = t.getAttribute('data-bid'); S.openB[id] = !S.openB[id]; renderAll(); if (S.openB[id]) focusPreview({ bid: id }); return; }
        if (a === 'icon') { e.preventDefault(); e.stopPropagation(); S.iconFor = +t.getAttribute('data-ti'); openIcons(); return; }
        if (a === 'vid-pick') { s = sel(t); openVideos({ ti: s.ti, bid: s.b.id }); return; }
        if (a === 'color-auto') { setPath(S.model, t.getAttribute('data-path'), null); changed(); renderAll(); return; }
        if (a === 'topic-add') { var nt = { id: 't' + (S.model.topics.length + 1), icon: 'i1', color: null, title: 'New topic', subtitle: 'Short description', blocks: [newBlock('paragraph')] }; S.model.topics.push(nt); S.openT[nt.id] = true; changed(); renderAll(); return; }
        if (a.indexOf('topic-') === 0) {
            e.preventDefault(); e.stopPropagation(); var ti = +t.getAttribute('data-ti'), ts = S.model.topics;
            if (a === 'topic-up') move(ts, ti, -1);
            else if (a === 'topic-down') move(ts, ti, 1);
            else if (a === 'topic-dup') { var c = clone(ts[ti]), base = maxBlockNo(); c.id = 't' + (ts.length + 1); c.blocks.forEach(function (b, i) { b.id = 'b' + (base + 1 + i); }); ts.splice(ti + 1, 0, c); }
            else if (a === 'topic-del') { if (ts.length < 2) { alert('A page needs at least one topic card.'); return; } if (!confirm('Remove this topic card and everything in it?')) return; ts.splice(ti, 1); }
            ts.forEach(function (x, i) { x.id = 't' + (i + 1); S.openT[x.id] = S.openT[x.id] || false; });
            changed(); renderAll(); return;
        }
        if (a.indexOf('blk-') === 0 && a !== 'blk-type' && a !== 'blk-add') {
            e.preventDefault(); e.stopPropagation(); s = sel(t); var arr = s.t.blocks;
            if (a === 'blk-up') move(arr, s.bi, -1);
            else if (a === 'blk-down') move(arr, s.bi, 1);
            else if (a === 'blk-dup') { var c2 = clone(s.b); c2.id = nextId(); arr.splice(s.bi + 1, 0, c2); S.openB[c2.id] = true; }
            else if (a === 'blk-del') { if (!confirm('Remove this block?')) return; arr.splice(s.bi, 1); }
            changed(); renderAll(); return;
        }
        s = sel(t); if (!s.b) return;
        var r = root(s.b.html);
        if (a.indexOf('li-') === 0) {
            var items = [].slice.call(r.children).map(function (li) { return li.innerHTML; }), ii = +t.getAttribute('data-ii');
            if (a === 'li-add') items.push('New item');
            else if (a === 'li-del') { if (items.length < 2) return; items.splice(ii, 1); }
            else if (a === 'li-up') move(items, ii, -1);
            else if (a === 'li-down') move(items, ii, 1);
            s.b.html = buildList(listKind(r), items); changed(); renderAll(); return;
        }
        if (/^(row|col)-/.test(a)) {
            var o = tableOf(s.b.html), rows = [].slice.call(o.t.querySelectorAll('tr'));
            if (a === 'row-add') { var last = rows[rows.length - 1], nr = last.cloneNode(true); [].slice.call(nr.cells).forEach(function (c3) { c3.innerHTML = ''; }); last.parentNode.appendChild(nr); }
            else if (a === 'row-del') { if (rows.length < 2) return; rows[+t.getAttribute('data-r')].remove(); }
            else if (a === 'col-add') { rows.forEach(function (tr) { var lc = tr.cells[tr.cells.length - 1], nc = lc.cloneNode(false); nc.innerHTML = ''; tr.appendChild(nc); }); }
            else if (a === 'col-del') { var cc = +t.getAttribute('data-c'); if (rows[0].cells.length < 2) return; rows.forEach(function (tr) { if (tr.cells[cc]) tr.deleteCell(cc); }); }
            s.b.html = tableOut(o); changed(); renderAll();
        }
    });
    panel.addEventListener('focusin', function (e) {
        var b = e.target.closest('[data-bid]'), t = e.target.closest('details[data-tid]');
        if (b) focusPreview({ bid: b.getAttribute('data-bid') }); else if (t) focusPreview({ tid: t.getAttribute('data-tid') });
    });
    panel.addEventListener('toggle', function (e) {
        var d = e.target;
        if (d.getAttribute && d.getAttribute('data-tid')) S.openT[d.getAttribute('data-tid')] = d.open;
        if (d.hasAttribute && d.hasAttribute('data-ban')) S.openT.__ban = d.open;
    }, true);
    panel.addEventListener('keydown', function (e) { if (e.target.classList.contains('cw-ce') && e.key === 'Enter') { e.preventDefault(); if (e.shiftKey) document.execCommand('insertHTML', false, '<br>'); } });
    panel.addEventListener('paste', function (e) { if (e.target.classList.contains('cw-ce')) { e.preventDefault(); document.execCommand('insertText', false, (e.clipboardData || window.clipboardData).getData('text/plain')); } });

    // ---------- icon picker ----------
    function drawIcons(q) {
        var cur = S.model.topics[S.iconFor].icon, f = (q || '').trim().toLowerCase();
        var list = ICONS.filter(function (i) { return !f || i.name.toLowerCase().indexOf(f) > -1; });
        $('#cwIcons').innerHTML = list.map(function (i) { return '<button type="button" data-ic="' + i.id + '" class="' + (i.id === cur ? 'on' : '') + '" title="' + attr(i.name) + '" aria-label="' + attr(i.name) + '"><svg viewBox="0 0 24 24"><use href="#' + i.id + '"/></svg></button>'; }).join('');
        $('#cwIconNone').style.display = list.length ? 'none' : 'block';
        $('#cwIconCount').textContent = '(' + list.length + (f ? ' found' : ' available') + ')';
    }
    function openIcons() {
        $('#cwIconSearch').value = ''; drawIcons('');
        if (window.jQuery) { window.jQuery('#cwIconModal').modal('show'); window.jQuery('#cwIconModal').one('shown.bs.modal', function () { $('#cwIconSearch').focus(); }); }
    }
    $('#cwIconSearch').addEventListener('input', function () { drawIcons(this.value); });
    $('#cwIcons').addEventListener('click', function (e) {
        var b = e.target.closest('[data-ic]'); if (!b) return;
        S.model.topics[S.iconFor].icon = b.getAttribute('data-ic');
        if (window.jQuery) window.jQuery('#cwIconModal').modal('hide');
        changed(); renderAll();
    });

    // ---------- save / discard / reset ----------
    $('#cwSave').addEventListener('click', function () {
        var btn = this; btn.disabled = true; $('#cwState').textContent = 'Saving…';
        post(URLS.save, { model: S.model }).then(function (r) {
            if (!r.ok) { btn.disabled = false; $('#cwState').innerHTML = '<span class="cw-dirty">Not saved: ' + esc((r.j && r.j.message) || 'error') + '</span>'; return; }
            S.saved = JSON.stringify(S.model); dirty(); $('#cwState').textContent = 'Saved. Examiners see this now.';
        }).catch(function () { btn.disabled = false; $('#cwState').innerHTML = '<span class="cw-dirty">Not saved: check your connection</span>'; });
    });
    $('#cwRevert').addEventListener('click', function () { if (confirm('Discard every change you have not saved?')) { S.dirty = false; location.reload(); } });
    $('#cwReset').addEventListener('click', function () {
        if (!confirm('Go back to the original design of this page? Every change made in this editor (saved or not) is thrown away, and examiners see the original page.')) return;
        post(URLS.reset, {}).then(function () { S.dirty = false; location.reload(); });
    });
    window.addEventListener('beforeunload', function (e) { if (S.dirty) { e.preventDefault(); e.returnValue = ''; } });

    // ---------- go ----------
    S.openT[S.model.topics[0].id] = true;
    renderAll(); dirty(); refreshPreview();
})();
</script>
@endpush

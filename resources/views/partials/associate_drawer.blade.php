{{--
  Side drawer with a short summary of an associate, opened from the Associates lists before the whole profile.
  Same look as the fees subscription drawer (admin/fees/subscriptions/report). Included once by layout/app.blade.php on the
  Associates list pages. Clicking a person's name link (a link to .../view/{id} in a table, outside the action menu) opens the
  drawer; ctrl/cmd/shift-click, middle click and the "View" item of the action menu still go to the full page.
  Content: cosecsa-api internal/associate-preview/{type}/{id} via admin/associates/{segment}/preview/{id}.
--}}
<style>
    .ad-root { --ad-ink:#141d23; --ad-muted:#6b7280; --ad-faint:#9ca3af; --ad-line:#e5e9f0; --ad-card:#fff; --ad-soft:#f7f8fa; --ad-brand:#a02626;
               --ad-good-bg:#e9f7ee; --ad-good-fg:#2e7d32; --ad-good-bd:#c3e9cf; --ad-warn-bg:#fff6dc; --ad-warn-fg:#8a6100; --ad-warn-bd:#f5e0a3;
               --ad-bad-bg:#fdeeee; --ad-bad-fg:#c62828; --ad-bad-bd:#f6caca; --ad-mute-bg:#f1f3f5; --ad-mute-fg:#6b7280; --ad-mute-bd:#e2e5e9; }
    body.dark-mode .ad-root { --ad-ink:#f1f5f9; --ad-muted:#94a3b8; --ad-faint:#718096; --ad-line:#2d3748; --ad-card:#1e2330; --ad-soft:#252c3b; --ad-brand:#f48a8a;
               --ad-good-bg:#17311f; --ad-good-fg:#7ddc97; --ad-good-bd:#25502f; --ad-warn-bg:#342a10; --ad-warn-fg:#f3cf6b; --ad-warn-bd:#54441a;
               --ad-bad-bg:#3a1c1f; --ad-bad-fg:#f59a9a; --ad-bad-bd:#5c2a2e; --ad-mute-bg:#2b3040; --ad-mute-fg:#a0aec0; --ad-mute-bd:#3b4254; }
    .ad-backdrop { position: fixed; inset: 0; background: rgba(15,20,30,.4); opacity: 0; pointer-events: none; transition: opacity .2s; z-index: 1060; }
    body.dark-mode .ad-backdrop { background: rgba(0,0,0,.55); }
    .ad-drawer { position: fixed; top: 0; right: 0; height: 100vh; width: min(440px, 100vw); background: var(--ad-card); z-index: 1061; color: var(--ad-ink);
                 box-shadow: -8px 0 30px rgba(0,0,0,.14); transform: translateX(100%); transition: transform .25s ease; display: flex; flex-direction: column; }
    .ad-open .ad-backdrop { opacity: 1; pointer-events: auto; }
    .ad-open .ad-drawer { transform: none; }
    .ad-dh { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--ad-line); }
    .ad-dh h5 { margin: 0; font-weight: 700; font-size: 1.1rem; }
    .ad-x { background: none; border: 0; color: var(--ad-faint); font-size: 1.3rem; line-height: 1; padding: 4px 6px; border-radius: 6px; cursor: pointer; }
    .ad-x:hover { color: var(--ad-ink); background: var(--ad-soft); }
    .ad-db { flex: 1; overflow-y: auto; padding: 20px 22px 28px; }
    .ad-df { padding: 14px 22px; border-top: 1px solid var(--ad-line); display: flex; gap: 8px; }
    .ad-df .btn { flex: 1; font-weight: 600; border-radius: 8px; }
    .ad-df .btn-ad { background: var(--ad-brand); border-color: var(--ad-brand); color: #fff; }
    body.dark-mode .ad-df .btn-ad { background: #a02626; border-color: #a02626; }
    .ad-df .btn-ad:hover { background: #870f0f; border-color: #870f0f; color: #FEC503; }
    .ad-df .btn-ad-ghost { border: 1px solid var(--ad-line); background: var(--ad-card); color: var(--ad-ink); }
    .ad-df .btn-ad-ghost:hover { color: var(--ad-brand); }
    .ad-eyebrow { font-size: .68rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--ad-faint); }
    .ad-id { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
    .ad-avatar { flex: none; width: 54px; height: 54px; border-radius: 50%; background: #f3e3e3; color: #a02626; font-weight: 700; font-size: 1.05rem; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; }
    body.dark-mode .ad-avatar { background: #3a2328; color: #f48a8a; }
    .ad-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .ad-id .nm { font-weight: 700; font-size: 1.08rem; line-height: 1.25; }
    .ad-id .meta { color: var(--ad-muted); font-size: .83rem; }
    .ad-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 22px; }
    .ad-stat { border: 1px solid var(--ad-line); background: var(--ad-soft); border-radius: 12px; padding: 12px 6px; text-align: center; min-width: 0; }
    .ad-stat .v { font-size: 1rem; font-weight: 700; margin-top: 3px; overflow-wrap: anywhere; }
    .ad-stat.t-good { background: var(--ad-good-bg); border-color: var(--ad-good-bd); } .ad-stat.t-good .v { color: var(--ad-good-fg); }
    .ad-stat.t-warn { background: var(--ad-warn-bg); border-color: var(--ad-warn-bd); } .ad-stat.t-warn .v { color: var(--ad-warn-fg); }
    .ad-stat.t-bad { background: var(--ad-bad-bg); border-color: var(--ad-bad-bd); } .ad-stat.t-bad .v { color: var(--ad-bad-fg); }
    .ad-stat.t-muted .v { color: var(--ad-muted); }
    .ad-sec { margin: 0 0 10px; }
    .ad-contact { margin-bottom: 22px; }
    .ad-contact div { display: flex; align-items: center; gap: 14px; padding: 11px 0; border-bottom: 1px solid var(--ad-line); font-size: .95rem; word-break: break-word; }
    .ad-contact i { width: 16px; text-align: center; color: var(--ad-faint); }
    .ad-contact a, body.dark-mode .ad-contact a { color: var(--ad-ink) !important; }
    .ad-contact a:hover { color: var(--ad-brand) !important; }
    .ad-contact .none { color: var(--ad-faint); }
    .ad-table { border: 1px solid var(--ad-line); border-radius: 12px; overflow: hidden; margin-bottom: 22px; }
    .ad-row { display: flex; gap: 12px; padding: 10px 16px; border-bottom: 1px solid var(--ad-line); font-size: .9rem; }
    .ad-row:last-child { border-bottom: 0; }
    .ad-row .k { flex: 0 0 38%; color: var(--ad-muted); }
    .ad-row .v { flex: 1; min-width: 0; font-weight: 600; overflow-wrap: anywhere; }
    .ad-empty { padding: 22px 16px; text-align: center; color: var(--ad-faint); font-size: .88rem; }
    .ad-skel { background: linear-gradient(90deg, var(--ad-soft) 25%, var(--ad-line) 50%, var(--ad-soft) 75%); background-size: 200% 100%; animation: adsk 1.2s infinite; border-radius: 8px; }
    @keyframes adsk { to { background-position: -200% 0; } }
    a.ad-peek { cursor: pointer; }
</style>

<div class="ad-root" id="adRoot">
    <div class="ad-backdrop" data-close></div>
    <aside class="ad-drawer" role="dialog" aria-modal="true" aria-labelledby="adTitle" aria-hidden="true">
        <div class="ad-dh"><h5 id="adTitle">Summary</h5><button type="button" class="ad-x" data-close aria-label="Close">&times;</button></div>
        <div class="ad-db" id="adBody"></div>
        <div class="ad-df" id="adFoot"></div>
    </aside>
</div>

<script>
var AssocDrawer = (function () {
    var root = document.getElementById('adRoot'), panel = root.querySelector('.ad-drawer'), body = document.getElementById('adBody'), foot = document.getElementById('adFoot'), title = document.getElementById('adTitle');
    var BASE = @json(url('admin/associates'));
    var SEGMENT = { trainee: 'trainees', candidate: 'candidates', fellow: 'fellows', member: 'members', programme_director: 'programme-directors', country_rep: 'reps', examiner: 'examiners' };
    var LABEL = { trainee: 'Trainee', candidate: 'Candidate', fellow: 'Fellow', member: 'Member', programme_director: 'Programme director', country_rep: 'Country representative', examiner: 'Examiner' };
    // where each type's summary and full page live (examiners are under Examinations, the rest under Associates)
    var PREVIEW = { examiner: @json(url('admin/exams/examiners/preview')) };
    var FULL = { examiner: @json(url('admin/exams/view_examiner')) };
    function previewUrl(type, id) { return (PREVIEW[type] || (BASE + '/' + SEGMENT[type] + '/preview')) + '/' + encodeURIComponent(id); }
    function fullUrl(type, id) { return (FULL[type] || (BASE + '/' + SEGMENT[type] + '/view')) + '/' + id; }
    var current = null;

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function initials(n) { var p = String(n || '').trim().split(/\s+/); return ((p[0] || '').charAt(0) + (p.length > 1 ? p[p.length - 1].charAt(0) : '')).toUpperCase(); }
    function skeleton() {
        return '<div class="ad-id"><div class="ad-skel" style="width:54px;height:54px;border-radius:50%;"></div><div style="flex:1"><div class="ad-skel" style="height:16px;width:70%;"></div><div class="ad-skel" style="height:12px;width:45%;margin-top:8px;"></div></div></div>' +
            '<div class="ad-stats">' + '<div class="ad-skel" style="height:64px;border-radius:12px;"></div>'.repeat(3) + '</div>' +
            '<div class="ad-skel" style="height:14px;margin:14px 0;"></div>'.repeat(3) + '<div class="ad-skel" style="height:200px;border-radius:12px;margin-top:22px;"></div>';
    }
    function render(d) {
        title.textContent = LABEL[d.type] || 'Summary';
        var avatar = d.photo
            ? '<span class="ad-avatar"><img src="' + esc(d.photo) + '" alt="" onerror="this.parentNode.textContent=\'' + esc(initials(d.name)) + '\';"></span>'
            : '<span class="ad-avatar">' + esc(initials(d.name)) + '</span>';
        var h = '<div class="ad-id">' + avatar + '<div><div class="nm">' + esc(d.name) + '</div>' + (d.meta ? '<div class="meta">' + esc(d.meta) + '</div>' : '') + '</div></div>';
        h += '<div class="ad-stats">' + (d.stats || []).map(function (s) {
            return '<div class="ad-stat' + (s.tone ? ' t-' + esc(s.tone) : '') + '"><div class="ad-eyebrow">' + esc(s.label) + '</div><div class="v">' + esc(s.value) + '</div></div>';
        }).join('') + '</div>';
        h += '<div class="ad-eyebrow ad-sec">Contact</div><div class="ad-contact">' + (d.contact || []).map(function (c) {
            if (!c.value) return '<div><i class="' + esc(c.icon) + '"></i><span class="none">Not on file</span></div>';
            return '<div><i class="' + esc(c.icon) + '"></i>' + (c.href ? '<a href="' + esc(c.href) + '">' + esc(c.value) + '</a>' : '<span>' + esc(c.value) + '</span>') + '</div>';
        }).join('') + '</div>';
        (d.sections || []).forEach(function (sec) {
            if (!sec.rows || !sec.rows.length) return;
            h += '<div class="ad-eyebrow ad-sec">' + esc(sec.title) + '</div><div class="ad-table">' + sec.rows.map(function (r) {
                return '<div class="ad-row"><span class="k">' + esc(r.k) + '</span><span class="v">' + esc(r.v) + '</span></div>';
            }).join('') + '</div>';
        });
        body.innerHTML = h;
        foot.innerHTML = (d.links || []).map(function (l) {
            return '<a href="' + esc(l.url) + '" class="btn btn-sm ' + (l.primary ? 'btn-ad' : 'btn-ad-ghost') + '">' + esc(l.label) + '</a>';
        }).join('');
    }
    function open(type, id) {
        if (!SEGMENT[type]) return false;
        current = type + ':' + id;
        title.textContent = LABEL[type];
        body.innerHTML = skeleton(); foot.innerHTML = '';
        root.classList.add('ad-open'); panel.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden';
        panel.querySelector('.ad-x').focus();
        var key = current;
        fetch(previewUrl(type, id), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (d) { if (current === key) render(d); })
            .catch(function () {
                if (current !== key) return;
                body.innerHTML = '<div class="ad-empty"><i class="fas fa-exclamation-triangle d-block mb-2" style="font-size:1.4rem;color:var(--ad-bad-fg);"></i>Couldn\'t load the summary. <a href="#" onclick="AssocDrawer.open(\'' + type + '\',' + Number(id) + ');return false;">Try again</a></div>';
                foot.innerHTML = '<a href="' + esc(fullUrl(type, id)) + '" class="btn btn-sm btn-ad">Open full page</a>';
            });
        return true;
    }
    function close() { current = null; root.classList.remove('ad-open'); panel.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; }

    root.addEventListener('click', function (e) { if (e.target.closest('[data-close]')) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && root.classList.contains('ad-open')) close(); });

    // A person's name link in a list table opens the drawer; everything else keeps working as before.
    var TYPE_OF = { trainees: 'trainee', candidates: 'candidate', fellows: 'fellow', members: 'member', 'programme-directors': 'programme_director', reps: 'country_rep' };
    var LINK = /\/admin\/associates\/(trainees|candidates|fellows|members|programme-directors|reps)\/view\/(\d+)\/?$/;
    var EXAMINER_LINK = /\/admin\/exams\/view_examiner\/(\d+)\/?$/;
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
        var a = e.target.closest('a[href]');
        if (!a || a.target === '_blank' || a.hasAttribute('data-no-peek') || !a.closest('table') || a.closest('.dropdown-menu') || a.closest('thead')) return;
        var ex = EXAMINER_LINK.exec(a.pathname);
        if (ex) { e.preventDefault(); open('examiner', ex[1]); return; }
        var m = LINK.exec(a.pathname);
        if (!m || !TYPE_OF[m[1]]) return;
        e.preventDefault();
        open(TYPE_OF[m[1]], m[2]);
    });
    return { open: open, close: close };
})();
</script>

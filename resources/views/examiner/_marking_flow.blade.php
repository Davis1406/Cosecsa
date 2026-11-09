{{--
    Step-by-step start of the marking forms: 1 Group, 2 Candidate, 3 Station. The marking form itself
    appears once all three are chosen. Big type and big buttons, one question on the screen at a time.

    The form keeps its own <select> boxes (group_id, candidate_id, station_id), hidden: this only fills
    them in, so the submit, the offline queue and the results pages work as before. A form calls
    CandidatePicker.load(candidates) after it has fetched the candidates of the chosen group.

    An examiner usually works one station at a time: after they mark their first candidate the station
    step offers only that station for the next candidate, with a "Show all stations" link.

    @param string      $kind            mcs | gs | an FCS exam type (cardiothoracic, urology, ...)
    @param string|null $format          clinical | viva for the FCS forms
    @param array       $assignedGroups  group ids assigned to this examiner (shown first)
--}}
<div id="mf" class="mf" style="display:none;">
    <ol class="mf-steps" id="mfSteps">
        <li data-step="group"><span class="mf-n">1</span><span class="mf-l">Group</span><span class="mf-v" id="mfVGroup"></span></li>
        <li data-step="candidate"><span class="mf-n">2</span><span class="mf-l">Candidate</span><span class="mf-v" id="mfVCand"></span></li>
        <li data-step="station"><span class="mf-n">3</span><span class="mf-l">Station</span><span class="mf-v" id="mfVStation"></span></li>
    </ol>

    <section class="mf-panel" id="mfPGroup">
        <h3 class="mf-h">Choose your group</h3>
        <div class="mf-tiles" id="mfGroupTiles"></div>
        <button type="button" class="mf-link" id="mfAllGroups" hidden>Show the other groups</button>
        <button type="button" class="mf-link" id="mfTestGroups" hidden>Show test groups</button>
    </section>

    <section class="mf-panel" id="mfPCand" hidden>
        <h3 class="mf-h">Choose the candidate <span class="mf-sub" id="mfCandCount"></span></h3>
        <div class="mf-bar">
            <input type="search" id="mfSearch" class="mf-search" placeholder="Type a candidate number…" autocomplete="off">
            <div class="mf-seg" id="mfFilter" role="group" aria-label="Show">
                <button type="button" class="on" data-filter="all">All</button>
                <button type="button" data-filter="todo">Not marked</button>
                <button type="button" data-filter="done">Marked</button>
            </div>
            <div class="mf-seg mf-view" id="mfView" role="group" aria-label="View">
                <button type="button" data-view="tiles" title="Tiles" aria-label="Tiles"><i class="fas fa-th-large"></i></button>
                <button type="button" data-view="list" title="List" aria-label="List"><i class="fas fa-list"></i></button>
            </div>
            <button type="button" class="mf-icon" id="mfRefresh" title="Refresh who has been examined" aria-label="Refresh"><i class="fas fa-sync-alt"></i></button>
        </div>
        <p class="mf-legend"><span class="mf-dot none"></span>Not marked yet <span class="mf-dot full"></span>Already marked (in green — tap <em>Review</em> to see your marks)</p>
        <div class="mf-cands mf-tiles" id="mfCandGrid" role="listbox" aria-label="Candidates"></div>
    </section>

    <section class="mf-panel" id="mfPStation" hidden>
        <h3 class="mf-h">Choose the station <span class="mf-sub" id="mfStationFor"></span></h3>
        <div class="mf-tiles mf-stations" id="mfStationTiles"></div>
        <button type="button" class="mf-link" id="mfAllStations" hidden>Show all stations</button>
    </section>

    <section class="mf-ready" id="mfReady" hidden>
        <span class="mf-ready-label">You are marking</span>
        <span class="mf-ready-val" id="mfReadyVal"></span>
        <button type="button" class="mf-link" id="mfChange">Change</button>
    </section>

    <p class="mf-warn" id="mfWarn" role="alert"></p>
</div>

<style>
    .mf { max-width: 940px; margin: 0 auto 1.25rem; padding: 0 10px; font-size: 1rem; }
    /* progress */
    .mf-steps { list-style: none; display: flex; gap: .5rem; padding: 0; margin: 0 0 1rem; }
    .mf-steps li { flex: 1; display: flex; flex-direction: column; align-items: center; text-align: center; padding: .35rem .4rem; border-radius: 10px; border: 2px solid #e5e7eb; background: #fff; color: #6b7280; min-height: 62px; }
    .mf-steps li.done { cursor: pointer; border-color: #86efac; background: #f0fdf4; color: #166534; }
    .mf-steps li.current { border-color: #a02626; color: #a02626; box-shadow: 0 0 0 3px rgba(160,38,38,.12); }
    .mf-n { width: 1.5rem; height: 1.5rem; font-size: .85rem; border-radius: 50%; background: #e5e7eb; color: #4b5563; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
    .mf-steps li.current .mf-n { background: #a02626; color: #fff; }
    .mf-steps li.done .mf-n { background: #16a34a; color: #fff; }
    .mf-l { font-weight: 700; font-size: .95rem; margin-top: .1rem; }
    .mf-v { font-family: monospace; font-weight: 800; font-size: .9rem; min-height: 1rem; }
    /* panels */
    .mf-h { font-size: 1.3rem; font-weight: 800; text-align: center; margin: 0 0 .75rem; color: #111827; }
    .mf-sub { display: block; font-size: .9rem; font-weight: 500; color: #6b7280; margin-top: .15rem; }
    .mf-tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(128px, 1fr)); gap: .55rem; }
    .mf-tile { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; background: #fff; border: 2px solid #d1d5db; border-radius: 10px; padding: .55rem .45rem; min-height: 0; cursor: pointer; color: #111827; font: inherit; transition: border-color .12s, box-shadow .12s, transform .12s; position: relative; }
    .mf-tile:hover { border-color: #a02626; box-shadow: 0 4px 12px rgba(0,0,0,.12); transform: translateY(-1px); }
    .mf-tile:focus { outline: 3px solid #a02626; outline-offset: 2px; }
    .mf-tile .mf-t1 { display: block; width: 100%; font-weight: 800; font-size: 1.15rem; color: #a02626; }
    .mf-tile .mf-t2 { display: block; width: 100%; font-size: .8rem; margin-top: .15rem; color: #4b5563; }
    .mf-tile .mf-t2 .mf-dot { margin-right: .35rem; }
    .mf-tile .mf-tag { display: inline-block; margin-top: .2rem; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; background: #fef3c7; color: #92400e; border-radius: 999px; padding: .1rem .55rem; }
    /* a candidate already examined is shown in green, not greyed out */
    .mf-tile.marked { background: #f0fdf4; border-color: #86efac; }
    .mf-tile.marked .mf-t1 { color: #166534; }
    .mf-tile.marked .mf-t2 { color: #166534; }
    .mf-tile.marked:hover { border-color: #16a34a; box-shadow: 0 4px 12px rgba(22,163,74,.18); transform: translateY(-1px); }
    .mf-review { display: inline-block; margin-top: .2rem; font-weight: 700; font-size: .85rem; color: #a02626; text-decoration: underline; }
    .mf-list .mf-review { margin: 0; }
    .mf-tile.is-picked { border-color: #a02626; box-shadow: 0 0 0 3px rgba(160,38,38,.2); }
    .mf-tile[disabled] { cursor: not-allowed; opacity: .55; transform: none; box-shadow: none; border-color: #d1d5db; background: #f3f4f6; }
    .mf-tile.s-other { border-color: #93c5fd; background: #eff6ff; }
    .mf-tile.s-mine .mf-t1, .mf-tile.s-mine .mf-t2 { color: #166534; }
    .mf-stations { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
    .mf-dot { display: inline-block; width: .65rem; height: .65rem; border-radius: 50%; background: #d1d5db; vertical-align: -1px; }
    .mf-dot.marked, .mf-dot.full { background: #16a34a; }
    /* candidate toolbar */
    .mf-bar { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; justify-content: center; margin-bottom: .4rem; }
    .mf-search { font: inherit; font-size: .95rem; padding: .4rem .75rem; border: 2px solid #d1d5db; border-radius: 8px; min-width: 200px; flex: 1 1 200px; max-width: 300px; }
    .mf-seg { display: inline-flex; border: 2px solid #d1d5db; border-radius: 8px; overflow: hidden; background: #fff; }
    .mf-seg button { font: inherit; font-size: .9rem; border: 0; background: transparent; padding: .4rem .7rem; cursor: pointer; color: #374151; }
    .mf-seg button + button { border-left: 2px solid #d1d5db; }
    .mf-seg button.on { background: #a02626; color: #fff; }
    .mf-icon { font: inherit; font-size: .9rem; border: 2px solid #d1d5db; background: #fff; border-radius: 8px; padding: .4rem .65rem; cursor: pointer; color: #a02626; }
    .mf-legend { text-align: center; font-size: .85rem; color: #4b5563; margin: .2rem 0 .6rem; }
    .mf-legend .mf-dot { margin: 0 .3rem 0 .9rem; } .mf-legend .mf-dot:first-child { margin-left: 0; }
    .mf-cands.mf-list { display: block; border: 2px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: #fff; }
    .mf-list .mf-tile { display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 1rem; width: 100%; text-align: center; border: 0; border-bottom: 1px solid #e5e7eb; border-radius: 0; min-height: 0; padding: .4rem .85rem; transform: none; }
    .mf-list .mf-tile:last-child { border-bottom: 0; }
    .mf-list .mf-tile .mf-t1 { width: auto; min-width: 110px; } .mf-list .mf-tile .mf-t2 { width: auto; margin: 0; }
    .mf-empty { text-align: center; padding: 1.2rem; color: #6b7280; grid-column: 1 / -1; }
    .mf-link { background: none; border: 0; color: #a02626; font: inherit; font-weight: 700; text-decoration: underline; cursor: pointer; padding: .4rem; }
    #mfPGroup .mf-link, #mfPStation .mf-link { display: block; margin: .6rem auto 0; font-size: .9rem; }
    /* summary once everything is chosen */
    .mf-ready { display: flex; flex-wrap: wrap; gap: .6rem; align-items: center; justify-content: center; background: #f0fdf4; border: 2px solid #86efac; border-radius: 10px; padding: .5rem .9rem; }
    .mf-ready-label { color: #166534; font-weight: 600; }
    .mf-ready-val { font-weight: 800; font-size: 1.15rem; color: #14532d; }
    .mf-warn { text-align: center; font-weight: 700; color: #b91c1c; min-height: 1.4rem; margin: .6rem 0 0; }
    .mf-note { text-align: center; color: #92400e; font-weight: 600; margin: .6rem 0 0; }
    .mf-gated, .mf [hidden] { display: none !important; }
    /* dark mode */
    body.dark-mode .mf-h { color: #f3f4f6; }
    body.dark-mode .mf-sub, body.dark-mode .mf-legend, body.dark-mode .mf-tile .mf-t2 { color: #cbd5e1; }
    body.dark-mode .mf-steps li, body.dark-mode .mf-tile, body.dark-mode .mf-seg, body.dark-mode .mf-icon, body.dark-mode .mf-search { background: #2b3035; border-color: #4b5563; color: #e5e7eb; }
    body.dark-mode .mf-steps li.done { background: #14301f; border-color: #166534; color: #86efac; }
    body.dark-mode .mf-steps li.current { border-color: #FEC503; color: #FEC503; }
    body.dark-mode .mf-steps li.current .mf-n { background: #FEC503; color: #1f2937; }
    body.dark-mode .mf-n { background: #4b5563; color: #f3f4f6; }
    body.dark-mode .mf-tile .mf-t1 { color: #FEC503; }
    body.dark-mode .mf-tile.marked { background: #14301f; border-color: #166534; }
    body.dark-mode .mf-tile.marked .mf-t1, body.dark-mode .mf-tile.marked .mf-t2 { color: #86efac; }
    body.dark-mode .mf-tile.marked:hover { border-color: #22c55e; box-shadow: 0 4px 12px rgba(34,197,94,.2); }
    body.dark-mode .mf-review { color: #FEC503; }
    body.dark-mode .mf-tile.s-other { background: #172a45; border-color: #1d4ed8; }
    body.dark-mode .mf-tile.s-mine .mf-t1, body.dark-mode .mf-tile.s-mine .mf-t2 { color: #86efac; }
    body.dark-mode .mf-tile[disabled] { background: #23272b; }
    body.dark-mode .mf-seg button { color: #e5e7eb; } body.dark-mode .mf-seg button.on { background: #FEC503; color: #1f2937; }
    body.dark-mode .mf-icon, body.dark-mode .mf-link { color: #FEC503; }
    body.dark-mode .mf-seg button + button { border-color: #4b5563; }
    body.dark-mode .mf-ready { background: #14301f; border-color: #166534; } body.dark-mode .mf-ready-label, body.dark-mode .mf-ready-val { color: #86efac; }
    body.dark-mode .mf-cands.mf-list { background: #2b3035; border-color: #4b5563; } body.dark-mode .mf-list .mf-tile { border-bottom-color: #4b5563; }
    @media (max-width: 575px) { .mf-h { font-size: 1.2rem; } .mf-tiles { grid-template-columns: repeat(2, 1fr); } .mf-stations { grid-template-columns: repeat(2, 1fr); } .mf-steps li { min-height: 56px; } }
</style>

<script>
(function () {
    var groupSel = document.getElementById('group_id');
    var candSel = document.getElementById('candidate_id');
    var stationSel = document.querySelector('select[name="station_id"]');
    var mount = document.getElementById('mf');
    if (!groupSel || !candSel || !stationSel || !mount) return;

    var CFG = {
        kind: @json($kind), format: @json($format ?? null), assigned: @json(array_map('strval', $assignedGroups ?? [])),
        examinedUrl: @json(url('examiner/examined')), reviewUrl: @json(url($kind === 'mcs' || $kind === 'gs' ? 'examiner/view_results' : 'examiner/view_fcs_results')),
        reviewNeedsStation: @json($kind === 'mcs' || $kind === 'gs'), storeKey: 'cosecsa.candidatePicker.view'
    };
    var GROUP_KEY = 'cosecsa.flow.group.' + CFG.kind + (CFG.format || '');
    var STATION_KEY = 'cosecsa.flow.station.' + CFG.kind + (CFG.format || '');
    var S = { step: 'group', list: [], marks: {}, flags: 'none', filter: 'all', q: '', view: 'tiles', cand: null, loading: false, allGroups: false, showTest: false, lastStation: null, showAllStations: false };
    try { S.view = localStorage.getItem(CFG.storeKey) === 'list' ? 'list' : 'tiles'; } catch (e) {}
    try { S.lastStation = parseInt(localStorage.getItem(STATION_KEY), 10) || null; } catch (e) {}

    function $(id) { return document.getElementById(id); }
    function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function number(c) { return c.candidate_id || c.name || ('#' + c.candidates_id); }

    // The form's own boxes stay in the page but out of sight; this flow fills them in.
    var row = groupSel.closest('.form-row');
    [groupSel, candSel, stationSel].forEach(function (s) { s.removeAttribute('required'); });
    row.insertAdjacentElement('afterend', mount);
    var gated = [];
    for (var el = mount.nextElementSibling; el; el = el.nextElementSibling) {
        if (el.tagName !== 'SCRIPT' && el.tagName !== 'STYLE') gated.push(el);
    }
    function gate(on) { gated.forEach(function (el) { el.classList.toggle('mf-gated', on); }); }

    function groupOptions() {
        return Array.prototype.filter.call(groupSel.options, function (o) { return o.value; }).map(function (o) { return { id: o.value, name: o.text.trim() }; });
    }
    function stationOptions() {
        return Array.prototype.filter.call(stationSel.options, function (o) { return o.value; }).map(function (o) { return { no: parseInt(o.value, 10), name: o.text.trim() }; });
    }
    function groupName() { var o = groupSel.options[groupSel.selectedIndex]; return o && o.value ? o.text.trim() : ''; }
    function candById(id) { return S.list.filter(function (c) { return String(c.candidates_id) === String(id); })[0]; }
    function marksOf(c) { return (c && S.marks[c.candidates_id]) || { all: [], mine: [] }; }

    function setStep(step) {
        S.step = step;
        $('mfPGroup').hidden = step !== 'group';
        $('mfPCand').hidden = step !== 'candidate';
        $('mfPStation').hidden = step !== 'station';
        $('mfReady').hidden = step !== 'mark';
        gate(step !== 'mark');
        $('mfWarn').textContent = '';
        render();
        if (step !== 'mark') mount.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // The browser's Back (and the phone swipe) walks back through the steps instead of leaving the page:
    // Candidate list > Group, Station > Candidate list... and only from the first step does it go to the
    // exam selection. Each step the examiner moves forward to is one history entry.
    var ORDER = ['group', 'candidate', 'station', 'mark'];
    function idx(step) { return ORDER.indexOf(step); }
    function stamp(step, how) { try { history[how === 'push' ? 'pushState' : 'replaceState']({ mf: step, i: idx(step) }, ''); } catch (e) {} }
    function feasible(step) {
        if (step === 'mark') return !!(groupSel.value && candSel.value && stationSel.value);
        if (step === 'station') return !!(groupSel.value && candSel.value && S.cand);
        if (step === 'candidate') return !!groupSel.value && (S.list.length > 0 || S.loading);
        return true;
    }
    function forward(step) {
        var cur = history.state && typeof history.state.i === 'number' ? history.state.i : -1;
        setStep(step); stamp(step, cur >= idx(step) ? 'replace' : 'push');   // never two entries for the same step
    }
    function backTo(step) {
        var cur = (history.state && typeof history.state.i === 'number') ? history.state.i : idx(S.step), to = idx(step);
        if (to < cur) { try { history.go(to - cur); return; } catch (e) {} }
        setStep(step);
    }
    window.addEventListener('popstate', function (e) {
        var st = e.state; if (!st || typeof st.mf !== 'string') return;
        var step = st.mf;
        while (idx(step) > 0 && !feasible(step)) step = ORDER[idx(step) - 1];
        if (step !== st.mf) stamp(step, 'replace');
        setStep(step);
    });

    // ── 1. Group ──
    // Test (sandbox) groups, named TEST-..., are hidden unless asked for; if a form has nothing but test groups they are shown.
    function isTest(g) { return /^(group\s+)?test-/i.test(g.name); }
    function renderGroups() {
        var everything = groupOptions(), real = everything.filter(function (g) { return !isTest(g); });
        var all = (S.showTest || !real.length) ? everything : real;
        var mine = all.filter(function (g) { return CFG.assigned.indexOf(g.id) !== -1; });
        var show = (mine.length && mine.length < all.length && !S.allGroups) ? mine : all;
        $('mfGroupTiles').innerHTML = show.map(function (g) {
            return '<button type="button" class="mf-tile' + (String(groupSel.value) === g.id ? ' is-picked' : '') + '" data-group="' + esc(g.id) + '"><span class="mf-t1">' + esc(g.name) + '</span>' +
                   (CFG.assigned.indexOf(g.id) !== -1 ? '<span class="mf-tag">Yours</span>' : '') + '</button>';
        }).join('') || '<div class="mf-empty">No groups are available.</div>';
        $('mfAllGroups').hidden = !(mine.length && mine.length < all.length && !S.allGroups);
        var hiddenTests = everything.length - real.length;
        $('mfTestGroups').hidden = !(real.length && hiddenTests > 0);
        $('mfTestGroups').textContent = S.showTest ? 'Hide test groups' : 'Show test groups';
    }

    // ── 2. Candidate ──
    // A candidate marked at any station (by anyone) is greyed out; the examiner's own marks can be reviewed.
    function candState(c) {
        var m = marksOf(c), n = m.all.length, t = stationOptions().length;
        if (S.flags !== 'ok') return { k: 'none', text: '', review: null };
        if (n === 0) return { k: 'none', text: 'Not marked yet', review: null };
        var text = 'Marked at ' + n + (t ? ' of ' + t : '') + ' station' + (n === 1 && !t ? '' : 's');
        if (!m.mine.length) return { k: 'marked', text: text + ' by another examiner', review: null };
        return { k: 'marked', text: text, review: m.mine[0] };
    }
    function reviewHref(c, station) {
        return CFG.reviewUrl + '/' + encodeURIComponent(c.candidates_id) + (CFG.reviewNeedsStation ? '/' + encodeURIComponent(station) : '');
    }
    function visibleCands() {
        var q = S.q.toLowerCase();
        return S.list.filter(function (c) {
            if (q && String(number(c)).toLowerCase().indexOf(q) === -1) return false;
            var k = candState(c).k;
            if (S.filter === 'todo' && k === 'marked') return false;
            if (S.filter === 'done' && k !== 'marked') return false;
            return true;
        });
    }
    function renderCands() {
        $('mfCandCount').textContent = S.loading ? 'Loading…' : (S.list.length + ' in ' + groupName());
        var grid = $('mfCandGrid');
        grid.className = 'mf-cands mf-' + S.view;
        if (S.loading) { grid.innerHTML = '<div class="mf-empty">Loading candidates…</div>'; return; }
        grid.innerHTML = visibleCands().map(function (c) {
            var st = candState(c);
            return '<div class="mf-tile' + (st.k === 'marked' ? ' marked' : '') + (S.cand && String(S.cand.candidates_id) === String(c.candidates_id) ? ' is-picked' : '') + '" role="option" tabindex="0" data-cand="' + esc(c.candidates_id) + '">' +
                   '<span class="mf-t1">' + esc(number(c)) + '</span>' + (st.text ? '<span class="mf-t2"><span class="mf-dot ' + st.k + '"></span>' + st.text + '</span>' : '') +
                   (st.review ? '<a class="mf-review" href="' + esc(reviewHref(c, st.review)) + '">Review</a>' : '') + '</div>';
        }).join('') || '<div class="mf-empty">' + (S.list.length ? 'No candidate matches.' : 'There are no candidates in this group.') + '</div>';
        $('mfFilter').querySelectorAll('button').forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-filter') === S.filter); });
        $('mfView').querySelectorAll('button').forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-view') === S.view); });
    }

    // ── 3. Station ──
    // An examiner normally works one station at a time. Once they have marked a candidate somewhere,
    // the next candidate offers only that station by default; "Show all stations" reveals the rest.
    function rememberStation(no) {
        var n = parseInt(no, 10);
        if (!n) return;
        S.lastStation = n;
        try { localStorage.setItem(STATION_KEY, String(n)); } catch (e) {}
    }
    function renderStations() {
        if (!S.cand) return;
        var m = marksOf(S.cand);
        $('mfStationFor').textContent = 'for ' + number(S.cand);
        var options = stationOptions();
        var only = null;
        if (!S.showAllStations && S.lastStation) {
            var usual = options.filter(function (s) { return s.no === S.lastStation; })[0];
            // The usual station is only offered on its own while this candidate has not been marked there yet.
            if (usual && m.mine.indexOf(usual.no) === -1) only = usual;
        }
        var show = only ? [only] : options;
        $('mfStationTiles').innerHTML = show.map(function (s) {
            var mine = m.mine.indexOf(s.no) !== -1, other = !mine && m.all.indexOf(s.no) !== -1;
            var cls = mine ? ' s-mine' : other ? ' s-other' : '';
            var sub = S.flags !== 'ok' ? '' : mine ? '<span class="mf-t2"><i class="fas fa-check"></i> You have marked this</span>' : other ? '<span class="mf-t2">Done by another examiner</span>' : '<span class="mf-t2">Not done yet</span>';
            return '<button type="button" class="mf-tile' + cls + (String(stationSel.value) === String(s.no) ? ' is-picked' : '') + '" data-station="' + s.no + '"' + (mine ? ' disabled' : '') + '><span class="mf-t1">' + esc(s.name) + '</span>' + sub + '</button>';
        }).join('') || '<div class="mf-empty">No stations are available. Please tell the examinations office.</div>';
        var all = $('mfAllStations');
        all.hidden = !only;
        all.textContent = 'Show all ' + options.length + ' stations';
    }

    function render() {
        mount.style.display = '';
        var steps = { group: groupName(), candidate: S.cand ? number(S.cand) : '', station: (stationSel.value ? (stationSel.options[stationSel.selectedIndex].text.trim()) : '') };
        $('mfVGroup').textContent = steps.group.replace(/^Group\s+/i, ''); $('mfVCand').textContent = steps.candidate; $('mfVStation').textContent = steps.station;
        var order = ['group', 'candidate', 'station'], cur = S.step === 'mark' ? 3 : order.indexOf(S.step);
        $('mfSteps').querySelectorAll('li').forEach(function (li, i) {
            li.classList.toggle('current', i === cur);
            li.classList.toggle('done', i < cur);
        });
        if (S.step === 'group') renderGroups();
        if (S.step === 'candidate') renderCands();
        if (S.step === 'station') renderStations();
        if (S.step === 'mark') $('mfReadyVal').textContent = [steps.group, steps.candidate, steps.station].join('  ›  ');
    }

    function fetchMarks() {
        if (!S.list.length) return;
        var ids = S.list.map(function (c) { return c.candidates_id; }).join(',');
        var url = CFG.examinedUrl + '/' + encodeURIComponent(CFG.kind) + '?ids=' + ids + (groupSel.value ? '&group_id=' + groupSel.value : '') + (CFG.format ? '&format=' + CFG.format : '');
        fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (d) { S.marks = d.marks || {}; S.flags = 'ok'; render(); })
            .catch(function () { S.flags = 'failed'; S.marks = {}; render(); });
    }

    // The form calls this with the candidates of the group it has just fetched.
    window.CandidatePicker = {
        load: function (candidates) {
            S.loading = false;
            S.list = Array.isArray(candidates) ? candidates.slice().sort(function (a, b) {
                return String(number(a)).localeCompare(String(number(b)), undefined, { numeric: true, sensitivity: 'base' });
            }) : [];
            S.marks = {}; S.flags = 'none'; S.cand = null; S.q = ''; S.filter = 'all'; S.showAllStations = false; $('mfSearch').value = '';
            candSel.innerHTML = '<option value="">Choose a Candidate...</option>';
            S.list.forEach(function (c) { var o = document.createElement('option'); o.value = c.candidates_id; o.textContent = number(c); candSel.appendChild(o); });
            candSel.value = ''; stationSel.value = '';
            if (S.step !== 'candidate') setStep('candidate'); else render();
            fetchMarks();
        },
        refresh: fetchMarks
    };

    // ── Events ──
    mount.addEventListener('click', function (e) {
        if (e.target.closest('.mf-review')) return;   // the Review link opens the examiner's saved marks
        var t;
        if ((t = e.target.closest('[data-group]'))) {
            groupSel.value = t.getAttribute('data-group');
            try { sessionStorage.setItem(GROUP_KEY, groupSel.value); } catch (x) {}
            candSel.value = ''; stationSel.value = ''; S.cand = null; S.list = []; S.loading = true;
            groupSel.dispatchEvent(new Event('change', { bubbles: true }));   // the form's own handler fetches the candidates
            return forward('candidate');
        }
        if (e.target.closest('#mfAllGroups')) { S.allGroups = true; return render(); }
        if (e.target.closest('#mfTestGroups')) { S.showTest = !S.showTest; return render(); }
        if ((t = e.target.closest('[data-cand]'))) {
            S.cand = candById(t.getAttribute('data-cand'));
            candSel.value = String(S.cand.candidates_id); candSel.dispatchEvent(new Event('change', { bubbles: true }));
            stationSel.value = '';
            S.showAllStations = false;
            return forward('station');
        }
        if ((t = e.target.closest('[data-station]')) && !t.disabled) {
            rememberStation(t.getAttribute('data-station'));
            stationSel.value = t.getAttribute('data-station'); stationSel.dispatchEvent(new Event('change', { bubbles: true }));
            return forward('mark');
        }
        if (e.target.closest('#mfAllStations')) { S.showAllStations = true; return render(); }
        if ((t = e.target.closest('#mfFilter button'))) { S.filter = t.getAttribute('data-filter'); return render(); }
        if ((t = e.target.closest('#mfView button'))) { S.view = t.getAttribute('data-view'); try { localStorage.setItem(CFG.storeKey, S.view); } catch (x) {} return render(); }
        if (e.target.closest('#mfRefresh')) return fetchMarks();
        if (e.target.closest('#mfChange')) return backTo('group');
        if ((t = e.target.closest('#mfSteps li.done'))) {
            var go = t.getAttribute('data-step');
            if (go === 'group') return backTo('group');
            if (go === 'candidate' && S.list.length) return backTo('candidate');
            if (go === 'station' && S.cand) return backTo('station');
        }
    });
    mount.addEventListener('keydown', function (e) {
        var t = e.target.closest('[data-cand]');
        if (t && (e.key === 'Enter' || e.key === ' ') && e.target === t) { e.preventDefault(); t.click(); }
    });
    $('mfSearch').addEventListener('input', function () { S.q = this.value.trim(); renderCands(); });
    document.addEventListener('visibilitychange', function () { if (!document.hidden && S.list.length) fetchMarks(); });

    // Everything has to be chosen before the form is sent (the boxes themselves are hidden).
    var form = groupSel.closest('form');
    if (form) form.addEventListener('submit', function (e) {
        if (!groupSel.value || !candSel.value || !stationSel.value) {
            e.preventDefault(); e.stopImmediatePropagation();
            backTo(!groupSel.value ? 'group' : !candSel.value ? 'candidate' : 'station');
            $('mfWarn').textContent = 'Please choose the group, the candidate and the station first.';
        }
    }, true);

    // After a mark is saved the form is cleared (or the page reloads): go back to this group's candidates,
    // not to the group choice, so the next candidate is one tap away.
    if (form) form.addEventListener('reset', function () {
        setTimeout(function () {
            var g = null; try { g = sessionStorage.getItem(GROUP_KEY); } catch (x) {}
            if (!g || !S.list.length) return backTo('group');
            groupSel.value = g; candSel.value = ''; stationSel.value = ''; S.cand = null;
            fetchMarks(); backTo('candidate');
        }, 0);
    });

    // Start: resume in the group used a moment ago (the page reloads after each saved mark), else step 1.
    // If anything goes wrong while starting the flow, fall back to the classic form rather than leaving
    // the page with no candidate evaluation form at all.
    try {
        gate(true);
        row.style.display = 'none';
        var saved = null; try { saved = sessionStorage.getItem(GROUP_KEY); } catch (x) {}
        if (saved && groupOptions().some(function (g) { return g.id === saved; })) {
            groupSel.value = saved; S.loading = true;
            stamp('group', 'replace'); stamp('candidate', 'push');   // Back from here goes to the group choice
            setStep('candidate');
            groupSel.dispatchEvent(new Event('change', { bubbles: true }));   // the form's own handler fetches the candidates
        } else {
            stamp('group', 'replace');
            setStep('group');
        }
    } catch (err) {
        if (window.console && console.error) console.error('Marking flow could not start; showing the classic form.', err);
        try { mount.style.display = 'none'; } catch (x) {}
        row.style.display = '';
        gate(false);
    }
})();
</script>

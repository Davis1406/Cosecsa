/* COSECSA-MIS Countries map. Vanilla JS, no build step. Needs only the JSON blob and world-map.json. */
(function () {
  'use strict';
  var NS = 'http://www.w3.org/2000/svg';
  var CONTINENTS = ['Africa', 'Asia', 'Europe', 'North America', 'South America', 'Oceania'];
  var VIEW_FOR = { 'Africa': 'africa', 'Asia': 'asia', 'Europe': 'europe', 'North America': 'namerica', 'South America': 'samerica', 'Oceania': 'oceania' };
  /* Names in the MIS that differ from the Natural Earth names in world-map.json. Prefer matching on iso2. */
  var ALIASES = { 'drc': 'Dem. Rep. Congo', 'democratic republic of the congo': 'Dem. Rep. Congo', 'congo (kinshasa)': 'Dem. Rep. Congo',
    'eswatini': 'eSwatini', 'swaziland': 'eSwatini', 'tanzania': 'Tanzania', 'south sudan': 'S. Sudan', 'central african republic': 'Central African Rep.',
    'ivory coast': "Côte d'Ivoire", 'cote d\'ivoire': "Côte d'Ivoire", 'usa': 'United States of America', 'united states': 'United States of America',
    'uk': 'United Kingdom', 'czech republic': 'Czechia', 'republic of the congo': 'Congo' };
  var norm = function (s) { return String(s || '').trim().toLowerCase(); };
  var svg = function (tag, attrs) { var el = document.createElementNS(NS, tag); for (var k in attrs) el.setAttribute(k, attrs[k]); return el; };
  var el = function (tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; };

  function init(root) {
    var data = JSON.parse(document.getElementById(root.dataset.json).textContent);
    fetch(root.dataset.geo).then(function (r) { return r.json(); }).then(function (geo) { build(root, data, geo); });
  }

  function build(root, data, geo) {
    var BASE = geo.base, CV = geo.views;
    var stage = root.querySelector('.cm-stage'), pop = root.querySelector('.cm-pop');
    var find = root.querySelector('.cm-find'), groupsEl = root.querySelector('.cm-groups');
    var state = { x: CV.africa.x, y: CV.africa.y, k: CV.africa.k, sel: null };

    /* match MIS rows to map shapes: iso2 first, then name or alias */
    var byIso = {}, byName = {};
    geo.countries.forEach(function (c) { if (c.i) byIso[c.i] = c; byName[norm(c.n)] = c; });
    var rows = data.countries.map(function (r) {
      var shape = (r.iso2 && byIso[String(r.iso2).toUpperCase()]) || byName[norm(r.name)] || byName[norm(ALIASES[norm(r.name)])];
      return Object.assign({}, r, { shape: shape || null, id: shape ? shape.n : null, continent: r.continent || (shape && (data.continents || {})[shape.i]) || 'Africa' });
    });
    var hit = {}; rows.forEach(function (r) { if (r.shape) hit[r.shape.n] = r; });
    var unmatched = rows.filter(function (r) { return !r.shape; });
    if (unmatched.length && window.console) console.warn('Countries map: no shape for', unmatched.map(function (r) { return r.name; }));

    /* SVG */
    var s = svg('svg', { 'class': 'cm-svg', preserveAspectRatio: 'xMidYMid slice', role: 'img', 'aria-label': 'Map of the COSECSA network. Drag to move. Highlighted countries have records.' });
    var shapes = {}, dots = {};
    geo.countries.forEach(function (c) {
      var p = svg('path', { d: c.d });
      var r = hit[c.n];
      if (r) { p.setAttribute('class', 'has'); p.setAttribute('tabindex', '0'); p.setAttribute('role', 'button'); p.setAttribute('aria-label', r.name); shapes[c.n] = p; }
      s.appendChild(p);
    });
    rows.forEach(function (r) {
      if (!r.shape) return;
      var d = svg('circle', { 'class': 'cm-dot', cx: r.shape.x, cy: r.shape.y, r: 3 }); dots[r.shape.n] = d; s.appendChild(d);
    });
    stage.insertBefore(s, stage.firstChild);

    function viewBox() { var w = BASE[2] / state.k, h = BASE[3] / state.k; return [state.x - w / 2, state.y - h / 2, w, h]; }
    function render() {
      var v = viewBox(); s.setAttribute('viewBox', v.map(function (n) { return n.toFixed(2); }).join(' '));
      var rad = 3.2 / Math.pow(state.k, 0.6); for (var n in dots) dots[n].setAttribute('r', rad);
      var seg = root.querySelectorAll('.cm-seg button');
      seg[0].classList.toggle('is-on', Math.abs(state.k - CV.africa.k) < .01 && Math.abs(state.x - CV.africa.x) < 1);
      seg[1].classList.toggle('is-on', state.k === 1);
      placePop(v);
    }
    function go(v) { state.x = v.x; state.y = v.y; state.k = v.k; render(); }
    function zoom(f) { state.k = Math.min(10, Math.max(1, state.k * f)); render(); }
    function clamp() { state.x = Math.min(1000, Math.max(0, state.x)); state.y = Math.min(BASE[1] + BASE[3], Math.max(BASE[1], state.y)); }

    /* popup */
    function select(name) {
      state.sel = name;
      for (var n in shapes) { shapes[n].classList.toggle('sel', n === name); dots[n].classList.toggle('sel', n === name); }
      var r = hit[name];
      if (r) {
        pop.querySelector('.cm-pop-name').textContent = r.name;
        ['hospitals', 'trainees', 'fellows', 'examiners'].forEach(function (k) { pop.querySelector('[data-k="' + k + '"]').textContent = r[k] == null ? 0 : r[k]; });
        var a = pop.querySelector('.cm-go'); a.href = r.url || '#'; a.hidden = !r.url;
      }
      pop.hidden = !r; render(); refreshChips();
    }
    function placePop(v) {
      if (!state.sel) return; var r = hit[state.sel]; if (!r) return;
      var px = Math.min(84, Math.max(16, (r.shape.x - v[0]) / v[2] * 100));
      var py = Math.min(94, Math.max(6, (r.shape.y - v[1]) / v[3] * 100));
      pop.style.left = px + '%'; pop.style.top = py + '%';
      pop.style.transform = 'translate(-50%,' + (py < 48 ? '18px' : 'calc(-100% - 18px)') + ')';
    }
    pop.querySelector('.cm-x').addEventListener('click', function () { select(null); });
    ['pointerdown', 'dblclick', 'wheel'].forEach(function (ev) { pop.addEventListener(ev, function (e) { e.stopPropagation(); }); });
    root.querySelector('.cm-zoom').addEventListener('pointerdown', function (e) { e.stopPropagation(); });

    /* pan (drag), zoom (wheel, buttons, double click), keyboard */
    var drag = null, moved = false;
    stage.addEventListener('pointerdown', function (e) { if (e.button) return; drag = { px: e.clientX, py: e.clientY, x: state.x, y: state.y }; moved = false; });
    stage.addEventListener('pointermove', function (e) {
      if (!drag) return; var dx = e.clientX - drag.px, dy = e.clientY - drag.py;
      if (!moved && Math.hypot(dx, dy) < 5) return; moved = true; stage.classList.add('is-drag');
      var u = (BASE[2] / state.k) / stage.getBoundingClientRect().width;
      state.x = drag.x - dx * u; state.y = drag.y - dy * u; clamp(); render();
    });
    var end = function () { drag = null; stage.classList.remove('is-drag'); setTimeout(function () { moved = false; }, 0); };
    stage.addEventListener('pointerup', end); stage.addEventListener('pointerleave', end);
    stage.addEventListener('wheel', function (e) { e.preventDefault(); zoom(e.deltaY < 0 ? 1.25 : 0.8); }, { passive: false });
    stage.addEventListener('dblclick', function () { zoom(1.6); });
    stage.addEventListener('keydown', function (e) {
      var st = (BASE[2] / state.k) * 0.1, m = { ArrowLeft: [-st, 0], ArrowRight: [st, 0], ArrowUp: [0, -st], ArrowDown: [0, st] };
      if (m[e.key] && e.target === stage) { state.x += m[e.key][0]; state.y += m[e.key][1]; clamp(); render(); e.preventDefault(); }
      else if (e.key === '+' || e.key === '=') zoom(1.4); else if (e.key === '-') zoom(1 / 1.4); else if (e.key === 'Escape') select(null);
    });
    s.addEventListener('click', function (e) {
      if (moved) return; var t = e.target;
      if (t.tagName === 'path' && t.classList.contains('has')) { for (var n in shapes) if (shapes[n] === t) select(n); }
      else if (t.tagName === 'circle') { for (var m in dots) if (dots[m] === t) select(m); }
      else select(null);
    });
    s.addEventListener('keydown', function (e) { if ((e.key === 'Enter' || e.key === ' ') && e.target.tagName === 'path') { e.preventDefault(); e.target.dispatchEvent(new MouseEvent('click', { bubbles: true })); } });

    root.querySelector('[data-view="africa"]').addEventListener('click', function () { go(CV.africa); });
    root.querySelector('[data-view="world"]').addEventListener('click', function () { go(CV.world); });
    root.querySelector('[data-act="in"]').addEventListener('click', function () { zoom(1.6); });
    root.querySelector('[data-act="out"]').addEventListener('click', function () { zoom(1 / 1.6); });
    root.querySelector('[data-act="reset"]').addEventListener('click', function () { go(CV.africa); });

    /* jump list grouped by continent */
    function refreshChips() { groupsEl.querySelectorAll('.cm-chip').forEach(function (c) { c.classList.toggle('is-on', c.dataset.id === state.sel); }); }
    function renderGroups() {
      var q = norm(find.value); groupsEl.textContent = '';
      CONTINENTS.forEach(function (cn) {
        var all = rows.filter(function (r) { return r.continent === cn && r.shape; });
        var list = all.filter(function (r) { return !q || norm(r.name).indexOf(q) > -1; });
        if (!all.length || !list.length) return;
        var g = el('div', 'cm-group'), b = el('button', 'cm-group-btn'); b.type = 'button'; b.setAttribute('aria-label', 'Show ' + cn + ' on the map');
        b.appendChild(el('strong', '', cn));
        var fellows = all.reduce(function (t, r) { return t + (+r.fellows || 0); }, 0);
        b.appendChild(el('small', '', all.length + (all.length === 1 ? ' country' : ' countries') + ' · ' + fellows + (fellows === 1 ? ' fellow' : ' fellows')));
        b.addEventListener('click', function () { go(CV[VIEW_FOR[cn]]); });
        var chips = el('div', 'cm-chips');
        list.forEach(function (r) {
          var c = el('button', 'cm-chip', r.name); c.type = 'button'; c.dataset.id = r.id; c.appendChild(el('em', '', r.fellows == null ? 0 : r.fellows));
          c.addEventListener('click', function () { select(r.id); });
          chips.appendChild(c);
        });
        g.appendChild(b); g.appendChild(chips); groupsEl.appendChild(g);
      });
      refreshChips();
    }
    find.addEventListener('input', renderGroups);
    renderGroups(); render();
  }

  document.querySelectorAll('.cm[data-json]').forEach(init);
})();

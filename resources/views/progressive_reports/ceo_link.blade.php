<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Secretariat Report — {{ $period->period_month->format('F Y') }}</title>
<style>
  :root { --red:#a02626; --gold:#FEC503; --ink:#2d2d2d; --muted:#6b6b6b; --line:#e3e3e3; --bg:#f4f4f4; }
  * { box-sizing:border-box; }
  body { margin:0; background:var(--bg); font-family: Arial, Helvetica, sans-serif; color:var(--ink); font-size:15px; line-height:1.5; }
  header { background:var(--red); border-bottom:4px solid var(--gold); color:#fff; }
  .wrap { max-width:1100px; margin:0 auto; padding:0 16px; }
  header .wrap { display:flex; align-items:center; gap:14px; padding-top:14px; padding-bottom:14px; }
  header img { width:48px; height:48px; }
  header .t1 { font-weight:700; font-size:1.05rem; letter-spacing:.3px; }
  header .t2 { font-size:.75rem; color:#f5c6c6; }
  h1 { font-size:1.35rem; margin:22px 0 4px; }
  .sub { color:var(--muted); margin:0 0 16px; }
  .card { background:#fff; border-radius:8px; box-shadow:0 1px 6px rgba(0,0,0,.07); margin:0 0 18px; overflow:hidden; }
  .card-h { padding:12px 18px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
  .card-h h2 { margin:0; font-size:1rem; }
  .mine { border-left:5px solid var(--red); }
  .chip { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.78rem; font-weight:700; }
  .chip.todo { background:#fff3cd; color:#7a5b00; } .chip.done { background:#d4edda; color:#1e6b34; }
  .help { margin:0; padding:12px 18px; background:#fffbea; border-bottom:1px solid var(--line); color:#5a4a00; font-size:.88rem; }
  .row-card { padding:16px 18px; border-bottom:1px solid var(--line); }
  .row-card:last-child { border-bottom:0; }
  .row-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
  .row-no { font-weight:700; color:var(--red); }
  .grid { display:grid; grid-template-columns:1fr; gap:12px; }
  @media (min-width: 820px) { .grid { grid-template-columns: 1fr 1fr 1fr; } .grid .full { grid-column: 1 / -1; } }
  label { display:block; font-size:.74rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--muted); margin-bottom:4px; }
  textarea { width:100%; border:1px solid #cfcfcf; border-radius:6px; padding:9px 10px; font:inherit; font-size:.92rem; resize:none; overflow:hidden; min-height:46px; background:#fff; }
  textarea:focus { outline:2px solid rgba(160,38,38,.35); border-color:var(--red); }
  textarea.act { font-weight:700; }
  .bar { padding:14px 18px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; background:#fafafa; border-top:1px solid var(--line); }
  button { font:inherit; cursor:pointer; border-radius:6px; padding:9px 16px; border:1px solid var(--red); }
  .btn-primary { background:var(--red); color:#fff; font-weight:700; }
  .btn-primary:disabled { opacity:.6; cursor:wait; }
  .btn-ghost { background:#fff; color:var(--red); }
  .btn-del { background:none; border:0; color:#b02a2a; padding:4px 8px; font-size:.82rem; }
  #saved { color:#1e6b34; font-size:.85rem; margin-right:10px; opacity:0; transition:opacity .25s; } #saved.on { opacity:1; }
  #err { color:#b02a2a; font-size:.85rem; margin-right:10px; display:none; }
  details { border-top:1px solid var(--line); }
  details:first-of-type { border-top:0; }
  summary { padding:12px 18px; cursor:pointer; font-weight:700; font-size:.92rem; }
  .ro-wrap { overflow-x:auto; padding:0 18px 16px; }
  table.ro { border-collapse:collapse; width:100%; min-width:680px; font-size:.84rem; }
  table.ro th, table.ro td { border:1px solid var(--line); padding:6px 8px; vertical-align:top; text-align:left; }
  table.ro th { background:#f3f3f3; }
  table.ro td.n { width:34px; text-align:center; }
  ul.b { margin:0; padding-left:18px; } ul.b li { margin:0 0 3px; }
  .foot { color:var(--muted); font-size:.78rem; text-align:center; padding:6px 0 30px; }
</style>
</head>
<body>
<header>
  <div class="wrap">
    <img src="{{ url('dist/img/Cosecsa_Logo_email.png') }}" alt="COSECSA">
    <div>
      <div class="t1">COSECSA</div>
      <div class="t2">Secretariat Monthly Report</div>
    </div>
  </div>
</header>

<div class="wrap">
  <h1>Secretariat Report — {{ $period->period_month->format('F Y') }}</h1>
  <p class="sub">{{ $ceo ? 'Hello ' . $ceo->name . '. ' : '' }}Please complete your section below. Everything you type is saved automatically; press <strong>Submit</strong> when you are done. You can come back to this link and keep editing until it expires on {{ $link->expires_at?->format('d M Y') }}.</p>

  <div class="card mine" id="mine">
    <div class="card-h">
      <h2>Your section — CEO</h2>
      <span class="chip {{ $participant->status === 'submitted' ? 'done' : 'todo' }}" id="chip">
        {{ $participant->status === 'submitted' ? 'Submitted ' . $participant->submitted_at?->format('d M Y H:i') : 'Not submitted yet' }}
      </span>
    </div>
    <p class="help">Planned Activities, Current Status and Next Steps are bullet lists: press <strong>Enter</strong> for a new point.</p>

    <div id="rows">
      @foreach($participant->tasks as $task)
        @include('progressive_reports._ceo_link_row', ['task' => $task, 'no' => $loop->iteration])
      @endforeach
    </div>
    <div id="empty" class="row-card" style="{{ $participant->tasks->isEmpty() ? '' : 'display:none;' }} color:var(--muted);">No rows yet. Use “Add a row” to start.</div>

    <div class="bar">
      <button type="button" class="btn-ghost" id="add">+ Add a row</button>
      <div>
        <span id="err"></span><span id="saved">Saved ✓</span>
        <button type="button" class="btn-primary" id="submit">{{ $participant->status === 'submitted' ? 'Submit again' : 'Submit my section' }}</button>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-h"><h2>The rest of the report (read-only)</h2><span class="sub" style="margin:0;font-size:.8rem;">Tap a name to open it</span></div>
    @foreach($period->participants as $p)
      <details>
        <summary>{{ $p->section_label }} <span style="font-weight:400;color:var(--muted);">— {{ $p->tasks->count() }} {{ \Illuminate\Support\Str::plural('row', $p->tasks->count()) }}</span></summary>
        <div class="ro-wrap">
          <table class="ro">
            <thead><tr><th>No</th><th>Activity</th><th>Planned Activities</th><th>Current Status</th><th>Next Steps</th></tr></thead>
            <tbody>
            @forelse($p->tasks as $t)
              <tr>
                <td class="n">{{ $loop->iteration }}</td>
                <td>{{ $t->activity_description }}</td>
                @foreach(['planned_activities', 'current_status', 'next_steps'] as $f)
                  <td>
                    <ul class="b">
                      @foreach(preg_split('/\r\n|\r|\n/', (string) $t->$f) as $line)
                        @if(trim(ltrim(trim($line), '❖')) !== '')<li>{{ trim(ltrim(trim($line), '❖')) }}</li>@endif
                      @endforeach
                    </ul>
                  </td>
                @endforeach
              </tr>
            @empty
              <tr><td colspan="5" style="color:var(--muted);text-align:center;">Nothing recorded.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </details>
    @endforeach
  </div>

  <p class="foot">This is a private link for you only — please do not forward it. COSECSA Management Information System.</p>
</div>

<script>
(function () {
  var base = @json(url('ceo-report/' . $token));
  var csrf = document.querySelector('meta[name=csrf-token]').content;
  var rows = document.getElementById('rows'), empty = document.getElementById('empty');
  var saved = document.getElementById('saved'), err = document.getElementById('err');
  var timers = {};

  function grow(el) { el.style.height = 'auto'; el.style.height = el.scrollHeight + 'px'; }
  function flash() { err.style.display = 'none'; saved.classList.add('on'); setTimeout(function () { saved.classList.remove('on'); }, 1500); }
  function fail(msg) { err.textContent = msg || 'Could not save. Please check your connection and try again.'; err.style.display = 'inline'; }
  function post(path, body) {
    return fetch(base + path, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify(body || {}) })
      .then(function (r) { return r.json().then(function (j) { if (!r.ok) throw new Error(j.message || 'error'); return j; }); });
  }
  function bullets(el) {
    el.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter') return;
      e.preventDefault();
      var s = el.selectionStart, ins = '\n❖ ';
      el.value = el.value.slice(0, s) + ins + el.value.slice(el.selectionEnd);
      el.selectionStart = el.selectionEnd = s + ins.length; grow(el);
    });
    el.addEventListener('focus', function () { if (!el.value) { el.value = '❖ '; el.selectionStart = el.selectionEnd = 2; grow(el); } });
  }
  function save(el) {
    var row = el.closest('.row-card'), field = el.dataset.field, body = {};
    body[field] = el.value;
    post('/tasks/' + row.dataset.id + '/update', body).then(function (j) {
      if (j.task && j.task[field] !== undefined && (j.task[field] || '') !== el.value) { el.value = j.task[field] || ''; grow(el); }
      flash();
    }).catch(function (e) { fail(); });
  }
  function wire(scope) {
    scope.querySelectorAll('textarea').forEach(function (el) {
      grow(el);
      if (el.classList.contains('bul')) bullets(el);
      el.addEventListener('input', function () { grow(el); clearTimeout(timers[el.id]); timers[el.id] = setTimeout(function () { save(el); }, 900); });
      el.addEventListener('change', function () { clearTimeout(timers[el.id]); save(el); });
    });
  }
  function renumber() { rows.querySelectorAll('.row-card').forEach(function (r, i) { r.querySelector('.row-no').textContent = 'Row ' + (i + 1); }); empty.style.display = rows.children.length ? 'none' : ''; }
  function rowHtml(id, n) {
    return '<div class="row-top"><span class="row-no">Row ' + n + '</span><button type="button" class="btn-del">Delete row</button></div>' +
      '<div class="grid"><div class="full"><label>Activity description</label><textarea class="act" data-field="activity_description" id="a' + id + '"></textarea></div>' +
      '<div><label>Planned activities</label><textarea class="bul" data-field="planned_activities" id="p' + id + '"></textarea></div>' +
      '<div><label>Current status</label><textarea class="bul" data-field="current_status" id="c' + id + '"></textarea></div>' +
      '<div><label>Next steps &amp; time frame</label><textarea class="bul" data-field="next_steps" id="n' + id + '"></textarea></div></div>';
  }

  wire(rows);
  rows.addEventListener('click', function (e) {
    if (!e.target.classList.contains('btn-del')) return;
    var row = e.target.closest('.row-card');
    if (!confirm('Delete this row?')) return;
    post('/tasks/' + row.dataset.id + '/delete').then(function () { row.remove(); renumber(); flash(); }).catch(function () { fail(); });
  });
  document.getElementById('add').addEventListener('click', function () {
    post('/tasks/add').then(function (j) {
      var d = document.createElement('div'); d.className = 'row-card'; d.dataset.id = j.task.id;
      d.innerHTML = rowHtml(j.task.id, rows.children.length + 1);
      rows.appendChild(d); wire(d); renumber(); d.querySelector('textarea').focus();
    }).catch(function () { fail(); });
  });
  document.getElementById('submit').addEventListener('click', function () {
    var btn = this; btn.disabled = true;
    // Save anything still waiting on the typing delay first.
    Object.keys(timers).forEach(function (k) { clearTimeout(timers[k]); });
    var chain = Promise.resolve();
    rows.querySelectorAll('.row-card').forEach(function (row) {
      var b = {}; row.querySelectorAll('textarea').forEach(function (el) { b[el.dataset.field] = el.value; });
      chain = chain.then(function () { return post('/tasks/' + row.dataset.id + '/update', b); });
    });
    chain.then(function () { return post('/submit'); }).then(function (j) {
      var chip = document.getElementById('chip'); chip.className = 'chip done'; chip.textContent = 'Submitted ' + j.submitted_at;
      btn.textContent = 'Submit again'; flash(); alert('Thank you — your section has been submitted.');
    }).catch(function () { fail('Could not submit. Please try again.'); }).then(function () { btn.disabled = false; });
  });
})();
</script>
</body>
</html>

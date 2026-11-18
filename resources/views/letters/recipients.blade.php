@extends('layout.app')

@section('content')
  <style>
    .rchk-filter-wrap  { position:relative; display:inline-block; }
    .rchk-filter-panel { position:absolute; top:calc(100% + 4px); left:0; z-index:1055;
                        background:#fff; border:1px solid #ced4da; border-radius:6px;
                        min-width:200px; max-width:260px; padding:8px;
                        box-shadow:0 4px 12px rgba(0,0,0,.12); }
    .rchk-list  { max-height:220px; overflow-y:auto; }
    .rchk-item  { display:flex; align-items:center; gap:6px; padding:3px 2px;
                 font-size:.82rem; font-weight:normal; cursor:pointer; white-space:nowrap; margin:0; }
    .rchk-item:hover { background:#f8f0f0; border-radius:4px; }
    .rchk-item input[type="checkbox"] { margin:0; cursor:pointer; accent-color:#a02626; }
    .rchk-footer { display:flex; justify-content:space-between; border-top:1px solid #eee;
                  margin-top:6px; padding-top:5px; font-size:.78rem; }
    .rchk-footer a { color:#6c757d; }
    .rchk-footer a:hover { color:#a02626; text-decoration:none; }
    .rchk-filter-btn { white-space:nowrap; }
    .cond-row { display:flex; flex-wrap:wrap; align-items:center; gap:.4rem; margin-top:.4rem; }
    .cond-row select, .cond-row input { width:auto; min-width:150px; }
    .cond-row .cond-op { min-width:130px; }
    .cond-del { border:0; background:transparent; color:#c0392b; cursor:pointer; padding:0 .35rem; }
    body.dark-mode .rchk-filter-panel { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .rchk-item { color:#e0e0e0 !important; }
    body.dark-mode .rchk-item:hover { background:#4a5568 !important; }
    body.dark-mode .rchk-footer { border-top-color:#4a5568 !important; }
    body.dark-mode .rchk-footer a { color:#9ca3af !important; }
  </style>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6"><h1 style="font-size:1.4rem;">{{ $header_title }}</h1></div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/letters') }}" class="btn btn-cosecsa-outline"><i class="fas fa-arrow-left mr-1"></i> Back</a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        @php
          $selectedCountryIds = array_map('strval', (array) ($filters['country_id'] ?? []));
          $selectedProgrammeIds = array_map('strval', (array) ($filters['programme_id'] ?? []));
        @endphp

        <div class="card card-outline card-secondary mb-2 shadow-sm">
          <div class="card-body py-2">
            <form method="GET" action="{{ url('admin/letters/'.$template->id.'/recipients') }}" id="recipFilterForm" class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
              <div class="d-flex align-items-center" style="gap:.4rem;">
                <label class="mb-0 font-weight-bold" style="font-size:.85rem;" for="sendTo">Send to</label>
                <select name="source" id="sendTo" class="form-control form-control-sm" style="width:auto;">
                  @foreach($sources as $key => $label)
                    <option value="{{ $key }}" {{ $source === $key ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>

              <div class="rchk-filter-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary rchk-filter-btn" data-filter="rFilterCountry">
                  Country
                  <span class="badge badge-danger rchk-badge ml-1" style="{{ count($selectedCountryIds) ? '' : 'display:none;' }}font-size:.65rem;">{{ count($selectedCountryIds) }}</span>
                  <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                </button>
                <div class="rchk-filter-panel shadow" id="rFilterCountry-panel" style="display:none;">
                  <input type="text" class="form-control form-control-sm rchk-search mb-1" placeholder="Search…" autocomplete="off">
                  <div class="rchk-list">
                    @foreach($countries as $id => $name)
                      <label class="rchk-item">
                        <input type="checkbox" name="country_id[]" value="{{ $id }}" {{ in_array((string) $id, $selectedCountryIds) ? 'checked' : '' }}>
                        {{ $name }}
                      </label>
                    @endforeach
                  </div>
                  <div class="rchk-footer">
                    <a href="#" class="rchk-select-all small">All</a>
                    <a href="#" class="rchk-clear small text-danger">Clear</a>
                  </div>
                </div>
              </div>

              <div class="rchk-filter-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary rchk-filter-btn" data-filter="rFilterProgramme">
                  Programme
                  <span class="badge badge-danger rchk-badge ml-1" style="{{ count($selectedProgrammeIds) ? '' : 'display:none;' }}font-size:.65rem;">{{ count($selectedProgrammeIds) }}</span>
                  <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                </button>
                <div class="rchk-filter-panel shadow" id="rFilterProgramme-panel" style="display:none;">
                  <input type="text" class="form-control form-control-sm rchk-search mb-1" placeholder="Search…" autocomplete="off">
                  <div class="rchk-list">
                    @foreach($programmes as $id => $name)
                      <label class="rchk-item">
                        <input type="checkbox" name="programme_id[]" value="{{ $id }}" {{ in_array((string) $id, $selectedProgrammeIds) ? 'checked' : '' }}>
                        {{ $name }}
                      </label>
                    @endforeach
                  </div>
                  <div class="rchk-footer">
                    <a href="#" class="rchk-select-all small">All</a>
                    <a href="#" class="rchk-clear small text-danger">Clear</a>
                  </div>
                </div>
              </div>

              <input type="number" name="year" class="form-control form-control-sm" style="max-width:120px;" value="{{ $filters['year'] ?? '' }}" placeholder="Year">
              <input type="text" name="search" class="form-control form-control-sm" style="max-width:200px;" value="{{ $filters['search'] ?? '' }}" placeholder="Search name…">

              <div class="form-check ml-1">
                <input type="checkbox" name="unsent_only" id="unsent_only" class="form-check-input" value="1" {{ !empty($filters['unsent_only']) ? 'checked' : '' }}>
                <label class="form-check-label" for="unsent_only" style="font-size:.85rem;">Not sent yet</label>
              </div>

              <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-filter mr-1"></i>Apply
              </button>
              <a href="{{ url('admin/letters/'.$template->id.'/recipients') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times mr-1"></i>Clear All
              </a>

              {{-- Conditions on the columns of the chosen group's table: every condition must hold --}}
              <div class="w-100 pt-2 mt-1" style="border-top:1px dashed #ddd;">
                <div class="d-flex align-items-center" style="gap:.5rem;">
                  <strong style="font-size:.85rem;"><i class="fas fa-sliders-h mr-1"></i> Only those where</strong>
                  <button type="button" class="btn btn-xs btn-outline-secondary" id="addCondition"><i class="fas fa-plus mr-1"></i>Add condition</button>
                  <small class="text-muted">all conditions must match (up to 5) · for example Admission year is 2027, Application stage is Complete, Admission letter status is Pending</small>
                </div>
                <div id="conditionRows"></div>
              </div>
            </form>
          </div>
        </div>

        {{-- Check before sending: see exactly what goes out, and try it on your own address --}}
        <div class="card card-outline card-info mb-2 shadow-sm">
          <div class="card-body py-2">
            <div class="d-flex flex-wrap align-items-center" style="gap:.6rem;">
              <strong style="font-size:.9rem;"><i class="fas fa-search mr-1"></i> Check before sending</strong>
              <label class="mb-0 small" for="previewWho">Using the details of</label>
              <select id="previewWho" class="form-control form-control-sm" style="width:auto;max-width:260px;">
                @foreach($recipients->take(500) as $r)
                  <option value="{{ $r->id }}">{{ $r->name }}</option>
                @endforeach
                <option value="">Sample details</option>
              </select>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPreview"><i class="fas fa-eye mr-1"></i> See what will be sent</button>
              <span class="text-muted">·</span>
              <label class="mb-0 small" for="testTo">Send a test to</label>
              <input type="email" id="testTo" class="form-control form-control-sm" style="width:250px;" value="{{ Auth::user()->email }}" placeholder="E-mail address">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="btnTest"><i class="fas fa-paper-plane mr-1"></i> Send test</button>
              <span id="testMsg" class="small"></span>
            </div>
            <div class="small text-muted mt-1">A test is the real e-mail and PDF, marked [TEST], sent only to the address above. Nobody on the list receives it and nothing is recorded or marked as sent.@if($recipients->count() > 500) The list above shows the first 500 people.@endif</div>
          </div>
        </div>

        <form method="POST" action="{{ url('admin/letters/'.$template->id.'/dispatch') }}" id="dispatchForm" enctype="multipart/form-data">
          @csrf
          @foreach($selectedCountryIds as $cid)
            <input type="hidden" name="country_id[]" value="{{ $cid }}">
          @endforeach
          @foreach($selectedProgrammeIds as $pid)
            <input type="hidden" name="programme_id[]" value="{{ $pid }}">
          @endforeach
          <input type="hidden" name="year" value="{{ $filters['year'] ?? '' }}">
          <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
          <input type="hidden" name="unsent_only" value="{{ !empty($filters['unsent_only']) ? '1' : '' }}">
          <input type="hidden" name="source" value="{{ $source }}">
          <input type="hidden" name="conditions_json" value="{{ json_encode(array_values($filters['conditions'] ?? [])) }}">

          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
              <h3 class="card-title mb-0">Recipients ({{ $recipients->count() }})</h3>
              <div class="d-flex align-items-center flex-wrap" style="gap:.75rem;">
                <div>
                  <label class="mr-2 mb-0" style="font-size:.85rem;">Attach extra file(s)</label>
                  <input type="file" name="attachments[]" class="d-inline-block" multiple style="font-size:.82rem;">
                </div>
                <div>
                  <label class="mr-2 mb-0" style="font-size:.85rem;">Letter date</label>
                  <input type="date" name="letter_date" class="form-control d-inline-block" style="width:170px;" value="{{ now()->format('Y-m-d') }}">
                </div>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive" style="max-height:480px; overflow-y:auto;">
                <table class="table table-striped table-sm mb-0">
                  <thead>
                    <tr>
                      <th style="width:3%;"><input type="checkbox" id="checkAll"></th>
                      <th>Name</th><th>Email</th><th>Country</th><th>Programme</th><th>Year</th><th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($recipients as $r)
                      <tr data-id="{{ $r->id }}" data-name="{{ $r->name }}" data-email="{{ $r->email }}" data-country="{{ $r->country }}" data-programme="{{ $r->programme }}" data-year="{{ $r->admission_year ?: $r->exam_year }}" data-sent="{{ $r->already_sent ? 1 : 0 }}">
                        <td><input type="checkbox" name="recipient_ids[]" value="{{ $r->id }}" class="recip-check"></td>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->email ?: '—' }}</td>
                        <td>{{ $r->country ?: '—' }}</td>
                        <td>{{ $r->programme ?: '—' }}</td>
                        <td>{{ $r->admission_year ?: ($r->exam_year ?: '—') }}</td>
                        <td>
                          @if($r->already_sent)
                            <span class="badge badge-success">Already Sent</span>
                          @else
                            <span class="badge badge-secondary">Not Sent</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                    @if($recipients->isEmpty())
                      <tr><td colspan="7" class="text-center text-muted py-3">No recipients match these filters.</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer text-right">
              <button type="button" class="btn btn-cosecsa" id="btnReview">
                <i class="fas fa-paper-plane mr-1"></i> Review &amp; Dispatch Selected
              </button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </div>

  {{-- What will be sent: the e-mail and the letter for one person --}}
  <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h5 class="modal-title"><i class="fas fa-eye mr-1"></i> What will be sent <small class="text-muted ml-2" id="pvWho"></small></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body p-0">
          <div class="px-3 pt-2 small" id="pvMeta" style="min-height:3.2em;"></div>
          <ul class="nav nav-tabs px-3 pt-1" id="pvTabs">
            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#pvEmailPane">E-mail</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#pvLetterPane">Letter (PDF)</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="pvEmailPane"><iframe id="pvEmail" title="E-mail" sandbox style="width:100%;height:62vh;border:0;"></iframe></div>
            <div class="tab-pane fade" id="pvLetterPane"><iframe id="pvLetter" title="Letter" sandbox="allow-scripts" style="width:100%;height:62vh;border:0;background:#e9ecef;"></iframe></div>
          </div>
        </div>
        <div class="modal-footer py-2"><button type="button" class="btn btn-light" data-dismiss="modal">Close</button></div>
      </div>
    </div>
  </div>

  {{-- The list that will receive the letter, for a last look --}}
  <div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h5 class="modal-title"><i class="fas fa-clipboard-check mr-1"></i> Review before sending</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p class="mb-2" id="rvSummary"></p>
          <div id="rvWarnings"></div>
          <div class="table-responsive" style="max-height:340px;overflow-y:auto;border:1px solid #eee;">
            <table class="table table-sm table-striped mb-0"><thead><tr><th>#</th><th>Name</th><th>E-mail</th><th>Country</th><th>Programme</th><th>Year</th></tr></thead><tbody id="rvRows"></tbody></table>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fas fa-arrow-left mr-1"></i> Back</button>
          <button type="button" class="btn btn-cosecsa" id="rvSend"><i class="fas fa-paper-plane mr-1"></i> <span id="rvSendLabel">Send</span></button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
// ── Conditions on the columns of the chosen group ──
(function () {
  var COLUMNS = @json($columns);
  var INITIAL = @json(array_values($filters['conditions'] ?? []));
  var form = document.getElementById('recipFilterForm'), rows = document.getElementById('conditionRows'), add = document.getElementById('addCondition');
  var n = 0, MAX = 5;
  var OPS = {
    eq: 'is', ne: 'is not', contains: 'contains', gt: 'is greater than', lt: 'is less than', empty: 'is empty', not_empty: 'is not empty'
  };
  function opsFor(col) {
    if (!col) return ['eq'];
    if (col.type === 'options') return ['eq', 'ne'];
    if (col.type === 'number' || col.type === 'date') return ['eq', 'ne', 'gt', 'lt', 'empty', 'not_empty'];
    return ['eq', 'ne', 'contains', 'empty', 'not_empty'];
  }
  function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function col(key) { return COLUMNS.filter(function (c) { return c.key === key; })[0]; }

  function valueControl(row, c, op, value) {
    var box = row.querySelector('.cond-val'), i = row.dataset.i;
    if (op === 'empty' || op === 'not_empty') { box.innerHTML = ''; return; }
    if (c && c.type === 'options' && c.options) {
      box.innerHTML = '<select name="conditions[' + i + '][value]" class="form-control form-control-sm"><option value="">Choose…</option>' +
        c.options.map(function (o) { return '<option value="' + esc(o.value) + '"' + (String(o.value) === String(value) ? ' selected' : '') + '>' + esc(o.label) + '</option>'; }).join('') + '</select>';
    } else {
      box.innerHTML = '<input name="conditions[' + i + '][value]" type="' + (c && c.type === 'number' ? 'number' : (c && c.type === 'date' ? 'date' : 'text')) + '" class="form-control form-control-sm" value="' + esc(value || '') + '" placeholder="Value">';
    }
  }
  function opsControl(row, c, op) {
    var sel = row.querySelector('.cond-op');
    sel.innerHTML = opsFor(c).map(function (o) { return '<option value="' + o + '"' + (o === op ? ' selected' : '') + '>' + OPS[o] + '</option>'; }).join('');
    if (!opsFor(c).includes(op)) sel.value = 'eq';
  }
  function addRow(c) {
    if (rows.children.length >= MAX) return;
    c = c || { field: '', op: 'eq', value: '' };
    var row = document.createElement('div'); row.className = 'cond-row'; row.dataset.i = n++;
    row.innerHTML = '<select name="conditions[' + row.dataset.i + '][field]" class="form-control form-control-sm cond-field"><option value="">Choose a column…</option>' +
      COLUMNS.map(function (x) { return '<option value="' + esc(x.key) + '"' + (x.key === c.field ? ' selected' : '') + '>' + esc(x.label) + '</option>'; }).join('') + '</select>' +
      '<select name="conditions[' + row.dataset.i + '][op]" class="form-control form-control-sm cond-op"></select><span class="cond-val"></span>' +
      '<button type="button" class="cond-del" title="Remove this condition"><i class="fas fa-times"></i></button>';
    rows.appendChild(row);
    opsControl(row, col(c.field), c.op); valueControl(row, col(c.field), c.op, c.value);
    add.disabled = rows.children.length >= MAX;
  }
  add.addEventListener('click', function () { addRow(); });
  rows.addEventListener('click', function (e) { var d = e.target.closest('.cond-del'); if (d) { d.closest('.cond-row').remove(); add.disabled = rows.children.length >= MAX; } });
  rows.addEventListener('change', function (e) {
    var row = e.target.closest('.cond-row'); if (!row) return;
    var c = col(row.querySelector('.cond-field').value);
    if (e.target.classList.contains('cond-field')) { opsControl(row, c, 'eq'); valueControl(row, c, 'eq', ''); }
    else if (e.target.classList.contains('cond-op')) { valueControl(row, c, e.target.value, ''); }
  });
  INITIAL.forEach(addRow);

  // a different group has different columns: start its conditions afresh
  document.getElementById('sendTo').addEventListener('change', function () { rows.innerHTML = ''; form.submit(); });
  // rows with no column chosen are not sent
  form.addEventListener('submit', function () {
    Array.prototype.forEach.call(rows.querySelectorAll('.cond-row'), function (r) { if (!r.querySelector('.cond-field').value) r.remove(); });
  });
})();

// ── Check before sending: preview, test, and the review of the list ──
(function () {
  var base = @json(url('admin/letters/' . $template->id));
  var csrf = @json(csrf_token());
  var form = document.getElementById('dispatchForm');
  function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function field(name) { var e = form.querySelector('[name="' + name + '"]'); return e ? e.value : ''; }
  function files() { var i = form.querySelector('input[type=file]'); return i ? Array.prototype.slice.call(i.files) : []; }

  // the person whose details are used: the one picked above (the first on the list by default), or sample details
  function whoId() {
    return document.getElementById('previewWho').value;   // empty: the sample details
  }

  // See what will be sent
  document.getElementById('btnPreview').addEventListener('click', function () {
    var btn = this, id = whoId(); btn.disabled = true;
    var q = new URLSearchParams({ source: field('source'), recipient_id: id, letter_date: field('letter_date') });
    fetch(base + '/compose?' + q.toString(), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        btn.disabled = false;
        if (!res.ok) { alert((res.d && res.d.message) || 'The preview could not be made.'); return; }
        var d = res.d, att = files().map(function (f) { return f.name; });
        document.getElementById('pvWho').textContent = d.recipient.sample ? '(sample details)' : '— ' + d.recipient.name;
        document.getElementById('pvMeta').innerHTML = 'To: <strong>' + esc(d.recipient.email || 'no e-mail address') + '</strong><br>Subject: <strong>' + esc(d.subject) + '</strong><br>Attached: <strong>' + esc(d.pdf_filename) + '</strong>' + (att.length ? ', ' + att.map(esc).join(', ') : '');
        document.getElementById('pvEmail').srcdoc = d.email_html; document.getElementById('pvLetter').srcdoc = d.letter_html;
        $('#previewModal').modal('show');
      })
      .catch(function () { btn.disabled = false; alert('No connection. The preview could not be made.'); });
  });

  // Send a test to one address
  document.getElementById('btnTest').addEventListener('click', function () {
    var btn = this, msg = document.getElementById('testMsg'), to = document.getElementById('testTo').value.trim();
    if (!to) { msg.className = 'small text-danger'; msg.textContent = 'Type the address to send the test to.'; return; }
    btn.disabled = true; msg.className = 'small text-muted'; msg.textContent = 'Sending the test…';
    var body = new FormData();
    body.append('to', to); body.append('source', field('source')); body.append('recipient_id', whoId()); body.append('letter_date', field('letter_date'));
    files().forEach(function (f) { body.append('attachments[]', f); });
    fetch(base + '/test', { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) { btn.disabled = false; msg.className = 'small ' + (res.ok ? 'text-success' : 'text-danger'); msg.textContent = (res.d && res.d.message) || (res.ok ? 'Test sent.' : 'The test could not be sent.'); })
      .catch(function () { btn.disabled = false; msg.className = 'small text-danger'; msg.textContent = 'No connection. Nothing was sent.'; });
  });

  // Review the list that will receive the letter, then send
  document.getElementById('btnReview').addEventListener('click', function () {
    var rows = Array.prototype.map.call(form.querySelectorAll('.recip-check:checked'), function (c) { return c.closest('tr'); });
    if (!rows.length) { alert('Tick the people who should receive this letter first.'); return; }
    var noEmail = rows.filter(function (r) { return !r.dataset.email; }).length, already = rows.filter(function (r) { return r.dataset.sent === '1'; }).length;
    document.getElementById('rvSummary').innerHTML = '<strong>' + rows.length + '</strong> letter' + (rows.length === 1 ? '' : 's') + ' will be sent, dated <strong>' + esc(field('letter_date')) + '</strong>' +
      (files().length ? ', each with ' + files().length + ' extra file' + (files().length === 1 ? '' : 's') + ' (' + files().map(function (f) { return esc(f.name); }).join(', ') + ')' : '') + '.';
    var warn = '';
    if (noEmail) warn += '<div class="alert alert-warning py-1 mb-2 small"><i class="fas fa-exclamation-triangle mr-1"></i><strong>' + noEmail + '</strong> of them ' + (noEmail === 1 ? 'has' : 'have') + ' no e-mail address: the letter is made and recorded but not e-mailed.</div>';
    if (already) warn += '<div class="alert alert-info py-1 mb-2 small"><i class="fas fa-info-circle mr-1"></i><strong>' + already + '</strong> already received this letter and will get it again.</div>';
    document.getElementById('rvWarnings').innerHTML = warn;
    document.getElementById('rvRows').innerHTML = rows.map(function (r, i) {
      var d = r.dataset;
      return '<tr><td>' + (i + 1) + '</td><td>' + esc(d.name) + (d.sent === '1' ? ' <span class="badge badge-success">Already sent</span>' : '') + '</td><td>' + (d.email ? esc(d.email) : '<span class="text-danger">none</span>') + '</td><td>' + esc(d.country) + '</td><td>' + esc(d.programme) + '</td><td>' + esc(d.year) + '</td></tr>';
    }).join('');
    document.getElementById('rvSendLabel').textContent = 'Send ' + rows.length + ' letter' + (rows.length === 1 ? '' : 's');
    $('#reviewModal').modal('show');
  });
  document.getElementById('rvSend').addEventListener('click', function () { this.disabled = true; form.submit(); });
})();

document.getElementById('checkAll').addEventListener('change', function () {
  document.querySelectorAll('.recip-check').forEach(cb => cb.checked = this.checked);
});

document.addEventListener('click', function (e) {
  const btn = e.target.closest('.rchk-filter-btn');
  if (btn) {
    e.stopPropagation();
    const filterId = btn.dataset.filter;
    const panel = document.getElementById(filterId + '-panel');
    document.querySelectorAll('.rchk-filter-panel').forEach(p => { if (p !== panel) p.style.display = 'none'; });
    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
    return;
  }
  if (e.target.closest('.rchk-filter-panel')) { e.stopPropagation(); return; }
  document.querySelectorAll('.rchk-filter-panel').forEach(p => p.style.display = 'none');
});

document.querySelectorAll('.rchk-search').forEach(function (input) {
  input.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    this.closest('.rchk-filter-panel').querySelectorAll('.rchk-item').forEach(function (item) {
      item.style.display = item.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
    });
  });
});

document.querySelectorAll('.rchk-select-all').forEach(function (link) {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    this.closest('.rchk-filter-panel').querySelectorAll('.rchk-item:not([style*="display: none"]) input[type="checkbox"]').forEach(cb => cb.checked = true);
  });
});
document.querySelectorAll('.rchk-clear').forEach(function (link) {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    this.closest('.rchk-filter-panel').querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
  });
});
</script>
@endpush

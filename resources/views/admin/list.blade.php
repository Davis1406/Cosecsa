@extends('layout.app')

@section('content')
  <style>
    .admin-list-table .action-btn { padding:2px 8px; line-height:1.4; border-radius:4px; }
    .admin-list-table .action-btn:hover { background-color:#f0f0f0; }
    .admin-list-table .dropdown-menu { min-width:170px; font-size:.875rem; }
    .admin-list-table .dropdown-item { padding:6px 14px; }
    .admin-list-table .dropdown-item:hover { background-color:#f8f0f0; }
    body.dark-mode .admin-list-table .action-btn:hover { background-color:#4a5568 !important; }
    body.dark-mode .admin-list-table .dropdown-item:hover { background-color:#4a5568 !important; color:#fff !important; }
    .ud-tabs .nav-link { color:#a02626; font-weight:600; }
    .ud-tabs .nav-link.active { border-top:3px solid #FEC503; color:#333; }
    .ud-tabs .badge { background:#f1f1f1; color:#555; font-weight:600; }
    .ud-flag { display:inline-block; font-size:.72rem; padding:1px 7px; border-radius:10px; margin:0 3px 2px 0; }
    .ud-flag.warn { background:#fff3cd; color:#7a5b00; } .ud-flag.info { background:#e7f1ff; color:#1d4f91; }
    .ud-login { border:1px solid #e3e3e3; border-radius:6px; padding:8px 10px; margin-bottom:6px; }
    .ud-login.mismatch { background:#fff8e1; border-color:#f0d68a; }
    #udModal .modal-header { background:#a02626; color:#fff; border-bottom:4px solid #FEC503; }
    #udModal .close { color:#fff; opacity:.9; }
  </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{url('admin/roles/list')}}" class="btn btn-outline-secondary">Roles & Permissions</a>
            <a href="{{url('admin/add')}}" class="btn btn-primary" style="background-color:#FEC503;border-color:#FEC503;">Add New Admin</a>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <ul class="nav nav-tabs ud-tabs mb-3" id="udTabs">
          <li class="nav-item"><a class="nav-link active" href="#" data-type="1">Secretariat <span class="badge">{{ $getRecord->total() }}</span></a></li>
          @foreach($directoryTabs->where('type', '!=', 1) as $tab)
            <li class="nav-item"><a class="nav-link" href="#" data-type="{{ $tab['type'] }}">{{ \Illuminate\Support\Str::plural($tab['label']) }} <span class="badge">{{ number_format($tab['count']) }}</span></a></li>
          @endforeach
        </ul>
        @if(($directorySummary['with_different_passwords'] ?? 0) > 0)
          <p class="text-muted" style="font-size:.85rem;">
            <i class="fas fa-info-circle"></i>
            {{ $directorySummary['shared_email_people'] ?? 0 }} people have more than one login on the same email;
            <strong>{{ $directorySummary['with_different_passwords'] }}</strong> of them have a different password on each login.
            Open a person with <em>Edit login</em> to give all their logins one password.
          </p>
        @endif
      </div>

      <div id="pane-secretariat">
      <!-- General form elements -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Search Admin</h3>
        </div>
        <form method="get" action="">
          <div class="card-body">
            <div class="row">
              <div class="form-group col-md-3">
                <label>Name</label>
                <input type="text" class="form-control" value="{{ Request::get('name') }}" name="name" placeholder="Enter Name">
              </div>
              <div class="form-group col-md-3">
                <label>Email address</label>
                <input type="email" class="form-control" value="{{ Request::get('email') }}" name="email" placeholder="Enter email">
              </div>
              <div class="form-group col-md-3">
                <label>Date</label>
                <input type="date" class="form-control" value="{{ Request::get('date') }}" name="date">
              </div>
              <div class="form-group col-md-3 d-flex align-items-end">
                <button class="btn btn-primary mr-2" style="background-color:#FEC503;border-color:#FEC503;" type="submit">Search</button>
                <a href="{{ url('admin/list') }}" class="btn" style="background-color:#a02626;border-color:#a02626;color:#fff;">Clear</a>
              </div>
            </div>
          </div>
        </form>
      </div>
  
      <div class="container-fluid">
        <div class="row">     
          <div class="col-md-12">
            @include('_message')
            <!-- Admins List Card -->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Admins List (Total : {{ $getRecord->total() }})</h3>
              </div>
              <!-- Card body with responsive table -->
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped admin-list-table">
                    <thead>
                      <tr>
                        <th style="width: 10px">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created Date</th>
                        <th class="text-center">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($getRecord as $value)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $value->name }}</td>
                          <td>{{ $value->email }}</td>
                          <td>{{ $value->adminRole->name ?? 'Super Admin' }}</td>
                          <td>{{ date('d-m-y H:i A', strtotime($value->created_at)) }}</td>
                          <td class="text-center" style="white-space:nowrap;">
                            <div class="dropdown">
                              <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                      type="button" data-toggle="dropdown"
                                      aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                              </button>
                              <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                <a class="dropdown-item" href="{{ url('admin/edit/'.$value->id) }}">
                                  <i class="fas fa-edit text-warning mr-2"></i> Edit
                                </a>
                                @if(Auth::user()->hasPermission('admin_users.manage'))
                                  <a class="dropdown-item ud-open" href="#" data-id="{{ $value->id }}">
                                    <i class="fas fa-key text-secondary mr-2"></i> Edit login (name, email, password)
                                  </a>
                                @endif
                                @if($value->id != Auth::id() && Auth::user()->hasPermission('admin_users.manage'))
                                  <a class="dropdown-item" href="{{ url('admin/impersonate/'.$value->id) }}"
                                     onclick="return confirm('Log in as this user? You can return to your admin account anytime from the banner at the top of the page.')">
                                    <i class="fas fa-user-secret text-info mr-2"></i> Login as User
                                  </a>
                                @endif
                                @if(Auth::user()->isSuperAdmin())
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item text-danger" href="{{ url('admin/delete/'.$value->id) }}"
                                     onclick="return confirm('Delete this admin account?')">
                                    <i class="fas fa-trash mr-2"></i> Delete
                                  </a>
                                @endif
                              </div>
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-end p-2">
                  {!! $getRecord->appends(Illuminate\Support\Facades\Request::except('page'))->links() !!}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /.container-fluid -->
      </div><!-- /#pane-secretariat -->

      <div id="pane-directory" style="display:none;">
        <div class="container-fluid">
          @include('_message')
          <div class="card">
            <div class="card-header d-flex align-items-center flex-wrap" style="gap:12px;">
              <h3 class="card-title mb-0" id="udTitle">Users</h3>
              <form id="udSearch" class="form-inline ml-auto" onsubmit="return false;">
                <input type="search" id="udQ" class="form-control form-control-sm mr-2" placeholder="Search name or email" style="min-width:240px;">
                <button class="btn btn-sm btn-primary" style="background-color:#FEC503;border-color:#FEC503;" type="submit">Search</button>
              </form>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped mb-0" id="udTable">
                  <thead><tr><th style="width:10px">#</th><th>Name</th><th>Email</th><th style="white-space:nowrap;">Logins / roles</th><th>Check</th><th class="text-center" style="white-space:nowrap;">Action</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between flex-wrap" style="gap:8px;">
              <span class="text-muted" id="udCount" style="font-size:.85rem;"></span>
              <div>
                <button class="btn btn-sm btn-outline-secondary" id="udPrev">&laquo; Previous</button>
                <span class="mx-2" id="udPage" style="font-size:.85rem;"></span>
                <button class="btn btn-sm btn-outline-secondary" id="udNext">Next &raquo;</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
@endsection

@push('scripts')
<div class="modal fade" id="udModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0" id="udModalTitle">Edit login</h5>
          <small id="udModalSub"></small>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div id="udLoginsBox" class="mb-3">
          <label class="font-weight-bold mb-1" id="udLoginsLabel">Logins of this person</label>
          <p class="text-muted mb-2" style="font-size:.82rem;" id="udLoginsHelp"></p>
          <div id="udLogins"></div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6"><label class="font-weight-bold">Full name</label><input type="text" id="udName" class="form-control" maxlength="255"></div>
          <div class="form-group col-md-6"><label class="font-weight-bold">Email (login)</label><input type="email" id="udEmail" class="form-control" maxlength="255"></div>
        </div>
        <p class="text-muted" style="font-size:.8rem;margin-top:-8px;" id="udNameNote"></p>
        <div class="form-group mb-2">
          <label class="font-weight-bold d-block">New password <small class="text-muted font-weight-normal">(leave empty to keep the current one)</small></label>
          <div class="input-group" style="max-width:460px;">
            <input type="text" id="udPwText" class="form-control" autocomplete="new-password" placeholder="Type the new password (at least 6 characters)">
            <div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="udGen">Generate</button></div>
          </div>
          <div id="udPwFromRow" class="mt-2" style="display:none;">
            <small class="text-muted">Or, instead of typing one, give all ticked logins the same password as:</small>
            <select id="udPwFromSel" class="form-control form-control-sm d-inline-block ml-1" style="width:auto;"></select>
          </div>
        </div>
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="udMust"><label class="custom-control-label" for="udMust">Ask them to change the password at next login</label>
        </div>
        <div id="udModalErr" class="alert alert-danger mt-3 mb-0" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="udSave" style="background:#a02626;border-color:#a02626;">Save changes</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var base = @json(url('admin/list/users'));
  var csrf = @json(csrf_token());
  var meIsSuper = @json((bool) Auth::user()->isSuperAdmin());
  var canManage = @json((bool) Auth::user()->hasPermission('admin_users.manage'));
  var state = { type: 7, q: '', page: 1, last: 1 };
  var current = null;

  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function api(url, opts) {
    opts = opts || {}; opts.headers = Object.assign({ 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, opts.headers || {});
    return fetch(url, opts).then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { if (!r.ok) { var e = new Error((j && (j.message || (j.errors && Object.values(j.errors)[0][0]))) || 'Something went wrong'); throw e; } return j; }); });
  }

  // ── tabs ──
  document.querySelectorAll('#udTabs .nav-link').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelectorAll('#udTabs .nav-link').forEach(function (x) { x.classList.remove('active'); });
      a.classList.add('active');
      var type = parseInt(a.dataset.type, 10);
      document.getElementById('pane-secretariat').style.display = type === 1 ? '' : 'none';
      document.getElementById('pane-directory').style.display = type === 1 ? 'none' : '';
      if (type !== 1) { state.type = type; state.page = 1; state.q = ''; document.getElementById('udQ').value = ''; document.getElementById('udTitle').textContent = a.textContent.replace(/\s*\d[\d,]*\s*$/, '').trim(); load(); }
    });
  });

  function load() {
    var qs = new URLSearchParams({ type: state.type, q: state.q, page: state.page, per_page: 25 });
    var tb = document.querySelector('#udTable tbody'); tb.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Loading…</td></tr>';
    api(base + '?' + qs).then(function (res) {
      state.last = res.last_page;
      document.getElementById('udCount').textContent = res.total.toLocaleString() + ' login' + (res.total === 1 ? '' : 's');
      document.getElementById('udPage').textContent = 'Page ' + res.page + ' of ' + res.last_page;
      document.getElementById('udPrev').disabled = res.page <= 1; document.getElementById('udNext').disabled = res.page >= res.last_page;
      if (!res.data.length) { tb.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">No users found.</td></tr>'; return; }
      var n = (res.page - 1) * res.per_page;
      tb.innerHTML = res.data.map(function (r, i) {
        var flags = '';
        if (r.logins > 1) flags += '<span class="ud-flag info">' + r.logins + ' logins on this email</span>';
        if (r.passwords_differ) flags += '<span class="ud-flag warn">passwords differ</span>';
        if (r.names_differ) flags += '<span class="ud-flag warn">names differ</span>';
        if (r.must_change_password) flags += '<span class="ud-flag info">must change password</span>';
        return '<tr><td>' + (n + i + 1) + '</td><td>' + esc(r.name) + '</td><td>' + esc(r.email) + '</td><td>' + esc(r.roles.join(', ')) + '</td><td>' + (flags || '<span class="text-muted">—</span>') + '</td>' +
          '<td class="text-center" style="white-space:nowrap;">' + (canManage ? '<a href="#" class="btn btn-sm btn-light border ud-open" data-id="' + r.id + '"><i class="fas fa-key text-secondary"></i> Edit login</a>' : '') + '</td></tr>';
      }).join('');
    }).catch(function (e) { tb.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-3">' + esc(e.message) + '</td></tr>'; });
  }
  document.getElementById('udSearch').addEventListener('submit', function () { state.q = document.getElementById('udQ').value.trim(); state.page = 1; load(); });
  document.getElementById('udPrev').addEventListener('click', function () { if (state.page > 1) { state.page--; load(); } });
  document.getElementById('udNext').addEventListener('click', function () { if (state.page < state.last) { state.page++; load(); } });

  // ── edit modal ──
  document.addEventListener('click', function (e) {
    var a = e.target.closest('.ud-open'); if (!a) return;
    e.preventDefault(); openModal(a.dataset.id);
  });

  function setErr(msg) { var b = document.getElementById('udModalErr'); b.style.display = msg ? '' : 'none'; b.textContent = msg || ''; }

  function openModal(id) {
    setErr(''); current = null;
    $('#udModal').modal('show');
    document.getElementById('udLogins').innerHTML = '<div class="text-muted">Loading…</div>';
    api(base + '/' + id).then(function (d) {
      current = d;
      document.getElementById('udModalTitle').textContent = d.user.name;
      document.getElementById('udModalSub').textContent = d.user.type_label + ' login · ' + d.user.email;
      document.getElementById('udName').value = d.user.name;
      document.getElementById('udEmail').value = d.user.email;
      document.getElementById('udPwText').value = '';
      document.getElementById('udMust').checked = !!(d.logins.find(function (l) { return l.id === d.user.id; }) || {}).must_change_password;

      var many = d.logins.length > 1;
      document.getElementById('udLoginsLabel').textContent = many ? 'This person has ' + d.logins.length + ' logins on this email' : 'Login';
      document.getElementById('udLoginsHelp').textContent = many
        ? 'Changes are applied to every ticked login, so the name and password stay the same everywhere. Logins with a different name are left unticked: tick them only if they really are the same person.'
        : 'Only this login uses this email.';
      document.getElementById('udLogins').innerHTML = d.logins.map(function (l) {
        var isThis = l.id === d.user.id, locked = l.protected && !meIsSuper;
        var checked = isThis || (l.name_compatible && !locked);
        return '<label class="ud-login d-block mb-1' + (l.name_compatible ? '' : ' mismatch') + '"><input type="checkbox" class="ud-apply mr-2" value="' + l.id + '"' + (checked ? ' checked' : '') + (isThis || locked ? ' disabled' : '') + '>' +
          '<strong>' + esc(l.type_label) + '</strong>' + (l.roles.length > 1 ? ' <span class="text-muted">(' + esc(l.roles.join(', ')) + ')</span>' : '') + ' — ' + esc(l.name) +
          (isThis ? ' <span class="ud-flag info">this login</span>' : '') +
          (l.same_password ? '' : ' <span class="ud-flag warn">different password</span>') +
          (l.name_compatible ? '' : ' <span class="ud-flag warn">different name</span>') +
          (l.protected ? ' <span class="ud-flag info">Super Admin</span>' : '') + '</label>';
      }).join('');

      var recs = d.records || [];
      document.getElementById('udNameNote').textContent = recs.length
        ? 'Changing the name also updates the first / middle / last name on ' + recs.map(function (r) { var t = r.table.replace(/s$/, ''); return t.charAt(0).toUpperCase() + t.slice(1) + ' record #' + r.id; }).join(', ') + '.'
        : '';
      var sel = document.getElementById('udPwFromSel');
      sel.innerHTML = '<option value="">— no, keep as is —</option>' + d.logins.map(function (l) { return '<option value="' + l.id + '">' + esc(l.type_label) + ' login' + (l.id === d.user.id ? ' (this one)' : '') + '</option>'; }).join('');
      document.getElementById('udPwFromRow').style.display = many ? '' : 'none';
    }).catch(function (e) { setErr(e.message); document.getElementById('udLogins').innerHTML = ''; });
  }

  // The same generated password the Draft Email sends: 6 characters, no look-alikes (0/O, 1/l/I).
  document.getElementById('udGen').addEventListener('click', function () {
    api(base + '/new-password').then(function (r) { document.getElementById('udPwText').value = r.password; }).catch(function (e) { setErr(e.message); });
  });

  document.getElementById('udSave').addEventListener('click', function () {
    if (!current) return;
    var btn = this, body = { apply_to: [].map.call(document.querySelectorAll('.ud-apply:checked'), function (c) { return parseInt(c.value, 10); }) };
    var name = document.getElementById('udName').value.trim(), email = document.getElementById('udEmail').value.trim();
    if (!name || !email) { setErr('Name and email cannot be empty.'); return; }
    if (name !== String(current.user.name || '').trim()) body.name = name;
    if (email !== current.user.email) body.email = email;
    var pw = document.getElementById('udPwText').value;
    if (pw !== '') {
      if (pw.length < 6) { setErr('The password must be at least 6 characters.'); return; }
      body.password = pw;
    } else if (document.getElementById('udPwFromSel').value) { body.password_from = parseInt(document.getElementById('udPwFromSel').value, 10); }
    body.must_change_password = document.getElementById('udMust').checked;
    var applying = body.apply_to.length + (body.apply_to.indexOf(current.user.id) === -1 ? 1 : 0);
    var what = [body.name ? 'name' : null, body.email ? 'email' : null, (body.password || body.password_from) ? 'password' : null].filter(Boolean);
    if (!what.length && body.must_change_password === !!(current.logins.find(function (l) { return l.id === current.user.id; }) || {}).must_change_password) { setErr('Nothing to change yet: type a new password (or change the name or email) first.'); return; }
    var go = function () {
      btn.disabled = true; setErr('');
      api(base + '/' + current.user.id, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) }).then(function (res) {
        var s = res.summary || {};
        $('#udModal').modal('hide');
        if (window.CosecsaAlert && CosecsaAlert.toast) CosecsaAlert.toast('Saved on ' + s.logins + ' login' + (s.logins === 1 ? '' : 's') + (s.records ? ' and ' + s.records + ' record(s)' : '') + '.', 'success'); else alert('Saved.');
        if (document.getElementById('pane-directory').style.display !== 'none') load(); else location.reload();
      }).catch(function (e) { setErr(e.message); }).then(function () { btn.disabled = false; });
    };
    if (what.length && window.CosecsaAlert && CosecsaAlert.confirm) {
      CosecsaAlert.confirm('Change the ' + what.join(' and ') + ' on ' + applying + ' login' + (applying === 1 ? '' : 's') + '?').then(function (ok) { if (ok) go(); });
    } else { go(); }
  });
})();
</script>
@endpush


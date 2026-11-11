@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 style="font-size:1.4rem;">Exam Groups &amp; Stations</h1>
                    <p class="text-muted mb-0" style="font-size:.85rem;">
                        Manage the exam groups (their candidates and examiners for {{ $year }}) and the stations the examiner forms offer.
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ url('admin/exams/examiners') }}" class="btn btn-cosecsa-dark"><i class="fas fa-arrow-left mr-1"></i> Examiners</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('_message')
            <div id="esAlert"></div>

            <div class="card mb-3" style="border-left:4px solid #a02626;">
                <div class="card-body py-2 d-flex flex-wrap align-items-center" style="gap:.75rem;">
                    <div>
                        <strong><i class="fas fa-flag-checkered mr-1" style="color:#a02626;"></i> Current exam</strong>
                        <div class="text-muted" style="font-size:.8rem;">Examiners land on their dashboard with this exam ready to mark.</div>
                    </div>
                    <select id="currentExam" class="form-control form-control-sm ml-auto" style="width:auto;min-width:260px;">
                        @foreach($types as $t)
                            <option value="{{ $t['key'] }}" {{ ($current_exam ?? 'mcs') === $t['key'] ? 'selected' : '' }}>{{ $t['label'] }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-cosecsa" id="btnSaveCurrentExam"><i class="fas fa-save mr-1"></i> Save</button>
                </div>
            </div>

            {{-- ───────── Exam control: examiners cannot select the exam or save marks until it is started ───────── --}}
            @php $ec = $exam_control ?? null; @endphp
            @if($ec && ($ec['guarded'] ?? false))
            <div class="card mb-3" id="ecCard" style="border-left:4px solid {{ $ec['open'] ? '#28a745' : '#f0ad4e' }};">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-center" style="gap:.75rem;">
                        <div>
                            <strong><i class="fas fa-play-circle mr-1" style="color:#a02626;"></i> Start the exam — {{ $ec['label'] }} {{ $ec['year'] }}</strong>
                            <div id="ecStatus" style="font-size:.9rem;"></div>
                        </div>
                        <div class="ml-auto d-flex flex-wrap align-items-center" style="gap:.5rem;">
                            <button class="btn btn-cosecsa" id="btnExamStart"><i class="fas fa-play mr-1"></i> Start the exam</button>
                            <button class="btn btn-outline-secondary" id="btnExamLock"><i class="fas fa-lock mr-1"></i> Lock marking again</button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center mt-2" style="gap:.5rem;">
                        <label class="mb-0 mr-1" style="font-size:.85rem;" for="ecWhen">Exam starts at</label>
                        <input type="datetime-local" id="ecWhen" class="form-control form-control-sm" style="width:auto;" value="{{ !empty($ec['scheduled_for']) ? str_replace(' ', 'T', $ec['scheduled_for']) : '' }}">
                        <button class="btn btn-sm btn-outline-secondary" id="btnExamWhen">Save time</button>
                        <span class="text-muted" id="ecCountdown" style="font-size:.85rem;"></span>
                    </div>
                    <div class="text-muted mt-2" style="font-size:.8rem;">
                        Until the exam is started, examiners see a waiting screen and cannot select the exam or save marks (practice on the TEST groups stays open).
                        Start it about <strong>5 minutes before</strong> the exam begins. If you lock it again, marks saved on a phone without a connection wait and are saved once the exam is started.
                    </div>
                </div>
            </div>
            @elseif($ec)
            <div class="alert alert-light border mb-3" style="font-size:.85rem;"><i class="fas fa-info-circle mr-1"></i> The start control covers MCS. The current exam ({{ $ec['label'] }}) is open to examiners.</div>
            @endif

            <ul class="nav nav-tabs mb-3" id="esTabs">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#esGroups"><i class="fas fa-users mr-1"></i> Groups</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#esStations"><i class="fas fa-clipboard-list mr-1"></i> Stations</a></li>
            </ul>

            <div class="tab-content">
                {{-- ───────── Groups ───────── --}}
                <div class="tab-pane fade show active" id="esGroups">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h3 class="card-title mb-0">Groups <small class="text-muted ml-1">candidates and examiners for {{ $year }}</small></h3>
                            <div class="custom-control custom-switch ml-auto mr-3" title="Test groups (TEST-...) are sandboxes used to try the examiner forms">
                                <input type="checkbox" class="custom-control-input" id="showTestGroups">
                                <label class="custom-control-label small" for="showTestGroups">Show test groups <span class="text-muted" id="testGroupCount"></span></label>
                            </div>
                            <button class="btn btn-sm btn-cosecsa" id="btnAddGroup"><i class="fas fa-plus mr-1"></i> Add group</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0" id="groupsTable" style="font-size:.9rem;">
                                <thead>
                                    <tr><th>Group</th><th class="text-right">MCS candidates</th><th class="text-right">FCS candidates</th><th class="text-right">Examiners</th><th class="text-right" style="width:230px;">Actions</th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ───────── Stations ───────── --}}
                <div class="tab-pane fade" id="esStations">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
                            <h3 class="card-title mb-0">Stations</h3>
                            <select id="stationType" class="form-control form-control-sm ml-2" style="width:auto;">
                                @foreach($types as $t)
                                    <option value="{{ $t['key'] }}">{{ $t['label'] }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-cosecsa ml-auto" id="btnAddStation"><i class="fas fa-plus mr-1"></i> Add station</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0" id="stationsTable" style="font-size:.9rem;">
                                <thead>
                                    <tr><th style="width:70px;">No.</th><th>Name</th><th class="text-right">Max marks</th><th>Questions</th><th class="text-right">Marks saved {{ $year }}</th><th>On forms</th><th class="text-right" style="width:140px;">Actions</th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="card-footer text-muted" style="font-size:.8rem;">
                            The station list and the maximum marks are what the examiner forms offer. Questions are optional: leave them empty and the examiner enters the marks freely (up to the maximum).
                            A station with saved marks cannot be deleted; switch it off instead.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Group modal: add / rename --}}
<div class="modal fade" id="groupModal" tabindex="-1">
    <div class="modal-dialog modal-sm"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="groupModalTitle">Add group</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <label for="groupName">Group name</label>
            <input type="text" id="groupName" class="form-control" maxlength="40" placeholder="e.g. Q">
            <small class="text-muted">Names starting with TEST- are reserved for sandbox groups.</small>
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-cosecsa" id="btnSaveGroup">Save</button></div>
    </div></div>
</div>

{{-- Manage group modal --}}
<div class="modal fade" id="manageModal" tabindex="-1">
    <div class="modal-dialog modal-xl"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Group <span id="mgName"></span> <small class="text-muted" id="mgYear"></small></h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <ul class="nav nav-pills mb-3">
                <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#mgExaminers">Examiners <span class="badge badge-light" id="mgExCount">0</span></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#mgCandidates">Candidates <span class="badge badge-light" id="mgCandCount">0</span></a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="mgExaminers">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>In this group</h6>
                            <table class="table table-sm" id="mgExTable"><tbody></tbody></table>
                        </div>
                        <div class="col-md-6">
                            <h6>Add examiners</h6>
                            <input type="text" id="mgExSearch" class="form-control form-control-sm mb-1" placeholder="Search name or Examiner ID…">
                            <select id="mgExAvail" class="form-control" multiple size="10"></select>
                            <button class="btn btn-sm btn-cosecsa mt-2" id="btnAddExaminers"><i class="fas fa-user-plus mr-1"></i> Add selected</button>
                            <small class="text-muted d-block mt-1">Hold Ctrl or Cmd to pick several. Only active examiners are listed.</small>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="mgCandidates">
                    <div class="d-flex flex-wrap align-items-center mb-2" style="gap:.5rem;">
                        <span class="text-muted small">Candidates of {{ $year }} in this group. Tick some to move them to another group.</span>
                        <select id="mgMoveTo" class="form-control form-control-sm ml-auto" style="width:auto;"></select>
                        <button class="btn btn-sm btn-cosecsa" id="btnMove"><i class="fas fa-exchange-alt mr-1"></i> Move selected</button>
                    </div>
                    <div style="max-height:50vh;overflow:auto;">
                        <table class="table table-sm table-hover" id="mgCandTable">
                            <thead><tr><th style="width:30px;"><input type="checkbox" id="mgCandAll"></th><th>Candidate no.</th><th>PEN</th><th>Programme</th><th>Session</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div></div>
</div>

{{-- Station modal --}}
<div class="modal fade" id="stationModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="stationModalTitle">Add station</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group col-md-3"><label>Station no.</label><input type="number" id="stNumber" class="form-control" min="1" max="99"></div>
                <div class="form-group col-md-6"><label>Name</label><input type="text" id="stName" class="form-control" maxlength="150" placeholder="e.g. Station 9 - Resuscitation"></div>
                <div class="form-group col-md-3"><label>Max marks</label><input type="number" id="stMax" class="form-control" min="0" step="0.5" placeholder="no limit"></div>
            </div>
            <div class="custom-control custom-switch mb-3">
                <input type="checkbox" class="custom-control-input" id="stActive" checked>
                <label class="custom-control-label" for="stActive">Show on the examiner forms</label>
            </div>
            <div class="d-flex align-items-center mb-1">
                <strong>Questions</strong> <span class="text-muted small ml-2">(optional: leave empty for free entry)</span>
                <button type="button" class="btn btn-sm btn-outline-secondary ml-auto" id="btnAddQuestion"><i class="fas fa-plus mr-1"></i> Add question</button>
            </div>
            <div id="stQuestions"></div>
            <small class="text-muted" id="stSum"></small>
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-cosecsa" id="btnSaveStation">Save station</button></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var base = @json(url('admin/exams/setup'));
    var csrf = $('meta[name="csrf-token"]').attr('content') || @json(csrf_token());
    var yearName = @json($year);
    var groups = @json($groups);
    var manageId = null, manageData = null, groupEditId = null, stationEditId = null, showTest = false;

    function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    function say(msg, ok) {
        var el = $('<div class="alert alert-' + (ok === false ? 'danger' : 'success') + ' alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>' + esc(msg) + '</div>');
        $('#esAlert').empty().append(el); setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 6000);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function api(method, url, data) {
        return $.ajax({ url: base + url, method: method, data: data ? JSON.stringify(data) : undefined, contentType: 'application/json', dataType: 'json',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    }
    function fail(x) { say((x.responseJSON && x.responseJSON.message) || 'Something went wrong. Nothing was changed.', false); }

    // ───── Start the exam ─────
    var examControl = @json($exam_control ?? null);
    function fmtTime(t) { return t ? t.replace('T', ' ').slice(0, 16) : ''; }
    function renderExamControl() {
        if (!examControl || !examControl.guarded) return;
        $('#ecCard').css('border-left-color', examControl.open ? '#28a745' : '#f0ad4e');
        $('#ecStatus').html(examControl.open
            ? '<span class="badge badge-success">Started</span> ' + esc(fmtTime(examControl.started_at)) + (examControl.started_by_name ? ' by ' + esc(examControl.started_by_name) : '') + ' — examiners can select groups and enter marks.'
            : '<span class="badge badge-warning">Not started</span> Examiners see the waiting screen and cannot save marks yet.');
        $('#btnExamStart').toggle(!examControl.open); $('#btnExamLock').toggle(!!examControl.open);
        var cd = '';
        if (!examControl.open && examControl.scheduled_for) {
            var mins = Math.round((new Date(examControl.scheduled_for.replace(' ', 'T')) - new Date()) / 60000);
            cd = mins > 0 ? 'Starts in ' + (mins >= 60 ? Math.floor(mins / 60) + ' h ' + (mins % 60) + ' min' : mins + ' min') + (mins <= 5 ? ' — start it now' : (mins <= 15 ? ' — start it in ' + (mins - 5) + ' min' : '')) : 'The start time has passed — start the exam.';
        }
        $('#ecCountdown').text(cd);
    }
    function examAction(body, confirmMsg) {
        var go = function () { api('POST', '/exam-control', body).done(function (r) { examControl = r; renderExamControl(); say(r.message); }).fail(fail); };
        if (confirmMsg && window.CosecsaAlert && CosecsaAlert.confirm) { CosecsaAlert.confirm(confirmMsg).then(function (ok) { if (ok) go(); }); } else { go(); }
    }
    $('#btnExamStart').on('click', function () { examAction({ action: 'start' }, 'Start the exam now? Examiners will be able to select groups and enter marks right away.'); });
    $('#btnExamLock').on('click', function () { examAction({ action: 'lock' }, 'Lock marking again? Examiners will see the waiting screen and cannot save new marks until you start the exam.'); });
    $('#btnExamWhen').on('click', function () { examAction({ action: 'schedule', scheduled_for: $('#ecWhen').val() || '' }); });
    renderExamControl(); setInterval(renderExamControl, 30000);

    // ───── Current exam ─────
    $('#btnSaveCurrentExam').on('click', function () {
        api('POST', '/current-exam', { exam_type: $('#currentExam').val() }).done(function (r) { say(r.message); }).fail(fail);
    });

    // ───── Groups ─────
    function renderGroups() {
        var tests = groups.filter(function (g) { return g.is_test; }).length;
        $('#testGroupCount').text(tests ? '(' + tests + ')' : '');
        var rows = groups.filter(function (g) { return showTest || !g.is_test; }).map(function (g) {
            var test = g.is_test ? ' <span class="badge badge-secondary">test</span>' : '';
            var actions = '<button class="btn btn-xs btn-light border btn-manage" data-id="' + g.id + '"><i class="fas fa-users-cog mr-1"></i>Manage</button> ' +
                (g.is_test ? '' : '<button class="btn btn-xs btn-light border btn-rename" data-id="' + g.id + '" title="Rename"><i class="fas fa-pen"></i></button> ' +
                '<button class="btn btn-xs btn-light border btn-delgroup" data-id="' + g.id + '" title="Delete"><i class="fas fa-trash text-danger"></i></button>');
            return '<tr><td><strong>' + esc(g.name) + '</strong>' + test + '</td><td class="text-right">' + g.candidates_mcs + '</td><td class="text-right">' + g.candidates_fcs +
                   '</td><td class="text-right">' + g.examiners + '</td><td class="text-right">' + actions + '</td></tr>';
        });
        $('#groupsTable tbody').html(rows.join('') || '<tr><td colspan="5" class="text-muted p-3">No groups yet.</td></tr>');
    }
    function reloadGroups() { return api('GET', '/overview').done(function (r) { groups = r.groups; renderGroups(); }); }
    renderGroups();
    $('#showTestGroups').on('change', function () { showTest = this.checked; renderGroups(); });

    $('#btnAddGroup').on('click', function () { groupEditId = null; $('#groupModalTitle').text('Add group'); $('#groupName').val(''); $('#groupModal').modal('show'); });
    $('#groupsTable').on('click', '.btn-rename', function () {
        groupEditId = $(this).data('id'); var g = groups.filter(function (x) { return x.id == groupEditId; })[0];
        $('#groupModalTitle').text('Rename group ' + g.name); $('#groupName').val(g.name); $('#groupModal').modal('show');
    });
    $('#btnSaveGroup').on('click', function () {
        var name = $('#groupName').val();
        (groupEditId ? api('POST', '/groups/' + groupEditId, { group_name: name }) : api('POST', '/groups', { group_name: name }))
            .done(function (r) { $('#groupModal').modal('hide'); say(r.message); reloadGroups(); }).fail(fail);
    });
    $('#groupsTable').on('click', '.btn-delgroup', function () {
        var id = $(this).data('id'), g = groups.filter(function (x) { return x.id == id; })[0];
        if (!confirm('Delete group ' + g.name + '?')) return;
        api('POST', '/groups/' + id + '/delete').done(function (r) { say(r.message); reloadGroups(); }).fail(fail);
    });

    // ───── Manage group ─────
    function loadManage() {
        return api('GET', '/groups/' + manageId).done(function (r) {
            manageData = r;
            $('#mgName').text(r.group.name); $('#mgYear').text(r.year);
            $('#mgExCount').text(r.examiners.length); $('#mgCandCount').text(r.candidates.length);
            $('#mgExTable tbody').html(r.examiners.map(function (e) {
                return '<tr><td>' + esc(e.name) + '<small class="text-muted d-block">' + esc(e.examiner_id || '') + (e.specialty ? ' · ' + esc(e.specialty) : '') + '</small></td>' +
                       '<td class="text-right"><button class="btn btn-xs btn-light border btn-rmex" data-id="' + e.id + '" title="Remove"><i class="fas fa-times text-danger"></i></button></td></tr>';
            }).join('') || '<tr><td class="text-muted">No examiners yet.</td></tr>');
            renderAvail();
            $('#mgCandTable tbody').html(r.candidates.map(function (c) {
                return '<tr><td><input type="checkbox" class="mg-cand" value="' + c.id + '"></td><td><strong>' + esc(c.number || '—') + '</strong></td><td>' + esc(c.entry_number || '') +
                       '</td><td>' + esc(c.programme || '') + '</td><td>' + esc(c.session || '—') + '</td></tr>';
            }).join('') || '<tr><td colspan="5" class="text-muted">No candidates in this group for ' + esc(r.year) + '.</td></tr>');
            $('#mgCandAll').prop('checked', false);
            $('#mgMoveTo').html('<option value="">Move to group…</option>' + r.other_groups.map(function (g) { return '<option value="' + g.id + '">Group ' + esc(g.name) + '</option>'; }).join(''));
        }).fail(fail);
    }
    function renderAvail() {
        var q = ($('#mgExSearch').val() || '').toLowerCase();
        $('#mgExAvail').html(manageData.available_examiners.filter(function (e) {
            return !q || (e.name || '').toLowerCase().indexOf(q) !== -1 || (e.examiner_id || '').toLowerCase().indexOf(q) !== -1;
        }).slice(0, 400).map(function (e) { return '<option value="' + e.id + '">' + esc(e.name) + ' (' + esc(e.examiner_id || '') + ')</option>'; }).join(''));
    }
    $('#groupsTable').on('click', '.btn-manage', function () { manageId = $(this).data('id'); loadManage().done(function () { $('#manageModal').modal('show'); }); });
    $('#mgExSearch').on('input', function () { if (manageData) renderAvail(); });
    $('#btnAddExaminers').on('click', function () {
        var ids = $('#mgExAvail').val() || [];
        if (!ids.length) return say('Pick one or more examiners first.', false);
        api('POST', '/groups/' + manageId + '/examiners', { examiner_ids: ids }).done(function (r) { say(r.message); loadManage(); reloadGroups(); }).fail(fail);
    });
    $('#mgExTable').on('click', '.btn-rmex', function () {
        if (!confirm('Remove this examiner from the group? Marks already saved are kept.')) return;
        api('POST', '/groups/' + manageId + '/examiners/' + $(this).data('id') + '/remove').done(function (r) { say(r.message); loadManage(); reloadGroups(); }).fail(fail);
    });
    $('#mgCandAll').on('change', function () { $('.mg-cand').prop('checked', this.checked); });
    $('#btnMove').on('click', function () {
        var ids = $('.mg-cand:checked').map(function () { return this.value; }).get(), to = $('#mgMoveTo').val();
        if (!ids.length) return say('Tick the candidates to move.', false);
        if (!to) return say('Choose the group to move them to.', false);
        if (!confirm('Move ' + ids.length + ' candidate(s) to the chosen group?')) return;
        api('POST', '/groups/' + manageId + '/move-candidates', { candidate_ids: ids, to_group_id: to }).done(function (r) { say(r.message); loadManage(); reloadGroups(); }).fail(fail);
    });

    // ───── Stations ─────
    function loadStations() {
        var type = $('#stationType').val();
        $('#stationsTable tbody').html('<tr><td colspan="7" class="text-muted p-3">Loading…</td></tr>');
        api('GET', '/stations?exam_type=' + encodeURIComponent(type)).done(function (r) {
            window.__stations = r.stations;
            $('#stationsTable tbody').html(r.stations.map(function (s) {
                var qs = s.questions.length ? s.questions.length + ' fixed' : '<span class="text-muted">free entry</span>';
                return '<tr class="' + (s.is_active ? '' : 'text-muted') + '"><td><strong>' + s.number + '</strong></td><td>' + esc(s.name) + '</td><td class="text-right">' + (s.max_total == null ? '—' : s.max_total) +
                       '</td><td>' + qs + '</td><td class="text-right">' + s.marks_this_year + '</td><td>' + (s.is_active ? '<span class="badge badge-success">On</span>' : '<span class="badge badge-secondary">Off</span>') +
                       '</td><td class="text-right"><button class="btn btn-xs btn-light border btn-editst" data-id="' + s.id + '" title="Edit"><i class="fas fa-pen"></i></button> ' +
                       '<button class="btn btn-xs btn-light border btn-delst" data-id="' + s.id + '" title="Delete"><i class="fas fa-trash text-danger"></i></button></td></tr>';
            }).join('') || '<tr><td colspan="7" class="text-muted p-3">No stations for this exam type yet.</td></tr>');
        }).fail(fail);
    }
    $('#stationType').on('change', loadStations);
    $('a[href="#esStations"]').one('shown.bs.tab', loadStations);

    function questionRow(q) {
        return '<div class="form-row mb-1 st-q"><div class="col-8"><input class="form-control form-control-sm st-q-label" placeholder="Question / task" value="' + esc(q ? q.label : '') + '"></div>' +
               '<div class="col-3"><input type="number" min="0" step="0.5" class="form-control form-control-sm st-q-max" placeholder="Max" value="' + (q ? q.max : '') + '"></div>' +
               '<div class="col-1"><button type="button" class="btn btn-sm btn-light st-q-del" title="Remove"><i class="fas fa-times text-danger"></i></button></div></div>';
    }
    function updateSum() {
        var n = 0, t = 0; $('.st-q').each(function () { var v = parseFloat($(this).find('.st-q-max').val()); if (!isNaN(v)) { n++; t += v; } });
        $('#stSum').text(n ? n + ' question' + (n === 1 ? '' : 's') + ', ' + t + ' marks in total' + ($('#stMax').val() === '' ? ' (used as the maximum)' : '') : '');
    }
    $('#btnAddStation').on('click', function () {
        stationEditId = null; $('#stationModalTitle').text('Add station');
        var used = (window.__stations || []).map(function (s) { return s.number; }), next = 1; while (used.indexOf(next) !== -1) next++;
        $('#stNumber').val(next); $('#stName').val('Station ' + next); $('#stMax').val(''); $('#stActive').prop('checked', true); $('#stQuestions').empty(); updateSum(); $('#stationModal').modal('show');
    });
    $('#stationsTable').on('click', '.btn-editst', function () {
        var s = window.__stations.filter(function (x) { return x.id == $(this).data('id'); }.bind(this))[0]; stationEditId = s.id;
        $('#stationModalTitle').text('Edit ' + s.name); $('#stNumber').val(s.number); $('#stName').val(s.name); $('#stMax').val(s.max_total == null ? '' : s.max_total);
        $('#stActive').prop('checked', s.is_active); $('#stQuestions').html(s.questions.map(questionRow).join('')); updateSum(); $('#stationModal').modal('show');
    });
    $('#btnAddQuestion').on('click', function () { $('#stQuestions').append(questionRow()); updateSum(); });
    $('#stQuestions').on('click', '.st-q-del', function () { $(this).closest('.st-q').remove(); updateSum(); }).on('input', '.st-q-max', updateSum);
    $('#stMax').on('input', updateSum);
    $('#btnSaveStation').on('click', function () {
        var qs = $('.st-q').map(function () { return { label: $(this).find('.st-q-label').val(), max: $(this).find('.st-q-max').val() }; }).get();
        var data = { exam_type: $('#stationType').val(), station_number: $('#stNumber').val(), name: $('#stName').val(), max_total: $('#stMax').val(), is_active: $('#stActive').is(':checked'), questions: qs };
        (stationEditId ? api('POST', '/stations/' + stationEditId, data) : api('POST', '/stations', data))
            .done(function (r) { $('#stationModal').modal('hide'); say(r.message); loadStations(); }).fail(fail);
    });
    $('#stationsTable').on('click', '.btn-delst', function () {
        var id = $(this).data('id'); if (!confirm('Delete this station?')) return;
        api('POST', '/stations/' + id + '/delete').done(function (r) { say(r.message); loadStations(); }).fail(fail);
    });
});
</script>
@endpush

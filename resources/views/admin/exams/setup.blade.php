@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 style="font-size:1.4rem;">Exams Settings</h1>
                    <p class="text-muted mb-0" style="font-size:.85rem;">
                        Pick an exam, then manage its groups (candidates and examiners for {{ $year }}), the stations and the wording of the examiner forms.
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

            <div class="card mb-3" style="border-left:4px solid #a02626;">
                <div class="card-body py-2 d-flex flex-wrap align-items-center" style="gap:.75rem;">
                    <div>
                        <strong><i class="fas fa-door-open mr-1" style="color:#a02626;"></i> Examiners' landing page</strong>
                        <div class="text-muted" style="font-size:.8rem;">The page examiners open first after they log in.</div>
                    </div>
                    <select id="examinerLanding" class="form-control form-control-sm ml-auto" style="width:auto;min-width:260px;">
                        @foreach(($landing_options ?? []) as $o)
                            <option value="{{ $o['key'] }}" {{ ($examiner_landing ?? 'dashboard') === $o['key'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-cosecsa" id="btnSaveLanding"><i class="fas fa-save mr-1"></i> Save</button>
                </div>
            </div>

            <div class="card mb-3" style="border-left:4px solid #a02626;">
                <div class="card-body py-2 d-flex flex-wrap align-items-center" style="gap:.75rem;">
                    <div>
                        <strong><i class="fas fa-stethoscope mr-1" style="color:#a02626;"></i> Exam</strong>
                        <div class="text-muted" style="font-size:.8rem;">The groups, stations and form labels below are for this exam.</div>
                    </div>
                    <select id="examType" class="form-control form-control-sm ml-auto" style="width:auto;min-width:260px;">
                        @foreach($types as $t)
                            <option value="{{ $t['key'] }}" {{ ($current_exam ?? 'mcs') === $t['key'] ? 'selected' : '' }}>{{ $t['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- ───────── Start the exam (for the exam picked above): examiners cannot select it or save marks until it is started ───────── --}}
            <div class="card mb-3 d-none" id="ecCard" style="border-left:4px solid #f0ad4e;">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-center" style="gap:.75rem;">
                        <div>
                            <strong><i class="fas fa-play-circle mr-1" style="color:#a02626;"></i> Start the exam — <span id="ecTitle"></span></strong>
                            <div id="ecStatus" style="font-size:.9rem;"></div>
                        </div>
                        <div class="ml-auto d-flex flex-wrap align-items-center" style="gap:.5rem;">
                            <button class="btn btn-cosecsa" id="btnExamStart"><i class="fas fa-play mr-1"></i> Start the exam</button>
                            <button class="btn btn-outline-secondary" id="btnExamLock"><i class="fas fa-lock mr-1"></i> Lock marking again</button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center mt-2" style="gap:.5rem;">
                        <label class="mb-0 mr-1" style="font-size:.85rem;" for="ecWhen">Exam starts at</label>
                        <input type="datetime-local" id="ecWhen" class="form-control form-control-sm" style="width:auto;">
                        <button class="btn btn-sm btn-outline-secondary" id="btnExamWhen">Save time</button>
                        <span class="text-muted" id="ecCountdown" style="font-size:.85rem;"></span>
                    </div>
                    <div class="text-muted mt-2" style="font-size:.8rem;">
                        Until the exam is started, examiners see a waiting screen and cannot select the exam or save marks (practice on the TEST groups stays open).
                        Start it about <strong>5 minutes before</strong> the exam begins. If you lock it again, marks saved on a phone without a connection wait and are saved once the exam is started.
                        Each exam is started on its own, and every exam starts locked.
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs mb-3" id="esTabs">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#esGroups"><i class="fas fa-users mr-1"></i> Groups</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#esStations"><i class="fas fa-clipboard-list mr-1"></i> Stations</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#esForms"><i class="fas fa-edit mr-1"></i> Form labels</a></li>
            </ul>

            <div class="tab-content">
                {{-- ───────── Groups ───────── --}}
                <div class="tab-pane fade show active" id="esGroups">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h3 class="card-title mb-0">Groups <small class="text-muted ml-1">candidates and examiners for {{ $year }}</small> <span class="badge badge-light border ml-1" id="groupsExamLabel"></span></h3>
                            <div class="custom-control custom-switch ml-auto mr-3" title="Test groups (TEST-...) are sandboxes used to try the examiner forms">
                                <input type="checkbox" class="custom-control-input" id="showTestGroups">
                                <label class="custom-control-label small" for="showTestGroups">Show test groups <span class="text-muted" id="testGroupCount"></span></label>
                            </div>
                            <button class="btn btn-sm btn-cosecsa" id="btnAddGroup"><i class="fas fa-plus mr-1"></i> Add group</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0" id="groupsTable" style="font-size:.9rem;">
                                <thead>
                                    <tr><th>Group</th><th class="text-right" id="thExamCandidates">Candidates</th><th class="text-right">All exams</th><th class="text-right">Examiners</th><th class="text-right" style="width:230px;">Actions</th></tr>
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
                            <h3 class="card-title mb-0">Stations <span class="badge badge-light border ml-1" id="stationsExamLabel"></span></h3>
                            <select id="stationFormat" class="form-control form-control-sm ml-2 d-none" style="width:auto;" title="Each FCS exam has a station list for its Clinical form and one for its Viva form">
                                <option value="clinical">Clinical form</option>
                                <option value="viva">Viva form</option>
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
                            Rename a station with the pencil: the new name is what examiners see in "Select Station". For the MCS and General Surgery forms the maximum marks and the questions are also set here
                            (questions are optional: leave them empty and the examiner enters the marks freely, up to the maximum). The FCS forms mark by cases, so their wording is under <strong>Form labels</strong>.
                            A station with saved marks cannot be deleted; switch it off instead. An FCS exam has one list for the Clinical form and one for the Viva form (Viva 1, Viva 2…): add or remove stations to match the exam.
                        </div>
                    </div>
                </div>

                {{-- ───────── Form labels (FCS Clinical / Viva) ───────── --}}
                <div class="tab-pane fade" id="esForms">
                    <div id="formsBody"><p class="text-muted p-3">Loading…</p></div>
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
                        <div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="mgAllExams"><label class="custom-control-label small" for="mgAllExams">Show every exam</label></div>
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
                <div class="form-group col-md-3 st-marks-only"><label>Max marks</label><input type="number" id="stMax" class="form-control" min="0" step="0.5" placeholder="no limit"></div>
            </div>
            <div class="custom-control custom-switch mb-3">
                <input type="checkbox" class="custom-control-input" id="stActive" checked>
                <label class="custom-control-label" for="stActive">Show on the examiner forms</label>
            </div>
            <div class="st-marks-only">
            <div class="d-flex align-items-center mb-1">
                <strong>Questions</strong> <span class="text-muted small ml-2">(optional: leave empty for free entry)</span>
                <button type="button" class="btn btn-sm btn-outline-secondary ml-auto" id="btnAddQuestion"><i class="fas fa-plus mr-1"></i> Add question</button>
            </div>
            <div id="stQuestions"></div>
            <small class="text-muted" id="stSum"></small>
            </div>
            <small class="text-muted st-fcs-only d-none">An FCS station is a name and a number: the marks are given on the Clinical and Viva forms (see Form labels).</small>
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-cosecsa" id="btnSaveStation">Save station</button></div>
    </div></div>
</div>
@endsection


<style>
    .fm-handle { cursor: grab; }
    .fm-handle:active { cursor: grabbing; }
    .fm-chandle { padding: 0 .25rem; }
    .fm-q .input-group-text.fm-handle { background: #f8f9fa; }
    .fm-placeholder { border: 2px dashed #a02626; border-radius: .25rem; background: rgba(160,38,38,.05); margin-bottom: .5rem; }
    .fm-case.ui-sortable-helper, .fm-q.ui-sortable-helper { background: #fff; box-shadow: 0 4px 14px rgba(0,0,0,.18); }
</style>
@push('scripts')
<script>
$(function () {
    var base = @json(url('admin/exams/setup'));
    var csrf = $('meta[name="csrf-token"]').attr('content') || @json(csrf_token());
    var yearName = @json($year);
    var groups = @json($groups);
    var examTypes = @json($types);
    function examType() { return $('#examType').val() || 'mcs'; }
    function examLabel() { var t = examTypes.filter(function (x) { return x.key === examType(); })[0]; return t ? t.label : ''; }
    function stationFormat() { return isFcsExam() ? ($('#stationFormat').val() || 'clinical') : ''; }
    function isFcsExam() { return ['mcs', 'gs'].indexOf(examType()) === -1; }   // the FCS specialties mark by cases (Clinical / Viva)
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

    // ───── Start the exam (the exam picked at the top) ─────
    var examControl = null;
    function fmtTime(t) { return t ? t.replace('T', ' ').slice(0, 16) : ''; }
    function renderExamControl() {
        if (!examControl || !examControl.guarded) { $('#ecCard').addClass('d-none'); return; }
        $('#ecCard').removeClass('d-none').css('border-left-color', examControl.open ? '#28a745' : '#f0ad4e');
        $('#ecTitle').text(examControl.label + ' ' + examControl.year);
        $('#ecStatus').html(examControl.open
            ? '<span class="badge badge-success">Started</span> ' + esc(fmtTime(examControl.started_at)) + (examControl.started_by_name ? ' by ' + esc(examControl.started_by_name) : '') + ' — examiners can select groups and enter marks.'
            : '<span class="badge badge-warning">Not started</span> Examiners see the waiting screen and cannot save marks yet.');
        $('#btnExamStart').toggle(!examControl.open); $('#btnExamLock').toggle(!!examControl.open);
        if (document.activeElement !== $('#ecWhen')[0]) $('#ecWhen').val(examControl.scheduled_for ? String(examControl.scheduled_for).replace(' ', 'T') : '');
        var cd = '';
        if (!examControl.open && examControl.scheduled_for) {
            var mins = Math.round((new Date(examControl.scheduled_for.replace(' ', 'T')) - new Date()) / 60000);
            cd = mins > 0 ? 'Starts in ' + (mins >= 60 ? Math.floor(mins / 60) + ' h ' + (mins % 60) + ' min' : mins + ' min') + (mins <= 5 ? ' — start it now' : (mins <= 15 ? ' — start it in ' + (mins - 5) + ' min' : '')) : 'The start time has passed — start the exam.';
        }
        $('#ecCountdown').text(cd);
    }
    function loadExamControl() {
        var t = examType();
        api('GET', '/exam-control?exam_type=' + encodeURIComponent(t)).done(function (r) { if (t === examType()) { examControl = r; renderExamControl(); } }).fail(function () { examControl = null; renderExamControl(); });
    }
    function examAction(body, confirmMsg) {
        body.exam_type = examType();
        var go = function () { api('POST', '/exam-control', body).done(function (r) { examControl = r; renderExamControl(); say(r.message); }).fail(fail); };
        if (confirmMsg && window.CosecsaAlert && CosecsaAlert.confirm) { CosecsaAlert.confirm(confirmMsg).then(function (ok) { if (ok) go(); }); } else { go(); }
    }
    $('#btnExamStart').on('click', function () { examAction({ action: 'start' }, 'Start ' + examLabel() + ' now? Examiners will be able to select groups and enter marks right away.'); });
    $('#btnExamLock').on('click', function () { examAction({ action: 'lock' }, 'Lock ' + examLabel() + ' again? Examiners will see the waiting screen and cannot save new marks until you start the exam.'); });
    $('#btnExamWhen').on('click', function () { examAction({ action: 'schedule', scheduled_for: $('#ecWhen').val() || '' }); });
    examControl = @json($exam_control ?? null);
    renderExamControl(); setInterval(function () { loadExamControl(); }, 30000);

    // ───── Current exam ─────
    $('#btnSaveCurrentExam').on('click', function () {
        api('POST', '/current-exam', { exam_type: $('#currentExam').val() }).done(function (r) { say(r.message); }).fail(fail);
    });

    // ───── Where examiners land after login ─────
    $('#btnSaveLanding').on('click', function () {
        api('POST', '/examiner-landing', { landing: $('#examinerLanding').val() }).done(function (r) { say(r.message); }).fail(fail);
    });

    // ───── Groups ─────
    function renderGroups() {
        var tests = groups.filter(function (g) { return g.is_test; }).length, et = examType();
        $('#groupsExamLabel').text(examLabel()); $('#thExamCandidates').text('Candidates: ' + examLabel());
        $('#testGroupCount').text(tests ? '(' + tests + ')' : '');
        var rows = groups.filter(function (g) { return showTest || !g.is_test; }).map(function (g) {
            var test = g.is_test ? ' <span class="badge badge-secondary">test</span>' : '';
            var actions = '<button class="btn btn-xs btn-light border btn-manage" data-id="' + g.id + '"><i class="fas fa-users-cog mr-1"></i>Manage</button> ' +
                (g.is_test ? '' : '<button class="btn btn-xs btn-light border btn-rename" data-id="' + g.id + '" title="Rename"><i class="fas fa-pen"></i></button> ' +
                '<button class="btn btn-xs btn-light border btn-delgroup" data-id="' + g.id + '" title="Delete"><i class="fas fa-trash text-danger"></i></button>');
            return '<tr><td><strong>' + esc(g.name) + '</strong>' + test + '</td><td class="text-right"><strong>' + ((g.exam_counts || {})[et] || 0) + '</strong></td><td class="text-right text-muted">' + (g.candidates_mcs + g.candidates_fcs) +
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
        var q = $('#mgAllExams').is(':checked') ? '' : '?exam_type=' + encodeURIComponent(examType());
        return api('GET', '/groups/' + manageId + q).done(function (r) {
            manageData = r;
            $('#mgName').text(r.group.name); $('#mgYear').text(r.year);
            $('#mgExCount').text(r.examiners.length); $('#mgCandCount').text(r.candidates.length);
            $('#mgExTable tbody').html(r.examiners.map(function (e) {
                if (examType() !== 'mcs') {   // placing an examiner at a station is an MCS feature
                    return '<tr><td>' + esc(e.name) + '<small class="text-muted d-block">' + esc(e.examiner_id || '') + (e.specialty ? ' · ' + esc(e.specialty) : '') + '</small></td><td></td>' +
                           '<td class="text-right"><button class="btn btn-xs btn-light border btn-rmex" data-id="' + e.id + '" title="Remove"><i class="fas fa-times text-danger"></i></button></td></tr>';
                }
                var stOpts = '<option value="">Any station</option>' + (r.stations || []).map(function (s) { return '<option value="' + s.number + '"' + (String(e.station) === String(s.number) ? ' selected' : '') + '>' + esc(s.name || 'Station ' + s.number) + '</option>'; }).join('');
                return '<tr><td>' + esc(e.name) + '<small class="text-muted d-block">' + esc(e.examiner_id || '') + (e.specialty ? ' · ' + esc(e.specialty) : '') + '</small></td>' +
                       '<td style="width:150px;"><select class="form-control form-control-sm mg-station" data-id="' + e.id + '" title="MCS station for this examiner in this group">' + stOpts + '</select></td>' +
                       '<td class="text-right"><button class="btn btn-xs btn-light border btn-rmex" data-id="' + e.id + '" title="Remove"><i class="fas fa-times text-danger"></i></button></td></tr>';
            }).join('') || '<tr><td class="text-muted" colspan="3">No examiners yet.</td></tr>');
            renderAvail();
            $('#mgCandTable tbody').html(r.candidates.map(function (c) {
                return '<tr><td><input type="checkbox" class="mg-cand" value="' + c.id + '"></td><td><strong>' + esc(c.number || '—') + '</strong></td><td>' + esc(c.entry_number || '') +
                       '</td><td>' + esc(c.programme || '') + '</td><td>' + esc(c.session || '—') + '</td></tr>';
            }).join('') || '<tr><td colspan="5" class="text-muted">No ' + ($('#mgAllExams').is(':checked') ? '' : esc(examLabel()) + ' ') + 'candidates in this group for ' + esc(r.year) + '.</td></tr>');
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
    $('#mgAllExams').on('change', function () { if (manageId) loadManage(); });
    $('#mgExSearch').on('input', function () { if (manageData) renderAvail(); });
    $('#btnAddExaminers').on('click', function () {
        var ids = $('#mgExAvail').val() || [];
        if (!ids.length) return say('Pick one or more examiners first.', false);
        api('POST', '/groups/' + manageId + '/examiners', { examiner_ids: ids }).done(function (r) { say(r.message); loadManage(); reloadGroups(); }).fail(fail);
    });
    $('#mgExTable').on('change', '.mg-station', function () {
        api('POST', '/groups/' + manageId + '/examiners/' + $(this).data('id') + '/station', { station_number: $(this).val() }).done(function (r) { say(r.message); }).fail(fail);
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
        var type = examType(), format = stationFormat();
        $('#stationsExamLabel').text(examLabel());
        $('#stationsTable tbody').html('<tr><td colspan="7" class="text-muted p-3">Loading…</td></tr>');
        api('GET', '/stations?exam_type=' + encodeURIComponent(type) + '&format=' + format).done(function (r) {
            if (type !== examType() || format !== stationFormat()) return;   // the exam was changed while this was loading
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
    function stationModalFor() { var fcs = isFcsExam(); $('.st-marks-only').toggleClass('d-none', fcs); $('.st-fcs-only').toggleClass('d-none', !fcs); }
    $('a[href="#esStations"]').on('shown.bs.tab', loadStations);
    $('a[href="#esForms"]').on('shown.bs.tab', loadForms);
    $('#stationFormat').on('change', loadStations);
    $('#stationFormat').toggleClass('d-none', !isFcsExam());
    $('#examType').on('change', function () {
        renderGroups();
        $('#stationFormat').toggleClass('d-none', !isFcsExam());
        loadExamControl();
        $('#stationsExamLabel').text(examLabel());
        if ($('#esStations').hasClass('active')) loadStations(); else window.__stations = null;
        if ($('#esForms').hasClass('active')) loadForms();
    });

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
        stationModalFor(); $('#stNumber').val(next); $('#stName').val((stationFormat() === 'viva' ? 'Viva ' : 'Station ') + next); $('#stMax').val(''); $('#stActive').prop('checked', true); $('#stQuestions').empty(); updateSum(); $('#stationModal').modal('show');
    });
    $('#stationsTable').on('click', '.btn-editst', function () {
        var s = window.__stations.filter(function (x) { return x.id == $(this).data('id'); }.bind(this))[0]; stationEditId = s.id;
        stationModalFor(); $('#stationModalTitle').text('Edit ' + s.name); $('#stNumber').val(s.number); $('#stName').val(s.name); $('#stMax').val(s.max_total == null ? '' : s.max_total);
        $('#stActive').prop('checked', s.is_active); $('#stQuestions').html(s.questions.map(questionRow).join('')); updateSum(); $('#stationModal').modal('show');
    });
    $('#btnAddQuestion').on('click', function () { $('#stQuestions').append(questionRow()); updateSum(); });
    $('#stQuestions').on('click', '.st-q-del', function () { $(this).closest('.st-q').remove(); updateSum(); }).on('input', '.st-q-max', updateSum);
    $('#stMax').on('input', updateSum);
    $('#btnSaveStation').on('click', function () {
        var qs = $('.st-q').map(function () { return { label: $(this).find('.st-q-label').val(), max: $(this).find('.st-q-max').val() }; }).get();
        var data = { exam_type: examType(), format: stationFormat(), station_number: $('#stNumber').val(), name: $('#stName').val(), max_total: $('#stMax').val(), is_active: $('#stActive').is(':checked'), questions: qs };
        (stationEditId ? api('POST', '/stations/' + stationEditId, data) : api('POST', '/stations', data))
            .done(function (r) { $('#stationModal').modal('hide'); say(r.message); loadStations(); }).fail(fail);
    });
    $('#stationsTable').on('click', '.btn-delst', function () {
        var id = $(this).data('id'); if (!confirm('Delete this station?')) return;
        api('POST', '/stations/' + id + '/delete').done(function (r) { say(r.message); loadStations(); }).fail(fail);
    });

    // ───── Form labels (FCS Clinical / Viva): cases, questions, labels, marks ─────
    var formLimits = { cases: 8, questions: 10 };
    function questionRow(label, n) {
        return '<div class="input-group input-group-sm mb-1 fm-q"><div class="input-group-prepend"><span class="input-group-text fm-handle fm-qhandle" title="Drag to reorder, or into another case"><i class="fas fa-grip-vertical"></i></span><span class="input-group-text fm-qn">' + n + '</span></div>' +
               '<input class="form-control fm-label" maxlength="120" value="' + esc(label) + '" placeholder="Question ' + n + '">' +
               '<div class="input-group-append"><button type="button" class="btn btn-light border fm-qdel" title="Remove this question"><i class="fas fa-times text-danger"></i></button></div></div>';
    }
    function caseBlock(c, n) {
        return '<div class="border rounded p-2 mb-2 fm-case"><div class="d-flex align-items-center mb-2" style="gap:.5rem;"><span class="fm-handle fm-chandle text-muted" title="Drag to reorder the cases"><i class="fas fa-grip-vertical"></i></span><strong class="fm-cn text-nowrap">Case ' + n + '</strong>' +
               '<input class="form-control form-control-sm fm-title" maxlength="80" value="' + esc(c.title) + '" placeholder="Heading (empty: Case ' + n + ')">' +
               '<button type="button" class="btn btn-sm btn-light border fm-cdel text-nowrap" title="Remove this case"><i class="fas fa-trash text-danger"></i></button></div>' +
               '<div class="fm-qs">' + c.questions.map(function (q, i) { return questionRow(q, i + 1); }).join('') + '</div>' +
               '<button type="button" class="btn btn-xs btn-outline-secondary fm-qadd"><i class="fas fa-plus mr-1"></i> Add question</button></div>';
    }
    function renumberForm(card) {
        card.find('.fm-case').each(function (ci) {
            var n = ci + 1;
            $(this).find('.fm-cn').text('Case ' + n); $(this).find('.fm-title').attr('placeholder', 'Heading (empty: Case ' + n + ')');
            $(this).find('.fm-q').each(function (qi) { $(this).find('.fm-qn').text(qi + 1); $(this).find('.fm-label').attr('placeholder', 'Question ' + (qi + 1)); });
        });
        card.find('.fm-cadd').prop('disabled', card.find('.fm-case').length >= formLimits.cases);
        card.find('.fm-cdel').prop('disabled', card.find('.fm-case').length <= 1);
        card.find('.fm-case').each(function () {
            $(this).find('.fm-qdel').prop('disabled', $(this).find('.fm-q').length <= 1);
            $(this).find('.fm-qadd').prop('disabled', $(this).find('.fm-q').length >= formLimits.questions);
        });
    }
    // Drag and drop: cases by their grip, questions by theirs (also from one case into another of the same form)
    function enableDragging(card) {
        var f = card.data('format');
        card.find('.fm-cases').not('.ui-sortable').sortable({
            items: '> .fm-case', handle: '.fm-chandle', axis: 'y', tolerance: 'pointer', placeholder: 'fm-placeholder', forcePlaceholderSize: true,
            stop: function () { renumberForm(card); }
        });
        card.find('.fm-qs').addClass('fm-qs-' + f).not('.ui-sortable').sortable({
            items: '> .fm-q', handle: '.fm-qhandle', connectWith: '.fm-qs-' + f, tolerance: 'pointer', placeholder: 'fm-placeholder', forcePlaceholderSize: true,
            stop: function () {
                card.find('.fm-qs').each(function () { if (!$(this).children('.fm-q').length) $(this).append(questionRow('', 1)); });   // a case keeps at least one question
                renumberForm(card);
            }
        });
    }
    function formCard(f, c) {
        var title = f === 'clinical' ? 'Clinical form' : 'Viva form';
        var h = '<div class="card mb-3 fm-card" data-format="' + f + '"><div class="card-header d-flex align-items-center"><h3 class="card-title mb-0">' + title + '</h3><span class="ml-auto">' +
                (c.customised ? '<span class="badge badge-warning">Changed</span>' : '<span class="badge badge-light border">Standard layout</span>') + '</span></div><div class="card-body">';
        h += '<div class="fm-cases">' + c.cases.map(function (cs, i) { return caseBlock(cs, i + 1); }).join('') + '</div>';
        h += '<button type="button" class="btn btn-sm btn-outline-secondary fm-cadd"><i class="fas fa-plus mr-1"></i> Add case</button>';
        h += '<div class="form-group mt-3 mb-2"><label>Marks an examiner can give</label><input class="form-control form-control-sm fm-marks" style="max-width:320px;" value="' + esc(c.marks.join(', ')) +
             '"><small class="text-muted">Separated by commas, for example 2, 4, 6, 8, 10: these are the choices in each question\'s drop-down.</small></div>';
        h += '<div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input fm-extra" id="fmExtra_' + f + '"' + (c.allow_extra ? ' checked' : '') + '><label class="custom-control-label" for="fmExtra_' + f +
             '">Examiners can add an extra question to a case while marking</label></div>';
        h += '</div><div class="card-footer d-flex" style="gap:.5rem;"><button class="btn btn-sm btn-cosecsa fm-save"><i class="fas fa-save mr-1"></i> Save ' + title + '</button>' +
             '<button class="btn btn-sm btn-outline-secondary fm-reset"' + (c.customised ? '' : ' disabled') + '>Back to standard layout</button></div></div>';
        return h;
    }
    function loadForms() {
        var t = examType();
        $('#formsBody').html('<p class="text-muted p-3">Loading…</p>');
        api('GET', '/forms?exam_type=' + encodeURIComponent(t)).done(function (r) {
            if (t !== examType()) return;
            if (!r.supported) {
                $('#formsBody').html('<div class="alert alert-light border"><i class="fas fa-info-circle mr-1"></i> The ' + esc(r.label) + ' form has its own fixed layout, so there is nothing to change here. Rename its stations under <strong>Stations</strong>. Pick an FCS specialty above to change its Clinical and Viva forms.</div>');
                return;
            }
            formLimits = r.limits || formLimits;
            $('#formsBody').html('<div class="text-muted small mb-2">The cases, questions and wording of the ' + esc(r.label) + ' marking forms. Add or remove cases and questions as the exam needs, and drag the grip at the left of a case or a question to put it in order (a question can also be dragged into another case). ' +
                'Labels are read by position, so reordering or renaming also changes how marks already saved are labelled. ' +
                'Marks already given are kept exactly as they were saved. Changes show the next time an examiner opens the form. The names of the stations (Station 1… or Viva 1…) are under <strong>Stations</strong>.</div>' +
                ['clinical', 'viva'].map(function (f) { return formCard(f, r.formats[f]); }).join(''));
            $('#formsBody .fm-card').each(function () { renumberForm($(this)); enableDragging($(this)); });
        }).fail(fail);
    }
    $('#formsBody').on('click', '.fm-cadd', function () {
        var card = $(this).closest('.fm-card'), last = card.find('.fm-case').last();   // a new case starts with the questions of the one before it
        var qs = last.find('.fm-label').map(function () { return $(this).val(); }).get();
        card.find('.fm-cases').append(caseBlock({ title: '', questions: qs.length ? qs : [''] }, card.find('.fm-case').length + 1)); renumberForm(card); enableDragging(card);
    }).on('click', '.fm-cdel', function () {
        var card = $(this).closest('.fm-card'); $(this).closest('.fm-case').remove(); renumberForm(card);
    }).on('click', '.fm-qadd', function () {
        var card = $(this).closest('.fm-card'), box = $(this).siblings('.fm-qs'); box.append(questionRow('', box.children().length + 1)); renumberForm(card);
    }).on('click', '.fm-qdel', function () {
        var card = $(this).closest('.fm-card'); $(this).closest('.fm-q').remove(); renumberForm(card);
    }).on('click', '.fm-save', function () {
        var card = $(this).closest('.fm-card');
        api('POST', '/forms', {
            exam_type: examType(), format: card.data('format'), marks: card.find('.fm-marks').val(), allow_extra: card.find('.fm-extra').is(':checked'),
            cases: card.find('.fm-case').map(function () {
                return { title: $(this).find('.fm-title').val(), questions: $(this).find('.fm-label').map(function () { return $(this).val(); }).get() };
            }).get()
        }).done(function (r) { say(r.message); loadForms(); }).fail(fail);
    }).on('click', '.fm-reset', function () {
        var f = $(this).closest('.fm-card').data('format');
        if (!confirm('Put the ' + f + ' form back to the standard layout?')) return;
        api('POST', '/forms/reset', { exam_type: examType(), format: f }).done(function (r) { say(r.message); loadForms(); }).fail(fail);
    });
});
</script>
@endpush

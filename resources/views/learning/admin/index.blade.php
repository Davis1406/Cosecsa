@extends('layout.app')

@section('content')
<div class="content-wrapper">
    @include('learning.admin._head', ['title' => 'Examiner Training', 'subtitle' => $course['title']])
    <section class="content">
        <div class="container-fluid lms">
            @include('learning.partials.flash')
            @include('learning.admin._tabs')
            @php $quizModule = collect($modules)->firstWhere('type', 'quiz'); @endphp

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card card-pad mb-3 mb-md-0">
                        <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase;">Confirmed examiners</div>
                        <div style="font-size:30px; font-weight:800;">{{ count($learners) }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-pad">
                        <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase;">Course modules</div>
                        <div style="font-size:30px; font-weight:800;">{{ count($modules) }}</div>
                    </div>
                </div>
            </div>

            @php
                $by = collect($learners)->groupBy('status');
                $completedRows = $by->get('completed', collect());
                $ongoingRows   = $by->get('ongoing', collect());
                $pendingRows   = $by->get('pending', collect());
                $fmt = fn ($iso) => $iso ? \Illuminate\Support\Carbon::parse($iso)->format('d M Y') : null;
                $canRemind = $pendingRows->filter(fn ($r) => $r['login_sent_at'])->count();
            @endphp

            <div class="card card-pad">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:10px;">
                    <h5 class="mb-0" style="font-size:16px; font-weight:700;">Learner progress</h5>
                    {{-- Switches panes in the browser: all three lists are already on the page, so nothing reloads. --}}
                    <ul class="nav nav-pills status-tabs" id="statusTabs" role="tablist">
                        <li class="nav-item"><button type="button" class="nav-link" data-pane="completed" data-toggle="pill" data-target="#pane-completed" role="tab" aria-controls="pane-completed">Completed <span class="count" id="count-completed">{{ $completedRows->count() }}</span></button></li>
                        <li class="nav-item"><button type="button" class="nav-link" data-pane="ongoing" data-toggle="pill" data-target="#pane-ongoing" role="tab" aria-controls="pane-ongoing">Ongoing <span class="count" id="count-ongoing">{{ $ongoingRows->count() }}</span></button></li>
                        <li class="nav-item"><button type="button" class="nav-link" data-pane="pending" data-toggle="pill" data-target="#pane-pending" role="tab" aria-controls="pane-pending">Pending <span class="count" id="count-pending">{{ $pendingRows->count() }}</span></button></li>
                    </ul>
                </div>

                <div class="tab-content">
                    {{-- COMPLETED --}}
                    <div class="tab-pane" id="pane-completed" role="tabpanel">
                        <p class="muted pane-hint">Finished every module of the course.</p>
                        <div class="table-responsive">
                            <table class="tbl" id="table-completed">
                                <thead><tr>
                                    <th>Name</th><th>Email</th><th>Completed on</th><th>Quiz score</th><th class="text-center">Action</th>
                                </tr></thead>
                                <tbody>
                                @foreach($completedRows as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        <td class="muted">{{ $row['email'] }}</td>
                                        <td data-order="{{ $row['completed_at'] ? strtotime($row['completed_at']) : 0 }}">{{ $fmt($row['completed_at']) ?? '—' }}</td>
                                        <td data-order="{{ $row['quiz_score'] ?? -1 }}">@if($row['quiz_score'] !== null) {{ round($row['quiz_score']) }}% @else <span class="muted">—</span> @endif</td>
                                        <td class="text-center">@include('learning.admin._learner-actions', ['row' => $row, 'quizModule' => $quizModule])</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ONGOING --}}
                    <div class="tab-pane" id="pane-ongoing" role="tabpanel">
                        <p class="muted pane-hint">Started the course but have not finished it.</p>
                        <div class="table-responsive">
                            <table class="tbl" id="table-ongoing">
                                <thead><tr>
                                    <th>Name</th><th>Email</th><th>Started</th><th>Modules done</th><th>Progress</th><th>Quiz score</th><th class="text-center">Action</th>
                                </tr></thead>
                                <tbody>
                                @foreach($ongoingRows as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        <td class="muted">{{ $row['email'] }}</td>
                                        <td data-order="{{ $row['started_at'] ? strtotime($row['started_at']) : 0 }}">{{ $fmt($row['started_at']) ?? '—' }}</td>
                                        <td data-order="{{ $row['completed'] }}">{{ $row['completed'] }} / {{ $row['total'] }}</td>
                                        <td style="width:200px;" data-order="{{ $row['percent'] }}">
                                            <div class="progress-track"><div class="progress-fill" style="width: {{ $row['percent'] }}%;"></div></div>
                                        </td>
                                        <td data-order="{{ $row['quiz_score'] ?? -1 }}">@if($row['quiz_score'] !== null) {{ round($row['quiz_score']) }}% @else <span class="muted">—</span> @endif</td>
                                        <td class="text-center">@include('learning.admin._learner-actions', ['row' => $row, 'quizModule' => $quizModule])</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- PENDING --}}
                    <div class="tab-pane" id="pane-pending" role="tabpanel">
                        <p class="muted pane-hint">Have not opened any part of the course yet.</p>

                        <div class="reminder-box" id="reminderBox">
                            <div class="reminder-head">
                                <div>
                                    <div class="reminder-title"><i class="fas fa-bell mr-2"></i>Automatic reminders</div>
                                    <div class="muted" style="font-size:12.5px;">Emails examiners who were sent their login but have not started. The email never contains a password.</div>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="remEnabled" {{ $reminders['enabled'] ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="remEnabled"><span id="remEnabledLabel">{{ $reminders['enabled'] ? 'On' : 'Off' }}</span></label>
                                </div>
                            </div>
                            <div class="reminder-fields">
                                <label>Remind
                                    <input type="number" id="remAfter" class="form-control form-control-sm" min="1" max="30" value="{{ $reminders['after_days'] }}">
                                    days after the login email was sent</label>
                                <label>Send up to
                                    <input type="number" id="remMax" class="form-control form-control-sm" min="1" max="10" value="{{ $reminders['max_reminders'] }}">
                                    reminder(s) per examiner, that many days apart</label>
                                <button type="button" class="btn btn-sm btn-primary" id="remSave">Save</button>
                                <span class="muted" id="remSaved" style="font-size:12.5px;"></span>
                            </div>
                            <div class="reminder-foot">
                                <span class="muted" id="remDue" style="font-size:12.5px;">
                                    {{ $reminders['enabled'] ? $reminders['due_now'].' due at the next automatic run (daily, 08:30 UTC).' : 'Switched off — nothing is sent automatically.' }}
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="remAll" @disabled($canRemind === 0)>
                                    <i class="fas fa-paper-plane mr-1"></i> Remind all pending now (<span id="remAllCount">{{ $canRemind }}</span>)
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="tbl" id="table-pending">
                                <thead><tr>
                                    <th>Name</th><th>Email</th><th>Login emailed</th><th>Reminders</th><th class="text-center">Action</th>
                                </tr></thead>
                                <tbody>
                                @foreach($pendingRows as $row)
                                    <tr data-user="{{ $row['id'] }}" data-name="{{ $row['name'] }}" data-login="{{ $row['login_sent_at'] ? 1 : 0 }}">
                                        <td>{{ $row['name'] }}</td>
                                        <td class="muted">{{ $row['email'] }}</td>
                                        <td data-order="{{ $row['login_sent_at'] ? strtotime($row['login_sent_at']) : 0 }}">
                                            @if($row['login_sent_at']) {{ $fmt($row['login_sent_at']) }} @else <span class="badge-gray pill">Not sent yet</span> @endif
                                        </td>
                                        <td class="rem-cell" data-order="{{ $row['last_reminded_at'] ? strtotime($row['last_reminded_at']) : 0 }}">
                                            @if($row['reminders_sent'])
                                                {{ $row['reminders_sent'] }} sent · last {{ $fmt($row['last_reminded_at']) }}
                                            @else <span class="muted">None</span> @endif
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <button type="button" class="btn btn-sm btn-outline-primary rem-one"
                                                @disabled(! $row['login_sent_at'])
                                                title="{{ $row['login_sent_at'] ? 'Email a reminder now' : 'No login email has been sent to this examiner yet' }}">
                                                <i class="fas fa-bell"></i> Remind
                                            </button>
                                            @include('learning.admin._learner-actions', ['row' => $row, 'quizModule' => $quizModule])
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="confirmResetModal" tabindex="-1" role="dialog" aria-labelledby="confirmResetTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content confirm-reset">
            <div class="modal-body text-center">
                <div class="confirm-reset-icon"><i class="fas fa-undo"></i></div>
                <h5 class="confirm-reset-title" id="confirmResetTitle">Reset?</h5>
                <div class="confirm-reset-name" id="confirmResetName"></div>
                <p class="confirm-reset-text" id="confirmResetText"></p>
                <div class="confirm-reset-actions">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmResetOk">Reset</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
<style>
    /* let the action menu open past the bottom of the table instead of being clipped by the scroll box */
    @media (min-width: 768px) { .lms .table-responsive { overflow: visible; } }

    /* confirmation popup for the reset actions */
    .confirm-reset { border: 0; border-radius: 14px; box-shadow: 0 24px 60px rgba(16,24,40,.28); overflow: hidden; }
    .confirm-reset .modal-body { padding: 30px 28px 24px; }
    .confirm-reset-icon { width: 56px; height: 56px; margin: 0 auto 14px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: rgba(160,38,38,.10); color: #a02626; font-size: 22px; }
    .confirm-reset-title { font-size: 19px; font-weight: 800; margin: 0 0 4px; }
    .confirm-reset-name { display: inline-block; margin: 6px 0 10px; padding: 3px 12px; border-radius: 999px; font-size: 13px; font-weight: 700;
        background: #ecf5fe; color: #141d23; }
    .confirm-reset-text { color: #59413f; font-size: 14px; line-height: 1.55; margin: 0 0 22px; }
    .confirm-reset-actions { display: flex; gap: 10px; justify-content: center; }
    .confirm-reset-actions .btn { min-width: 120px; border-radius: 8px; font-weight: 600; }
    .confirm-reset-actions .btn-danger { background: #a02626; border-color: #a02626; }
    .confirm-reset-actions .btn-danger:hover { background: #870f0f; border-color: #870f0f; }
    .dark-mode .confirm-reset-name { background: #2d3748; color: #fff; }
    .dark-mode .confirm-reset-text { color: #cbd5e0; }
    .dark-mode .confirm-reset-icon { background: rgba(254,197,3,.14); color: #FEC503; }

    /* Completed / Ongoing / Pending switcher */
    .status-tabs .nav-link { font-weight: 600; color: #475569; border-radius: 8px; padding: 6px 14px; }
    .status-tabs .nav-link.active { background: #a02626; color: #fff; }
    .status-tabs .count { display: inline-block; min-width: 22px; margin-left: 6px; padding: 0 7px; border-radius: 999px; font-size: 11.5px; line-height: 20px; text-align: center;
        background: rgba(20,29,35,.08); color: #141d23; }
    .status-tabs .nav-link.active .count { background: rgba(255,255,255,.22); color: #fff; }
    .pane-hint { font-size: 13px; margin: 0 0 14px; }
    .lms .pill { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }

    /* automatic reminders box on the Pending tab */
    .reminder-box { border: 1px solid #e0e9f2; border-left: 4px solid #a02626; border-radius: 8px; padding: 16px 18px; margin-bottom: 18px; background: #fff; }
    .reminder-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 12px; }
    .reminder-title { font-weight: 700; font-size: 14.5px; color: #141d23; }
    .reminder-fields { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 22px; }
    .reminder-fields label { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 13.5px; font-weight: 500; }
    .reminder-fields input[type=number] { width: 68px; }
    .reminder-foot { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #eef1f5; }
    .dark-mode .status-tabs .nav-link { color: #cbd5e1; }
    .dark-mode .status-tabs .nav-link.active { background: #a02626; color: #fff; }
    .dark-mode .status-tabs .count { background: rgba(255,255,255,.12); color: #e2e8f0; }
    .dark-mode .reminder-box { background: #1e2330; border-color: #2d3748; border-left-color: #a02626; }
    .dark-mode .reminder-title { color: #f1f5f9; }
    .dark-mode .reminder-foot { border-top-color: #2d3748; }
</style>
@endpush

@push('scripts')
<script>
    $(function () {
        // ── Completed / Ongoing / Pending: switching is done in the browser, no reload ──
        var TABLES = {
            completed: { order: [[2, 'desc']], actionCol: 4 },
            ongoing:   { order: [[3, 'desc']], actionCol: 6 },
            pending:   { order: [[2, 'asc']],  actionCol: 4 }
        };
        var tables = {};
        function table(pane) {
            if (!tables[pane]) {   // built the first time the tab is shown, so a hidden tab never measures itself at zero width
                tables[pane] = $('#table-' + pane).DataTable({ pageLength: 25, order: TABLES[pane].order, columnDefs: [{ orderable: false, targets: TABLES[pane].actionCol }] });
            }
            return tables[pane];
        }
        var KEY = 'lmsLearnerTab', first = 'completed';
        try { var saved = localStorage.getItem(KEY); if (TABLES[saved]) first = saved; } catch (e) {}
        $('#statusTabs [data-pane="' + first + '"]').tab('show');
        $('#statusTabs [data-toggle="pill"]').on('shown.bs.tab', function () {
            var pane = $(this).data('pane');
            table(pane).columns.adjust();
            try { localStorage.setItem(KEY, pane); } catch (e) {}
        });
        table(first);

        // ── Reminders (Pending tab) ──
        var TOKEN = @json(csrf_token());
        var URLS = { settings: @json(route('admin.exams.learning.reminders.settings')), send: @json(route('admin.exams.learning.reminders.send')) };
        function post(url, data) {
            return $.ajax({ url: url, method: 'POST', data: $.extend({ _token: TOKEN }, data), dataType: 'json' });
        }
        function say(msg, type) { if (window.CosecsaAlert) CosecsaAlert.toast(msg, type || 'success'); else alert(msg); }
        function fail(xhr) { say((xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong. Please try again.', 'error'); }
        function fmtDate(iso) { return new Date(iso).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); }
        function ask(message, title, ok) {
            return window.CosecsaAlert ? CosecsaAlert.confirm(message, { title: title, confirmText: ok, variant: 'question' }) : Promise.resolve(confirm(message));
        }

        function showDue(r) {
            $('#remDue').text(r.enabled ? r.due_now + ' due at the next automatic run (daily, 08:30 UTC).' : 'Switched off — nothing is sent automatically.');
        }
        $('#remEnabled').on('change', function () { $('#remEnabledLabel').text(this.checked ? 'On' : 'Off'); });
        $('#remSave').on('click', function () {
            var $b = $(this).prop('disabled', true);
            post(URLS.settings, { enabled: $('#remEnabled').is(':checked') ? 1 : 0, after_days: $('#remAfter').val(), max_reminders: $('#remMax').val() })
                .done(function (res) { showDue(res.reminders); $('#remSaved').text('Saved'); setTimeout(function () { $('#remSaved').text(''); }, 2500); })
                .fail(fail).always(function () { $b.prop('disabled', false); });
        });

        // Marks the rows that were just reminded, without reloading.
        function applySent(res) {
            var dt = table('pending');
            (res.learners || []).forEach(function (l) {
                var tr = $('#table-pending tr[data-user="' + l.id + '"]');
                tr.find('.rem-cell').html(l.reminders_sent + ' sent · last ' + fmtDate(l.last_reminded_at)).attr('data-order', Date.parse(l.last_reminded_at) / 1000);
                if (dt.row(tr).length) dt.row(tr).invalidate('dom');
            });
            dt.draw(false);
            showDue(res.reminders);
        }

        $('#table-pending').on('click', '.rem-one', function () {
            var $tr = $(this).closest('tr'), $b = $(this), name = $tr.data('name'), id = $tr.data('user');
            ask('Email ' + name + ' a reminder to start the course?', 'Send reminder?', 'Send reminder').then(function (yes) {
                if (!yes) return;
                $b.prop('disabled', true);
                post(URLS.send, { user_ids: [id] }).done(function (res) { say(res.message, res.sent ? 'success' : 'warning'); applySent(res); })
                    .fail(fail).always(function () { $b.prop('disabled', $tr.data('login') != 1); });
            });
        });
        $('#remAll').on('click', function () {
            var $b = $(this), n = $('#remAllCount').text();
            ask('Email a reminder to all ' + n + ' pending examiner(s) whose login was sent? This sends right away, whatever the days above say.', 'Remind all pending?', 'Send ' + n + ' reminder(s)').then(function (yes) {
                if (!yes) return;
                $b.prop('disabled', true);
                post(URLS.send, {}).done(function (res) { say(res.message, res.sent ? 'success' : 'warning'); applySent(res); })
                    .fail(fail).always(function () { $b.prop('disabled', false); });
            });
        });


        // Reset actions confirm in a modal instead of the browser's confirm() box.
        var pending = null;
        $(document).on('submit', 'form[data-confirm-title]', function (e) {
            e.preventDefault();
            pending = this;
            $('#confirmResetTitle').text(this.dataset.confirmTitle);
            $('#confirmResetName').text(this.dataset.confirmName);
            $('#confirmResetText').text(this.dataset.confirmText);
            $('#confirmResetOk').text(this.dataset.confirmButton).prop('disabled', false);
            $('#confirmResetModal').modal('show');
        });
        $('#confirmResetOk').on('click', function () {
            if (!pending) return;
            $(this).prop('disabled', true);
            pending.submit(); // native submit skips the handler above
        });
        $('#confirmResetModal').on('hidden.bs.modal', function () { pending = null; });
    });
</script>
@endpush

@extends('layout.app')

@section('title', 'Annual Subscription Report')

@push('styles')
<style>
    .fee-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px;
                padding:20px 24px; color:#fff; margin-bottom:1.2rem; }

    .entity-link { color:#a02626; font-weight:500; text-decoration:none; }
    .entity-link:hover { color:#a02626; text-decoration:underline; }

    .fee-card { border-top:3px solid #a02626; }
    .fee-group-title { font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:#888; margin:12px 0 4px; font-weight:700; }

    .report-table thead th { background:#f8f0f0; color:#a02626; font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; }
    body.dark-mode .report-table thead th { background:#252c3b; color:#f48a8a; }

    .status-pill { display:inline-block; padding:3px 12px; border-radius:20px; font-weight:600; font-size:.78rem; }
    .status-Paid    { background:#d4edda; color:#155724; }
    .status-Partial { background:#fff3cd; color:#856404; }
    .status-Unpaid  { background:#f8d7da; color:#721c24; }
    .status-Waived  { background:#d1ecf1; color:#0c5460; }
    .status-None    { background:#e2e3e5; color:#383d41; }

    .sum-chip { flex:1 1 130px; min-width:130px; background:#fff; border:1px solid #e0e9f2; border-left:4px solid #a02626;
                border-radius:8px; padding:10px 14px; box-shadow:0 1px 4px rgba(0,0,0,.07); }
    body.dark-mode .sum-chip { background:#1e2330; border-color:#2d3748; }
    .sum-chip .sc-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; }
    body.dark-mode .sum-chip .sc-label { color:#94a3b8; }
    .sum-chip .sc-value { font-size:1.25rem; font-weight:700; color:#141d23; }
    body.dark-mode .sum-chip .sc-value { color:#f1f5f9; }
    .sum-chip.owing { border-left-color:#FEC503; background:#fff8dc; }
    body.dark-mode .sum-chip.owing { background:#2d3030; }

    .token-chip { cursor:pointer; border:1px solid #a02626; color:#a02626; background:#fff; border-radius:4px;
                  font-size:.72rem; padding:2px 8px; margin:0 3px 3px 0; }
    .token-chip:hover { background:#a02626; color:#fff; }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                <div class="fee-hero d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-file-invoice-dollar mr-2"></i>Annual Subscription Report</h4>
                        <div style="font-size:.85rem;opacity:.85;">
                            Per-year subscription payment status across all fellows. Fellows with no record
                            for the year count as <strong>No Record</strong> (treated as owing).
                            <a href="{{ url('admin/fees') }}" style="color:#fff;text-decoration:underline;">Back to Manage Fees</a>
                        </div>
                    </div>
                    @if (Auth::user()->hasPermission('fees.manage') && ($summary['owing'] ?? 0) > 0)
                    <button type="button" class="btn btn-sm" style="background:#FEC503;border-color:#FEC503;color:#3a2a00;font-weight:600;"
                            onclick="$('#reminderModal').modal('show')">
                        <i class="fas fa-envelope-open-text mr-1"></i>Send Reminders ({{ $summary['owing'] ?? 0 }} owing)
                    </button>
                    @endif
                </div>

                {{-- ── Summary ── --}}
                <div class="d-flex flex-wrap" style="gap:.75rem;margin-bottom:1.1rem;">
                    <div class="sum-chip"><div class="sc-label">Total Fellows</div><div class="sc-value">{{ $summary['total_fellows'] ?? 0 }}</div></div>
                    <div class="sum-chip"><div class="sc-label">Paid</div><div class="sc-value" style="color:#28a745;">{{ $summary['paid'] ?? 0 }}</div></div>
                    <div class="sum-chip"><div class="sc-label">Partial</div><div class="sc-value" style="color:#856404;">{{ $summary['partial'] ?? 0 }}</div></div>
                    <div class="sum-chip"><div class="sc-label">Unpaid</div><div class="sc-value" style="color:#d64545;">{{ $summary['unpaid'] ?? 0 }}</div></div>
                    <div class="sum-chip"><div class="sc-label">Waived</div><div class="sc-value" style="color:#0c5460;">{{ $summary['waived'] ?? 0 }}</div></div>
                    <div class="sum-chip"><div class="sc-label">No Record</div><div class="sc-value">{{ $summary['none'] ?? 0 }}</div></div>
                    <div class="sum-chip owing"><div class="sc-label">Owing</div><div class="sc-value" style="color:#a02626;">{{ $summary['owing'] ?? 0 }}</div></div>
                    <div class="sum-chip"><div class="sc-label">Due (USD)</div><div class="sc-value">{{ number_format($summary['amount_due'] ?? 0, 2) }}</div></div>
                    <div class="sum-chip"><div class="sc-label">Collected (USD)</div><div class="sc-value" style="color:#28a745;">{{ number_format($summary['amount_collected'] ?? 0, 2) }}</div></div>
                    <div class="sum-chip"><div class="sc-label">Outstanding (USD)</div><div class="sc-value" style="color:#d64545;">{{ number_format($summary['outstanding'] ?? 0, 2) }}</div></div>
                </div>

                {{-- ── Filters ── --}}
                <div class="card mt-3">
                    <div class="card-body">
                        <form method="GET" action="{{ url('admin/fees/subscriptions/report') }}" class="d-flex flex-wrap align-items-end" style="gap:.75rem;">
                            <div>
                                <label class="d-block mb-1 small font-weight-bold text-muted">Year</label>
                                <select name="year" class="form-control form-control-sm" style="width:110px;" onchange="this.form.submit()">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ (string)$year === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="d-block mb-1 small font-weight-bold text-muted">Status</label>
                                <select name="status" class="form-control form-control-sm" style="width:130px;" onchange="this.form.submit()">
                                    <option value="">All statuses</option>
                                    @foreach(['Paid','Partial','Unpaid','Waived','None'] as $opt)
                                        <option value="{{ $opt }}" {{ ($filters['status'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt === 'None' ? 'No Record' : $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="d-block mb-1 small font-weight-bold text-muted">Country</label>
                                <select name="country_id" class="form-control form-control-sm" style="width:180px;" onchange="this.form.submit()">
                                    <option value="">All countries</option>
                                    @foreach($countries as $c)
                                        <option value="{{ $c->id }}" {{ (string)($filters['country_id'] ?? '') === (string)$c->id ? 'selected' : '' }}>{{ $c->country_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="d-block mb-1 small font-weight-bold text-muted">Search</label>
                                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, email, reg no..."
                                       class="form-control form-control-sm" onchange="this.form.submit()">
                            </div>
                            <div>
                                <a href="{{ url('admin/fees/subscriptions/report') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ── Report table ── --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size:1rem;">
                            <i class="fas fa-users mr-2" style="color:#a02626;"></i>{{ $year }} Subscription Status
                            <span class="badge badge-pill text-white ml-1" style="background:#a02626;">{{ $rows->count() }}</span>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table id="subscriptionReportTable" class="table table-sm table-bordered table-striped report-table mb-0" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fellow</th>
                                    <th>Country</th>
                                    <th>Fellowship Type</th>
                                    <th>Status</th>
                                    <th>Due (USD)</th>
                                    <th>Paid (USD)</th>
                                    <th>Outstanding (USD)</th>
                                    <th>Date Paid</th>
                                    <th>Mode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <a href="{{ url('admin/associates/fellows/view/' . $row->fellow_id) }}#tab-subs" class="entity-link">{{ $row->name }}</a>
                                        @if($row->email)
                                            <div class="small text-muted">{{ $row->email }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $row->country_name ?? '—' }}</td>
                                    <td>{{ $row->fellowship_type ?? '—' }}</td>
                                    <td>
                                        <span class="status-pill status-{{ $row->effective_status }}">
                                            {{ $row->effective_status === 'None' ? 'No Record' : $row->effective_status }}
                                        </span>
                                    </td>
                                    <td>{{ $row->amount_due !== null ? number_format($row->amount_due, 2) : '—' }}</td>
                                    <td>{{ $row->amount_paid !== null ? number_format($row->amount_paid, 2) : '—' }}</td>
                                    <td>{{ $row->outstanding !== null ? number_format($row->outstanding, 2) : '—' }}</td>
                                    <td>{{ $row->date_paid ? \Carbon\Carbon::parse($row->date_paid)->format('d M Y') : '—' }}</td>
                                    <td>{{ (!$row->mode_of_payment || preg_match('/^\d{4}-\d{2}-\d{2}/', $row->mode_of_payment)) ? '—' : $row->mode_of_payment }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>

{{-- ── Send Reminder Modal ── --}}
@if (Auth::user()->hasPermission('fees.manage') && ($summary['owing'] ?? 0) > 0)
<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.fees.subscriptions.remind') }}">
                @csrf
                <div class="modal-header" style="border-bottom:2px solid #a02626;">
                    <h5 class="modal-title" style="color:#a02626;"><i class="fas fa-envelope-open-text mr-1"></i>Send Subscription Reminders</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2" style="font-size:.85rem;">
                        This will email <strong>{{ $summary['owing'] ?? 0 }} fellow(s)</strong> with an outstanding {{ $year }}
                        annual subscription (status Unpaid, Partial, or No Record). Waived fellows are never emailed.
                    </div>
                    <input type="hidden" name="year" value="{{ $year }}">
                    <div class="form-group">
                        <label class="font-weight-bold small">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" required
                               placeholder="Annual Subscription Reminder for {{ $year }}"
                               value="Annual Subscription Reminder for {{ $year }}">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Body <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="4" required>Dear @{{first_name}},

This is a reminder that your COSECSA annual subscription for @{{year}} is still outstanding. Please arrange payment at your earliest convenience.

Kind Regards,
COSECSA Secretariat</textarea>
                        <div class="mt-2">
                            <small class="text-muted">Insert a token:</small>
                            @foreach(['name','first_name','year','country','fellowship_type','amount_due','amount_paid','outstanding'] as $tok)
                                <span class="token-chip" onclick="insertToken(this)">{{ '{' . '{' . $tok . '}' . '}' }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn font-weight-bold" style="background:#a02626;border-color:#a02626;color:#fff;"
                            onclick="return confirm('Send reminder emails to all {{ $summary['owing'] ?? 0 }} outstanding fellows for {{ $year }}?');">
                        <i class="fas fa-paper-plane mr-1"></i>Send Reminder Emails
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#subscriptionReportTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copyHtml5',  className: 'btn-sm', exportOptions: { columns: ':not(.no-export)' } },
            { extend: 'csvHtml5',   className: 'btn-sm', title: 'annual_subscriptions_{{ $year }}', exportOptions: { columns: ':not(.no-export)' } },
            { extend: 'excelHtml5', className: 'btn-sm', title: 'annual_subscriptions_{{ $year }}', exportOptions: { columns: ':not(.no-export)' } },
            { extend: 'pdfHtml5',   className: 'btn-sm', title: 'Annual Subscription Report {{ $year }}', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: ':not(.no-export)' } },
            { extend: 'print',      className: 'btn-sm', exportOptions: { columns: ':not(.no-export)' } }
        ],
        columnDefs: [
            { orderable: false, targets: 0, render: function (data, type, row, meta) { return meta.row + 1; } }
        ],
        pageLength: 25,
        order: []
    });
});

// Insert a {{token}} into the reminder body at the cursor.
function insertToken(el) {
    var ta = document.querySelector('#reminderModal textarea[name="body"]');
    if (!ta) return;
    var token = el.textContent;
    var start = ta.selectionStart || 0;
    var end = ta.selectionEnd || 0;
    ta.value = ta.value.slice(0, start) + token + ta.value.slice(end);
    ta.selectionStart = ta.selectionEnd = start + token.length;
    ta.focus();
}
</script>
@endpush
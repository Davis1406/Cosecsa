@extends('layout.app')

@section('title', 'Fees')

@push('styles')
<style>
    .fee-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px;
                padding:20px 24px; color:#fff; margin-bottom:1.2rem; }
    .stat-chip { display:inline-flex; flex-direction:column; align-items:center;
                 background:#fff; border:1px solid #e9ecef; border-radius:8px;
                 padding:10px 18px; min-width:130px; text-align:center; }
    .stat-chip .lbl { font-size:.66rem; color:#999; text-transform:uppercase; letter-spacing:.04em; }
    .stat-chip .val { font-size:1.2rem; font-weight:700; color:#222; }
    body.dark-mode .stat-chip { background:#374151; border-color:#4a5568; }
    body.dark-mode .stat-chip .val { color:#e0e0e0; }

    .fee-card { border-top:3px solid #a02626; }
    .fee-type-row { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #f5f5f5; font-size:.86rem; }
    body.dark-mode .fee-type-row { border-color:#4a5568; }
    .fee-amount { font-weight:700; color:#a02626; }
    .fee-group-title { font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:#888; margin:12px 0 4px; font-weight:700; }

    #payerResults { position:absolute; z-index:1000; background:#fff; border:1px solid #ddd; border-radius:6px;
                    width:100%; max-height:260px; overflow-y:auto; box-shadow:0 4px 14px rgba(0,0,0,.12); display:none; }
    body.dark-mode #payerResults { background:#374151; border-color:#4a5568; }
    #payerResults .pr-item { padding:8px 12px; cursor:pointer; border-bottom:1px solid #f0f0f0; }
    #payerResults .pr-item:hover { background:#f8f0f0; }
    body.dark-mode #payerResults .pr-item { border-color:#4a5568; }
    body.dark-mode #payerResults .pr-item:hover { background:#4a5568; }

    .fees-table thead th { background:#f8f0f0; color:#a02626; font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; }
    .status-pill { display:inline-block; padding:3px 12px; border-radius:20px; font-weight:600; font-size:.78rem; }
    .status-Paid { background:#d4edda; color:#155724; }
    .status-Partial { background:#fff3cd; color:#856404; }
    .status-Unpaid { background:#f8d7da; color:#721c24; }
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
                        <h4 class="mb-0"><i class="fas fa-money-check-alt mr-2"></i>Fees</h4>
                        <div style="font-size:.85rem;opacity:.85;">
                            Fellowship registration, annual subscriptions, graduation, gown, transcript, and programme
                            entry / exam fees. Fee amounts for programmes are managed under Programmes.
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap mb-3" style="gap:.75rem;">
                    <div class="stat-chip"><span class="lbl">Total Collected</span><span class="val">${{ number_format($totalCollected, 2) }}</span></div>
                    <div class="stat-chip"><span class="lbl">Outstanding</span><span class="val">${{ number_format($totalDue, 2) }}</span></div>
                    <div class="stat-chip"><span class="lbl">Paid Records</span><span class="val">{{ number_format($paidCount) }}</span></div>
                    <div class="stat-chip"><span class="lbl">Total Records</span><span class="val">{{ number_format($log->count()) }}</span></div>
                </div>

                <div class="row">
                    {{-- ── Programme Fees (read-only reference) ── --}}
                    <div class="col-md-6">
                        <div class="card fee-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title" style="font-size:1rem;"><i class="fas fa-graduation-cap mr-2" style="color:#a02626;"></i>Programme Fees</h3>
                                <a href="{{ url('admin/programmes/list') }}" class="btn btn-xs btn-outline-secondary">
                                    <i class="fas fa-external-link-alt mr-1"></i>Edit
                                </a>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-2">
                                    Entry, exam, and repeat fees per programme — amounts are managed on the
                                    <a href="{{ url('admin/programmes/list') }}">Programmes</a> page, but you can
                                    record a payment against them via <strong>Record a Payment</strong> below.
                                </p>
                                @foreach($programmes as $p)
                                    <div class="fee-type-row">
                                        <div>{{ $p->name }}</div>
                                        <div class="text-right" style="font-size:.78rem;">
                                            <span class="fee-amount">Entry {{ number_format($p->entry_fee, 0) }}</span> ·
                                            Exam {{ number_format($p->exam_fee, 0) }} ·
                                            Repeat {{ number_format($p->repeat_fee, 0) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ── Fee Type Catalog ── --}}
                    <div class="col-md-6">
                        <div class="card fee-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title" style="font-size:1rem;"><i class="fas fa-list-ul mr-2" style="color:#a02626;"></i>Fee Catalog</h3>
                                <button type="button" class="btn btn-xs btn-outline-danger" onclick="toggleAddFeeForm()">
                                    <i class="fas fa-plus mr-1"></i>Add
                                </button>
                            </div>
                            <div class="card-body">

                                {{-- Inline add-fee form, collapsed by default --}}
                                <form method="POST" action="{{ route('admin.fees.types.store') }}" id="addFeeTypeForm" class="mb-3 pb-3" style="display:none;border-bottom:1px solid #eee;">
                                    @csrf
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold mb-1">Group</label>
                                        <input type="text" name="fee_group" class="form-control form-control-sm" list="feeGroupList" required placeholder="e.g. Annual Subscription">
                                        <datalist id="feeGroupList">
                                            @foreach($feeTypes->keys() as $g)<option value="{{ $g }}">@endforeach
                                        </datalist>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold mb-1">Name</label>
                                        <input type="text" name="name" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-8 mb-2">
                                            <label class="small font-weight-bold mb-1">Amount</label>
                                            <input type="number" step="0.01" min="0" name="amount" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="form-group col-4 mb-2">
                                            <label class="small font-weight-bold mb-1">Currency</label>
                                            <input type="text" name="currency" class="form-control form-control-sm" value="USD">
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="addAppliesSub" name="applies_to_subscription">
                                            <label class="custom-control-label small" for="addAppliesSub">Routes into a fellow's Annual Subscription record</label>
                                        </div>
                                    </div>
                                    <div class="d-flex" style="gap:.4rem;">
                                        <button type="submit" class="btn btn-sm font-weight-bold" style="background:#a02626;border-color:#a02626;color:#fff;">Create</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAddFeeForm()">Cancel</button>
                                    </div>
                                </form>

                                @foreach($feeTypes as $groupName => $types)
                                    <div class="fee-group-title">{{ $groupName }}</div>
                                    @foreach($types as $ft)
                                        <div class="fee-type-row">
                                            <div>
                                                {{ $ft->name }}
                                                @unless($ft->is_active)<span class="badge badge-secondary ml-1">Inactive</span>@endunless
                                            </div>
                                            <div class="d-flex align-items-center" style="gap:.4rem;">
                                                <span class="fee-amount">{{ $ft->currency }} {{ number_format($ft->amount, 0) }}</span>
                                                <button type="button" class="btn btn-xs btn-outline-warning"
                                                        onclick='editFeeType({{ $ft->id }}, {!! json_encode($ft->fee_group) !!}, {!! json_encode($ft->name) !!}, {{ $ft->amount }}, {!! json_encode($ft->currency) !!}, {{ $ft->applies_to_subscription ? 1 : 0 }}, {{ $ft->is_active ? 1 : 0 }})'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="{{ url('admin/fees/types/' . $ft->id) }}" style="display:inline;"
                                                      onsubmit="return confirm('Delete fee type &quot;{{ $ft->name }}&quot;?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Record Payment (collapsed behind a + button) ── --}}
                <div class="card fee-card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title" style="font-size:1rem;"><i class="fas fa-hand-holding-usd mr-2" style="color:#a02626;"></i>Record a Payment</h3>
                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="toggleRecordPaymentForm()">
                            <i class="fas fa-plus mr-1"></i>Record Payment
                        </button>
                    </div>
                    <div class="card-body" id="recordPaymentWrapper" style="display:none;">
                        <form method="POST" action="{{ route('admin.fees.record-payment') }}" id="recordPaymentForm">
                            @csrf
                            <div class="form-group position-relative">
                                <label class="font-weight-bold small">Search Person <span class="text-danger">*</span></label>
                                <input type="text" id="payerSearch" class="form-control" autocomplete="off"
                                       placeholder="Type a name, email, or entry number...">
                                <div id="payerResults"></div>
                                <input type="hidden" name="payer_type" id="payer_type" required>
                                <input type="hidden" name="payer_id" id="payer_id" required>
                                <input type="hidden" name="payer_name" id="payer_name" required>
                                <input type="hidden" name="programme_fee_amount" id="programme_fee_amount">
                                <small class="text-muted" id="payerSelectedLabel"></small>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold small">Fee Type <span class="text-danger">*</span></label>
                                    <select name="fee_type_id" id="fee_type_id" class="form-control" required>
                                        <option value="">-- Select fee --</option>
                                        <optgroup label="Programme Fees" id="programmeFeeOptgroup" style="display:none;">
                                            <option value="programme" id="programmeFeeOption" data-amount="" data-subscription="0"></option>
                                        </optgroup>
                                        @foreach($feeTypes as $groupName => $types)
                                            <optgroup label="{{ $groupName }}">
                                                @foreach($types->where('is_active', true) as $ft)
                                                    <option value="{{ $ft->id }}"
                                                            data-amount="{{ $ft->amount }}"
                                                            data-subscription="{{ $ft->applies_to_subscription ? 1 : 0 }}">
                                                        {{ $ft->name }} ({{ $ft->currency }} {{ number_format($ft->amount, 0) }})
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6" id="yearField" style="display:none;">
                                    <label class="font-weight-bold small">Subscription Year</label>
                                    <input type="number" name="year" class="form-control" value="{{ date('Y') }}" min="2000" max="2100">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label class="font-weight-bold small">Amount Paid <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="amount_paid" id="amount_paid" class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="font-weight-bold small">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="Paid">Paid</option>
                                        <option value="Partial">Partial</option>
                                        <option value="Unpaid">Unpaid</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="font-weight-bold small">Date Paid</label>
                                    <input type="date" name="date_paid" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold small">Mode of Payment</label>
                                    <select name="mode_of_payment" class="form-control">
                                        <option value="">-- Select --</option>
                                        <option>Bank Transfer</option>
                                        <option>Online Payment</option>
                                        <option>Cash</option>
                                        <option>Cheque</option>
                                        <option>Mobile Money</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="font-weight-bold small">Reference / Invoice #</label>
                                    <input type="text" name="reference_number" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold small">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="d-flex" style="gap:.4rem;">
                                <button type="submit" class="btn font-weight-bold" style="background:#a02626;border-color:#a02626;color:#fff;">
                                    <i class="fas fa-check mr-1"></i>Save Payment
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleRecordPaymentForm()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ── Payments Log ── --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size:1rem;"><i class="fas fa-history mr-2" style="color:#a02626;"></i>Payments Log</h3>
                    </div>
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ url('admin/fees') }}" class="d-flex flex-wrap align-items-end" style="gap:.75rem;">
                            <div>
                                <label class="d-block mb-1 small font-weight-bold text-muted">Search</label>
                                <input type="text" name="q" value="{{ $search }}" placeholder="Payer name..."
                                       class="form-control form-control-sm" onchange="this.form.submit()">
                            </div>
                            <div>
                                <label class="d-block mb-1 small font-weight-bold text-muted">Year</label>
                                <select name="year" class="form-control form-control-sm" style="width:120px;" onchange="this.form.submit()">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ (string)$year === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                    <option value="all" {{ $year === 'all' ? 'selected' : '' }}>All years</option>
                                </select>
                            </div>
                            <div>
                                <label class="d-block mb-1 small font-weight-bold text-muted">Fee Group</label>
                                <select name="group" class="form-control form-control-sm" style="width:180px;" onchange="this.form.submit()">
                                    <option value="">All groups</option>
                                    <option value="Programme Fees" {{ $group == 'Programme Fees' ? 'selected' : '' }}>Programme Fees</option>
                                    @foreach($feeTypes->keys() as $g)
                                        <option value="{{ $g }}" {{ $group == $g ? 'selected' : '' }}>{{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="d-block mb-1 small font-weight-bold text-muted">Payer Type</label>
                                <select name="payer_type" class="form-control form-control-sm" style="width:130px;" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="fellow" {{ $payerType == 'fellow' ? 'selected' : '' }}>Fellow</option>
                                    <option value="trainee" {{ $payerType == 'trainee' ? 'selected' : '' }}>Trainee</option>
                                    <option value="candidate" {{ $payerType == 'candidate' ? 'selected' : '' }}>Candidate</option>
                                </select>
                            </div>
                            <div>
                                <label class="d-block mb-1 small font-weight-bold text-muted">Status</label>
                                <select name="status" class="form-control form-control-sm" style="width:120px;" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="Paid" {{ $status == 'Paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="Partial" {{ $status == 'Partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="Unpaid" {{ $status == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                                </select>
                            </div>
                            <div>
                                <a href="{{ url('admin/fees') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                            </div>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <table id="feesTable" class="table table-sm table-bordered table-striped fees-table mb-0" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Payer</th>
                                    <th>Type</th>
                                    <th>Fee</th>
                                    <th>Due</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                    <th>Date Paid</th>
                                    <th>Mode</th>
                                    <th class="no-export">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($log as $row)
                                <tr>
                                    <td>{{ $row->payer_name }}</td>
                                    <td>{{ ucfirst($row->payer_type) }}</td>
                                    <td>{{ $row->fee_group }} — {{ $row->fee_name }}</td>
                                    <td>{{ number_format($row->amount_due ?? 0, 2) }}</td>
                                    <td>{{ number_format($row->amount_paid ?? 0, 2) }}</td>
                                    <td><span class="status-pill status-{{ $row->status }}">{{ $row->status }}</span></td>
                                    <td>{{ $row->date_paid ? \Carbon\Carbon::parse($row->date_paid)->format('d M Y') : '—' }}</td>
                                    <td>{{ $row->mode_of_payment ?: '—' }}</td>
                                    <td class="no-export">
                                        <button type="button" class="btn btn-xs btn-outline-warning"
                                                onclick='editPayment({!! json_encode($row) !!})'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ url('admin/fees/payment/' . $row->row_key) }}" style="display:inline;"
                                              onsubmit="return confirm('Delete this payment record?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
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

{{-- ── Edit Fee Type Modal ── --}}
<div class="modal fade" id="editFeeTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editFeeTypeForm">
                @csrf @method('PUT')
                <div class="modal-header" style="border-bottom:2px solid #a02626;">
                    <h5 class="modal-title" style="color:#a02626;">Edit Fee Type</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Group <span class="text-danger">*</span></label>
                        <input type="text" name="fee_group" id="editFtGroup" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editFtName" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-8">
                            <label>Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="amount" id="editFtAmount" class="form-control" required>
                        </div>
                        <div class="form-group col-4">
                            <label>Currency</label>
                            <input type="text" name="currency" id="editFtCurrency" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="editFtSub" name="applies_to_subscription">
                            <label class="custom-control-label" for="editFtSub">Routes into a fellow's Annual Subscription record</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="editFtActive" name="is_active">
                            <label class="custom-control-label" for="editFtActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn font-weight-bold" style="background:#a02626;border-color:#a02626;color:#fff;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit Payment Modal ── --}}
<div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editPaymentForm">
                @csrf @method('PUT')
                <div class="modal-header" style="border-bottom:2px solid #a02626;">
                    <h5 class="modal-title" style="color:#a02626;">Edit Payment</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong id="epPayerName"></strong> — <span id="epFeeName" class="text-muted"></span></p>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Amount Paid <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="amount_paid" id="epAmountPaid" class="form-control" required>
                        </div>
                        <div class="form-group col-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" id="epStatus" class="form-control" required>
                                <option value="Paid">Paid</option>
                                <option value="Partial">Partial</option>
                                <option value="Unpaid">Unpaid</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Date Paid</label>
                            <input type="date" name="date_paid" id="epDatePaid" class="form-control">
                        </div>
                        <div class="form-group col-6">
                            <label>Mode of Payment</label>
                            <input type="text" name="mode_of_payment" id="epMode" class="form-control">
                        </div>
                    </div>
                    <div class="form-group" id="epReferenceGroup">
                        <label>Reference / Invoice #</label>
                        <input type="text" name="reference_number" id="epReference" class="form-control">
                    </div>
                    <div class="form-group" id="epNotesGroup">
                        <label>Notes</label>
                        <textarea name="notes" id="epNotes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn font-weight-bold" style="background:#a02626;border-color:#a02626;color:#fff;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#feesTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copyHtml5',  className: 'btn-sm', exportOptions: { columns: ':not(.no-export)' } },
            { extend: 'csvHtml5',   className: 'btn-sm', title: 'fees_log', exportOptions: { columns: ':not(.no-export)' } },
            { extend: 'excelHtml5', className: 'btn-sm', title: 'fees_log', exportOptions: { columns: ':not(.no-export)' } },
            { extend: 'pdfHtml5',   className: 'btn-sm', title: 'Fees Log', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: ':not(.no-export)' } },
            { extend: 'print',      className: 'btn-sm', exportOptions: { columns: ':not(.no-export)' } }
        ],
        columnDefs: [{ orderable: false, targets: -1 }],
        pageLength: 25,
        order: [[6, 'desc']]
    });
});

// ── Payer search ──────────────────────────────────────────────────────────
var payerSearchTimer = null;
document.getElementById('payerSearch').addEventListener('input', function () {
    var q = this.value.trim();
    clearTimeout(payerSearchTimer);
    if (q.length < 2) { document.getElementById('payerResults').style.display = 'none'; return; }
    payerSearchTimer = setTimeout(function () {
        fetch('{{ route("admin.fees.search-payer") }}?q=' + encodeURIComponent(q))
            .then(function (r) { return r.json(); })
            .then(function (results) {
                var box = document.getElementById('payerResults');
                box.innerHTML = '';
                if (!results.length) { box.style.display = 'none'; return; }
                results.forEach(function (r) {
                    var div = document.createElement('div');
                    div.className = 'pr-item';
                    div.innerHTML = '<strong>' + r.name + '</strong><br><small class="text-muted">' + r.subtitle + '</small>';
                    div.onclick = function () { selectPayer(r); };
                    box.appendChild(div);
                });
                box.style.display = 'block';
            });
    }, 300);
});

function selectPayer(r) {
    document.getElementById('payer_type').value = r.type;
    document.getElementById('payer_id').value = r.id;
    document.getElementById('payer_name').value = r.name;
    document.getElementById('payerSearch').value = r.name;
    document.getElementById('payerSelectedLabel').textContent = 'Selected: ' + r.subtitle;
    document.getElementById('payerResults').style.display = 'none';

    var pfGroup = document.getElementById('programmeFeeOptgroup');
    var pfOption = document.getElementById('programmeFeeOption');
    if (r.programme_fee) {
        pfOption.textContent = r.programme_fee.label + ' (USD ' + r.programme_fee.amount + ')';
        pfOption.dataset.amount = r.programme_fee.amount;
        pfGroup.style.display = '';
    } else {
        pfGroup.style.display = 'none';
        if (document.getElementById('fee_type_id').value === 'programme') {
            document.getElementById('fee_type_id').value = '';
        }
    }
}

document.addEventListener('click', function (e) {
    if (!document.getElementById('payerResults').contains(e.target) && e.target.id !== 'payerSearch') {
        document.getElementById('payerResults').style.display = 'none';
    }
});

// ── Fee type select: show year field only for subscription-linked fees ─────
document.getElementById('fee_type_id').addEventListener('change', function () {
    var opt = this.options[this.selectedIndex];
    var isSub = opt.dataset.subscription === '1';
    var amount = opt.dataset.amount;
    document.getElementById('yearField').style.display = isSub ? '' : 'none';
    if (amount) document.getElementById('amount_paid').value = amount;
    document.getElementById('programme_fee_amount').value = this.value === 'programme' ? amount : '';
});

// ── Toggle inline add-fee form ──────────────────────────────────────────────
function toggleAddFeeForm() {
    var form = document.getElementById('addFeeTypeForm');
    form.style.display = form.style.display === 'none' ? '' : 'none';
}

// ── Toggle Record a Payment form ────────────────────────────────────────────
function toggleRecordPaymentForm() {
    var el = document.getElementById('recordPaymentWrapper');
    el.style.display = el.style.display === 'none' ? '' : 'none';
}

// ── Edit fee type ────────────────────────────────────────────────────────
function editFeeType(id, group, name, amount, currency, appliesSub, isActive) {
    document.getElementById('editFeeTypeForm').action = '/admin/fees/types/' + id;
    document.getElementById('editFtGroup').value = group;
    document.getElementById('editFtName').value = name;
    document.getElementById('editFtAmount').value = amount;
    document.getElementById('editFtCurrency').value = currency;
    document.getElementById('editFtSub').checked = appliesSub == 1;
    document.getElementById('editFtActive').checked = isActive == 1;
    $('#editFeeTypeModal').modal('show');
}

// ── Edit payment ─────────────────────────────────────────────────────────
function editPayment(row) {
    document.getElementById('editPaymentForm').action = '/admin/fees/payment/' + row.row_key;
    document.getElementById('epPayerName').textContent = row.payer_name;
    document.getElementById('epFeeName').textContent = row.fee_group + ' — ' + row.fee_name;
    document.getElementById('epAmountPaid').value = row.amount_paid || 0;
    document.getElementById('epStatus').value = row.status;
    document.getElementById('epDatePaid').value = row.date_paid ? row.date_paid.substring(0, 10) : '';
    document.getElementById('epMode').value = row.mode_of_payment || '';
    var isSub = row.row_key.startsWith('sub-');
    var isProgrammeFee = row.row_key.startsWith('tfee-') || row.row_key.startsWith('cfee-');
    document.getElementById('epReferenceGroup').style.display = isSub ? 'none' : '';
    document.getElementById('epNotesGroup').style.display = (isSub || isProgrammeFee) ? 'none' : '';
    document.getElementById('epReference').value = row.reference_number || '';
    document.getElementById('epNotes').value = row.notes || '';
    $('#editPaymentModal').modal('show');
}
</script>
@endpush

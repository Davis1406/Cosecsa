@extends('layout.app')

@push('styles')
<style>
/* ══════════════════════════════════════
   SUBSCRIPTIONS MANAGE – COSECSA STYLES
══════════════════════════════════════ */
.form-label { font-size:.82rem; font-weight:600; color:#444; margin-bottom:3px; }
.req { color:#a02626; }

/* Table */
.subs-table th {
    font-size:.75rem; font-weight:700;
    color:#a02626; background:#fff5f5;
    white-space:nowrap;
}
.subs-table td { font-size:.83rem; vertical-align:middle; }

/* Status badges */
.sub-badge {
    display:inline-block; padding:3px 10px;
    border-radius:10px; font-size:.72rem; font-weight:700;
}
.sub-paid    { background:#d4edda; color:#155724; }
.sub-unpaid  { background:#f8d7da; color:#721c24; }
.sub-partial { background:#fff3cd; color:#856404; }
.sub-waived  { background:#e2e3e5; color:#383d41; }

/* Summary chips */
.summary-chip {
    border-radius:8px; padding:14px 18px;
    background:#fff; border:1px solid #e9ecef;
    display:flex; align-items:center; gap:12px;
}
.summary-chip .sc-icon {
    width:38px; height:38px; border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; background:#f0d4d4; color:#a02626;
}
.sc-label { font-size:.65rem; color:#999; margin-bottom:1px; }
.sc-val   { font-size:.95rem; font-weight:700; color:#222; }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 style="font-size:1.2rem;">
                        <i class="fas fa-receipt mr-2" style="color:#a02626;"></i>
                        Annual Subscriptions &mdash;
                        {{ trim(($fellow->firstname ?? '') . ' ' . ($fellow->lastname ?? '')) }}
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close py-2" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show py-2">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                <button type="button" class="close py-2" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif

            {{-- ── Action Bar ── --}}
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:8px;">
                <div>
                    <a href="{{ url('admin/associates/fellows/view/' . $fellow->fellow_id) }}"
                       class="btn btn-sm btn-secondary mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Profile
                    </a>
                    <a href="{{ url('admin/associates/fellows/edit/' . $fellow->fellow_id) }}"
                       class="btn btn-sm btn-warning">
                        <i class="fas fa-edit mr-1"></i> Edit Fellow
                    </a>
                </div>
                <button type="button" class="btn btn-sm btn-danger"
                        style="background:#a02626; border-color:#a02626;"
                        data-toggle="modal" data-target="#addSubModal">
                    <i class="fas fa-plus mr-1"></i> Add Subscription Year
                </button>
            </div>

            {{-- ── Summary Chips ── --}}
            @php
                $totalDue    = $subscriptions->sum('amount_due');
                $totalPaid   = $subscriptions->sum('amount_paid');
                $paidCount   = $subscriptions->where('status','Paid')->count();
                $unpaidCount = $subscriptions->whereIn('status',['Unpaid','Partial'])->count();
                $balance     = max(0, $totalDue - $totalPaid);
            @endphp
            <div class="row mb-3">
                <div class="col-6 col-md-3 mb-2">
                    <div class="summary-chip">
                        <div class="sc-icon"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <div class="sc-label">Total Years</div>
                            <strong class="sc-val">{{ $subscriptions->count() }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="summary-chip">
                        <div class="sc-icon"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="sc-label">Years Paid</div>
                            <strong class="sc-val">{{ $paidCount }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="summary-chip">
                        <div class="sc-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div>
                            <div class="sc-label">Total Collected</div>
                            <strong class="sc-val">USD {{ number_format($totalPaid, 2) }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="summary-chip">
                        <div class="sc-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <div>
                            <div class="sc-label">Outstanding</div>
                            <strong class="sc-val" style="{{ $balance > 0 ? 'color:#a02626;' : '' }}">
                                USD {{ number_format($balance, 2) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Subscriptions Table ── --}}
            <div class="card">
                <div class="card-header py-2" style="background:#fafafa; border-bottom:2px solid #f0d4d4;">
                    <h3 class="card-title" style="color:#a02626; font-size:.9rem;">
                        <i class="fas fa-table mr-2"></i>Subscription Records
                    </h3>
                    <div class="card-tools">
                        @if($unpaidCount > 0)
                            <span class="badge" style="background:#f8d7da; color:#721c24; font-size:.72rem;">
                                {{ $unpaidCount }} outstanding
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($subscriptions->count())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover subs-table mb-0">
                            <thead>
                            <tr>
                                <th class="px-3" style="width:70px;">Year</th>
                                <th style="width:100px;">Status</th>
                                <th>Amount Due</th>
                                <th>Amount Paid</th>
                                <th>Date Paid</th>
                                <th>Mode</th>
                                <th class="text-right pr-3" style="width:100px;">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($subscriptions as $sub)
                            <tr>
                                <td class="px-3"><strong>{{ $sub->year }}</strong></td>
                                <td>
                                    @php
                                        $bc = [
                                            'Paid'    => 'sub-paid',
                                            'Unpaid'  => 'sub-unpaid',
                                            'Partial' => 'sub-partial',
                                            'Waived'  => 'sub-waived',
                                        ][$sub->status] ?? 'sub-waived';
                                    @endphp
                                    <span class="sub-badge {{ $bc }}">{{ $sub->status }}</span>
                                </td>
                                <td>USD {{ number_format($sub->amount_due, 2) }}</td>
                                <td>
                                    @if($sub->amount_paid > 0)
                                        USD {{ number_format($sub->amount_paid, 2) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $sub->date_paid
                                        ? \Carbon\Carbon::parse($sub->date_paid)->format('d/m/Y')
                                        : '—' }}
                                </td>
                                <td>{{ $sub->mode_of_payment ?? '—' }}</td>
                                <td class="text-right pr-3">
                                    <button type="button"
                                            class="btn btn-xs btn-warning mr-1"
                                            title="Edit"
                                            onclick="openEditModal({{ json_encode($sub) }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST"
                                          action="{{ url('admin/associates/fellows/subscriptions/delete/' . $sub->id) }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('Delete {{ $sub->year }} subscription record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                            <tfoot style="background:#f9f9f9;">
                            <tr>
                                <td colspan="2" class="px-3 font-weight-bold" style="font-size:.8rem; color:#555;">
                                    Totals ({{ $subscriptions->count() }} year{{ $subscriptions->count() != 1 ? 's' : '' }})
                                </td>
                                <td class="font-weight-bold" style="font-size:.83rem;">
                                    USD {{ number_format($totalDue, 2) }}
                                </td>
                                <td class="font-weight-bold" style="font-size:.83rem; color:#155724;">
                                    USD {{ number_format($totalPaid, 2) }}
                                </td>
                                <td colspan="4">
                                    @if($balance > 0)
                                        <span style="font-size:.78rem; color:#a02626; font-weight:600;">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            USD {{ number_format($balance, 2) }} outstanding
                                        </span>
                                    @else
                                        <span style="font-size:.78rem; color:#155724; font-weight:600;">
                                            <i class="fas fa-check-circle mr-1"></i> Fully paid
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x mb-3" style="color:#e0c5c5;"></i>
                        <p class="text-muted mb-3">No subscription records yet for this fellow.</p>
                        <button type="button" class="btn btn-sm btn-danger"
                                style="background:#a02626; border-color:#a02626;"
                                data-toggle="modal" data-target="#addSubModal">
                            <i class="fas fa-plus mr-1"></i> Add First Subscription Year
                        </button>
                    </div>
                    @endif
                </div>
            </div>

        </div>{{-- /.container-fluid --}}
    </section>
</div>{{-- /.content-wrapper --}}


{{-- ══════════════════════════════════════════════
     ADD SUBSCRIPTION MODAL
══════════════════════════════════════════════ --}}
<div class="modal fade" id="addSubModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ url('admin/associates/fellows/subscriptions/' . $fellow->fellow_id) }}">
                @csrf
                <div class="modal-header py-2" style="background:#a02626;">
                    <h5 class="modal-title text-white" style="font-size:.95rem;">
                        <i class="fas fa-plus-circle mr-2"></i>Add Subscription Year
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="color:#fff;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="form-label">Year <span class="req">*</span></label>
                            <input type="text" name="year" class="form-control form-control-sm"
                                   placeholder="e.g. 2024" maxlength="4" required
                                   value="{{ date('Y') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="form-label">Status <span class="req">*</span></label>
                            <select name="status" class="form-control form-control-sm" required>
                                <option value="Unpaid">Unpaid</option>
                                <option value="Paid">Paid</option>
                                <option value="Partial">Partial</option>
                                <option value="Waived">Waived</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="form-label">Amount Due (USD) <span class="req">*</span></label>
                            <input type="number" step="0.01" min="0" name="amount_due"
                                   class="form-control form-control-sm"
                                   placeholder="150.00" value="150.00" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="form-label">Amount Paid (USD)</label>
                            <input type="number" step="0.01" min="0" name="amount_paid"
                                   class="form-control form-control-sm"
                                   placeholder="0.00" value="0.00">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="form-label">Date Paid</label>
                            <input type="date" name="date_paid" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label">Mode of Payment</label>
                            <select name="mode_of_payment" class="form-control form-control-sm">
                                <option value="">— Select —</option>
                                @foreach(['Bank Transfer','Cheque','Cash','Online','Waived'] as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger"
                            style="background:#a02626; border-color:#a02626;">
                        <i class="fas fa-save mr-1"></i> Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════
     EDIT SUBSCRIPTION MODAL
══════════════════════════════════════════════ --}}
<div class="modal fade" id="editSubModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="editSubForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-header py-2" style="background:#856404;">
                    <h5 class="modal-title text-white" style="font-size:.95rem;">
                        <i class="fas fa-edit mr-2"></i>Edit Subscription — <span id="editSubYearTitle"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="form-label">Year <span class="req">*</span></label>
                            <input type="text" name="year" id="edit_year"
                                   class="form-control form-control-sm" maxlength="4" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="form-label">Status <span class="req">*</span></label>
                            <select name="status" id="edit_status" class="form-control form-control-sm" required>
                                <option value="Unpaid">Unpaid</option>
                                <option value="Paid">Paid</option>
                                <option value="Partial">Partial</option>
                                <option value="Waived">Waived</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="form-label">Amount Due (USD)</label>
                            <input type="number" step="0.01" min="0" name="amount_due"
                                   id="edit_amount_due" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="form-label">Amount Paid (USD)</label>
                            <input type="number" step="0.01" min="0" name="amount_paid"
                                   id="edit_amount_paid" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="form-label">Date Paid</label>
                            <input type="date" name="date_paid" id="edit_date_paid"
                                   class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label">Mode of Payment</label>
                            <select name="mode_of_payment" id="edit_mode" class="form-control form-control-sm">
                                <option value="">— Select —</option>
                                @foreach(['Bank Transfer','Cheque','Cash','Online','Waived'] as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm"
                            style="background:#856404; border-color:#856404; color:#fff;">
                        <i class="fas fa-save mr-1"></i> Update Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openEditModal(sub) {
    // Set form action URL
    document.getElementById('editSubForm').action =
        '{{ url("admin/associates/fellows/subscriptions/update") }}/' + sub.id;

    // Populate fields
    document.getElementById('editSubYearTitle').textContent = sub.year;
    document.getElementById('edit_year').value        = sub.year;
    document.getElementById('edit_amount_due').value  = sub.amount_due;
    document.getElementById('edit_amount_paid').value = sub.amount_paid;
    document.getElementById('edit_date_paid').value   = sub.date_paid  || '';

    // Status select
    var statusSel = document.getElementById('edit_status');
    for (var i = 0; i < statusSel.options.length; i++) {
        statusSel.options[i].selected = (statusSel.options[i].value === sub.status);
    }

    // Mode of payment select
    var modeSel = document.getElementById('edit_mode');
    for (var i = 0; i < modeSel.options.length; i++) {
        modeSel.options[i].selected = (modeSel.options[i].value === (sub.mode_of_payment || ''));
    }

    $('#editSubModal').modal('show');
}
</script>
@endpush

@endsection

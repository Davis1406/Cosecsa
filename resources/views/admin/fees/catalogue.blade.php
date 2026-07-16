@extends('layout.app')

@section('title', 'Fee Catalogues')

@push('styles')
<style>
    .fee-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px;
                padding:20px 24px; color:#fff; margin-bottom:1.2rem; }
    .fee-card { border-top:3px solid #a02626; }
    .fee-type-row { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #f5f5f5; font-size:.86rem; }
    body.dark-mode .fee-type-row { border-color:#4a5568; }
    .fee-amount { font-weight:700; color:#a02626; }
    .fee-group-title { font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:#888; margin:12px 0 4px; font-weight:700; }
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
                        <h4 class="mb-0"><i class="fas fa-list-ul mr-2"></i>Fee Catalogues</h4>
                        <div style="font-size:.85rem;opacity:.85;">
                            Reference rates for fellowship registration, annual subscriptions, graduation, gown,
                            transcript, and programme entry / exam fees. To record a payment or view the payments
                            log, go to <a href="{{ url('admin/fees') }}" style="color:#fff;text-decoration:underline;">Manage Fees</a>.
                        </div>
                    </div>
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
                                    record a payment against them via <a href="{{ url('admin/fees') }}">Manage Fees</a>.
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

@endsection

@push('scripts')
<script>
function toggleAddFeeForm() {
    var form = document.getElementById('addFeeTypeForm');
    form.style.display = form.style.display === 'none' ? '' : 'none';
}

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
</script>
@endpush

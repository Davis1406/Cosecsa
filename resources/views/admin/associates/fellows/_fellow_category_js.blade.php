{{--
    Category-driven behaviour for the fellow add/edit form:
      - Only "Fellow by Examination" (category 5) has exam fields; every other
        fellowship type hides them (.exam-only).
      - The Registration Fee / Annual Subscription Fee inputs are prefilled
        from the fee catalogue for the chosen type. $fellowshipFees is built in
        FellowsController::fellowshipFeesByCategory(); each entry has a
        'registration' and 'subscription' object ({name, amount, currency}).
    On load only empty inputs are filled, so a saved amount is never overwritten;
    changing the Fellowship Type re-fills them.
--}}
@php $feeMap = $fellowshipFees ?? []; @endphp
@push('scripts')
<script>
$(function () {
    var FEE_MAP = @json($feeMap);
    var EXAM_CATEGORY = '5';

    function categoryValue() {
        var sel = document.querySelector('select[name="category_id"]');
        return sel ? String(sel.value) : '';
    }

    function applyExamFields(val) {
        var isExam = val === EXAM_CATEGORY;
        document.querySelectorAll('.exam-only').forEach(function (el) {
            el.style.display = isExam ? '' : 'none';
        });
    }

    function applyFees(val, force) {
        var entry = FEE_MAP[val] || null;
        var reg = document.getElementById('fellowRegistrationFee');
        var sub = document.getElementById('fellowSubscriptionFee');

        if (reg && force) {
            reg.value = (entry && entry.registration) ? Number(entry.registration.amount).toFixed(2) : '';
        } else if (reg && !reg.value && entry && entry.registration) {
            reg.value = Number(entry.registration.amount).toFixed(2);
        }

        if (sub && force) {
            sub.value = (entry && entry.subscription) ? Number(entry.subscription.amount).toFixed(2) : '';
        } else if (sub && !sub.value && entry && entry.subscription) {
            sub.value = Number(entry.subscription.amount).toFixed(2);
        }
    }

    function refresh(force) {
        var val = categoryValue();
        applyExamFields(val);
        applyFees(val, force);
    }

    var sel = document.querySelector('select[name="category_id"]');
    if (sel) {
        sel.addEventListener('change', function () { refresh(true); });
    }

    refresh(false);
});
</script>
@endpush

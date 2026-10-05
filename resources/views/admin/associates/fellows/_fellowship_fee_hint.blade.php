{{--
    Fee hint shown in section 4 (Fees & Finance) on the fellow add/edit pages,
    plus the category-driven field logic:
      - Only "Fellow by Examination" (category 5) has exam-related fields; every
        other fellowship type hides them (.exam-only).
      - The hint lists the catalogue's "Fellowship Registration" and
        "Annual Subscription" fee that best match the chosen fellowship type.
    The fee map ($fellowshipFees) is built in
    FellowsController::fellowshipFeesByCategory().
--}}
@php $feeMap = $fellowshipFees ?? []; @endphp
<div id="fellowFeeHint"
     class="alert py-2 px-3 mb-3 small"
     data-fees='@json($feeMap)'
     style="display:none; background:#fff8f8; border:1px solid #f0d4d4; color:#59413f;">
    <i class="fas fa-file-invoice-dollar mr-1" style="color:#a02626;"></i>
    <span id="fellowFeeHintText"></span>
</div>

@push('scripts')
<script>
$(function () {
    // Only "Fellow by Examination" keeps the exam fields (Intake/MCS/Exam years,
    // exam fees, etc.); every other fellowship type hides them.
    var EXAM_CATEGORY = '5';

    function applyExamFields() {
        var sel = document.querySelector('select[name="category_id"]');
        var isExam = sel && String(sel.value) === EXAM_CATEGORY;

        document.querySelectorAll('.exam-only').forEach(function (el) {
            el.style.display = isExam ? '' : 'none';
        });
    }

    function renderFeeHint() {
        var box = document.getElementById('fellowFeeHint');
        if (!box) return;

        var text = document.getElementById('fellowFeeHintText');
        var map  = {};
        try { map = JSON.parse(box.getAttribute('data-fees') || '{}') || {}; } catch (e) { map = {}; }

        function money(fee) {
            return fee.currency + ' ' + Number(fee.amount).toFixed(2);
        }

        var sel = document.querySelector('select[name="category_id"]');
        var val = sel ? String(sel.value) : '';
        var entry = map[val] || null;
        var parts = [];

        if (entry) {
            if (entry.registration) parts.push('Registration — ' + entry.registration.name + ': ' + money(entry.registration));
            if (entry.subscription) parts.push('Annual Subscription — ' + entry.subscription.name + ': ' + money(entry.subscription));
        }

        if (!val || !parts.length) {
            text.textContent = '';
            box.style.display = 'none';
            return;
        }

        text.textContent = 'Applicable catalogue fees — ' + parts.join('  ·  ');
        box.style.display = '';
    }

    var sel = document.querySelector('select[name="category_id"]');
    if (sel) {
        sel.addEventListener('change', function () {
            applyExamFields();
            renderFeeHint();
        });
    }

    applyExamFields();
    renderFeeHint();
});
</script>
@endpush

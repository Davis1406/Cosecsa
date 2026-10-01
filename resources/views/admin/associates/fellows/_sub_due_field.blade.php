{{-- Amount Due for the subscription modals: a dropdown of the fee catalogue's
     Annual Subscription fees plus "Other amount…", which reveals a number box.
     The number box (.js-due, name="amount_due") is what gets submitted; picking
     a fee just copies its amount in. See syncDueField() in subscriptions.blade.php. --}}
<label class="form-label">Amount Due (USD) <span class="req">*</span></label>
<select class="form-control form-control-sm js-due-select" @isset($selectId) id="{{ $selectId }}" @endisset>
    @foreach($subscriptionFees as $fee)
        @php $isSuggested = $suggestedFee && $suggestedFee->name === $fee->name; @endphp
        <option value="{{ number_format($fee->amount, 2, '.', '') }}" {{ $preselect && $isSuggested ? 'selected' : '' }}>
            {{ $fee->name }} — USD {{ number_format($fee->amount, 2) }}{{ $isSuggested ? ' (' . $suggestedFee->type . ')' : '' }}
        </option>
    @endforeach
    <option value="other" {{ $preselect && ! $suggestedFee ? 'selected' : '' }}>Other amount…</option>
</select>
<input type="number" step="0.01" min="0" name="amount_due" placeholder="Enter amount"
       class="form-control form-control-sm mt-1 js-due" @isset($inputId) id="{{ $inputId }}" @endisset>

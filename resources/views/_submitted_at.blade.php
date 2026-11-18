{{-- When marks were submitted: "18 Nov 2026, 09:42", and when they were last edited if that was later. $created, $updated --}}
@php
    $submittedAt = ! empty($created) ? \Carbon\Carbon::parse($created) : null;
    $editedAt = ! empty($updated) ? \Carbon\Carbon::parse($updated) : null;
@endphp
@if ($submittedAt){{ $submittedAt->format('d M Y, H:i') }}@if ($editedAt && $editedAt->diffInSeconds($submittedAt) > 120)<small class="text-muted d-block">edited {{ $editedAt->format('d M Y, H:i') }}</small>@endif @else — @endif
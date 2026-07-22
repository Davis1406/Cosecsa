{{-- Shared signature block — every outgoing email @includes this so the
     structure (logo, org name, address, contact line) looks identical
     regardless of which staff member sent it or whether they've filled in
     their personal title/phone in Profile Settings. Expects (all optional):
     $senderName, $senderTitle, $senderPhone, $senderEmail. --}}
<div style="margin-top:28px; font-size:13px; color:#a02626; font-family:Arial,Helvetica,sans-serif;">
  <p style="margin:0;">Kind Regards,@if(!empty($senderName))<br>{{ $senderName }}@endif</p>
  @if(!empty($senderTitle))
    <p style="font-weight:bold; margin:4px 0 10px;">{{ $senderTitle }}</p>
  @endif
  <img src="{{ $message->embed(\Illuminate\Mail\Mailables\Attachment::fromPath(public_path('dist/img/Cosecsa_Logo_email.png'))->as('cosecsa-logo.png')->withMime('image/png')) }}" alt="COSECSA" width="60" height="60" style="display:block; object-fit:contain; margin:6px 0;">
  <p style="margin:10px 0 0; font-weight:bold;">COSECSA – College of Surgeons of East, Central and Southern Africa</p>
  <p style="margin:2px 0;">ECSA-HC, P.O.Box 1009<br>Arusha, Tanzania</p>
  @if(!empty($senderPhone))
    <p style="margin:2px 0;">Tel: {{ $senderPhone }}</p>
  @endif
  <p style="margin:6px 0 0;">
    <span style="color:#c99400;">E:</span>
    <a href="mailto:{{ $senderEmail ?? config('mail.from.address') }}" style="color:#2a6ebb; text-decoration:none;">{{ $senderEmail ?? config('mail.from.address') }}</a>
    &nbsp;<span style="color:#c99400;">W:</span>
    <a href="https://www.cosecsa.org" style="color:#2a6ebb; text-decoration:none;">www.cosecsa.org</a>
  </p>
</div>

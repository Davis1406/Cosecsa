<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $emailSubject }}</title>
<style>
  body { margin:0; padding:0; background:#f4f4f4; font-family: Arial, Helvetica, sans-serif; }
  .wrapper { width:100%; background:#f4f4f4; padding:24px 0; }
  .container { max-width:620px; margin:0 auto; background:#ffffff; border-radius:6px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }

  /* ── Header ── */
  .header { background:#a02626; padding:0; }
  .header-inner { display:flex; align-items:center; padding:18px 28px; gap:16px; }
  .header img { width:60px; height:60px; object-fit:contain; }
  .header-text { color:#ffffff; }
  .header-text h1 { margin:0; font-size:18px; font-weight:700; letter-spacing:.3px; }
  .header-text p  { margin:2px 0 0; font-size:12px; color:#f5c6c6; }
  .header-bar { height:4px; background:#FEC503; }

  /* ── Body ── */
  .body { padding:32px 32px 24px; color:#2d2d2d; font-size:15px; line-height:1.7; }
  .body p { margin:0 0 14px; }

  /* ── Footer ── */
  .footer { background:#f9f9f9; border-top:1px solid #e8e8e8; padding:20px 32px; }
  .footer-logo { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
  .footer-logo img { width:36px; height:36px; object-fit:contain; }
  .footer-logo span { font-size:13px; font-weight:700; color:#a02626; }
  .footer p { margin:0; font-size:11.5px; color:#888; line-height:1.6; }
  .footer a { color:#a02626; text-decoration:none; }
  .footer-divider { border:none; border-top:1px solid #e0e0e0; margin:12px 0; }
  .footer-disclaimer { font-size:10.5px; color:#aaa; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="container">

    {{-- ── Header ── --}}
    <div class="header">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td style="padding:18px 28px; vertical-align:middle; width:76px;">
            <img src="{{ $message->embed(public_path('dist/img/Cosecsa_Logo.png')) }}"
                 alt="COSECSA" width="60" height="60"
                 style="display:block; border:0;">
          </td>
          <td style="padding:18px 12px 18px 0; vertical-align:middle;">
            <div style="color:#ffffff; font-family:Arial,Helvetica,sans-serif;">
              <div style="font-size:18px; font-weight:700; letter-spacing:.3px;">COSECSA</div>
              <div style="font-size:11.5px; color:#f5c6c6; margin-top:2px;">
                College of Surgeons of East, Central and Southern Africa
              </div>
            </div>
          </td>
        </tr>
      </table>
      <div style="height:4px; background:#FEC503;"></div>
    </div>

    {{-- ── Body ── --}}
    <div class="body">
      {!! $emailBody !!}

      @if(!empty($senderName))
      <div style="margin-top:28px; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#a02626;">
        <p style="margin:0;">Best Regards,<br>{{ $senderName }}.</p>
        @if(!empty($senderTitle))
          <p style="font-weight:bold; margin:4px 0 10px;">{{ $senderTitle }}</p>
        @endif
        <img src="{{ $message->embed(public_path('dist/img/Cosecsa_Logo.png')) }}" alt="COSECSA" width="60" height="60" style="display:block; object-fit:contain;">
        <p style="margin:10px 0 0; font-weight:bold;">The College of Surgeons of East, Central and Southern Africa (COSECSA)</p>
        <p style="margin:2px 0;">ECSA-HC, P.O. Box 1009<br>Arusha, Tanzania.</p>
        @if(!empty($senderPhone))
          <p style="margin:2px 0;">Tel: {{ $senderPhone }}</p>
        @endif
        <p style="margin:6px 0 0;">
          <span style="color:#c99400;">Email:</span>
          <a href="mailto:{{ $senderEmail }}" style="color:#2a6ebb; text-decoration:none;">{{ $senderEmail }}</a>
          &nbsp;<span style="color:#c99400;">W:</span>
          <a href="https://www.cosecsa.org" style="color:#2a6ebb; text-decoration:none;">www.cosecsa.org</a>
        </p>
      </div>
      @endif
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td style="width:46px; vertical-align:middle; padding-right:10px;">
            <img src="{{ $message->embed(public_path('dist/img/Cosecsa_Logo.png')) }}"
                 alt="COSECSA" width="36" height="36"
                 style="display:block; border:0;">
          </td>
          <td style="vertical-align:middle;">
            <span style="font-size:13px; font-weight:700; color:#a02626; font-family:Arial,Helvetica,sans-serif;">
              COSECSA — Examinations Office
            </span>
          </td>
        </tr>
      </table>

      <hr style="border:none; border-top:1px solid #e0e0e0; margin:12px 0;">

      <p style="margin:0; font-size:11.5px; color:#888; line-height:1.6; font-family:Arial,Helvetica,sans-serif;">
        College of Surgeons of East, Central and Southern Africa<br>
        Plot 1009, 157 Olorien, Njiro. Arusha, Tanzania<br>
        Email: <a href="mailto:{{ config('mail.from.address') }}" style="color:#a02626; text-decoration:none;">{{ config('mail.from.address') }}</a>
        &nbsp;|&nbsp;
        Web: <a href="https://www.cosecsa.org" style="color:#a02626; text-decoration:none;">www.cosecsa.org</a>
      </p>

      <p style="margin:10px 0 0; font-size:10.5px; color:#aaa; font-family:Arial,Helvetica,sans-serif;">
        This email was sent from the COSECSA Management Information System.
        If you believe you received this in error, please contact
        <a href="mailto:{{ config('mail.from.address') }}" style="color:#aaa;">{{ config('mail.from.address') }}</a>.
      </p>
    </div>

  </div>
</div>
@if(!empty($trackingToken))
<img src="{{ url('track/open/' . $trackingToken) }}"
     width="1" height="1" border="0"
     style="display:block;width:1px;height:1px;border:0;margin:0;padding:0;"
     alt="">
@endif
</body>
</html>

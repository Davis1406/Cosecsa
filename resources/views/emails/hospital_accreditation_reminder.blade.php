<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>COSECSA Accreditation Reminder</title>
</head>
<body style="margin:0; padding:0; background:#f4f4f4; font-family: Arial, Helvetica, sans-serif;">
<div style="width:100%; background:#f4f4f4; padding:24px 0;">
  <div style="max-width:620px; margin:0 auto; background:#ffffff; border-radius:6px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08);">

    <div style="background:#a02626;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td style="padding:18px 28px; vertical-align:middle; width:76px;">
            <img src="{{ $message->embed(\Illuminate\Mail\Mailables\Attachment::fromPath(public_path('dist/img/Cosecsa_Logo_email.png'))->as('cosecsa-logo.png')->withMime('image/png')) }}" alt="COSECSA" width="60" height="60" style="display:block; border:0;">
          </td>
          <td style="padding:18px 12px 18px 0; vertical-align:middle;">
            <div style="color:#ffffff;">
              <div style="font-size:18px; font-weight:700; letter-spacing:.3px;">COSECSA</div>
              <div style="font-size:11.5px; color:#f5c6c6; margin-top:2px;">College of Surgeons of East, Central and Southern Africa</div>
            </div>
          </td>
        </tr>
      </table>
      <div style="height:4px; background:#FEC503;"></div>
    </div>

    <div style="padding:32px 32px 24px; color:#2d2d2d; font-size:15px; line-height:1.7;">
      <p style="margin:0 0 14px;">Dear Colleague,</p>

      @if($isExpired)
        <p style="margin:0 0 14px;">
          This is a reminder that the COSECSA accreditation for <strong>{{ $programmeName }}</strong> at
          <strong>{{ $hospitalName }}</strong> <span style="color:#a02626;font-weight:bold;">expired on {{ $expiryDateFormatted }}</span>.
          Please contact the Secretariat to begin the re-accreditation process as soon as possible.
        </p>
      @else
        <p style="margin:0 0 14px;">
          This is a reminder that the COSECSA accreditation for <strong>{{ $programmeName }}</strong> at
          <strong>{{ $hospitalName }}</strong> is due to expire on <strong>{{ $expiryDateFormatted }}</strong>.
          Please contact the Secretariat in good time to renew this accreditation and avoid a lapse.
        </p>
      @endif

      @include('emails._signature')
    </div>

    <div style="background:#f9f9f9; border-top:1px solid #e8e8e8; padding:20px 32px;">
      <p style="margin:0; font-size:10.5px; color:#aaa;">
        This email was sent from the COSECSA Management Information System.
      </p>
    </div>

  </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Progress Report — Edit Access Requested</title>
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
      <p style="margin:0 0 14px;">Dear Administrative Officer,</p>
      <p style="margin:0 0 14px;">
        <strong>{{ $requesterName }}</strong> is requesting edit access to the locked
        "<strong>{{ $sectionLabel }}</strong>" section of the <strong>{{ $periodMonthFormatted }}</strong>
        Progressive Report.
      </p>
      <p style="margin:0 0 22px;">
        Please review and approve or decline this request from the Progressive Reports page.
      </p>
      <p style="margin:0 0 22px;">
        <a href="{{ $reviewUrl }}" style="display:inline-block; background:#a02626; color:#ffffff; text-decoration:none; padding:10px 22px; border-radius:4px; font-weight:bold; font-size:14px;">
          Review Request
        </a>
      </p>

      <div style="margin-top:28px; font-size:13px; color:#a02626;">
        <p style="margin:0;">Kind Regards,</p>
        <img src="{{ $message->embed(\Illuminate\Mail\Mailables\Attachment::fromPath(public_path('dist/img/Cosecsa_Logo_email.png'))->as('cosecsa-logo.png')->withMime('image/png')) }}" alt="COSECSA" width="60" height="60" style="display:block; object-fit:contain; margin:6px 0;">
        <p style="margin:10px 0 0; font-weight:bold;">COSECSA – College of Surgeons of East, Central and Southern Africa</p>
        <p style="margin:2px 0;">ECSA-HC, P.O.Box 1009<br>Arusha, Tanzania</p>
        <p style="margin:6px 0 0;">
          <span style="color:#c99400;">E:</span>
          <a href="mailto:info@cosecsa.org" style="color:#2a6ebb; text-decoration:none;">info@cosecsa.org</a>
          &nbsp;<span style="color:#c99400;">W:</span>
          <a href="https://www.cosecsa.org" style="color:#2a6ebb; text-decoration:none;">www.cosecsa.org</a>
        </p>
      </div>
    </div>

    <div style="background:#f9f9f9; border-top:1px solid #e8e8e8; padding:20px 32px;">
      <p style="margin:0; font-size:10.5px; color:#aaa;">
        This email was sent automatically from the COSECSA Management Information System.
      </p>
    </div>

  </div>
</div>
</body>
</html>

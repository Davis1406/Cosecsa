<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset your COSECSA MIS password</title>
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
      <p style="margin:0 0 14px;">Hello{{ count($accounts) === 1 && !empty($accounts[0]['name']) ? ' '.$accounts[0]['name'] : '' }},</p>
      <p style="margin:0 0 18px;">We received a request to reset the password of your COSECSA MIS account. Use the button below to choose a new password. The link can be used once and expires in {{ $minutes }} minutes.</p>

      @foreach($accounts as $a)
        <div style="margin:0 0 20px; padding:16px 18px; border:1px solid #e3e3e3; border-radius:6px; background:#fafafa;">
          <div style="font-size:13px; color:#555;">
            @if(count($accounts) > 1)Account: @endif<strong>{{ $a['label'] }}</strong>@if(!empty($a['roles'])) &nbsp;·&nbsp; {{ $a['roles'] }}@endif
          </div>
          <div style="margin-top:12px;">
            <a href="{{ $a['url'] }}" style="display:inline-block; background:#a02626; color:#ffffff; text-decoration:none; font-weight:bold; padding:11px 22px; border-radius:4px; font-size:15px;">Reset password</a>
          </div>
          <div style="margin-top:12px; font-size:12px; color:#777; word-break:break-all;">If the button does not work, copy this address into your browser:<br>{{ $a['url'] }}</div>
        </div>
      @endforeach

      @if(count($accounts) > 1)
        <p style="margin:0 0 14px; font-size:13.5px; color:#555;">This email address belongs to more than one account, so there is a link for each. Reset the one you log in with.</p>
      @endif

      <p style="margin:0 0 14px; font-size:13.5px; color:#555;">If you did not ask for this, you can ignore this email: your password stays as it is.</p>

      @include('emails._signature', ['senderEmail' => $senderEmail])
    </div>
  </div>
</div>
</body>
</html>

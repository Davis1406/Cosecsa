<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 165px 50px 90px 50px; }
    body { font-family: Arial, sans-serif; font-size: 10.5pt; color: #222; line-height: 1.5; }

    .watermark { position: fixed; top: 260px; left: 150px; width: 300px; opacity: 0.08; z-index: -10; }

    .letterhead { position: fixed; top: -155px; left: -30px; right: -30px; }
    .letterhead table { width: 100%; border-collapse: collapse; }
    .letterhead .logo-cell { width: 95px; }
    .letterhead .logo-cell img { width: 85px; }
    .letterhead .title-cell { text-align: right; }
    .letterhead .name { font-weight: bold; font-size: 12pt; color: #a02626; }
    .letterhead .address { font-size: 9pt; color: #444; width: 340px; margin-left: auto; line-height: 1.35; white-space: nowrap; }
    .letterhead .rule { border-bottom: 2px solid #a02626; margin-top: 4px; }

    .page-footer { position: fixed; bottom: -75px; left: -30px; right: -30px; font-size: 8pt; color: #222; border-top: 1px solid #ccc; padding-top: 4px; }
    .page-footer table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .page-footer td { padding: 1px 6px; vertical-align: top; width: 50%; }
    .page-footer td:last-child { text-align: right; }
    .page-footer b { color: #000; }

    .letter-date { margin-bottom: 18px; }
    .to-block { margin-bottom: 16px; }
    .to-block .recipient-name { font-weight: bold; }
    .body-text { text-align: justify; }
    .closing { margin-top: 20px; }
    .sign-block { margin-top: 8px; }
    .sign-block img.signature { height: 45px; }
    .signatory p { margin: 2px 0; }
    .signatory .name { font-weight: bold; }
</style>
</head>
<body>
    @if(!empty($letterhead->watermark_path))
        <img class="watermark" src="{{ storage_path('app/public/'.$letterhead->watermark_path) }}">
    @endif

    <div class="letterhead">
        @if(!empty($letterhead->logo_path) || !empty($letterhead->institution_name))
        <table>
            <tr>
                @if(!empty($letterhead->logo_path))
                <td class="logo-cell"><img src="{{ storage_path('app/public/'.$letterhead->logo_path) }}"></td>
                @endif
                <td class="title-cell">
                    <div class="name">{{ $letterhead->institution_name }}</div>
                    @if(!empty($letterhead->address_text))
                        <div class="address">{!! nl2br(e($letterhead->address_text)) !!}</div>
                    @endif
                </td>
            </tr>
        </table>
        <div class="rule"></div>
        @endif
    </div>

    @if(!empty($letterhead->footer_text))
    <div class="page-footer">
        <table>
            @foreach(explode("\n", trim($letterhead->footer_text)) as $footerRow)
                @php $cols = explode('||', $footerRow); @endphp
                <tr>
                    @foreach($cols as $col)
                        @php [$label, $rest] = array_pad(explode(':', $col, 2), 2, ''); @endphp
                        <td><b>{{ trim($label) }}{{ $rest !== '' ? ':' : '' }}</b>{{ $rest }}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </div>
    @endif

    <div class="letter-date">{{ $letterDate->format('jS F, Y') }}</div>

    <div class="to-block">
        To<br>
        <span class="recipient-name">{{ $recipient->name }}</span><br>
        @if(!empty($recipient->email))Email: {{ $recipient->email }}<br>@endif
        @if(!empty($recipient->country))Country: {{ $recipient->country }}@endif
    </div>

    <div class="body-text">{!! nl2br(e($bodyHtml)) !!}</div>

    <div class="closing">
        <p>Kind Regards,</p>
        <div class="sign-block">
            @if(!empty($sender->signature_image_path))
                <img class="signature" src="{{ storage_path('app/public/'.$sender->signature_image_path) }}">
            @endif
        </div>
        <div class="signatory">
            <p class="name">{{ $sender->name }}</p>
            <p>{{ $sender->signature_title }}</p>
            <p>{{ $letterhead->institution_name }}</p>
        </div>
    </div>
</body>
</html>

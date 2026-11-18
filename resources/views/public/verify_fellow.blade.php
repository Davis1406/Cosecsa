<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="no-referrer">
<meta name="theme-color" content="#a02626">
<title>@if($state === 'ok') Verified COSECSA Fellow @elseif($state === 'invalid') Certificate not verified @else Verification unavailable @endif · COSECSA</title>
<link rel="icon" href="{{ asset('dist/img/Cosecsa_Logo_sm.png') }}">
<style>
  :root {
    --bg: #f4f1ec; --card: #ffffff; --ink: #1c2230; --muted: #6b7280; --line: #ece7df;
    --brand: #a02626; --ok: #16a34a; --ok-soft: #e8f7ee; --bad: #dc2626; --warn: #d97706;
    --shadow: 0 1px 2px rgba(28,34,48,.06), 0 12px 32px rgba(28,34,48,.10);
  }
  @media (prefers-color-scheme: dark) {
    :root { --bg: #0e1116; --card: #171b22; --ink: #eef1f6; --muted: #9aa3b2; --line: #262c37; --ok-soft: #12301f;
            --shadow: 0 1px 2px rgba(0,0,0,.4), 0 12px 32px rgba(0,0,0,.45); }
  }
  * { box-sizing: border-box; }
  html, body { margin: 0; min-height: 100%; }
  body {
    font: 16px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: var(--ink); background: var(--bg);
    background-image: radial-gradient(1200px 500px at 50% -10%, rgba(160,38,38,.10), transparent 60%);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: max(20px, env(safe-area-inset-top)) 16px max(20px, env(safe-area-inset-bottom));
    -webkit-font-smoothing: antialiased;
  }
  .card {
    width: 100%; max-width: 420px; background: var(--card); border-radius: 24px; box-shadow: var(--shadow);
    padding: 0; text-align: center; position: relative; overflow: hidden;
  }
  .inner { padding: 26px 24px 22px; }

  /* the small burgundy header: the college logo on the left */
  .top { display: flex; align-items: center; gap: 11px; padding: 10px 16px; background: var(--brand); color: #fff; text-align: left; }
  .card.bad .top { background: var(--bad); } .card.warn .top { background: var(--warn); }
  .logo { flex: 0 0 auto; width: 42px; height: 42px; border-radius: 50%; background: #fff; display: grid; place-items: center; box-shadow: 0 1px 4px rgba(0,0,0,.25); }
  .logo img { width: 34px; height: 34px; display: block; object-fit: contain; }
  .org b { display: block; font-size: 15px; font-weight: 750; letter-spacing: .16em; line-height: 1.1; }
  .org small { display: block; margin-top: 2px; font-size: 10.5px; line-height: 1.25; opacity: .88; letter-spacing: .02em; text-wrap: balance; }

  .emblem { position: relative; width: 116px; height: 116px; margin: 6px auto 18px; }
  /* the seal sits on a light plate so its blue stays readable on the dark theme too */
  .plate { width: 116px; height: 116px; border-radius: 50%; background: #fff; display: grid; place-items: center;
           box-shadow: 0 0 0 1px var(--line), 0 8px 22px rgba(28,34,48,.12); }
  .seal { width: 96px; height: 96px; display: block; object-fit: contain; }
  .card:not(.ok) .seal { filter: grayscale(1); opacity: .5; }
  .badge {
    position: absolute; right: -6px; bottom: -4px; width: 44px; height: 44px; border-radius: 50%;
    background: var(--ok); box-shadow: 0 0 0 4px var(--card), 0 6px 14px rgba(22,163,74,.35);
    display: grid; place-items: center; transform: scale(0); animation: pop .5s .15s cubic-bezier(.2,1.4,.4,1) forwards;
  }
  .badge.bad { background: var(--bad); box-shadow: 0 0 0 4px var(--card), 0 6px 14px rgba(220,38,38,.3); }
  .badge.warn { background: var(--warn); box-shadow: 0 0 0 4px var(--card), 0 6px 14px rgba(217,119,6,.3); }
  .badge svg { width: 24px; height: 24px; fill: none; stroke: #fff; stroke-width: 3.2; stroke-linecap: round; stroke-linejoin: round; }
  .badge .draw { stroke-dasharray: 30; stroke-dashoffset: 30; animation: draw .45s .5s ease-out forwards; }
  @keyframes pop { to { transform: scale(1); } }
  @keyframes draw { to { stroke-dashoffset: 0; } }
  @media (prefers-reduced-motion: reduce) { .badge, .badge .draw { animation: none; transform: none; stroke-dashoffset: 0; } }

  .status { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
            color: var(--ok); background: var(--ok-soft); padding: 5px 12px; border-radius: 999px; }
  .status.bad { color: var(--bad); background: rgba(220,38,38,.10); } .status.warn { color: var(--warn); background: rgba(217,119,6,.12); }
  h1 { font-size: 26px; line-height: 1.2; margin: 14px 0 6px; font-weight: 750; letter-spacing: -.01em; overflow-wrap: anywhere; }
  .sub { color: var(--muted); font-size: 14.5px; margin: 0 auto; max-width: 30ch; }

  dl { margin: 24px 0 0; text-align: left; border-top: 1px solid var(--line); }
  .row { display: flex; justify-content: space-between; gap: 16px; padding: 13px 2px; border-bottom: 1px solid var(--line); }
  dt { color: var(--muted); font-size: 14px; flex: 0 0 auto; }
  dd { margin: 0; font-weight: 600; text-align: right; overflow-wrap: anywhere; }

  .foot { margin-top: 20px; font-size: 12.5px; color: var(--muted); line-height: 1.6; }
  .foot b { color: var(--ink); font-weight: 650; }
  .brand { margin-top: 18px; font-size: 12px; letter-spacing: .08em; color: var(--muted); text-align: center; }
  .btn { display: inline-block; margin-top: 18px; padding: 10px 18px; border-radius: 12px; background: var(--brand); color: #fff;
         text-decoration: none; font-weight: 600; font-size: 14.5px; border: 0; cursor: pointer; }
  @media (max-width: 360px) { h1 { font-size: 23px; } .inner { padding: 22px 18px 20px; } .org small { font-size: 9.5px; } }
</style>
</head>
<body>
@php
  $f = $fellow;
  // what the page says and lists is decided by the API from "QR settings" (wording, which rows for which kind of person)
  $rows = $state === 'ok' ? collect($f->rows ?? [])->filter(fn ($r) => ($r->value ?? '') !== '')->values()->all() : [];
  $sentence = $state === 'ok' ? ($f->sentence ?? 'is a Fellow of the College of Surgeons of East, Central and Southern Africa.') : '';
  $tone = $state === 'ok' ? '' : ($state === 'invalid' ? 'bad' : 'warn');
@endphp

<main class="card {{ $state === 'ok' ? 'ok' : $tone }}" role="main">
  <header class="top">
    <span class="logo"><img src="{{ asset('dist/img/Cosecsa_Logo_sm.png') }}" alt="COSECSA logo" width="34" height="34"></span>
    <span class="org"><b>COSECSA</b><small>College of Surgeons of East, Central and Southern Africa</small></span>
  </header>
  <div class="inner">
  <div class="emblem">
    <span class="plate"><img class="seal" src="{{ asset('dist/img/cosecsa_seal.png') }}" alt="College of Surgeons of East, Central and Southern Africa seal" width="96" height="96"></span>
    <span class="badge {{ $tone }}" aria-hidden="true">
      @if($state === 'ok')
        <svg viewBox="0 0 24 24"><path class="draw" d="M5 12.5l4.5 4.5L19 7.5"/></svg>
      @elseif($state === 'invalid')
        <svg viewBox="0 0 24 24"><path d="M7 7l10 10M17 7L7 17"/></svg>
      @else
        <svg viewBox="0 0 24 24"><path d="M12 6v7M12 17.5v.5"/></svg>
      @endif
    </span>
  </div>

  @if($state === 'ok')
    <span class="status">Verified</span>
    <h1>{{ $f->name }}</h1>
    <p class="sub">{{ $sentence }}</p>
    @if($rows)
      <dl>
        @foreach($rows as $row)
          <div class="row"><dt>{{ $row->label }}</dt><dd>{{ $row->value }}</dd></div>
        @endforeach
      </dl>
    @endif
    <p class="foot">Checked against the COSECSA register on <b>{{ now()->format('j F Y') }}</b>.<br>Compare these details with the certificate you were given.</p>
  @elseif($state === 'invalid')
    <span class="status bad">Not verified</span>
    <h1>We couldn’t verify this certificate</h1>
    <p class="sub">This link does not match any record in the COSECSA register.</p>
    <p class="foot">If you believe this is a mistake, please contact the COSECSA Secretariat and quote the certificate.</p>
  @else
    <span class="status warn">Try again</span>
    <h1>Verification is unavailable</h1>
    <p class="sub">We couldn’t reach the COSECSA register just now. This does not mean the certificate is invalid.</p>
    <a class="btn" href="">Try again</a>
  @endif
  </div>
</main>

<div class="brand">COSECSA · cosecsa.org</div>
</body>
</html>

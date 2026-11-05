<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $course['title'] ?? 'Examiner Training' }} — COSECSA</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Raleway:wght@400;500;600;700;800&display=swap">
    <style>
{!! $baseCss !!}
{!! $css !!}
    </style>
    <style>
        /* ── Print document ─────────────────────────────────────────────── */
        @page { size: A4; margin: 12mm; }
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        html, body { margin: 0; padding: 0; background: #fff; }
        body { font-family: 'Raleway', Arial, sans-serif; color: #15241c; }

        /* Faded COSECSA logo, centred on every page (fixed elements repeat on each printed page).
           Drawn over the content at low opacity so cards and banners cannot hide it. */
        .print-wm {
            position: fixed; top: 50%; left: 50%;
            width: 130mm; height: 130mm; margin: -65mm 0 0 -65mm;
            opacity: .07; z-index: 2147483000; pointer-events: none;
        }
        .print-wm img { width: 100%; height: 100%; object-fit: contain; display: block; }

        /* Screen-only toolbar */
        .print-bar {
            position: sticky; top: 0; z-index: 2147483600; display: flex; flex-wrap: wrap; align-items: center; gap: 12px;
            padding: 10px 16px; background: #87201f; color: #fff; font-size: 14px;
        }
        .print-bar button, .print-bar a {
            font: 700 13px 'Raleway', Arial, sans-serif; border: 0; border-radius: 6px; padding: 8px 14px; cursor: pointer;
            background: #fff; color: #87201f; text-decoration: none;
        }
        .print-bar .hint { flex: 1; min-width: 260px; opacity: .92; }
        .print-note { margin: 12px 16px 0; padding: 10px 14px; border-radius: 8px; background: #fbf0d6; color: #5c4200; font-size: 13px; }

        /* Cover */
        .print-cover { min-height: 255mm; display: flex; flex-direction: column; justify-content: center; padding: 0 14mm; page-break-after: always; break-after: page; }
        .print-cover img.crest { width: 34mm; height: 34mm; object-fit: contain; margin-bottom: 10mm; }
        .print-cover .k { font: 700 13px 'Raleway', sans-serif; letter-spacing: .14em; text-transform: uppercase; color: #87201f; }
        .print-cover h1 { font: 800 34px/1.15 'Raleway', sans-serif; margin: 6px 0 14px; color: #15241c; }
        .print-cover .rule { width: 60mm; height: 4px; background: #e9b11c; margin: 0 0 12mm; border-radius: 2px; }
        .print-cover ol { padding-left: 0; list-style: none; margin: 0; counter-reset: m; }
        .print-cover li { counter-increment: m; padding: 7px 0; border-bottom: 1px solid #dcdcd0; font: 600 15px 'Raleway', sans-serif; }
        .print-cover li::before { content: counter(m, decimal-leading-zero); display: inline-block; width: 11mm; color: #87201f; font-weight: 800; }
        .print-cover .meta { margin-top: 12mm; font-size: 12px; color: #56655c; }

        /* One module after another, each from a new page */
        .print-module { page-break-before: always; break-before: page; }

        /* Make the interactive course readable as a document */
        .cs-course { min-height: 0 !important; height: auto !important; background: #fff !important; }
        .cs-course, .cs-course * { animation: none !important; transition: none !important; }
        /* topic accordions: every topic open, nothing to click */
        .cs-course .pn { display: block !important; }
        .cs-course .th .ch, .cs-course .th::after { display: none !important; }
        .cs-course .hero .prog, .cs-course .hero .bar, .cs-course .done { display: none !important; }
        .cs-course .th { break-after: avoid; page-break-after: avoid; }
        .cs-course .topic { break-inside: auto; margin-bottom: 14px; }
        .cs-course table, .cs-course figure, .cs-course .callout, .cs-course .tile, .cs-course .fc, .cs-course .hero, .cs-course img { break-inside: avoid; page-break-inside: avoid; }
        .cs-course img, .cs-course svg { max-width: 100%; }
        /* flashcards: front and back one under the other instead of a flip */
        .cs-course .fc button { transform: none !important; display: flex !important; flex-direction: column; gap: 6px; min-height: 0 !important; }
        .cs-course .fc .f, .cs-course .fc .b { position: static !important; inset: auto !important; transform: none !important; backface-visibility: visible !important; }
        .cs-course video, .cs-course iframe { max-width: 100%; }
        /* the animated Zoom-station walkthrough cannot be shown on paper: leave a one-line pointer instead */
        .cs-course .demo > * { display: none !important; }
        .cs-course .demo::before { content: "Animation: the Zoom station walkthrough is in the online course."; display: block; padding: 6px 2px; font-style: italic; color: #56655c; }
        /* wide tables: no sideways scrolling on paper, so they shrink to the page width */
        .cs-course .scroll { overflow: visible !important; max-width: 100% !important; }
        .cs-course .scroll table { width: 100% !important; min-width: 0 !important; table-layout: auto; font-size: 10px; }
        .cs-course .scroll th, .cs-course .scroll td { padding: 3px 3px !important; white-space: normal !important; font-size: 8.5px !important; letter-spacing: 0 !important; line-height: 1.25 !important; min-width: 0 !important; width: auto !important; }
        .cs-course .note:has(+ .scroll), .cs-course .scroll + .note { display: none; }

        @media print {
            .print-bar, .print-note { display: none !important; }
            .cs-course nav, .cs-course .rail { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="print-wm" aria-hidden="true"><img src="{{ asset('dist/img/Cosecsa_Logo.png') }}" alt=""></div>

    <div class="print-bar">
        <span class="hint">
            <strong>Course PDF.</strong> In the print window choose <strong>Save as PDF</strong> as the destination
            (A4; margins Default; Background graphics is switched on automatically).
        </span>
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
        <a href="{{ url('admin/exams/learning/course') }}">Close</a>
    </div>
    @if(count($skipped))
        <div class="print-note">Not included: {{ implode('; ', $skipped) }}. Quizzes, and pages without a coded design, are left out.</div>
    @endif

    <section class="print-cover">
        <img class="crest" src="{{ asset('dist/img/Cosecsa_Logo.png') }}" alt="COSECSA">
        <div class="k">COSECSA · Examiner course</div>
        <h1>{{ $course['title'] ?? 'Examiner Training' }}</h1>
        <div class="rule"></div>
        <ol>
            @foreach($sections as $s)
                <li>{{ $s['title'] }}</li>
            @endforeach
        </ol>
        <div class="meta">Printed {{ now()->format('j F Y') }}</div>
    </section>

    @foreach($sections as $s)
        <section class="print-module">{!! $s['html'] !!}</section>
    @endforeach

    <script>
        // ?auto=1 (the "Download course PDF" button) opens the print window as soon as fonts and images are in.
        if (/[?&]auto=1\b/.test(location.search)) {
            window.addEventListener('load', function () {
                var ready = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
                ready.then(function () { setTimeout(function () { window.print(); }, 400); });
            });
        }
    </script>
</body>
</html>

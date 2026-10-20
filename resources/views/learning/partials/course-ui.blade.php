{{-- Examiner Training, October 2026 design: hero banner, typography scale, tick lists and the sectioned quiz.
     Included after learning.partials.styles by the player and the quiz page. Dark mode is body.dark-mode. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&family=Literata:opsz,wght@7..72,700&family=Public+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<style>
    .lms {
        --display: 'Literata', Georgia, 'Times New Roman', serif;
        --text: 'Public Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        --mono: 'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
        --ink: #1e293b;
        --ink-body: color-mix(in srgb, var(--ink) 88%, transparent);
    }
    body.dark-mode .lms { --ink: #f1f5f9; }

    /* ── Hero banner ─────────────────────────────────────────────────────── */
    .hero {
        --hero1: #5e0a14; --hero2: #99060a;
        background: linear-gradient(135deg, var(--hero1), var(--hero2));
        color: #fff; border-radius: 16px; padding: 26px 28px 28px; margin-bottom: 22px;
    }
    .hero-eyebrow { font: 500 11.5px/1.3 var(--mono); letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.88); margin-bottom: 10px; }
    .hero-title { font: 700 30px/1.2 var(--display); color: #fff; margin: 0; letter-spacing: -.01em; }
    .hero-text { font: 400 16.5px/1.55 var(--text); color: rgba(255,255,255,.92); margin: 10px 0 0; max-width: 60ch; }
    @media (max-width: 600px) {
        .hero { padding: 20px 18px 22px; border-radius: 14px; margin-bottom: 16px; }
        .hero-title { font-size: 26px; }
    }

    /* ── Typography inside topic cards ───────────────────────────────────── */
    .topic-title { font: 700 18px/1.25 var(--display); color: var(--ink); }
    .topic-sub { font: 400 14px/1.4 var(--text); }
    .topics-progress .tp-count { font: 500 11.5px/1.3 var(--mono); letter-spacing: .06em; text-transform: uppercase; color: var(--brand); }
    .topic-body { font-family: var(--text); }
    .topic-body .block-paragraph, .topic-body .block-paragraph p,
    .topic-body li, .topic-body .block-impact p, .topic-body .caption { font: 400 16.5px/1.65 var(--text); color: var(--ink-body); }
    .topic-body .block-paragraph.lead, .topic-body .block-paragraph.lead p { font: 500 18.5px/1.55 var(--text); color: var(--ink); }
    .topic-body .block-subheading { font: 700 20px/1.3 var(--display); color: var(--ink); margin: 6px 0 10px; }
    .topic-body .block-subheading::before { content: ""; display: block; width: 30px; height: 3px; background: var(--brand); margin-bottom: 8px; }
    .topic-body .block-heading, .topic-body .block-heading2 { font: 700 17px/1.3 var(--display); color: var(--ink); }
    .topic-body .details-label { font: 500 11.5px/1.3 var(--mono); letter-spacing: .06em; }
    .topic-body table, .topic-body table td, .topic-body table th { font-size: 14.5px; }

    /* ── Tick and cross lists (no tick boxes) ───────────────────────────── */
    .list-marks { list-style: none; padding: 0; margin: 0; }
    .list-marks li { display: flex; gap: 12px; align-items: flex-start; padding: 5px 0; }
    .list-marks .mark {
        flex: none; width: 24px; height: 24px; margin-top: 1px; border-radius: 50%; display: grid; place-items: center;
        font: 700 14px/1 var(--text); background: #dcfce7; color: #166534;
    }
    .list-crosses .mark { background: #fee2e2; color: #991b1b; }
    body.dark-mode .list-marks .mark { background: rgba(22,163,74,.2); color: #86efac; }
    body.dark-mode .list-crosses .mark { background: rgba(220,38,38,.2); color: #fca5a5; }

    /* ── Sectioned quiz ──────────────────────────────────────────────────── */
    .qz-wrap { max-width: 820px; }
    .qz-bar { margin: 4px 0 16px; }
    .qz-count { font: 500 11.5px/1.3 var(--mono); letter-spacing: .06em; text-transform: uppercase; color: var(--brand); margin-bottom: 6px; }
    .qz-body { padding: 6px 18px 14px; border-top: 1px solid #eef1f5; }
    .qz-body[hidden] { display: none; }
    .qz { padding: 16px 0 8px; border-bottom: 1px solid #eef1f5; }
    .qz:last-child { border-bottom: 0; }
    .qz-n { font: 500 11.5px/1.3 var(--mono); letter-spacing: .06em; text-transform: uppercase; color: var(--brand); margin-bottom: 8px; }
    .qz-text { font: 500 18px/1.5 var(--text); color: var(--ink); margin-bottom: 14px; }
    .qz-options { display: flex; flex-direction: column; gap: 10px; }
    .qz-opt {
        display: flex; align-items: flex-start; gap: 12px; width: 100%; text-align: left; cursor: pointer;
        border: 1.5px solid #dbe3ec; border-radius: 12px; padding: 12px 14px; background: #fff; color: var(--ink-body);
        font: 400 16.5px/1.5 var(--text); transition: border-color .15s ease, background .15s ease;
    }
    .qz-opt:hover:not(:disabled) { border-color: var(--brand); }
    .qz-opt:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }
    .qz-opt:disabled { cursor: default; }
    .qz-letter { flex: none; width: 28px; height: 28px; border-radius: 50%; display: grid; place-items: center; font: 700 14px/1 var(--text); background: #eef2f7; color: #334155; }
    .qz-label { flex: 1; min-width: 0; }
    .qz-label p { margin: 0; font: inherit; color: inherit; }
    .qz-opt.is-right { border-color: #16a34a; background: #f0fdf4; color: #14532d; }
    .qz-opt.is-right .qz-letter { background: #16a34a; color: #fff; }
    .qz-opt.is-wrong { border-color: #dc2626; background: #fef2f2; color: #7f1d1d; }
    .qz-opt.is-wrong .qz-letter { background: #dc2626; color: #fff; }
    .qz-opt.is-right .qz-letter::before { content: "\2713"; }
    .qz-opt.is-wrong .qz-letter::before { content: "\2715"; }
    .qz-opt.is-right .qz-letter span, .qz-opt.is-wrong .qz-letter span { display: none; }
    .qz-feedback { margin: 14px 0 10px; border-radius: 12px; padding: 12px 14px; font: 400 16.5px/1.6 var(--text); }
    .qz-feedback[hidden] { display: none; }
    .qz-feedback.is-right { background: #f0fdf4; border: 1px solid #bbf7d0; color: #14532d; }
    .qz-feedback.is-wrong { background: #fef2f2; border: 1px solid #fecaca; color: #7f1d1d; }
    .qz-verdict { font: 700 17px/1.3 var(--display); margin-bottom: 6px; }
    .qz-klp { font: 500 11.5px/1.3 var(--mono); letter-spacing: .06em; text-transform: uppercase; margin-bottom: 4px; }
    .qz-feedback p { margin: 0; font: inherit; color: inherit; }

    .qz-result { margin-top: 18px; border: 1px solid #e0e9f2; border-radius: 14px; padding: 22px; background: #fff; text-align: center; }
    .qz-result[hidden] { display: none; }
    .qz-score { font: 700 40px/1.1 var(--display); color: var(--ink); }
    .qz-score small { font: 500 16px/1 var(--text); color: #64748b; }
    .qz-msg { font: 400 16.5px/1.6 var(--text); color: var(--ink-body); margin: 10px auto 16px; max-width: 52ch; }
    .qz-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }

    body.dark-mode .qz-opt { background: #1e2330; border-color: #4a5568; color: #cbd5e1; }
    body.dark-mode .qz-letter { background: #2d3748; color: #e2e8f0; }
    body.dark-mode .qz-opt.is-right { background: #14532d; border-color: #16a34a; color: #bbf7d0; }
    body.dark-mode .qz-opt.is-wrong { background: #7f1d1d; border-color: #dc2626; color: #fecaca; }
    body.dark-mode .qz-feedback.is-right { background: #14532d; border-color: #166534; color: #bbf7d0; }
    body.dark-mode .qz-feedback.is-wrong { background: #7f1d1d; border-color: #991b1b; color: #fecaca; }
    body.dark-mode .qz-body, body.dark-mode .qz { border-color: #2d3748; }
    body.dark-mode .qz-result { background: #1e2330; border-color: #4a5568; }
    body.dark-mode .qz-score small { color: #94a3b8; }
    .lms [data-result] .btn[hidden], .lms .qz-actions [hidden] { display: none !important; }
    @media (prefers-reduced-motion: reduce) { .qz-opt { transition: none; } }
</style>

<style>
    /* ── Examiner Training base (scoped to .lms so Bootstrap/AdminLTE stay untouched) ── */
    .lms { --brand: #99060A; color: #2d3748; }
    .lms a { text-decoration: none; }
    .lms .card { border-radius: 14px; border: 1px solid #e6e9ef; box-shadow: 0 1px 3px rgba(16,24,40,.05); }
    .lms .card-pad { padding: 24px; }
    .lms .muted { color: #64748b; }
    .lms .btn-primary { background: var(--brand); border-color: var(--brand); color: #fff; }
    .lms .btn-primary:hover, .lms .btn-primary:focus { background: #7d0508; border-color: #7d0508; color: #fff; }
    .lms .btn-outline { background: transparent; color: var(--brand); border: 1.5px solid var(--brand); }
    .lms .btn-outline:hover { background: rgba(153,6,10,.06); color: var(--brand); }
    .lms .btn-light { background: #fff; color: #2d3748; border: 1px solid #e2e8f0; }
    .lms .btn-light:hover { background: #f8fafc; }
    .lms .progress-track { background: #e9edf2; border-radius: 999px; height: 8px; overflow: hidden; }
    .lms .progress-fill { background: var(--brand); height: 100%; border-radius: 999px; transition: width .4s ease; }
    .lms .badge-green { background: #d1fae5; color: #065f46; }
    .lms .badge-red { background: #fee2e2; color: #991b1b; }
    .lms .badge-gray { background: #f1f5f9; color: #475569; }
    .lms .badge-brand { background: rgba(153,6,10,.1); color: var(--brand); }
    .lms .badge-green, .lms .badge-red, .lms .badge-gray, .lms .badge-brand {
        display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600;
    }
    .lms table.tbl { width: 100%; border-collapse: collapse; }
    .lms table.tbl th, .lms table.tbl td { padding: 12px 14px; text-align: left; font-size: 13.5px; border-bottom: 1px solid #eef1f5; }
    .lms table.tbl th { background: #f8fafc; font-weight: 600; color: #475569; }
    .lms .flex-between { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
</style>
<style>
    /* ── Course block styles (shared: player + admin editor preview) ───────── */

    .pm-content .block { margin-bottom: 26px; }

    /* text blocks */
    .block-heading { font-size: 22px; font-weight: 800; color: #1e293b; }
    .block-heading2 { font-size: 17px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
    .block-subheading { font-size: 15px; font-weight: 700; color: var(--brand); margin-bottom: 6px; }
    .block-paragraph { font-size: 15px; line-height: 1.7; color: #334155; }
    .block-paragraph p, .block-impact p, .block-quote p { margin-bottom: 12px; }
    .block-paragraph p:last-child { margin-bottom: 0; }
    .block-paragraph, .block-heading, .block-heading2, .block-subheading,
    .block-quote blockquote, .quote-attrib, .step-desc, .lg-desc,
    .block-impact, .img-caption, .aside-caption, .video-caption {
        overflow-wrap: anywhere;
    }
    .pm-content, .preview-inner { overflow-wrap: anywhere; }
    .block-paragraph ol, .block-paragraph ul { padding-left: 22px; margin-bottom: 12px; }
    .block-paragraph li { margin-bottom: 4px; }

    /* Rich text produced by the block editor toolbar */
    :where(.block-paragraph, .block-impact, .step-desc, .lg-desc, .process-desc, .review-feedback) h2 { font-size: 22px; font-weight: 800; color: #1e293b; margin: 18px 0 10px; line-height: 1.3; }
    :where(.block-paragraph, .block-impact, .step-desc, .lg-desc, .process-desc, .review-feedback) h3 { font-size: 18px; font-weight: 700; color: #1e293b; margin: 16px 0 8px; line-height: 1.35; }
    :where(.block-paragraph, .block-impact, .step-desc, .lg-desc, .process-desc, .review-feedback) h4 { font-size: 15px; font-weight: 700; color: var(--brand); margin: 14px 0 6px; text-transform: uppercase; letter-spacing: .4px; }
    :where(.block-paragraph, .block-impact, .step-desc, .lg-desc, .process-desc, .review-feedback) > :first-child { margin-top: 0; }
    :where(.block-paragraph, .block-impact, .step-desc, .lg-desc, .process-desc, .review-feedback) blockquote {
        margin: 14px 0; padding: 10px 16px; border-left: 3px solid var(--brand);
        background: #f8fafc; border-radius: 0 8px 8px 0; color: #475569; font-style: italic;
    }
    :where(.block-paragraph, .block-impact, .step-desc, .lg-desc, .process-desc, .review-feedback) hr { border: 0; border-top: 1px solid #e6e9ef; margin: 18px 0; }
    :where(.block-paragraph, .block-impact, .step-desc, .lg-desc, .process-desc, .review-feedback, .check-text) a { color: var(--brand); text-decoration: underline; text-underline-offset: 2px; font-weight: 600; }
    .block-impact {
        background: rgba(153,6,10,.06); border-left: 4px solid var(--brand);
        padding: 18px 20px; border-radius: 0 10px 10px 0;
        transition: background .2s ease, transform .2s ease;
    }
    .block-impact:hover { background: rgba(153,6,10,.09); }
    .block-quote {
        background: #f8fafc; border: 1px solid #eef1f5; border-radius: 12px;
        padding: 28px 30px; position: relative;
        transition: box-shadow .2s ease, transform .2s ease;
    }
    .block-quote:hover { box-shadow: 0 4px 16px rgba(16,24,40,.06); }
    .quote-mark { font-size: 44px; color: var(--brand); font-family: Georgia, serif; line-height: 1; margin-bottom: 8px; }
    .block-quote blockquote { font-size: 16px; font-style: italic; color: #334155; line-height: 1.6; border-left: 0; }
    .quote-attrib { margin-top: 12px; font-size: 13px; font-weight: 600; color: var(--brand); }

    /* image blocks */
    /* Image frame — size, alignment, corners, border and shadow come from the block editor */
    .img-frame { display: block; position: relative; overflow: hidden; max-width: 100%; box-sizing: border-box; border-radius: 12px; }
    .img-frame img { display: block; width: 100%; height: auto; }
    .img-shadow-soft { box-shadow: 0 6px 18px rgba(16,24,40,.10); }
    .img-shadow-strong { box-shadow: 0 18px 44px rgba(16,24,40,.24); }
    .img-shadow-lifted { box-shadow: 0 2px 4px rgba(16,24,40,.06), 0 28px 50px -14px rgba(16,24,40,.32); }
    .img-caption { font-size: 12.5px; color: #64748b; text-align: center; margin-top: 8px; }
    .block-image-aside { display: flex; gap: 24px; align-items: flex-start; }
    .block-image-aside .aside-img { flex: 0 0 45%; }
    .aside-caption { font-size: 12.5px; color: #64748b; margin-bottom: 6px; }
    .block-image-overlay { position: relative; border-radius: 12px; overflow: hidden; }
    .block-image-overlay img { width: 100%; display: block; }
    .block-image-overlay .overlay-content {
        position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center;
        padding: 28px; color: #fff; background: rgba(0,0,0,.55);
    }
    .overlay-caption { font-size: 17px; font-weight: 700; margin-bottom: 6px; }
    .block-image-overlay .block-paragraph { color: #fff; font-size: 14px; }
    .block-image-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .block-image-grid2 figcaption { font-size: 12px; color: #64748b; text-align: center; margin-top: 6px; }

    /* ── Lists (improved bullets) ─────────────────────────────────────────── */
    .list-bullet, .list-num, .list-check { list-style: none; padding-left: 0; }
    .list-bullet li, .list-num li, .list-check li {
        position: relative; padding-left: 30px;
        font-size: 15px; line-height: 1.65; margin-bottom: 10px; color: #334155;
    }
    .list-bullet li { padding-left: 26px; }
    .list-bullet li::before {
        content: ''; position: absolute; left: 2px; top: .62em;
        width: 7px; height: 7px; border-radius: 50%;
        background: var(--brand);
        box-shadow: 0 0 0 3px rgba(153,6,10,.12);
        transition: transform .15s ease;
    }
    .list-bullet li:hover::before { transform: scale(1.25); }

    .list-num { counter-reset: lst; }
    .list-num li { counter-increment: lst; padding-left: 40px; }
    .list-num li::before {
        content: counter(lst);
        position: absolute; left: 0; top: 0;
        width: 26px; height: 26px; border-radius: 50%;
        background: rgba(153,6,10,.1); color: var(--brand);
        font-size: 12.5px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        transition: background .15s ease, color .15s ease;
    }
    .list-num li:hover::before { background: var(--brand); color: #fff; }

    /* Checklist — card rows with an animated tick */
    .checklist { --check: var(--brand); }
    .list-check { display: grid; gap: 10px; margin: 0; }
    .list-check li {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 14px 16px; margin: 0;
        background: #fff; border: 1.5px solid #e6e9ef; border-radius: 12px;
        cursor: pointer; user-select: none; outline: none;
        transition: border-color .2s ease, background .2s ease, box-shadow .2s ease, transform .15s ease;
    }
    .list-check li::before { content: none; }
    .list-check li:hover { border-color: rgba(153,6,10,.35); box-shadow: 0 4px 14px rgba(16,24,40,.06); }
    .list-check li:active { transform: scale(.995); }
    .list-check li:focus-visible { box-shadow: 0 0 0 3px rgba(153,6,10,.22); border-color: var(--check); }
    .check-box {
        flex: 0 0 24px; width: 24px; height: 24px; margin-top: 1px;
        border-radius: 7px; border: 2px solid #cbd5e1; background: #fff;
        display: flex; align-items: center; justify-content: center;
        transition: background .2s ease, border-color .2s ease, transform .2s cubic-bezier(.34,1.56,.64,1);
    }
    .check-box svg {
        width: 16px; height: 16px; fill: none; stroke: #fff; stroke-width: 3;
        stroke-linecap: round; stroke-linejoin: round;
        stroke-dasharray: 24; stroke-dashoffset: 24;
        transition: stroke-dashoffset .3s ease .05s;
    }
    .list-check li:hover .check-box { border-color: var(--check); }
    .check-text { flex: 1; font-size: 15px; line-height: 1.6; color: #334155; transition: color .2s ease; }
    .check-text p { margin: 0; }
    .list-check li.checked { background: rgba(153,6,10,.035); border-color: rgba(153,6,10,.3); }
    .list-check li.checked .check-box { background: var(--check); border-color: var(--check); transform: scale(1.08); }
    .list-check li.checked .check-box svg { stroke-dashoffset: 0; }
    .list-check li.checked .check-text { color: #64748b; }

    .checklist-foot {
        display: flex; align-items: center; gap: 12px; margin-top: 12px;
        font-size: 12.5px; font-weight: 600; color: #94a3b8;
    }
    .checklist-bar { flex: 1; max-width: 180px; height: 5px; border-radius: 999px; background: #eef1f5; overflow: hidden; }
    .checklist-bar span {
        display: block; height: 100%; width: 100%; background: var(--check);
        transform-origin: 0 50%; transform: scaleX(0);
        transition: transform .4s cubic-bezier(.22,1,.36,1);
    }
    .checklist.is-complete .checklist-foot { color: #047857; }
    .checklist.is-complete .checklist-bar span { background: #10b981; }

    /* video blocks */
    .block-video .video-frame { background: #000; border-radius: 12px; overflow: hidden; }
    .block-video video { width: 100%; display: block; }
    .video-placeholder {
        display: flex; align-items: center; gap: 16px;
        background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 12px; padding: 22px;
        transition: border-color .2s ease;
    }
    .video-placeholder:hover { border-color: var(--brand); }
    .vp-icon { color: var(--brand); }
    .vp-text { display: flex; flex-direction: column; }
    .vp-text strong { font-size: 14px; color: #334155; }
    .vp-text span { font-size: 12.5px; color: #94a3b8; }
    .video-caption { font-size: 12.5px; color: #64748b; text-align: center; margin-top: 8px; }

    .block-divider hr { border: 0; border-top: 1px solid #e6e9ef; margin: 10px 0; }

    /* flashcards */
    .flashcards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 18px; }
    .flip-card { perspective: 1000px; height: 190px; cursor: pointer; }
    .flip-card:focus-visible { outline: none; }
    .flip-card:focus-visible .flip-face { box-shadow: 0 0 0 3px rgba(153,6,10,.3); }
    .flip-inner { position: relative; width: 100%; height: 100%; transition: transform .6s cubic-bezier(.22,1,.36,1); transform-style: preserve-3d; }
    .flip-card:hover .flip-inner { transform: translateY(-3px); }
    .flip-card.flipped:hover .flip-inner { transform: rotateY(180deg) translateY(-3px); }
    .flip-card.flipped .flip-inner { transform: rotateY(180deg); }
    .flip-face {
        position: absolute; inset: 0; backface-visibility: hidden; border-radius: 12px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: 14px; text-align: center;
    }
    .flip-face img { width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; border-radius: 12px; z-index: 0; }
    .flip-front { background: var(--brand); color: #fff; }
    .flip-front .flip-label, .flip-front .flip-hint { position: relative; z-index: 1; background: rgba(0,0,0,.35); padding: 4px 10px; border-radius: 6px; }
    .flip-front .flip-label { font-size: 15px; font-weight: 700; }
    .flip-hint { font-size: 11px; opacity: .85; margin-top: 6px; }
    .flip-back { background: #fff; border: 1px solid #e6e9ef; transform: rotateY(180deg); }
    .flip-back .flip-label { font-size: 13px; color: #334155; position: relative; z-index: 1; background: rgba(255,255,255,.85); padding: 6px 10px; border-radius: 6px; }

    /* process stepper */
    .process-view { border: 1px solid #e6e9ef; border-radius: 12px; overflow: hidden; }
    .process-hero img { max-height: 220px; object-fit: cover; }
    .process-body { padding: 22px; }
    .process-title { font-size: 17px; font-weight: 700; color: #1e293b; }
    .process-desc { font-size: 14px; color: #64748b; margin: 6px 0 16px; }
    .process-step { display: none; }
    .process-step.active { display: block; animation: stepIn .32s ease; }
    @keyframes stepIn { from { opacity: 0; transform: translateX(14px); } to { opacity: 1; transform: none; } }
    .step-head { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .step-num {
        width: 30px; height: 30px; border-radius: 50%; background: var(--brand); color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;
    }
    .step-title { font-size: 15px; font-weight: 600; color: #1e293b; }
    .step-body .img-frame { margin-bottom: 12px; }
    .step-body img { max-height: 240px; object-fit: cover; }
    .step-desc { font-size: 14.5px; line-height: 1.65; color: #334155; }
    .process-nav { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 18px; }
    .process-nav .btn:disabled { opacity: .4; cursor: default; transform: none; }
    .process-dots { display: flex; gap: 6px; flex-wrap: wrap; justify-content: center; }
    .process-dots span {
        width: 8px; height: 8px; border-radius: 999px; background: #e2e8f0;
        transition: width .3s cubic-bezier(.22,1,.36,1), background .3s ease;
    }
    .process-dots span.seen { background: rgba(153,6,10,.35); }
    .process-dots span.active { width: 22px; background: var(--brand); }
    .process-summary {
        margin-top: 18px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;
        padding: 14px 16px; border-radius: 10px; font-size: 14px;
    }

    /* labeled graphics */
    .block-labeledgraphic { position: relative; }
    .lg-wrap { position: relative; border-radius: 12px; overflow: hidden; }
    .lg-wrap img { width: 100%; display: block; }
    .lg-marker {
        position: absolute; transform: translate(-50%, -50%);
        width: 28px; height: 28px; border-radius: 50%;
        background: var(--brand); color: #fff; border: 2px solid #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.3);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .lg-marker:hover { transform: translate(-50%, -50%) scale(1.18); box-shadow: 0 4px 14px rgba(0,0,0,.35); }
    .lg-marker::after {
        content: ''; position: absolute; inset: -2px; border-radius: 50%;
        border: 2px solid var(--brand); animation: lgPulse 2.2s ease-out infinite;
        animation-delay: calc(var(--i, 0) * 180ms); pointer-events: none;
    }
    @keyframes lgPulse { 0% { transform: scale(1); opacity: .7; } 100% { transform: scale(1.9); opacity: 0; } }
    .lg-marker span { transition: transform .25s ease; }
    .lg-marker.active { background: #111; }
    .lg-marker.active span { transform: rotate(45deg); }
    .lg-marker.active::after { animation: none; opacity: 0; }
    .lg-pop {
        position: absolute; width: 260px; background: #fff; border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,.18); padding: 16px; z-index: 5;
        animation: popIn .22s cubic-bezier(.22,1,.36,1);
    }
    @keyframes popIn { from { opacity: 0; transform: translateY(6px) scale(.97); } to { opacity: 1; transform: none; } }
    .lg-close { position: absolute; top: 8px; right: 10px; border: 0; background: none; font-size: 18px; cursor: pointer; color: #94a3b8; }
    .lg-title { font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 6px; }
    .lg-desc { font-size: 13px; color: #475569; line-height: 1.55; }

    /* ── Zoomable images ─────────────────────────────────────────────────── */
    .is-zoomable { cursor: zoom-in; position: relative; }
    img.is-zoomable { transition: transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s ease, filter .35s ease; }
    img.is-zoomable:hover { transform: scale(1.025); }
    .is-zoomable:focus-visible { outline: 3px solid rgba(153,6,10,.4); outline-offset: 3px; }
    .img-frame:has(.is-zoomable)::after, .block-image-overlay.is-zoomable::after {
        content: ''; position: absolute; top: 12px; right: 12px; width: 34px; height: 34px; border-radius: 10px;
        background: rgba(15,23,42,.62) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round'%3E%3Cpath d='M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7'/%3E%3C/svg%3E") center/16px no-repeat;
        opacity: 0; transform: scale(.85); transition: opacity .2s ease, transform .2s ease; pointer-events: none;
    }
    .img-frame:has(.is-zoomable):hover::after, .block-image-overlay.is-zoomable:hover::after {
        opacity: 1; transform: none;
    }

    /* ── Scroll entrance animations (Rise-style) ──────────────────────────── */
    .block-reveal { opacity: 1; }
    .block-reveal[data-animate="1"] {
        opacity: 0;
        transform: translateY(28px);
        transition: opacity .7s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1);
    }
    .block-reveal[data-animate="1"].is-visible { opacity: 1; transform: none; }

    /* Images settle in with a soft zoom */
    .block-reveal[data-animate="1"] .block-image-hero img,
    .block-reveal[data-animate="1"] .aside-img img,
    .block-reveal[data-animate="1"] .block-image-overlay img,
    .block-reveal[data-animate="1"] .block-image-grid2 img {
        transform: scale(1.06); transition: transform 1.2s cubic-bezier(.22,1,.36,1), box-shadow .35s ease;
    }
    .block-reveal[data-animate="1"].is-visible .block-image-hero img,
    .block-reveal[data-animate="1"].is-visible .aside-img img,
    .block-reveal[data-animate="1"].is-visible .block-image-overlay img,
    .block-reveal[data-animate="1"].is-visible .block-image-grid2 img { transform: none; }

    /* Overlay text rises after the image */
    .block-reveal[data-animate="1"] .overlay-content > * { opacity: 0; transform: translateY(12px); transition: opacity .6s ease .25s, transform .6s cubic-bezier(.22,1,.36,1) .25s; }
    .block-reveal[data-animate="1"].is-visible .overlay-content > * { opacity: 1; transform: none; }

    /* Headings draw a short brand accent */
    .block-heading { position: relative; padding-bottom: 10px; }
    .block-heading::after {
        content: ''; position: absolute; left: 0; bottom: 0; height: 3px; width: 44px; border-radius: 3px;
        background: var(--brand); transform-origin: 0 50%;
    }
    .block-heading:has([style*="text-align: center"])::after { left: 50%; margin-left: -22px; transform-origin: 50% 50%; }
    .block-reveal[data-animate="1"] .block-heading::after { transform: scaleX(0); transition: transform .7s cubic-bezier(.22,1,.36,1) .2s; }
    .block-reveal[data-animate="1"].is-visible .block-heading::after { transform: scaleX(1); }

    /* Impact notes slide their accent bar in */
    .block-reveal[data-animate="1"] .block-impact { border-left-color: transparent; transition: border-color .6s ease .2s, background .2s ease; }
    .block-reveal[data-animate="1"].is-visible .block-impact { border-left-color: var(--brand); }

    /* Stagger list items, flashcards and hotspots inside a revealed block */
    .block-reveal[data-animate="1"].is-visible li,
    .block-reveal[data-animate="1"].is-visible .flip-card {
        animation: itemIn .55s cubic-bezier(.22,1,.36,1) backwards;
        animation-delay: calc(.12s + var(--i, 0) * 70ms);
    }
    .block-reveal[data-animate="1"].is-visible .lg-marker {
        animation: markerIn .45s cubic-bezier(.34,1.56,.64,1) backwards;
        animation-delay: calc(.35s + var(--i, 0) * 90ms);
    }
    @keyframes itemIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
    @keyframes markerIn { from { opacity: 0; transform: translate(-50%, -50%) scale(0); } to { opacity: 1; transform: translate(-50%, -50%) scale(1); } }

    /* ── Lightbox ─────────────────────────────────────────────────────────── */
    html.lb-lock { overflow: hidden; }
    .lightbox {
        position: fixed; inset: 0; z-index: 2000;
        display: grid; grid-template-columns: 64px 1fr 64px; grid-template-rows: 56px 1fr auto;
        background: rgba(10,12,18,.9); backdrop-filter: blur(6px);
        opacity: 0; transition: opacity .22s ease;
    }
    .lightbox[hidden] { display: none; }
    .lightbox.is-open { opacity: 1; }
    .lb-toolbar { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; color: rgba(255,255,255,.75); }
    .lb-count { font-size: 13px; font-weight: 600; letter-spacing: .4px; }
    .lb-actions { display: flex; gap: 8px; }
    .lb-btn, .lb-nav {
        border: 0; cursor: pointer; color: #fff; background: rgba(255,255,255,.1);
        display: flex; align-items: center; justify-content: center;
        transition: background .15s ease, transform .15s ease;
    }
    .lb-btn { width: 40px; height: 40px; border-radius: 10px; }
    .lb-btn:hover, .lb-nav:hover { background: rgba(255,255,255,.2); }
    .lb-btn svg, .lb-nav svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .lb-nav { align-self: center; justify-self: center; width: 46px; height: 46px; border-radius: 50%; }
    .lb-nav:active { transform: scale(.94); }
    .lb-nav[hidden] { display: none; }
    .lb-prev { grid-column: 1; grid-row: 2; }
    .lb-next { grid-column: 3; grid-row: 2; }
    .lb-stage { grid-column: 2; grid-row: 2; display: flex; align-items: center; justify-content: center; overflow: hidden; min-height: 0; }
    .lb-img {
        max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; cursor: zoom-in;
        box-shadow: 0 20px 60px rgba(0,0,0,.5);
        opacity: 0; transform: scale(.96); transition: opacity .25s ease, transform .3s cubic-bezier(.22,1,.36,1);
    }
    .lb-img.is-in { opacity: 1; transform: none; }
    .lb-stage.zoomed { overflow: auto; align-items: flex-start; justify-content: flex-start; }
    .lb-stage.zoomed .lb-img { max-width: none; max-height: none; cursor: zoom-out; margin: auto; }
    .lb-caption { grid-column: 1 / -1; grid-row: 3; text-align: center; color: rgba(255,255,255,.85); font-size: 14px; padding: 14px 20px 22px; }
    .lb-caption[hidden] { display: none; }
    @media (max-width: 640px) {
        .lightbox { grid-template-columns: 0 1fr 0; }
        .lb-nav { position: absolute; bottom: 18px; background: rgba(255,255,255,.14); }
        .lb-prev { left: 18px; } .lb-next { right: 18px; }
        .lb-caption { padding-bottom: 80px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .block-reveal[data-animate="1"] { opacity: 1; transform: none; transition: none; }
        .block-reveal[data-animate="1"].is-visible li,
        .block-reveal[data-animate="1"].is-visible .flip-card,
        .block-reveal[data-animate="1"].is-visible .lg-marker,
        .lg-marker::after { animation: none; }
        .block-reveal[data-animate="1"] img,
        .block-reveal[data-animate="1"] .overlay-content > *,
        .block-reveal[data-animate="1"] .block-heading::after { transform: none !important; opacity: 1 !important; transition: none !important; }
        .lightbox, .lb-img { transition: none; }
        .process-step.active { animation: none; }
    }
</style>

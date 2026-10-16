{{-- Shared look of the examiner 'View marks' pages (view_results, view_fcs_results). --}}
<style>
    .vr { --vr-surface:#fff; --vr-subtle:#f7f4f3; --vr-border:#ece6e4; --vr-text:#251917; --vr-muted:#6f605d;
          --vr-pass:#1b6b3a; --vr-pass-bg:#e5f3ea; 
          --vr-borderline:#8a5a00; --vr-borderline-bg:#fdf1d6; --vr-fail:#a02626; --vr-fail-bg:#fbe4e2; }
    body.dark-mode .vr { --vr-surface:#1e2330; --vr-subtle:#252c3b; --vr-border:#2d3748; --vr-text:#e2e8f0; --vr-muted:#94a3b8;
          --vr-pass:#7ad69a; --vr-pass-bg:rgba(122,214,154,.14); --vr-borderline:#f3c969; --vr-borderline-bg:rgba(243,201,105,.14);
          --vr-fail:#f48a8a; --vr-fail-bg:rgba(244,138,138,.14); }
    .vr { max-width:720px; margin:0 auto; padding:8px 0 32px; color:var(--vr-text); }
    .vr-back { display:inline-flex; align-items:center; gap:6px; font-size:.9rem; color:var(--vr-muted); text-decoration:none; margin:4px 0 14px; }
    .vr-back:hover { color:#a02626; text-decoration:none; }
    .vr-card { background:var(--vr-surface); border:1px solid var(--vr-border); border-radius:6px; overflow:hidden; }
    .vr-head { padding:22px 24px 18px; }
    .vr-eyebrow { font-size:.72rem; letter-spacing:.08em; text-transform:uppercase; color:var(--vr-muted); }
    .vr-name { font-size:1.45rem; font-weight:700; margin:2px 0 4px; color:var(--vr-text); }
    .vr-meta { font-size:.9rem; color:var(--vr-muted); }
    .vr-score { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; padding:18px 24px; background:var(--vr-subtle); border-top:1px solid var(--vr-border); border-bottom:1px solid var(--vr-border); }
    .vr-total { font-size:2.6rem; line-height:1; font-weight:700; color:#a02626; }
    body.dark-mode .vr-total { color:#f48a8a; }
    .vr-total small { font-size:.85rem; font-weight:500; color:var(--vr-muted); margin-left:6px; }
    .vr-grade { font-size:.82rem; font-weight:600; text-transform:capitalize; padding:4px 10px; border-radius:4px; }
    .vr-grade.pass { color:var(--vr-pass); background:var(--vr-pass-bg); }
    .vr-grade.borderline { color:var(--vr-borderline); background:var(--vr-borderline-bg); }
    .vr-grade.fail { color:var(--vr-fail); background:var(--vr-fail-bg); }
    .vr-section { padding:18px 24px 6px; }
    .vr-section h4 { font-size:.72rem; letter-spacing:.08em; text-transform:uppercase; color:var(--vr-muted); margin:0 0 6px; font-weight:600; }
    .vr-row { display:flex; justify-content:space-between; align-items:center; padding:11px 0; border-bottom:1px solid var(--vr-border); }
    .vr-row:last-child { border-bottom:0; }
    .vr-row span:first-child { color:var(--vr-muted); }
    .vr-row span:last-child { font-weight:600; font-variant-numeric:tabular-nums; }
    .vr-remarks { margin:0; padding:2px 0 18px; line-height:1.6; white-space:pre-wrap; overflow-wrap:anywhere; color:var(--vr-text); }
    .vr-empty { color:var(--vr-muted); font-style:italic; }
    .vr-actions { display:flex; gap:10px; padding:16px 24px 22px; border-top:1px solid var(--vr-border); }
    .vr-btn { display:inline-block; padding:8px 16px; border-radius:4px; font-weight:600; font-size:.9rem; text-decoration:none; }
    .vr-btn.primary { background:#FEC503; color:#000; }
    .vr-btn.primary:hover { color:#000; text-decoration:none; filter:brightness(.96); }
    .vr-btn.ghost { background:transparent; color:var(--vr-text); border:1px solid var(--vr-border); }
    .vr-btn.ghost:hover { color:#a02626; text-decoration:none; }
    @media (max-width:575px) { .vr-head, .vr-score, .vr-section, .vr-actions { padding-left:16px; padding-right:16px; } .vr-name { font-size:1.2rem; } .vr-total { font-size:2.1rem; } }
    .vr-subs { display:flex; gap:28px; }
    .vr-subs .vr-eyebrow { margin-bottom:2px; }
    .vr-sub { font-size:1.15rem; font-weight:700; }
    .vr-station { display:flex; justify-content:space-between; align-items:baseline; margin:14px 0 2px; padding-top:12px; border-top:1px solid var(--vr-border); font-weight:600; }
    .vr-station:first-of-type { border-top:0; margin-top:4px; padding-top:0; }
    .vr-station small { color:var(--vr-muted); font-weight:500; font-size:.8rem; }
    .vr-case { font-size:.78rem; color:#a02626; font-weight:600; margin:8px 0 0; }
    body.dark-mode .vr-case { color:#f48a8a; }
    .vr-sum { display:flex; justify-content:space-between; padding:9px 0 4px; font-weight:700; }
    .vr-note { margin:2px 0 8px; font-size:.88rem; color:var(--vr-muted); overflow-wrap:anywhere; }
    .vr-section-total { display:flex; justify-content:space-between; margin:6px -24px 0; padding:12px 24px; background:var(--vr-subtle); font-weight:700; }
    @media (max-width:575px) { .vr-section-total { margin-left:-16px; margin-right:-16px; padding-left:16px; padding-right:16px; } .vr-subs { gap:18px; } }
</style>

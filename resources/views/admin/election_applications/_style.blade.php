<style>
    /* Fellowship by Election: one small design system for the list and the application page.
       Colours are variables on .fe so the dark theme (body.dark-mode) only has to swap them. */
    .fe {
        --fe-surface:#fff; --fe-soft:#f6f7f9; --fe-line:#e8eaee; --fe-text:#1f2937; --fe-muted:#6b7280; --fe-accent:#a02626; --fe-accent-soft:#fbeeee;
        --fe-pending-bg:#fff6e0; --fe-pending-fg:#8a5a00; --fe-ok-bg:#e6f6ec; --fe-ok-fg:#166534; --fe-no-bg:#fdecec; --fe-no-fg:#b42318;
        --fe-info-bg:#eef0fb; --fe-info-fg:#3b4394; --fe-warn-bg:#fff6e0; --fe-warn-fg:#8a5a00;
        color:var(--fe-text); max-width:1180px; margin:0 auto; padding-bottom:40px;
    }
    body.dark-mode .fe {
        --fe-surface:#2d3748; --fe-soft:#26303f; --fe-line:#3f4b5f; --fe-text:#e5e7eb; --fe-muted:#9aa6b8; --fe-accent:#f48a8a; --fe-accent-soft:#3b2b33;
        --fe-pending-bg:rgba(251,191,36,.14); --fe-pending-fg:#fcd34d; --fe-ok-bg:rgba(52,211,153,.14); --fe-ok-fg:#6ee7b7; --fe-no-bg:rgba(248,113,113,.15); --fe-no-fg:#fca5a5;
        --fe-info-bg:rgba(129,140,248,.17); --fe-info-fg:#c7cbff; --fe-warn-bg:rgba(251,191,36,.14); --fe-warn-fg:#fcd34d;
    }
    .fe a { color:inherit; }
    .fe-head { display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:12px; margin:6px 0 20px; }
    .fe-title { font-size:1.55rem; font-weight:650; letter-spacing:-.01em; margin:0; }
    .fe-sub { color:var(--fe-muted); font-size:.88rem; margin-top:2px; }
    .fe-panel { background:var(--fe-surface); border:1px solid var(--fe-line); border-radius:12px; margin-bottom:16px; }
    .fe-panel-h { padding:14px 20px; border-bottom:1px solid var(--fe-line); font-size:.74rem; font-weight:650; letter-spacing:.07em; text-transform:uppercase; color:var(--fe-muted); display:flex; justify-content:space-between; align-items:center; }
    .fe-panel-b { padding:6px 20px 14px; }
    .fe-note { color:var(--fe-muted); font-size:.82rem; }

    /* buttons */
    .fe-btn { display:inline-flex; align-items:center; gap:7px; border:1px solid var(--fe-line); background:var(--fe-surface); color:var(--fe-text) !important; border-radius:8px; padding:7px 14px; font-size:.86rem; font-weight:550; cursor:pointer; text-decoration:none !important; line-height:1.3; transition:background .12s, border-color .12s; }
    .fe-btn:hover { background:var(--fe-soft); }
    .fe-btn[disabled] { opacity:.5; cursor:not-allowed; }
    .fe-btn.primary { background:var(--fe-accent); border-color:var(--fe-accent); color:#fff !important; }
    body.dark-mode .fe-btn.primary { color:#2a1517 !important; }
    .fe-btn.primary:hover { filter:brightness(1.07); }
    .fe-btn.ok { background:#16a34a; border-color:#16a34a; color:#fff !important; }
    .fe-btn.ok:hover { filter:brightness(1.07); background:#16a34a; }
    .fe-btn.ghost-danger { color:var(--fe-no-fg) !important; }
    .fe-btn.ghost-danger:hover { background:var(--fe-no-bg); }
    .fe-btn.block { width:100%; justify-content:center; }
    .fe-icon-btn { width:32px; height:32px; padding:0; justify-content:center; border-radius:8px; }

    /* status: a coloured dot and plain text, no chip */
    .fe-pill { display:inline-flex; align-items:center; gap:7px; font-size:.84rem; font-weight:600; white-space:nowrap; background:none; padding:0; }
    .fe-pill::before { content:''; width:7px; height:7px; border-radius:50%; background:currentColor; }
    .fe-pill.pending { color:var(--fe-pending-fg); }
    .fe-pill.approved { color:var(--fe-ok-fg); }
    .fe-pill.rejected { color:var(--fe-no-fg); }
    .fe-pill.info, .fe-pill.warn { color:var(--fe-muted); font-weight:500; }
    .fe-pill.plain::before, .fe-pill.info::before, .fe-pill.warn::before { display:none; }
    .fe-tag { color:var(--fe-muted); font-size:.8rem; }
    .fe-sys { font-weight:600; font-size:.86rem; } .fe-sys.fellow { color:var(--fe-info-fg); } .fe-sys.tentative { color:var(--fe-warn-fg); }
    .fe-sys small { display:block; font-weight:400; color:var(--fe-muted); font-size:.8rem; }

    /* form controls */
    .fe-input, .fe-select, .fe-textarea { width:100%; background:var(--fe-surface); color:var(--fe-text); border:1px solid var(--fe-line); border-radius:8px; padding:7px 11px; font-size:.88rem; }
    .fe-input:focus, .fe-select:focus, .fe-textarea:focus { outline:none; border-color:var(--fe-accent); box-shadow:0 0 0 3px var(--fe-accent-soft); }
    .fe-label { display:block; font-size:.76rem; font-weight:600; color:var(--fe-muted); margin:0 0 4px; }
    .fe select.fe-select option { background:var(--fe-surface); color:var(--fe-text); }

    /* avatar */
    .fe-avatar { flex:0 0 auto; width:36px; height:36px; border-radius:50%; background:var(--fe-accent-soft); color:var(--fe-accent); display:inline-flex; align-items:center; justify-content:center; font-weight:650; font-size:.8rem; }
    .fe-avatar.lg { width:52px; height:52px; font-size:1.05rem; }

    /* modal */
    .fe-modal .modal-content { background:var(--fe-surface); color:var(--fe-text); border:1px solid var(--fe-line); border-radius:14px; }
    .fe-modal .modal-header, .fe-modal .modal-footer { border-color:var(--fe-line); }
    .fe-modal .close { color:var(--fe-text); text-shadow:none; opacity:.6; }
</style>

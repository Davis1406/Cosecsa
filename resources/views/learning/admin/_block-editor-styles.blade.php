<style>
    .editor-head { margin-bottom: 20px; }
    .editor-split {
        display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 20px; align-items: stretch;
    }
    @media (max-width: 900px) { .editor-split { grid-template-columns: 1fr; } }
    .editor-pane { display: flex; flex-direction: column; overflow: hidden; }
    .editor-pane:hover { transform: none; }
    .editor-pane-head {
        padding: 13px 18px; border-bottom: 1px solid #eef1f5;
        font-size: 13px; font-weight: 700; color: #1e293b;
    }
    .editor-fields { padding: 18px; overflow-y: auto; overflow-x: hidden; max-height: calc(100vh - 360px); }
    .editor-field { margin-bottom: 18px; min-width: 0; }
    .editor-field:last-child { margin-bottom: 0; }
    .editor-images-divider { margin: 26px 0 16px; padding-top: 20px; border-top: 1px solid #eef1f5; }
    .editor-fields > .editor-images-divider:first-child { margin-top: 0; padding-top: 0; border-top: 0; }
    .editor-label {
        display: block; font-size: 11.5px; font-weight: 700; color: #475569;
        text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px;
    }

    /* ── Formatting toolbar ─────────────────────────────────────────────── */
    .rt-toolbar {
        display: flex; flex-wrap: wrap; gap: 4px 6px; align-items: center;
        padding: 8px 12px; border-bottom: 1px solid #eef1f5; background: #fafbfc;
        transition: opacity .15s ease;
    }
    .rt-toolbar[data-disabled="1"] .rt-group { opacity: .45; }
    .rt-group { display: flex; align-items: center; gap: 2px; padding-right: 6px; border-right: 1px solid #e6e9ef; }
    .rt-group:last-of-type { border-right: 0; }
    .rt-btn {
        width: 30px; height: 30px; border: 0; border-radius: 7px; background: transparent; color: #334155;
        display: inline-flex; align-items: center; justify-content: center; cursor: pointer; position: relative;
        transition: background .12s ease, color .12s ease;
    }
    .rt-btn svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .rt-btn:hover { background: #eef1f5; }
    .rt-btn.is-active { background: rgba(153,6,10,.1); color: var(--brand); }
    .rt-btn-color svg { margin-top: -4px; }
    .rt-swatch-bar { position: absolute; left: 7px; right: 7px; bottom: 5px; height: 3px; border-radius: 2px; background: #99060a; }
    .rt-select {
        height: 30px; border: 1px solid #e2e8f0; border-radius: 7px; background: #fff;
        font-size: 12.5px; font-weight: 600; color: #334155; padding: 0 6px; cursor: pointer;
    }
    .rt-pop-wrap { position: relative; }
    .rt-pop {
        position: absolute; top: 36px; left: 0; z-index: 30; width: 200px;
        background: #fff; border: 1px solid #e6e9ef; border-radius: 12px; padding: 12px;
        box-shadow: 0 12px 32px rgba(16,24,40,.16);
    }
    .rt-pop-title { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
    .rt-swatches { display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px; }
    .rt-swatch {
        width: 24px; height: 24px; border-radius: 6px; border: 1px solid rgba(0,0,0,.12); cursor: pointer; padding: 0;
        transition: transform .1s ease;
    }
    .rt-swatch:hover { transform: scale(1.12); }
    .rt-swatch-custom {
        background: conic-gradient(red, yellow, lime, aqua, blue, magenta, red); overflow: hidden; position: relative;
    }
    .rt-swatch-custom input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .rt-pop-link { margin-top: 10px; border: 0; background: none; color: #64748b; font-size: 12px; font-weight: 600; cursor: pointer; padding: 0; }
    .rt-pop-link:hover { color: var(--brand); }
    .rt-linkbar[hidden] { display: none; }
    .rt-linkbar { flex-basis: 100%; display: flex; align-items: center; gap: 8px; padding-top: 6px; }
    .rt-linkbar .form-control { padding: 6px 10px; font-size: 13px; }
    .rt-check { display: flex; align-items: center; gap: 5px; font-size: 12px; color: #475569; white-space: nowrap; }

    .editable-field {
        border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px 12px;
        font-size: 14.5px; line-height: 1.65; color: #1e293b; background: #fff;
        min-height: 46px; transition: border-color .15s ease, box-shadow .15s ease;
        overflow-wrap: anywhere; word-break: break-word; overflow-x: hidden;
        max-width: 100%;
    }
    .editable-field :where(p, ol, ul, li, h1, h2, h3, h4, h5, blockquote, div, span, a, strong, em) {
        max-width: 100%; overflow-wrap: anywhere; word-break: break-word;
    }
    .editable-field ol, .editable-field ul { padding-left: 22px; }
    .editable-field p { margin: 0 0 10px; }
    .editable-field p:last-child, .editable-field li:last-child { margin-bottom: 0; }
    .editable-field h2 { font-size: 20px; margin: 6px 0; }
    .editable-field h3 { font-size: 17px; margin: 6px 0; }
    .editable-field h4 { font-size: 14px; margin: 6px 0; text-transform: uppercase; letter-spacing: .4px; color: var(--brand); }
    .editable-field blockquote { border-left: 3px solid var(--brand); padding: 4px 12px; margin: 6px 0; color: #475569; font-style: italic; }
    .editable-field a { color: var(--brand); text-decoration: underline; }
    .editable-field hr { border: 0; border-top: 1px solid #e2e8f0; margin: 10px 0; }
    .editable-field:focus {
        outline: none; border-color: var(--brand);
        box-shadow: 0 0 0 3px rgba(153,6,10,.12);
    }
    .editable-field:empty::before { content: attr(data-placeholder); color: #94a3b8; }

    /* ── Image settings ─────────────────────────────────────────────────── */
    .image-field { padding: 14px; border: 1px solid #eef1f5; border-radius: 12px; background: #fcfcfd; }
    .image-field-row { display: flex; gap: 16px; align-items: center; }
    .image-field-preview {
        width: 132px; height: 92px; min-width: 132px; border-radius: 10px;
        border: 1.5px dashed #cbd5e1; background: #f8fafc;
        display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 6px;
    }
    .image-field-preview img { width: 100%; height: 100%; object-fit: contain; }
    .image-field-controls { flex: 1; }
    .upload-btn {
        display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
        padding: 8px 14px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff;
        font-size: 13px; font-weight: 600; color: #334155; transition: border-color .15s ease;
    }
    .upload-btn:hover { border-color: var(--brand); color: var(--brand); }
    .upload-btn input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .img-settings { display: grid; gap: 10px; margin-top: 14px; }
    .setting { display: flex; align-items: center; gap: 10px; }
    .setting-label { width: 64px; font-size: 12px; font-weight: 700; color: #475569; }
    .setting input[type="range"] { flex: 1; accent-color: var(--brand); }
    .setting output { width: 44px; text-align: right; font-size: 12px; font-weight: 600; color: #64748b; font-variant-numeric: tabular-nums; }
    .setting-color { width: 32px; height: 26px; padding: 0; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; cursor: pointer; }
    .setting-select { flex: 1; padding: 6px 10px; font-size: 13px; }
    .seg { display: inline-flex; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; }
    .seg label { position: relative; }
    .seg input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
    .seg span { display: block; padding: 5px 12px; font-size: 12px; font-weight: 600; color: #475569; border-right: 1px solid #e2e8f0; }
    .seg label:last-child span { border-right: 0; }
    .seg input:checked + span { background: var(--brand); color: #fff; }
    .seg input:focus-visible + span { box-shadow: inset 0 0 0 2px rgba(153,6,10,.35); }
    .setting-reset { justify-self: start; margin-top: 2px; }

    .preview-frame { background: #f0f2f5; padding: 22px; flex: 1; overflow-y: auto; max-height: calc(100vh - 320px); }
    .preview-inner {
        background: #fff; border-radius: 12px; padding: 26px 30px;
        box-shadow: 0 1px 4px rgba(16,24,40,.08);
        max-width: 760px; margin: 0 auto;
    }
    .preview-inner .block { margin-bottom: 26px; }
    .sync-status { font-size: 11.5px; font-weight: 700; }
    .sync-status[data-state="synced"] { color: #059669; }
    .sync-status[data-state="syncing"] { color: #d97706; }
    .sync-status[data-state="error"] { color: #dc2626; }
    .editor-footer { margin-top: 20px; display: flex; align-items: center; gap: 16px; }
</style>
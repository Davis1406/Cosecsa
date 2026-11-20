<style>
    .fe-grid { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:20px; align-items:start; }
    @media (max-width: 991px) { .fe-grid { grid-template-columns:1fr; } }
    .fe-aside { position:sticky; top:12px; }
    @media (max-width: 991px) { .fe-aside { position:static; } }
    .fe-back { color:var(--fe-muted) !important; font-size:.85rem; text-decoration:none !important; }
    .fe-back:hover { color:var(--fe-accent) !important; }
    .fe-id { display:flex; align-items:center; gap:16px; margin:10px 0 22px; }
    .fe-id h1 { font-size:1.5rem; font-weight:650; margin:0; letter-spacing:-.01em; }
    .fe-dl { display:grid; grid-template-columns:190px 1fr; }
    .fe-dl dt, .fe-dl dd { margin:0; padding:10px 0; border-top:1px solid var(--fe-line); font-size:.9rem; }
    .fe-dl dt:first-of-type, .fe-dl dd:first-of-type { border-top:0; }
    .fe-dl dt { color:var(--fe-muted); font-weight:550; padding-right:14px; }
    .fe-dl dd { white-space:pre-line; word-break:break-word; }
    .fe-dl .none { color:var(--fe-muted); opacity:.6; }
    @media (max-width: 575px) { .fe-dl { grid-template-columns:1fr; } .fe-dl dt { border-top:1px solid var(--fe-line); padding-bottom:0; } .fe-dl dd { border-top:0; padding-top:2px; } }
    .fe-doc { display:flex; align-items:center; gap:12px; padding:10px 0; border-top:1px solid var(--fe-line); }
    .fe-doc:first-child { border-top:0; }
    .fe-doc .ic { width:34px; height:34px; border-radius:8px; background:var(--fe-soft); color:var(--fe-muted); display:flex; align-items:center; justify-content:center; }
    .fe-doc.on .ic { background:var(--fe-accent-soft); color:var(--fe-accent); }
    .fe-doc .t { flex:1; font-size:.9rem; font-weight:550; }
    details.fe-more summary { cursor:pointer; list-style:none; }
    details.fe-more summary::-webkit-details-marker { display:none; }
    details.fe-more[open] .chev { transform:rotate(90deg); }
    .chev { transition:transform .15s; }
    .fe-callout { border-left:3px solid var(--fe-line); padding:2px 0 2px 12px; font-size:.86rem; margin-bottom:14px; color:var(--fe-text); }
    .fe-callout.info { border-color:var(--fe-info-fg); }
    .fe-callout.warn { border-color:var(--fe-warn-fg); }
    .fe-callout.ok { border-color:var(--fe-ok-fg); }
    .fe-callout a { text-decoration:underline; }
    .fe-gap { display:flex; align-items:flex-start; gap:10px; padding:9px 0; border-top:1px solid var(--fe-line); font-size:.88rem; cursor:pointer; margin:0; }
    .fe-gap:first-of-type { border-top:0; }
    .fe-gap input { margin-top:3px; accent-color:var(--fe-accent); }
    .fe-gap .l { font-weight:600; } .fe-gap .v { color:var(--fe-muted); word-break:break-word; }
    .fe-stack > * + * { margin-top:10px; }
</style>
<style>
    .fe-grid.single { grid-template-columns:1fr; }
    .fe-grid.single .fe-aside { order:-1; position:static; }
</style>

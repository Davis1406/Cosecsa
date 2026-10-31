<style>
    .portal-hello { background:#fff; border:1px solid #e2e8f0; border-left:4px solid #a02626; border-radius:.75rem; padding:1.25rem 1.5rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1.25rem; flex-wrap:wrap; }
    .portal-hello img, .portal-hello .portal-avatar { width:64px; height:64px; border-radius:50%; object-fit:cover; flex:0 0 64px; }
    .portal-hello .portal-avatar { background:#f5e6e6; color:#a02626; display:flex; align-items:center; justify-content:center; font-size:1.6rem; }
    .portal-hello h2 { font-size:1.35rem; margin:0; color:#2d2d2d; }
    .portal-hello .portal-sub { color:#6b7280; font-size:.9rem; margin-top:.15rem; }
    .portal-tile { background:#fff; border:1px solid #e2e8f0; border-radius:.75rem; padding:1.1rem 1.25rem; margin-bottom:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,.05); display:flex; justify-content:space-between; align-items:center; }
    .portal-tile .label { font-size:.72rem; font-weight:700; letter-spacing:.06em; color:#6b7280; text-transform:uppercase; margin:0; }
    .portal-tile .value { font-size:1.9rem; font-weight:700; color:#2d2d2d; margin:0; line-height:1.2; }
    .portal-tile .icon { width:46px; height:46px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.15rem; background:#f5e6e6; color:#a02626; }
    .portal-card { background:#fff; border:1px solid #e2e8f0; border-radius:.75rem; margin-bottom:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,.05); }
    .portal-card > .portal-card-head { padding:.9rem 1.25rem; border-bottom:1px solid #eef0f3; font-weight:700; color:#a02626; display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
    .portal-card > .portal-card-head .ml-auto input { font-size:.85rem; }
    .portal-card > .portal-card-body { padding:1.1rem 1.25rem; }
    .portal-card table { margin-bottom:0; }
    .portal-card th { font-size:.74rem; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; border-top:0; white-space:nowrap; }
    .portal-kv { display:grid; grid-template-columns:minmax(120px,200px) 1fr; gap:.45rem 1rem; margin:0; }
    .portal-kv dt { color:#6b7280; font-weight:600; font-size:.85rem; }
    .portal-kv dd { margin:0; color:#2d2d2d; word-break:break-word; }
    .portal-empty { text-align:center; padding:2.5rem 1rem; color:#6b7280; }
    .portal-empty i { font-size:2rem; color:#d6b3b3; margin-bottom:.75rem; display:block; }
    .portal-pill { display:inline-block; padding:.1rem .6rem; border-radius:20px; font-size:.75rem; font-weight:700; background:#e5e7eb; color:#374151; }
    .portal-pill.ok { background:#dcfce7; color:#166534; } .portal-pill.warn { background:#fef3c7; color:#92400e; } .portal-pill.bad { background:#fee2e2; color:#991b1b; }
    @media (max-width:575px) { .portal-kv { grid-template-columns:1fr; gap:.1rem; } .portal-kv dt { margin-top:.5rem; } }
</style>

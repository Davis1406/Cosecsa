{{-- Styles of the examiner player shell. Shared by player.blade.php and the coded modules. --}}
@include('learning.partials.styles')
@include('learning.partials.course-ui')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="{{ asset('dist/css/lms-certificate.css') }}" rel="stylesheet">
<style>
    .reading-progress {
        position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 1000;
        background: transparent; pointer-events: none;
    }
    .reading-progress span {
        display: block; height: 100%; width: 100%;
        transform-origin: 0 50%; transform: scaleX(0);
        background: linear-gradient(90deg, var(--brand), #f2bd2c);
    }

    .player-shell { display: flex; align-items: flex-start; }

    .player-sidebar {
        position: sticky; top: 12px; align-self: flex-start;
        width: 290px; min-width: 290px; height: calc(100vh - 24px);
        border-radius: 14px; overflow: hidden; border: 1px solid #e6e9ef;
        background: #fff;
        border-right: 1px solid #e6e9ef;
        display: flex; flex-direction: column;
        box-shadow: 4px 0 18px rgba(16,24,40,.03);
    }
    .ps-head { padding: 22px 22px 18px; border-bottom: 1px solid #eef1f5; }
    .ps-head-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .ps-home {
        display: inline-flex; align-items: center; gap: 7px;
        font-size: 12.5px; font-weight: 600; color: var(--brand); letter-spacing: .2px;
    }
    .ps-close { display: none; border: 0; background: #f1f5f9; width: 30px; height: 30px; border-radius: 8px; font-size: 20px; line-height: 1; color: #475569; cursor: pointer; }
    .ps-course { font-size: 15px; font-weight: 800; color: #0f172a; line-height: 1.35; display: flex; align-items: center; gap: 10px; }
    .ps-logo { width: 36px; height: 36px; object-fit: contain; flex-shrink: 0; }
    .ps-progress { margin-top: 16px; }
    .ps-progress-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 7px; }
    .ps-progress-label { font-size: 12px; color: #64748b; font-weight: 600; }
    .ps-progress-pct { font-size: 12px; font-weight: 800; color: var(--brand); }
    .ps-progress .progress-track { height: 7px; background: #eef1f5; }
    .ps-progress .progress-fill { animation: fillIn 1s cubic-bezier(.22,1,.36,1) both .2s; transform-origin: 0 50%; }
    @keyframes fillIn { from { transform: scaleX(0); } to { transform: scaleX(1); } }

    .ps-nav { flex: 1; overflow-y: auto; padding: 12px 12px 16px; scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent; }
    .ps-nav::-webkit-scrollbar { width: 6px; }
    .ps-nav::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 999px; }
    .ps-nav-label {
        font-size: 10.5px; font-weight: 800; color: #94a3b8;
        text-transform: uppercase; letter-spacing: 1.1px; padding: 4px 10px 10px;
    }
    .ps-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 12px; border-radius: 12px;
        font-size: 13.5px; color: #475569; margin-bottom: 4px;
        transition: background .14s ease, transform .14s ease, box-shadow .14s ease;
        animation: psIn .45s cubic-bezier(.22,1,.36,1) both;
        animation-delay: calc(var(--i, 0) * 35ms);
    }
    @keyframes psIn { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: none; } }
    .ps-item:hover { background: #f6f8fb; transform: translateX(2px); }
    .ps-item.active {
        background: rgba(153,6,10,.06);
        color: var(--brand); font-weight: 700;
        box-shadow: inset 3px 0 0 var(--brand);
    }
    .ps-num {
        width: 26px; height: 26px; min-width: 26px; border-radius: 8px;
        background: #f1f5f9; color: #64748b;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700;
        transition: background .15s ease, color .15s ease, transform .2s ease;
    }
    .ps-item:hover .ps-num { transform: scale(1.08); }
    .ps-item.active .ps-num { background: var(--brand); color: #fff; }
    .ps-num.done { background: #10b981; color: #fff; }
    .ps-title { line-height: 1.35; flex: 1; }
    .ps-backdrop { display: none; }

    .player-main { flex: 1; min-width: 0; padding: 4px 0 60px 32px; }
    .pm-top { margin-bottom: 28px; max-width: 820px; animation: pmIn .6s cubic-bezier(.22,1,.36,1) both; }
    @keyframes pmIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    .pm-modules-btn {
        display: none; align-items: center; gap: 8px; margin-bottom: 14px;
        border: 1px solid #e2e8f0; background: #fff; color: #334155;
        padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .pm-breadcrumb {
        font-size: 12px; color: #94a3b8; font-weight: 700;
        text-transform: uppercase; letter-spacing: .8px; margin-bottom: 8px;
        display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    }
    .pm-badge {
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(153,6,10,.1); color: var(--brand);
        padding: 2px 9px; border-radius: 999px; font-size: 10.5px; letter-spacing: .5px;
    }
    .pm-badge-done { background: #d1fae5; color: #047857; }
    .pm-title { font-size: 28px; font-weight: 800; color: #0f172a; letter-spacing: -.3px; line-height: 1.25; }
    .pm-desc { font-size: 14.5px; color: #64748b; margin-top: 10px; line-height: 1.6; max-width: 640px; }
    .pm-content {
        padding: 38px 40px; max-width: 820px;
        border-radius: 18px;
        box-shadow: 0 1px 3px rgba(16,24,40,.05), 0 12px 40px rgba(16,24,40,.06);
        animation: pmIn .7s cubic-bezier(.22,1,.36,1) both .08s;
    }
    .pm-content:hover {
        box-shadow: 0 1px 3px rgba(16,24,40,.05), 0 12px 40px rgba(16,24,40,.06);
        transform: none;
    }

    .pm-complete {
        margin-top: 16px; padding-top: 26px; border-top: 1px solid #eef1f5;
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    }

    .pm-prevnext { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 20px; max-width: 820px; }
    .pn-form { display: flex; }
    .pn-card {
        display: inline-flex; align-items: center; gap: 6px; max-width: 100%;
        padding: 7px 14px; border-radius: 999px; overflow: hidden;
        border: 1px solid #e2e8f0; background: #fff;
        font: inherit; text-align: left; cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, opacity .2s ease;
    }
    .pn-card:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,24,40,.08); border-color: var(--brand); }
    .pn-primary { background: var(--brand); border-color: var(--brand); }
    .pn-primary:hover { background: var(--brand); box-shadow: 0 6px 16px rgba(153,6,10,.28); }
    .pn-primary.is-loading { opacity: .75; pointer-events: none; }
    .pn-dir { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #94a3b8; white-space: nowrap; }
    .pn-primary .pn-dir { color: rgba(255,255,255,.8); }
    .pn-title { font-size: 13px; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pn-primary .pn-title { color: #fff; }

    /* Leaving the page after "Next" — content eases out while the request runs */
    body.is-leaving .pm-content, body.is-leaving .pm-top {
        opacity: .4; transform: translateY(-6px);
        transition: opacity .25s ease, transform .25s ease;
    }

    @media (max-width: 900px) {
        .player-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 1100;
            transform: translateX(-105%); transition: transform .3s cubic-bezier(.22,1,.36,1);
            box-shadow: 12px 0 40px rgba(16,24,40,.18);
        }
        body.drawer-open .player-sidebar { transform: none; }
        .ps-backdrop {
            display: block; position: fixed; inset: 0; z-index: 1090;
            background: rgba(15,23,42,.45); opacity: 0; pointer-events: none; transition: opacity .3s ease;
        }
        body.drawer-open .ps-backdrop { opacity: 1; pointer-events: auto; }
        .ps-close { display: inline-block; }
        .pm-modules-btn { display: inline-flex; }
        .player-main { padding: 8px 0 60px; }
        .pm-title { font-size: 23px; }
        .pm-content { padding: 24px 18px; }
        .pm-prevnext { flex-direction: column-reverse; align-items: stretch; }
        .pn-card, .pn-form { max-width: none; width: 100%; }
        .pn-card { justify-content: center; }
        .block-image-aside { flex-direction: column; }
    }

    @media (prefers-reduced-motion: reduce) {
        .pm-top, .pm-content, .ps-item, .ps-progress .progress-fill { animation: none; }
        body.is-leaving .pm-content, body.is-leaving .pm-top { transform: none; }
    }
</style>

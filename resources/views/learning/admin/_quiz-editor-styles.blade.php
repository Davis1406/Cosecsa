<style>
    .qe-fields { padding: 18px; max-height: calc(100vh - 320px); overflow-y: auto; }
    .qe-muted { font-weight: 400; color: #64748b; font-size: 11.5px; text-transform: none; letter-spacing: 0; }
    .qe-input { font-size: 14px; line-height: 1.5; }
    .qe-answers { display: flex; flex-direction: column; gap: 10px; margin-bottom: 10px; }
    .qe-answer { display: flex; align-items: stretch; gap: 10px; }
    .qe-answer > textarea { flex: 1; min-width: 0; }
    .qe-correct {
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
        min-width: 78px; max-width: 78px; margin: 0; padding: 8px 6px;
        border: 1.5px solid #cbd5e1; border-radius: 10px; cursor: pointer;
        font-size: 11px; font-weight: 700; color: #475569; background: #f8fafc;
        transition: border-color .15s ease, background .15s ease, color .15s ease;
    }
    .qe-correct input { accent-color: var(--brand); margin: 0; cursor: pointer; }
    .qe-correct:hover { border-color: #94a3b8; }
    .qe-correct:has(input:checked) { border-color: var(--brand); background: rgba(153,6,10,.06); color: var(--brand); }
    .qe-correct:has(input:focus-visible) { box-shadow: 0 0 0 3px rgba(153,6,10,.18); }
    .qe-remove { display: inline-flex; align-items: center; align-self: stretch; white-space: nowrap; }
    .qe-add { margin-top: 2px; }
    @media (max-width: 560px) {
        .qe-answer { flex-wrap: wrap; }
        .qe-answer > textarea { flex: 1 1 100%; order: 2; }
        .qe-correct { order: 1; }
        .qe-remove { order: 3; }
    }
</style>
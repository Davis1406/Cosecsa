{{-- "Check before you submit": before marks are sent, a popup says which group, station and candidate they are for,
     so a wrong group or station is caught before it is saved. Used by the MCS, General Surgery and FCS marking forms. --}}
<script>
(function () {
    var form = document.getElementById('msform');
    if (!form) return;

    function chosen(name) {
        var sel = form.querySelector('select[name="' + name + '"]');
        if (!sel || !sel.value) return '';
        var o = sel.options[sel.selectedIndex];
        return o ? o.textContent.replace(/\s+/g, ' ').trim() : '';
    }

    // Capture phase on the document: runs before the offline script's own submit handler on the form
    document.addEventListener('submit', function (event) {
        if (event.target !== form) return;
        if (form.dataset.confirmed === '1') { form.dataset.confirmed = ''; return; }   // the examiner said yes: let it go through

        event.preventDefault();
        event.stopImmediatePropagation();

        var kind = (form.querySelector('input[name="form_type"]') || {}).value;
        var lines = ['You are submitting marks for:', '', 'Group: ' + (chosen('group_id') || '—'), 'Station: ' + (chosen('station_id') || '—')];
        var cand = chosen('candidate_id'); if (cand) lines.push('Candidate: ' + cand);
        if (kind) lines.push('Form: ' + kind.charAt(0).toUpperCase() + kind.slice(1));
        lines.push('', 'If this is not correct, choose Go back and select the right group and station.');
        var message = lines.join('\n');

        var go = function () { form.dataset.confirmed = '1'; if (form.requestSubmit) form.requestSubmit(); else form.submit(); };
        if (window.CosecsaAlert && CosecsaAlert.confirm) {
            CosecsaAlert.confirm(message, { variant: 'question', title: 'Check before you submit', confirmText: 'Yes, submit the marks', cancelText: 'Go back' }).then(function (ok) { if (ok) go(); });
        } else if (window.confirm(message)) {
            go();
        }
    }, true);
})();
</script>

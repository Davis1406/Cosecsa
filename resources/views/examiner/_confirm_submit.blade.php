{{-- "Check before you submit": before marks are sent, a popup shows the group, candidate and station they are for and lets the
     examiner correct any of them right there (a different group loads that group's candidates). Used by the MCS, General Surgery
     and FCS marking forms. The form carries data-candidates-url (the endpoint that lists a group's candidates). --}}
<style>
    .cs-confirm-fields { text-align: left; margin: 14px 0 4px; }
    .cs-confirm-fields label { display: block; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #6b6b6b; margin: 10px 0 3px; }
    .cs-confirm-fields select { width: 100%; }
    .cs-confirm-note { font-size: .82rem; color: #a02626; min-height: 1.1em; margin-top: 6px; }
    .cs-alert-btn[disabled] { opacity: .5; cursor: not-allowed; }
</style>
<script>
(function () {
    var form = document.getElementById('msform');
    if (!form) return;
    var base = form.getAttribute('data-candidates-url') || '';

    function sel(name) { return form.querySelector('select[name="' + name + '"]'); }
    function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function optionsOf(s, chosen) {
        return Array.prototype.filter.call(s ? s.options : [], function (o) { return o.value; })
            .map(function (o) { return '<option value="' + esc(o.value) + '"' + (o.value === chosen ? ' selected' : '') + '>' + esc(o.textContent.replace(/\s+/g, ' ').trim()) + '</option>'; }).join('');
    }
    function apply(name, value, text) {   // put the chosen value on the form's own select (no change event: that would reset the form)
        var s = sel(name); if (!s || !value) return;
        if (!Array.prototype.some.call(s.options, function (o) { return o.value === value; })) {
            var o = document.createElement('option'); o.value = value; o.textContent = text; s.appendChild(o);
        }
        s.value = value;
    }

    function ask(onYes) {
        var g = sel('group_id'), c = sel('candidate_id'), st = sel('station_id');
        var kind = (form.querySelector('input[name="form_type"]') || {}).value;

        var root = document.createElement('div');
        root.className = 'cs-alert-root cs-open'; root.setAttribute('role', 'dialog'); root.setAttribute('aria-modal', 'true');
        root.innerHTML =
            '<div class="cs-alert-backdrop"></div><div class="cs-alert-dialog"><div class="cs-alert-body">' +
              '<div class="cs-alert-icon cs-question"><i class="fas fa-question-circle"></i></div>' +
              '<h5 class="cs-alert-title">Check before you submit</h5>' +
              '<p class="cs-alert-msg">You are submitting marks for the group, candidate and station below' + (kind ? ' (' + esc(kind.charAt(0).toUpperCase() + kind.slice(1)) + ' form)' : '') + '.<br>If anything is not correct, change it here.</p>' +
              '<div class="cs-confirm-fields">' +
                (g ? '<label>Group</label><select class="form-control" data-f="group">' + optionsOf(g, g.value) + '</select>' : '') +
                (c ? '<label>Candidate</label><select class="form-control" data-f="cand">' + optionsOf(c, c.value) + '</select>' : '') +
                (st ? '<label>Station</label><select class="form-control" data-f="station">' + optionsOf(st, st.value) + '</select>' : '') +
              '</div><div class="cs-confirm-note" data-f="note"></div>' +
            '</div><div class="cs-alert-footer"><button type="button" class="cs-alert-btn cs-alert-btn-cancel">Go back</button>' +
            '<button type="button" class="cs-alert-btn cs-alert-btn-ok">Yes, submit the marks</button></div></div>';
        document.body.appendChild(root);
        document.documentElement.style.overflow = 'hidden';

        var $g = root.querySelector('[data-f=group]'), $c = root.querySelector('[data-f=cand]'), $s = root.querySelector('[data-f=station]');
        var note = root.querySelector('[data-f=note]'), ok = root.querySelector('.cs-alert-btn-ok'), cancel = root.querySelector('.cs-alert-btn-cancel');
        var loading = false, groupAtStart = g ? g.value : '';

        function ready() { ok.disabled = loading || ($g && !$g.value) || ($c && !$c.value) || ($s && !$s.value); }
        function close(result) {
            document.removeEventListener('keydown', onKey, true);
            root.parentNode && root.parentNode.removeChild(root);
            document.documentElement.style.overflow = '';
            if (result) onYes();
        }
        function onKey(e) {
            if (e.key === 'Escape') { e.preventDefault(); close(false); }
            else if (e.key === 'Enter' && e.target.tagName !== 'SELECT' && !ok.disabled) { e.preventDefault(); close(true); }
        }
        document.addEventListener('keydown', onKey, true);
        cancel.addEventListener('click', function () { close(false); });
        root.querySelector('.cs-alert-backdrop').addEventListener('click', function () { close(false); });

        ok.addEventListener('click', function () {
            if (ok.disabled) return;
            // hand the corrections to the form itself
            if ($g) apply('group_id', $g.value, $g.options[$g.selectedIndex].text);
            if ($c) apply('candidate_id', $c.value, $c.options[$c.selectedIndex].text);
            if ($s) apply('station_id', $s.value, $s.options[$s.selectedIndex].text);
            close(true);
        });

        if ($g && $c) $g.addEventListener('change', function () {
            // a different group means a different list of candidates: load it and ask for the candidate again
            if (!base) { note.textContent = 'This group cannot be changed here. Choose Go back and pick the group on the form.'; $g.value = groupAtStart; return; }
            loading = true; ready(); note.textContent = 'Loading the candidates of this group…';
            $c.innerHTML = '<option value="">Loading…</option>';
            fetch(base + encodeURIComponent($g.value), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (res) {
                    if (!res.ok || !Array.isArray(res.d)) throw new Error((res.d && res.d.message) || 'Could not load the candidates.');
                    $c.innerHTML = '<option value="">Choose the candidate…</option>' + res.d.map(function (x) {
                        return '<option value="' + esc(x.candidates_id) + '">' + esc(x.candidate_id || x.name || ('#' + x.candidates_id)) + '</option>';
                    }).join('');
                    note.textContent = res.d.length ? 'Now choose the candidate you are marking.' : 'There are no candidates in this group.';
                })
                .catch(function (err) {
                    $g.value = groupAtStart; $c.innerHTML = optionsOf(c, c.value);
                    note.textContent = (navigator.onLine ? err.message : 'No connection: the group cannot be changed now.') + ' The group was put back.';
                })
                .then(function () { loading = false; ready(); });
        });
        [$c, $s].forEach(function (x) { if (x) x.addEventListener('change', function () { note.textContent = ''; ready(); }); });

        ready();
        (ok.disabled ? ($g || cancel) : ok).focus();
    }

    // Capture phase on the document: runs before the offline script's own submit handler on the form
    document.addEventListener('submit', function (event) {
        if (event.target !== form) return;
        if (form.dataset.confirmed === '1') { form.dataset.confirmed = ''; return; }   // the examiner said yes: let it go through

        event.preventDefault();
        event.stopImmediatePropagation();
        ask(function () { form.dataset.confirmed = '1'; if (form.requestSubmit) form.requestSubmit(); else form.submit(); });
    }, true);
})();
</script>

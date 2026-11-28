{{-- "Send this letter again": asks where to send it and which date it carries, then composes it from the person's CURRENT
     details and e-mails it. Open it with window.openLetterResend(sentLetterId, defaultEmail, onDone, date);
     without a date the letter is dated today. --}}
<div class="modal fade" id="letterResendModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-paper-plane mr-1"></i> Send this letter again</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3">The letter is made again from the details on the profile as they are now, so a correction you made there is in the new letter.</p>
        <div class="form-group">
          <label for="resendTo" class="font-weight-bold small mb-1">Send to</label>
          <input type="email" id="resendTo" class="form-control" placeholder="E-mail address">
          <small class="text-muted">Prefilled with the e-mail on the profile; change it to send somewhere else.</small>
        </div>
        <div class="form-group mb-0">
          <label for="resendDate" class="font-weight-bold small mb-1">Date on the letter</label>
          <input type="date" id="resendDate" class="form-control" style="max-width:200px;">
        </div>
        <div id="resendMsg" class="small mt-3" style="min-height:1.2em;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-cosecsa" id="resendGo"><i class="fas fa-paper-plane mr-1"></i> Send letter</button>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  var current = { id: null, done: null };
  window.openLetterResend = function (id, email, onDone, date) {
    current = { id: id, done: onDone || null };
    document.getElementById('resendTo').value = email || '';
    document.getElementById('resendDate').value = date || new Date().toISOString().slice(0, 10);
    document.getElementById('resendMsg').textContent = '';
    $('#letterResendModal').modal('show');
  };
  document.getElementById('resendGo').addEventListener('click', function () {
    var btn = this, msg = document.getElementById('resendMsg');
    var to = document.getElementById('resendTo').value.trim();
    if (!to) { msg.className = 'small mt-3 text-danger'; msg.textContent = 'Type the e-mail address to send to.'; return; }
    btn.disabled = true; msg.className = 'small mt-3 text-muted'; msg.textContent = 'Sending…';
    var body = new FormData();
    body.append('to', to); body.append('letter_date', document.getElementById('resendDate').value);
    fetch(@json(url('admin/letters/sent')) + '/' + current.id + '/resend', {
      method: 'POST', body: body, credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' }
    }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        btn.disabled = false;
        msg.className = 'small mt-3 ' + (res.ok ? 'text-success' : 'text-danger');
        msg.textContent = (res.d && res.d.message) || (res.ok ? 'Sent.' : 'The letter could not be sent.');
        if (res.ok) { if (current.done) current.done(); setTimeout(function () { $('#letterResendModal').modal('hide'); }, 1400); }
      })
      .catch(function () { btn.disabled = false; msg.className = 'small mt-3 text-danger'; msg.textContent = 'No connection. Nothing was sent.'; });
  });
})();
</script>

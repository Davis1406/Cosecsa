{{-- Write on the sent letter's PDF. Open with window.openLetterWrite(). The finished PDF is sent to the API,
     which keeps it beside the original and serves it for downloads and re-sends. --}}
<div class="modal fade" id="letterWriteModal" tabindex="-1" role="dialog" aria-labelledby="letterWriteTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document" style="max-width:1180px;">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="letterWriteTitle"><i class="fas fa-pen mr-1"></i> Write on this letter</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body p-2">
        <div class="alert alert-light py-1 px-2 small mb-2">
          Draw on the PDF with the <strong>Pen</strong>, or type words and click where they should go. <strong>Save writing</strong> puts your writing on the PDF of this letter; the PDF as it was e-mailed is still kept.
        </div>
        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
          <div class="btn-group btn-group-sm" role="group" aria-label="Writing tool">
            <button type="button" class="btn btn-outline-primary active lw-tool" data-tool="pen"><i class="fas fa-pen mr-1"></i> Pen</button>
            <button type="button" class="btn btn-outline-primary lw-tool" data-tool="text"><i class="fas fa-font mr-1"></i> Text</button>
          </div>
          <label class="small mb-0">Colour <input type="color" id="lwColor" value="#c62828" style="width:34px;height:30px;border:0;background:none;vertical-align:middle;"></label>
          <label class="small mb-0">Size
            <select id="lwSize" class="form-control form-control-sm d-inline-block ml-1" style="width:72px;">
              <option value="2">2</option><option value="3" selected>3</option><option value="5">5</option>
              <option value="8">8</option><option value="12">12</option><option value="18">18</option>
            </select>
          </label>
          <input type="text" id="lwText" class="form-control form-control-sm" style="width:200px;" placeholder="Words to put on the PDF">
          <div class="btn-group btn-group-sm ml-auto" role="group" aria-label="Pages">
            <button type="button" class="btn btn-outline-secondary" id="lwPrev" disabled>&lsaquo; Previous</button>
            <span class="btn btn-light disabled" id="lwPageNo">1 / 1</span>
            <button type="button" class="btn btn-outline-secondary" id="lwNext" disabled>Next &rsaquo;</button>
          </div>
        </div>
        <div class="d-flex flex-wrap align-items-center mt-2" style="gap:.4rem;">
          <button type="button" class="btn btn-sm btn-outline-secondary" id="lwUndo"><i class="fas fa-undo mr-1"></i> Undo</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="lwClear"><i class="fas fa-eraser mr-1"></i> Clear this page</button>
          <span id="lwMsg" class="small text-muted ml-2" style="min-height:1.2em;"></span>
        </div>
        <div id="lwStage" class="lw-stage mt-2">
          <div id="lwLoading" class="text-center text-muted py-5 small">Opening the PDF…</div>
          <div id="lwPage" class="lw-page d-none">
            <canvas id="lwBase"></canvas>
            <canvas id="lwInk"></canvas>
          </div>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-cosecsa" id="lwSave" disabled><i class="fas fa-save mr-1"></i> Save writing</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  .lw-tool.active { background:#a02626; border-color:#a02626; color:#fff; }
  .lw-stage { max-height:64vh; overflow:auto; background:#e9ecef; border:1px solid #ced4da; text-align:center; }
  .lw-page { position:relative; display:inline-block; margin:12px auto; background:#fff; box-shadow:0 1px 6px rgba(0,0,0,.2); }
  .lw-page canvas { display:block; }
  #lwInk { position:absolute; left:0; top:0; cursor:crosshair; touch-action:none; }
  body.dark-mode .lw-stage { background:#2d3748; border-color:#4a5568; }
  body.dark-mode .alert-light { background:#3b4658; color:#e2e8f0; border-color:#4a5568; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
(function () {
  var sourceUrl = @json(url('admin/letters/sent/' . $id . '/pdf-source'));
  var saveUrl = @json(url('admin/letters/sent/' . $id . '/pdf'));
  var csrf = @json(csrf_token());
  var VIEW_SCALE = 1.6, EXPORT_SCALE = 2;
  var pdfBytes = null, pdf = null, page = 1, tool = 'pen', activeStroke = null;
  var marks = {}; // page number -> pen strokes and typed words, in page coordinates (0..1)
  var base = document.getElementById('lwBase'), ink = document.getElementById('lwInk');
  var pageBox = document.getElementById('lwPage'), loading = document.getElementById('lwLoading');
  var msg = document.getElementById('lwMsg'), saveBtn = document.getElementById('lwSave');

  function say(text, bad) {
    msg.className = 'small ml-2 ' + (bad ? 'text-danger' : 'text-muted');
    msg.textContent = text;
  }

  function hasMarks() {
    return Object.keys(marks).some(function (n) { return marks[n] && marks[n].length; });
  }

  function point(e) {
    var r = ink.getBoundingClientRect();
    return {
      x: Math.min(1, Math.max(0, (e.clientX - r.left) / r.width)),
      y: Math.min(1, Math.max(0, (e.clientY - r.top) / r.height))
    };
  }

  function drawMark(ctx, item, w, h, factor) {
    ctx.save();
    if (item.type === 'text') {
      ctx.fillStyle = item.color;
      ctx.font = Math.max(6, item.size * factor) + 'px Arial, sans-serif';
      ctx.textBaseline = 'top';
      ctx.fillText(item.text, item.x * w, item.y * h);
    } else {
      ctx.strokeStyle = item.color;
      ctx.lineWidth = Math.max(1, item.width * factor);
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.beginPath();
      item.points.forEach(function (p, i) {
        if (i) ctx.lineTo(p.x * w, p.y * h); else ctx.moveTo(p.x * w, p.y * h);
      });
      ctx.stroke();
    }
    ctx.restore();
  }

  function redraw() {
    var ctx = ink.getContext('2d');
    ctx.clearRect(0, 0, ink.width, ink.height);
    (marks[page] || []).forEach(function (item) { drawMark(ctx, item, ink.width, ink.height, VIEW_SCALE); });
    saveBtn.disabled = !hasMarks();
  }

  function showPage(n) {
    if (!pdf || n < 1 || n > pdf.numPages) return;
    page = n;
    document.getElementById('lwPageNo').textContent = n + ' / ' + pdf.numPages;
    document.getElementById('lwPrev').disabled = n === 1;
    document.getElementById('lwNext').disabled = n === pdf.numPages;
    pdf.getPage(n).then(function (p) {
      var viewport = p.getViewport({ scale: VIEW_SCALE });
      base.width = ink.width = Math.round(viewport.width);
      base.height = ink.height = Math.round(viewport.height);
      base.style.width = ink.style.width = viewport.width + 'px';
      base.style.height = ink.style.height = viewport.height + 'px';
      pageBox.style.width = viewport.width + 'px';
      pageBox.style.height = viewport.height + 'px';
      return p.render({ canvasContext: base.getContext('2d'), viewport: viewport }).promise;
    }).then(redraw).catch(function () { say('That page could not be shown.', true); });
  }

  function load() {
    if (pdf) return;
    if (!window.pdfjsLib || !window.PDFLib) {
      say('The writing tools could not load. Check your connection and try again.', true);
      return;
    }
    say('Opening the PDF…');
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
    fetch(sourceUrl, { credentials: 'same-origin' })
      .then(function (r) { if (!r.ok) throw new Error('The PDF could not be opened.'); return r.arrayBuffer(); })
      .then(function (buf) {
        pdfBytes = new Uint8Array(buf);
        return pdfjsLib.getDocument({ data: pdfBytes.slice() }).promise;
      })
      .then(function (doc) {
        pdf = doc;
        loading.classList.add('d-none');
        pageBox.classList.remove('d-none');
        showPage(1);
        say('Write with the pen, or type words and click where they should go.');
      })
      .catch(function (e) { say(e.message || 'The PDF could not be opened.', true); });
  }

  function pngBytes(url) {
    var raw = atob(url.slice(url.indexOf(',') + 1));
    var out = new Uint8Array(raw.length);
    for (var i = 0; i < raw.length; i++) out[i] = raw.charCodeAt(i);
    return out;
  }

  function makePdf() {
    if (!pdfBytes || !hasMarks()) return Promise.reject(new Error('Write something on the PDF first.'));
    return PDFLib.PDFDocument.load(pdfBytes.slice()).then(function (doc) {
      var pages = doc.getPages();
      var chain = Promise.resolve();
      for (var n = 1; n <= pages.length; n++) {
        (function (n) {
          var items = marks[n] || [];
          if (!items.length) return;
          chain = chain.then(function () {
            var size = pages[n - 1].getSize();
            var c = document.createElement('canvas');
            c.width = Math.round(size.width * EXPORT_SCALE);
            c.height = Math.round(size.height * EXPORT_SCALE);
            var ctx = c.getContext('2d');
            items.forEach(function (item) { drawMark(ctx, item, c.width, c.height, EXPORT_SCALE); });
            return doc.embedPng(pngBytes(c.toDataURL('image/png'))).then(function (img) {
              pages[n - 1].drawImage(img, { x: 0, y: 0, width: size.width, height: size.height });
            });
          });
        })(n);
      }
      return chain.then(function () { return doc.save(); });
    });
  }

  document.querySelectorAll('.lw-tool').forEach(function (btn) {
    btn.addEventListener('click', function () {
      tool = btn.dataset.tool;
      document.querySelectorAll('.lw-tool').forEach(function (b) { b.classList.toggle('active', b === btn); });
      ink.style.cursor = tool === 'text' ? 'copy' : 'crosshair';
    });
  });

  ink.addEventListener('pointerdown', function (e) {
    if (!pdf || (e.button !== 0 && e.pointerType === 'mouse')) return;
    var p = point(e);
    if (tool === 'text') {
      var text = document.getElementById('lwText').value.trim();
      if (!text) { say('Type the words you want to put on the PDF.', true); return; }
      marks[page] = marks[page] || [];
      marks[page].push({ type: 'text', text: text, x: p.x, y: p.y, color: document.getElementById('lwColor').value, size: Number(document.getElementById('lwSize').value) });
      redraw();
      say('Words placed. Click again to put them somewhere else.');
      e.preventDefault();
      return;
    }
    activeStroke = { type: 'stroke', color: document.getElementById('lwColor').value, width: Number(document.getElementById('lwSize').value), points: [p] };
    marks[page] = marks[page] || [];
    marks[page].push(activeStroke);
    if (ink.setPointerCapture) ink.setPointerCapture(e.pointerId);
    redraw();
    e.preventDefault();
  });

  ink.addEventListener('pointermove', function (e) {
    if (!activeStroke) return;
    activeStroke.points.push(point(e));
    redraw();
    e.preventDefault();
  });

  ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (type) {
    ink.addEventListener(type, function () { activeStroke = null; });
  });

  document.getElementById('lwUndo').addEventListener('click', function () {
    if (marks[page] && marks[page].length) { marks[page].pop(); redraw(); }
  });
  document.getElementById('lwClear').addEventListener('click', function () {
    if (marks[page] && marks[page].length && window.confirm('Clear everything written on this page?')) {
      marks[page] = [];
      redraw();
    }
  });
  document.getElementById('lwPrev').addEventListener('click', function () { showPage(page - 1); });
  document.getElementById('lwNext').addEventListener('click', function () { showPage(page + 1); });

  saveBtn.addEventListener('click', function () {
    saveBtn.disabled = true;
    say('Saving the writing…');
    makePdf().then(function (bytes) {
      var body = new FormData();
      body.append('pdf', new Blob([bytes], { type: 'application/pdf' }), 'letter-with-writing.pdf');
      return fetch(saveUrl, {
        method: 'POST', body: body, credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); });
    }).then(function (res) {
      if (!res.ok) {
        saveBtn.disabled = false;
        say((res.d && res.d.message) || 'The writing could not be saved.', true);
        return;
      }
      say('Saved. This letter now carries your writing.', false);
      setTimeout(function () { window.location.reload(); }, 900);
    }).catch(function (e) {
      saveBtn.disabled = false;
      say(e.message || 'The writing could not be saved.', true);
    });
  });

  window.openLetterWrite = function () {
    $('#letterWriteModal').modal('show');
    load();
  };
})();
</script>
@endpush

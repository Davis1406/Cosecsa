/* COSECSA examiner offline marking.
 *
 * Loaded on the examiner dashboard and marking forms (see
 * resources/views/examiner/_offline.blade.php). It:
 *
 *   1. registers /examiner-sw.js so the marking pages and candidate lists
 *      stay available without a connection;
 *   2. "prepares" the device by fetching those pages/lists while online;
 *   3. takes over submit on forms marked data-offline-submit: online it posts
 *      the mark as JSON, offline (or if the request cannot reach the server)
 *      it queues the mark in IndexedDB. Forms that edit an existing mark add
 *      data-offline-kind="update" (the latest queued edit of a mark replaces
 *      an earlier one) and data-offline-redirect (where to go afterwards);
 *   4. syncs queued marks, in order, once the connection and session are back;
 *   4b. the results page saves the View / Resubmit pages it links to every time it
 *      is opened online, so a result can be reviewed offline even if it was
 *      submitted after the device was last prepared;
 *   5. on the results page, marks still waiting on the device can be viewed,
 *      edited and discarded from the "saved on this device" card;
 *   6. forms marked data-requires-online (profile edit: uploads, password) are
 *      not queued; offline, the examiner is told to reconnect first.
 *
 * Syncing is safe to repeat: the marking endpoints reject a second mark for
 * the same candidate/station/format with a "duplicate" status, which the queue
 * treats as already saved. Queued marks belong to the examiner who entered
 * them and are only ever sent from that examiner's own session.
 */
(function () {
  'use strict';

  var cfg = window.EXAMINER_OFFLINE;
  if (!cfg || !cfg.userId) return;

  var DB_NAME = 'cosecsa-examiner';
  var DB_VERSION = 1;
  var STORE = 'queue';
  var SYNC_INTERVAL_MS = 30000;
  var WARM_MAX_AGE_MS = 20 * 60 * 1000;
  var WARM_CONCURRENCY = 3;
  var WARM_KEY = 'examiner-offline-warm-v5'; // bump to make every device re-download the offline pages
  var FLASH_KEY = 'examiner-offline-flash';

  var state = { auth: false, syncing: false, warming: false, warmError: null };
  var ui = {};

  // ── IndexedDB queue ─────────────────────────────────────────────────────

  var dbPromise = null;

  function openDb() {
    if (!dbPromise) {
      dbPromise = new Promise(function (resolve, reject) {
        if (!window.indexedDB) {
          reject(new Error('IndexedDB is not available'));
          return;
        }
        var request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = function () {
          var db = request.result;
          if (!db.objectStoreNames.contains(STORE)) db.createObjectStore(STORE, { keyPath: 'id' });
        };
        request.onsuccess = function () { resolve(request.result); };
        request.onerror = function () { reject(request.error); };
      });
      dbPromise.catch(function () { dbPromise = null; });
    }
    return dbPromise;
  }

  function withStore(mode, action) {
    return openDb().then(function (db) {
      return new Promise(function (resolve, reject) {
        var tx = db.transaction(STORE, mode);
        var result;
        var request = action(tx.objectStore(STORE));
        if (request) request.onsuccess = function () { result = request.result; };
        tx.oncomplete = function () { resolve(result); };
        tx.onabort = tx.onerror = function () { reject(tx.error); };
      });
    });
  }

  function queueAll() {
    return withStore('readonly', function (store) { return store.getAll(); }).then(function (rows) {
      return (rows || [])
        .filter(function (row) { return row.userId === cfg.userId; })
        .sort(function (a, b) { return a.createdAt - b.createdAt; });
    });
  }
  function queuePut(item) { return withStore('readwrite', function (store) { return store.put(item); }); }
  function queueDelete(id) { return withStore('readwrite', function (store) { return store.delete(id); }); }

  // ── Small helpers ───────────────────────────────────────────────────────

  function uuid() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'm-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
  }

  function el(tag, attrs, children) {
    var node = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (key) {
      if (key === 'text') node.textContent = attrs[key];
      else if (key === 'class') node.className = attrs[key];
      else if (key.indexOf('on') === 0) node.addEventListener(key.slice(2), attrs[key]);
      else node.setAttribute(key, attrs[key]);
    });
    (children || []).forEach(function (child) { if (child) node.appendChild(child); });
    return node;
  }

  function plural(n, one, many) { return n + ' ' + (n === 1 ? one : many); }

  function formatTime(ts) {
    try {
      return new Date(ts).toLocaleString([], { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
    } catch (e) { return ''; }
  }

  function setFlash(type, text) {
    try { sessionStorage.setItem(FLASH_KEY, JSON.stringify({ type: type, text: text })); } catch (e) { /* ignore */ }
  }

  // ── Network: token + submission ─────────────────────────────────────────

  var AUTH = { kind: 'auth' };

  /** Fresh CSRF token. Throws AUTH when the session has ended. */
  function fetchToken() {
    return fetch(cfg.tokenUrl, {
      credentials: 'same-origin',
      redirect: 'manual',
      cache: 'no-store',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    }).then(function (response) {
      if (response.type === 'opaqueredirect' || response.status === 401) throw AUTH;
      if (!response.ok) throw new Error('token ' + response.status);
      return response.json().then(function (body) {
        if (!body || !body.token) throw AUTH;
        if (body.user_id !== cfg.userId) throw AUTH; // someone else is signed in now
        return body.token;
      });
    });
  }

  /**
   * POST one queued/new submission. Resolves to
   * { outcome: success|duplicate|invalid|auth|token|retry, message }.
   * Rejects only when the server cannot be reached at all.
   */
  function send(item, token) {
    var body = new FormData();
    item.fields.forEach(function (pair) { body.append(pair[0], pair[1]); });
    body.set('_token', token);

    return fetch(item.url, {
      method: 'POST',
      body: body,
      credentials: 'same-origin',
      redirect: 'manual',
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
    }).then(function (response) {
      if (response.type === 'opaqueredirect' || response.status === 401) return { outcome: 'auth' };
      if (response.status === 419) return { outcome: 'token' };

      return response.json().then(
        function (json) { return json; },
        function () { return null; }
      ).then(function (json) {
        var message = json && json.message ? json.message : '';
        if (response.status >= 500) return { outcome: 'retry', message: message };
        if (!json || !json.status) return { outcome: response.ok ? 'auth' : 'retry', message: message };
        if (json.status === 'success') return { outcome: 'success', message: message };
        if (json.status === 'duplicate') return { outcome: 'duplicate', message: message };
        return { outcome: 'invalid', message: message || 'The server rejected this mark.' };
      });
    });
  }

  // ── Building a queue item from a form ───────────────────────────────────

  function fieldValue(form, name) {
    var node = form.elements[name];
    return node && node.value != null ? String(node.value) : '';
  }

  function selectedText(form, name) {
    var node = form.elements[name];
    if (!node || !node.options || node.selectedIndex < 0) return '';
    return (node.options[node.selectedIndex].text || '').trim();
  }

  function buildItem(form) {
    var fields = [];
    new FormData(form).forEach(function (value, key) {
      if (typeof value === 'string' && key !== '_token') fields.push([key, value]);
    });

    var heading = document.querySelector('.tittle h2');
    var kind = form.getAttribute('data-offline-kind') === 'update' ? 'update' : 'create';
    return {
      id: uuid(),
      userId: cfg.userId,
      kind: kind,
      redirect: form.getAttribute('data-offline-redirect') || '',
      url: form.action,
      fields: fields,
      createdAt: Date.now(),
      status: 'pending',
      error: '',
      key: [kind, form.action, fieldValue(form, 'candidate_id'), fieldValue(form, 'station_id'),
            fieldValue(form, 'form_type'), fieldValue(form, 'exam_format'), fieldValue(form, 'exam_type')].join('|'),
      label: {
        exam: heading ? heading.textContent.replace(/\s+/g, ' ').trim() : 'Evaluation',
        // New marks pick these from dropdowns; edit forms show them as read-only text.
        group: selectedText(form, 'group_id') || fieldValue(form, 'group_id_display'),
        candidate: selectedText(form, 'candidate_id') || fieldValue(form, 'candidate_id_display'),
        station: selectedText(form, 'station_id') || (fieldValue(form, 'station_id') ? 'Station ' + fieldValue(form, 'station_id') : ''),
      },
    };
  }

  // ── Submitting ──────────────────────────────────────────────────────────

  function enqueue(item) {
    return queueAll().then(function (items) {
      var same = items.filter(function (other) { return other.key === item.key; });

      if (item.kind === 'update') {
        // The latest edit of a mark wins: drop any earlier queued edit of it.
        return Promise.all(same.map(function (other) { return queueDelete(other.id); })).then(function () {
          return queuePut(item);
        }).then(function () { return 'queued'; });
      }

      if (same.length) return 'exists';
      return queuePut(item).then(function () { return 'queued'; });
    });
  }

  function onSubmit(event, form) {
    if (event.defaultPrevented) return; // the form's own validation refused it
    event.preventDefault();
    if (form.getAttribute('data-offline-busy')) return;

    var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    var setBusy = function (busy) {
      if (busy) form.setAttribute('data-offline-busy', '1'); else form.removeAttribute('data-offline-busy');
      buttons.forEach(function (button) { button.disabled = busy; });
    };

    setBusy(true);
    var item = buildItem(form);

    var queueIt = function (reason) {
      return enqueue(item).then(function (result) {
        if (result === 'exists') {
          toast('error', 'This candidate and station are already saved on this device, waiting to sync.');
          setBusy(false);
          return;
        }
        var text = reason === 'auth'
          ? 'Saved on this device. Sign in again to submit it.'
          : 'Saved on this device. It will be submitted automatically when you are back online.';
        finishAndReset(form, 'warning', text, reason !== 'auth', item.redirect);
      }, function () {
        toast('error', 'Could not save this mark on this device. Reconnect before submitting.');
        setBusy(false);
      });
    };

    if (!navigator.onLine) {
      queueIt('offline');
      return;
    }

    submitNow(item, fieldValue(form, '_token')).then(function (result) {
      if (result.outcome === 'success') {
        finishAndReset(form, 'success', result.message || 'Evaluation submitted successfully.', true, item.redirect);
      } else if (result.outcome === 'duplicate' || result.outcome === 'invalid') {
        toast('error', result.message);
        setBusy(false);
      } else {
        return queueIt(result.outcome);
      }
    }, function () {
      // Could not reach the server even though the browser says it is online.
      return queueIt('offline');
    });
  }

  /** Post now, retrying once with a fresh token if the page's token is stale. */
  function submitNow(item, pageToken) {
    return send(item, pageToken).then(function (result) {
      if (result.outcome !== 'token') return result;
      return fetchToken().then(
        function (token) { return send(item, token); },
        function (error) { if (error === AUTH) return { outcome: 'auth' }; throw error; }
      );
    });
  }

  /** Show a message and give the examiner a clean form, like the old redirect did. */
  function finishAndReset(form, type, text, reload, redirect) {
    refreshStatus();
    if (reload !== false && navigator.serviceWorker && navigator.serviceWorker.controller) {
      setFlash(type, text);
      if (redirect) window.location.assign(redirect); else window.location.reload();
      return;
    }
    if (reload !== false && redirect && navigator.onLine) {
      setFlash(type, text);
      window.location.assign(redirect);
      return;
    }
    // Reloading is not possible (no cached copy offline) or not wanted (session ended): reset in place.
    form.removeAttribute('data-offline-busy');
    form.reset();
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (b) { b.disabled = false; });
    toast(type, text);
  }

  // ── Syncing ─────────────────────────────────────────────────────────────

  /** Send everything waiting, oldest first. Resolves to { synced, duplicates, failed }. */
  function syncQueue() {
    var summary = { synced: 0, duplicates: 0, failed: 0 };
    if (state.syncing || !navigator.onLine) return Promise.resolve(summary);

    var run = function () {
      state.syncing = true;
      refreshStatus();
      return queueAll().then(function (items) {
        var pending = items.filter(function (item) { return item.status === 'pending'; });
        if (!pending.length) return summary;

        return fetchToken().then(function (token) {
          state.auth = false;
          return pending.reduce(function (chain, item) {
            return chain.then(function () {
              return send(item, token).then(function (result) {
                if (result.outcome !== 'token') return result;
                return fetchToken().then(function (fresh) { token = fresh; return send(item, token); });
              }).then(function (result) {
                switch (result.outcome) {
                  case 'success': summary.synced++; return queueDelete(item.id);
                  case 'duplicate': summary.duplicates++; return queueDelete(item.id);
                  case 'invalid':
                    summary.failed++;
                    item.status = 'failed';
                    item.error = result.message;
                    return queuePut(item);
                  case 'auth':
                    state.auth = true;
                    throw AUTH;
                  default:
                    throw new Error('retry later'); // server error: keep it queued, stop here
                }
              });
            });
          }, Promise.resolve());
        }).then(function () { return summary; });
      }).catch(function (error) {
        if (error === AUTH) state.auth = true;
        return summary; // network trouble: leave the queue as is, try again later
      }).then(function (result) {
        state.syncing = false;
        refreshStatus();
        if (result.synced || result.duplicates) {
          toast('success', plural(result.synced + result.duplicates, 'mark', 'marks') + ' synced.');
          // The results page lists what the server holds: reload it to include what just synced.
          if (document.getElementById('examiner-offline-pending')) setTimeout(function () { window.location.reload(); }, 1200);
        }
        if (result.failed) {
          toast('error', plural(result.failed, 'mark was', 'marks were') + ' rejected by the server. Open the sync badge for details.');
        }
        return result;
      });
    };

    // One tab at a time; the server also rejects repeats, this just avoids the noise.
    if (navigator.locks && navigator.locks.request) {
      return navigator.locks.request('examiner-sync', { ifAvailable: true }, function (lock) {
        return lock ? run() : summary;
      });
    }
    return run();
  }

  // ── Preparing the device (service worker + cached pages) ────────────────

  function readWarm() {
    try { return JSON.parse(localStorage.getItem(WARM_KEY) || 'null'); } catch (e) { return null; }
  }
  function writeWarm(value) {
    try { localStorage.setItem(WARM_KEY, JSON.stringify(value)); } catch (e) { /* ignore */ }
  }

  function registerWorker() {
    if (!('serviceWorker' in navigator)) return Promise.resolve(null);
    return navigator.serviceWorker.register(cfg.swUrl, { scope: cfg.scope }).then(function () {
      return navigator.serviceWorker.ready;
    }).then(function (registration) {
      if (registration.active) registration.active.postMessage({ type: 'session', userId: cfg.userId });
      return registration;
    }).catch(function () { return null; });
  }

  function waitForController(timeoutMs) {
    if (!('serviceWorker' in navigator)) return Promise.resolve(false);
    if (navigator.serviceWorker.controller) return Promise.resolve(true);
    return new Promise(function (resolve) {
      var done = function (value) { resolve(value); };
      var timer = setTimeout(function () { done(!!navigator.serviceWorker.controller); }, timeoutMs);
      navigator.serviceWorker.addEventListener('controllerchange', function () { clearTimeout(timer); done(true); }, { once: true });
    });
  }

  /** Download the marking pages and candidate lists so they work offline. */
  function prepareOffline(force) {
    if (!('serviceWorker' in navigator) || !navigator.onLine || state.warming) return Promise.resolve();

    var last = readWarm();
    if (!force && last && last.userId === cfg.userId && Date.now() - last.at < WARM_MAX_AGE_MS) return Promise.resolve();

    state.warming = true;
    state.warmError = null;
    refreshStatus();

    return waitForController(5000).then(function (controlled) {
      if (!controlled) throw new Error('The offline helper is not active yet. Reload the page and try again.');
      return fetch(cfg.manifestUrl, { credentials: 'same-origin', cache: 'no-store', redirect: 'manual', headers: { Accept: 'application/json' } });
    }).then(function (response) {
      if (response.type === 'opaqueredirect' || !response.ok) throw new Error('Could not read the list of pages to download.');
      return response.json();
    }).then(function (manifest) {
      var urls = (manifest.pages || []).concat(manifest.data || []);
      var okCount = 0;
      var next = 0;

      var worker = function () {
        if (next >= urls.length) return Promise.resolve();
        var url = urls[next++];
        return fetch(url, { credentials: 'same-origin' }).then(function (response) {
          if (response.ok && !response.redirected) okCount++;
        }, function () { /* counted as a miss */ }).then(worker);
      };

      var workers = [];
      for (var i = 0; i < WARM_CONCURRENCY; i++) workers.push(worker());
      return Promise.all(workers).then(function () {
        writeWarm({ userId: cfg.userId, at: Date.now(), total: urls.length, ok: okCount });
        if (okCount < urls.length) state.warmError = 'Some pages could not be downloaded (' + okCount + ' of ' + urls.length + ').';
      });
    }).catch(function (error) {
      state.warmError = error && error.message ? error.message : 'Could not prepare for offline use.';
    }).then(function () {
      state.warming = false;
      refreshStatus();
    });
  }

  // ── Results page: keep every linked View / Resubmit page ────────────────

  var RESULT_PAGE = /^\/examiner\/(?:(?:resubmit|view_results)\/\d+\/\d+|(?:fcs-resubmit|view_fcs_results)\/\d+)\/?$/;
  var FCS_SELECTION = /^\/examiner\/fcs-resubmit\/\d+\/?$/;
  var FCS_FORM_LINK = /\/examiner\/fcs-resubmit\/\d+\/(?:clinical|viva)/g;

  /** Links to result pages, including rows on other pages of the DataTable (not in the DOM). */
  function resultPageLinks() {
    var anchors = Array.prototype.slice.call(document.querySelectorAll('a[href]'));

    var jq = window.jQuery;
    if (jq && jq.fn && jq.fn.dataTable) {
      jq('table.dataTable').each(function () {
        try {
          anchors = anchors.concat(jq(this).DataTable().rows().nodes().to$().find('a[href]').toArray());
        } catch (e) { /* not a DataTable */ }
      });
    }

    var urls = {};
    anchors.forEach(function (a) {
      try {
        var url = new URL(a.getAttribute('href'), window.location.href);
        if (url.origin === window.location.origin && RESULT_PAGE.test(url.pathname)) urls[url.href] = true;
      } catch (e) { /* ignore */ }
    });
    return Object.keys(urls);
  }

  function prefetchResultPages() {
    if (!navigator.onLine || !('serviceWorker' in navigator)) return Promise.resolve();

    return waitForController(5000).then(function (controlled) {
      if (!controlled) return;

      var queue = resultPageLinks();
      var seen = {};
      queue.forEach(function (u) { seen[u] = true; });

      var worker = function () {
        if (!queue.length) return Promise.resolve();
        var url = queue.shift();
        return fetch(url, { credentials: 'same-origin' }).then(function (response) {
          // The FCS "resubmit" page links to its Clinical / Viva forms: keep those too.
          if (!response.ok || response.redirected || !FCS_SELECTION.test(new URL(url).pathname)) return;
          return response.text().then(function (html) {
            (html.match(FCS_FORM_LINK) || []).forEach(function (path) {
              var form = new URL(path, window.location.origin).href;
              if (!seen[form]) { seen[form] = true; queue.push(form); }
            });
          });
        }, function () { /* offline again: stop quietly */ }).then(worker);
      };

      return Promise.all([worker(), worker(), worker()]);
    });
  }

  // ── UI ──────────────────────────────────────────────────────────────────

  function injectStyles() {
    var css = [
      '.eo-toasts{position:fixed;top:64px;right:16px;z-index:2100;display:flex;flex-direction:column;gap:8px;max-width:360px}',
      '.eo-toast{padding:10px 14px;border-radius:6px;color:#fff;font-size:.9rem;box-shadow:0 2px 8px rgba(0,0,0,.25)}',
      '.eo-toast.success{background:#2e7d32}.eo-toast.error{background:#c62828}.eo-toast.warning{background:#8a5a00}',
      '.eo-pill{position:fixed;left:16px;bottom:16px;z-index:2000;font-family:inherit}',
      '.eo-pill button.eo-badge{border:0;border-radius:20px;padding:8px 16px;font-size:.85rem;font-weight:600;color:#fff;background:#37474f;box-shadow:0 2px 8px rgba(0,0,0,.3);cursor:pointer}',
      '.eo-pill.pending button.eo-badge{background:#8a5a00}.eo-pill.error button.eo-badge{background:#c62828}',
      '.eo-panel{position:absolute;left:0;bottom:46px;width:340px;max-width:calc(100vw - 32px);max-height:60vh;overflow:auto;background:#fff;color:#251917;border:1px solid #ddd;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.25);padding:12px;font-size:.85rem}',
      '.eo-panel h4{margin:0 0 8px;font-size:.95rem;color:#a02626}',
      '.eo-item{border-top:1px solid #eee;padding:8px 0}.eo-item small{color:#666;display:block}',
      '.eo-item .eo-err{color:#c62828}.eo-actions{display:flex;gap:8px;margin-top:8px}',
      '.eo-btn{border:0;border-radius:4px;padding:5px 12px;background:#FEC503;color:#000;font-weight:600;cursor:pointer}',
      '.eo-btn.link{background:none;color:#a02626;padding:0;text-decoration:underline}',
      '.eo-overlay{position:fixed;inset:0;z-index:2200;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;padding:12px}',
      '.eo-dialog{background:#fff;color:#251917;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,.35);width:100%;max-width:460px;max-height:92vh;overflow:auto;padding:16px;font-size:.9rem}',
      '.eo-dialog h4{margin:0 0 2px;font-size:1.05rem;color:#a02626}.eo-dialog .eo-sub{color:#666;margin-bottom:12px}',
      '.eo-dialog label{display:block;font-weight:600;margin:10px 0 3px;font-size:.82rem}',
      '.eo-dialog input,.eo-dialog select,.eo-dialog textarea{width:100%;padding:6px 8px;border:1px solid #ccc;border-radius:4px;font:inherit}',
      '.eo-dialog .eo-total{font-weight:700;margin-top:10px}.eo-dialog .eo-error{color:#c62828;margin-top:8px}',
      '.eo-dialog .eo-case{font-weight:700;color:#a02626;margin-top:12px}.eo-dialog .eo-row{display:flex;gap:8px;justify-content:flex-end;margin-top:14px}',
      '.eo-btn.sec{background:#e0e0e0}.eo-btn.danger{background:#c62828;color:#fff}.eo-btns{display:flex;gap:6px;flex-wrap:wrap}',
      '.eo-ready{border-left:4px solid #FEC503;background:#fffdf3;border-radius:6px;padding:10px 14px;margin:0 0 16px;text-align:left;font-size:.88rem}',
      '.eo-ready.ok{border-left-color:#2e7d32}.eo-ready.bad{border-left-color:#c62828}',
    ].join('');
    document.head.appendChild(el('style', { text: css }));
  }

  function toast(type, text) {
    if (!text) return;
    if (!ui.toasts) {
      ui.toasts = el('div', { class: 'eo-toasts', role: 'status', 'aria-live': 'polite' });
      document.body.appendChild(ui.toasts);
    }
    var node = el('div', { class: 'eo-toast ' + type, text: text });
    ui.toasts.appendChild(node);
    setTimeout(function () { if (node.parentNode) node.parentNode.removeChild(node); }, type === 'error' ? 9000 : 5000);
  }

  function buildPill() {
    ui.pill = el('div', { class: 'eo-pill', hidden: 'hidden' });
    ui.badge = el('button', { class: 'eo-badge', type: 'button', onclick: function () { togglePanel(); } });
    ui.panel = el('div', { class: 'eo-panel', hidden: 'hidden' });
    ui.pill.appendChild(ui.panel);
    ui.pill.appendChild(ui.badge);
    document.body.appendChild(ui.pill);
  }

  function togglePanel(force) {
    var open = force != null ? force : ui.panel.hasAttribute('hidden');
    if (open) ui.panel.removeAttribute('hidden'); else ui.panel.setAttribute('hidden', 'hidden');
    if (open) renderPanel();
  }

  var lastItems = [];

  function renderPanel() {
    var panel = ui.panel;
    while (panel.firstChild) panel.removeChild(panel.firstChild);
    panel.appendChild(el('h4', { text: 'Marks saved on this device' }));

    if (!lastItems.length) {
      panel.appendChild(el('div', { text: 'Nothing is waiting to sync.' }));
      return;
    }

    if (state.auth) {
      panel.appendChild(el('div', { class: 'eo-err', text: 'Your session has ended. Sign in again while online and these marks will be submitted.' }));
    } else if (!navigator.onLine) {
      panel.appendChild(el('div', { text: 'You are offline. These marks will be submitted when the connection returns.' }));
    }

    lastItems.forEach(function (item) {
      var label = [item.kind === 'update' ? 'Edit' : '', item.label.candidate, item.label.station, item.label.group].filter(Boolean).join(' · ');
      var node = el('div', { class: 'eo-item' }, [
        el('strong', { text: label || 'Evaluation' }),
        el('small', { text: (item.label.exam || '') + ' — ' + formatTime(item.createdAt) }),
      ]);
      if (item.status === 'failed') {
        node.appendChild(el('small', { class: 'eo-err', text: 'Rejected: ' + (item.error || 'unknown reason') + (item.kind === 'update' ? ' Open the mark again and re-apply the edit if still needed.' : ' Re-enter it from the form if still needed.') }));
        node.appendChild(el('button', { class: 'eo-btn link', type: 'button', text: 'Discard', onclick: function () {
          queueDelete(item.id).then(refreshStatus);
        } }));
      }
      panel.appendChild(node);
    });

    var actions = el('div', { class: 'eo-actions' });
    if (navigator.onLine && !state.syncing) {
      actions.appendChild(el('button', { class: 'eo-btn', type: 'button', text: 'Sync now', onclick: function () {
        state.auth = false;
        syncQueue().then(refreshStatus);
      } }));
    }
    panel.appendChild(actions);
  }

  /** Recount the queue and redraw the badge and the dashboard card. */
  function refreshStatus() {
    return queueAll().catch(function () { return []; }).then(function (items) {
      lastItems = items;
      var pending = items.filter(function (i) { return i.status === 'pending'; }).length;
      var failed = items.filter(function (i) { return i.status === 'failed'; }).length;
      var online = navigator.onLine;

      var text = '';
      var mode = '';
      if (failed) { text = plural(failed, 'mark needs', 'marks need') + ' attention'; mode = 'error'; }
      else if (pending && state.auth) { text = 'Sign in again to sync ' + plural(pending, 'mark', 'marks'); mode = 'error'; }
      else if (pending && !online) { text = 'Offline · ' + plural(pending, 'mark', 'marks') + ' waiting'; mode = 'pending'; }
      else if (pending && state.syncing) { text = 'Syncing ' + plural(pending, 'mark', 'marks') + '…'; mode = 'pending'; }
      else if (pending) { text = plural(pending, 'mark', 'marks') + ' waiting to sync'; mode = 'pending'; }
      else if (!online) { text = 'Offline · marks are saved on this device'; mode = ''; }

      if (ui.pill) {
        if (text) {
          ui.badge.textContent = text;
          ui.pill.className = 'eo-pill ' + mode;
          ui.pill.removeAttribute('hidden');
        } else {
          ui.pill.setAttribute('hidden', 'hidden');
          ui.panel.setAttribute('hidden', 'hidden');
        }
        if (!ui.panel.hasAttribute('hidden')) renderPanel();
      }
      renderReadiness();
      renderPendingCard();
    });
  }

  function renderReadiness() {
    var host = document.getElementById('examiner-offline-status');
    if (!host) return;
    while (host.firstChild) host.removeChild(host.firstChild);

    var warm = readWarm();
    var ready = warm && warm.userId === cfg.userId && warm.ok > 0;
    var message;
    var cls = 'eo-ready';

    if (!('serviceWorker' in navigator)) {
      message = 'Offline mode is not available in this browser. Marks can still be saved on this device if the connection drops while a form is open.';
      cls += ' bad';
    } else if (state.warming) {
      message = 'Preparing this device for offline marking…';
    } else if (state.warmError) {
      message = state.warmError;
      cls += ' bad';
    } else if (ready) {
      message = 'Ready for offline marking. Last updated ' + formatTime(warm.at) + '.';
      cls += ' ok';
    } else {
      message = 'Not prepared for offline marking yet.';
    }

    var box = el('div', { class: cls }, [el('span', { text: message })]);
    if ('serviceWorker' in navigator && navigator.onLine && !state.warming) {
      box.appendChild(document.createTextNode(' '));
      box.appendChild(el('button', { class: 'eo-btn link', type: 'button', text: ready ? 'Update now' : 'Prepare now', onclick: function () { prepareOffline(true); } }));
    }
    host.appendChild(box);
  }

  /** On the results page: list the marks still on this device, ahead of the server's results. */
  function renderPendingCard() {
    var host = document.getElementById('examiner-offline-pending');
    if (!host) return;
    while (host.firstChild) host.removeChild(host.firstChild);
    if (!lastItems.length) return;

    var card = el('div', { class: 'eo-ready', style: 'margin-top:20px' }, [
      el('strong', { text: 'Saved on this device, not yet submitted (' + lastItems.length + ')' }),
    ]);
    if (state.auth) card.appendChild(el('div', { class: 'eo-err', style: 'color:#c62828', text: 'Sign in again while online to submit them.' }));
    else if (!navigator.onLine) card.appendChild(el('div', { text: 'They will be submitted automatically when you are back online.' }));

    var table = el('table', { class: 'table table-sm', style: 'margin:8px 0 0;background:#fff' }, [
      el('thead', {}, [el('tr', {}, ['Candidate', 'Station', 'Group', 'Exam', 'Saved', 'Status', 'Actions'].map(function (h) { return el('th', { text: h }); }))]),
    ]);
    var body = el('tbody');
    lastItems.forEach(function (item) {
      body.appendChild(el('tr', {}, [
        el('td', { text: (item.kind === 'update' ? 'Edit · ' : '') + (item.label.candidate || '-') }),
        el('td', { text: item.label.station || '-' }),
        el('td', { text: item.label.group || '-' }),
        el('td', { text: item.label.exam || '-' }),
        el('td', { text: formatTime(item.createdAt) }),
        el('td', { text: item.status === 'failed' ? 'Rejected: ' + (item.error || 'unknown reason') : 'Waiting to sync' }),
        el('td', {}, [el('div', { class: 'eo-btns' }, [
          el('button', { class: 'eo-btn sec', type: 'button', text: 'View', onclick: function () { openItemDialog(item, false); } }),
          el('button', { class: 'eo-btn', type: 'button', text: 'Edit', onclick: function () { openItemDialog(item, true); } }),
          el('button', { class: 'eo-btn danger', type: 'button', text: 'Discard', onclick: function () { discardItem(item); } }),
        ])]),
      ]));
    });
    table.appendChild(body);
    // Wide on a phone: scroll the table inside the card rather than letting it spill over the page.
    card.appendChild(el('div', { style: 'overflow-x:auto;-webkit-overflow-scrolling:touch' }, [table]));
    host.appendChild(card);
  }

  function showStoredFlash() {
    try {
      var raw = sessionStorage.getItem(FLASH_KEY);
      if (!raw) return;
      sessionStorage.removeItem(FLASH_KEY);
      var flash = JSON.parse(raw);
      toast(flash.type, flash.text);
    } catch (e) { /* ignore */ }
  }

  // ── View / edit / discard a mark that is still on the device ────────────

  var MARK_FIELD = /^question_marks(?:_case(\d+))?\[\]$/;
  var GRADES = ['pass', 'borderline', 'fail'];

  /** Pull the editable parts (marks, total, remarks, grade) out of a queued form post. */
  function parseItem(item) {
    var groups = [];
    var byName = {};
    var info = { total: '', remarks: '', gradeName: '', grade: '', format: '' };

    item.fields.forEach(function (pair) {
      var name = pair[0];
      var value = pair[1];
      var match = MARK_FIELD.exec(name);
      if (match) {
        if (!byName[name]) {
          byName[name] = { name: name, caseNo: match[1] || '', values: [] };
          groups.push(byName[name]);
        }
        byName[name].values.push(value);
      } else if (name === 'total_marks') info.total = value;
      else if (name === 'remarks') info.remarks = value;
      else if (name === 'overall' || name === 'grade') { info.gradeName = name; info.grade = value; }
      else if (name === 'form_type' || name === 'exam_format') info.format = value;
    });

    return { groups: groups, info: info, flat: groups.length === 1 && !groups[0].caseNo };
  }

  function closeDialog() {
    var open = document.querySelector('.eo-overlay');
    if (open && open.parentNode) open.parentNode.removeChild(open);
  }

  function sumMarks(inputs) {
    var total = 0;
    inputs.forEach(function (input) { total += parseFloat(input.value) || 0; });
    return Math.round(total * 10) / 10;
  }

  function openItemDialog(item, editable) {
    closeDialog();
    var parsed = parseItem(item);
    var inputs = [];

    var overlay = el('div', { class: 'eo-overlay', onclick: function (event) { if (event.target === overlay) closeDialog(); } });
    var box = el('div', { class: 'eo-dialog', role: 'dialog', 'aria-modal': 'true' });
    overlay.appendChild(box);

    var context = [item.label.candidate, item.label.station, item.label.group, parsed.info.format ? parsed.info.format : ''].filter(Boolean).join(' · ');
    box.appendChild(el('h4', { text: (editable ? 'Edit' : 'View') + ' saved mark' + (item.kind === 'update' ? ' (edit)' : '') }));
    box.appendChild(el('div', { class: 'eo-sub', text: (context || item.label.exam || '') + ' — saved ' + formatTime(item.createdAt) }));
    if (item.status === 'failed') box.appendChild(el('div', { class: 'eo-error', text: 'Rejected by the server: ' + (item.error || 'unknown reason') }));

    var totalNode = el('div', { class: 'eo-total' });
    var errorNode = el('div', { class: 'eo-error' });
    var refreshTotal = function () { totalNode.textContent = 'Total marks: ' + (editable ? sumMarks(inputs) : (parsed.info.total || sumMarks(inputs.length ? inputs : []))); };

    parsed.groups.forEach(function (group) {
      if (group.caseNo) box.appendChild(el('div', { class: 'eo-case', text: 'Case ' + group.caseNo }));
      group.values.forEach(function (value, i) {
        box.appendChild(el('label', { text: 'Question ' + (i + 1) }));
        var input = el('input', { type: 'number', step: '0.5', min: '0', value: value });
        if (!editable) input.setAttribute('readonly', 'readonly');
        input.addEventListener('input', refreshTotal);
        inputs.push(input);
        box.appendChild(input);
      });
    });

    var gradeSelect = null;
    if (parsed.info.gradeName) {
      box.appendChild(el('label', { text: 'Grade' }));
      gradeSelect = el('select');
      (GRADES.indexOf(parsed.info.grade) === -1 ? GRADES.concat([parsed.info.grade]) : GRADES).forEach(function (g) {
        gradeSelect.appendChild(el('option', { value: g, text: g ? g.charAt(0).toUpperCase() + g.slice(1) : '—' }));
      });
      gradeSelect.value = parsed.info.grade;
      if (!editable) gradeSelect.setAttribute('disabled', 'disabled');
      box.appendChild(gradeSelect);
    }

    box.appendChild(el('label', { text: 'Remarks' }));
    var remarks = el('textarea', { rows: '3' });
    remarks.value = parsed.info.remarks;
    if (!editable) remarks.setAttribute('readonly', 'readonly');
    box.appendChild(remarks);

    box.appendChild(totalNode);
    refreshTotal();
    box.appendChild(errorNode);

    var row = el('div', { class: 'eo-row' });
    if (editable) {
      row.appendChild(el('button', { class: 'eo-btn sec', type: 'button', text: 'Cancel', onclick: closeDialog }));
      row.appendChild(el('button', { class: 'eo-btn', type: 'button', text: 'Save changes', onclick: function () {
        saveItemEdit(item, parsed, inputs, remarks, gradeSelect, errorNode);
      } }));
    } else {
      row.appendChild(el('button', { class: 'eo-btn sec', type: 'button', text: 'Close', onclick: closeDialog }));
      row.appendChild(el('button', { class: 'eo-btn', type: 'button', text: 'Edit', onclick: function () { openItemDialog(item, true); } }));
    }
    box.appendChild(row);

    document.body.appendChild(overlay);
  }

  /** Validate the edited values and write them back into the queued post. */
  function saveItemEdit(item, parsed, inputs, remarks, gradeSelect, errorNode) {
    var bad = inputs.some(function (input) { return input.value === '' || isNaN(parseFloat(input.value)) || parseFloat(input.value) < 0; });
    if (bad) { errorNode.textContent = 'Enter a mark of 0 or more for every question.'; return; }

    var total = sumMarks(inputs);
    // MCS and General Surgery (one list of questions) are marked out of 20 per station.
    if (parsed.flat && total > 20) { errorNode.textContent = 'The total marks should not exceed 20 per station.'; return; }

    // The mark may have been submitted since this dialog was opened: re-read it first.
    queueAll().then(function (items) {
      var current = items.filter(function (i) { return i.id === item.id; })[0];
      if (!current) {
        closeDialog();
        toast('warning', 'This mark was just submitted. Use Resubmit on the results list to change it.');
        if (navigator.onLine) setTimeout(function () { window.location.reload(); }, 1500);
        return null;
      }

      var position = {};
      var fields = current.fields.map(function (pair) {
        var name = pair[0];
        if (MARK_FIELD.test(name)) {
          var index = position[name] = (position[name] || 0) + 1;
          var group = parsed.groups.filter(function (g) { return g.name === name; })[0];
          var flatIndex = 0;
          for (var g = 0; g < parsed.groups.length && parsed.groups[g] !== group; g++) flatIndex += parsed.groups[g].values.length;
          return [name, String(parseFloat(inputs[flatIndex + index - 1].value))];
        }
        if (name === 'total_marks') return [name, String(total)];
        if (name === 'remarks') return [name, remarks.value];
        if (gradeSelect && name === parsed.info.gradeName) return [name, gradeSelect.value];
        return pair;
      });

      current.fields = fields;
      current.status = 'pending';
      current.error = '';
      return queuePut(current).then(function () { return true; });
    }).then(function (saved) {
      if (!saved) return;
      closeDialog();
      toast('success', 'Changes saved on this device.' + (navigator.onLine ? '' : ' They will be submitted when you are back online.'));
      return refreshStatus().then(function () { if (navigator.onLine) syncQueue(); });
    }).catch(function () {
      errorNode.textContent = 'Could not save the changes on this device.';
    });
  }

  function discardItem(item) {
    var label = [item.label.candidate, item.label.station].filter(Boolean).join(' · ') || 'this mark';
    if (!window.confirm('Discard ' + label + '? It has not been submitted and will be lost.')) return;
    queueDelete(item.id).then(refreshStatus).then(function () { toast('warning', 'Mark discarded.'); });
  }

  // ── Editing a mark that already has an unsynced edit ────────────────────

  /**
   * The cached edit page still shows the server's old values. If this mark has
   * an edit waiting on the device, say so and show the edited values instead.
   */
  function showQueuedEdit(form) {
    var key = buildItem(form).key;
    queueAll().then(function (items) {
      var queued = items.filter(function (item) { return item.key === key; })[0];
      if (!queued) return;

      var byName = {};
      queued.fields.forEach(function (pair) { (byName[pair[0]] = byName[pair[0]] || []).push(pair[1]); });

      Object.keys(byName).forEach(function (name) {
        if (name === '_token' || name === '_method') return;
        var inputs = form.querySelectorAll('[name="' + name.replace(/"/g, '\\"') + '"]');
        // Only restore when the form has the same number of inputs (same shape of marks).
        if (inputs.length !== byName[name].length) return;
        inputs.forEach(function (input, i) {
          if (input.type === 'hidden' || input.readOnly || input.type === 'file') return;
          input.value = byName[name][i];
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        });
      });

      var notice = el('div', { class: 'alert alert-warning', style: 'margin:0 10px 12px', text: 'You edited this mark on this device and it has not been submitted yet. The values below are your edit; saving again replaces it.' });
      form.insertBefore(notice, form.firstChild);
    }).catch(function () { /* no queue, nothing to restore */ });
  }

  // ── Logout ──────────────────────────────────────────────────────────────

  function bindLogout() {
    document.addEventListener('click', function (event) {
      var link = event.target.closest ? event.target.closest('a[href]') : null;
      if (!link || !/\/logout\/?$/.test(link.getAttribute('href'))) return;

      var waiting = lastItems.length;
      if (waiting && !window.confirm(plural(waiting, 'mark has', 'marks have') + ' not been submitted yet. They stay on this device and are submitted the next time you sign in while online. Log out anyway?')) {
        event.preventDefault();
        return;
      }
      if (navigator.serviceWorker && navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'clear' });
      }
      try { localStorage.removeItem(WARM_KEY); } catch (e) { /* ignore */ }
    }, true);
  }

  // ── Start ───────────────────────────────────────────────────────────────

  function init() {
    injectStyles();
    buildPill();
    showStoredFlash();

    document.querySelectorAll('form[data-offline-submit]').forEach(function (form) {
      form.addEventListener('submit', function (event) { onSubmit(event, form); });
      if (form.getAttribute('data-offline-kind') === 'update') showQueuedEdit(form);
    });
    document.querySelectorAll('form[data-requires-online]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (navigator.onLine) return;
        event.preventDefault();
        toast('error', 'You are offline. Reconnect to save changes to your profile.');
      });
    });
    bindLogout();

    window.addEventListener('online', function () {
      state.auth = false;
      refreshStatus();
      syncQueue().then(function () { return prepareOffline(false); });
    });
    window.addEventListener('offline', refreshStatus);

    setInterval(function () {
      if (navigator.onLine && lastItems.some(function (i) { return i.status === 'pending'; }) && !state.auth) syncQueue();
    }, SYNC_INTERVAL_MS);

    refreshStatus().then(function () {
      return registerWorker();
    }).then(function () {
      return syncQueue();
    }).then(function () {
      return prepareOffline(false);
    });

    if (document.getElementById('examiner-offline-pending')) {
      // The results page: after its table is built, keep every linked page for offline use.
      var keepResultPages = function () { setTimeout(prefetchResultPages, 600); };
      if (document.readyState === 'complete') keepResultPages();
      else window.addEventListener('load', keepResultPages);
    }
  }

  // For support and automated tests.
  window.ExaminerOffline = {
    sync: syncQueue,
    prepare: function () { return prepareOffline(true); },
    pending: queueAll,
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();

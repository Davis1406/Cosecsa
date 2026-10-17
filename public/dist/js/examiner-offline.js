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
 *   4c. on the results page a mark with a queued edit shows the edited values in the
 *      table (tagged "Edited offline") until it syncs, and its card entry links back
 *      to the Resubmit page;
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
  var STATUS_SHOW_MS = 3000;    // how long the status label stays after a page opens / the connection changes
  var WARM_MAX_AGE_MS = 2 * 60 * 1000;
  var WARM_CONCURRENCY = 3;
  var WARM_KEY = 'examiner-offline-warm-v6'; // bump to make every device re-download the offline pages
  var FLASH_KEY = 'examiner-offline-flash';
  // No floating status label on the pages where a result is recorded or looked at: the marking forms
  // (MCS, General Surgery, FCS Clinical / Viva) and the View / Resubmit pages of one specific result.
  var NO_STATUS_PAGE = /^\/examiner\/(?:examiner_form|general_surgery|(?:cardiothoracic|neurosurgery|orthopaedic|ent|paediatric_orthopaedics|paediatric|plastic_surgery|urology)\/(?:clinical|viva)|(?:resubmit|view_results)\/\d+\/\d+|(?:fcs-resubmit|view_fcs_results)\/\d+(?:\/(?:clinical|viva))?)\/?$/;

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
          // New or changed results need their View / Resubmit pages saved again.
          prepareOffline(true);
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

  var RESULT_PAGE = /^\/examiner\/(?:(?:resubmit|view_results)\/\d+\/\d+|(?:fcs-resubmit|view_fcs_results)\/\d+(?:\/(?:clinical|viva))?)\/?$/;
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
      // Sits just above the page footer (its height is measured into --eo-footer): bottom-left on small screens, centred over the content on large ones.
      '.eo-pill{transition:opacity .4s ease;position:fixed;left:16px;bottom:calc(var(--eo-footer,56px) + 12px);z-index:2000;font-family:inherit;max-width:calc(100vw - 32px)}',
      '.eo-pill button.eo-badge{border:0;border-radius:20px;padding:8px 16px;font-size:.85rem;font-weight:600;color:#fff;background:#37474f;box-shadow:0 2px 8px rgba(0,0,0,.3);cursor:pointer}',
      '.eo-pill.eo-gone{opacity:0;pointer-events:none}',
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
      'a.eo-btn{display:inline-block;text-decoration:none;color:#000}a.eo-btn:hover{color:#000;text-decoration:none}',
      // The app's dark-mode link rules (pink text, gold on hover) outrank the rule above and put pink/gold text on this gold button.
      'body a.eo-btn,body a.eo-btn:hover,body a.eo-btn:focus,body.dark-mode a.eo-btn,body.dark-mode a.eo-btn:hover,body.dark-mode a.eo-btn:focus{color:#000 !important;text-decoration:none}',
      'a.eo-btn:hover,a.eo-btn:focus{background:#e5b002}',
      '.eo-btn.sec{background:#e0e0e0}.eo-btn.danger{background:#c62828;color:#fff}.eo-btns{display:flex;gap:6px;flex-wrap:wrap}',
      '.eo-ready{border-left:4px solid #FEC503;background:#fffdf3;color:#251917;border-radius:6px;padding:10px 14px;margin:0 0 16px;text-align:left;font-size:.88rem}',
      '.eo-ready .eo-err{color:#c62828}',
      '.eo-table{background:#fff;color:#251917}.eo-table th,.eo-table td{border-color:#eee}',
      '.eo-ready.ok{border-left-color:#2e7d32}.eo-ready.bad{border-left-color:#c62828}',
      // Dark mode (body.dark-mode): the light panels above would otherwise keep light text on a light background, or the reverse.
      'body.dark-mode .eo-ready{background:#2a2616;color:#f1e7c0}',
      'body.dark-mode .eo-ready.ok{background:#16281c;color:#cfe9d6}body.dark-mode .eo-ready.bad{background:#2d1a1a;color:#f3cccc}',
      'body.dark-mode .eo-table{background:#1e2330;color:#e2e8f0}body.dark-mode .eo-table th,body.dark-mode .eo-table td{border-color:#2d3748;color:#e2e8f0}',
      'body.dark-mode .eo-panel,body.dark-mode .eo-dialog{background:#1e2330;color:#e2e8f0;border-color:#2d3748}',
      'body.dark-mode .eo-panel h4,body.dark-mode .eo-dialog h4,body.dark-mode .eo-dialog .eo-case{color:#f48a8a}',
      'body.dark-mode .eo-item{border-color:#2d3748}body.dark-mode .eo-item small,body.dark-mode .eo-dialog .eo-sub{color:#94a3b8}',
      'body.dark-mode .eo-dialog input,body.dark-mode .eo-dialog select,body.dark-mode .eo-dialog textarea{background:#2b3040;color:#f1f5f9;border-color:#4a5568}',
      'body.dark-mode .eo-btn.sec{background:#374151;color:#e2e8f0}body.dark-mode .eo-btn.link{color:#f48a8a}',
      'body.dark-mode .eo-ready .eo-err,body.dark-mode .eo-item .eo-err,body.dark-mode .eo-dialog .eo-error{color:#f48a8a}',
      // Large screens come last so they override the base rules above.
      '@media (min-width:992px){.eo-pill{left:var(--eo-center,50%);transform:translateX(-50%)}.eo-panel{left:50%;transform:translateX(-50%)}}',
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
    watchFooter();
  }

  /**
   * Keep the badge just above the page footer, and (on large screens) centred
   * over the content area: both depend on the footer, whose height and left
   * edge change with the screen size and when the sidebar opens or closes.
   */
  function positionPill() {
    var footer = document.querySelector('footer.main-footer');
    if (!footer || !ui.pill) return;

    var box = footer.getBoundingClientRect();
    ui.pill.style.setProperty('--eo-footer', Math.round(box.height) + 'px');
    ui.pill.style.setProperty('--eo-center', Math.round(box.left + box.width / 2) + 'px');
  }

  function watchFooter() {
    positionPill();
    window.addEventListener('resize', positionPill);
    var footer = document.querySelector('footer.main-footer');
    if (footer && window.ResizeObserver) new ResizeObserver(positionPill).observe(footer);
    // the sidebar toggle animates the footer's left edge
    document.addEventListener('transitionend', function (event) {
      if (event.target && event.target.matches && event.target.matches('.content-wrapper, .main-footer, .main-sidebar')) positionPill();
    });
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

  /** Show the status label for a few seconds, then fade it (marking forms and results page only). */
  function revealStatus() {
    if (!ui.pill) return;
    ui.faded = false;
    ui.revealing = true;
    clearTimeout(ui.fadeTimer);
    ui.pill.classList.remove('eo-gone');
    if (!ui.autoHide) { ui.revealing = false; return; }
    var fade = function () {
      if (!ui.autoHide) { ui.revealing = false; return; }   // an error label (set meanwhile) stays
      if (!ui.panel.hasAttribute('hidden')) { ui.fadeTimer = setTimeout(fade, 2000); return; }   // keep it while its panel is open
      ui.revealing = false;
      ui.faded = true;
      ui.pill.classList.add('eo-gone');
    };
    ui.fadeTimer = setTimeout(fade, STATUS_SHOW_MS);
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
        if (text && !NO_STATUS_PAGE.test(window.location.pathname)) {
          // On every examiner page the status label is only revealed when the page opens or the
          // connection changes (see revealStatus), and fades after a few seconds; errors stay until dealt with.
          ui.autoHide = mode !== 'error';
          if (!ui.autoHide) ui.faded = false;
          else if (!ui.revealing) ui.faded = true;   // a status change alone does not bring the label back
          ui.badge.textContent = text;
          ui.pill.className = 'eo-pill ' + mode + (ui.faded ? ' eo-gone' : '');
          ui.pill.removeAttribute('hidden');
        } else {
          clearTimeout(ui.fadeTimer);
          ui.pill.setAttribute('hidden', 'hidden');
          ui.panel.setAttribute('hidden', 'hidden');
        }
        if (!ui.panel.hasAttribute('hidden')) renderPanel();
      }
      renderReadiness();
      renderPendingCard();
      applyPendingEdits();
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
    if (state.auth) card.appendChild(el('div', { class: 'eo-err', text: 'Sign in again while online to submit them.' }));
    else if (!navigator.onLine) card.appendChild(el('div', { text: 'They will be submitted automatically when you are back online.' }));

    var table = el('table', { class: 'table table-sm eo-table', style: 'margin:8px 0 0' }, [
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
        el('td', {}, [el('div', { class: 'eo-btns' }, item.kind === 'update' ? [
          // An edit of a mark the server already has: go back to its Resubmit page (it shows the edited values).
          goButton('Resubmit', function () { window.location.href = resubmitUrl(item); }),
          el('button', { class: 'eo-btn danger', type: 'button', text: 'Discard', onclick: function () { discardItem(item); } }),
        ] : [
          viewControl(item),
          editControl(item),
          el('button', { class: 'eo-btn danger', type: 'button', text: 'Discard', onclick: function () { discardItem(item); } }),
        ])]),
      ]));
    });
    table.appendChild(body);
    // Wide on a phone: scroll the table inside the card rather than letting it spill over the page.
    card.appendChild(el('div', { style: 'overflow-x:auto;-webkit-overflow-scrolling:touch' }, [table]));
    host.appendChild(card);
    var fcsBox = buildPendingFcsBox();
    if (fcsBox) host.appendChild(fcsBox);
  }

  /** 'mcs' / 'gs' / 'fcs' for a new mark waiting on the device (its Resubmit form is rebuilt offline), else null. */
  function pendingKind(item) {
    if (item.kind !== 'create') return null;
    var path = new URL(item.url, window.location.href).pathname;
    if (/\/examiner\/examiner_form\/?$/.test(path)) return 'mcs';
    if (/\/examiner\/general_surgery\/?$/.test(path)) return 'gs';
    if (/\/examiner\/submit-exam\/?$/.test(path)) return 'fcs';
    return null;
  }

  function pendingField(item, name) {
    var pair = item.fields.filter(function (p) { return p[0] === name; })[0];
    return pair ? pair[1] : '';
  }

  /** Resubmit page for a mark that is still waiting on the device (null if it cannot be built). */
  function pendingResubmitUrl(item) {
    var kind = pendingKind(item);
    var candidate = pendingField(item, 'candidate_id');
    if (!kind || !/^\d+$/.test(candidate)) return null;

    if (kind === 'fcs') {
      var format = pendingField(item, 'form_type') || pendingField(item, 'exam_format');
      var exam = pendingField(item, 'exam_type');
      if (format !== 'clinical' && format !== 'viva') return null;
      return '/examiner/fcs-resubmit/' + candidate + '/' + format + '?eo=' + encodeURIComponent(item.id) + '&type=fcs&exam=' + encodeURIComponent(exam) + '&format=' + format;
    }

    var station = pendingField(item, 'station_id');
    if (!/^\d+$/.test(station)) return null;
    return '/examiner/resubmit/' + candidate + '/' + station + '?eo=' + encodeURIComponent(item.id) + '&type=' + kind;
  }

  /** Is a saved Resubmit page of this kind on the device to use as the starting point? */
  function hasResubmitTemplate(kind, item) {
    if (!window.caches) return Promise.resolve(false);
    var fcs = kind === 'fcs';
    // FCS: the form is rebuilt from the waiting mark when no saved page of that specialty exists, on top of the saved Results page
    if (fcs) {
      return caches.open('examiner-pages-v1').then(function (cache) {
        return cache.match(window.location.origin + '/examiner/results');
      }).then(function (hit) { return !!hit; }).catch(function () { return false; });
    }
    var pagePath = fcs ? /\/examiner\/fcs-resubmit\/\d+\/(?:clinical|viva)$/ : /\/examiner\/resubmit\/\d+\/\d+$/;
    return caches.open('examiner-pages-v1').then(function (cache) {
      return cache.keys().then(function (requests) {
        var pages = requests.filter(function (r) { return pagePath.test(new URL(r.url).pathname); });
        return Promise.all(pages.map(function (r) {
          return cache.match(r).then(function (res) { return res ? res.text() : ''; }).then(function (html) {
            if (fcs) {
              // same specialty and format: the marks are laid out in the same cases
              return html.indexOf('name="exam_type" value="' + pendingField(item, 'exam_type') + '"') !== -1 &&
                     html.indexOf('name="form_type" value="' + (pendingField(item, 'form_type') || pendingField(item, 'exam_format')) + '"') !== -1;
            }
            return (html.indexOf('question_marks_case') !== -1) === (kind === 'gs');
          });
        }));
      }).then(function (matches) { return matches.some(Boolean); });
    }).catch(function () { return false; });
  }

  /** A button that goes to a page. A real <button> (not a link) so the app's dark-mode link colours cannot repaint its text. */
  function goButton(text, onclick) {
    return el('button', { class: 'eo-btn', type: 'button', text: text, onclick: onclick });
  }

  /** Edit goes to the mark's Resubmit page; the in-page editor is the fallback when that page cannot be built. */
  function editControl(item) {
    var url = pendingResubmitUrl(item);
    if (!url) return el('button', { class: 'eo-btn', type: 'button', text: 'Edit', onclick: function () { openItemDialog(item, true); } });
    return goButton('Edit', function () {
      if (navigator.onLine) { window.location.href = url; return; }   // online the server page is used (or the SW falls back to the device copy)
      hasResubmitTemplate(pendingKind(item), item).then(function (ok) {
        if (ok) window.location.href = url; else openItemDialog(item, true);
      });
    });
  }

  // ── View page for a mark that is still only on the device ───────────────

  var VIEW_PAGE = /^\/examiner\/(?:view_results\/\d+\/\d+|view_fcs_results\/\d+)\/?$/;

  /** The View marks page of a waiting mark (null if it cannot be built). */
  function pendingViewUrl(item) {
    var kind = pendingKind(item);
    var candidate = pendingField(item, 'candidate_id');
    if (!kind || !/^\d+$/.test(candidate)) return null;
    if (kind === 'fcs') return '/examiner/view_fcs_results/' + candidate + '?eo=' + encodeURIComponent(item.id) + '&type=fcs';
    var station = pendingField(item, 'station_id');
    if (!/^\d+$/.test(station)) return null;
    return '/examiner/view_results/' + candidate + '/' + station + '?eo=' + encodeURIComponent(item.id) + '&type=' + kind;
  }

  /** Is a saved View page of this kind on the device to use as the starting point? */
  function hasViewTemplate(kind) {
    if (!window.caches) return Promise.resolve(false);
    var pagePath = kind === 'fcs' ? /\/examiner\/view_fcs_results\/\d+$/ : /\/examiner\/view_results\/\d+\/\d+$/;
    return caches.open('examiner-pages-v1').then(function (cache) {
      return cache.keys();
    }).then(function (requests) {
      return requests.some(function (r) { return pagePath.test(new URL(r.url).pathname); });
    }).catch(function () { return false; });
  }

  /** View goes to the mark's View page; the in-page viewer is the fallback when that page cannot be built. */
  function viewControl(item) {
    var url = pendingViewUrl(item);
    if (!url) return el('button', { class: 'eo-btn sec', type: 'button', text: 'View', onclick: function () { openItemDialog(item, false); } });
    return el('button', { class: 'eo-btn sec', type: 'button', text: 'View', onclick: function () {
      if (navigator.onLine) { window.location.href = url; return; }
      hasViewTemplate(pendingKind(item)).then(function (ok) {
        if (ok) window.location.href = url; else openItemDialog(item, false);
      });
    } });
  }

  function viewRow(label, value) {
    return el('div', { class: 'vr-row' }, [el('span', { text: label }), el('span', { text: String(value) })]);
  }

  function viewShell(name, meta, scoreChildren, sections, actions) {
    return el('div', { class: 'vr-card' }, [
      el('div', { class: 'vr-head' }, [el('div', { class: 'vr-eyebrow', text: 'Candidate' }), el('div', { class: 'vr-name', text: name }), el('div', { class: 'vr-meta', text: meta })]),
      el('div', { class: 'vr-score' }, scoreChildren),
    ].concat(sections, [
      el('div', { class: 'vr-section' }, [el('p', { class: 'vr-empty', text: 'Saved on this device, not yet submitted. It is sent when you are back online.' })]),
      el('div', { class: 'vr-actions' }, actions),
    ]));
  }

  function viewLink(text, cls, href) { return el('a', { class: 'vr-btn ' + cls, href: href, text: text }); }

  /** MCS / General Surgery View page content for a waiting mark. */
  function buildMarksViewCard(item) {
    var marks = item.fields.filter(function (p) { return p[0] === 'question_marks[]'; }).map(function (p) { return p[1]; });
    var grade = pendingKind(item) === 'mcs' ? String(pendingField(item, 'overall')).toLowerCase() : '';
    var scoreChildren = [el('div', {}, [el('div', { class: 'vr-eyebrow', text: 'Total marks' }), el('div', { class: 'vr-total', text: pendingField(item, 'total_marks') || '0' })])];
    if (['pass', 'borderline', 'fail'].indexOf(grade) !== -1) scoreChildren.push(el('span', { class: 'vr-grade ' + grade, text: grade }));

    var remarks = pendingField(item, 'remarks');
    var sections = [
      el('div', { class: 'vr-section' }, [el('h4', { text: 'Marks per question' })].concat(marks.length ? marks.map(function (m, i) { return viewRow('Question ' + (i + 1), m); }) : [el('p', { class: 'vr-empty', text: 'No question marks available.' })])),
      el('div', { class: 'vr-section' }, [el('h4', { text: 'Remarks' }), remarks.trim() ? el('p', { class: 'vr-remarks', text: remarks }) : el('p', { class: 'vr-empty', text: 'No remarks.' })]),
    ];
    var resubmit = pendingResubmitUrl(item);
    return viewShell(item.label.candidate || '-', (item.label.group || '') + ' \u00b7 ' + (item.label.station || ''), scoreChildren, sections,
      [resubmit ? viewLink('Edit', 'primary', resubmit) : null, viewLink('All results', 'ghost', '/examiner/results')].filter(Boolean));
  }

  /** FCS View page content: every waiting Clinical / Viva mark of this candidate and specialty. */
  function buildFcsViewCard(item, items) {
    var CLINICAL = ['Overall Professional Capacity and Patient Care', 'Knowledge and Judgement', 'Quality of Response', 'Bedside Manner'];
    var VIVA_CASE = { 1: 'Knowledge & Judgement', 2: 'Quality of Response' };
    var candidate = pendingField(item, 'candidate_id');
    var exam = pendingField(item, 'exam_type');
    var mine = items.filter(function (i) {
      return pendingKind(i) === 'fcs' && pendingField(i, 'candidate_id') === candidate && pendingField(i, 'exam_type') === exam;
    }).sort(function (a, b) { return a.createdAt - b.createdAt; });

    var totals = { clinical: 0, viva: 0 };
    var firstOf = {};
    var sections = ['clinical', 'viva'].map(function (format) {
      var rows = mine.filter(function (i) { return (pendingField(i, 'form_type') || pendingField(i, 'exam_format')) === format; });
      var children = [el('h4', { text: format === 'clinical' ? 'Clinical' : 'Viva' })];
      if (!rows.length) children.push(el('p', { class: 'vr-empty', text: 'No ' + format + ' marks recorded.' }));
      rows.forEach(function (row) {
        firstOf[format] = firstOf[format] || row;
        var total = parseFloat(pendingField(row, 'total_marks')) || 0;
        totals[format] += total;
        var cases = {};
        var order = [];
        row.fields.forEach(function (pair) {
          var m = /^question_marks_case(\d+)\[\]$/.exec(pair[0]);
          if (!m) return;
          if (!cases[m[1]]) { cases[m[1]] = []; order.push(m[1]); }
          cases[m[1]].push(pair[1]);
        });
        children.push(el('div', { class: 'vr-station' }, [el('span', { text: 'Station ' + pendingField(row, 'station_id') }), el('small', { text: 'Total ' + formatMark(total) })]));
        if (order.length <= 1) {
          (cases[order[0]] || []).forEach(function (m, i) { children.push(viewRow(format === 'clinical' && CLINICAL[i] ? CLINICAL[i] : 'Question ' + (i + 1), m)); });
        } else {
          order.forEach(function (n) {
            children.push(el('div', { class: 'vr-case', text: format === 'viva' ? (VIVA_CASE[n] || 'Case ' + n) : 'Case ' + n }));
            cases[n].forEach(function (m, i) { children.push(viewRow('Question ' + (i + 1), m)); });
          });
        }
        var remarks = pendingField(row, 'remarks');
        if (remarks.trim()) children.push(el('p', { class: 'vr-note', text: 'Remarks: ' + remarks }));
      });
      var section = el('div', { class: 'vr-section' }, children);
      section.appendChild(el('div', { class: 'vr-section-total' }, [el('span', { text: (format === 'clinical' ? 'Clinical' : 'Viva') + ' total' }), el('span', { text: formatMark(totals[format]) })]));
      return section;
    });

    var scoreChildren = [
      el('div', {}, [el('div', { class: 'vr-eyebrow', text: 'Overall total' }), el('div', { class: 'vr-total', text: formatMark(totals.clinical + totals.viva) })]),
      el('div', { class: 'vr-subs' }, [
        el('div', {}, [el('div', { class: 'vr-eyebrow', text: 'Clinical' }), el('div', { class: 'vr-sub', text: formatMark(totals.clinical) })]),
        el('div', {}, [el('div', { class: 'vr-eyebrow', text: 'Viva' }), el('div', { class: 'vr-sub', text: formatMark(totals.viva) })]),
      ]),
    ];
    var actions = [];
    ['clinical', 'viva'].forEach(function (format) {
      var url = firstOf[format] ? pendingResubmitUrl(firstOf[format]) : null;
      if (url) actions.push(viewLink('Edit ' + (format === 'clinical' ? 'Clinical' : 'Viva'), 'primary', url));
    });
    actions.push(viewLink('All results', 'ghost', '/examiner/results'));
    return viewShell(item.label.candidate || '-', item.label.group || '', scoreChildren, sections, actions);
  }

  /** The View page opened for a mark that is only on the device (?eo=<queue id>): rebuild its card from the waiting mark. */
  function hydrateQueuedView() {
    if (!VIEW_PAGE.test(window.location.pathname)) return;
    var id = new URLSearchParams(window.location.search).get('eo');
    var card = document.querySelector('.vr .vr-card');
    if (!id || !card) return;

    queueAll().then(function (items) {
      var item = items.filter(function (i) { return i.id === id && i.kind === 'create'; })[0];
      if (!item || !pendingKind(item)) return;
      card.parentNode.replaceChild(pendingKind(item) === 'fcs' ? buildFcsViewCard(item, items) : buildMarksViewCard(item), card);
    });
  }

  /**
   * The Resubmit page opened for a mark that is only on the device (?eo=<queue id>): the
   * service worker serves a saved Resubmit page of the same kind, and this fills it with the
   * waiting mark. Saving changes the waiting mark itself, so it is still submitted once, when online.
   */
  function hydrateQueuedNew() {
    var form = document.getElementById('msform');
    var params = new URLSearchParams(window.location.search);
    var id = params.get('eo');
    if (!id) return;

    // FCS Resubmit page of a specialty / format with no saved page on this device: it arrives on top of the saved
    // Results page, and the whole form is built from the waiting mark.
    if ((!form || !document.getElementById('case-container-1')) && FCS_RESUBMIT_PAGE.test(window.location.pathname)) {
      queueAll().then(function (items) {
        var waiting = items.filter(function (i) { return i.id === id && i.kind === 'create' && pendingKind(i) === 'fcs'; })[0];
        var built = waiting && buildFcsFormPage(waiting);
        if (built) hydrateFcs(built, waiting);
      });
      return;
    }

    if (!form || form.getAttribute('data-offline-kind') !== 'update') return;

    queueAll().then(function (items) {
      var item = items.filter(function (i) { return i.id === id && i.kind === 'create'; })[0];
      if (!item) return;
      if (pendingKind(item) === 'fcs') { hydrateFcs(form, item); return; }

      var set = function (name, value) {
        var node = form.elements[name];
        if (node && node.tagName) { node.value = value; node.dispatchEvent(new Event('input', { bubbles: true })); node.dispatchEvent(new Event('change', { bubbles: true })); }
      };
      set('group_id_display', item.label.group || '');
      set('candidate_id_display', item.label.candidate || '');
      set('station_id_display', item.label.station || '');
      ['group_id', 'candidate_id', 'station_id'].forEach(function (name) { set(name, pendingField(item, name)); });

      var marks = item.fields.filter(function (p) { return p[0] === 'question_marks[]'; }).map(function (p) { return p[1]; });
      var count = function () { return form.querySelectorAll('[name="question_marks[]"]').length; };
      while (count() < marks.length && typeof window.addQuestionField === 'function') window.addQuestionField();
      Array.prototype.forEach.call(form.querySelectorAll('[name="question_marks[]"]'), function (input, i) {
        if (marks[i] == null) return;
        input.value = marks[i];
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
      set('total_marks', pendingField(item, 'total_marks'));
      set('grade', pendingField(item, 'overall') || pendingField(item, 'grade'));
      set('remarks', pendingField(item, 'remarks'));

      form.insertBefore(el('div', { class: 'alert alert-warning', style: 'margin:0 10px 12px', text: 'This mark has not been submitted yet; it is saved on this device. Saving here changes it, and it is submitted when you are back online.' }), form.firstChild);

      // Run before the normal submit handler: this form must change the waiting mark, not queue a second one.
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        if (!form.checkValidity()) { form.reportValidity(); return; }

        var values = Array.prototype.map.call(form.querySelectorAll('[name="question_marks[]"]'), function (i) { return i.value; });
        var gradeName = item.fields.some(function (p) { return p[0] === 'grade'; }) ? 'grade' : 'overall';
        var written = false;
        var fields = [];
        item.fields.forEach(function (pair) {
          if (pair[0] === 'question_marks[]') {
            if (!written) { written = true; values.forEach(function (v) { fields.push(['question_marks[]', v]); }); }
          } else if (pair[0] === 'total_marks') fields.push(['total_marks', form.elements.total_marks.value]);
          else if (pair[0] === gradeName) fields.push([gradeName, form.elements.grade.value]);
          else if (pair[0] === 'remarks') fields.push(['remarks', form.elements.remarks.value]);
          else fields.push(pair);
        });
        item.fields = fields;
        item.status = 'pending';
        item.error = '';

        queuePut(item).then(function () {
          setFlash('success', 'Changes saved on this device.' + (navigator.onLine ? '' : ' They will be submitted when you are back online.'));
          window.location.href = '/examiner/results';
        }, function () { toast('error', 'Could not save the changes on this device.'); });
      }, true);
    });
  }

  var FCS_RESUBMIT_PAGE = /^\/examiner\/fcs-resubmit\/\d+\/(?:clinical|viva)\/?$/;

  /**
   * The whole FCS Clinical / Viva Resubmit form for a waiting mark, laid out like the server's page
   * (resubmit_form_universal): used when no saved page of that specialty and format is on the device.
   * It replaces the page content of the Results page that was served instead.
   */
  function buildFcsFormPage(item) {
    var host = document.querySelector('.content-wrapper .content') || document.querySelector('.content-wrapper') || document.querySelector('.content');
    if (!host) return null;   // not a layout page: nothing to put the form into

    var format = pendingField(item, 'form_type') || pendingField(item, 'exam_format');
    var exam = pendingField(item, 'exam_type');
    var isClinical = format === 'clinical';
    var CLINICAL = ['Overall Professional Capacity and Patient Care:', 'Knowledge and Judgement:', 'Quality of Response:', 'Bedside Manner:'];
    var VIVA_CASE = { 1: 'Knowledge & Judgement', 2: 'Quality of Response' };

    var perCase = {};
    var order = [];
    item.fields.forEach(function (pair) {
      var m = /^question_marks_case(\d+)\[\]$/.exec(pair[0]);
      if (!m) return;
      if (!perCase[m[1]]) { perCase[m[1]] = []; order.push(m[1]); }
      perCase[m[1]].push(pair[1]);
    });
    if (!order.length) return null;

    var optionValues = (exam === 'urology' && format === 'viva' ? ['0'] : []).concat(['2', '4', '6', '8', '10']);
    var label = function (caseNo, i) {
      if (!isClinical) return 'Question ' + (i + 1) + ':';
      var base = CLINICAL[i] || ('Question ' + (i + 1) + ':');
      return order.length === 1 ? base : base.replace(':', '') + ':' + caseNo;
    };
    var select = function () {
      var node = el('select', { name: 'question_marks[]', class: 'form-control question-mark', required: 'required' },
        [el('option', { value: '', text: 'Select Mark' })].concat(optionValues.map(function (v) { return el('option', { value: v, text: v }); })));
      node.addEventListener('change', function () { window.updateTotalMarks(); });
      return node;
    };
    var block = function (caseNo, i, removable) {
      var kids = [select()];
      if (removable) {
        kids.push(el('div', { class: 'input-group-append' }, [el('button', { type: 'button', class: 'btn btn-danger', text: 'X', onclick: function (event) {
          event.target.closest('.question-block').remove();
          window.updateTotalMarks();
        } })]));
      }
      return el('div', { class: 'form-group question-block' }, [el('label', { text: label(caseNo, i) }), el('div', { class: 'input-group' }, kids)]);
    };

    window.updateTotalMarks = function () {
      var total = 0;
      document.querySelectorAll('.question-mark').forEach(function (node) { total += parseInt(node.value, 10) || 0; });
      var out = document.getElementById('total_marks');
      if (out) out.value = total;
    };
    window.addCaseQuestion = function (caseNo) {
      var box = document.getElementById('case-container-' + caseNo);
      if (!box) return;
      var count = box.querySelectorAll('.question-block').length;
      if (isClinical && count >= 4) return;
      box.appendChild(block(caseNo, count, true));
      window.updateTotalMarks();
    };

    var readonlyBox = function (title, hiddenName) {
      return el('div', { class: 'form-group col-md-4 col-sm-12' }, [
        el('label', { text: title }),
        el('input', { type: 'text', class: 'form-control', readonly: 'readonly' }),
        el('input', { type: 'hidden', name: hiddenName }),
      ]);
    };
    var colClass = order.length === 2 ? 'col-md-6' : (order.length === 4 ? 'col-md-3' : 'col-md-12');
    var caseColumns = order.map(function (n, idx) {
      var count = perCase[n].length;
      var blocks = [];
      for (var i = 0; i < count; i++) blocks.push(block(n, i, i >= 3));
      var heading = !isClinical && order.length === 2 ? (VIVA_CASE[n] || 'Case ' + n) : 'Case ' + (order.length === 1 ? pendingField(item, 'station_id') : n);
      return el('div', { class: colClass, style: 'padding:' + (order.length > 2 ? '10px' : '0 20px') + ';' }, [
        el('h5', { style: 'text-align:center;color:#a02626;', text: heading }),
        el('div', { id: 'case-container-' + n }, blocks),
        el('button', { type: 'button', class: 'btn btn-outline-secondary btn-sm mb-3 add-question-btn', style: 'color:black; background-color: #FEC503; border-color: #FEC503;', text: '+ Add Question', onclick: function () { window.addCaseQuestion(n); } }),
      ]);
    });

    var form = el('form', { id: 'msform', method: 'POST' }, [
      el('input', { type: 'hidden', name: 'exam_type', value: exam }),
      el('input', { type: 'hidden', name: 'form_type', value: format }),
      el('input', { type: 'hidden', name: 'exam_format', value: format }),
      el('div', { class: 'tittle' }, [el('h2', { text: 'Resubmit ' + String(item.label.exam || 'FCS').replace(/\s*-\s*(?:Clinical|Viva)\s*Evaluation Form\s*$/i, '') + ' - ' + (isClinical ? 'Clinical' : 'Viva') + ' Evaluation' })]),
      el('div', { class: 'form-row' }, [readonlyBox('Group', 'group_id'), readonlyBox('Candidate', 'candidate_id'), readonlyBox('Station', 'station_id')]),
      el('div', { class: 'form-row', style: 'border: 1px solid #a02626; padding: 15px; border-radius: 5px; position: relative;' }, caseColumns),
      el('div', { class: 'form-row justify-content-center' }, [el('div', { class: 'form-group col-md-9 col-sm-12' }, [
        el('label', { text: 'Overall Marks' }),
        el('input', { type: 'number', name: 'total_marks', id: 'total_marks', class: 'form-control', readonly: 'readonly' }),
      ])]),
      el('div', { class: 'form-row justify-content-center' }, [el('div', { class: 'form-group col-md-9 col-sm-12' }, [
        el('label', { text: 'Examiner Remarks' }),
        el('textarea', { name: 'remarks', class: 'form-control', rows: '3', placeholder: 'Enter remarks' }),
      ])]),
      el('div', { class: 'text-center' }, [
        el('a', { href: '/examiner/results', class: 'btn btn-secondary mr-2', text: 'Cancel' }),
        el('button', { type: 'submit', class: 'action-button', text: 'Update Evaluation' }),
      ]),
    ]);
    form.querySelectorAll('.form-row').forEach(function (row) { row.style.setProperty('margin', '0 10px 10px 10px', 'important'); });

    while (host.firstChild) host.removeChild(host.firstChild);
    host.appendChild(el('section', { class: 'multi_step_form' }, [form]));
    return form;
  }

  /** The same for an FCS Clinical / Viva mark: fill the saved Resubmit form of that specialty and format. */
  function hydrateFcs(form, item) {
    var change = function (node) {
      node.dispatchEvent(new Event('input', { bubbles: true }));
      node.dispatchEvent(new Event('change', { bubbles: true }));
    };

    // group / candidate / station are read-only text boxes (no name) followed by hidden ids
    var shown = [item.label.group, item.label.candidate, item.label.station];
    Array.prototype.forEach.call(form.querySelectorAll('.form-row input[type="text"][readonly]'), function (node, i) {
      if (i < 3) node.value = shown[i] || '';
    });
    ['group_id', 'candidate_id', 'station_id'].forEach(function (name) {
      var node = form.elements[name];
      if (node && node.tagName) node.value = pendingField(item, name);
    });

    // the saved marks, case by case (the create form posts question_marks_case1[], question_marks_case2[] ...)
    var perCase = {};
    var cases = [];
    item.fields.forEach(function (pair) {
      var m = /^question_marks_case(\d+)\[\]$/.exec(pair[0]);
      if (!m) return;
      if (!perCase[m[1]]) { perCase[m[1]] = []; cases.push(m[1]); }
      perCase[m[1]].push(pair[1]);
    });
    cases.forEach(function (n) {
      var box = document.getElementById('case-container-' + n);
      if (!box) return;
      var values = perCase[n];
      var blocks = function () { return box.querySelectorAll('.question-block'); };
      while (blocks().length < values.length && typeof window.addCaseQuestion === 'function') {
        var before = blocks().length;
        window.addCaseQuestion(parseInt(n, 10));
        if (blocks().length === before) break;
      }
      while (blocks().length > values.length) blocks()[blocks().length - 1].remove();
      Array.prototype.forEach.call(blocks(), function (block, i) {
        var select = block.querySelector('select');
        if (select) { select.value = values[i]; change(select); }
      });
    });
    if (form.elements.total_marks) form.elements.total_marks.value = pendingField(item, 'total_marks');
    if (form.elements.remarks) form.elements.remarks.value = pendingField(item, 'remarks');
    if (typeof window.updateTotalMarks === 'function') window.updateTotalMarks();

    form.insertBefore(el('div', { class: 'alert alert-warning', style: 'margin:0 10px 12px', text: 'This mark has not been submitted yet; it is saved on this device. Saving here changes it, and it is submitted when you are back online.' }), form.firstChild);

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      if (!form.checkValidity()) { form.reportValidity(); return; }

      var written = {};
      var fields = [];
      item.fields.forEach(function (pair) {
        var m = /^question_marks_case(\d+)\[\]$/.exec(pair[0]);
        if (m) {
          if (written[m[1]]) return;
          written[m[1]] = true;
          var box = document.getElementById('case-container-' + m[1]);
          Array.prototype.forEach.call(box ? box.querySelectorAll('.question-block select') : [], function (select) {
            fields.push([pair[0], select.value]);
          });
        } else if (pair[0] === 'total_marks') fields.push(['total_marks', form.elements.total_marks.value]);
        else if (pair[0] === 'remarks') fields.push(['remarks', form.elements.remarks.value]);
        else fields.push(pair);
      });
      item.fields = fields;
      item.status = 'pending';
      item.error = '';

      queuePut(item).then(function () {
        setFlash('success', 'Changes saved on this device.' + (navigator.onLine ? '' : ' They will be submitted when you are back online.'));
        window.location.href = '/examiner/results';
      }, function () { toast('error', 'Could not save the changes on this device.'); });
    }, true);
  }

  /**
   * FCS marks (Clinical / Viva) saved on the device: the Results page only has an FCS table once the
   * server holds FCS results, so until they sync show them here in the same layout, per candidate.
   */
  function buildPendingFcsBox() {
    var people = {};
    var order = [];
    lastItems.forEach(function (item) {
      if (item.kind !== 'create' || !/\/examiner\/submit-exam\/?$/.test(new URL(item.url, window.location.href).pathname)) return;
      var candidate = pendingField(item, 'candidate_id');
      var format = pendingField(item, 'form_type') || pendingField(item, 'exam_format');
      if (!candidate) return;
      var key = candidate + '|' + pendingField(item, 'exam_type');
      if (!people[key]) { people[key] = { name: item.label.candidate || ('Candidate ' + candidate), group: item.label.group || '-', clinical: 0, viva: 0, failed: false }; order.push(key); }
      var total = parseFloat(pendingField(item, 'total_marks')) || 0;
      if (format === 'viva') people[key].viva += total; else people[key].clinical += total;
      if (item.status === 'failed') people[key].failed = true;
    });
    if (!order.length) return null;

    var head = el('tr', {}, ['Candidate ID', 'Group', 'Clinical Total', 'Viva Total', 'Overall Total', 'Status'].map(function (h) { return el('th', { text: h }); }));
    var body = el('tbody');
    order.forEach(function (key) {
      var p = people[key];
      body.appendChild(el('tr', {}, [
        el('td', { text: p.name }),
        el('td', { text: p.group }),
        el('td', {}, [el('b', { text: formatMark(p.clinical) })]),
        el('td', {}, [el('b', { text: formatMark(p.viva) })]),
        el('td', {}, [el('b', { text: formatMark(p.clinical + p.viva) })]),
        el('td', {}, [el('span', { class: 'badge ' + (p.failed ? 'badge-danger' : 'badge-warning'), text: p.failed ? 'Needs attention' : 'Waiting to sync' })]),
      ]));
    });

    return el('div', { class: 'card', style: 'margin-top:20px' }, [
      el('div', { class: 'card-header', style: 'background-color: #FEC503; color: #a02626;' }, [
        el('h3', { class: 'card-title', text: 'FCS Specialty Results (waiting to sync)' }),
      ]),
      el('div', { class: 'card-body', style: 'overflow-x:auto' }, [
        el('table', { class: 'table table-bordered table-striped w-100' }, [el('thead', {}, [head]), body]),
        el('div', { class: 'text-muted', style: 'font-size:.85rem', text: 'Totals are from the marks saved on this device. Use the list above to view, edit or discard a mark; once synced they join the FCS results table.' }),
      ]),
    ]);
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

  // ── Results table: show queued edits until they sync ─────────────────────

  var EDIT_MCS_GS = /\/examiner\/resubmit\/(\d+)\/(\d+)/;
  var EDIT_FCS = /\/examiner\/fcs-resubmit\/(\d+)/;

  /** Where to go to edit this mark again: the Resubmit page (clinical/viva form for FCS). */
  function resubmitUrl(item) {
    var path = new URL(item.url, window.location.href).pathname;
    var fcs = EDIT_FCS.exec(path);
    if (fcs) {
      var format = parseItem(item).info.format;
      return path.replace(/\/$/, '') + (format === 'clinical' || format === 'viva' ? '/' + format : '');
    }
    return path;
  }

  function plainNumber(text) {
    var n = parseFloat(String(text).replace(/<[^>]*>/g, '').trim());
    return isNaN(n) ? 0 : n;
  }

  function formatMark(n) { return String(Math.round(n * 10) / 10); }

  function setCell(dt, tr, col, html) {
    if (dt) { dt.cell(tr, col).data(html); return; }
    var cells = tr.querySelectorAll('td');
    if (cells[col]) cells[col].innerHTML = html;
  }

  /**
   * The results table lists what the server holds, so after a mark is edited
   * offline it would keep showing the old marks. Overlay the queued edits on
   * their rows (and take them off again if an edit is discarded).
   */
  function applyPendingEdits() {
    var jq = window.jQuery;
    if (!document.getElementById('examiner-offline-pending') || !jq) return;

    var edits = lastItems.filter(function (item) { return item.kind === 'update'; }).map(function (item) {
      var path = new URL(item.url, window.location.href).pathname;
      var plain = EDIT_MCS_GS.exec(path);
      var fcs = plain ? null : EDIT_FCS.exec(path);
      return { item: item, parsed: parseItem(item), key: plain ? 'm:' + plain[1] + ':' + plain[2] : (fcs ? 'f:' + fcs[1] : null) };
    }).filter(function (edit) { return edit.key; });

    jq('table.dataTable, table.table').each(function () {
      var table = this;
      if (jq(table).closest('#examiner-offline-pending').length) return;   // the "waiting" card has its own table
      var dt = (jq.fn.dataTable && jq.fn.dataTable.isDataTable(table)) ? jq(table).DataTable() : null;
      var rows = dt ? dt.rows().nodes().toArray() : Array.prototype.slice.call(table.querySelectorAll('tbody tr'));

      rows.forEach(function (tr) {
        var cells = tr.querySelectorAll('td');
        if (cells.length < 5) return;

        // put back what the server sent before laying the current edits over it
        if (tr.getAttribute('data-eo-orig')) {
          JSON.parse(tr.getAttribute('data-eo-orig')).forEach(function (cell) { setCell(dt, tr, cell.col, cell.html); });
          tr.removeAttribute('data-eo-orig');
        }

        var key = null;
        Array.prototype.forEach.call(tr.querySelectorAll('a[href]'), function (a) {
          var path = a.getAttribute('href');
          var plain = EDIT_MCS_GS.exec(path);
          var fcs = plain ? null : EDIT_FCS.exec(path);
          if (plain) key = 'm:' + plain[1] + ':' + plain[2];
          else if (fcs) key = 'f:' + fcs[1];
        });
        var edit = key && edits.filter(function (e) { return e.key === key; }).pop();
        if (!edit) return;

        var wide = cells.length >= 10;
        var changes = [];
        var original = [];
        var remember = function (col) { original.push({ col: col, html: cells[col].innerHTML }); };

        if (wide && edit.key.charAt(0) === 'm') {
          // Candidate | Group | Q1..Q5 | Total | Grade | Actions
          for (var q = 0; q < 5; q++) {
            remember(2 + q);
            var group = edit.parsed.groups[0];
            changes.push([2 + q, group && group.values[q] != null ? formatMark(parseFloat(group.values[q])) : '-']);
          }
          remember(7); changes.push([7, '<b>' + formatMark(parseFloat(edit.parsed.info.total) || 0) + '</b>']);
          if (edit.parsed.info.gradeName) { remember(8); changes.push([8, edit.parsed.info.grade || '-']); }
        } else if (!wide && edit.key.charAt(0) === 'f') {
          // Candidate | Group | Clinical | Viva | Overall | Actions
          var col = edit.parsed.info.format === 'viva' ? 3 : 2;
          var clinical = col === 2 ? parseFloat(edit.parsed.info.total) || 0 : plainNumber(cells[2].textContent);
          var viva = col === 3 ? parseFloat(edit.parsed.info.total) || 0 : plainNumber(cells[3].textContent);
          remember(col); remember(4);
          changes.push([col, '<b>' + formatMark(col === 2 ? clinical : viva) + '</b>']);
          changes.push([4, '<b>' + formatMark(clinical + viva) + '</b>']);
        } else {
          return;
        }

        remember(0);
        changes.push([0, original[original.length - 1].html + ' <span class="badge badge-warning" title="Edited on this device; it will be submitted when you are back online">Edited offline</span>']);

        tr.setAttribute('data-eo-orig', JSON.stringify(original));
        changes.forEach(function (change) { setCell(dt, tr, change[0], change[1]); });
      });

      if (dt) {
        dt.draw(false);                                  // keep search / sort in step with what is shown
        if (dt.responsive && dt.responsive.recalc) dt.responsive.recalc();
      }
    });
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
    hydrateQueuedNew();
    hydrateQueuedView();

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
      refreshStatus().then(revealStatus);
      syncQueue().then(function () { return prepareOffline(false); });
    });
    window.addEventListener('offline', function () { refreshStatus().then(revealStatus); });

    setInterval(function () {
      if (navigator.onLine && lastItems.some(function (i) { return i.status === 'pending'; }) && !state.auth) syncQueue();
    }, SYNC_INTERVAL_MS);

    refreshStatus().then(function () {
      revealStatus();   // the page was just opened
      return registerWorker();
    }).then(function () {
      return syncQueue();
    }).then(function () {
      // Opening the dashboard online always re-saves every page, so what is
      // available offline matches the server (results, totals, new entries).
      var dashboard = /^\/examiner\/dashboard\/?$/.test(window.location.pathname);
      return prepareOffline(dashboard);
    });

    if (document.getElementById('examiner-offline-pending')) {
      // The results page: after its table is built, keep every linked page for offline use.
      var keepResultPages = function () { setTimeout(applyPendingEdits, 700); setTimeout(prefetchResultPages, 600); };
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

/* COSECSA examiner offline service worker.
 *
 * Registered from the examiner pages with scope /examiner/ (see
 * public/dist/js/examiner-offline.js). It makes the marking screens usable
 * without a connection:
 *
 *   - marking pages + candidate lists  -> network first, cached copy when the
 *                                         network is down or slow
 *   - scripts, styles, fonts, images   -> stale-while-revalidate, falling back to an
 *                                         older cached version if the network is down
 *   - every cached page also has its own scripts and styles downloaded with it, so a
 *     page saved while online never needs a file that was not saved too
 *   - any other examiner page (training, ID badge, ...) is online-only: it loads
 *     normally, and with no connection a "connection needed" page is shown
 *     instead of the browser's error screen.
 *   - everything else (POSTs, pages outside /examiner/) is left to the browser
 *     untouched.
 *
 * Marks entered offline are queued by the page script (IndexedDB), not here.
 * Cached pages are personal to the logged-in examiner, so the caches are
 * emptied when the account changes or on logout.
 */

const VERSION = 'v1';  // cache names; assets are re-checked on every visit, so this only changes if the cache layout does
const PAGE_CACHE = `examiner-pages-${VERSION}`;
const STATIC_CACHE = `examiner-static-${VERSION}`;
const META_CACHE = `examiner-meta-${VERSION}`;
const SESSION_KEY = '/__examiner-session';
const NETWORK_TIMEOUT_MS = 5000;

// Pages and data worth keeping for offline marking. Anything else under
// /examiner/ (profile, password, badge, learning, ...) is deliberately not cached.
// The results summary, each result's View / Resubmit pages and the profile pages are cached
// as copies of what was last seen online.
const PAGE_PATTERNS = [
  /^\/examiner\/dashboard\/?$/,
  /^\/examiner\/examiner_form\/?$/,
  /^\/examiner\/general_surgery\/?$/,
  /^\/examiner\/results\/?$/,
  /^\/examiner\/profile_settings\/?$/,
  /^\/examiner\/edit_info\/\d+\/?$/,
  /^\/examiner\/(resubmit|view_results)\/\d+\/\d+\/?$/,
  /^\/examiner\/fcs-resubmit\/\d+(\/(clinical|viva))?\/?$/,
  /^\/examiner\/view_fcs_results\/\d+\/?$/,
  /^\/examiner\/(cardiothoracic|urology|paediatric|orthopaedic|ent|plastic_surgery|neurosurgery|paediatric_orthopaedics)(\/(clinical|viva))?\/?$/,
  /^\/get-(mcs|gs)-candidates\/\d+\/?$/,
  /^\/get-exam-candidates\/[a-z_]+\/\d+\/?$/,
];

const STATIC_DESTINATIONS = new Set(['style', 'script', 'font', 'image']);

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  const keep = new Set([PAGE_CACHE, STATIC_CACHE, META_CACHE]);
  event.waitUntil(
    (async () => {
      const names = await caches.keys();
      await Promise.all(names.filter((n) => n.startsWith('examiner-') && !keep.has(n)).map((n) => caches.delete(n)));
      await self.clients.claim();
    })()
  );
});

self.addEventListener('message', (event) => {
  const data = event.data || {};
  if (data.type === 'session') {
    event.waitUntil(bindToUser(data.userId));
  } else if (data.type === 'clear') {
    event.waitUntil(clearPersonalData());
  }
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);

  if (url.origin === self.location.origin && isCacheablePage(url)) {
    event.respondWith(networkFirst(request, url, event));
    return;
  }

  // Examiner pages that are not kept for offline use: normal when online,
  // a friendly "connection needed" page when not.
  if (request.mode === 'navigate' && url.origin === self.location.origin && url.pathname.startsWith('/examiner/')) {
    event.respondWith(onlineOnlyNavigation(request, url));
    return;
  }

  if (STATIC_DESTINATIONS.has(request.destination)) {
    event.respondWith(staleWhileRevalidate(request));
  }
});

async function onlineOnlyNavigation(request, url) {
  try {
    return await fetch(request);
  } catch (error) {
    return connectionNeededPage(url);
  }
}

/** A readable name for the page that could not be opened. */
function pageName(url) {
  if (/^\/examiner\/learning/.test(url.pathname)) return 'Examiner Training';
  if (/^\/examiner\/badge/.test(url.pathname)) return 'The ID badge';
  if (/^\/examiner\/(profile_settings|edit_info)/.test(url.pathname)) return 'Your profile';
  return 'This page';
}

function isCacheablePage(url) {
  return PAGE_PATTERNS.some((pattern) => pattern.test(url.pathname));
}

/** Cache key ignores the query string, so ?x=1 variants share one entry. */
function cacheKey(url) {
  return url.origin + url.pathname.replace(/\/$/, '');
}

async function networkFirst(request, url, event) {
  const cache = await caches.open(PAGE_CACHE);
  const key = cacheKey(url);

  // ?eo=<id>: a Resubmit page for a mark that only exists on this device (not on the server yet).
  // The server cannot render it, so use a saved Resubmit page of the same kind; the page script
  // fills it with the waiting mark.
  const waitingMark = url.searchParams.has('eo') && /^\/examiner\/(?:resubmit\/\d+\/\d+|fcs-resubmit\/\d+\/(?:clinical|viva))\/?$/.test(url.pathname);

  try {
    const response = await fetchWithTimeout(request, NETWORK_TIMEOUT_MS);
    if (waitingMark && (response.redirected || !response.ok)) {
      const template = await resubmitTemplate(cache, url);
      if (template) return template;
    }
    // A redirect here means the session ended (we got bounced to the login
    // page): never store that as the marking page.
    if (response.ok && !response.redirected) {
      await cache.put(key, response.clone());
      if ((response.headers.get('content-type') || '').includes('text/html')) {
        event.waitUntil(cachePageAssets(response.clone(), url.href));
      }
    }
    return response;
  } catch (error) {
    if (waitingMark) {
      const template = await resubmitTemplate(cache, url);
      if (template) return template;
    }
    const cached = await cache.match(key);
    if (cached) return cached;
    if (request.mode === 'navigate') return offlinePage();
    return new Response(JSON.stringify({ error: 'offline' }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' },
    });
  }
}

/**
 * A saved Resubmit page to use as the starting point for a mark that only exists on this device:
 * MCS / General Surgery (any candidate, matching the form's shape) or FCS (same specialty and format).
 */
async function resubmitTemplate(cache, url) {
  const type = url.searchParams.get('type');
  const fcs = type === 'fcs';
  const wantGs = type === 'gs';
  const exam = url.searchParams.get('exam');
  const format = url.searchParams.get('format');
  const pagePath = fcs ? /^\/examiner\/fcs-resubmit\/\d+\/(?:clinical|viva)$/ : /^\/examiner\/resubmit\/\d+\/\d+$/;

  for (const req of await cache.keys()) {
    if (!pagePath.test(new URL(req.url).pathname)) continue;
    const saved = await cache.match(req);
    if (!saved) continue;
    const html = await saved.clone().text();
    if (fcs) {
      if (html.indexOf('name="exam_type" value="' + exam + '"') === -1 || html.indexOf('name="form_type" value="' + format + '"') === -1) continue;
    } else if ((html.indexOf('question_marks_case') !== -1) !== wantGs) {
      continue;
    }
    return new Response(html, { status: 200, headers: { 'Content-Type': 'text/html; charset=utf-8' } });
  }
  return null;
}

async function staleWhileRevalidate(request) {
  const cache = await caches.open(STATIC_CACHE);
  const cached = await cache.match(request);

  const refresh = fetch(request)
    .then((response) => {
      // status 0 = opaque cross-origin response (CDN assets); fine to reuse.
      if (response && (response.ok || response.type === 'opaque')) {
        cache.put(request, response.clone());
      }
      return response;
    })
    .catch(() => null);

  if (cached) return cached;

  const response = await refresh;
  if (response) return response;

  // Offline and this exact file (e.g. app.js?v=<new deploy>) was never saved:
  // an older copy of the same file is far better than nothing.
  const older = await cache.match(request, { ignoreSearch: true });
  return older || Response.error();
}

/**
 * Download the scripts and stylesheets a page refers to and keep them, so the
 * saved copy of the page can be opened offline with every file it asks for.
 * Versioned URLs (?v=...) change on each deploy, which is why this matters.
 */
async function cachePageAssets(response, pageUrl) {
  try {
    const html = await response.text();
    const cache = await caches.open(STATIC_CACHE);

    for (const url of assetUrls(html, pageUrl)) {
      if (await cache.match(url)) continue;
      try {
        const sameOrigin = new URL(url).origin === self.location.origin;
        const asset = await fetch(url, { mode: sameOrigin ? 'same-origin' : 'no-cors', credentials: 'omit' });
        if (asset.ok || asset.type === 'opaque') await cache.put(url, asset);
      } catch (error) {
        // one missing file must not stop the rest
      }
    }
  } catch (error) {
    // best effort only
  }
}

/** Absolute URLs of the <script src> and <link rel="stylesheet"> files in a page. */
function assetUrls(html, pageUrl) {
  const found = new Set();
  const add = (value) => {
    if (!value || value.startsWith('data:') || value.startsWith('javascript:')) return;
    try { found.add(new URL(value, pageUrl).href); } catch (error) { /* ignore */ }
  };

  for (const match of html.matchAll(/<script\b[^>]*\bsrc=["']([^"']+)["']/gi)) add(match[1]);
  for (const tag of html.matchAll(/<link\b[^>]*>/gi)) {
    if (/\brel=["']?stylesheet/i.test(tag[0])) {
      const href = /\bhref=["']([^"']+)["']/i.exec(tag[0]);
      if (href) add(href[1]);
    }
  }
  return found;
}

function fetchWithTimeout(request, ms) {
  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => reject(new Error('timeout')), ms);
    fetch(request).then(
      (response) => {
        clearTimeout(timer);
        resolve(response);
      },
      (error) => {
        clearTimeout(timer);
        reject(error);
      }
    );
  });
}

/** Empty the personal caches if a different examiner is now signed in. */
async function bindToUser(userId) {
  if (userId == null) return;
  const meta = await caches.open(META_CACHE);
  const stored = await meta.match(SESSION_KEY);
  const previous = stored ? await stored.text() : null;

  if (previous !== null && previous !== String(userId)) {
    await caches.delete(PAGE_CACHE);
  }
  await meta.put(SESSION_KEY, new Response(String(userId)));
}

async function clearPersonalData() {
  await caches.delete(PAGE_CACHE);
  await caches.delete(META_CACHE);
}

/** Branded stand-alone page shown when a page cannot be opened without a connection. */
function offlineDocument(heading, message) {
  const html = `<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1"><title>${heading} - COSECSA</title>
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;
font-family:system-ui,-apple-system,"Segoe UI",sans-serif;background:#fff8f7;color:#251917}
.card{width:100%;max-width:480px;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(160,38,38,.12);overflow:hidden;text-align:center}
.top{background:#a02626;color:#fff;padding:22px 20px}.top svg{width:44px;height:44px;margin-bottom:6px}
.top h1{margin:0;font-size:1.3rem}.body{padding:22px 22px 26px}p{line-height:1.55;margin:0 0 14px}
.status{display:inline-block;padding:4px 12px;border-radius:20px;background:#f3e3e0;color:#7b1d1d;font-size:.85rem;font-weight:600;margin-bottom:16px}
.status.on{background:#e3f1e4;color:#1b5e20}
.actions{display:flex;flex-direction:column;gap:10px;margin-top:6px}
a,button{display:block;width:100%;padding:11px 14px;border-radius:8px;border:0;font:inherit;font-weight:600;text-decoration:none;cursor:pointer}
.primary{background:#FEC503;color:#000}.secondary{background:#f3e3e0;color:#7b1d1d}
.hint{font-size:.85rem;color:#6b5a57;margin-top:18px;margin-bottom:0}
</style></head><body><div class="card">
<div class="top"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
<h1>${heading}</h1></div>
<div class="body"><div class="status" id="s">You are offline</div>
<p>${message}</p>
<div class="actions"><button class="primary" onclick="location.reload()">Try again</button>
<a class="secondary" href="/examiner/dashboard">Go to dashboard</a>
<a class="secondary" href="/examiner/results">View my results</a></div>
<p class="hint">Marking candidates and reviewing or editing your results work without a connection. Anything you save offline is sent automatically when you are back online.</p>
</div></div>
<script>addEventListener('online',function(){var s=document.getElementById('s');s.textContent='Back online - reloading...';s.className='status on';setTimeout(function(){location.reload()},600)});</script>
</body></html>`;
  return new Response(html, { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } });
}

/** An online-only page (training, badge, ...) was opened without a connection. */
function connectionNeededPage(url) {
  return offlineDocument('Connection needed', `${pageName(url)} is only available online. Connect to the internet and try again.`);
}

/** A page that can be saved for offline use was never saved on this device. */
function offlinePage() {
  return offlineDocument('Not saved for offline use', 'This page has not been saved on this device yet. Connect once and open the examiner dashboard: the marking pages are then downloaded automatically.');
}

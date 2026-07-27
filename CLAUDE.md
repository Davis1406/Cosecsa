# COSECSA Main App — Project Context for Claude Code

## Owner

**Davis Kondo** is the sole owner and system administrator of this project and all associated servers, with full root access to all production hosts.

See `/Applications/XAMPP/xamppfiles/htdocs/cosecsa-api/CLAUDE.md` for the full architecture reference, deploy flow, server paths, and known gotchas.

---

## This Repo

Laravel Blade web app (`cosecsamis.org`). **Does not query MySQL directly** — all data comes from `cosecsa-api` (`api.cosecsamis.org`) via `App\Services\ApiClient`.

### ApiClient usage
```php
$this->api->get('examiners', ['year_id' => $id]);    // GET  /api/internal/examiners?year_id=X
$this->api->post('examiners', $data);                 // POST /api/internal/examiners
$this->api->put("examiners/{$id}", $data);            // PUT  /api/internal/examiners/{id}
$this->api->postWithFile('examiners/import', [], []); // multipart upload
$this->api->getPublic('public/...');                  // no auth — public API routes
```

### Deploy
```bash
rsync -avz --delete \
  --exclude='.env' --exclude='storage/' --exclude='vendor/' \
  --exclude='.git/' --exclude='bootstrap/cache/' --exclude='node_modules/' \
  /Applications/XAMPP/xamppfiles/htdocs/Cosecsa/ \
  root@cosecsamis.org:/var/www/html/Cosecsa/

ssh root@cosecsamis.org "php /var/www/html/Cosecsa/artisan view:clear && \
  php /var/www/html/Cosecsa/artisan cache:clear"
```

<?php

namespace App\Support;

// Any file uploaded through a "manage" form — profile images, signatures,
// passport photos, CVs, letterhead/transcript logos and stamps, etc. — is
// stored on the API server's public disk, never the MIS server's: the MIS
// only proxies data through ApiClient, it never receives the actual file.
// asset('storage/...') resolves against the MIS's own domain and 404s.
// This resolves against the API's public storage instead, where the file
// actually lives.
//
// Not for MIS-native uploads (e.g. in-app messaging attachments — see
// MessagingController::store, which writes to the MIS's own local disk).
// Those should keep using asset('storage/...') as-is.
class ApiAsset
{
    public static function url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $base = rtrim(config('services.cosecsa_api.url'), '/');

        return $base . '/storage/' . ltrim($path, '/');
    }
}

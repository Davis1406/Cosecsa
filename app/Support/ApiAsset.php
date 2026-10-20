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

        return self::publicBase() . '/storage/' . ltrim($path, '/');
    }

    // Browsers need the API's public address. COSECSA_API_URL may be the
    // loopback vhost (http://127.0.0.1:8081, see ApiClient) which only this
    // server can reach; COSECSA_API_HOST then holds the public hostname.
    private static function publicBase(): string
    {
        $host = config('services.cosecsa_api.host');

        return $host
            ? 'https://' . $host
            : rtrim(config('services.cosecsa_api.url'), '/');
    }

    // A stored photo/document path → its public URL, whichever server has it.
    // Same rule as cosecsa-api's MediaUrl: newer fellow photos are written to
    // this app's own public storage (served from this host); everything else
    // lives on the API's public disk. Capsule-imported paths were never
    // copied, so they resolve to null.
    public static function media(?string $path): ?string
    {
        if (empty($path) || str_starts_with(basename($path), 'capsule_')) {
            return null;
        }

        $path = ltrim($path, '/');

        return \Illuminate\Support\Facades\Storage::disk('public')->exists($path)
            ? asset('storage/' . $path)
            : self::url($path);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The page behind the QR code on a Fellow's certificate (cosecsamis.org/verify/{token}): no login, no account data, just
 * the green tick and the few facts printed on the certificate. The long random token in the URL is the only key.
 * A scan is counted only when a person opened it: not a link-preview or mail-scanner bot, not an admin previewing it.
 */
class FellowVerifyController extends Controller
{
    // Preview crawlers and monitors fetch a link without anyone having scanned it.
    private const BOTS = '/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|skype|slack|discord|curl|wget|python|java\/|go-http|headless|monitor|uptime|pingdom|scan/i';

    public function __construct(private ApiClient $api) {}

    public function show(Request $request, string $token)
    {
        if (! preg_match('/^[a-z0-9]{16}$/', $token)) {
            return $this->page('invalid', null, 404);
        }

        $query = ['scan' => 0];
        if ($this->isPerson($request)) {
            $ua = (string) $request->userAgent();
            $query = [
                'scan'    => 1,
                // a hash, not the address: it only lets the API count a reload by the same phone once
                'visitor' => hash_hmac('sha256', $request->ip() . '|' . $ua, (string) config('app.key')),
                'ua'      => mb_substr($ua, 0, 255),
            ];
        }

        try {
            $response = $this->api->get("fellow-verification/{$token}", $query);
        } catch (\Throwable $e) {
            return $this->page('unavailable', null, 503);
        }

        if ($response->status() === 404) {
            return $this->page('invalid', null, 404);
        }
        if ($response->failed()) {
            return $this->page('unavailable', null, 503);
        }

        return $this->page('ok', $response->object()->fellow ?? null, 200);
    }

    private function isPerson(Request $request): bool
    {
        $ua = (string) $request->userAgent();

        return ! $request->isMethod('HEAD')
            && ! $request->boolean('preview')              // the admin's "Open the page" button
            && ! Auth::check()                              // an admin looking at it is not a scan
            && $ua !== ''
            && ! preg_match(self::BOTS, $ua);
    }

    private function page(string $state, $fellow, int $status)
    {
        return response()->view('public.verify_fellow', ['state' => $state, 'fellow' => $fellow], $status)->withHeaders([
            // every scan must reach the server (and be counted once); nothing about a person belongs in a search index
            'Cache-Control'   => 'no-store, max-age=0',
            'X-Robots-Tag'    => 'noindex, nofollow',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}

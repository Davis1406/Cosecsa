<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request as HttpRequest;
use Tests\TestCase;

/**
 * cosecsamis.org/verify/{token}: the page behind the QR code on a Fellow's certificate. The data comes from cosecsa-api
 * (faked here); this covers what the visitor sees, the headers, and when a visit is counted as a scan.
 */
class FellowVerifyPageTest extends TestCase
{
    private const TOKEN = 'k3x9q2mf7a1b8c4d';
    private const PHONE = ['HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Safari/604.1'];

    private function fellow(array $over = []): array
    {
        return ['verified' => true, 'fellow' => $over + [
            'name' => 'Amina Otieno', 'trained_in' => 'FCS General Surgery', 'joined' => '2021', 'exam_year' => '2026',
            'graduated_on' => '26 November 2026', 'graduated_year' => '2026', 'country' => 'Zambia',
        ]];
    }

    private function visit(string $url, array $server = self::PHONE)
    {
        config(['services.cosecsa_api.url' => 'http://api.test', 'services.cosecsa_api.token' => 't']);

        return $this->withServerVariables($server)->get($url);
    }

    private function scanQuery(): ?array
    {
        $sent = Http::recorded()->map(fn ($pair) => $pair[0])->first(fn (HttpRequest $r) => str_contains($r->url(), 'fellow-verification'));
        if (! $sent) {
            return null;
        }
        parse_str((string) parse_url($sent->url(), PHP_URL_QUERY), $q);

        return $q;
    }

    public function test_a_valid_link_shows_the_tick_the_seal_and_the_details(): void
    {
        Http::fake(['*' => Http::response($this->fellow())]);

        $r = $this->visit('/verify/' . self::TOKEN)->assertOk();

        $r->assertSee('Amina Otieno')->assertSee('Verified')->assertSee('FCS General Surgery')->assertSee('2021')->assertSee('26 November 2026')->assertSee('Zambia');
        $r->assertSee('dist/img/cosecsa_seal.png', false)->assertSee('Year joined')->assertSee('Exam year');
    }

    public function test_facts_the_register_does_not_have_are_left_off_not_shown_as_blanks(): void
    {
        Http::fake(['*' => Http::response($this->fellow(['joined' => null, 'exam_year' => null, 'graduated_on' => null]))]);

        $r = $this->visit('/verify/' . self::TOKEN)->assertOk();

        $r->assertDontSee('Year joined')->assertDontSee('Exam year');
        $r->assertSee('Graduated')->assertSee('2026');   // no date: the year stands in
    }

    public function test_the_page_is_never_cached_or_indexed_and_sends_no_referrer(): void
    {
        Http::fake(['*' => Http::response($this->fellow())]);

        $r = $this->visit('/verify/' . self::TOKEN);

        $this->assertStringContainsString('no-store', $r->headers->get('Cache-Control'));
        $this->assertSame('noindex, nofollow', $r->headers->get('X-Robots-Tag'));
        // the app-wide SecureHeaders middleware overwrites the Referrer-Policy header, so the page also says it itself
        $r->assertSee('<meta name="referrer" content="no-referrer">', false)->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_an_unknown_link_says_not_verified_with_a_404(): void
    {
        Http::fake(['*' => Http::response(['verified' => false], 404)]);

        $this->visit('/verify/' . self::TOKEN)->assertNotFound()->assertSee('Not verified')->assertDontSee('Amina');
    }

    public function test_a_malformed_link_never_reaches_the_api(): void
    {
        Http::fake();

        $this->visit('/verify/NOT-A-TOKEN')->assertNotFound()->assertSee('Not verified');
        $this->visit('/verify/' . strtoupper(self::TOKEN))->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_an_api_outage_is_not_reported_as_an_invalid_certificate(): void
    {
        Http::fake(['*' => Http::response('boom', 500)]);

        $this->visit('/verify/' . self::TOKEN)->assertStatus(503)->assertSee('unavailable')->assertDontSee('Not verified');
    }

    public function test_a_person_opening_it_is_a_scan_and_only_a_hash_of_them_is_sent(): void
    {
        Http::fake(['*' => Http::response($this->fellow())]);

        $this->visit('/verify/' . self::TOKEN);

        $q = $this->scanQuery();
        $this->assertSame('1', $q['scan']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $q['visitor']);
        $this->assertStringNotContainsString('127.0.0.1', http_build_query($q), 'the address itself is never sent');
        $this->assertStringContainsString('iPhone', $q['ua']);
    }

    public function test_bots_previews_and_the_admins_own_preview_are_not_scans(): void
    {
        Http::fake(['*' => Http::response($this->fellow())]);

        foreach ([
            ['/verify/' . self::TOKEN, ['HTTP_USER_AGENT' => 'WhatsApp/2.23.20 A']],
            ['/verify/' . self::TOKEN, ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1)']],
            ['/verify/' . self::TOKEN, ['HTTP_USER_AGENT' => 'Slackbot-LinkExpanding 1.0']],
            ['/verify/' . self::TOKEN, ['HTTP_USER_AGENT' => '']],
            ['/verify/' . self::TOKEN . '?preview=1', self::PHONE],
        ] as [$url, $server]) {
            Http::fake(['*' => Http::response($this->fellow())]);
            $this->visit($url, $server)->assertOk();
            $this->assertSame('0', $this->scanQuery()['scan'], "$url as " . ($server['HTTP_USER_AGENT'] ?? ''));
        }
    }

    public function test_a_logged_in_admin_looking_at_it_is_not_a_scan(): void
    {
        Http::fake(['*' => Http::response($this->fellow())]);

        $this->actingAs(new \App\Models\User(['id' => 1]))->withServerVariables(self::PHONE)->get('/verify/' . self::TOKEN)->assertOk();

        $this->assertSame('0', $this->scanQuery()['scan']);
    }
}

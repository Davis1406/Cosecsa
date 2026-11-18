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
            'kind' => 'fellow', 'name' => 'Amina Otieno', 'sentence' => 'is a Fellow of the College of Surgeons of East, Central and Southern Africa.',
            'rows' => [
                ['label' => 'Fellowship Type', 'value' => 'Fellow by Examination'], ['label' => 'Exam Year', 'value' => '2026'],
                ['label' => 'Specialty', 'value' => 'FCS General Surgery'], ['label' => 'Graduated in', 'value' => '26 November 2026'],
            ],
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

    public function test_a_valid_link_shows_the_tick_the_seal_the_sentence_and_the_rows_it_is_given(): void
    {
        Http::fake(['*' => Http::response($this->fellow())]);

        $r = $this->visit('/verify/' . self::TOKEN)->assertOk();

        $r->assertSee('Amina Otieno')->assertSee('Verified')->assertSee('is a Fellow of the College of Surgeons of East, Central and Southern Africa.')
          ->assertSeeInOrder(['Fellowship Type', 'Fellow by Examination', 'Exam Year', '2026', 'Specialty', 'FCS General Surgery', 'Graduated in', '26 November 2026']);
        $r->assertSee('dist/img/cosecsa_seal.png', false)->assertSee('Cosecsa_Logo_sm.png', false);
        $r->assertDontSee('Country')->assertDontSee('Year joined');
    }

    public function test_the_wording_comes_from_qr_settings_not_from_the_page(): void
    {
        Http::fake(['*' => Http::response($this->fellow(['sentence' => 'is a Fellow of COSECSA.', 'rows' => [['label' => 'Discipline', 'value' => 'Surgery']]]))]);

        $this->visit('/verify/' . self::TOKEN)->assertOk()->assertSee('is a Fellow of COSECSA.')->assertSee('Discipline')->assertDontSee('Specialty');
    }

    public function test_a_candidate_who_passed_part_one_is_not_called_a_fellow(): void
    {
        Http::fake(['*' => Http::response(['verified' => true, 'fellow' => [
            'kind' => 'candidate', 'name' => 'Anna Banda',
            'sentence' => 'is an FCS candidate of the College of Surgeons of East, Central and Southern Africa who passed the Part I examination in 2026.',
            'rows' => [['label' => 'Programme', 'value' => 'FCS General Surgery'], ['label' => 'Part I exam', 'value' => 'Passed · 2026'], ['label' => 'Country', 'value' => 'Zambia']],
        ]])]);

        $r = $this->visit('/verify/' . self::TOKEN)->assertOk();

        $r->assertSee('Anna Banda')->assertSee('Verified')->assertSee('passed the Part I examination in 2026')->assertSee('Programme')->assertSee('Passed · 2026');
        $r->assertDontSee('is a Fellow')->assertDontSee('Graduated')->assertDontSee('Fellowship');
    }

    public function test_rows_without_a_value_are_left_off_not_shown_as_blanks(): void
    {
        Http::fake(['*' => Http::response($this->fellow(['rows' => [['label' => 'Fellowship Type', 'value' => 'Fellow by Election'], ['label' => 'Specialty', 'value' => null], ['label' => 'Fellowship Year', 'value' => '']]]))]);

        $this->visit('/verify/' . self::TOKEN)->assertOk()->assertSee('Fellowship Type')->assertDontSee('Specialty')->assertDontSee('Fellowship Year');
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

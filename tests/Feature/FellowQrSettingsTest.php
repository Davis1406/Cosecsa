<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Fellows list > Certificate QR codes > QR settings: the MIS endpoints pass the wording, the graduation date and the
 * scan-count resets to cosecsa-api (faked here). Permissions come from the route middleware (fellows: view / manage),
 * which these tests skip.
 */
class FellowQrSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['services.cosecsa_api.url' => 'http://api.test', 'services.cosecsa_api.token' => 't']);
    }

    private function sent(): ?HttpRequest
    {
        return Http::recorded()->map(fn ($pair) => $pair[0])->first();
    }

    public function test_the_settings_are_read_from_the_api(): void
    {
        Http::fake(['*' => Http::response(['custom' => ['label_specialty' => 'Discipline'], 'defaults' => ['label_specialty' => 'Specialty'], 'scans_total' => 3])]);

        $this->getJson('admin/associates/fellows/qr-settings')->assertOk()->assertJson(['scans_total' => 3, 'custom' => ['label_specialty' => 'Discipline']]);
        $this->assertStringEndsWith('/api/internal/certificate-qr/settings', $this->sent()->url());
    }

    public function test_saving_passes_the_wording_and_the_date_on_but_not_the_csrf_token(): void
    {
        Http::fake(['*' => Http::response(['message' => 'QR settings saved.', 'custom' => []])]);

        $this->postJson('admin/associates/fellows/qr-settings', ['_token' => 'x', 'graduation_date' => '2026-11-26', 'label_specialty' => 'Discipline'])->assertOk();

        $data = $this->sent()->data();
        $this->assertSame(['graduation_date' => '2026-11-26', 'label_specialty' => 'Discipline'], $data);
    }

    public function test_a_validation_error_from_the_api_reaches_the_page(): void
    {
        Http::fake(['*' => Http::response(['message' => 'The given data was invalid.', 'errors' => ['graduation_date' => ['Enter a real date, or leave it blank.']]], 422)]);

        $this->postJson('admin/associates/fellows/qr-settings', ['graduation_date' => 'tomorrow'])->assertStatus(422)->assertJsonPath('errors.graduation_date.0', 'Enter a real date, or leave it blank.');
    }

    public function test_one_persons_scans_can_be_reset(): void
    {
        Http::fake(['*' => Http::response(['message' => '2 scans removed.', 'removed' => 2])]);

        $this->postJson('admin/associates/fellows/qr-scans/reset', ['_token' => 'x', 'subject_type' => 'candidate', 'subject_id' => 7])->assertOk()->assertJson(['removed' => 2]);

        $this->assertStringEndsWith('/api/internal/certificate-qr/reset-scans', $this->sent()->url());
        $this->assertSame(['subject_type' => 'candidate', 'subject_id' => 7], $this->sent()->data());
    }

    public function test_everyones_scans_are_reset_only_when_that_is_explicitly_asked_for(): void
    {
        Http::fake(['*' => Http::response(['message' => '9 scans removed.', 'removed' => 9])]);

        // an empty request must never reach the API: with no person given it would wipe every count
        $this->postJson('admin/associates/fellows/qr-scans/reset', [])->assertStatus(422);
        $this->postJson('admin/associates/fellows/qr-scans/reset', ['subject_type' => 'someone-else', 'subject_id' => 1])->assertStatus(422);
        Http::assertNothingSent();

        $this->postJson('admin/associates/fellows/qr-scans/reset', ['scope' => 'all'])->assertOk();
        $this->assertSame([], $this->sent()->data());
    }
}

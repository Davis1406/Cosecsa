<?php

namespace Tests\Feature;

use App\Http\Controllers\Learning\AdminLearningController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Examiner Training > Learner progress: the Completed / Ongoing / Pending tabs and the
 * "not started" reminder buttons (data and reminders come from cosecsa-api).
 */
class LearnerTabsTest extends TestCase
{
    private function learner(int $id, string $name, string $status, array $extra = []): array
    {
        return $extra + [
            'id' => $id, 'name' => $name, 'email' => strtolower(str_replace(' ', '.', $name)).'@example.org',
            'completed' => $status === 'completed' ? 3 : ($status === 'ongoing' ? 1 : 0), 'total' => 3,
            'percent' => $status === 'completed' ? 100 : ($status === 'ongoing' ? 33 : 0), 'quiz_score' => null,
            'status' => $status, 'started_at' => null, 'completed_at' => null,
            'login_sent_at' => null, 'login_email' => null, 'reminders_sent' => 0, 'last_reminded_at' => null,
        ];
    }

    private function html(array $learners, array $reminders = []): string
    {
        View::getFinder()->prependLocation(base_path('tests/Fixtures/views'));   // layout.app without the sidebar queries
        View::share('errors', new \Illuminate\Support\ViewErrorBag());           // normally shared by the web middleware

        return view('learning.admin.index', [
            'course' => ['title' => 'Examiner Training'],
            'modules' => [['id' => 1, 'title' => 'Module 1', 'type' => 'content'], ['id' => 9, 'title' => 'Quiz', 'type' => 'quiz']],
            'learners' => $learners,
            'reminders' => $reminders + ['enabled' => false, 'after_days' => 3, 'max_reminders' => 1, 'due_now' => 0],
        ])->render();
    }

    /** The markup of one tab pane. */
    private function pane(string $html, string $name): string
    {
        $this->assertSame(1, preg_match('/<div class="tab-pane" id="pane-'.$name.'".*?(?=<div class="tab-pane"|<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/section>)/s', $html, $m), "pane {$name} missing");

        return $m[0];
    }

    public function test_each_examiner_is_listed_only_under_their_own_tab(): void
    {
        $html = $this->html([
            $this->learner(1, 'Alice Done', 'completed', ['completed_at' => '2026-10-30T09:00:00+00:00', 'quiz_score' => 95]),
            $this->learner(2, 'Bob Midway', 'ongoing', ['started_at' => '2026-11-01T09:00:00+00:00']),
            $this->learner(3, 'Cara Waiting', 'pending', ['login_sent_at' => '2026-11-01T09:00:00+00:00']),
        ]);

        $this->assertStringContainsString('Alice Done', $this->pane($html, 'completed'));
        $this->assertStringNotContainsString('Bob Midway', $this->pane($html, 'completed'));
        $this->assertStringContainsString('Bob Midway', $this->pane($html, 'ongoing'));
        $this->assertStringNotContainsString('Cara Waiting', $this->pane($html, 'ongoing'));
        $this->assertStringContainsString('Cara Waiting', $this->pane($html, 'pending'));
        $this->assertStringNotContainsString('Alice Done', $this->pane($html, 'pending'));

        // counts on the tab buttons
        $this->assertMatchesRegularExpression('/id="count-completed">1</', $html);
        $this->assertMatchesRegularExpression('/id="count-ongoing">1</', $html);
        $this->assertMatchesRegularExpression('/id="count-pending">1</', $html);
    }

    public function test_tabs_are_buttons_so_switching_never_loads_a_page(): void
    {
        $html = $this->html([$this->learner(1, 'Alice Done', 'completed')]);

        $tabs = explode('id="pane-completed"', $html)[0];   // the tab strip comes before the panes

        $this->assertSame(3, substr_count($tabs, '<button type="button" class="nav-link" data-pane='));
        $this->assertSame(3, substr_count($tabs, 'data-toggle="pill"'));
        $this->assertDoesNotMatchRegularExpression('/id="statusTabs".*?<a /s', $tabs);   // no links, so no navigation
    }

    public function test_pending_examiner_without_a_login_email_cannot_be_reminded(): void
    {
        $html = $this->html([
            $this->learner(3, 'No Login', 'pending'),
            $this->learner(4, 'Has Login', 'pending', ['login_sent_at' => '2026-11-01T09:00:00+00:00', 'reminders_sent' => 1, 'last_reminded_at' => '2026-11-04T08:30:00+00:00']),
        ]);
        $pending = $this->pane($html, 'pending');

        $this->assertStringContainsString('Not sent yet', $pending);
        $this->assertStringContainsString('1 sent · last 04 Nov 2026', $pending);
        $this->assertMatchesRegularExpression('/data-user="3".*?class="btn btn-sm btn-outline-primary rem-one"\s+disabled/s', $pending);
        $this->assertDoesNotMatchRegularExpression('/data-user="4".*?rem-one"\s+disabled/s', $pending);
        $this->assertStringContainsString('<span id="remAllCount">1</span>', $pending);   // only the one with a login can be reminded
    }

    public function test_reminder_box_shows_the_saved_settings(): void
    {
        $html = $this->html([], ['enabled' => true, 'after_days' => 5, 'max_reminders' => 2, 'due_now' => 4]);

        $this->assertMatchesRegularExpression('/id="remEnabled" checked/', $html);
        $this->assertStringContainsString('id="remAfter" class="form-control form-control-sm" min="1" max="30" value="5"', $html);
        $this->assertStringContainsString('id="remMax" class="form-control form-control-sm" min="1" max="10" value="2"', $html);
        $this->assertStringContainsString('4 due at the next automatic run', $html);
    }

    public function test_send_reminders_passes_the_chosen_examiners_to_the_api(): void
    {
        config(['services.cosecsa_api.url' => 'http://api.test', 'services.cosecsa_api.token' => 't']);
        Http::fake(['api.test/*' => Http::response(['message' => '1 reminder sent.', 'sent' => 1, 'learners' => []], 200)]);

        $response = app(AdminLearningController::class)->sendReminders(Request::create('x', 'POST', ['user_ids' => [7]]));

        $this->assertSame(200, $response->status());
        $this->assertSame('1 reminder sent.', $response->getData(true)['message']);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/api/internal/learning/admin/reminders/send') && $r['user_ids'] === [7]);
    }

    public function test_a_failed_api_call_answers_with_a_message_not_an_error_page(): void
    {
        config(['services.cosecsa_api.url' => 'http://api.test', 'services.cosecsa_api.token' => 't']);
        Http::fake(['api.test/*' => Http::response(['message' => 'No published course.'], 404)]);

        $response = app(AdminLearningController::class)->sendReminders(Request::create('x', 'POST'));

        $this->assertSame(502, $response->status());
        $this->assertSame('No published course.', $response->getData(true)['message']);
    }
}

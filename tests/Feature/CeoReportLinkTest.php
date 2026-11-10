<?php

namespace Tests\Feature;

use App\Mail\ProgressReportCeoLinkReminderMail;
use App\Models\ProgressReportCeoLink;
use App\Models\ProgressReportParticipant;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The CEO's private report link: opening it, editing only her own section, submitting,
 * and the reminder emails that stop once she has opened it. In-memory SQLite.
 */
class CeoReportLinkTest extends TestCase
{
    private string $token;
    private ProgressReportCeoLink $link;
    private const UA = ['HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh) Chrome/120'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
                'services.progress_reports.ceo_user_id' => 50]);
        DB::purge('sqlite');
        $this->withoutMiddleware(VerifyCsrfToken::class);

        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name')->nullable(); $t->string('email')->nullable(); $t->string('signature_title')->nullable(); $t->string('signature_phone')->nullable(); });
        Schema::create('progress_report_periods', function (Blueprint $t) { $t->id(); $t->date('period_month'); $t->date('due_date'); $t->string('status')->default('open'); $t->boolean('is_current')->default(0); $t->timestamp('reminder_sent_at')->nullable(); $t->timestamp('consolidated_at')->nullable(); $t->integer('consolidated_by')->nullable(); $t->integer('created_by')->nullable(); $t->timestamps(); });
        Schema::create('progress_report_participants', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('period_id'); $t->unsignedBigInteger('user_id'); $t->string('section_label'); $t->string('status')->default('pending'); $t->timestamp('submitted_at')->nullable(); $t->boolean('edit_unlocked')->default(0); $t->unsignedInteger('sort_order')->default(0); $t->timestamp('reminder_3day_sent_at')->nullable(); $t->timestamp('reminder_1day_sent_at')->nullable(); $t->timestamps(); });
        Schema::create('progress_report_tasks', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('period_id'); $t->unsignedBigInteger('participant_id'); $t->unsignedBigInteger('template_id')->nullable(); $t->unsignedInteger('row_no')->default(1); $t->string('activity_description')->nullable(); $t->text('planned_activities')->nullable(); $t->text('current_status')->nullable(); $t->text('next_steps')->nullable(); $t->integer('updated_by')->nullable(); $t->timestamps(); });
        Schema::create('progress_report_task_revisions', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('task_id'); $t->integer('editor_id')->nullable(); $t->text('old_values')->nullable(); $t->text('new_values')->nullable(); $t->timestamp('created_at')->nullable(); });
        Schema::create('progress_report_ceo_links', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('period_id'); $t->unsignedBigInteger('participant_id')->unique(); $t->char('token_hash', 64)->unique(); $t->text('token_encrypted');
            $t->timestamp('expires_at')->nullable(); $t->timestamp('sent_at')->nullable(); $t->timestamp('first_opened_at')->nullable(); $t->timestamp('last_opened_at')->nullable();
            $t->unsignedInteger('open_count')->default(0); $t->unsignedTinyInteger('reminder_count')->default(0); $t->timestamp('last_reminder_at')->nullable(); $t->timestamp('submitted_at')->nullable();
            $t->unsignedInteger('created_by')->nullable(); $t->timestamps();
        });

        DB::table('users')->insert([['id' => 50, 'name' => 'Stella Itungu', 'email' => 'ceo@example.org'], ['id' => 7, 'name' => 'Diana', 'email' => 'diana@example.org']]);
        DB::table('progress_report_periods')->insert(['id' => 1, 'period_month' => '2026-10-01', 'due_date' => '2026-10-24', 'status' => 'consolidated', 'is_current' => 0]);
        DB::table('progress_report_participants')->insert([
            ['id' => 1, 'period_id' => 1, 'user_id' => 50, 'section_label' => 'CEO', 'sort_order' => 0, 'status' => 'pending'],
            ['id' => 2, 'period_id' => 1, 'user_id' => 7, 'section_label' => 'ADMINISTRATIVE OFFICER (DIANA KAIZA)', 'sort_order' => 1, 'status' => 'submitted'],
        ]);
        DB::table('progress_report_tasks')->insert([
            ['id' => 1, 'period_id' => 1, 'participant_id' => 1, 'row_no' => 1, 'activity_description' => 'Grants', 'planned_activities' => null],
            ['id' => 2, 'period_id' => 1, 'participant_id' => 2, 'row_no' => 1, 'activity_description' => 'Travel', 'planned_activities' => '❖ Book flights'],
        ]);

        $this->link = ProgressReportCeoLink::issue(ProgressReportParticipant::find(1), 7);
        $this->token = basename($this->link->url());
    }

    public function test_the_link_opens_the_report_without_logging_in_and_records_the_open(): void
    {
        $this->get("/ceo-report/{$this->token}", self::UA)
            ->assertOk()
            ->assertSee('Secretariat Report — October 2026')
            ->assertSee('Grants')                 // her own, editable row
            ->assertSee('Book flights');          // someone else's row, read-only

        $link = $this->link->fresh();
        $this->assertNotNull($link->first_opened_at);
        $this->assertSame(1, $link->open_count);
    }

    public function test_mail_scanners_do_not_count_as_an_open(): void
    {
        $this->get("/ceo-report/{$this->token}", ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Microsoft Office Safelinks Scanner)'])->assertOk();

        $this->assertNull($this->link->fresh()->first_opened_at);
    }

    public function test_a_wrong_or_expired_token_shows_the_unavailable_page(): void
    {
        $this->get('/ceo-report/not-a-real-token', self::UA)->assertStatus(410);

        $this->link->update(['expires_at' => now()->subDay()]);
        $this->get("/ceo-report/{$this->token}", self::UA)->assertStatus(410);
        $this->postJson("/ceo-report/{$this->token}/submit")->assertStatus(410);
    }

    public function test_she_can_edit_add_delete_and_submit_her_own_section(): void
    {
        $this->postJson("/ceo-report/{$this->token}/tasks/1/update", ['planned_activities' => "one\ntwo"])->assertOk();
        $this->assertSame("❖ one\n❖ two", DB::table('progress_report_tasks')->where('id', 1)->value('planned_activities'));
        $this->assertSame(1, DB::table('progress_report_task_revisions')->where('task_id', 1)->where('editor_id', 50)->count());

        $new = $this->postJson("/ceo-report/{$this->token}/tasks/add")->assertOk()->json('task.id');
        $this->assertSame(2, (int) DB::table('progress_report_tasks')->where('id', $new)->value('row_no'));
        $this->postJson("/ceo-report/{$this->token}/tasks/{$new}/delete")->assertOk();
        $this->assertNull(DB::table('progress_report_tasks')->where('id', $new)->first());

        $this->postJson("/ceo-report/{$this->token}/submit")->assertOk();
        $this->assertSame('submitted', DB::table('progress_report_participants')->where('id', 1)->value('status'));
        $this->assertNotNull($this->link->fresh()->submitted_at);
    }

    public function test_the_link_cannot_touch_anyone_elses_rows(): void
    {
        $this->postJson("/ceo-report/{$this->token}/tasks/2/update", ['activity_description' => 'hacked'])->assertNotFound();
        $this->postJson("/ceo-report/{$this->token}/tasks/2/delete")->assertNotFound();

        $this->assertSame('Travel', DB::table('progress_report_tasks')->where('id', 2)->value('activity_description'));
    }

    public function test_sharing_again_keeps_the_same_link_and_restarts_the_tracking(): void
    {
        $this->link->update(['first_opened_at' => now(), 'reminder_count' => 3]);

        $again = ProgressReportCeoLink::issue(ProgressReportParticipant::find(1), 7);

        $this->assertSame($this->link->url(), $again->url());
        $this->assertNull($again->first_opened_at);
        $this->assertSame(0, $again->reminder_count);
    }

    public function test_reminders_go_out_every_two_days_until_she_opens_the_link(): void
    {
        Mail::fake();

        // Sent today: nothing due yet.
        $this->artisan('progress-reports:remind-ceo-link')->assertSuccessful();
        Mail::assertNothingSent();

        // Three days later, not opened: first reminder, to the CEO.
        $this->link->update(['sent_at' => now()->subDays(3)]);
        $this->artisan('progress-reports:remind-ceo-link')->assertSuccessful();
        Mail::assertSent(ProgressReportCeoLinkReminderMail::class, fn ($m) => $m->hasTo('ceo@example.org') && $m->reportUrl === $this->link->url());
        $this->assertSame(1, $this->link->fresh()->reminder_count);

        // Same day again, and one day later: not yet.
        $this->artisan('progress-reports:remind-ceo-link')->assertSuccessful();
        $this->link->update(['last_reminder_at' => now()->subDay()]);
        $this->artisan('progress-reports:remind-ceo-link')->assertSuccessful();
        Mail::assertSent(ProgressReportCeoLinkReminderMail::class, 1);

        // Two days after the last one: second reminder.
        $this->link->update(['last_reminder_at' => now()->subDays(2)]);
        $this->artisan('progress-reports:remind-ceo-link')->assertSuccessful();
        Mail::assertSent(ProgressReportCeoLinkReminderMail::class, 2);

        // Once she has opened it, the reminders stop.
        $this->link->update(['first_opened_at' => now(), 'last_reminder_at' => now()->subDays(9)]);
        $this->artisan('progress-reports:remind-ceo-link')->assertSuccessful();
        Mail::assertSent(ProgressReportCeoLinkReminderMail::class, 2);
    }

    public function test_reminders_stop_at_the_maximum_and_after_submitting_or_expiry(): void
    {
        Mail::fake();
        $this->link->update(['sent_at' => now()->subDays(30)]);

        $this->link->update(['reminder_count' => 5]);
        $this->artisan('progress-reports:remind-ceo-link')->assertSuccessful();

        $this->link->update(['reminder_count' => 0, 'submitted_at' => now()]);
        $this->artisan('progress-reports:remind-ceo-link')->assertSuccessful();

        $this->link->update(['submitted_at' => null, 'expires_at' => now()->subDay()]);
        $this->artisan('progress-reports:remind-ceo-link')->assertSuccessful();

        Mail::assertNothingSent();
    }
}

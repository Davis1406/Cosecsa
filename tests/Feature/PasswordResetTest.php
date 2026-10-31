<?php

namespace Tests\Feature;

use App\Mail\ForgetPasswordMail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * "Forget password" on the web login: who gets an email and where it goes, that it is sent from
 * communications@cosecsa.org, and that the link works once, for an hour, and never leaks whether an email has an account.
 *
 * Uses an in-memory SQLite database with just the tables the flow touches.
 */
class PasswordResetTest extends TestCase
{
    private const GENERIC = 'If that email address belongs to a COSECSA MIS account';

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createSchema();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        // A trainee whose login is a placeholder rcsi.com address; their real email is on the trainee record.
        $this->user(1, 'Amina Hassan', 'AminaHASSAN@rcsi.com', 2, ['trainees' => 'amina@gmail.com']);
        // A fellow who logs in with a real address.
        $this->user(2, 'Peter Okello', 'peter@hospital.org', 7, ['fellows' => 'peter.personal@gmail.com']);
    }

    private function user(int $id, string $name, string $email, int $role, array $personal = [], int $type = 2): void
    {
        DB::table('users')->insert(['id' => $id, 'name' => $name, 'email' => $email, 'password' => Hash::make('old-password'), 'user_type' => $type, 'is_deleted' => 0, 'must_change_password' => 1]);
        DB::table('user_roles')->insert(['user_id' => $id, 'role_type' => $role, 'is_active' => 1]);
        foreach ($personal as $table => $address) {
            DB::table($table)->insert(['user_id' => $id, 'personal_email' => $address]);
        }
    }

    private function sent()
    {
        return $this->app['mailer']->getSymfonyTransport()->messages()->map(fn ($m) => $m->getOriginalMessage());
    }

    private function forget(string $email)
    {
        return $this->post('forget-password', ['email' => $email]);
    }

    /** The reset links in an email body, in order. */
    private function links(object $message): array
    {
        preg_match_all('#href="(http[^"]*/reset/[A-Za-z0-9]+)"#', $message->getHtmlBody(), $m);

        return array_values(array_unique($m[1]));
    }

    private function token(string $link): string
    {
        return basename($link);
    }

    public function test_the_login_email_is_a_placeholder_so_the_link_goes_to_the_personal_email_from_communications(): void
    {
        $this->forget('AminaHASSAN@rcsi.com')->assertRedirect('forget-password')->assertSessionHas('success');

        $this->assertCount(1, $this->sent());
        $mail = $this->sent()->first();
        $this->assertSame('communications@cosecsa.org', $mail->getFrom()[0]->getAddress());
        $this->assertSame('COSECSA', $mail->getFrom()[0]->getName());
        $this->assertSame('amina@gmail.com', $mail->getTo()[0]->getAddress());
        $this->assertSame('Reset your COSECSA MIS password', $mail->getSubject());
        $this->assertCount(1, $this->links($mail));
        $this->assertStringContainsString('AminaHASSAN@rcsi.com', $mail->getHtmlBody());
    }

    public function test_the_sender_stays_communications_even_when_the_app_is_configured_with_another_address(): void
    {
        config(['mail.from.address' => 'hello@example.com', 'mail.from.name' => 'Example']);

        $this->forget('peter@hospital.org');

        $this->assertSame('communications@cosecsa.org', $this->sent()->first()->getFrom()[0]->getAddress());
    }

    public function test_a_real_login_email_receives_the_link_itself(): void
    {
        $this->forget('Peter@Hospital.org');

        $this->assertSame('peter@hospital.org', $this->sent()->first()->getTo()[0]->getAddress());
    }

    public function test_a_personal_email_finds_the_account(): void
    {
        $this->forget('  Amina@Gmail.com ');

        $this->assertSame('amina@gmail.com', $this->sent()->first()->getTo()[0]->getAddress());
        $this->assertStringContainsString('AminaHASSAN@rcsi.com', $this->sent()->first()->getHtmlBody());
    }

    public function test_an_email_kept_on_the_user_row_itself_also_works(): void
    {
        $this->user(5, 'Kept Here', 'KeptHere@rcsi.com', 9);
        DB::table('users')->where('id', 5)->update(['personal_email' => 'kept.here@gmail.com', 'secondary_email' => 'kept.second@gmail.com']);

        $this->forget('KeptHere@rcsi.com');
        $this->forget('kept.second@gmail.com');

        $this->assertSame(['kept.here@gmail.com', 'kept.second@gmail.com'], $this->sent()->map(fn ($m) => $m->getTo()[0]->getAddress())->all());
    }

    public function test_an_unknown_email_gets_the_same_answer_and_no_mail(): void
    {
        $this->forget('nobody@example.com')->assertRedirect('forget-password')
            ->assertSessionHas('success', fn ($m) => str_starts_with($m, self::GENERIC));

        $this->assertCount(0, $this->sent());
    }

    public function test_an_rcsi_login_with_no_personal_email_gets_no_mail(): void
    {
        $this->user(3, 'No Mailbox', 'NoMailbox@rcsi.com', 2);

        $this->forget('NoMailbox@rcsi.com')->assertSessionHas('success');

        $this->assertCount(0, $this->sent());
    }

    public function test_deleted_accounts_and_accounts_without_an_active_role_get_no_mail(): void
    {
        DB::table('users')->where('id', 2)->update(['is_deleted' => 1]);
        DB::table('user_roles')->where('user_id', 1)->update(['is_active' => 0]);

        $this->forget('peter@hospital.org');
        $this->forget('amina@gmail.com');

        $this->assertCount(0, $this->sent());
    }

    public function test_two_accounts_on_one_mailbox_get_one_email_with_a_link_each(): void
    {
        $this->user(4, 'Amina Hassan', 'Amina.Hassan@cosecsa.org', 7, ['fellows' => 'amina@gmail.com']);

        $this->forget('amina@gmail.com');

        $this->assertCount(1, $this->sent());
        $links = $this->links($this->sent()->first());
        $this->assertCount(2, $links);
        $this->assertNotSame($links[0], $links[1]);
    }

    public function test_the_link_sets_the_password_once_and_signs_the_account_out_elsewhere(): void
    {
        $this->forget('AminaHASSAN@rcsi.com');
        $token = $this->token($this->links($this->sent()->first())[0]);
        DB::table('users')->where('id', 1)->update(['remember_token' => 'old-remember']);
        DB::table('personal_access_tokens')->insert(['tokenable_type' => User::class, 'tokenable_id' => 1, 'name' => 'app', 'token' => 'x', 'abilities' => '*']);

        $this->get("reset/$token")->assertOk()->assertSee('AminaHASSAN@rcsi.com')->assertSee('Change password');
        $this->post("reset/$token", ['password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertRedirect(route('select.role'))->assertSessionHas('success');

        $user = DB::table('users')->find(1);
        $this->assertTrue(Hash::check('new-password-1', $user->password));
        $this->assertFalse(Hash::check('old-password', $user->password));
        $this->assertSame(0, (int) $user->must_change_password);
        $this->assertNotSame('old-remember', $user->remember_token);
        $this->assertSame(0, DB::table('personal_access_tokens')->count());

        // Used once: the same link is dead.
        $this->get("reset/$token")->assertOk()->assertSee('no longer valid');
        $this->post("reset/$token", ['password' => 'another-pass-2', 'password_confirmation' => 'another-pass-2'])
            ->assertRedirect('forget-password')->assertSessionHas('error');
        $this->assertTrue(Hash::check('new-password-1', DB::table('users')->find(1)->password));
    }

    public function test_an_admins_password_is_not_hashed_twice(): void
    {
        $this->user(9, 'Admin Person', 'admin@cosecsa.org', 1, [], 1);

        $this->forget('admin@cosecsa.org');
        $token = $this->token($this->links($this->sent()->first())[0]);
        $this->post("reset/$token", ['password' => 'admin-pass-123', 'password_confirmation' => 'admin-pass-123']);

        $this->assertTrue(Hash::check('admin-pass-123', DB::table('users')->find(9)->password));
    }

    public function test_a_weak_or_mismatched_password_is_refused_and_the_link_survives(): void
    {
        $this->forget('AminaHASSAN@rcsi.com');
        $token = $this->token($this->links($this->sent()->first())[0]);

        $this->from("reset/$token")->post("reset/$token", ['password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
        $this->from("reset/$token")->post("reset/$token", ['password' => 'long-enough-1', 'password_confirmation' => 'different-1'])->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('old-password', DB::table('users')->find(1)->password));

        $this->post("reset/$token", ['password' => 'long-enough-1', 'password_confirmation' => 'long-enough-1'])->assertSessionHas('success');
    }

    public function test_the_link_expires_after_an_hour(): void
    {
        $this->forget('AminaHASSAN@rcsi.com');
        $token = $this->token($this->links($this->sent()->first())[0]);

        Carbon::setTestNow(now()->addMinutes(61));
        $this->get("reset/$token")->assertSee('no longer valid');
        $this->post("reset/$token", ['password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])->assertSessionHas('error');
        Carbon::setTestNow();

        $this->assertTrue(Hash::check('old-password', DB::table('users')->find(1)->password));
    }

    public function test_changing_the_password_retires_every_older_link(): void
    {
        $this->forget('AminaHASSAN@rcsi.com');
        $first = $this->token($this->links($this->sent()->last())[0]);
        // Someone changes the password another way (e.g. Profile > Change password) before the link is used.
        DB::table('users')->where('id', 1)->update(['password' => Hash::make('changed-elsewhere')]);

        $this->get("reset/$first")->assertSee('no longer valid');
    }

    public function test_an_invented_token_is_refused(): void
    {
        $this->get('reset/'.str_repeat('a', 64))->assertOk()->assertSee('no longer valid');
        $this->post('reset/'.str_repeat('a', 64), ['password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])->assertSessionHas('error');
    }

    public function test_one_email_cannot_be_used_to_flood_a_mailbox(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->forget('peter@hospital.org');
        }

        $this->assertCount(3, $this->sent());
    }

    public function test_the_forgot_page_renders_and_requires_an_email(): void
    {
        $this->get('forget-password')->assertOk()->assertSee('Send reset link');
        $this->post('forget-password', ['email' => 'not-an-email'])->assertSessionHasErrors('email');
        $this->assertCount(0, $this->sent());
        $this->assertSame('communications@cosecsa.org', ForgetPasswordMail::FROM_ADDRESS);
    }

    private function createSchema(): void
    {
        Schema::create('users', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('personal_email')->nullable();
            $t->string('secondary_email')->nullable();
            $t->string('password')->nullable();
            $t->integer('user_type')->default(2);
            $t->tinyInteger('is_deleted')->default(0);
            $t->boolean('must_change_password')->default(0);
            $t->string('remember_token')->nullable();
            $t->timestamps();
        });
        Schema::create('user_roles', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->integer('role_type');
            $t->tinyInteger('is_active')->default(1);
        });
        foreach (['trainees', 'candidates', 'members'] as $table) {
            Schema::create($table, function ($t) {
                $t->increments('id');
                $t->unsignedInteger('user_id')->nullable();
                $t->string('personal_email')->nullable();
            });
        }
        Schema::create('fellows', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->string('personal_email')->nullable();
            $t->string('second_email')->nullable();
        });
        Schema::create('personal_access_tokens', function ($t) {
            $t->id();
            $t->morphs('tokenable');
            $t->string('name');
            $t->string('token', 64)->unique();
            $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });
    }
}

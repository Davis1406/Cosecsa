<?php

namespace App\Services;

use App\Mail\ForgetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * "Forget password" for the web login. Someone types an email; if it is the login email or a personal email on
 * file for an account, a single-use link (valid for TTL_MINUTES) is emailed from communications@cosecsa.org.
 *
 * Most accounts log in with a name@rcsi.com address that cannot receive mail, so the link goes to the personal
 * email on the person's trainee, fellow, candidate or member record instead. One mailbox can belong to more than
 * one account (a trainee who is also a fellow, a duplicate account): they all go in one email, one link each.
 */
class PasswordReset
{
    public const TTL_MINUTES = 60;

    private const MAX_ACCOUNTS_PER_EMAIL = 6;
    private const MAX_REQUESTS_PER_EMAIL_PER_HOUR = 3;

    /** Login domains that are placeholders, never mailboxes. */
    private const UNDELIVERABLE_DOMAINS = ['rcsi.com'];

    /** [table, email column, column that holds the user's id] for every place a person's own email is kept. */
    private const PERSONAL_EMAILS = [
        ['trainees', 'personal_email', 'user_id'], ['fellows', 'personal_email', 'user_id'], ['fellows', 'second_email', 'user_id'],
        ['candidates', 'personal_email', 'user_id'], ['members', 'personal_email', 'user_id'],
        ['users', 'personal_email', 'id'], ['users', 'secondary_email', 'id'],
    ];

    private const ROLE_NAMES = [
        1 => 'Admin', 2 => 'Trainee', 3 => 'Candidate', 4 => 'Programme Director',
        5 => 'Country Representative', 7 => 'Fellow', 8 => 'Member', 9 => 'Examiner',
    ];

    /**
     * Email a reset link for every account this address belongs to.
     *
     * @return int number of emails sent (the page says the same thing whatever this is)
     */
    public function request(string $typed): int
    {
        $typed = Str::lower(trim($typed));
        if (! filter_var($typed, FILTER_VALIDATE_EMAIL)) {
            return 0;
        }
        if (! RateLimiter::attempt('pw-reset:'.sha1($typed), self::MAX_REQUESTS_PER_EMAIL_PER_HOUR, fn () => true, 3600)) {
            return 0;
        }

        $byRecipient = [];
        foreach ($this->accountsFor($typed) as $account) {
            $byRecipient[$account['to']][] = $account;
        }

        $sent = 0;
        foreach ($byRecipient as $to => $accounts) {
            $links = [];
            foreach (array_slice($accounts, 0, self::MAX_ACCOUNTS_PER_EMAIL) as $a) {
                $links[] = ['label' => $a['label'], 'roles' => $a['roles'], 'url' => url('reset/'.$this->issueToken($a['user'])), 'name' => $a['user']->name];
            }
            try {
                Mail::to($to)->send(new ForgetPasswordMail($links, self::TTL_MINUTES));
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Password reset email failed', ['to' => $to, 'error' => $e->getMessage()]);
            }
        }

        return $sent;
    }

    /** The account a reset link belongs to, or null when it is unknown, used, expired or the password changed since. */
    public function accountFor(string $token): ?object
    {
        $payload = $token !== '' ? Cache::get($this->key($token)) : null;
        if (! $payload) {
            return null;
        }
        $user = DB::table('users')->where('id', $payload['user_id'])->where('is_deleted', 0)->first();

        return $user && hash_equals($payload['fingerprint'], $this->fingerprint($user)) ? $user : null;
    }

    /** Set the new password. False when the link is no longer valid. */
    public function reset(string $token, string $password): bool
    {
        $user = $this->accountFor($token);
        if (! $user) {
            return false;
        }

        // Written with a plain query: User::setPasswordAttribute() hashes admins only and would hash an admin's twice.
        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($password),
            'must_change_password' => 0,
            'remember_token' => Str::random(60),
            'updated_at' => now(),
        ]);
        Cache::forget($this->key($token));
        User::find($user->id)?->tokens()->delete(); // signs the mobile apps out, as the API's reset does

        return true;
    }

    /** @return array<int, array{user: object, to: string, label: string, roles: string}> */
    private function accountsFor(string $typed): array
    {
        $viaPersonal = [];
        foreach (self::PERSONAL_EMAILS as [$table, $column, $userKey]) {
            foreach (DB::table($table)->whereRaw("LOWER(TRIM($column)) = ?", [$typed])->whereNotNull($userKey)->pluck($userKey) as $id) {
                $viaPersonal[(int) $id] = true;
            }
        }
        $viaLogin = DB::table('users')->whereRaw('LOWER(TRIM(email)) = ?', [$typed])->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();

        $ids = array_keys($viaPersonal + $viaLogin);
        if (! $ids) {
            return [];
        }

        $users = DB::table('users')->whereIn('id', $ids)->where('is_deleted', 0)
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('user_roles')->whereColumn('user_roles.user_id', 'users.id')->where('user_roles.is_active', 1))
            ->get();

        $out = [];
        foreach ($users as $user) {
            // They typed a personal email: it goes there. They typed the login email: to it if it is a real mailbox, else to a personal one.
            $to = isset($viaPersonal[$user->id]) ? $typed : ($this->deliverable((string) $user->email) ? Str::lower(trim($user->email)) : $this->personalEmail($user->id));
            if (! $to || ! $this->deliverable($to)) {
                continue;
            }
            $roles = DB::table('user_roles')->where('user_id', $user->id)->where('is_active', 1)->pluck('role_type')
                ->map(fn ($t) => self::ROLE_NAMES[$t] ?? null)->filter()->unique()->implode(', ');
            $out[] = ['user' => $user, 'to' => $to, 'label' => $user->email ?: $user->name, 'roles' => $roles];
        }

        return $out;
    }

    private function personalEmail(int $userId): ?string
    {
        foreach (self::PERSONAL_EMAILS as [$table, $column, $userKey]) {
            $value = DB::table($table)->where($userKey, $userId)->whereNotNull($column)->where($column, '!=', '')->value($column);
            if ($value && filter_var(trim($value), FILTER_VALIDATE_EMAIL) && $this->deliverable($value)) {
                return Str::lower(trim($value));
            }
        }

        return null;
    }

    private function deliverable(string $email): bool
    {
        $email = Str::lower(trim($email));

        return filter_var($email, FILTER_VALIDATE_EMAIL) && ! in_array(Str::after($email, '@'), self::UNDELIVERABLE_DOMAINS, true);
    }

    private function issueToken(object $user): string
    {
        $token = Str::random(64);
        Cache::put($this->key($token), ['user_id' => (int) $user->id, 'fingerprint' => $this->fingerprint($user)], now()->addMinutes(self::TTL_MINUTES));

        return $token;
    }

    /** Ties a link to the password the account had when it was issued, so any password change retires every older link. */
    private function fingerprint(object $user): string
    {
        return hash('sha256', (string) $user->password);
    }

    private function key(string $token): string
    {
        return 'pw-reset:'.hash('sha256', $token);
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * One person can have several logins: a Fellow login on one email and an Examiner login on their Gmail,
 * tied together by the personal email on their Fellow / Trainee / Candidate / Member record. At sign-in a
 * person who enters their Fellowship ID but picks the Examiner role lands on the Fellow login, which does
 * not hold that role; this finds their other login that does. (The Users directory in cosecsa-api keeps
 * the passwords of such logins the same, so the password they typed is accepted by both.)
 */
class PersonLogins
{
    private const TITLES = ['dr', 'prof', 'professor', 'mr', 'mrs', 'ms', 'miss', 'eng', 'hon', 'sir', 'madam', 'rev', 'pastor'];
    private const RECORD_TABLES = ['fellows', 'trainees', 'candidates', 'members'];

    public static function normEmail(?string $e): string
    {
        return mb_strtolower(trim((string) $e));
    }

    public static function nameWords(?string $name): array
    {
        $n = preg_replace('/[^a-z0-9 ]+/', ' ', mb_strtolower(Str::ascii(trim((string) $name))));
        $words = array_values(array_filter(explode(' ', $n)));
        while ($words && in_array($words[0], self::TITLES, true)) {
            array_shift($words);
        }
        sort($words);

        return $words;
    }

    public static function namesCompatible(?string $a, ?string $b): bool
    {
        $a = self::nameWords($a);
        $b = self::nameWords($b);
        if (! $a || ! $b) {
            return false;
        }
        if ($a === $b) {
            return true;
        }
        [$short, $long] = count($a) <= count($b) ? [$a, $b] : [$b, $a];

        return count($short) >= 2 && ! array_diff($short, $long);
    }

    /** Another login of the same person that holds $role and accepts $password, or null. */
    public static function loginHoldingRole(User $user, int $role, string $password): ?User
    {
        $emails = [self::normEmail($user->email)];
        $ownerIds = [];

        foreach (self::RECORD_TABLES as $table) {
            $cols = array_values(array_filter(['personal_email', 'second_email'], fn ($c) => Schema::hasColumn($table, $c)));
            foreach ($cols as $c) {
                // Emails on this login's own records ...
                foreach (DB::table($table)->where('user_id', $user->id)->whereNotNull($c)->pluck($c) as $e) {
                    if (trim((string) $e) !== '') {
                        $emails[] = self::normEmail($e);
                    }
                }
                // ... and records elsewhere that name this login's email.
                foreach (DB::table($table)->whereNotNull('user_id')->whereRaw("lower(trim($c)) = ?", [self::normEmail($user->email)])->pluck('user_id') as $uid) {
                    $ownerIds[] = (int) $uid;
                }
            }
        }

        $candidates = User::where('is_deleted', 0)->where('id', '!=', $user->id)
            ->where(function ($q) use ($emails, $ownerIds) {
                $q->whereIn(DB::raw('lower(trim(email))'), array_unique($emails));
                if ($ownerIds) {
                    $q->orWhereIn('id', array_unique($ownerIds));
                }
            })->get();

        foreach ($candidates as $c) {
            if (self::namesCompatible($c->name, $user->name) && $c->hasRole($role) && Hash::check($password, $c->password)) {
                return $c;
            }
        }

        return null;
    }
}

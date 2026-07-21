<?php

namespace App\Services;

use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Writes to the three system-log tables (login_logs, activity_logs,
 * email_logs) that back the admin "System Logs" screen. Uses raw DB::table()
 * inserts rather than Eloquent models, so these writes never re-trigger the
 * eloquent.* wildcard listeners registered in AppServiceProvider.
 */
class SystemLogger
{
    // Only these models generate an activity log entry — everything else
    // (Attendance, EmailTracking, session-adjacent rows, etc.) would just be
    // noise nobody asked to audit.
    public const TRACKED_MODELS = [
        \App\Models\Trainee::class,
        \App\Models\Candidates::class,
        \App\Models\FellowsModel::class,
        \App\Models\MembersModel::class,
        \App\Models\Trainer::class,
        \App\Models\CountryRepsModel::class,
        \App\Models\ExamsModel::class,
        \App\Models\Role::class,
        \App\Models\User::class,
    ];

    protected const HIDDEN_FIELDS = ['password', 'remember_token'];

    public static function logLogin(mixed $user): void
    {
        if (! $user instanceof \App\Models\User) {
            return;
        }

        DB::table('login_logs')->insert([
            'user_id'      => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'role_type'    => session('active_role'),
            'ip_address'   => request()?->ip(),
            'user_agent'   => substr((string) request()?->userAgent(), 0, 255),
            'logged_in_at' => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    public static function logModelEvent(string $action, ?Model $model): void
    {
        if (! $model || ! in_array(get_class($model), self::TRACKED_MODELS, true)) {
            return;
        }

        $changes = null;
        if ($action === 'updated') {
            $changes = collect($model->getChanges())
                ->except(array_merge(self::HIDDEN_FIELDS, ['updated_at']))
                ->mapWithKeys(fn ($new, $field) => [$field => [
                    'old' => $model->getOriginal($field),
                    'new' => $new,
                ]])
                ->all();
            if (empty($changes)) {
                return; // e.g. only updated_at touched — nothing meaningful changed
            }
        }

        DB::table('activity_logs')->insert([
            'user_id'    => Auth::id(),
            'user_name'  => Auth::user()->name ?? 'System',
            'action'     => $action,
            'model_type' => class_basename($model),
            'model_id'   => $model->getKey(),
            'summary'    => self::describe($model),
            'changes'    => $changes ? json_encode($changes) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function logEmail(MessageSent $event): void
    {
        // getTo() returns Symfony Address objects on a plain indexed array
        // (not an associative array keyed by email) — array_keys() here was
        // silently logging "0" for every send instead of the real address.
        $toAddress = collect($event->message->getTo())
            ->map(fn ($addr) => $addr->getAddress())
            ->implode(', ') ?: 'unknown';

        DB::table('email_logs')->insert([
            'to_address' => substr($toAddress, 0, 255),
            'subject'    => substr((string) $event->message->getSubject(), 0, 255),
            // Laravel's MessageSent event carries the Mailable's public
            // properties (via buildViewData()) but not its class name, so
            // this is left blank rather than guessed.
            'mailable'   => null,
            'sent_at'    => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected static function describe(Model $model): string
    {
        foreach (['name', 'firstname'] as $field) {
            if (! empty($model->{$field})) {
                $name = $field === 'firstname'
                    ? trim($model->firstname . ' ' . ($model->lastname ?? ''))
                    : $model->{$field};

                foreach (['entry_number', 'candidate_number', 'email'] as $extraField) {
                    if (! empty($model->{$extraField})) {
                        return "{$name} ({$model->{$extraField}})";
                    }
                }
                return $name;
            }
        }

        return class_basename($model) . ' #' . $model->getKey();
    }
}

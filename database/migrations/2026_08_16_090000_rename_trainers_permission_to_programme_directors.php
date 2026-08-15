<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Renames the existing `trainers.view` / `trainers.manage` permission rows
// (which have always actually gated the Programme Directors pages — see
// CHANGE.md "Split Trainers into Programme Directors + a real Trainers
// page") to `programme_directors.view` / `programme_directors.manage`,
// preserving every role's existing grants (UPDATE, not delete+recreate, so
// `role_permissions` pivot rows keyed by permission_id are untouched).
//
// Also creates fresh `trainers.view` / `trainers.manage` rows for the new
// ToT Trainers page — unassigned to any role, so nobody silently gains
// access to it; grant it explicitly per role via Roles & Permissions.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->where('key', 'trainers.view')->update([
            'key' => 'programme_directors.view',
            'module' => 'Programme Directors',
            'label' => 'See the programme directors list and profiles',
            'updated_at' => now(),
        ]);
        DB::table('permissions')->where('key', 'trainers.manage')->update([
            'key' => 'programme_directors.manage',
            'module' => 'Programme Directors',
            'label' => 'Add, edit, delete, import programme directors',
            'updated_at' => now(),
        ]);

        foreach (['view' => 'See the master trainer / ToT roster and profiles', 'manage' => 'Add, edit, delete, import trainers'] as $suffix => $label) {
            $key = "trainers.{$suffix}";
            if (! DB::table('permissions')->where('key', $key)->exists()) {
                DB::table('permissions')->insert([
                    'key' => $key,
                    'module' => 'Trainers (ToT)',
                    'label' => $label,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Must run BEFORE renaming programme_directors.* back below, or the
        // rename would collide with these on the unique `key` column.
        DB::table('role_permissions')
            ->whereIn('permission_id', DB::table('permissions')->whereIn('key', ['trainers.view', 'trainers.manage'])->pluck('id'))
            ->delete();
        DB::table('permissions')->whereIn('key', ['trainers.view', 'trainers.manage'])->delete();

        DB::table('permissions')->where('key', 'programme_directors.view')->update([
            'key' => 'trainers.view',
            'module' => 'Trainers (Programme Directors)',
            'label' => 'See the programme directors list and profiles',
            'updated_at' => now(),
        ]);
        DB::table('permissions')->where('key', 'programme_directors.manage')->update([
            'key' => 'trainers.manage',
            'module' => 'Trainers (Programme Directors)',
            'label' => 'Add, edit, delete, import programme directors',
            'updated_at' => now(),
        ]);
    }
};

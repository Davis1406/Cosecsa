<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestExaminerSeeder extends Seeder
{
    public function run(): void
    {
        $examinerId = 556;  // EXA292 Dr Joel Kiryabwire (examiners.id — NOT user_id)
        $yearId     = 7;    // 2026
        $groupId    = 1;    // Group A

        // ── 1. Assign examiner to Group A for 2026 ─────────────────────────
        $alreadyAssigned = DB::table('exams_groups')
            ->where('exm_id', $examinerId)
            ->where('group_id', $groupId)
            ->where('year_id', $yearId)
            ->exists();

        if (!$alreadyAssigned) {
            DB::table('exams_groups')->insert([
                'exm_id'     => $examinerId,
                'group_id'   => $groupId,
                'year_id'    => $yearId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('✓ Assigned EXA292 to Group A for 2026.');
        } else {
            $this->command->info('→ Group A already assigned for 2026.');
        }

        // ── 2. Create 4 test neurosurgery candidates ────────────────────────
        $testCandidates = [
            ['code' => 'NSG2026-001', 'name' => 'Test Alpha Neurosurgery',   'email' => 'test.alpha.neuro@cosecsa.test'],
            ['code' => 'NSG2026-002', 'name' => 'Test Beta Neurosurgery',    'email' => 'test.beta.neuro@cosecsa.test'],
            ['code' => 'NSG2026-003', 'name' => 'Test Gamma Neurosurgery',   'email' => 'test.gamma.neuro@cosecsa.test'],
            ['code' => 'NSG2026-004', 'name' => 'Test Delta Neurosurgery',   'email' => 'test.delta.neuro@cosecsa.test'],
        ];

        foreach ($testCandidates as $tc) {
            if (DB::table('users')->where('email', $tc['email'])->exists()) {
                $this->command->info("→ {$tc['code']} already exists, skipping.");
                continue;
            }

            $userId = DB::table('users')->insertGetId([
                'name'       => $tc['name'],
                'email'      => $tc['email'],
                'password'   => Hash::make('test1234'),
                'user_type'  => 3,
                'is_deleted' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('user_roles')->insert([
                'user_id'    => $userId,
                'role_type'  => 3,
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('candidates')->insert([
                'user_id'        => $userId,
                'entry_number'   => 'TEST/' . $tc['code'],
                'firstname'      => 'Test',
                'middlename'     => '',
                'lastname'       => $tc['code'],
                'personal_email' => $tc['email'],
                'candidate_id'   => $tc['code'],
                'programme_id'   => 3,        // Neurosurgery
                'group_id'       => $groupId, // Group A
                'exam_year'      => '2026',
                'gender'         => 'Male',
                'country_id'     => 1,
                'hospital_id'    => 1,
                'invoice_status' => 'Sent',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $this->command->info("✓ Created candidate {$tc['code']} (user_id={$userId})");
        }

        // ── 3. Verify ───────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('── Verification ──');

        $groups = DB::table('exams_groups')
            ->join('examiners_groups', 'exams_groups.group_id', '=', 'examiners_groups.id')
            ->where('exams_groups.exm_id', $examinerId)
            ->where('exams_groups.year_id', $yearId)
            ->pluck('examiners_groups.group_name');
        $this->command->info('EXA292 groups for 2026: ' . $groups->join(', '));

        $cands = DB::table('candidates')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->where('candidates.programme_id', 3)
            ->where('candidates.exam_year', '2026')
            ->where('candidates.group_id', $groupId)
            ->where('users.is_deleted', 0)
            ->select('candidates.candidate_id', 'users.name')
            ->get();

        $this->command->info("Neurosurgery 2026 Group A candidates ({$cands->count()}):");
        foreach ($cands as $c) {
            $this->command->info("  - {$c->candidate_id}: {$c->name}");
        }
    }
}

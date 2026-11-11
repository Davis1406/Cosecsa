<?php

namespace Tests\Feature;

use App\Services\ExamControl;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** The MIS side of the "start the exam" guard: reads what the exams officer set in the API. */
class ExamControlReadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('years', fn (Blueprint $t) => [$t->id(), $t->string('year_name')]);
        Schema::create('app_settings', fn (Blueprint $t) => [$t->id(), $t->string('key')->unique(), $t->text('value')->nullable(), $t->timestamps()]);
        Schema::create('examiners_groups', fn (Blueprint $t) => [$t->id(), $t->string('group_name')]);
        DB::table('years')->insert(['id' => 7, 'year_name' => date('Y')]);
        DB::table('examiners_groups')->insert([['id' => 1, 'group_name' => 'A'], ['id' => 2, 'group_name' => 'TEST-PART1']]);
    }

    public function test_an_exam_is_closed_until_the_officer_starts_it_and_only_mcs_is_guarded(): void
    {
        $this->assertFalse(ExamControl::isOpen('mcs'));
        $this->assertTrue(ExamControl::isOpen('gs'));

        DB::table('app_settings')->insert(['key' => 'exam_control.mcs.7', 'value' => json_encode(['open' => true, 'started_at' => '2026-11-11 08:25:00', 'started_by_name' => 'Amani'])]);

        $s = ExamControl::state('mcs');
        $this->assertTrue($s['open']);
        $this->assertSame('Amani', $s['started_by_name']);
    }

    public function test_test_groups_stay_open_for_practice_while_real_groups_wait(): void
    {
        $this->assertFalse(ExamControl::marksAllowed('mcs', 1));
        $this->assertTrue(ExamControl::marksAllowed('mcs', 2));

        DB::table('app_settings')->insert(['key' => 'exam_control.mcs.7', 'value' => json_encode(['open' => true])]);
        $this->assertTrue(ExamControl::marksAllowed('mcs', 1));
    }

    public function test_the_start_time_is_exposed_for_the_waiting_screen(): void
    {
        DB::table('app_settings')->insert(['key' => 'exam_control.mcs.7', 'value' => json_encode(['open' => false, 'scheduled_for' => '2026-11-11 08:30'])]);

        $this->assertSame('2026-11-11 08:30', ExamControl::state('mcs')['scheduled_for']);
        $this->assertFalse(ExamControl::state('mcs')['open']);
    }
}

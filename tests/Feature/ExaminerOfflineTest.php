<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Server side of offline marking: the JSON replies the browser queue relies
 * on (success / duplicate / invalid), that the classic redirect flow still
 * works, and the manifest + token endpoints.
 *
 * Uses an in-memory SQLite database with just the tables these endpoints touch.
 */
class ExaminerOfflineTest extends TestCase
{
    private int $examinerId = 5;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        $this->createSchema();
        $this->seedBaseData();

        $this->withoutMiddleware();
        $this->actingAs(User::find(1));
    }

    // ── Marking endpoints ───────────────────────────────────────────────

    public function test_mcs_mark_is_saved_and_reported_as_success(): void
    {
        $this->postJson('examiner/examiner_form', $this->flatPayload(['overall' => 'Pass']))
            ->assertStatus(201)
            ->assertJson(['status' => 'success']);

        $row = DB::table('mcs_results')->first();
        $this->assertSame(10, (int) $row->candidate_id);
        $this->assertSame($this->examinerId, (int) $row->examiner_id);
        $this->assertSame('pass', $row->overall);
        $this->assertSame('[4,5.5]', $row->question_mark);
        $this->assertSame(2, (int) $row->exam_year); // years.id of 2026
    }

    public function test_repeating_a_mark_is_a_duplicate_and_writes_nothing(): void
    {
        $payload = $this->flatPayload(['overall' => 'pass']);

        $this->postJson('examiner/examiner_form', $payload)->assertStatus(201);
        $this->postJson('examiner/examiner_form', $payload)
            ->assertStatus(409)
            ->assertJson(['status' => 'duplicate']);

        $this->assertSame(1, DB::table('mcs_results')->count());
    }

    public function test_a_different_station_for_the_same_candidate_is_not_a_duplicate(): void
    {
        $this->postJson('examiner/examiner_form', $this->flatPayload(['overall' => 'pass']))->assertStatus(201);
        $this->postJson('examiner/examiner_form', $this->flatPayload(['overall' => 'pass', 'station_id' => 2]))->assertStatus(201);

        $this->assertSame(2, DB::table('mcs_results')->count());
    }

    public function test_incomplete_marks_are_rejected_not_stored(): void
    {
        $payload = $this->flatPayload(['overall' => 'pass']);
        unset($payload['question_marks']);

        $this->postJson('examiner/examiner_form', $payload)
            ->assertStatus(422)
            ->assertJson(['status' => 'invalid']);

        $this->postJson('examiner/examiner_form', $this->flatPayload(['candidate_id' => 999999, 'overall' => 'pass']))
            ->assertStatus(422);

        $this->postJson('examiner/examiner_form', $this->flatPayload(['station_id' => 9, 'overall' => 'pass']))
            ->assertStatus(422);

        $this->assertSame(0, DB::table('mcs_results')->count());
    }

    public function test_general_surgery_mark_round_trip(): void
    {
        $payload = $this->flatPayload();

        $this->postJson('examiner/general_surgery', $payload)->assertStatus(201)->assertJson(['status' => 'success']);
        $this->postJson('examiner/general_surgery', $payload)->assertStatus(409)->assertJson(['status' => 'duplicate']);

        $this->assertSame(1, DB::table('gs_results')->count());
    }

    public function test_fcs_mark_is_saved_per_format_and_duplicates_are_per_format(): void
    {
        $clinical = $this->fcsPayload('clinical');
        $viva = $this->fcsPayload('viva');

        $this->postJson('examiner/submit-exam', $clinical)->assertStatus(201)->assertJson(['status' => 'success']);
        $this->postJson('examiner/submit-exam', $clinical)->assertStatus(409)->assertJson(['status' => 'duplicate']);
        $this->postJson('examiner/submit-exam', $viva)->assertStatus(201);

        $this->assertSame(2, DB::table('cardiothoracic_results')->count());
        $this->assertSame('[3,4,5]', DB::table('cardiothoracic_results')->where('exam_format', 'clinical')->value('question_mark'));
    }

    public function test_fcs_rejects_an_unknown_exam_type(): void
    {
        $this->postJson('examiner/submit-exam', $this->fcsPayload('clinical', ['exam_type' => 'nonsense']))
            ->assertStatus(422)
            ->assertJson(['status' => 'invalid']);
    }

    public function test_the_normal_form_post_still_redirects_back_with_a_flash_message(): void
    {
        $this->from('examiner/examiner_form')
            ->post('examiner/examiner_form', $this->flatPayload(['overall' => 'pass']))
            ->assertRedirect('examiner/examiner_form')
            ->assertSessionHas('success');

        $this->from('examiner/examiner_form')
            ->post('examiner/examiner_form', $this->flatPayload(['overall' => 'pass']))
            ->assertRedirect('examiner/examiner_form')
            ->assertSessionHas('error');

        $this->assertSame(1, DB::table('mcs_results')->count());
    }

    // ── Sandbox (TEST-…) groups ─────────────────────────────────────────

    public function test_marks_for_a_test_group_go_to_the_test_tables_only(): void
    {
        $this->postJson('examiner/examiner_form', $this->flatPayload(['group_id' => 17, 'overall' => 'pass']))->assertStatus(201);
        $this->postJson('examiner/general_surgery', $this->flatPayload(['group_id' => 17]))->assertStatus(201);
        $this->postJson('examiner/submit-exam', $this->fcsPayload('clinical', ['group_id' => 17]))->assertStatus(201);

        foreach (['mcs_results', 'gs_results', 'cardiothoracic_results'] as $table) {
            $this->assertSame(0, DB::table($table)->count(), "$table must stay untouched");
            $this->assertSame(1, DB::table("test_$table")->count(), "test_$table should hold the mark");
        }
        $this->assertSame(17, (int) DB::table('test_mcs_results')->value('group_id'));
    }

    public function test_duplicates_in_a_test_group_are_detected_in_the_test_table(): void
    {
        $payload = $this->flatPayload(['group_id' => 17]);

        $this->postJson('examiner/general_surgery', $payload)->assertStatus(201);
        $this->postJson('examiner/general_surgery', $payload)->assertStatus(409);

        $this->assertSame(1, DB::table('test_gs_results')->count());
    }

    public function test_a_real_mark_does_not_count_as_a_duplicate_of_a_test_mark(): void
    {
        $this->postJson('examiner/general_surgery', $this->flatPayload(['group_id' => 17]))->assertStatus(201);
        $this->postJson('examiner/general_surgery', $this->flatPayload(['group_id' => 3]))->assertStatus(201);

        $this->assertSame(1, DB::table('test_gs_results')->count());
        $this->assertSame(1, DB::table('gs_results')->count());
    }

    public function test_a_test_group_never_falls_back_to_the_real_table_when_the_test_table_is_missing(): void
    {
        Schema::drop('test_gs_results');

        $this->postJson('examiner/general_surgery', $this->flatPayload(['group_id' => 17]))
            ->assertStatus(422)
            ->assertJson(['status' => 'invalid']);

        $this->assertSame(0, DB::table('gs_results')->count());
    }

    // ── Results summary ─────────────────────────────────────────────────

    public function test_results_page_lists_real_and_sandbox_marks_and_flags_the_sandbox_ones(): void
    {
        $this->postJson('examiner/general_surgery', $this->flatPayload(['group_id' => 3]))->assertStatus(201);
        $this->postJson('examiner/general_surgery', $this->flatPayload(['group_id' => 17, 'candidate_id' => 11, 'station_id' => 2]))->assertStatus(201);

        $view = app(\App\Http\Controllers\CandidatesController::class)->results();
        $rows = $view->getData()['gsResults']->sortBy('station_id')->values();

        $this->assertCount(2, $rows);
        $this->assertSame('PEN-1', $rows[0]->candidate_name);   // PEN shown: no candidate number yet
        $this->assertFalse((bool) $rows[0]->is_test);
        $this->assertSame('A', $rows[0]->group_name);
        $this->assertTrue((bool) $rows[1]->is_test);
        $this->assertSame('TEST-PART1', $rows[1]->group_name);
    }

    public function test_results_page_groups_fcs_marks_per_candidate_and_keeps_sandbox_apart(): void
    {
        $this->postJson('examiner/submit-exam', $this->fcsPayload('clinical', ['group_id' => 3]))->assertStatus(201);
        $this->postJson('examiner/submit-exam', $this->fcsPayload('viva', ['group_id' => 3]))->assertStatus(201);
        $this->postJson('examiner/submit-exam', $this->fcsPayload('clinical', ['group_id' => 17]))->assertStatus(201);

        $fcs = app(\App\Http\Controllers\CandidatesController::class)->results()->getData()['fcsResults'];

        $this->assertCount(2, $fcs);
        $real = $fcs->firstWhere('is_test', false);
        $sandbox = $fcs->firstWhere('is_test', true);
        $this->assertSame(24.0, (float) $real->overall_total);       // clinical 12 + viva 12
        $this->assertSame(12.0, (float) $sandbox->overall_total);
        $this->assertSame('cardiothoracic_results', $real->source_table);
    }

    // ── Editing (resubmitting) saved marks ──────────────────────────────

    public function test_editing_an_mcs_mark_updates_it_and_replies_json(): void
    {
        $this->postJson('examiner/examiner_form', $this->flatPayload(['overall' => 'pass']))->assertStatus(201);

        $this->postJson('examiner/resubmit/10/1', [
            'group_id' => 3, 'station_id' => 1, 'question_marks' => [7, 8], 'total_marks' => 15, 'remarks' => 'Revised', 'grade' => 'borderline',
        ])->assertOk()->assertJson(['status' => 'success']);

        $row = DB::table('mcs_results')->first();
        $this->assertSame('[7,8]', $row->question_mark);
        $this->assertEquals(15, $row->total);
        $this->assertSame('borderline', $row->overall);
        $this->assertSame(1, DB::table('mcs_results')->count());
    }

    public function test_editing_the_same_mark_twice_is_safe_to_repeat(): void
    {
        $this->postJson('examiner/general_surgery', $this->flatPayload())->assertStatus(201);
        $edit = ['group_id' => 3, 'station_id' => 1, 'question_marks' => [9], 'total_marks' => 9, 'remarks' => ''];

        $this->postJson('examiner/resubmit/10/1', $edit)->assertOk();
        $this->postJson('examiner/resubmit/10/1', $edit)->assertOk();

        $this->assertSame('[9]', DB::table('gs_results')->value('question_mark'));
        $this->assertSame(1, DB::table('gs_results')->count());
    }

    public function test_editing_a_mark_that_does_not_exist_or_has_no_marks_is_rejected(): void
    {
        $this->postJson('examiner/resubmit/10/1', ['question_marks' => [5], 'total_marks' => 5])
            ->assertStatus(422)->assertJson(['status' => 'invalid', 'message' => 'Evaluation not found.']);

        $this->postJson('examiner/general_surgery', $this->flatPayload())->assertStatus(201);
        $this->postJson('examiner/resubmit/10/1', ['total_marks' => 5])->assertStatus(422)->assertJson(['status' => 'invalid']);
        $this->assertSame('[4,5.5]', DB::table('gs_results')->value('question_mark'));
    }

    public function test_the_classic_edit_form_still_redirects_to_the_results_page(): void
    {
        $this->postJson('examiner/general_surgery', $this->flatPayload())->assertStatus(201);

        $this->post('examiner/resubmit/10/1', ['group_id' => 3, 'station_id' => 1, 'question_marks' => [6], 'total_marks' => 6])
            ->assertRedirect(url('examiner/results'))
            ->assertSessionHas('success');
    }

    public function test_editing_an_fcs_mark_changes_only_that_format(): void
    {
        $this->postJson('examiner/submit-exam', $this->fcsPayload('clinical'))->assertStatus(201);
        $this->postJson('examiner/submit-exam', $this->fcsPayload('viva'))->assertStatus(201);

        $this->postJson('examiner/fcs-resubmit/10', [
            'form_type' => 'viva', 'group_id' => 3, 'station_id' => 1, 'question_marks' => [1, 2, 3], 'total_marks' => 6,
        ])->assertOk()->assertJson(['status' => 'success']);

        $this->assertSame('[1,2,3]', DB::table('cardiothoracic_results')->where('exam_format', 'viva')->value('question_mark'));
        $this->assertSame('[3,4,5]', DB::table('cardiothoracic_results')->where('exam_format', 'clinical')->value('question_mark'));
    }

    public function test_fcs_edit_without_marks_or_without_an_existing_mark_is_rejected(): void
    {
        $this->postJson('examiner/fcs-resubmit/10', ['form_type' => 'viva', 'question_marks' => [1]])
            ->assertStatus(422)->assertJson(['status' => 'invalid']);

        $this->postJson('examiner/fcs-resubmit/10', ['form_type' => 'viva'])
            ->assertStatus(422)->assertJson(['status' => 'invalid']);
    }

    public function test_manifest_lists_the_profile_pages_and_the_view_and_resubmit_pages_for_saved_results(): void
    {
        $this->postJson('examiner/examiner_form', $this->flatPayload(['overall' => 'pass']))->assertStatus(201);
        $this->postJson('examiner/submit-exam', $this->fcsPayload('clinical'))->assertStatus(201);
        $this->postJson('examiner/submit-exam', $this->fcsPayload('clinical', ['group_id' => 17, 'candidate_id' => 11]))->assertStatus(201); // sandbox

        $pages = array_map(fn ($u) => parse_url($u, PHP_URL_PATH), $this->getJson('examiner/offline/manifest')->assertOk()->json('pages'));

        foreach (['/examiner/profile_settings', '/examiner/edit_info/5',
                  '/examiner/resubmit/10/1', '/examiner/view_results/10/1',
                  '/examiner/fcs-resubmit/10', '/examiner/fcs-resubmit/10/clinical', '/examiner/view_fcs_results/10'] as $path) {
            $this->assertContains($path, $pages);
        }
        $this->assertNotContains('/examiner/fcs-resubmit/10/viva', $pages);   // no viva mark yet
        $this->assertNotContains('/examiner/fcs-resubmit/11', $pages);        // sandbox marks have no edit pages
        $this->assertSame(count($pages), count(array_unique($pages)));
    }

    // ── Offline support endpoints ───────────────────────────────────────

    public function test_token_endpoint_returns_a_fresh_token_and_the_user_and_is_not_cacheable(): void
    {
        $response = $this->withSession(['seed' => 1])->getJson('examiner/offline/token')->assertOk();

        $response->assertJson(['user_id' => 1]);
        $this->assertNotEmpty($response->json('token'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_manifest_lists_marking_pages_and_only_the_candidate_lists_that_exist(): void
    {
        $manifest = $this->getJson('examiner/offline/manifest')->assertOk()->json();

        $this->assertSame(1, $manifest['user_id']);

        $pages = array_map(fn ($u) => parse_url($u, PHP_URL_PATH), $manifest['pages']);
        foreach (['/examiner/dashboard', '/examiner/examiner_form', '/examiner/general_surgery',
                  '/examiner/results', '/examiner/cardiothoracic', '/examiner/cardiothoracic/clinical', '/examiner/paediatric_orthopaedics/viva'] as $path) {
            $this->assertContains($path, $pages);
        }
        // 4 fixed pages + profile (2) + 8 specialties x (selection, clinical, viva)
        $this->assertCount(30, $pages);

        $data = array_map(fn ($u) => parse_url($u, PHP_URL_PATH), $manifest['data']);
        sort($data);
        $this->assertSame([
            '/get-exam-candidates/cardiothoracic/3',
            '/get-gs-candidates/3',
            '/get-mcs-candidates/4',
        ], $data);
    }

    public function test_manifest_has_no_candidate_urls_without_a_current_exam_year(): void
    {
        DB::table('years')->delete();

        $this->getJson('examiner/offline/manifest')->assertOk()->assertJson(['data' => []]);
    }

    // ── Fixtures ────────────────────────────────────────────────────────

    private function flatPayload(array $overrides = []): array
    {
        return array_merge([
            'candidate_id'   => 10,
            'group_id'       => 3,
            'station_id'     => 1,
            'question_marks' => [4, 5.5],
            'total_marks'    => 9.5,
            'remarks'        => 'Good',
        ], $overrides);
    }

    private function fcsPayload(string $format, array $overrides = []): array
    {
        return array_merge([
            'exam_type'            => 'cardiothoracic',
            'form_type'            => $format,
            'candidate_id'         => 10,
            'group_id'             => 3,
            'station_id'           => 1,
            'question_marks_case1' => [3, 4],
            'question_marks_case2' => [5],
            'total_marks'          => 12,
            'remarks'              => '',
        ], $overrides);
    }

    private function seedBaseData(): void
    {
        DB::table('users')->insert(['id' => 1, 'name' => 'Dr Examiner', 'email' => 'e@example.org', 'password' => 'x']);
        DB::table('examiners')->insert(['id' => $this->examinerId, 'user_id' => 1]);
        DB::table('years')->insert(['id' => 2, 'year_name' => '2026']);
        DB::table('examiners_groups')->insert([['id' => 3, 'group_name' => 'A'], ['id' => 4, 'group_name' => 'B'], ['id' => 17, 'group_name' => 'TEST-PART1']]);

        DB::table('candidates')->insert([
            ['id' => 10, 'programme_id' => 1, 'group_id' => 3, 'exam_year' => '2026', 'entry_number' => 'PEN-1'],
            ['id' => 11, 'programme_id' => 2, 'group_id' => 3, 'exam_year' => '2026', 'entry_number' => 'PEN-2'],
            ['id' => 12, 'programme_id' => 10, 'group_id' => 4, 'exam_year' => '2026', 'entry_number' => 'PEN-3'],
            ['id' => 13, 'programme_id' => 1, 'group_id' => 4, 'exam_year' => '2025', 'entry_number' => 'PEN-4'], // other year
            ['id' => 14, 'programme_id' => 1, 'group_id' => null, 'exam_year' => '2026', 'entry_number' => 'PEN-5'], // ungrouped
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('users', function ($t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('password')->nullable();
            $t->string('remember_token')->nullable();
            $t->timestamps();
        });
        Schema::create('examiners', function ($t) {
            $t->increments('id');
            $t->integer('user_id');
        });
        Schema::create('years', function ($t) {
            $t->increments('id');
            $t->string('year_name');
        });
        Schema::create('examiners_groups', function ($t) {
            $t->increments('id');
            $t->string('group_name');
        });
        Schema::create('candidates', function ($t) {
            $t->increments('id');
            $t->integer('programme_id')->nullable();
            $t->integer('group_id')->nullable();
            $t->string('exam_year')->nullable();
            $t->string('candidate_id')->nullable();
            $t->string('entry_number')->nullable();
        });

        $resultTables = ['mcs_results', 'gs_results', 'cardiothoracic_results'];
        foreach (array_merge($resultTables, array_map(fn ($t) => "test_$t", $resultTables)) as $table) {
            Schema::create($table, function ($t) use ($table) {
                $t->increments('id');
                $t->integer('candidate_id');
                $t->integer('examiner_id');
                $t->integer('station_id');
                $t->integer('group_id')->nullable();
                $t->text('question_mark')->nullable();
                $t->decimal('total', 8, 2)->nullable();
                $t->string('remarks')->nullable();
                $t->integer('exam_year');
                if (str_ends_with($table, 'mcs_results')) $t->string('overall')->nullable();
                if (str_ends_with($table, 'cardiothoracic_results')) $t->string('exam_format')->nullable();
                $t->timestamps();
            });
        }
    }
}

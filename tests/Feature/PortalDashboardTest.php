<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PortalData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The Candidate, Member, Programme Director and Country Representative dashboards: that each person sees their own
 * records and only what their role oversees, and that the role guard lets through only someone working as that role.
 *
 * Uses an in-memory SQLite database with just the tables these pages read.
 */
class PortalDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createSchema();

        DB::table('countries')->insert([['id' => 1, 'country_name' => 'Uganda'], ['id' => 2, 'country_name' => 'Kenya']]);
        DB::table('programmes')->insert([['id' => 1, 'name' => 'MCS'], ['id' => 2, 'name' => 'FCS General Surgery']]);
        DB::table('hospitals')->insert([
            ['id' => 1, 'name' => 'Mulago', 'country_id' => 1, 'status' => 1, 'is_deleted' => 0],
            ['id' => 2, 'name' => 'Kenyatta', 'country_id' => 2, 'status' => 1, 'is_deleted' => 0],
            ['id' => 3, 'name' => 'Old Clinic', 'country_id' => 1, 'status' => 0, 'is_deleted' => 1],
        ]);
        DB::table('hospital_programmes')->insert([
            ['hospital_id' => 1, 'programme_id' => 1, 'is_delete' => 0, 'status' => 'Active', 'expiry_date' => '2027-12-18'],
            ['hospital_id' => 1, 'programme_id' => 2, 'is_delete' => 0, 'status' => 'Expired', 'expiry_date' => '2015-11-01'],
            ['hospital_id' => 1, 'programme_id' => 2, 'is_delete' => 1, 'status' => 'Active', 'expiry_date' => null],
        ]);
        $this->trainee('Joan', 'Babalanda', 1, 1, 1, 'Active', 'Female');
        $this->trainee('Peter', 'Okello', 1, 1, 2, 'Active', 'Male');
        $this->trainee('Old', 'Timer', 1, 1, 1, 'Completed', 'Male');
        $this->trainee('Gone', 'Quiet', 1, 1, 1, 'Inactive', 'Female');
        $this->trainee('Wanjiru', 'Kamau', 2, 2, 1, 'Active', 'Female');

        $this->user(10, 'Dr Director', 4);
        $this->user(11, 'Rep Uganda', 5);
        $this->user(12, 'Cand Idate', 3);
        $this->user(13, 'Mem Ber', 8);
        DB::table('programme_directors')->insert(['user_id' => 10, 'hospital_id' => 1]);
        DB::table('country_reps')->insert(['user_id' => 11, 'country_id' => 1]);
    }

    private function user(int $id, string $name, int $role, bool $active = true): void
    {
        DB::table('users')->insert(['id' => $id, 'name' => $name, 'email' => "u$id@rcsi.com", 'password' => 'x', 'user_type' => $role, 'is_deleted' => 0]);
        DB::table('user_roles')->insert(['user_id' => $id, 'role_type' => $role, 'is_active' => $active ? 1 : 0]);
    }

    private function trainee(string $first, string $last, int $country, int $hospital, int $programme, string $status, string $gender): void
    {
        DB::table('trainees')->insert(['firstname' => $first, 'lastname' => $last, 'country_id' => $country, 'hospital_id' => $hospital, 'programme_id' => $programme, 'status' => $status, 'gender' => $gender, 'admission_year' => '2024']);
    }

    public function test_programme_director_sees_only_their_hospital_with_current_trainees(): void
    {
        $h = (new PortalData)->programmeDirector(10)['hospitals'];

        $this->assertCount(1, $h);
        $mulago = $h->first();
        $this->assertSame('Mulago', $mulago->name);
        $this->assertSame(['Joan Babalanda', 'Peter Okello'], $mulago->trainees->pluck('full_name')->sort()->values()->all());
        $this->assertSame(1, $mulago->completed_count);
        // Soft-deleted accreditation rows are left out (listed by programme name); an expired one is shown as expired.
        $this->assertSame(['FCS General Surgery' => 'Expired', 'MCS' => 'Active'], $mulago->programmes->pluck('status', 'name')->all());
        $this->assertSame(1, $mulago->by_programme['MCS']);
    }

    public function test_programme_director_with_no_hospital_gets_an_empty_page_not_an_error(): void
    {
        $this->assertCount(0, (new PortalData)->programmeDirector(11)['hospitals']);
    }

    public function test_country_rep_sees_the_numbers_of_their_own_country_only(): void
    {
        DB::table('candidates')->insert([['user_id' => 12, 'country_id' => 1, 'exam_year' => date('Y')], ['user_id' => null, 'country_id' => 2, 'exam_year' => date('Y')]]);
        DB::table('fellows')->insert([['country_id' => 1, 'status' => 'Active'], ['country_id' => 1, 'status' => 'Deceased'], ['country_id' => 2, 'status' => 'Active']]);
        DB::table('members')->insert([['country_id' => 1, 'is_deleted' => '0'], ['country_id' => 1, 'is_deleted' => '1']]);

        $countries = (new PortalData)->countryRep(11)['countries'];

        $this->assertSame(['Uganda'], $countries->pluck('country_name')->all());
        $this->assertSame(
            ['trainees_active' => 2, 'trainees_completed' => 1, 'candidates' => 1, 'fellows' => 1, 'members' => 1, 'hospitals' => 1],
            $countries->first()->counts
        );
        $this->assertSame(1, (int) $countries->first()->by_programme->firstWhere('programme_name', 'MCS')->female);
        // The list of current trainees is that country's only: no Completed / Inactive ones, none from Kenya.
        $this->assertSame(['Joan Babalanda', 'Peter Okello'], $countries->first()->trainees->pluck('full_name')->sort()->values()->all());
        // Hospitals of that country (not the deleted one), each with its director and current trainees.
        $mulago = $countries->first()->hospitals->firstWhere('name', 'Mulago');
        $this->assertSame('Dr Director', $mulago->directors);
        $this->assertSame(2, $mulago->active_trainees);
        $this->assertNull($countries->first()->hospitals->firstWhere('name', 'Kenyatta'));
    }

    public function test_candidate_sees_their_own_entries_newest_first_with_fee_status(): void
    {
        DB::table('candidates')->insert([
            ['id' => 1, 'user_id' => 12, 'firstname' => 'Cand', 'lastname' => 'Idate', 'exam_year' => 2024, 'programme_id' => 1, 'fee_paid' => 'No', 'invoice_status' => 'Pending'],
            ['id' => 2, 'user_id' => 12, 'firstname' => 'Cand', 'lastname' => 'Idate', 'exam_year' => 2025, 'programme_id' => 1, 'fee_paid' => 'Yes', 'invoice_status' => 'Complete'],
            ['id' => 3, 'user_id' => 99, 'firstname' => 'Someone', 'lastname' => 'Else', 'exam_year' => 2025, 'programme_id' => 1, 'fee_paid' => 'No', 'invoice_status' => 'Sent'],
        ]);

        $r = (new PortalData)->candidate(12);

        $this->assertSame([2, 1], $r['entries']->pluck('id')->all());
        $this->assertSame('Cand Idate', $r['current']->full_name);
        $this->assertSame(['Paid', 'Pending'], $r['entries']->pluck('fee_status')->all());
        $this->assertNull((new PortalData)->candidate(500)['current']);
    }

    public function test_member_sees_their_own_record_and_not_a_removed_one(): void
    {
        DB::table('categories')->insert(['id' => 1, 'category_name' => 'Honorary Member']);
        DB::table('members')->insert([
            ['id' => 1, 'user_id' => 13, 'firstname' => 'Mem', 'lastname' => 'Ber', 'category_id' => 1, 'country_id' => 1, 'status' => 'Active', 'is_deleted' => '0'],
        ]);
        DB::table('members')->insert(['id' => 2, 'user_id' => 13, 'firstname' => 'Removed', 'lastname' => 'Row', 'is_deleted' => '1']);

        $r = (new PortalData)->member(13);

        $this->assertCount(1, $r['records']);
        $this->assertSame('Mem Ber', $r['current']->full_name);
        $this->assertSame('Honorary Member', $r['current']->category_name);
        $this->assertSame('Uganda', $r['current']->country_name);
    }

    public function test_the_guard_lets_through_only_someone_working_as_that_role(): void
    {
        Route::get('portal-guard-test/{role}', fn () => 'in')->middleware(['web', 'portal:5'])->where('role', '.*');
        $rep = User::find(11);

        $this->get('portal-guard-test/x')->assertRedirect(''); // not signed in

        $this->actingAs($rep)->withSession(['active_role' => 5])->get('portal-guard-test/x')->assertOk()->assertSee('in');
        $this->actingAs($rep)->withSession(['active_role' => 4])->get('portal-guard-test/x')->assertRedirect(''); // working as another role
        $this->actingAs(User::find(10))->withSession(['active_role' => 5])->get('portal-guard-test/x')->assertRedirect(''); // not a rep at all

        DB::table('user_roles')->where('user_id', 11)->update(['is_active' => 0]); // retired rep
        $this->actingAs(User::find(11))->withSession(['active_role' => 5])->get('portal-guard-test/x')->assertRedirect('');
    }

    public function test_every_role_has_a_dashboard_route_the_login_redirect_can_reach(): void
    {
        foreach (['candidate', 'programme-director', 'country-rep', 'member', 'fellow', 'trainee', 'admin'] as $prefix) {
            $this->assertNotNull(Route::getRoutes()->match(\Illuminate\Http\Request::create("/$prefix/dashboard"))->getName() ?? 'unnamed', $prefix);
        }
    }

    private function createSchema(): void
    {
        Schema::create('users', function ($t) {
            $t->increments('id'); $t->string('name'); $t->string('email'); $t->string('password'); $t->integer('user_type')->default(2);
            $t->integer('is_deleted')->default(0); $t->rememberToken(); $t->timestamps();
        });
        Schema::create('user_roles', function ($t) { $t->increments('id'); $t->integer('user_id'); $t->integer('role_type'); $t->integer('is_active')->default(1); $t->timestamps(); });
        Schema::create('countries', function ($t) { $t->increments('id'); $t->string('country_name'); });
        Schema::create('programmes', function ($t) { $t->increments('id'); $t->string('name'); });
        Schema::create('categories', function ($t) { $t->increments('id'); $t->string('category_name'); });
        Schema::create('hospitals', function ($t) { $t->increments('id'); $t->string('name'); $t->integer('country_id')->nullable(); $t->integer('status')->default(1); $t->integer('is_deleted')->default(0); });
        Schema::create('hospital_programmes', function ($t) {
            $t->increments('id'); $t->integer('hospital_id'); $t->integer('programme_id'); $t->integer('is_delete')->default(0);
            $t->string('status')->default('Active'); $t->date('accredited_date')->nullable(); $t->date('expiry_date')->nullable();
        });
        Schema::create('trainees', function ($t) {
            $t->increments('id'); $t->integer('user_id')->nullable(); $t->string('entry_number')->nullable(); $t->string('firstname')->nullable(); $t->string('middlename')->nullable(); $t->string('lastname')->nullable();
            $t->string('gender')->nullable(); $t->integer('programme_id')->nullable(); $t->integer('hospital_id')->nullable(); $t->integer('country_id')->nullable();
            $t->string('admission_year')->nullable(); $t->integer('training_year')->nullable(); $t->string('status')->nullable();
        });
        Schema::create('candidates', function ($t) {
            $t->increments('id'); $t->integer('user_id')->nullable(); $t->string('entry_number')->nullable(); $t->string('exam_number')->nullable(); $t->string('candidate_id')->nullable();
            $t->string('firstname')->nullable(); $t->string('middlename')->nullable(); $t->string('lastname')->nullable(); $t->string('personal_email')->nullable();
            $t->integer('programme_id')->nullable(); $t->integer('hospital_id')->nullable(); $t->integer('country_id')->nullable(); $t->integer('exam_year')->nullable(); $t->integer('admission_year')->nullable();
            $t->string('invoice_status')->nullable(); $t->string('fee_paid')->nullable();
        });
        Schema::create('fellows', function ($t) { $t->increments('id'); $t->integer('user_id')->nullable(); $t->integer('country_id')->nullable(); $t->string('status')->nullable(); });
        Schema::create('members', function ($t) {
            $t->increments('id'); $t->integer('user_id')->nullable(); $t->integer('category_id')->nullable(); $t->integer('country_id')->nullable(); $t->string('member_id_number')->nullable();
            $t->string('firstname')->nullable(); $t->string('middlename')->nullable(); $t->string('lastname')->nullable(); $t->string('personal_email')->nullable(); $t->string('phone_number')->nullable();
            $t->string('address')->nullable(); $t->string('membership_year')->nullable(); $t->string('admission_year')->nullable(); $t->string('status')->nullable(); $t->string('is_deleted')->default('0');
        });
        Schema::create('programme_directors', function ($t) { $t->increments('id'); $t->integer('user_id'); $t->integer('hospital_id')->nullable(); $t->integer('programme_id')->nullable(); });
        Schema::create('country_reps', function ($t) { $t->increments('id'); $t->integer('user_id'); $t->integer('country_id')->nullable(); });
    }
}

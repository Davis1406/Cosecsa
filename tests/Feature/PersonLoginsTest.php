<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PersonLogins;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Signing in with the Fellowship ID while picking the Examiner role: find the person's login that holds the role. */
class PersonLoginsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email'); $t->string('password'); $t->tinyInteger('is_deleted')->default(0); $t->integer('user_type')->nullable(); $t->timestamps();
        });
        Schema::create('user_roles', fn (Blueprint $t) => [$t->id(), $t->unsignedBigInteger('user_id'), $t->integer('role_type'), $t->tinyInteger('is_active')->default(1), $t->timestamps()]);
        foreach (['fellows', 'trainees', 'candidates', 'members'] as $table) {
            Schema::create($table, fn (Blueprint $t) => [$t->id(), $t->unsignedBigInteger('user_id')->nullable(), $t->string('personal_email')->nullable()]);
        }
        $h = Hash::make('Shin99');
        DB::table('users')->insert([
            ['id' => 13673, 'name' => 'Dr Patricia Lahliwe SHINONDO', 'email' => 'patty@yahoo.com', 'password' => $h, 'user_type' => 7],
            ['id' => 6986, 'name' => 'Dr Patricia Lahliwe SHINONDO', 'email' => 'patty@gmail.com', 'password' => $h, 'user_type' => 9],
            ['id' => 99, 'name' => 'Someone Else', 'email' => 'patty@gmail.com', 'password' => $h, 'user_type' => 9],
        ]);
        DB::table('user_roles')->insert([['user_id' => 13673, 'role_type' => 7], ['user_id' => 6986, 'role_type' => 9], ['user_id' => 99, 'role_type' => 9]]);
        DB::table('fellows')->insert(['user_id' => 13673, 'personal_email' => 'Patty@Gmail.com']);
    }

    public function test_the_fellow_login_hands_over_to_the_examiner_login_for_the_examiner_role(): void
    {
        $fellowLogin = User::find(13673);

        $this->assertSame(6986, PersonLogins::loginHoldingRole($fellowLogin, 9, 'Shin99')?->id);
    }

    public function test_it_works_the_other_way_round_too(): void
    {
        $this->assertSame(13673, PersonLogins::loginHoldingRole(User::find(6986), 7, 'Shin99')?->id);
    }

    public function test_the_other_login_must_accept_the_password_that_was_typed(): void
    {
        DB::table('users')->where('id', 6986)->update(['password' => Hash::make('different')]);

        $this->assertNull(PersonLogins::loginHoldingRole(User::find(13673), 9, 'Shin99'));
    }

    public function test_a_different_person_on_the_same_email_is_never_picked(): void
    {
        DB::table('users')->where('id', 6986)->delete();

        $this->assertNull(PersonLogins::loginHoldingRole(User::find(13673), 9, 'Shin99')); // login 99 has the role and the password, but is someone else
    }

    public function test_nothing_is_found_when_no_other_login_holds_the_role(): void
    {
        $this->assertNull(PersonLogins::loginHoldingRole(User::find(13673), 4, 'Shin99'));
    }
}

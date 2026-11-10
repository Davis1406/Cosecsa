<?php

namespace Tests\Feature;

use App\Http\Middleware\PermissionMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/** Building a report (a POST) needs only reports.view, so view-only roles are not bounced to the dashboard. */
class ReportsPermissionTest extends TestCase
{
    private function run_as(array $permissions, string $method, string $uri)
    {
        $user = new class($permissions) extends User {
            public array $perms;
            public function __construct(array $perms = []) { $this->perms = $perms; }
            public function hasPermission(string $key): bool { return in_array($key, $this->perms, true); }
            public function isSuperAdmin(): bool { return false; }
        };
        Auth::setUser($user);

        return (new PermissionMiddleware())->handle(Request::create($uri, $method), fn () => response('ok'));
    }

    public function test_view_only_role_can_generate_a_report(): void
    {
        $this->assertSame('ok', $this->run_as(['reports.view'], 'POST', '/admin/reports/generate')->getContent());
    }

    public function test_role_without_reports_view_is_redirected(): void
    {
        $this->assertTrue($this->run_as(['fellows.view'], 'POST', '/admin/reports/generate')->isRedirection());
        $this->assertTrue($this->run_as(['fellows.view'], 'GET', '/admin/reports')->isRedirection());
    }

    public function test_other_posts_still_need_manage(): void
    {
        $this->assertTrue($this->run_as(['fellows.view'], 'POST', '/admin/associates/fellows/add')->isRedirection());
        $this->assertSame('ok', $this->run_as(['fellows.manage'], 'POST', '/admin/associates/fellows/add')->getContent());
    }

    public function test_the_users_directory_needs_admin_users_view_to_look_and_manage_to_change(): void
    {
        $this->assertSame('ok', $this->run_as(['admin_users.view'], 'GET', '/admin/list/users')->getContent());
        $this->assertTrue($this->run_as(['admin_users.view'], 'POST', '/admin/list/users/5')->isRedirection());
        $this->assertSame('ok', $this->run_as(['admin_users.manage'], 'POST', '/admin/list/users/5')->getContent());
        $this->assertTrue($this->run_as(['fellows.manage'], 'GET', '/admin/list/users')->isRedirection());
    }
}

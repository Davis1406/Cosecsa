<?php

namespace Tests\Feature;

use App\Http\Middleware\PermissionMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/** Writing on a sent letter's PDF: opening the PDF needs letters.view, saving the writing needs letters.manage. */
class LetterPdfWriteTest extends TestCase
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

    public function test_the_writing_editor_needs_manage_but_the_pdf_can_be_opened_with_view(): void
    {
        $this->assertSame('ok', $this->run_as(['letters.view'], 'GET', '/admin/letters/sent/15/pdf-source')->getContent());
        $this->assertTrue($this->run_as(['letters.view'], 'POST', '/admin/letters/sent/15/pdf')->isRedirection());
        $this->assertSame('ok', $this->run_as(['letters.manage'], 'POST', '/admin/letters/sent/15/pdf')->getContent());
        $this->assertTrue($this->run_as(['fellows.manage'], 'POST', '/admin/letters/sent/15/pdf')->isRedirection());
    }
}

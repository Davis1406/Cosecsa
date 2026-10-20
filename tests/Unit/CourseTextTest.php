<?php

namespace Tests\Unit;

use App\Support\CourseText;
use PHPUnit\Framework\TestCase;

class CourseTextTest extends TestCase
{
    private const PAGE = <<<'HTML'
<div class="app"><div class="hero"><div class="k">Module 1 · Welcome</div><h1>Welcome, examiner</h1><p class="l">Lead &amp; more</p>
<div class="prog"><span id="pc">0 of 2 topics read</span><span>Saved to your account</span></div></div>
<div class="topics">
<div class="topic" data-i="0"><button class="th" type="button"><span class="ic"><svg><use href="#i1"/></svg></span><span><b>About us</b><small>Who we are</small></span></button>
<div class="pn"><p>First <b>bold</b> paragraph.</p><ul><li>One</li><li>Two</li><li>One</li></ul><div class="callout">Note <a href="/x">here</a></div></div></div>
<div class="topic" data-i="1"><button class="th" type="button"><span><b>Second</b><small>Next</small></span></button>
<div class="pn"><table><tr><th>Head</th></tr><tr><td>Cell</td></tr></table><script>var x = "Not wording";</script><button type="button">Back</button></div></div>
</div></div>
HTML;

    public function test_finds_the_wording_and_nothing_else(): void
    {
        $texts = array_column(CourseText::fields(self::PAGE), 'text');

        $this->assertSame([
            'Module 1 · Welcome', 'Welcome, examiner', 'Lead & more', 'About us', 'Who we are',
            'First bold paragraph.', 'One', 'Two', 'One', 'Note here', 'Second', 'Next', 'Head', 'Cell',
        ], $texts);
    }

    public function test_keys_are_stable_and_identical_items_get_their_own_key(): void
    {
        $a = CourseText::fields(self::PAGE);
        $b = CourseText::fields(self::PAGE);
        $this->assertSame(array_column($a, 'key'), array_column($b, 'key'));
        $this->assertCount(count($a), array_unique(array_column($a, 'key')));
        $ones = array_values(array_filter($a, fn ($f) => $f['text'] === 'One'));
        $this->assertNotSame($ones[0]['key'], $ones[1]['key']);
    }

    public function test_apply_changes_only_the_edited_field_and_keeps_markup(): void
    {
        $fields = collect(CourseText::fields(self::PAGE));
        $second = $fields->filter(fn ($f) => $f['text'] === 'One')->values()[1];   // the second "One"
        $out = CourseText::apply(self::PAGE, [$second['key'] => 'Uno <b>bold</b>']);

        $this->assertStringContainsString('<li>One</li><li>Two</li><li>Uno <b>bold</b></li>', $out);
        $this->assertSame(self::PAGE, CourseText::apply(self::PAGE, []));
        $this->assertSame(self::PAGE, CourseText::apply(self::PAGE, ['nope#0' => 'x']));
        // everything outside the edited element is byte-for-byte the same
        $this->assertSame(strlen(self::PAGE) - strlen('One') + strlen('Uno <b>bold</b>'), strlen($out));
    }

    public function test_applying_every_original_back_is_a_no_op(): void
    {
        $over = [];
        foreach (CourseText::fields(self::PAGE) as $f) {
            $over[$f['key']] = $f['html'];
        }
        $this->assertSame(self::PAGE, CourseText::apply(self::PAGE, $over));
    }

    public function test_marking_tags_every_field_with_its_key_and_still_applies_overrides(): void
    {
        $fields = CourseText::fields(self::PAGE);
        $h1 = collect($fields)->firstWhere('tag', 'h1');
        $out = CourseText::apply(self::PAGE, [$h1['key'] => 'Hello'], true);

        $this->assertStringContainsString('<h1 data-cwkey="'.$h1['key'].'">Hello</h1>', $out);
        $this->assertSame(count($fields), substr_count($out, 'data-cwkey="'));
        // every key on the marked page is a key of the unmarked page (the preview and the editor agree)
        preg_match_all('/data-cwkey="([^"]+)"/', $out, $m);
        $this->assertSame(array_column($fields, 'key'), $m[1]);
        // attributes already on the tag are kept
        $this->assertStringContainsString('<p class="l" data-cwkey="', $out);
    }

    public function test_inline_markup_in_a_field_is_part_of_that_field(): void
    {
        $f = collect(CourseText::fields(self::PAGE))->firstWhere('text', 'First bold paragraph.');
        $this->assertSame('First <b>bold</b> paragraph.', $f['html']);
        $this->assertSame('Highlighted note', CourseText::kind(collect(CourseText::fields(self::PAGE))->firstWhere('text', 'Note here')));
    }
}

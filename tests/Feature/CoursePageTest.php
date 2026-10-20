<?php

namespace Tests\Feature;

use App\Support\CoursePage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/** The coded course pages as data: reading a page into a model and writing it back out unchanged, and rendering an edited model. */
class CoursePageTest extends TestCase
{
    private function page(): string
    {
        $img = asset('images/course');

        return <<<HTML
<div class="app">
<div class="bar0"><span class="brand"><img class="crest" alt="COSECSA crest" src="{$img}/cosecsa-crest.png">COSECSA · Examiner course</span></div>
<div class="hero"><svg class="lens" viewBox="0 0 200 200"><path d="M1 1"/></svg><div class="k">Module 9 · Test</div><h1>A <b>test</b> page</h1><p class="l">Intro text</p>
<div class="prog"><span id="pc">0 of 2 topics read</span><span>Saved on this device</span></div><div class="bar"><i id="fill"></i></div></div>
<div class="topics" id="topics">

<div class="topic" data-i="0"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i10"/></svg></span><span><b>First</b><small>One</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">Lead</p>
<p>Plain paragraph</p>
<h3>Heading</h3>
<ul class="ticks"><li>A</li><li>B</li></ul>
<div class="callout">Note</div>
<div class="scroll cs-card"><table><tr><th></th><th>X</th></tr><tr><td>r</td><td>c</td></tr></table></div>
<img class="pic" alt="A picture" src="{$img}/ab12.gif"></div></div>

<div class="topic" data-i="1" style="--a:#112233"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i3"/></svg></span><span><b>Second</b><small>Two</small></span><span class="ch"></span></button><div class="pn"><div class="stats"><div><b>15</b><span>things</span></div></div>
<ol class="nums"><li>One</li></ol></div></div>

</div>
<div class="done" id="done"><h2>You have read all 2 topics.</h2><p>Well done.</p><div class="label">Remember</div><ol><li>Be fair</li></ol></div>
</div>
HTML;
    }

    private function norm(string $h): string
    {
        return preg_replace('/\s+/', ' ', preg_replace('/>\s+</', '><', trim($h)));
    }

    public function test_a_page_is_read_into_a_model_with_typed_blocks(): void
    {
        $m = CoursePage::parse($this->page());

        $this->assertSame('Module 9 · Test', $m['hero']['k']);
        $this->assertSame('A <b>test</b> page', $m['hero']['title']);
        $this->assertStringContainsString('<svg class="lens"', $m['hero']['lens']);
        $this->assertCount(2, $m['topics']);
        $this->assertSame(['icon' => 'i10', 'color' => null, 'title' => 'First', 'subtitle' => 'One'], array_intersect_key($m['topics'][0], array_flip(['icon', 'color', 'title', 'subtitle'])));
        $this->assertSame('#112233', $m['topics'][1]['color']);
        $this->assertSame(['lead', 'paragraph', 'heading', 'list', 'callout', 'table', 'image'], array_column($m['topics'][0]['blocks'], 'type'));
        $this->assertSame(['special', 'list'], array_column($m['topics'][1]['blocks'], 'type'));
        $this->assertStringContainsString('{{img}}/ab12.gif', $m['topics'][0]['blocks'][6]['html']);   // no host name in the model
        $this->assertSame('<div class="label">Remember</div><ol><li>Be fair</li></ol>', $m['done']['extra']);
        $this->assertCount(9, array_unique(array_column(array_merge(...array_column($m['topics'], 'blocks')), 'id')));
    }

    public function test_the_model_renders_back_to_the_same_page(): void
    {
        $page = $this->page();

        $this->assertSame($this->norm($page), $this->norm(CoursePage::render(CoursePage::parse($page))));
    }

    public function test_an_edited_model_renders_the_changes_and_marks_blocks_for_the_preview(): void
    {
        $m = CoursePage::parse($this->page());
        $m['topics'][0]['icon'] = 'i5';
        $m['topics'][0]['color'] = '#ff0000';
        $m['topics'][0]['blocks'][1]['html'] = '<p>Reworded</p>';
        array_splice($m['topics'], 1, 1);                    // remove the second topic

        $html = CoursePage::render($m);
        $this->assertStringContainsString('<use href="#i5"/>', $html);
        $this->assertStringContainsString('style="--a:#ff0000"', $html);
        $this->assertStringContainsString('<p>Reworded</p>', $html);
        $this->assertStringContainsString('0 of 1 topics read', $html);   // the count follows the cards
        $this->assertStringNotContainsString('Second', $html);

        $marked = CoursePage::render($m, true);
        $this->assertStringContainsString('data-tid="t1"', $marked);
        $this->assertStringContainsString('<p data-bid="b2">Reworded</p>', $marked);
    }

    public function test_page_view_uses_the_saved_model_and_banner_colours(): void
    {
        View::share('errors', new ViewErrorBag);
        $m = CoursePage::parse($this->page());
        $m['colors'] = ['hero1' => '#112233', 'hero2' => '#445566'];

        $html = view('course.modules._page', [
            'page' => $m, 'pageMeta' => CoursePage::META['module-1-welcome'],
            'module' => ['id' => 1, 'title' => 'T', 'slug' => 's', 'type' => 'content', 'view' => 'module-1-welcome'],
            'modules' => [['id' => 1, 'title' => 'T', 'slug' => 's', 'completed' => false]], 'course' => ['title' => 'C', 'color' => '#000', 'logo_url' => null],
            'progress_percent' => 0, 'layout' => 'learning.examiner._standalone', 'topics_read' => [],
        ])->render();

        $this->assertStringContainsString('--hero1:#112233;--hero2:#445566;', $html);
        $this->assertStringContainsString('A <b>test</b> page', $html);
        $this->assertStringNotContainsString('{{img}}', $html);
    }

    public function test_markup_that_can_run_code_is_removed(): void
    {
        $dirty = '<p onclick="x()">Hi<script>alert(1)</script><a href="javascript:evil()">l</a><iframe src="//e"></iframe></p>';
        $clean = CoursePage::sanitize($dirty);

        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('javascript', $clean);
        $this->assertStringNotContainsString('iframe', $clean);
        $this->assertStringContainsString('Hi', $clean);
    }

    public function test_default_banner_colours_come_from_the_page_stylesheet(): void
    {
        $this->assertSame(['hero1' => '#0e281b', 'hero2' => '#1f6b4a'], CoursePage::defaultColors(CoursePage::META['module-1-welcome']));
        $this->assertSame(['hero1' => '#4f1213', 'hero2' => '#9b2a27'], CoursePage::defaultColors(CoursePage::META['module-6-fellowship']));
    }
}

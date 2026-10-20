<?php

namespace App\Support;

/**
 * Page model of a coded examiner-course page (resources/views/course/modules/*).
 *
 * Every coded page has the same skeleton — brand bar, banner (hero), topic cards, "you have read all" card —
 * so a page can be held as DATA and rendered by one template. parse() reads the markup of an existing page into
 * that model (this is how the pages are seeded); render() writes it back out as the SAME markup, so the
 * learner sees no difference until an admin changes the model.
 *
 * Model:
 *   hero   { lens: raw <svg>, k, title, lead }          (k/title/lead: inner HTML)
 *   colors { hero1, hero2 }                              (optional banner gradient)
 *   topics [ { id, icon: "i10", color: null|"#hex", title, subtitle, blocks: [ { id, type, html } ] } ]
 *   done   { title, text, extra }                      (extra: raw markup after the text, e.g. a "Remember" list)
 *
 * A block's html is the block's own markup (outerHTML). Types: heading, paragraph, lead, note, callout, list,
 * table, image, special. "special" is any designed block (card grids, stats, tabs, video, animations ...):
 * it is kept exactly and can be moved, copied, removed and reworded.
 * Image sources are stored with the {{img}} token so a model does not depend on the host name.
 */
class CoursePage
{
    private const VOID = ['br', 'img', 'hr', 'input', 'meta', 'link', 'source', 'use', 'path', 'circle', 'rect', 'line', 'polyline', 'polygon', 'ellipse', 'stop', 'col', 'track', 'wbr'];

    /** Layout settings of each coded page (the @extends line of its view). */
    public const META = [
        'module-1-welcome' => ['mod' => '1', 'slug' => 'module-1-welcome', 'key' => 'm1-seen', 'lite' => false, 'moduleJs' => false],
        'module-2-intro' => ['mod' => '2', 'slug' => 'module-2-intro', 'key' => 'm2-seen', 'lite' => false, 'moduleJs' => true],
        'module-3-conduct' => ['mod' => '3', 'slug' => 'module-3-conduct', 'key' => 'm3-seen', 'lite' => false, 'moduleJs' => false],
        'module-4-bias' => ['mod' => '4', 'slug' => 'module-4-bias', 'key' => 'm4-seen', 'lite' => false, 'moduleJs' => false],
        'module-5-online-osce' => ['mod' => '5', 'slug' => 'module-5-online-osce', 'key' => 'm5-seen', 'lite' => false, 'moduleJs' => true],
        'module-6-fellowship' => ['mod' => '6', 'slug' => 'module-6-fellowship', 'key' => 'm6-seen', 'lite' => false, 'moduleJs' => true],
        'module-7-marking' => ['mod' => '7', 'slug' => 'module-7-marking', 'key' => 'm7-seen', 'lite' => false, 'moduleJs' => true],
        'acknowledgements' => ['mod' => 'ack', 'slug' => 'acknowledgements', 'key' => 'ack-seen', 'lite' => false, 'moduleJs' => false],
    ];

    private const LIST_CLASSES = ['ticks', 'crosses', 'nums', 'pts', 'steps', 'bands', 'p'];

    // ── Reading a page ───────────────────────────────────────────────────────

    /** @return array the page model, or throws \RuntimeException when the markup is not a coded page. */
    public static function parse(string $html): array
    {
        $html = self::tokenise($html);
        $nodes = self::tree($html);
        $app = self::first($nodes, fn ($n) => $n['tag'] === 'div' && self::hasClass($n, 'app'));
        if (! $app) {
            throw new \RuntimeException('No .app wrapper.');
        }

        $hero = self::child($app, 'div', 'hero');
        $topicsNode = self::child($app, 'div', 'topics');
        $done = self::child($app, 'div', 'done');
        if (! $hero || ! $topicsNode || ! $done) {
            throw new \RuntimeException('Missing hero, topics or done card.');
        }

        $lens = self::child($hero, 'svg', 'lens');
        $model = [
            'hero' => [
                'lens' => $lens ? self::outer($html, $lens) : '',
                'k' => self::inner($html, self::child($hero, 'div', 'k')),
                'title' => self::inner($html, self::childTag($hero, 'h1')),
                'lead' => self::inner($html, self::child($hero, 'p', 'l')),
            ],
            'colors' => ['hero1' => null, 'hero2' => null],
            'topics' => [],
            'done' => self::parseDone($html, $done),
        ];

        $bid = 0;
        foreach ($topicsNode['children'] as $ti => $t) {
            if ($t['tag'] !== 'div' || ! self::hasClass($t, 'topic')) {
                continue;
            }
            $btn = self::childTag($t, 'button');
            $pn = self::child($t, 'div', 'pn');
            if (! $btn || ! $pn) {
                throw new \RuntimeException("Topic {$ti} has no header or body.");
            }
            $icon = preg_match('/<use href="#([a-z0-9]+)"/i', self::inner($html, $btn), $m) ? $m[1] : 'i1';
            $titleNode = self::findDesc($btn, 'b');
            $subNode = self::findDesc($btn, 'small');

            $topic = [
                'id' => 't'.(count($model['topics']) + 1),
                'icon' => $icon,
                'color' => preg_match('/--a:\s*(#[0-9a-f]{3,8})/i', $t['attrs'], $cm) ? $cm[1] : null,
                'title' => self::inner($html, $titleNode),
                'subtitle' => self::inner($html, $subNode),
                'blocks' => [],
            ];
            foreach ($pn['children'] as $b) {
                $topic['blocks'][] = ['id' => 'b'.(++$bid), 'type' => self::blockType($html, $b), 'html' => trim(self::outer($html, $b))];
            }
            $model['topics'][] = $topic;
        }

        return $model;
    }

    /** The "you have read all" card: its title and text, and whatever follows (a "Remember" list in some modules), kept as it is. */
    private static function parseDone(string $html, array $done): array
    {
        $h2 = self::childTag($done, 'h2');
        $p = self::childTag($done, 'p');
        $extraStart = $p ? $p['end'] : ($h2 ? $h2['end'] : $done['inner']);

        return [
            'title' => self::inner($html, $h2),
            'text' => self::inner($html, $p),
            'extra' => trim(substr($html, $extraStart, $done['close'] - $extraStart)),
        ];
    }

    private static function blockType(string $html, array $b): string
    {
        $tag = $b['tag'];
        if ($tag === 'h3' || $tag === 'h4') {
            return 'heading';
        }
        if ($tag === 'p') {
            if (self::hasClass($b, 'cs-lead') && self::classOf($b) === 'cs-lead') {
                return 'lead';
            }
            if (self::hasClass($b, 'note') && self::classOf($b) === 'note') {
                return 'note';
            }

            return trim($b['attrs']) === '' && self::noBlockChildren($b) ? 'paragraph' : 'special';
        }
        if ($tag === 'div' && self::hasClass($b, 'callout') && self::classOf($b) === 'callout') {
            return self::noBlockChildren($b) ? 'callout' : 'special';
        }
        if (($tag === 'ul' || $tag === 'ol') && self::isSimpleList($html, $b)) {
            return 'list';
        }
        if ($tag === 'table' || ($tag === 'div' && self::hasClass($b, 'scroll') && count($b['children']) === 1 && $b['children'][0]['tag'] === 'table')) {
            return 'table';
        }
        if ($tag === 'img' && self::hasClass($b, 'pic')) {
            return 'image';
        }
        if ($tag === 'figure' && ($img = self::findDesc($b, 'img')) && count($b['children']) <= 2) {
            return 'image';
        }

        return 'special';
    }

    private static function isSimpleList(string $html, array $n): bool
    {
        $cls = self::classOf($n);
        if ($cls !== '' && ! in_array($cls, self::LIST_CLASSES, true)) {
            return false;
        }
        foreach ($n['children'] as $li) {
            if ($li['tag'] !== 'li' || ! self::noBlockChildren($li)) {
                return false;
            }
        }

        return count($n['children']) > 0;
    }

    private static function noBlockChildren(array $n): bool
    {
        foreach ($n['children'] as $c) {
            if (! in_array($c['tag'], ['b', 'strong', 'i', 'em', 'u', 'a', 'br', 'sup', 'sub', 'span'], true)) {
                return false;
            }
        }

        return true;
    }

    // ── Writing a page ───────────────────────────────────────────────────────

    /** The page's markup (what a course page's `course-body` section holds). $mark adds data-tid / data-bid for the editor's preview. */
    public static function render(array $m, bool $mark = false): string
    {
        $topics = array_values($m['topics'] ?? []);
        $n = count($topics);
        $h = $m['hero'] ?? [];
        $img = asset('images/course');

        $out = '<div class="app">'."\n";
        $out .= '<div class="bar0"><span class="brand"><img class="crest" alt="COSECSA crest" src="'.asset('images/course/cosecsa-crest.png').'">COSECSA · Examiner course</span></div>'."\n";
        $out .= '<div class="hero">'.($h['lens'] ?? '').'<div class="k">'.($h['k'] ?? '').'</div><h1>'.($h['title'] ?? '').'</h1><p class="l">'.($h['lead'] ?? '').'</p>'."\n";
        $out .= '<div class="prog"><span id="pc">0 of '.$n.' topics read</span><span>Saved on this device</span></div><div class="bar"><i id="fill"></i></div></div>'."\n";
        $out .= '<div class="topics" id="topics">'."\n";

        foreach ($topics as $i => $t) {
            $style = ! empty($t['color']) && preg_match('/^#[0-9a-f]{3,8}$/i', $t['color']) ? ' style="--a:'.$t['color'].'"' : '';
            $tid = $mark ? ' data-tid="'.e($t['id'] ?? 't'.($i + 1)).'"' : '';
            $out .= "\n".'<div class="topic" data-i="'.$i.'"'.$style.$tid.'><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#'.e($t['icon'] ?? 'i1').'"/></svg></span><span><b>'.($t['title'] ?? '').'</b><small>'.($t['subtitle'] ?? '').'</small></span><span class="ch"></span></button><div class="pn">';
            $blocks = [];
            foreach ($t['blocks'] ?? [] as $b) {
                $bh = str_replace('{{img}}', $img, (string) ($b['html'] ?? ''));
                if ($mark && ! empty($b['id'])) {
                    $bh = preg_replace('/^<([a-zA-Z][a-zA-Z0-9]*)/', '<$1 data-bid="'.e($b['id']).'"', $bh, 1);
                }
                $blocks[] = $bh;
            }
            $out .= implode("\n", $blocks).'</div></div>'."\n";
        }

        $d = $m['done'] ?? [];
        $out .= "\n".'</div>'."\n".'<div class="done" id="done"><h2>'.($d['title'] ?? '').'</h2><p>'.($d['text'] ?? '').'</p>'.($d['extra'] ?? '').'</div>'."\n".'</div>';

        return str_replace('{{img}}', $img, $out);
    }

    /** Inline CSS variables for the page wrapper (banner colours). */
    public static function wrapStyle(array $m): string
    {
        $c = $m['colors'] ?? [];
        $css = '';
        foreach (['hero1', 'hero2'] as $k) {
            if (! empty($c[$k]) && preg_match('/^#[0-9a-f]{3,8}$/i', $c[$k])) {
                $css .= "--{$k}:{$c[$k]};";
            }
        }

        return $css;
    }

    // ── Showing a page ───────────────────────────────────────────────────────

    /**
     * The view that shows a module's page: its saved page model when it has one, else its own coded view.
     * $data is the module payload from cosecsa-api (it carries `page`).
     */
    public static function view(array $data, array $extra = [])
    {
        $viewName = $data['module']['view'] ?? null;
        if (! empty($data['page']) && isset(self::META[$viewName])) {
            return view('course.modules._page', $data + $extra + ['pageMeta' => self::META[$viewName]]);
        }

        return view("course.modules.{$viewName}", $data + $extra);
    }

    /** The same clean-up the API applies: markup stays as designed, nothing that can run code. */
    public static function sanitize($html): string
    {
        $html = (string) $html;
        $html = preg_replace('#<\s*(script|style|iframe|object|embed|link|meta|base|form)\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html);
        $html = preg_replace('#<\s*/?\s*(script|style|iframe|object|embed|link|meta|base|form)\b[^>]*>#i', '', $html);
        $html = preg_replace('#\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);

        return preg_replace('#(href|src|xlink:href)\s*=\s*("|\')\s*(javascript|vbscript|data\s*:\s*text/html)[^"\']*\2#i', '$1=$2#$2', $html);
    }

    /** Banner colours of each page as the stylesheet defines them (what "automatic" means in the editor). */
    public static function defaultColors(array $meta): array
    {
        $css = @file_get_contents(public_path('css/course/modules/'.$meta['slug'].'.css')) ?: '';
        if (preg_match('/\.mod-'.preg_quote($meta['mod'], '/').'\s*\{[^}]*--hero1:\s*(#[0-9a-f]{3,8})[^}]*--hero2:\s*(#[0-9a-f]{3,8})/i', $css, $m)) {
            return ['hero1' => $m[1], 'hero2' => $m[2]];
        }

        return ['hero1' => '#1f6b4a', 'hero2' => '#0e281b'];
    }

    // ── Tree of elements ─────────────────────────────────────────────────────

    /** Image sources and absolute asset URLs become tokens, so a model is the same on every host. */
    private static function tokenise(string $html): string
    {
        return str_replace(asset('images/course'), '{{img}}', $html);
    }

    /**
     * @return array<int, array{tag:string, attrs:string, open:int, inner:int, close:int, end:int, children:array}>
     */
    private static function tree(string $html): array
    {
        preg_match_all('/<(\/?)([a-zA-Z][a-zA-Z0-9]*)((?:\s[^>]*?)?)(\/?)>/s', $html, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        $root = ['children' => []];
        $stack = [&$root];

        foreach ($m as $t) {
            [$full, $pos] = $t[0];
            $closing = $t[1][0] === '/';
            $tag = strtolower($t[2][0]);
            $attrs = $t[3][0];
            $end = $pos + strlen($full);
            $parent = &$stack[count($stack) - 1];

            if (! $closing && (in_array($tag, self::VOID, true) || $t[4][0] === '/')) {
                $parent['children'][] = ['tag' => $tag, 'attrs' => $attrs, 'open' => $pos, 'inner' => $end, 'close' => $end, 'end' => $end, 'children' => []];

                continue;
            }
            if (! $closing) {
                $parent['children'][] = ['tag' => $tag, 'attrs' => $attrs, 'open' => $pos, 'inner' => $end, 'close' => $end, 'end' => $end, 'children' => []];
                $idx = count($parent['children']) - 1;
                $stack[] = &$parent['children'][$idx];

                continue;
            }
            // closing tag: pop to the matching opener
            for ($i = count($stack) - 1; $i > 0; $i--) {
                if ($stack[$i]['tag'] === $tag) {
                    $stack[$i]['close'] = $pos;
                    $stack[$i]['end'] = $end;
                    array_splice($stack, $i);

                    break;
                }
            }
        }

        return $root['children'];
    }

    private static function hasClass(array $n, string $c): bool
    {
        return (bool) preg_match('/class\s*=\s*"[^"]*(?<![\w-])'.preg_quote($c, '/').'(?![\w-])[^"]*"/', $n['attrs']);
    }

    private static function classOf(array $n): string
    {
        return preg_match('/class\s*=\s*"([^"]*)"/', $n['attrs'], $m) ? trim($m[1]) : '';
    }

    private static function first(array $nodes, callable $f): ?array
    {
        foreach ($nodes as $n) {
            if ($f($n)) {
                return $n;
            }
            if ($r = self::first($n['children'], $f)) {
                return $r;
            }
        }

        return null;
    }

    private static function child(array $n, string $tag, string $class): ?array
    {
        foreach ($n['children'] as $c) {
            if ($c['tag'] === $tag && self::hasClass($c, $class)) {
                return $c;
            }
        }

        return null;
    }

    private static function childTag(array $n, string $tag): ?array
    {
        foreach ($n['children'] as $c) {
            if ($c['tag'] === $tag) {
                return $c;
            }
        }

        return null;
    }

    private static function findDesc(array $n, string $tag): ?array
    {
        return self::first($n['children'], fn ($c) => $c['tag'] === $tag);
    }

    private static function inner(string $html, ?array $n): string
    {
        return $n ? trim(substr($html, $n['inner'], $n['close'] - $n['inner'])) : '';
    }

    private static function outer(string $html, array $n): string
    {
        return substr($html, $n['open'], $n['end'] - $n['open']);
    }
}

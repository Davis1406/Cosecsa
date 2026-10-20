<?php

namespace App\Support;

/**
 * Editable wording of the coded examiner-course pages (resources/views/course/modules/*).
 *
 * The pages are static HTML, so the wording an admin edits is stored as OVERRIDES (cosecsa-api,
 * lms_text_overrides) and applied when a page is shown. fields() finds every piece of wording in a
 * page's HTML — an element whose content is text with at most simple inline markup (bold, italic,
 * links) — and gives it a stable key built from its original content; apply() swaps in the overrides.
 * Both work on the same raw HTML, so the key an admin edited is the key the learner page applies.
 *
 * A key is sha1(tag|original) plus "#n" for the nth identical element, so changing the page layout
 * does not lose overrides; changing the original wording in the view makes an override stale
 * (it simply stops applying and the editor lists it).
 */
class CourseText
{
    /** Elements that are never wording. */
    private const SKIP_TAGS = ['script', 'style', 'svg', 'symbol', 'defs', 'title', 'option', 'select', 'textarea', 'use', 'path'];

    /** Void elements: no closing tag, never on the stack. */
    private const VOID = ['br', 'img', 'hr', 'input', 'meta', 'link', 'source', 'use', 'path', 'circle', 'rect', 'line', 'polyline', 'polygon', 'ellipse', 'stop', 'col', 'track', 'wbr'];

    /** Inline tags that may sit inside an editable element. */
    private const INLINE = ['b', 'strong', 'i', 'em', 'u', 'a', 'br', 'sup', 'sub'];

    /**
     * @return array<int, array{key:string, tag:string, class:string, topic:int, start:int, end:int, html:string, text:string}>
     *         start/end are the byte offsets of the element's inner HTML in $html
     */
    public static function fields(string $html): array
    {
        preg_match_all('/<(\/?)([a-zA-Z][a-zA-Z0-9]*)((?:\s[^>]*?)?)(\/?)>/s', $html, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        $stack = [];      // each: [tag, attrs, innerStart, hasBlockChild, skip]
        $cands = [];
        $topic = -1;
        $skipDepth = 0;

        foreach ($m as $t) {
            [$full, $fullPos] = $t[0];
            $closing = $t[1][0] === '/';
            $tag = strtolower($t[2][0]);
            $attrs = $t[3][0];
            $selfClosed = $t[4][0] === '/';
            $end = $fullPos + strlen($full);

            if (in_array($tag, self::VOID, true) || $selfClosed) {
                continue;
            }

            if (! $closing) {
                // An opening block tag means the element it sits in is not text-only.
                if ($stack && ! in_array($tag, self::INLINE, true)) {
                    $stack[count($stack) - 1][3] = true;
                }
                if (preg_match('/class\s*=\s*"[^"]*\btopic\b[^"]*"/', $attrs) && $tag === 'div') {
                    $topic++;
                }
                $skip = in_array($tag, self::SKIP_TAGS, true);
                $stack[] = [$tag, $attrs, $end, false, $skip];
                if ($skip) {
                    $skipDepth++;
                }

                continue;
            }

            // closing tag: find its opener
            $i = count($stack) - 1;
            while ($i >= 0 && $stack[$i][0] !== $tag) {
                $i--;
            }
            if ($i < 0) {
                continue;
            }
            [$otag, $oattrs, $innerStart, $hasBlock, $skip] = $stack[$i];
            array_splice($stack, $i);
            if ($skip) {
                $skipDepth--;
            }

            if ($skip || $skipDepth > 0 || $hasBlock) {
                continue;
            }

            $inner = substr($html, $innerStart, $fullPos - $innerStart);
            $text = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (! self::isWording($otag, $oattrs, $inner, $text)) {
                continue;
            }

            $cands[] = [
                'tag' => $otag,
                'class' => preg_match('/class\s*=\s*"([^"]*)"/', $oattrs, $cm) ? trim($cm[1]) : '',
                'topic' => $topic,
                'start' => $innerStart,
                'end' => $fullPos,
                'html' => trim($inner),
                'text' => $text,
                'inline' => in_array($otag, self::INLINE, true),
            ];
        }

        // A bold/italic/link that sits inside a field that is already wording is part of that field.
        $whole = array_values(array_filter($cands, fn ($c) => ! $c['inline']));
        $out = [];
        foreach ($cands as $c) {
            if ($c['inline'] && array_filter($whole, fn ($w) => $w['start'] <= $c['start'] && $w['end'] >= $c['end'])) {
                continue;
            }
            $out[] = $c;
        }
        usort($out, fn ($a, $b) => $a['start'] <=> $b['start']);

        $seen = [];
        foreach ($out as &$f) {
            $base = sha1($f['tag'].'|'.$f['html']);
            $n = $seen[$base] = ($seen[$base] ?? -1) + 1;
            $f['key'] = substr($base, 0, 16).'#'.$n;
            unset($f['inline']);
        }
        unset($f);

        return $out;
    }

    /** The page's HTML with the overrides (key => html) swapped in. Unknown keys are ignored. */
    public static function apply(string $html, array $overrides): string
    {
        if (! $overrides) {
            return $html;
        }

        $fields = array_reverse(self::fields($html));
        foreach ($fields as $f) {
            if (isset($overrides[$f['key']]) && is_string($overrides[$f['key']])) {
                $html = substr($html, 0, $f['start']).$overrides[$f['key']].substr($html, $f['end']);
            }
        }

        return $html;
    }

    /** A short human label for the kind of wording. */
    public static function kind(array $f): string
    {
        $c = ' '.$f['class'].' ';

        return match (true) {
            $f['tag'] === 'h1' => 'Page title',
            $f['tag'] === 'h2' || $f['tag'] === 'h3' || $f['tag'] === 'h4' => 'Heading',
            str_contains($c, ' cs-lead ') || str_contains($c, ' l ') => 'Lead text',
            str_contains($c, ' callout ') => 'Highlighted note',
            $f['tag'] === 'li' => 'List item',
            in_array($f['tag'], ['td', 'th'], true) => 'Table cell',
            $f['tag'] === 'small' => 'Subtitle',
            $f['tag'] === 'b' => 'Title',
            $f['tag'] === 'figcaption' => 'Caption',
            $f['tag'] === 'summary' => 'Expandable heading',
            str_contains($c, ' k ') => 'Label',
            default => 'Text',
        };
    }

    private static function isWording(string $tag, string $attrs, string $inner, string $text): bool
    {
        if ($tag === 'button' || $text === '' || ! preg_match('/\p{L}/u', $text)) {
            return false;
        }
        // Anything wired to the page's scripts is not wording (counters, quiz slots, ids).
        if (preg_match('/\sid\s*=/', $attrs) || preg_match('/\sdata-[a-z-]+\s*=/', $attrs) || str_contains($attrs, 'hidden')) {
            return false;
        }
        // Inner markup must be only simple inline tags.
        if (preg_match_all('/<\/?([a-zA-Z][a-zA-Z0-9]*)/', $inner, $tm)) {
            foreach ($tm[1] as $t) {
                if (! in_array(strtolower($t), self::INLINE, true)) {
                    return false;
                }
            }
        }
        // Counters and JS-filled labels such as "0 of 6 topics read" / "Saved to your account".
        if (preg_match('/^\d+ of \d+/', $text) || in_array($text, ['Saved to your account', 'Saved on this device'], true)) {
            return false;
        }

        return true;
    }
}

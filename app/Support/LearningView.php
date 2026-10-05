<?php

namespace App\Support;

use App\Services\ApiClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * View helpers for the Examiner Training (learning) pages. Block payloads come
 * from cosecsa-api with image URLs already resolved (`resolved_url`) and videos
 * as `video_url`.
 */
class LearningView
{
    /**
     * Whether the examiner menu shows the course link: only examiners who confirmed
     * attendance for this year (the API decides and enforces it on every request).
     * Shown if the API can't be reached, so an outage doesn't hide it for everyone.
     */
    public static function canAccess(int $userId): bool
    {
        return Cache::remember("learning_access_{$userId}", 600, function () use ($userId) {
            try {
                $response = app(ApiClient::class)->get('learning/access', ['user_id' => $userId]);
            } catch (\Illuminate\Http\Client\ConnectionException) {
                return true;
            }

            return $response->successful() ? (bool) $response->json('allowed') : true;
        });
    }

    /**
     * Certificate wording with {name}, {course} and {date} filled in for one examiner.
     * `$certificate` is the API's saved wording; `$date` is a Y-m-d string.
     */
    public static function certificateText(array $certificate, string $name, string $course, string $date): array
    {
        $vars = ['{name}' => $name, '{course}' => $course, '{date}' => \Illuminate\Support\Carbon::parse($date)->format('j F Y')];

        return array_map(fn ($text) => strtr((string) $text, $vars), $certificate);
    }

    public static function imageUrl(?array $image): ?string
    {
        return $image['resolved_url'] ?? null;
    }

    public static function html(?string $html): ?string
    {
        return $html ? (string) Str::of($html)->trim() : null;
    }

    /**
     * Normalised display settings — mirrors cosecsa-api App\Services\Lms\ImageDisplay.
     */
    public static function display(?array $image, array $defaults = []): array
    {
        $d = array_merge([
            'width' => 100, 'align' => 'center', 'radius' => 12,
            'border' => 0, 'border_color' => '#e2e8f0', 'shadow' => 'none',
        ], $defaults, array_filter((array) ($image['display'] ?? []), fn ($v) => $v !== null && $v !== ''));

        return [
            'width' => max(10, min(100, (int) $d['width'])),
            'align' => in_array($d['align'], ['left', 'center', 'right'], true) ? $d['align'] : 'center',
            'radius' => max(0, min(48, (int) $d['radius'])),
            'border' => max(0, min(12, (int) $d['border'])),
            'border_color' => preg_match('/^#[0-9a-f]{6}$/i', (string) $d['border_color']) ? strtolower($d['border_color']) : '#e2e8f0',
            'shadow' => in_array($d['shadow'], ['none', 'soft', 'strong', 'lifted'], true) ? $d['shadow'] : 'none',
        ];
    }

    /** Inline style + class for the frame around a block image. */
    public static function frame(?array $image, array $defaults = []): array
    {
        $d = static::display($image, $defaults);

        $margin = match ($d['align']) {
            'left' => 'margin-right: auto;',
            'right' => 'margin-left: auto;',
            default => 'margin-left: auto; margin-right: auto;',
        };

        $style = 'width: '.$d['width'].'%; '.$margin.' border-radius: '.$d['radius'].'px;';
        if ($d['border'] > 0) {
            $style .= ' border: '.$d['border'].'px solid '.$d['border_color'].';';
        }

        return ['style' => $style, 'class' => 'img-shadow-'.$d['shadow']];
    }

    /** API block arrays → objects, so block templates can use $block->type etc. */
    public static function blocks(array $blocks): array
    {
        return array_map(fn ($b) => (object) $b, $blocks);
    }

    /**
     * Groups a module's blocks for the player: back-to-back videos become one
     * two-column grid of square tiles. A video "unit" is the video plus the
     * paragraph right after it (its explanation) and the subheading right before
     * it (its "Watch this…" lead-in). Returns segments:
     *   ['block' => $block] or ['heading' => ?$block, 'units' => [['lead', 'video', 'after']]]
     * Lead-ins stay on their tiles only when every tile in the grid has one;
     * otherwise the first one (e.g. "Please watch the following animations:")
     * is shown above the grid.
     */
    public static function segments(array $blocks): array
    {
        $segments = [];
        $count = count($blocks);

        for ($i = 0; $i < $count; $i++) {
            $block = $blocks[$i];
            if ($block->type !== 'multimedia') {
                $segments[] = ['block' => $block];
                continue;
            }

            $unit = ['lead' => null, 'video' => $block, 'after' => null];
            $next = $blocks[$i + 1] ?? null;
            if ($next && $next->type === 'text' && $next->variant === 'paragraph') {
                $unit['after'] = $next;
                $i++;
            }

            $last = end($segments);
            if ($last && isset($last['block']) && $last['block']->type === 'text' && $last['block']->variant === 'subheading') {
                $unit['lead'] = array_pop($segments)['block'];
                $last = end($segments);
            }

            if ($last && isset($last['units'])) {
                $segments[array_key_last($segments)]['units'][] = $unit;
            } else {
                $segments[] = ['heading' => null, 'units' => [$unit]];
            }
        }

        foreach ($segments as &$segment) {
            if (isset($segment['units']) && (count($segment['units']) === 1
                    || collect($segment['units'])->contains(fn ($u) => ! $u['lead']))) {
                $segment['heading'] = $segment['units'][0]['lead'];
                $segment['units'][0]['lead'] = null;
            }
        }

        return $segments;
    }
}

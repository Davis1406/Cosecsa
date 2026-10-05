<?php

namespace App\Http\Controllers\Learning;

use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use App\Support\LearningView;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Examiner Training course for examiners — examiner/learning.
 * Content and progress live in cosecsa-api (internal/learning/*), which also
 * limits the course to examiners who confirmed attendance for this year.
 */
class ExaminerLearningController extends Controller
{
    public function __construct(private ApiClient $api) {}

    // GET examiner/learning
    public function cover()
    {
        $response = $this->api->get('learning/course', ['user_id' => Auth::id()]);
        if ($fail = $this->failed($response, 'examiner/dashboard')) {
            return $fail;
        }

        return view('learning.examiner.cover', $response->json());
    }

    // GET examiner/learning/certificate — available once every module is complete
    public function certificate()
    {
        $response = $this->api->get('learning/certificate', ['user_id' => Auth::id()]);

        if ($response->status() === 403 && ! str_contains((string) $response->json('message'), 'confirmed')) {
            return redirect()->route('examiner.learning')->with('error', $response->json('message'));
        }
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        $data = $response->json();
        $cert = LearningView::certificateText($data['certificate'], $data['name'], $data['course']['title'], $data['completed_at']);

        return view('learning.examiner.certificate', ['cert' => $cert, 'name' => $data['name'], 'course' => $data['course'], 'signature' => $data['signature'] ?? null]);
    }

    // GET examiner/learning/certificate/modal — the certificate as modal content (HTML fragment)
    public function certificateModal()
    {
        $response = $this->api->get('learning/certificate', ['user_id' => Auth::id()]);
        abort_unless($response->successful(), $response->status() === 403 ? 403 : 502);

        $data = $response->json();
        $cert = LearningView::certificateText($data['certificate'], $data['name'], $data['course']['title'], $data['completed_at']);

        // "Dr Hannah Getachew" → "Hannah"
        $firstName = explode(' ', trim(preg_replace('/^(dr|prof|professor|mr|mrs|ms|miss)\.?\s+/i', '', $data['name'])))[0];

        return view('learning.examiner._certificate-modal', [
            'cert' => $cert,
            'name' => $data['name'],
            'firstName' => $firstName,
            'course' => $data['course'],
            'signature' => $data['signature'] ?? null,
        ]);
    }

    // GET examiner/learning/{slug}
    public function module(string $slug)
    {
        $response = $this->api->get("learning/modules/{$slug}", ['user_id' => Auth::id()]);
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        $data = $response->json();
        $data['blocks'] = LearningView::blocks($data['blocks']);

        return view('learning.examiner.player', $data);
    }

    // POST examiner/learning/{slug}/next — completes the module, then moves on
    public function advance(string $slug)
    {
        $response = $this->api->post("learning/modules/{$slug}/advance", ['user_id' => Auth::id()]);
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        // The last module's "Get your certificate" button calls this with AJAX and opens the modal itself.
        if (request()->expectsJson()) {
            return response()->json(['goto' => $response->json('goto'), 'modal' => route('examiner.learning.certificate.modal')]);
        }

        return match ($response->json('goto')) {
            'module' => redirect()->route('examiner.learning.module', $response->json('slug')),
            'quiz' => redirect()->route('examiner.learning.quiz', $response->json('slug')),
            default => redirect()->route('examiner.learning.certificate'),
        };
    }

    // GET examiner/learning/{slug}/quiz
    public function quiz(string $slug)
    {
        $response = $this->api->get("learning/quiz/{$slug}", ['user_id' => Auth::id()]);
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        if ($response->json('passed')) {
            return redirect()->route('examiner.learning.result', $slug);
        }

        return view('learning.examiner.quiz', $response->json());
    }

    // POST examiner/learning/{slug}/quiz
    public function submit(Request $request, string $slug)
    {
        $answers = collect($request->input())
            ->filter(fn ($v, $k) => str_starts_with($k, 'question_') && is_string($v))
            ->mapWithKeys(fn ($v, $k) => [(int) substr($k, 9) => $v])
            ->all();

        $response = $this->api->post("learning/quiz/{$slug}/submit", ['user_id' => Auth::id(), 'answers' => $answers]);
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        return redirect()->route('examiner.learning.result', $slug);
    }

    // GET examiner/learning/{slug}/quiz/result
    public function result(string $slug)
    {
        $response = $this->api->get("learning/quiz/{$slug}/result", ['user_id' => Auth::id()]);
        if ($response->status() === 404) {
            return redirect()->route('examiner.learning.quiz', $slug);
        }
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        return view('learning.examiner.result', $response->json());
    }

    private function failed(Response $response, string $fallback = 'examiner/learning')
    {
        if ($response->successful()) {
            return null;
        }

        // 403: not confirmed for this year's exams — the API's message says so.
        if ($response->status() === 403) {
            return redirect('examiner/dashboard')->with('error', $response->json('message'));
        }

        $message = $response->status() === 404
            ? 'That part of the course could not be found.'
            : 'The Examiner Training course is unavailable right now. Please try again shortly.';

        return redirect($fallback === 'examiner/learning' && request()->is('examiner/learning') ? 'examiner/dashboard' : $fallback)
            ->with('error', $message);
    }
}

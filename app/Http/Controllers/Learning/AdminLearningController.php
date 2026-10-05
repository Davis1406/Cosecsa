<?php

namespace App\Http\Controllers\Learning;

use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use App\Support\LearningView;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Examiner Training administration — admin/exams/learning (Examinations menu).
 * Access follows the examiners permission (admin/exams prefix in
 * config/admin_permissions.php): GET needs view, POST needs manage.
 */
class AdminLearningController extends Controller
{
    public function __construct(private ApiClient $api) {}

    // GET admin/exams/learning — learner progress
    public function index()
    {
        $response = $this->api->get('learning/admin/overview');
        if ($fail = $this->failed($response, 'admin/exams/examiners')) {
            return $fail;
        }

        return view('learning.admin.index', $response->json());
    }

    // GET admin/exams/learning/users/{id}
    public function userProgress(int $id)
    {
        $response = $this->api->get("learning/admin/users/{$id}/progress");
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        return view('learning.admin.user-progress', $response->json());
    }

    // GET admin/exams/learning/course
    public function course()
    {
        $response = $this->api->get('learning/admin/modules');
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        return view('learning.admin.course', $response->json());
    }

    // GET admin/exams/learning/modules/{id}
    public function module(int $id)
    {
        $response = $this->api->get("learning/admin/modules/{$id}");
        if ($fail = $this->failed($response, 'admin/exams/learning/course')) {
            return $fail;
        }

        $data = $response->json();
        $types = $this->api->get('learning/admin/block-types');
        $data['blockTypes'] = $types->successful() ? $types->json('types') : [];

        return view('learning.admin.module', $data);
    }

    // POST admin/exams/learning/blocks  {module_id, type, after_block_id?}
    public function addBlock(Request $request)
    {
        $response = $this->api->post('learning/admin/blocks', [
            'module_id' => $request->integer('module_id'),
            'type' => $request->input('type'),
            'after_block_id' => $request->filled('after_block_id') ? $request->integer('after_block_id') : null,
        ]);

        if ($response->successful()) {
            // Redirect back and open the new block's editor (focus_block).
            return back()->with('success', $response->json('message'))->with('focus_block', $response->json('block.id'));
        }

        return back()->with('error', $response->json('message') ?: 'The block could not be added. Please try again.');
    }

    // GET admin/exams/learning/blocks/{id}/edit
    public function editBlock(int $id)
    {
        $response = $this->api->get("learning/admin/blocks/{$id}");
        if ($fail = $this->failed($response, 'admin/exams/learning/course')) {
            return $fail;
        }

        $data = $response->json();
        $data['blockObject'] = (object) $data['block'];

        return view('learning.admin.block-edit', $data);
    }

    // POST admin/exams/learning/blocks/{id}/preview — rendered HTML of unsaved edits
    public function previewBlock(Request $request, int $id)
    {
        $response = $this->api->post("learning/admin/blocks/{$id}/preview", ['fields' => $request->input('fields', [])]);
        abort_unless($response->successful(), 502);

        return view('learning.blocks._dispatch', ['block' => (object) $response->json('block')]);
    }

    // POST admin/exams/learning/blocks/{id}
    public function updateBlock(Request $request, int $id)
    {
        $response = $this->api->post("learning/admin/blocks/{$id}", ['fields' => $request->input('fields', [])]);

        return back()->with($response->successful() ? 'success' : 'error',
            $response->successful() ? 'Block saved.' : 'The block could not be saved. Please try again.');
    }

    // POST admin/exams/learning/blocks/{id}/move  {direction: up|down}
    public function moveBlock(Request $request, int $id)
    {
        $response = $this->api->post("learning/admin/blocks/{$id}/move", ['direction' => $request->input('direction')]);

        return back()->with($response->successful() ? 'success' : 'error',
            $response->successful() ? 'Block moved.' : 'The block could not be moved. Please try again.');
    }

    // POST admin/exams/learning/blocks/{id}/duplicate
    public function duplicateBlock(Request $request, int $id)
    {
        $response = $this->api->post("learning/admin/blocks/{$id}/duplicate");

        return back()->with($response->successful() ? 'success' : 'error',
            $response->successful() ? 'Block duplicated.' : 'The block could not be duplicated. Please try again.');
    }

    // POST admin/exams/learning/blocks/{id}/delete
    public function deleteBlock(Request $request, int $id)
    {
        $response = $this->api->post("learning/admin/blocks/{$id}/delete");

        return back()->with($response->successful() ? 'success' : 'error',
            $response->successful() ? 'Block deleted.' : 'The block could not be deleted. Please try again.');
    }

    // POST admin/exams/learning/blocks/{id}/list-items  {action: add|remove, index?} — AJAX
    public function listItems(Request $request, int $id)
    {
        $response = $this->api->post("learning/admin/blocks/{$id}/list-items", [
            'action' => $request->input('action'),
            'index' => $request->filled('index') ? $request->integer('index') : null,
            'fields' => $request->input('fields', []),
        ]);

        return response()->json($response->json(), $response->status());
    }

    // GET admin/exams/learning/blocks/{id}/editor — the block editor fragment, used
    // to re-render the inline editor after add/remove of list items.
    public function editorFragment(int $id)
    {
        $response = $this->api->get("learning/admin/blocks/{$id}");
        abort_unless($response->successful(), 502);

        $data = $response->json();

        return view('learning.admin._block-editor', [
            'block' => (object) $data['block'],
            'fields' => $data['fields'],
            'images' => $data['images'],
            'module' => $data['module'],
            'cancelUrl' => null,
        ]);
    }

    // POST admin/exams/learning/blocks/{id}/image (AJAX)
    public function uploadBlockImage(Request $request, int $id)
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:png,jpg,jpeg,svg,webp,gif', 'max:5120'],
            'path' => ['required', 'string', 'regex:/^[a-zA-Z0-9_.]+$/'],
        ]);

        $response = $this->api->postWithFile("learning/admin/blocks/{$id}/image",
            ['path' => $request->input('path')], ['image' => $request->file('image')]);

        return response()->json($response->json(), $response->status());
    }

    // GET admin/exams/learning/preview — the course as examiners see it. Uses the
    // learner endpoints (GET only), so viewing never records progress; preview=1
    // skips the API's confirmed-attendance check. Rendered with a standalone
    // layout (no admin chrome) so it embeds cleanly in the admin preview modal.
    public function previewCover()
    {
        $response = $this->api->get('learning/course', ['user_id' => Auth::id(), 'preview' => 1]);
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        return view('learning.examiner.cover', $response->json() + ['preview' => true, 'layout' => 'learning.examiner._standalone']);
    }

    // GET admin/exams/learning/preview/{slug}
    public function previewModule(string $slug)
    {
        $response = $this->api->get("learning/modules/{$slug}", ['user_id' => Auth::id(), 'preview' => 1]);
        if ($fail = $this->failed($response, 'admin/exams/learning/preview')) {
            return $fail;
        }

        $data = $response->json();
        $data['blocks'] = LearningView::blocks($data['blocks']);

        return view('learning.examiner.player', $data + ['preview' => true, 'layout' => 'learning.examiner._standalone']);
    }

    // GET admin/exams/learning/preview/{slug}/quiz — questions only; submitting is disabled
    public function previewQuiz(string $slug)
    {
        $response = $this->api->get("learning/quiz/{$slug}", ['user_id' => Auth::id(), 'preview' => 1]);
        if ($fail = $this->failed($response, 'admin/exams/learning/preview')) {
            return $fail;
        }

        // The API hides the questions from anyone who has already passed.
        if ($response->json('passed')) {
            return redirect()->route('admin.exams.learning.preview.module', $slug)
                ->with('error', 'You have passed this quiz on your own account, so its questions are hidden.');
        }

        return view('learning.examiner.quiz', $response->json() + ['preview' => true, 'layout' => 'learning.examiner._standalone']);
    }

    // GET admin/exams/learning/certificate — edit the certificate wording with a live preview
    public function certificate()
    {
        $response = $this->api->get('learning/admin/certificate', ['user_id' => Auth::id()]);
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        $data = $response->json();

        return view('learning.admin.certificate', [
            'settings' => $data['certificate'],
            'defaults' => $data['defaults'],
            'course' => $data['course'],
            'sampleName' => $data['sample_name'],
            'sampleDate' => now()->toDateString(),
        ]);
    }

    // POST admin/exams/learning/certificate — save the wording, or reset it to the defaults
    public function saveCertificate(Request $request)
    {
        $fields = ['org_name', 'heading', 'subtitle', 'body_text', 'course_name', 'detail_text',
            'sig1_name', 'sig1_title', 'cpd_points'];

        $request->validate([
            'org_name' => ['nullable', 'string', 'max:160'],
            'heading' => ['nullable', 'string', 'max:80'],
            'subtitle' => ['nullable', 'string', 'max:120'],
            'body_text' => ['nullable', 'string', 'max:160'],
            'course_name' => ['nullable', 'string', 'max:200'],
            'detail_text' => ['nullable', 'string', 'max:200'],
            'sig1_name' => ['nullable', 'string', 'max:80'],
            'sig1_title' => ['nullable', 'string', 'max:80'],
            'cpd_points' => ['nullable', 'string', 'max:10'],
        ]);

        $payload = [];
        foreach ($fields as $field) {
            $payload[$field] = (string) $request->input($field, '');
        }

        $response = $this->api->post('learning/admin/certificate', $payload);

        return back()->with($response->successful() ? 'success' : 'error',
            $response->successful() ? 'Certificate wording saved.' : 'The certificate could not be saved. Please try again.');
    }

    // GET admin/exams/learning/videos
    public function videos()
    {
        $response = $this->api->get('learning/admin/videos');
        if ($fail = $this->failed($response)) {
            return $fail;
        }

        return view('learning.admin.videos', $response->json());
    }

    // POST admin/exams/learning/videos
    public function uploadVideo(Request $request)
    {
        $request->validate([
            'block_id' => ['required', 'integer'],
            'video' => ['required', 'file', 'mimes:mp4,mov,webm,m4v', 'max:409600'],
            'video_label' => ['nullable', 'string', 'max:255'],
        ]);

        $response = $this->api->postStream('learning/admin/videos',
            ['block_id' => $request->input('block_id'), 'video_label' => $request->input('video_label')],
            'video', $request->file('video'));

        return back()->with($response->successful() ? 'success' : 'error',
            $response->successful() ? 'Video uploaded and linked to the module.' : 'The video could not be uploaded.');
    }

    // POST admin/exams/learning/videos/new — an extra video in a module (end, or after a block)
    public function addVideo(Request $request)
    {
        $request->validate([
            'module_id' => ['required', 'integer'],
            'after_block_id' => ['nullable', 'integer'],
            'video' => ['required', 'file', 'mimes:mp4,mov,webm,m4v', 'max:409600'],
            'video_label' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $response = $this->api->postStream('learning/admin/videos/new',
            $request->only('module_id', 'after_block_id', 'video_label', 'caption'),
            'video', $request->file('video'));

        return back()->with($response->successful() ? 'success' : 'error',
            $response->successful() ? $response->json('message') : ($response->json('message') ?: 'The video could not be added.'));
    }

    // POST admin/exams/learning/logo
    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => ['required', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048']]);
        $response = $this->api->postWithFile('learning/admin/logo', [], ['logo' => $request->file('logo')]);

        return back()->with($response->successful() ? 'success' : 'error',
            $response->successful() ? 'Course logo updated.' : 'The logo could not be uploaded.');
    }

    // POST admin/exams/learning/logo/remove
    public function removeLogo()
    {
        $response = $this->api->post('learning/admin/logo/remove');

        return back()->with($response->successful() ? 'success' : 'error',
            $response->successful() ? 'Course logo removed.' : 'The logo could not be removed.');
    }

    private function failed(Response $response, string $fallback = 'admin/exams/learning')
    {
        if ($response->successful()) {
            return null;
        }

        return redirect($fallback)->with('error', $response->status() === 404
            ? ($response->json('message') ?: 'Not found.')
            : 'The Examiner Training service is unavailable right now.');
    }
}

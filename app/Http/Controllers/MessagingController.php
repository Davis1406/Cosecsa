<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MessagingController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->with(['latestMessage', 'participants.user'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($c) use ($userId) {
                $c->display_name = $this->conversationTitle($c, $userId);
                return $c;
            });

        return view('messaging.index', [
            'header_title'  => 'Messages',
            'conversations' => $conversations,
        ]);
    }

    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if (! $q) {
            return response()->json([]);
        }

        $like = "%{$q}%";
        $results = DB::table('users')
            ->where('id', '!=', Auth::id())
            ->where('is_deleted', 0)
            ->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)->orWhere('email', 'like', $like);
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($results);
    }

    // Find-or-create a direct conversation with the given user and jump to it.
    public function startDirect(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);
        $otherId = (int) $request->user_id;
        $myId = Auth::id();

        if ($otherId === $myId) {
            return back()->with('error', "You can't message yourself.");
        }
        if (! DB::table('users')->where('id', $otherId)->exists()) {
            return back()->with('error', 'User not found.');
        }

        $existing = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $myId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $otherId))
            ->first();

        if ($existing) {
            return redirect("messages/{$existing->id}");
        }

        $conversation = Conversation::create(['type' => 'direct', 'created_by' => $myId]);
        ConversationParticipant::insert([
            ['conversation_id' => $conversation->id, 'user_id' => $myId, 'created_at' => now(), 'updated_at' => now()],
            ['conversation_id' => $conversation->id, 'user_id' => $otherId, 'created_at' => now(), 'updated_at' => now()],
        ]);

        return redirect("messages/{$conversation->id}");
    }

    public function show($id)
    {
        $conversation = Conversation::with(['messages.sender', 'messages.attachments', 'participants.user'])->findOrFail($id);
        $this->authorizeParticipant($conversation);

        ConversationParticipant::where('conversation_id', $id)
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);

        return view('messaging.show', [
            'header_title'  => 'Messages',
            'conversation'  => $conversation,
            'title'         => $this->conversationTitle($conversation, Auth::id()),
        ]);
    }

    public function send(Request $request, $id)
    {
        $hasFiles = $request->hasFile('attachments') || $request->hasFile('voice_note');
        $request->validate([
            'body'          => $hasFiles ? 'nullable|string|max:5000' : 'required|string|max:5000',
            'attachments.*' => 'nullable|file|max:20480', // 20MB per file
            'voice_note'    => 'nullable|file|max:20480',
        ]);

        $conversation = Conversation::findOrFail($id);
        $this->authorizeParticipant($conversation);

        $message = Message::create([
            'conversation_id' => $id,
            'sender_id'       => Auth::id(),
            'body'            => trim((string) $request->body),
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $this->storeAttachment($message, $file);
        }
        if ($request->hasFile('voice_note')) {
            $this->storeAttachment($message, $request->file('voice_note'), 'audio');
        }

        $conversation->update(['last_message_at' => now()]);

        return redirect("messages/{$id}");
    }

    public function editMessage(Request $request, $conversationId, $messageId)
    {
        $request->validate(['body' => 'required|string|max:5000']);

        $message = Message::where('conversation_id', $conversationId)->findOrFail($messageId);
        abort_unless($message->sender_id == Auth::id(), 403, 'You can only edit your own messages.');
        abort_if($message->deleted_at, 404);

        $message->update(['body' => trim($request->body), 'edited_at' => now()]);

        return redirect("messages/{$conversationId}");
    }

    public function deleteMessage($conversationId, $messageId)
    {
        $message = Message::with('attachments')->where('conversation_id', $conversationId)->findOrFail($messageId);
        abort_unless($message->sender_id == Auth::id(), 403, 'You can only delete your own messages.');

        foreach ($message->attachments as $a) {
            if (Storage::disk('public')->exists($a->path)) {
                Storage::disk('public')->delete($a->path);
            }
        }
        $message->attachments()->delete();
        $message->update(['deleted_at' => now(), 'body' => '']);

        return redirect("messages/{$conversationId}");
    }

    protected function storeAttachment(Message $message, $file, ?string $kindOverride = null): void
    {
        $mime = $file->getMimeType();
        $kind = $kindOverride ?? (str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'audio/') ? 'audio' : 'file'));

        $path = $file->store('messages/attachments', 'public');

        MessageAttachment::create([
            'message_id'    => $message->id,
            'path'          => $path,
            'original_name' => $kind === 'audio' ? 'Voice note' : $file->getClientOriginalName(),
            'mime_type'     => $mime,
            'size'          => $file->getSize(),
            'kind'          => $kind,
        ]);
    }

    // ── Groups (admin-created) ─────────────────────────────────────────────

    public function groupsIndex()
    {
        $this->authorizeAdmin();

        $groups = Conversation::where('type', 'group')
            ->withCount('participants')
            ->orderBy('name')
            ->get();

        return view('messaging.groups.index', ['header_title' => 'Discussion Groups', 'groups' => $groups]);
    }

    public function groupCreate()
    {
        $this->authorizeAdmin();
        return view('messaging.groups.form', ['header_title' => 'New Discussion Group', 'group' => null, 'members' => collect()]);
    }

    public function groupStore(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate(['name' => 'required|string|max:255', 'member_ids' => 'array']);

        $group = Conversation::create([
            'type'       => 'group',
            'name'       => $request->name,
            'created_by' => Auth::id(),
        ]);

        $memberIds = array_unique(array_merge($request->input('member_ids', []), [Auth::id()]));
        $rows = collect($memberIds)->map(fn ($uid) => [
            'conversation_id' => $group->id, 'user_id' => $uid, 'created_at' => now(), 'updated_at' => now(),
        ])->all();
        ConversationParticipant::insert($rows);

        return redirect('messages/groups')->with('success', 'Group created');
    }

    public function groupEdit($id)
    {
        $this->authorizeAdmin();
        $group = Conversation::where('type', 'group')->with('participants.user')->findOrFail($id);
        return view('messaging.groups.form', ['header_title' => 'Edit Discussion Group', 'group' => $group, 'members' => $group->participants]);
    }

    public function groupUpdate(Request $request, $id)
    {
        $this->authorizeAdmin();
        $request->validate(['name' => 'required|string|max:255', 'member_ids' => 'array']);

        $group = Conversation::where('type', 'group')->findOrFail($id);
        $group->update(['name' => $request->name]);

        $memberIds = array_unique(array_merge($request->input('member_ids', []), [$group->created_by]));
        ConversationParticipant::where('conversation_id', $id)->delete();
        $rows = collect($memberIds)->filter()->map(fn ($uid) => [
            'conversation_id' => $id, 'user_id' => $uid, 'created_at' => now(), 'updated_at' => now(),
        ])->all();
        ConversationParticipant::insert($rows);

        return redirect('messages/groups')->with('success', 'Group updated');
    }

    public function groupDelete($id)
    {
        $this->authorizeAdmin();
        Conversation::where('type', 'group')->findOrFail($id)->delete();
        return redirect('messages/groups')->with('success', 'Group deleted');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    protected function authorizeParticipant(Conversation $conversation): void
    {
        abort_unless(
            ConversationParticipant::where('conversation_id', $conversation->id)->where('user_id', Auth::id())->exists(),
            403
        );
    }

    protected function authorizeAdmin(): void
    {
        abort_unless(Auth::user()->user_type == 1, 403, 'Only admins can manage discussion groups.');
    }

    protected function conversationTitle(Conversation $c, int $viewerId): string
    {
        if ($c->type === 'group') {
            return $c->name ?: 'Group';
        }
        $other = $c->participants->firstWhere('user_id', '!=', $viewerId);
        return $other?->user?->name ?? 'Direct Message';
    }
}

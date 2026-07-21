<?php

namespace App\Http\Controllers;

use App\Models\ConversationParticipant;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // "My Tasks": assigned to me, and ones I assigned to others.
    public function index()
    {
        $userId = Auth::id();

        return view('messaging.tasks', [
            'header_title' => 'My Tasks',
            'assignedToMe' => Task::with(['creator', 'conversation'])->where('assigned_to', $userId)->orderByRaw("status = 'done'")->orderBy('due_date')->get(),
            'assignedByMe' => Task::with(['assignee', 'conversation'])->where('created_by', $userId)->where('assigned_to', '!=', $userId)->orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request, $conversationId)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_to' => 'required|integer',
            'due_date'    => 'nullable|date',
        ]);

        abort_unless(
            ConversationParticipant::where('conversation_id', $conversationId)->where('user_id', Auth::id())->exists(),
            403
        );
        abort_unless(
            ConversationParticipant::where('conversation_id', $conversationId)->where('user_id', $request->assigned_to)->exists(),
            422,
            'You can only assign tasks to someone in this conversation.'
        );

        Task::create([
            'conversation_id' => $conversationId,
            'created_by'      => Auth::id(),
            'assigned_to'     => $request->assigned_to,
            'title'           => $request->title,
            'description'     => $request->description,
            'due_date'        => $request->due_date,
        ]);

        return redirect("messages/{$conversationId}")->with('success', 'Task assigned');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,in_progress,done']);

        $task = Task::findOrFail($id);
        abort_unless(in_array(Auth::id(), [$task->assigned_to, $task->created_by]), 403);

        $task->update(['status' => $request->status]);

        return back()->with('success', 'Task updated');
    }
}

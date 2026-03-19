<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatsController extends Controller
{
    public function index(Request $request)
    {
        $authUser = $request->user();
        $search = trim((string) $request->get('search', ''));

        $baseQuery = ChatMessage::query()
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->where(function ($query) use ($authUser) {
                $query->where('sender_id', $authUser->id)
                    ->orWhere('receiver_id', $authUser->id);
            });

        $receivedMessages = (clone $baseQuery)
            ->where('receiver_id', $authUser->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('thread', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('sender', function ($senderQuery) use ($search) {
                            $senderQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('receiver', function ($receiverQuery) use ($search) {
                            $receiverQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        $sentMessages = (clone $baseQuery)
            ->where('sender_id', $authUser->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('thread', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('sender', function ($senderQuery) use ($search) {
                            $senderQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('receiver', function ($receiverQuery) use ($search) {
                            $receiverQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        $users = User::query()
            ->where('id', '!=', $authUser->id)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Common/Chat/Index', [
            'receivedMessages' => $receivedMessages,
            'sentMessages' => $sentMessages,
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $authUser = $request->user();

        $validated = $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:5000', 'required_without:file'],
            'file' => ['nullable', 'image', 'max:5120', 'required_without:message'],
        ]);

        if ($validated['receiver_id'] === $authUser->id) {
            return back()->with('error', 'You cannot send a message to yourself.');
        }

        $receiver = User::query()->with(['doctor:id,user_id', 'patient:id,user_id'])->findOrFail($validated['receiver_id']);
        $authUser->loadMissing(['doctor:id,user_id', 'patient:id,user_id']);

        $doctorId = $authUser->doctor?->id ?? $receiver->doctor?->id;
        $patientId = $authUser->patient?->id ?? $receiver->patient?->id;

        $participants = [$authUser->id, $receiver->id];
        sort($participants);
        $thread = implode('_', $participants);
        $filePath = null;
        if ($request->hasFile('file')) {
            $storedPath = $request->file('file')->store('chat-files', 'public');
            $filePath = '/storage/'.$storedPath;
        }

        ChatMessage::create([
            'doctor_id' => $doctorId,
            'patient_id' => $patientId,
            'sender_id' => $authUser->id,
            'receiver_id' => $receiver->id,
            'thread' => $thread,
            'message' => $validated['message'] ?? null,
            'file' => $filePath,
            'is_read' => false,
        ]);

        return back()->with('success', 'Message sent successfully.');
    }

    public function markRead(Request $request, ChatMessage $chat)
    {
        if ($chat->receiver_id !== $request->user()->id) {
            abort(403, 'You can only mark your received messages as read.');
        }

        if (! $chat->is_read) {
            $chat->update(['is_read' => true]);
        }

        return back();
    }
}

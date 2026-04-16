<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationParticipant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatService
{
    /**
     * Get or create private conversation between 2 users
     */
    public function getOrCreateConversation($user1, $user2): Conversation
    {
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', fn($q) => $q->where('user_id', $user1))
            ->whereHas('participants', fn($q) => $q->where('user_id', $user2))
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'hospital_id' => Auth::user()->hospital_id,
                'type' => 'private',
            ]);

            $conversation->participants()->attach([$user1, $user2]);
        }
        return $conversation;
    }

    /**
     * Send message
     */
    public function sendMessage(
        string $conversationId,
        string $message,
        ?string $type = 'text',
        ?string $local_time,
        ?string $file = null,

    ): ConversationMessage {

        abort_unless(
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', (string) Auth::id())
                ->exists(),
            403
        );

        return DB::transaction(function () use ($conversationId, $message, $type, $file, $local_time) {

            $chatMessage = ConversationMessage::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversationId,
                'sender_id' => Auth::id(),
                'message' => $message,
                'type' => $type,
                'file' => $file,
                'send_at' => $local_time,
            ]);

            $receiverId = \App\Models\ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', '!=', Auth::id())
                ->value('user_id');

            $chatMessage->receiver_id = $receiverId;

            broadcast(new \App\Events\MessageSent($chatMessage))->toOthers();
            return $chatMessage;
        });
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead(string $conversationId): void
    {
        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', Auth::id())
            ->update([
                'last_read_at' => now()
            ]);
    }

    /**
     * Get unread count per conversation
     */
    public function getUnreadCount(string $conversationId): int
    {
        $participant = ConversationParticipant::where([
            'conversation_id' => $conversationId,
            'user_id' => Auth::id()
        ])->first();

        if (!$participant || !$participant->last_read_at) {
            return ConversationMessage::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', Auth::id())
                ->count();
        }

        return ConversationMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', Auth::id())
            ->where('created_at', '>', $participant->last_read_at)
            ->count();
    }

    /**
     * Get conversation messages (with pagination)
     */
    public function getMessages(string $conversationId, int $perPage = 20)
    {
        return ConversationMessage::with('sender')
            ->where('conversation_id', $conversationId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get user conversations (list view)
     */
    public function getUserConversations(int $userId)
    {
        return Conversation::with([
            'participants',
            'messages' => fn($q) => $q->latest()->limit(1)
        ])
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->latest()
            ->get();
    }
}

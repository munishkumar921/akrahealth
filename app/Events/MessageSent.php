<?php

namespace App\Events;

use App\Models\ConversationMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcastNow
{
    public $message;

    public function __construct(ConversationMessage $message)
    {
        $this->message = $message->load('sender:id,name');
    }

    public function broadcastOn()
    {
        Log::info('Broadcasting to user', [
            'receiver_id' => $this->message->receiver_id
        ]);
        return [
            new PrivateChannel('chat.' . $this->message->conversation_id),
            new PrivateChannel('user.' . $this->message->receiver_id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'message' => $this->message->message,
            'type' => $this->message->type,
            'file' => $this->message->file,
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
            ],
            'created_at' => $this->message->created_at->toDateTimeString(),
        ];
    }
}

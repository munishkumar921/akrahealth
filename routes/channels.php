<?php

use App\Models\ConversationParticipant;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    return ConversationParticipant::where([
        'conversation_id' => $conversationId,
        'user_id' => $user->id
    ])->exists();
});


Broadcast::channel('user.{id}', function ($user, $id) {
    return $user->id === $id;
});
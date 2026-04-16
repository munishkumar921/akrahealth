<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'conversation_id',
        'user_id',
        'last_read_at',
    ];

    /**
     * conversation
     *
     * @return void
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * user
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

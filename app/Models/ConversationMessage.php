<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'type',
        'file',
        'send_at',
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

    public function getSendAtAttribute($value)
    {
        return Carbon::parse($value)->format('d M, Y h:i A');
    }

    /**
     * sender
     *
     * @return void
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}

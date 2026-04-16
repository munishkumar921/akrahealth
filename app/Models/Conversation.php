<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
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
        'hospital_id',
        'type',
        'title',
    ];

    /**
     * hospital
     *
     * @return void
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * messages
     *
     * @return void
     */
    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    /**
     * participants
     *
     * @return void
     */
    public function participants()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'conversation_participants',
            'conversation_id',
            'user_id'
        );
    }
}

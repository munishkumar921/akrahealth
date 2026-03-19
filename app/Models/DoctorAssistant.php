<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorAssistant extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'doctor_assistants';

    protected $fillable = [
        'user_id',
        'doctor_assistant_id',
    ];

    protected $casts = ['created_at' => 'datetime:M d, Y', 'updated_at' => 'datetime:M d, Y'];

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'user_id');
    }
}

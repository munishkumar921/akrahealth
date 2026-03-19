<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabSpeciality extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    protected $table = 'lab_specialities';

    public $incrementing = false;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'lab_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * lab
     *
     * @return void
     */
    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }
}

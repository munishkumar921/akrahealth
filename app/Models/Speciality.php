<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Speciality extends Model
{
    use HasUuids;

    protected $table = 'specialities';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $fillable = [
        'id',
        'name',
        'hospital_id',
        'description',
        'banner',
        'is_active',
    ];

    protected $appends = [
        'icon',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_speciality');
    }

    public function getIconAttribute()
    {
        return asset($this->banner);
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'tests';

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'hospital_id',
        'type',
        'name',
        'result',
        'units',
        'reference',
        'flags',
        'time',
        'code',
    ];

    protected $appends = [
        'result',
    ];

    protected $attributes = [
        'result' => '',
        'flags' => '',
        'time' => null,
    ];

    public function getResultAttribute()
    {
        return $this->attributes['result'];
    }

    public function setResultAttribute($value)
    {
        $this->attributes['result'] = $value;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function getFlagAttribute()
    {
        return $this->attributes['flag'];
    }

    public function setFlagAttribute($value)
    {
        $this->attributes['flag'] = $value;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    public function getTimeAttribute($value)
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d');
    }

    public function setTimeAttribute($value)
    {
        $this->attributes['time'] = Carbon::parse($value)->format('Y-m-d');
    }
}

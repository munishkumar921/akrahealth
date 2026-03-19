<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProviderException extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'provider_exceptions';

    protected $fillable = [
        'exception_date',
        'start_time',
        'end_time',
        'title',
        'reason',
        'doctor_id',
        'hospital_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y H:i',
        'updated_at' => 'datetime:M d, Y H:i',
        'is_active' => 'boolean',
        'start_time' => 'datetime:H:i ',
        'end_time' => 'datetime:H:i',
        'exception_date' => 'date:M d, Y',
    ];

    public function getExceptionDateAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
    }

    public function getStartTimeAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('h:i A');
    }

    public function getEndTimeAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('h:i A');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the doctor that owns the exception.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the hospital that owns the exception.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Get the user who created the exception.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the exception.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

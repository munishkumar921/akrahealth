<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'alerts';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'hospital_id',
        'order_id',
        'alert',
        'why_not_complete',
        'message_alert',
        'description',
        'date_active',
        'date_complete',

    ];

    protected $casts = [
        'date_active' => 'datetime:M d, Y',
        'date_complete' => 'datetime:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function getDateActiveAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
    }

    public function getDateInactiveAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabOrder extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'order_id',
        'appointment_id',
        'lab_id',
        'lab_test_id',
        'status',
        'scheduled_at',
        'reported_at',
        'report_file',
        'payment_status',
        'total_amount',
        'notes',
    ];

    protected $appends = [
        'scheduled',
        'reported',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime:M d, Y',
        'reported_at' => 'datetime:M d, Y',
        // 'total_amount' => 'decimal:2',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * appointment
     *
     * @return void
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * lab
     *
     * @return void
     */
    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    /**
     * getScheduledAttribute
     *
     * @return void
     */
    public function getScheduledAttribute()
    {
        return Carbon::parse($this->scheduled_at)->format('M d, Y');
    }

    /**
     * getReportedAttribute
     *
     * @return void
     */
    public function getReportedAttribute()
    {
        return Carbon::parse($this->reported_at)->format('M d, Y');
    }

    /**
     * labTest
     *
     * @return void
     */
    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }

    /**
     * order
     * Relationship to the main orders table
     *
     * @return void
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

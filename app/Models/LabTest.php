<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
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
        'id',
        'lab_test_category_id',
        'hospital_id',
        'created_by',
        'updated_by',
        'name',
        'description',
        'sample_type',
        'fasting_required',
        'report_time',
        'instructions',
        'price',
        'discount',
        'final_price',
        'currency',
        'is_home_collection_available',
        'is_active',
    ];

    protected $casts = [
        // 'price' => 'decimal:2',
        // 'discount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function scopeForHospital($query, $hospitalId)
    {
        return $query->where('hospital_id', $hospitalId);
    }

    /**
     * category
     *
     * @return void
     */
    public function category()
    {
        return $this->belongsTo(LabTestCategory::class, 'lab_test_category_id');
    }

    /**
     * created_by
     *
     * @return void
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * updated_by
     *
     * @return void
     */
    public function updated_by()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * orders
     *
     * @return void
     */
    public function orders()
    {
        return $this->belongsToMany(LabOrder::class, 'lab_order_tests');
    }

    /**
     * labs
     *
     * @return void
     */
    public function labs()
    {
        return $this->belongsToMany(Lab::class, 'lab_lab_test')
            ->withPivot('price', 'is_active')
            ->withTimestamps();
    }
}

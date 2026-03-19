<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaboratoryTest extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    protected $table = 'lab_tests';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'lab_test_category_id',
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

    /**
     * Get the user that owns the lab test.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

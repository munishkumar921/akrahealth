<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'plan_for',
        'title',
        'label',
        'price',
        'currency',
        'frequency',
        'features',
        'status',
        'features',
        'permissions',
        'country_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'permissions' => 'array',   // ✅ REQUIRED
        'status' => 'boolean',
        'active' => 'boolean',
    ];
}

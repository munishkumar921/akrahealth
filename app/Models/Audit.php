<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'audits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'admin_id',
        'user_type',
        'module',
        'action',
        'description',
        'query',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'encounter_location',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime:M d, Y h:i:s',
        'updated_at' => 'datetime:M d, Y h:i:s',
    ];

    /**
     * Get the user that owns the audit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin that performed the action.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the formatted action type.
     */
    public function getFormattedActionAttribute(): string
    {
        return match ($this->action) {
            'create' => '<span class="badge bg-success">Create</span>',
            'update' => '<span class="badge bg-warning">Update</span>',
            'delete' => '<span class="badge bg-danger">Delete</span>',
            'view' => '<span class="badge bg-info">View</span>',
            'login' => '<span class="badge bg-primary">Login</span>',
            'logout' => '<span class="badge bg-secondary">Logout</span>',
            default => '<span class="badge bg-light">'.$this->action.'</span>',
        };
    }

    /**
     * Get the module label.
     */
    public function getModuleLabelAttribute(): string
    {
        return match ($this->module) {
            'patients' => __('Patients'),
            'doctors' => __('Doctors'),
            'admins' => __('Admins'),
            'appointments' => __('Appointments'),
            'prescriptions' => __('Prescriptions'),
            'lab_orders' => __('Lab Orders'),
            'pharmacy_orders' => __('Pharmacy Orders'),
            'settings' => __('Settings'),
            'users' => __('Users'),
            'roles' => __('Roles'),
            'permissions' => __('Permissions'),
            default => ucfirst($this->module ?? 'General'),
        };
    }
}

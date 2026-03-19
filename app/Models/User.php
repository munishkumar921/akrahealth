<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guard_name = 'web';

    /**
     * table
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'email',
        'mobile',
        'sex',
        'password',
        'description',
        'profile_photo_path',
        'secret_question',
        'secret_answer',
        'is_active',
        'skill_id',
        'language',
        'specialty_id',
        'is_email_verified',
        'template',
        'subscription_plan_id',
        'hospital_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'language' => 'object',
        'is_active' => 'boolean',
        'is_email_verified' => 'boolean',
        'specialty_id' => 'array',
        'skill_id' => 'array',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',

    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'plan_name',
    ];

    /**
     * Get the user's subscription plan name.
     */
    public function getPlanNameAttribute(): ?string
    {
        return $this->subscriptionPlan?->title ?? null;
    }

    public function scopeByRole($query, $user)
    {
        if ($user->hasRole('Admin')) {
            return $query;
        }

        return $query->where('id', $user->id);
    }

    public function getCreatedAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
    }

    /**
     * patient
     */
    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    /**
     * doctor
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    /**
     * address
     */
    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'user_id');
    }

    public function DoctorAssistant()
    {
        return $this->hasOne(DoctorAssistant::class, 'user_id');
    }

    public function userSkills(): HasOne
    {
        return $this->hasOne(UserSkill::class, 'user_id');
    }

    public function hospital()
    {
        return $this->hasOne(Hospital::class, 'user_id');
    }

    /**
     * subscription plan
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function lab()
    {
        return $this->hasOne(Lab::class, 'user_id');
    }

    public function pharmacy()
    {
        return $this->hasOne(Pharmacy::class, 'user_id');
    }
}

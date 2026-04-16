<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that still do not have soft deletes at the schema level.
     *
     * @var array<int, string>
     */
    private array $tables = [
        'audits',
        'bank_accounts',
        'billing_cores',
        'billings',
        'c_p_t_relates',
        'calendars',
        'cardiopulmonary',
        'chat_messages',
        'conversation_messages',
        'conversation_participants',
        'conversations',
        'countries',
        'cvxes',
        'demo_requests',
        'doctor_assistants',
        'doctor_patient',
        'doctor_specialties',
        'doctors',
        'documents',
        'hospital_cpts',
        'hospital_timings',
        'insurances',
        'lab_specialities',
        'messages',
        'notifications',
        'order_lists',
        'orders',
        'other_histories',
        'patient_illness_histories',
        'patient_notes',
        'patient_relate',
        'patient_reminders',
        'patient_requests',
        'patient_supplements',
        'payment_platforms',
        'payments',
        'physical_examinations',
        'present_illness_histories',
        'radiologies',
        'razorpay_orders',
        'razorpay_transactions',
        'referrals',
        'review_of_systems',
        'roles',
        'schedule_setups',
        'schedules',
        'services',
        'skills',
        'social_histories',
        'states',
        'subscription_plans',
        'supplement_lists',
        't_messages',
        'tags',
        'templates',
        'uma_invitations',
        'user_skills',
        'user_subscriptions',
        'users_verify',
        'laboratory_tests',
        'supplement_inventories',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};

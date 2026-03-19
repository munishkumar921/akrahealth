<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SubscriptionPlanPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /* --------------------------
         |  SUBSCRIPTION PLAN PERMISSIONS
         |  These permissions will be assigned to subscription plans
         |  and checked when users access features
         -------------------------- */

        $permissions = [

            /* ================= DOCTOR PERMISSIONS ================= */

            // Dashboard & Profile
            'doctor.dashboard',
            'doctor.profile',
            'doctor.search_patient',
            'doctor.select_patient',

            // Patient
            'doctor.patient.view',
            'doctor.patient.create',
            'doctor.patient.update',
            'doctor.patient.history',
            'doctor.patient.summary',

            // Conditions
            'doctor.conditions.view',
            'doctor.conditions.create',
            'doctor.conditions.update',
            'doctor.conditions.delete',
            'doctor.conditions.status',

            // Medications
            'doctor.medications.view',
            'doctor.medications.create',
            'doctor.medications.update',
            'doctor.medications.delete',
            'doctor.medications.reconcile',
            'doctor.medications.status',

            // Supplements
            'doctor.supplements.manage',

            // Immunizations
            'doctor.immunizations.manage',

            // Allergies & Alerts
            'doctor.allergies.manage',
            'doctor.alerts.manage',

            // Encounters & Orders
            'doctor.encounters.view',
            'doctor.encounters.create',
            'doctor.encounters.update',
            'doctor.encounters.sign',
            'doctor.orders.manage',

            // Prescriptions
            'doctor.prescriptions.view',
            'doctor.prescriptions.create',
            'doctor.prescriptions.update',
            'doctor.prescriptions.delete',
            'doctor.prescriptions.download',

            // Documents
            'doctor.documents.upload',
            'doctor.documents.view',
            'doctor.documents.delete',
            'doctor.documents.generate',

            // Billing & Finance
            'doctor.billing.view',
            'doctor.billing.create',
            'doctor.billing.payment',

            // Schedule & Appointment
            'doctor.schedule.manage',
            'doctor.appointments.manage',

            // Messages & Calls
            'doctor.messages.view',
            'doctor.calls.manage',
            'doctor.video_call',

            /* ================= ADMIN PERMISSIONS ================= */

            // Dashboard
            'admin.dashboard',

            // Users & Roles
            'admin.users.manage',
            'admin.roles.permissions',

            // Core Management
            'admin.doctors.manage',
            'admin.patients.manage',
            'admin.labs.manage',
            'admin.pharmacies.manage',

            // Practice / Hospital
            'admin.hospitals.manage',
            'admin.practice.settings',

            // Master Data
            'admin.specialities.manage',
            'admin.medicines.manage',
            'admin.lab_tests.manage',
            'admin.services.manage',
            'admin.visit_types.manage',

            // Subscription & Payments
            'admin.subscription_plans.manage',
            'admin.subscriptions.view',
            'admin.transactions.view',

            // Reports
            'admin.reports.view',
            'admin.reports.pharmacy',
            'admin.reports.lab',
            'admin.reports.ccda',
            'admin.reports.charts',

            // Appointments & Schedule
            'admin.appointments.manage',
            'admin.schedule.setup',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Clear permission cache again after creation
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}

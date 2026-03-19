<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class TrialSubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Trial Plan Features:
     * - Dashboard
     * - Patient Demographics / Charting
     * - Basic charting & SOAP notes
     * - Allergies, conditions, medication history
     * - Core Patient portal
     * - Basic Scheduling
     * - Basic Digital forms
     * - Labs / Results(View/Upload)
     * - Document Management
     * - Basic Messaging (Patient & Staff)
     * - Basic Analytics / Reports
     * - Email support
     */
    public function run(): void
    {
        $features = '<ul>
            <li>Dashboard</li>
            <li>Patient Demographics / Charting</li>
            <li>Basic charting & SOAP notes</li>
            <li>Allergies, conditions, medication history</li>
            <li>Core Patient portal</li>
            <li>Basic Scheduling</li>
            <li>Basic Digital forms</li>
            <li>Labs / Results(View/Upload)</li>
            <li>Document Management</li>
            <li>Basic Messaging (Patient & Staff)</li>
            <li>Basic Analytics / Reports</li>
            <li>Email support</li>
        </ul>';

        // Define permissions for the trial plan
        $permissions = [
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

            // Medications
            'doctor.medications.view',
            'doctor.medications.create',
            'doctor.medications.update',

            // Allergies & Alerts
            'doctor.allergies.manage',
            'doctor.alerts.manage',

            // Encounters & Orders
            'doctor.encounters.view',
            'doctor.encounters.create',
            'doctor.encounters.update',

            // Documents
            'doctor.documents.upload',
            'doctor.documents.view',
            'doctor.documents.generate',

            // Schedule & Appointment
            'doctor.schedule.manage',
            'doctor.appointments.manage',

            // Messages & Calls
            'doctor.messages.view',
            'doctor.calls.manage',

            // Billing & Finance
            'doctor.billing.view',
        ];

        $plans = [
            // INR Plans
            [
                'plan_for' => 'doctor',
                'title' => 'Trial',
                'price' => 0,
                'currency' => 'INR',
                'frequency' => 'monthly',
                'features' => $features,
                'permissions' => $permissions,
                'status' => true,
            ],

            // USD Plans
            [
                'plan_for' => 'doctor',
                'title' => 'Trial',
                'price' => 0,
                'currency' => 'USD',
                'frequency' => 'monthly',
                'features' => $features,
                'permissions' => $permissions,
                'status' => true,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                [
                    'title' => $planData['title'],
                    'currency' => $planData['currency'],
                    'frequency' => $planData['frequency'],
                ],
                $planData
            );
        }

        $this->command->info('Trial subscription plans created successfully!');
        $this->command->info('Created 6 Trial plans (INR/USD × Monthly/Annual/Custom)');
    }
}

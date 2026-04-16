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
            <li>Streamlined Clinical Workflow</li>
            <li>SOAP Notes, ICD-10 & CPT (Manual)</li>
            <li>Prescriptions (Manual)</li>
            <li>Appointment Scheduling (Manual)</li>
            <li>Secure Document Management</li>
            <li>Reporting and Analytics</li>
            <li>Forms & Templates </li>
            <li>Flexible Visit Configuration</li>
            <li>Encrypted Messages </li>
            <li>Alerts & Notifications</li>
            <li>Medical records</li>
            <li>User Roles 1 Doctor + 2 Users(assistant)</li>
            <li>Reports</li>
            <li>Audit Logs</li>
            <li>Secure Patient Portal </li>
            <li>Patient Charts</li>
            <li>Patient History</li>
            <li>Lifestyle & Social History</li>
            <li>Family History</li>
            <li>Patient Demographics</li>
            <li>Medical Conditions</li>
            <li>Supplements</li>
            <li>Allergies</li>
            <li>Immunizations</li>
            <li>Patient Summary</li>
            <li>Calender</li>
            <li>Vaccines</li>
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

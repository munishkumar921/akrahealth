<?php

namespace Database\Seeders;

use App\Models\Icd9;
use App\Models\Question;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run dependent seeders
        $this->call([
            LabCategoriesSeeder::class,
            SpecialitySeeder::class,
            CountrySeeder::class,
            StatesSeeder::class,
            SubscriptionPlanPermissionSeeder::class,
            TrialSubscriptionPlanSeeder::class,
        ]);
        /* --------------------------
         |  ROLES
         -------------------------- */
        $roles = [
            'SuperAdmin', 'Admin', 'Doctor', 'Patient',
            'Virtual Assistant', 'Lab', 'Pharmacy', 'Biller',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        /* --------------------------
         |  SECURITY QUESTIONS
         -------------------------- */
        $questions = [
            'What was your childhood nickname?',
            'In what city did you meet your spouse/significant other?',
            'What is the name of your favorite childhood friend?',
            'What is the middle name of your oldest child?',
            'What is your oldest siblings middle name?',
            'What is your oldest cousin\'s first and last name?',
            'What was the name of your first stuffed animal?',
            'In what city or town did your mother and father meet?',
            'In what city or town was your first job?',
            'What is the name of the place your wedding reception was held?',
            'What is the name of a college you applied to but didn\'t attend?',
        ];

        foreach ($questions as $q) {
            Question::firstOrCreate(['question' => $q]);
        }

        /* --------------------------
         |  DEFAULT SUPER ADMIN
         -------------------------- */
        $user = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => '$2y$10$fM6kjJ16Fcynthc5JJzsuOTUNXKnGKv1AABshwJ4HI719sFsr8htq', // dev@2000
                'is_active' => true,
                'is_email_verified' => true,
            ]
        );

        if (! $user->hasRole('SuperAdmin')) {
            $user->assignRole('SuperAdmin');
        }

        /* --------------------------
         |  SKILLS SEEDING
         -------------------------- */
        $skills = [
            'Microsoft',
            'Organizer',
            'Review and feedback collection',
            'Record management',
            'Phone support',
            'File maintenance',
            'Task coordination',
            'Appointment scheduling',
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(['skill' => $skill]);
        }

        /* --------------------------
         |  ICD9 SEEDING
         -------------------------- */
        Icd9::firstOrCreate(
            ['icd9' => '00001'],
            [
                'icd9_description' => 'Cholera',
                'icd9_common' => 1,
            ]
        );
    }
}

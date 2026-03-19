<?php

namespace Database\Seeders;

use App\Models\LabTestCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LabCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Pathology',
                'description' => 'Laboratory analysis of body fluids and tissues to diagnose diseases',
            ],
            [
                'name' => 'Radiology',
                'description' => 'Medical imaging techniques including X-rays, CT scans, and MRI',
            ],
            [
                'name' => 'Imaging',
                'description' => 'Advanced diagnostic imaging services such as CT, MRI, Ultrasound, and X-ray',
            ],
            [
                'name' => 'Cardiopulmonary',
                'description' => 'Heart and lung diagnostic services including ECG, Echo, TMT, and PFT',
            ],
            [
                'name' => 'Microbiology',
                'description' => 'Study of microorganisms for disease diagnosis and treatment',
            ],
            [
                'name' => 'Biochemistry',
                'description' => 'Chemical processes within living organisms for diagnostic purposes',
            ],
            [
                'name' => 'Hematology',
                'description' => 'Study of blood and blood-forming organs for disease diagnosis',
            ],
            [
                'name' => 'Blood Bank',
                'description' => 'Collection, processing, and storage of blood for transfusion',
            ],
            [
                'name' => 'Molecular Biology',
                'description' => 'DNA and RNA analysis for genetic and infectious disease testing',
            ],
            [
                'name' => 'Cytology',
                'description' => 'Study of cells for cancer diagnosis and detection of abnormalities',
            ],
            [
                'name' => 'Immunology',
                'description' => 'Study of immune system and immune-related disorders',
            ],
            [
                'name' => 'Endocrinology',
                'description' => 'Hormone analysis and endocrine system testing',
            ],
            [
                'name' => 'Cardiology',
                'description' => 'Heart and cardiovascular system diagnostic tests',
            ],
            [
                'name' => 'Neurology',
                'description' => 'Nervous system diagnostic testing and analysis',
            ],
            [
                'name' => 'Toxicology',
                'description' => 'Detection and analysis of drugs, poisons, and toxins',
            ],
            [
                'name' => 'Serology',
                'description' => 'Blood serum testing for antibodies and infectious diseases',
            ],
            [
                'name' => 'Urinalysis',
                'description' => 'Urine testing for kidney disease and metabolic conditions',
            ],
            [
                'name' => 'Fertility',
                'description' => 'Reproductive health testing and fertility assessment',
            ],
            [
                'name' => 'Dermatopathology',
                'description' => 'Skin tissue analysis for dermatological conditions',
            ],
            [
                'name' => 'Oncology',
                'description' => 'Cancer diagnosis, tumor markers, and cancer monitoring',
            ],
            [
                'name' => 'Referral',
                'description' => 'Referral services to external diagnostic or specialty centers',
            ],
        ];

        foreach ($services as $service) {
            $category = LabTestCategory::firstOrNew(['name' => $service['name']]);

            if (! $category->exists) {
                $category->id = Str::uuid();
            }

            $category->description = $service['description'];
            $category->is_active = true;
            $category->save();
        }
    }
}

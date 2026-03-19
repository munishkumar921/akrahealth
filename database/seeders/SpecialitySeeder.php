<?php

namespace Database\Seeders;

use App\Models\Speciality;
use Illuminate\Database\Seeder;

class SpecialitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialities = [
            [
                'name' => 'Cardiologist',
                'img' => 'cardiologist-cardiothoracic-surgeon.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Cardiothoracic Surgeon',
                'img' => 'cardiologist-cardiothoracic-surgeon.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Neurologist',
                'img' => 'neurologist-neurosurgeon-psychiatrist-psychologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Neurosurgeon',
                'img' => 'neurologist-neurosurgeon-psychiatrist-psychologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Psychiatrist',
                'img' => 'neurologist-neurosurgeon-psychiatrist-psychologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Psychologist',
                'img' => 'neurologist-neurosurgeon-psychiatrist-psychologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Pulmonologist',
                'img' => 'pulmonologist-chest-physician.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Chest Physician',
                'img' => 'pulmonologist-chest-physician.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Gastroenterologist',
                'img' => 'gastroenterologist-hepatologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Hepatologist',
                'img' => 'gastroenterologist-hepatologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Hematologist',
                'img' => 'hematologist-oncologist-radiation-oncologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Oncologist',
                'img' => 'hematologist-oncologist-radiation-oncologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Radiation Oncologist',
                'img' => 'hematologist-oncologist-radiation-oncologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Orthopedic Surgeon',
                'img' => 'orthopedic-surgeon-rheumatologist-sports-medicine.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Rheumatologist',
                'img' => 'orthopedic-surgeon-rheumatologist-sports-medicine.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Sports Medicine',
                'img' => 'orthopedic-surgeon-rheumatologist-sports-medicine.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Pediatrician',
                'img' => 'pediatrician-neonatologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Neonatologist',
                'img' => 'pediatrician-neonatologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Geriatrician',
                'img' => 'pediatrician-neonatologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Ophthalmologist',
                'img' => 'ophthalmologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'ENT Specialist',
                'img' => 'ophthalmologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Dentist',
                'img' => 'dentist-oral-surgeon-orthodontist-periodontist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Oral Surgeon',
                'img' => 'dentist-oral-surgeon-orthodontist-periodontist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Orthodontist',
                'img' => 'dentist-oral-surgeon-orthodontist-periodontist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Periodontist',
                'img' => 'dentist-oral-surgeon-orthodontist-periodontist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Gynecologist',
                'img' => 'gynecologist-obstetrician.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Obstetrician',
                'img' => 'gynecologist-obstetrician.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Andrologist',
                'img' => 'geriatrician.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Urologist',
                'img' => 'geriatrician.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Dermatologist',
                'img' => 'dermatologist-cosmetologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Cosmetologist',
                'img' => 'dermatologist-cosmetologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Sexologist',
                'img' => 'sexologist-urologist-andrologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Radiologist',
                'img' => 'radiologist-pathologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Pathologist',
                'img' => 'radiologist-pathologist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Dietitian',
                'img' => 'dietitian-nutritionist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Nutritionist',
                'img' => 'dietitian-nutritionist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Physiotherapist',
                'img' => 'physiotherapist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Ayurveda Specialist',
                'img' => 'ayurveda-specialist.png',
                'type' => 'Doctor',
            ],
            [
                'name' => 'Homeopathy Doctor',
                'img' => 'homeopathy-doctor.png',
                'type' => 'Doctor',

            ],
            [
                'name' => 'General Physician',
                'img' => 'general-physician-family-medicine-internal-medicine.png',
                'type' => 'Doctor',

            ],
            [
                'name' => 'Family Medicine',
                'img' => 'general-physician-family-medicine-internal-medicine.png',
                'type' => 'Doctor',

            ],
            [
                'name' => 'Internal Medicine',
                'img' => 'general-physician-family-medicine-internal-medicine.png',
                'type' => 'Doctor',

            ],
            [
                'name' => 'Psychiatrist',
                'img' => 'psychiatrist-psychologist.png',
                'type' => 'Doctor',

            ],
            [
                'name' => 'Psychologist',
                'img' => 'psychiatrist-psychologist.png',
                'type' => 'Doctor',
            ],
        ];

        foreach ($specialities as $speciality) {

            $banner = isset($speciality['img'])
                ? '/images/specialities/'.$speciality['img']
                : ($speciality['icon'] ?? null);

            Speciality::updateOrCreate([
                'name' => $speciality['name'],
                'description' => $speciality['description'] ?? null,
                'banner' => $banner,
                'type' => $speciality['type'],
                'is_active' => true,
            ]);
        }
    }
}

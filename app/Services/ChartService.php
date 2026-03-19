<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorPatient;
use App\Models\Document;
use App\Models\Encounter;
use App\Models\Hospital;
use App\Models\Patient;
use App\Traits\CommonTrait;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChartService extends BaseService
{
    use CommonTrait;

    public $encounter;

    /**
     * __construct
     *
     * @param  mixed  $encounter
     * @return void
     */
    public function __construct(EncounterService $encounter)
    {
        $this->encounter = $encounter;
    }

    /**
     * Generate and zip CCR charts for all patients in a branch.
     *
     * @param  int|string  $branchId
     * @return string|null The path to the generated ZIP file, or null on failure.
     */
    public function generateAndZipChartsForBranch($branchId)
    {
        $branchId = $branchId;
        // Get all doctors for the selected branch
        $doctorIds = Doctor::where('hospital_id', $branchId)->pluck('id');

        if ($doctorIds->isEmpty()) {
            Log::warning('No doctors found for branch.', ['branch_id' => $branchId]);

            return null;
        }

        // Get unique patient IDs linked to these doctors
        $patientIds = DoctorPatient::whereIn('doctor_id', $doctorIds)
            ->pluck('patient_id')
            ->unique()
            ->values();

        if ($patientIds->isEmpty()) {
            Log::warning('No patients found for branch.', ['branch_id' => $branchId]);

            return null;
        }

        // Create a master ZIP for this branch
        $zip = new \ZipArchive;
        $zipDir = storage_path('app/public/ccr');
        $tempDir = $zipDir.'/temp';

        if (! File::isDirectory($zipDir)) {
            File::makeDirectory($zipDir, 0775, true, true);
        }
        if (File::isDirectory($tempDir)) {
            File::cleanDirectory($tempDir);
        } else {
            File::makeDirectory($tempDir, 0775, true, true);
        }

        $zipFileName = 'branch_'.$branchId.'_charts_'.time().'.zip';
        $zipPath = $zipDir.DIRECTORY_SEPARATOR.$zipFileName;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            Log::error('Unable to create charts ZIP file.', ['path' => $zipPath]);

            return null;
        }

        foreach ($patientIds as $patientId) {
            $chartFile = $this->generateChart($patientId, $branchId);

            if ($chartFile && file_exists($chartFile)) {
                $zip->addFile($chartFile, basename($chartFile));
            }
        }

        $zip->close();

        // Clean up temporary individual files
        File::deleteDirectory($tempDir);

        if (! file_exists($zipPath) || File::size($zipPath) === 0) {
            Log::error('Failed to generate a valid charts ZIP.', ['path' => $zipPath]);
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            return null;
        }

        return $zipPath;
    }

    public function generateChart($patientId, $hospitalId)
    {

        ini_set('memory_limit', '196M');
        $hospital = Hospital::where('id', '=', $hospitalId)->first();
        $patient = Patient::where('id', '=', $patientId)->first();

        if (! $hospital || ! $patient) {
            Log::warning('Unable to generate chart, missing hospital or patient.', [
                'hospital_id' => $hospitalId,
                'patient_id' => $patientId,
            ]);

            return null;
        }

        $filename_string = Str::random(30);
        $pdf_arr = [];

        // Ensure public temp directory exists for generated PDFs
        $publicTempDir = public_path('temp');
        if (! File::isDirectory($publicTempDir)) {
            File::makeDirectory($publicTempDir, 0775, true, true);
        }

        // ---------------------------------------------------------------------
        // 1. Encounters PDF (detailed visit notes)
        // ---------------------------------------------------------------------
        $file_path_enc = $publicTempDir.'/'.time().'_'.$filename_string.'_printchart.pdf';

        $html = '<html><head><meta charset="utf-8"><style>
                    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
                    h1, h2, h3 { margin: 0 0 6px 0; padding: 0; }
                    .header { border-bottom: 1px solid #ccc; margin-bottom: 10px; padding-bottom: 5px; }
                    .section-title { background:#666; color:#fff; padding:4px 6px; margin: 20px 0 8px; font-size:12px; }
                    .encounter { margin-bottom: 18px; page-break-inside: avoid; }
                    .label { font-weight:bold; }
                    table.meta { width:100%; border-collapse:collapse; margin-bottom:8px; }
                    table.meta td { padding:2px 4px; vertical-align:top; }
                </style></head><body>';

        // Practice / patient header
        $html .= '<div class="header">';
        $html .= '<h1>Patient Chart</h1>';
        $html .= '<p class="label">Practice:</p><p>'.$this->getPracticeInfo($hospital).'</p>';
        $html .= '<p class="label">Patient:</p>';
        $html .= '<p>'.e($patient->name ?? '').'<br/>DOB: '.
            ($patient->user && $patient->user->dob ? Carbon::parse($patient->user->dob)->format('m/d/Y') : 'N/A').
            '<br/>MRN: '.e($patient->id).'</p>';
        $html .= '</div>';
        // Signed encounters for this patient in this hospital
        $query1 = Encounter::where('patient_id', '=', $patientId)
            // ->where('encounter_signed', '=', 'Yes')
            ->where('addendum', '=', 'n')
            ->where('hospital_id', '=', $hospitalId)
            ->orderBy('encounter_date_of_service', 'desc')
            ->get();
        $query3 = Document::where('patient_id', '=', $patientId)
            ->orderBy('date', 'desc')->get();

        if ($query1->isNotEmpty()) {
            $html .= '<div class="section-title">ENCOUNTERS</div>';
        }
        foreach ($query1 as $row) {
            $encounter = Encounter::with([
                'patient.user',
                'doctor.user',
                'appointment.patient.user',
                'appointment.doctor.user',
                'patientIllnessHistory',
                'reviewOfSystem',
                'vital',
                'assessment',
                'plan',
                'physicalExamination',
                'prescriptions',
                'supplements',
                'labOrders',
                'radiologyOrders',
                'cardOrders',
                'images',
                'photos',
                'procedures',
                'billingCore',
                'billing',
                'referral.doctor.user',
            ])->where('id', $row->id)->first();
            if (! $encounter) {
                continue;
            }
            $html .= '<div class="encounter">';
            $html .= '<h2>Encounter - '.Carbon::parse($encounter->encounter_date_of_service)->format('m/d/Y').'</h2>';

            $html .= '<table class="meta"><tr>';
            $html .= '<td><span class="label">Type:</span> '.e($encounter->encounter_type ?? '-').'</td>';
            $html .= '<td><span class="label">Location:</span> '.e($encounter->encounter_location ?? '-').'</td>';
            $html .= '<td><span class="label">Provider:</span> '.e(optional($encounter->doctor->user)->name ?? 'N/A').'</td>';
            $html .= '</tr></table>';

            if ($encounter->chief_complaint) {
                $html .= '<p><span class="label">Chief Complaint:</span> '.nl2br(e($encounter->chief_complaint)).'</p>';
            }

            if ($encounter->patientIllnessHistory && $encounter->patientIllnessHistory->hpi) {
                $html .= '<p><span class="label">History of Present Illness:</span><br>'.
                    nl2br(e($encounter->patientIllnessHistory->hpi)).'</p>';
            }

            if ($encounter->physicalExamination && $encounter->physicalExamination->pe) {
                $html .= '<p><span class="label">Physical Examination:</span><br>'.
                    nl2br(e($encounter->physicalExamination->pe)).'</p>';
            }

            if ($encounter->assessment) {
                $assessmentText = trim(
                    ($encounter->assessment->assessment_other ?? '').
                    "\n".
                    ($encounter->assessment->assessment_discussion ?? '')
                );
                if ($assessmentText !== '') {
                    $html .= '<p><span class="label">Assessment:</span><br>'.nl2br(e($assessmentText)).'</p>';
                }
            }

            if ($encounter->plan) {
                $planParts = [];
                if ($encounter->plan->plan) {
                    $planParts[] = 'Plan: '.$encounter->plan->plan;
                }
                if ($encounter->plan->followup) {
                    $planParts[] = 'Follow-up: '.$encounter->plan->followup;
                }
                if ($encounter->plan->duration) {
                    $planParts[] = 'Duration: '.$encounter->plan->duration;
                }
                if (! empty($planParts)) {
                    $html .= '<p><span class="label">Plan:</span><br>'.nl2br(e(implode("\n", $planParts))).'</p>';
                }
            }

            $html .= '</div>'; // .encounter

            $html .= '</body></html>';

            // Generate encounters PDF
            $this->generate_pdf($html, $file_path_enc);
            $pdf_arr[] = $file_path_enc;

            // ---------------------------------------------------------------------
            // 2. CCR-style summary PDF (high-level overview)
            // ---------------------------------------------------------------------
            $file_path_ccr = $publicTempDir.'/'.time().'_'.$filename_string.'_ccr.pdf';

            $html_ccr = '<html><head><meta charset="utf-8"><style>
                        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
                        h1, h2, h3 { margin: 0 0 6px 0; padding: 0; }
                        .section-title { background:#666; color:#fff; padding:4px 6px; margin: 20px 0 8px; font-size:12px; }
                    </style></head><body>';

            $html_ccr .= '<h1>Continuity of Care Summary</h1>';
            $html_ccr .= '<p><strong>Patient:</strong> '.e($patient->name ?? '').'<br/>DOB: '.
                ($patient->user && $patient->user->dob ? Carbon::parse($patient->user->dob)->format('m/d/Y') : 'N/A').
                '<br/>MRN: '.e($patient->id).'</p>';

            // Insurance
            $insuranceInfo = $this->getInsuranceInfo($patientId);
            if ($insuranceInfo !== '') {
                $html_ccr .= '<div class="section-title">INSURANCE</div>';
                $html_ccr .= '<p>'.$insuranceInfo.'</p>';
            }

            // Allergies
            $allergiesHtml = $this->getAllergyInfo($patientId);
            if ($allergiesHtml !== '') {
                $html_ccr .= '<div class="section-title">ALLERGIES</div>';
                $html_ccr .= $allergiesHtml;
            }

            // Basic encounter count information
            if ($query1->isNotEmpty()) {
                $html_ccr .= '<div class="section-title">ENCOUNTER SUMMARY</div>';
                $html_ccr .= '<p>Total signed encounters in this practice: '.$query1->count().'</p>';
                $latest = $query1->first();
                $html_ccr .= '<p>Most recent visit: '.
                    Carbon::parse($latest->encounter_date_of_service)->format('m/d/Y').
                    ' ('.e($latest->encounter_type ?? 'Encounter').')</p>';
            }

            $html_ccr .= '</body></html>';

            $this->generate_pdf($html_ccr, $file_path_ccr);
            $pdf_arr[] = $file_path_ccr;
            // Gather documents
            foreach ($query3 as $row3) {
                $pdf_arr[] = $row3->documents_url;
            }
            // Compile and save final merged PDF using iio/libmergepdf
            $merger = new Merger;
            foreach ($pdf_arr as $pdf_item) {
                if (file_exists($pdf_item)) {
                    $file_parts = pathinfo($pdf_item);
                    if (($file_parts['extension'] ?? '') === 'pdf') {
                        $merger->addFile($pdf_item);
                    }
                }
            }

            $file_path = $publicTempDir.'/'.time().'_'.$filename_string.'_'.$patientId.'_printchart_final.pdf';
            $mergedContent = $merger->merge();
            file_put_contents($file_path, $mergedContent);

            return $file_path;
        }

    }
}

<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Mail\MedicationPrescribed;
use App\Models\Allergy;
use App\Models\Doctor;
use App\Models\Encounter;
use App\Models\Hospital;
use App\Models\Insurance;
use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\Prescription;
use App\Models\User;
use App\Notifications\PrescriptionCreatedNotification;
use App\Traits\AlertTrait;
use App\Traits\CommonTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PrescriptionController extends Controller
{
    use AlertTrait, CommonTrait;

    public function upsert(Request $request)
    {
        $data = $request->all();
        $encounter = Encounter::find($data['encounter_id']);
        if (! $encounter) {
            return back()->with('error', 'Encounter not found.');
        }

        $hospitalId = auth()->user()->doctor->hospital_id;
        $patientId = auth()->user()->doctor->selected_patient_id ?? null;

        // Create prescription
        $prescription = Prescription::create([
            'encounter_id' => $data['encounter_id'] ?? null,
            'doctor_id' => $encounter->doctor_id,
            'patient_id' => $encounter->patient_id,
            'medication' => $data['medication'] ?? null,
            'date_active' => $data['date_active'] ?? null,
            'dosage' => $data['dosage'] ?? null,
            'dosage_unit' => $data['dosage_unit'] ?? null,
            'frequency' => $data['frequency'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'quantity' => $data['quantity'] ?? null,
            'days' => $data['days'] ?? null,
            'due_date' => (isset($data['date_active']) && isset($data['days'])) ? date('Y-m-d', strtotime($data['date_active']) + ($data['days'] * 86400)) : null,
            'refill' => $data['refill'] ?? 0,
            'route' => $data['route'] ?? null,
            'sig' => $data['sig'] ?? null,
            'prescription' => 'pending',
        ]);

        // Send notification
        $to = $data['notification'] ?? '';
        // if (($data['action_after_saving'] ?? '') != 'sign') {
        $this->prescription_notification($prescription->id, $to);
        // Send bell notification to patient
        $patient = Patient::find($prescription->patient_id);
        if ($patient && $patient->user) {
            $patient->user->notify(new PrescriptionCreatedNotification($prescription));
        }
        // Send bell notification to pharmacies
        $pharmacies = Pharmacy::where('is_active', true)->get();
        foreach ($pharmacies as $pharmacy) {
            if ($pharmacy->user) {
                $pharmacy->user->notify(new PrescriptionCreatedNotification($prescription));
            }
        }
        // }else{
        // $json_row = Prescription::where('id', $prescription->id)->first();
        // $prescription_json = $this->resource_detail($json_row, 'MedicationRequest');
        // $json_data['json'] = json_encode($prescription_json);
        // Prescription::where('id', '=', $json_row->id)->update($json_data);
        // }

        // MTM Alerts
        $hospital = Hospital::find($hospitalId);
        if ($hospital && $hospital->mtm_extension == 'y') {
            $this->add_mtm_alert($patientId, 'medications');
        }

        return $this->getPrescription($data['encounter_id']);
    }

    public function show($id)
    {
        $prescription = Prescription::find($id);

        if (! $prescription) {
            return Inertia::render('Prescriptions/Show', ['data' => null]);
        }

        $data = [];

        if (! empty($prescription->id)) {
            // GoodRX Search & Info
            $med = explode(' ', $prescription->medication ?? '');
            $data['rx'] = $this->goodrx_drug_search($med[0] ?? '');
            $data['link'] = $this->goodrx_information(
                $prescription->medication,
                $prescription->dosage.' '.$prescription->dosage_unit
            );

            // Generate PDF preview
            $previewData = $this->generatePdfPreview($prescription->id);
            if ($previewData) {
                $data = array_merge($data, $previewData);
            }

            $data['dropdown'] = [
                'items' => [
                    [
                        'type' => 'item',
                        'label' => 'GoodRX',
                        'icon' => 'fa-chevron-down',
                        'url' => '#goodrx_container',
                    ],
                ],
            ];
        }

        return Inertia::render('Prescriptions/Show', ['data' => $data]);
    }

    public function delete($id)
    {
        $prescription = Prescription::find($id);

        if (! $prescription) {
            return response()->json(['error' => 'Prescription not found'], 404);
        }

        $encounter_id = $prescription->encounter_id;
        $prescription->delete();

        return $this->getPrescription($encounter_id);
    }

    public function getPrescription($encounter_id)
    {
        return Prescription::where('encounter_id', $encounter_id)
            ->select(['id', 'medication', 'dosage', 'dosage_unit', 'frequency'])
            ->get();
    }

    public function downloadPrescriptionPdf($id)
    {
        try {
            $patientId = auth()->user()->doctor->selected_patient_id ?? null;
            $pageData = $this->page_medication($id, $patientId);

            if (empty($pageData)) {
                throw new \Exception('No prescription data found');
            }

            $html = view('pdf.prescription', $pageData)->render();
            $filename = 'prescription_'.$id.'.pdf';

            // Debug: Check if HTML is generated
            \Log::info('HTML generated, length: '.strlen($html));

            $pdf = PDF::loadHTML($html)
                ->setPaper('a4')
                ->setOption('isHtml5ParserEnabled', true);

            // Set options for remote content and base path
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
            $pdf->getDomPDF()->set_option('chroot', public_path());
            $pdf->setBasePath(public_path());

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('PDF download failed: '.$e->getMessage());

            // Return a simple error response that works with downloads
            return response('PDF Generation Failed: '.$e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    protected function generatePdfPreview($prescriptionId)
    {
        ini_set('memory_limit', '196M');

        $pageData = $this->page_medication($prescriptionId, Prescription::find($prescriptionId)->patient_id);

        if (empty($pageData)) {
            return null;
        }

        $html = view('pdf.prescription', $pageData)->render();
        $filename = time().'_rx.pdf';
        $file_path = public_path("temp/$filename");

        // Ensure temp directory exists
        if (! is_dir(public_path('temp'))) {
            mkdir(public_path('temp'), 0777, true);
        }

        // Generate PDF
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $pdf->save($file_path);

        if (! file_exists($file_path)) {
            return null;
        }

        // Convert to PNG for preview
        try {
            $imagick = new \Imagick;
            $imagick->setResolution(100, 100);
            $imagick->readImage($file_path);
            $imagick->setImageFormat('png');
            $pngPath = public_path("temp/{$filename}_preview.png");
            $imagick->writeImage($pngPath);
            $imagick->clear();

            return [
                'rx_jpg' => asset("temp/{$filename}_preview.png"),
                'document_url' => asset("temp/$filename"),
            ];
        } catch (\Exception $e) {
            \Log::error('Image conversion failed: '.$e->getMessage());

            return null;
        }
    }

    protected function prescription_notification($id, $to = '')
    {
        $patientId = auth()->user()->doctor->selected_patient_id ?? null;
        $patient = Patient::find($patientId);

        if (! $patient) {
            return;
        }

        if ($to == '') {
            $to = $patient->reminder_to;
        }

        if (! $to) {
            return;
        }

        $link = route('prescription.show', $id);

        try {
            Mail::to($to)->send(new MedicationPrescribed(
                'You have a new medication prescribed to you. For more details, click here: '.$link,
                $link
            ));
        } catch (\Exception $e) {
            \Log::error('Failed to send prescription email: '.$e->getMessage());
        }
    }

    protected function page_medication($id, $patientId)
    {
        $prescription = Prescription::find($id);
        if (! $prescription) {
            return [];
        }

        $patient = Patient::find($patientId);
        if (! $patient) {
            return [];
        }

        $doctor = Doctor::find($prescription->doctor_id);
        $hospital = $doctor?->hospital ?? Hospital::find(auth()->user()->doctor->hospital_id);

        $data = [];
        $data['prescription'] = $prescription;
        $data['patientInfo'] = $patient;
        $data['practiceName'] = $hospital?->name;
        $data['practiceInfo'] = $this->getPracticeInfo($hospital);
        $data['practiceLogo'] = $this->getPracticeLogo($hospital);
        $data['rxicon'] = $this->getRxIcon();
        $data['dob'] = date('m/d/Y', strtotime($patient->dob));
        $data['rx_date'] = date('m/d/Y', strtotime($prescription->created_at));
        $data['quantity_words'] = strtoupper($this->convert_number($prescription->quantity));
        $data['refill_words'] = strtoupper($this->convert_number($prescription->refill));
        $data['insuranceInfo'] = $this->getInsuranceInfo($patientId);
        $data['allergyInfo'] = $this->getAllergyInfo($patientId);
        $data['signature'] = $this->signature($prescription->id);

        return $data;
    }

    protected function getPracticeInfo($hospital)
    {
        if (! $hospital) {
            return '';
        }

        $address = $hospital->street_address1 ?? '';
        if ($hospital->street_address2) {
            $address .= ' '.$hospital->street_address2;
        }
        $address .= '<br />'.($hospital->city ?? '').', '.($hospital->state ?? '').' '.($hospital->zip ?? '');
        $address .= '<br />Phone: '.($hospital->phone ?? '');

        return $address;
    }

    protected function getPracticeLogo($hospital)
    {
        if (! $hospital || empty($hospital->practice_logo)) {
            return '<br><br><br><br><br>';
        }

        $filePath = public_path($hospital->practice_logo);
        if (file_exists($filePath)) {
            $url = asset($hospital->practice_logo);

            return "<img src=\"{$url}\" height=\"80\" alt=\"Practice Logo\" />";
        }

        return '<br><br><br><br><br>';
    }

    protected function getRxIcon()
    {
        $rxiconUrl = asset('assets/images/rxicon.png');

        return '<img src="'.$rxiconUrl.'" width="30" height="30" border="0" alt="Rx" />';
    }

    protected function getInsuranceInfo($patientId)
    {
        $insurances = Insurance::where('patient_id', $patientId)->get();
        if ($insurances->isEmpty()) {
            return '';
        }

        $info = '';
        foreach ($insurances as $row) {
            $info .= "{$row->insurance_plan_name}; ID: {$row->insurance_id_num}; Group: {$row->insurance_group}; "
                   ."{$row->insurance_insu_lastname}, {$row->insurance_insu_firstname}<br><br>";
        }

        return $info;
    }

    protected function getAllergyInfo($patientId)
    {
        $allergies = Allergy::where('patient_id', $patientId)->whereNull('date_inactive')->get();

        if ($allergies->isEmpty()) {
            return 'No known allergies.';
        }

        $html = '<ul>';
        foreach ($allergies as $row) {
            $html .= "<li>{$row->allergies_medicine}</li>";
        }
        $html .= '</ul>';

        return $html;
    }

    protected function signature($id)
    {
        $user = User::find(auth()->id());
        if (! $user) {
            return '<br><br><br><br><br><br><br>Unknown Provider';
        }

        $doctor = Doctor::where('user_id', auth()->id())->first();
        $signature = '<br><br><br><br><br><br><br>'.e($user->displayname ?? 'Unknown Provider');

        if ($doctor && ! empty($doctor->doctor_signature)) {
            $signaturePath = public_path(ltrim($doctor->doctor_signature, '/'));

            if (file_exists($signaturePath)) {
                $imgData = base64_encode(file_get_contents($signaturePath));
                $imgSrc = 'data:image/png;base64,'.$imgData;
                $signature = '<img src="'.$imgSrc.'" height="50" border="0" /><br>'.e($user->displayname ?? 'Unknown Provider');
            }
        }

        return $signature;
    }

    protected function convert_number($number)
    {
        $number = (int) $number;
        $words = [
            0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four',
            5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine',
            10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen',
            14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen',
            18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty',
        ];

        return $words[$number] ?? (string) $number;
    }
}

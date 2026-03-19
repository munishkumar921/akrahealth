<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Letter templates configuration
     */
    protected $letterTemplates = [
        'Referral Letter' => [
            'name' => 'Referral Letter',
            'template' => 'referral',
            'description' => 'Refer patient to specialist',
        ],
        'Medical Certificate' => [
            'name' => 'Medical Certificate',
            'template' => 'certificate',
            'description' => 'Medical fitness or illness certificate',
        ],
        'Discharge Summary' => [
            'name' => 'Discharge Summary',
            'template' => 'discharge',
            'description' => 'Hospital discharge documentation',
        ],
        'Consultation Report' => [
            'name' => 'Consultation Report',
            'template' => 'consultation',
            'description' => 'Specialist consultation findings',
        ],
        'Prescription Letter' => [
            'name' => 'Prescription Letter',
            'template' => 'prescription',
            'description' => 'Detailed prescription with instructions',
        ],
        'Test Results Letter' => [
            'name' => 'Test Results Letter',
            'template' => 'test_results',
            'description' => 'Laboratory or imaging test results',
        ],
        'Follow-up Letter' => [
            'name' => 'Follow-up Letter',
            'template' => 'followup',
            'description' => 'Follow-up appointment instructions',
        ],
        'Emergency Letter' => [
            'name' => 'Emergency Letter',
            'template' => 'emergency',
            'description' => 'Urgent medical attention letter',
        ],
    ];

    /**
     * Get all available letter templates
     */
    public function getLetterTemplates(): array
    {
        return $this->letterTemplates;
    }

    /**
     * Get specific template by type
     */
    public function getTemplateByType(string $letterType): ?array
    {
        return $this->letterTemplates[$letterType] ?? null;
    }

    /**
     * Get patient information for letter generation
     */
    public function getPatientInfo(string $patientId): array
    {
        $patient = Patient::find($patientId);

        if (! $patient) {
            return ['found' => false, 'message' => 'Patient not found'];
        }

        $user = $patient->user ?? null;

        try {
            $encounter = DB::table('encounters')
                ->where('patient_id', $patientId)
                ->whereNotNull('id')
                ->orderBy('id', 'desc')
                ->first();
            $lastEncounterDate = $encounter->encounter_date_of_service ?? null;
        } catch (\Exception $e) {
            $lastEncounterDate = null;
        }

        return [
            'found' => true,
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->full_name ?? ($patient->fname.' '.$patient->lname),
                'first_name' => $patient->fname,
                'last_name' => $patient->lname,
                'dob' => $patient->dob,
                'gender' => $patient->sex,
                'address' => $patient->address ?? '',
                'phone' => $patient->phone ?? '',
                'email' => $user->email ?? '',
                'last_encounter_date' => $lastEncounterDate,
            ],
        ];
    }

    /**
     * Get doctor information for letter generation
     */
    public function getDoctorInfo(): array
    {
        $user = auth()->user();
        $doctor = $user->doctor ?? null;

        if (! $doctor) {
            return ['found' => false, 'message' => 'Doctor profile not found'];
        }

        return [
            'found' => true,
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->full_name ?? $user->name,
                'specialty' => $doctor->specialty ?? 'General Practice',
                'qualification' => $doctor->qualification ?? '',
                'registration_number' => $doctor->registration_number ?? '',
                'phone' => $doctor->phone ?? $user->phone ?? '',
                'email' => $user->email,
                'address' => $doctor->address ?? '',
            ],
        ];
    }

    /**
     * Get hospital information for letter generation
     */
    public function getHospitalInfo(): array
    {
        $user = auth()->user();
        $doctor = $user->doctor ?? null;
        $hospitalId = $doctor?->hospital_id ?? $user?->hospital_id;
        $hospitalInfo = Hospital::find($hospitalId);

        if (! $hospitalInfo) {
            return ['found' => false, 'message' => 'Hospital profile not found'];
        }

        return [
            'found' => true,
            'hospital' => [
                'logo' => $hospitalInfo->logo ?? '',
                'name' => $hospitalInfo->name ?? '',
                'address' => $hospitalInfo->street_address1 ?? '',
                'address2' => $hospitalInfo->street_address2 ?? '',
                'city' => $hospitalInfo->city ?? '',
                'state' => $hospitalInfo->state ?? '',
                'zip' => $hospitalInfo->zip ?? '',
                'phone' => $hospitalInfo->phone ?? '',
                'email' => $hospitalInfo->email ?? '',
            ],
        ];
    }

    /**
     * Generate PDF content for a letter
     */
    public function generatePdfContent(array $letterData, string $templateType = 'default'): string
    {
        $patientInfo = $this->getPatientInfo($letterData['patient_id'] ?? '');
        $doctorInfo = $this->getDoctorInfo();
        $hospitalInfo = $this->getHospitalInfo();

        $data = array_merge([
            'letter_type' => $letterData['letterType'] ?? '',
            'subject' => $letterData['subject'] ?? '',
            'to' => $letterData['to'] ?? '',
            'body' => $letterData['body'] ?? '',
            'date' => date('F j, Y'),
            'template' => $templateType,
        ], $patientInfo, $doctorInfo, $hospitalInfo);

        $pdf = PDF::loadView('letters.templates.'.$templateType, $data);

        return $pdf->output();
    }

    /**
     * Get documents by patient and category/type
     */
    public function getDocumentsByPatient(string $patientId, ?string $type = null)
    {
        $query = Document::where('patient_id', $patientId)
            ->orderBy('created_at', 'desc');

        if ($type && $type !== 'All') {
            $query->where('type', $type);
        }

        return $query->get();
    }

    /**
     * Store generated letter as PDF with optional email sending
     */
    public function storeLetter(Request $request)
    {
        $validated = $request->validate([
            'letterType' => 'required|string',
            'subject' => 'required|string|max:255',
            'to' => 'required|string|max:255',
            'recipient_address' => 'nullable|string',
            'body' => 'required|string',
        ]);

        $user = auth()->user();
        $doctor = $user->doctor ?? null;
        $hospitalId = $doctor?->hospital_id ?? $user?->hospital_id ?? null;
        $patientId = $doctor?->selected_patient_id ?? $request->input('patient_id');

        $template = $this->getTemplateByType($validated['letterType']);
        $templateType = $template['template'] ?? 'default';

        $patientInfo = $this->getPatientInfo($patientId);
        $doctorInfo = $this->getDoctorInfo();
        $hospitalInfo = $this->getHospitalInfo();
        $adress = null;
        if ($request->has('user_id')) {
            $user = User::with('doctor', 'doctor.hospital', 'address')->find($request->input('user_id'));
            $adress = $user->address;

        }

        $data = array_merge([
            'letter_type' => $validated['letterType'],
            'subject' => $validated['subject'],
            'to' => $validated['to'],
            'recipient_address' => $adress ?? null,
            'body' => $validated['body'],
            'date' => date('F j, Y'),
            'template' => $templateType,
        ], $patientInfo, $doctorInfo, $hospitalInfo);

        $pdf = PDF::loadView('letters.templates.certificate', $data);
        $pdfContent = $pdf->output();

        $fileName = 'letter_'.time().'_'.preg_replace('/\s+/', '_', $validated['subject']).'.pdf';
        $filePath = 'letters/'.$fileName;
        Storage::disk('public')->put($filePath, $pdfContent);
        $publicUrl = 'storage/'.$filePath;

        $document = Document::create([
            'type' => 'letters',
            'description' => "{$validated['letterType']} - {$validated['subject']}",
            'url' => $publicUrl,
            'patient_id' => $patientId,
            'hospital_id' => $hospitalId,
            'doctor_id' => $doctor->id ?? null,
            'date' => date('Y-m-d'),
            'from' => $validated['to'],
        ]);

        if (request()->has('user_id')) {
            try {
                $recipient = User::find($request->input('user_id'));
                if ($recipient && $recipient->email) {
                    $this->sendLetterEmail(
                        $recipient->email,
                        $patientInfo['patient']['name'] ?? 'Patient',
                        $doctorInfo['doctor']['name'] ?? 'Doctor',
                        $validated['letterType'],
                        $validated['subject'],
                        $validated['body'],
                        $pdfContent,
                        $fileName
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send letter email', [
                    'error' => $e->getMessage(),
                    'recipient' => $validated['to'],
                ]);
            }
        }

        \Illuminate\Support\Facades\Log::info('Letter generated successfully', [
            'document_id' => $document->id,
            'patient_id' => $patientId,
            'letter_type' => $validated['letterType'],
            'subject' => $validated['subject'],
        ]);

        return $document;
    }

    /**
     * Send letter via email with PDF attachment
     */
    public function sendLetterEmail(
        string $recipientEmail,
        string $patientName,
        string $doctorName,
        string $letterType,
        string $subject,
        string $body,
        string $pdfContent,
        string $fileName
    ): bool {
        try {
            Mail::to($recipientEmail)->send(new \App\Mail\LetterMail(
                $patientName,
                $doctorName,
                $letterType,
                $subject,
                $body,
                $pdfContent,
                $fileName
            ));

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Email sending failed', [
                'error' => $e->getMessage(),
                'recipient' => $recipientEmail,
            ]);

            return false;
        }
    }

    /**
     * Upload document file and create/update document record
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp|max:10240', // 10MB max
            'type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'date' => 'nullable|date',
        ]);

        $user = auth()->user();
        $doctor = $user->doctor;

        $hospitalId = $doctor?->hospital_id ?? $user?->hospital_id ?? null;
        $patientId = $doctor?->selected_patient_id ?? auth()->user()->patient->id;

        $documentId = $request->input('id');
        $existingUrl = $request->input('existing_url');
        $file = $request->file('file');

        // Determine the URL to use
        $publicUrl = $existingUrl; // Default to existing URL if provided

        // If a new file is uploaded, process it
        if ($file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            // Generate unique filename
            $fileName = time().'_'.Str::random(10).'.'.$extension;
            $directory = 'documents/'.date('Y/m');
            $path = $file->storeAs($directory, $fileName, 'public');

            $publicUrl = 'storage/'.$path;
        } elseif (! $existingUrl && ! $documentId) {
            // New document without file - this is an error
            throw new \Exception('No file uploaded and no existing document found.');
        }

        // Determine document type from uploaded file extension or provided type
        $documentType = $request->input('type', 'other');

        // Get existing document if updating
        $existingDocument = $documentId ? Document::find($documentId) : null;
        $originalName = $existingDocument ? basename($existingDocument->url ?? '') : ($file ? $file->getClientOriginalName() : 'Untitled');

        $document = Document::updateOrCreate(
            ['id' => $documentId],
            [
                'type' => $documentType,
                'description' => $request->input('description', $originalName),
                'url' => $publicUrl,
                'patient_id' => $patientId ?? '',
                'doctor_id' => $doctor->id ?? '',
                'hospital_id' => $hospitalId ?? '',
                'date' => $request->input('date'),
                'from' => $request->input('from') ?? $user->name ?? 'Doctor',
            ]
        );

        return $document;
    }
}

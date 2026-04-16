<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Document;
use App\Models\Hospital;
use App\Models\Message;
use App\Models\Order;
use App\Models\Patient;
use App\Models\PatientRelate;
use App\Models\Test;
use App\Traits\CommonTrait;
use App\Traits\LangHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResultsService
{
    use CommonTrait, LangHelper;

    public function store($input)
    {
        $doctor = auth()->user()->doctor;
        $notificationService = app(InAppNotificationService::class);

        $hospitalId = $doctor->hospital_id;

        // Check if hospital exists, otherwise set to null
        if ($hospitalId && ! Hospital::find($hospitalId)) {
            $hospitalId = null;
        }

        $test = Test::updateOrCreate(

            ['id' => $input['id'] ?? null],
            [
                'patient_id' => $doctor->selected_patient_id,
                'doctor_id' => $input['doctor_id'],
                'hospital_id' => $hospitalId,
                'type' => $input['type'] ?? null,
                'name' => $input['testName'] ?? null,
                'result' => $input['result'] ?? null,
                'units' => $input['result_units'] ?? null,
                'reference' => $input['normal_reference_range'] ?? null,
                'flags' => $input['flag'] ?? null,
                'time' => $input['date'] ?? null,
                'code' => $input['loinc_code'] ?? null,
            ]
        );

        $notificationPayload = $notificationService->buildPayload(
            'Results uploaded',
            'New results have been uploaded to the patient chart.',
            'results_uploaded',
            [
                'encounter_id' => $input['encounter_id'] ?? null,
                'patient_id' => $doctor->selected_patient_id,
                'doctor_id' => $input['doctor_id'] ?? $doctor?->id,
                'related_model_type' => Test::class,
                'related_model_id' => $test->id,
            ]
        );

        $notificationService->notifyPatient(
            $doctor->selected_patient_id,
            array_merge($notificationPayload, [
                'recipient_role' => 'Patient',
                'action_url' => route('patient.results'),
                'message' => 'New test results have been uploaded to your chart.',
            ])
        );

        $notificationService->notifyUser(
            $doctor?->user,
            array_merge($notificationPayload, [
                'recipient_role' => 'Doctor',
            ])
        );

        app(EmailNotificationService::class)->queueDoctorOrderWorkflowEmail(
            $doctor?->user,
            'Results uploaded',
            'New results have been uploaded to the patient chart.',
            [
                'Patient: '.($test->patient?->user?->name ?? $test->patient?->name ?? 'N/A'),
                'Test: '.($test->name ?? 'N/A'),
                'Date: '.($test->time ?? 'N/A'),
            ],
            route('doctor.results.index')
        );
    }

    public function reply(array $input): bool
    {
        return DB::transaction(function () use ($input) {
            $notificationService = app(InAppNotificationService::class);

            $doctor = auth()->user()->doctor;

            if (! $doctor || ! $doctor->selected_patient_id) {
                throw new \Exception('No patient selected');
            }

            $pid = $doctor->selected_patient_id;

            $hospital = Hospital::find($doctor->hospital_id);
            if (! $hospital) {
                throw new \Exception('Hospital not found');
            }

            $patient = Patient::with('user')->find($pid);
            if (! $patient) {
                throw new \Exception('Patient not found');
            }

            $providerName = auth()->user()->name ?? 'Unknown Provider';
            $fromUserId = auth()->user()->id;

            $body = '';
            $testsPerformed = (array) ($input['testsPerformed'] ?? []);

            if (! empty($testsPerformed)) {
                $body .= "The following tests were performed:\n";

                foreach ($testsPerformed as $alertId) {
                    $alert = Alert::find($alertId);
                    if (! $alert) {
                        continue;
                    }

                    // Mark alert complete
                    $alert->update(['date_complete' => now()]);

                    // Update linked order
                    if ($alert->order_id) {
                        $order = Order::find($alert->order_id);
                        if ($order) {
                            $order->update(['orders_completed' => 'Yes']);

                            if (! empty($order->labs)) {
                                $body .= "• {$order->labs}\n";
                            }
                            if (! empty($order->radiology)) {
                                $body .= "• {$order->radiology}\n";
                            }
                            if (! empty($order->cp)) {
                                $body .= "• {$order->cp}\n";
                            }
                        }
                    }
                }
            }

            if (! empty($input['message'])) {
                $body .= "\n".trim($input['message']);
            }

            if (! empty($input['followup'])) {
                $body .= "\n\nFollow-up recommendations:\n".trim($input['followup']);
            }

            $action = $input['actionAfterSaving'] ?? 'Send Message to Portal';

            if ($action === 'Send Letter') {

                $dir = public_path(trim($hospital->documents_dir ?? 'documents', '/'))."/{$pid}";
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                $filePath = "{$dir}/letter_".time().'.pdf';

                $html = $this->generateLetterHtml(
                    nl2br(e($body)),
                    $patient,
                    $hospital,
                    $providerName
                );

                $this->generate_pdf($html, $filePath);

                $document = Document::create([
                    'patient_id' => $pid,
                    'hospital_id' => $hospital->id,
                    'url' => $filePath,
                    'type' => 'Letters',
                    'description' => 'Test Results Letter for '.$patient->name,
                    'from' => $providerName,
                    'viewed' => $providerName,
                    'date' => now(),
                ]);

                $notificationService->notifyUser(
                    $doctor?->user,
                    $notificationService->buildPayload(
                        'Document uploaded by lab',
                        'A result letter has been uploaded to the patient chart.',
                        'document_uploaded',
                        [
                            'recipient_role' => 'Doctor',
                            'patient_id' => $pid,
                            'doctor_id' => $doctor?->id,
                            'action_url' => route('doctor.documents.index'),
                            'related_model_type' => Document::class,
                            'related_model_id' => $document->id ?? null,
                        ]
                    )
                );
            }

            if ($action === 'Send Message to Portal') {
                // Check if patient has portal access
                $row_relate = PatientRelate::where('patient_id', '=', $pid)
                    ->where('hospital_id', '=', $hospital->id)
                    ->first();

                $hasPortalAccess = $row_relate && ! empty($row_relate->id);
                $patientEmail = $patient->user?->email ?? $patient->email;

                // Add closing message
                $body .= "\n\nPlease contact me if you have any questions.\n\nSincerely,\n{$providerName}";

                // Create document for the message
                $document = Document::create([
                    'patient_id' => $pid,
                    'hospital_id' => $hospital->id,
                    'url' => '',
                    'type' => 'Message',
                    'description' => 'Test Results Message',
                    'from' => $providerName,
                    'viewed' => $providerName,
                    'date' => now(),
                ]);

                // Determine recipient
                $toUserId = $patient->user_id ?? $fromUserId;

                // Create message record
                Message::create([
                    'patient_id' => $pid,
                    'to' => $toUserId,
                    'cc' => $fromUserId,
                    'from' => $fromUserId,
                    'subject' => 'Your Test Results',
                    'message' => $body,
                    'read' => false,
                    'hospital_id' => $hospital->id,
                    'document_id' => $document->id,
                    'date' => now(),
                ]);

                $notificationService->notifyPatient(
                    $pid,
                    $notificationService->buildPayload(
                        'Order update from lab',
                        'Your lab order has been updated and new results are available.',
                        'lab_order_updated',
                        [
                            'recipient_role' => 'Patient',
                            'patient_id' => $pid,
                            'doctor_id' => $doctor?->id,
                            'action_url' => route('patient.results'),
                        ]
                    )
                );

                $notificationService->notifyUser(
                    $doctor?->user,
                    $notificationService->buildPayload(
                        'Order updated by lab',
                        'Lab results have been uploaded for your patient.',
                        'lab_order_updated',
                        [
                            'recipient_role' => 'Doctor',
                            'patient_id' => $pid,
                            'doctor_id' => $doctor?->id,
                            'action_url' => route('doctor.results.index'),
                        ]
                    )
                );

                app(EmailNotificationService::class)->queueDoctorOrderWorkflowEmail(
                    $doctor?->user,
                    'Lab order updated',
                    'Lab results have been uploaded for your patient.',
                    [
                        'Patient: '.($patient->user?->name ?? $patient->name ?? 'N/A'),
                        'Action: Results uploaded to chart',
                    ],
                    route('doctor.results.index')
                );

                // Send email notification
                if (! empty($patientEmail)) {
                    try {
                        // Get practice email and portal URL from hospital
                        $practiceEmail = $hospital->email ?? config('mail.from.address');
                        $patientPortal = $hospital->patient_portal ?? config('app.url').'/patient/login';

                        // Send email notification using the new mailable
                        Mail::to($patientEmail)->send(
                            new \App\Mail\TestResultsNotification(
                                $hasPortalAccess,  // $portal
                                $providerName,      // $displayname
                                $practiceEmail,     // $email
                                $patientPortal      // $patient_portal
                            )
                        );
                    } catch (\Exception $e) {
                        // Log email failure but don't fail the operation
                        \Log::error('Failed to send test results email: '.$e->getMessage());
                    }
                }
            }

            return true;
        });
    }

    /**
     * Generate HTML content for the letter
     */
    protected function generateLetterHtml($body, $patient, $hospital, $displayname)
    {
        $patientName = e($patient->name);
        $patientDOB = $patient->user?->dob ? date('m/d/Y', strtotime($patient->user->dob)) : 'N/A';

        $hospitalAddress = '';
        if ($hospital) {
            $hospitalAddress = e($hospital->name).'<br>';
            if ($hospital->street_address1) {
                $hospitalAddress .= e($hospital->street_address1).'<br>';
            }
            if ($hospital->street_address2) {
                $hospitalAddress .= e($hospital->street_address2).'<br>';
            }
            $hospitalAddress .= e($hospital->city).', '.e($hospital->state).' '.e($hospital->zip);
            if ($hospital->phone) {
                $hospitalAddress .= '<br>Phone: '.e($hospital->phone);
            }
        }

        return view('letters.test-results', [
            'hospital' => $hospital,
            'hospitalAddress' => $hospitalAddress,
            'patientName' => $patientName,
            'patientDOB' => $patientDOB,
            'body' => nl2br(e($body)),
            'displayname' => e($displayname),
            'date' => date('m/d/Y'),
        ])->render();
    }
}

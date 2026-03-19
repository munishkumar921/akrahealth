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

        $hospitalId = $doctor->hospital_id;

        // Check if hospital exists, otherwise set to null
        if ($hospitalId && ! Hospital::find($hospitalId)) {
            $hospitalId = null;
        }

        Test::updateOrCreate(

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
    }

    public function reply(array $input): bool
    {
        return DB::transaction(function () use ($input) {

            /* ================= PATIENT & CONTEXT ================= */
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

            /* ================= BUILD MESSAGE BODY ================= */
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

            /* ================= SEND LETTER ================= */
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

                Document::create([
                    'patient_id' => $pid,
                    'hospital_id' => $hospital->id,
                    'url' => $filePath,
                    'type' => 'Letters',
                    'description' => 'Test Results Letter for '.$patient->name,
                    'from' => $providerName,
                    'viewed' => $providerName,
                    'date' => now(),
                ]);
            }

            /* ================= SEND PORTAL MESSAGE ================= */
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

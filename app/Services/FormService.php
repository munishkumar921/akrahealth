<?php

namespace App\Services;

use App\Models\DoctorForms;
use App\Models\Form;
use App\Models\Patient;
use SoapBox\Formatter\Formatter;
use Symfony\Component\Yaml\Yaml;

class FormService
{
    public function store($input)
    {
        $notificationService = app(InAppNotificationService::class);
        $doctor = auth()->user()->doctor;

        /* Get or create the DoctorForms record */
        $doctorForms = DoctorForms::firstOrCreate(
            ['doctor_id' => $doctor->id],
            ['form' => '']
        );

        /* Parse YAML if exists, otherwise create empty structure */
        $array = [];
        if (! empty($doctorForms->form)) {
            $formatter = Formatter::make($doctorForms->form, Formatter::YAML);
            $array = $formatter->toArray();
        }

        /* Ensure $array is an array */
        if (! is_array($array)) {
            $array = [];
        }

        foreach ($input['rows'] as $row) {

            $formKey = $row['title'] ?? 'form_' . uniqid();
            $array[$formKey] = [
                'forms_title' => $row['title'] ?? '',
                'forms_destination' => $row['forms_destination'] ?? null,
                'gender' => $row['gender'] ?? '',
                'age' => $row['age'] ?? '',
            ];

            $formatter = Formatter::make($array, Formatter::ARR);
            $data['form'] = $formatter->toYaml();

            $doctorForms->update($data);
        }

        if ($doctor?->selected_patient_id) {
            $notificationService->notifyPatient(
                $doctor->selected_patient_id,
                $notificationService->buildPayload(
                    'Forms assigned',
                    'New or updated forms have been assigned to your chart.',
                    'form_assigned',
                    [
                        'recipient_role' => 'Patient',
                        'patient_id' => $doctor->selected_patient_id,
                        'doctor_id' => $doctor->id,
                        'action_url' => route('patient.forms'),
                        'related_model_type' => DoctorForms::class,
                        'related_model_id' => $doctorForms->id,
                    ]
                )
            );
        }
    }

    public function formSubmit($input)
    {
        $notificationService = app(InAppNotificationService::class);
        // Step 1: Validate required inputs
        if (! isset($input['title'], $input['patient_id'])) {
            return back()->with('errror', 'form not submitted.');
        }

        // Step 2: Build form structure properly
        $formContent = [];

        // First, add the title and destination
        $formContent[$input['title']] = [
            'title' => $input['title'] ?? null,
            'destination' => $input['destination'] ?? null,
        ];

        // Then add questions under a separate key to avoid conflicts
        $questions = collect($input['questions'] ?? [])->mapWithKeys(function ($item, $index) {
            $questionData = [
                'input' => $item['type'] ?? $item['input'] ?? null,
                'name' => $item['name'] ?? null,
                'text' => $item['label'] ?? $item['text'] ?? null,
                'value' => $item['value'] ?? null,
            ];

            // Handle options properly
            if (isset($item['options'])) {
                if (is_array($item['options'])) {
                    $questionData['options'] = implode(',', $item['options']);
                } else {
                    $questionData['options'] = $item['options'];
                }
            }

            return [$index => $questionData];
        })->toArray();

        // Add questions to form content
        if (! empty($questions)) {
            $formContent[$input['title']]['questions'] = $questions;
        }

        // Step 3: Convert array → YAML
        $yaml = Formatter::make($formContent, Formatter::ARR)->toYaml();

        // Step 4: Fetch existing doctor form template
        $doctorId = auth()->user()->doctor->id ?? $input['doctor_id'] ?? null;
        $selectedForm = null;

        if ($doctorId) {
            $doctorForm = DoctorForms::where('doctor_id', $doctorId)->first();

            if ($doctorForm && $doctorForm->form) {
                try {
                    $formatterExisting = Formatter::make($doctorForm->form, Formatter::YAML);
                    $existingFormArray = $formatterExisting->toArray();

                    // Find form by title
                    $formTitle = $input['title'] ?? null;
                    if ($formTitle && isset($existingFormArray[$formTitle])) {
                        $selectedForm = $existingFormArray[$formTitle];
                    }
                } catch (\Exception $e) {
                    // Log error but continue
                    \Log::warning('Failed to parse existing form YAML: ' . $e->getMessage());
                }
            }
        }

        // Step 5: Extract content text (simple readable summary)
        $content_text = '';
        $score = 0;

        if (! empty($formContent)) {
            $formTitle = $input['title'] ?? 'N/A';
            $userName = auth()->user()->name ?? 'Unknown';
            $currentDate = now()->format('Y-m-d h:i A');

            $content_text .= "Form Title: {$formTitle}\n";
            $content_text .= "Completed by: {$userName} on {$currentDate}\n";
            $content_text .= "------------------------------------------\n";

            // Process questions if they exist
            if (isset($formContent[$formTitle]['questions'])) {
                foreach ($formContent[$formTitle]['questions'] as $index => $question) {
                    if (! isset($question['text'])) {
                        continue;
                    }

                    $answer = $question['value'] ?? '';

                    // Handle array values properly
                    if (is_array($answer)) {
                        $answerStr = implode(', ', array_filter($answer, 'strlen'));
                    } elseif (is_object($answer)) {
                        $answerStr = json_encode($answer);
                    } else {
                        $answerStr = (string) $answer;
                    }

                    if (empty($answerStr)) {
                        $answerStr = 'N/A';
                    }

                    $content_text .= $question['text'] . ': ' . $answerStr . "\n";

                    // Scoring logic for checkbox/radio
                    if (($question['input'] == 'checkbox' || $question['input'] == 'radio') && ! empty($answer)) {
                        if (isset($question['options']) && ! empty($question['options'])) {
                            $options_arr = explode(',', $question['options']);

                            foreach ($options_arr as $i => $option) {
                                $option = trim($option);
                                if (is_array($answer)) {
                                    if (in_array($option, $answer)) {
                                        $score += $i;
                                    }
                                } else {
                                    if ((string) $answer === $option) {
                                        $score += $i;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($score > 0) {
                $content_text .= "------------------------------------------\n";
                $content_text .= 'Score: ' . $score . "\n";
            }
        }

        // Step 6: Save or update form entry
        $formData = [
            'doctor_id' => $doctorId,
            'date' => now(),
            'content' => $yaml,
            'destination' => $input['destination'] ?? null,
            'content_text' => $content_text,
            'data' => $selectedForm ? json_encode($selectedForm) : null,
        ];

        // Remove null values from the form data
        $formData = array_filter($formData, function ($value) {
            return ! is_null($value);
        });

        $form = Form::updateOrCreate(
            [
                'patient_id' => $input['patient_id'],
                'title' => $input['title'],
            ],
            $formData
        );

        $patient = Patient::with('doctorPatients.doctor.user')->find($input['patient_id']);
        $doctorUsers = $patient?->doctorPatients
            ->map(fn ($doctorPatient) => $doctorPatient->doctor?->user)
            ->filter();

        $notificationService->notifyUsers(
            $doctorUsers ?? [],
            $notificationService->buildPayload(
                'Form updated by patient',
                ($patient?->name ?? 'A patient').' submitted or updated a form.',
                'form_updated',
                [
                    'recipient_role' => 'Doctor',
                    'form_id' => $form->id,
                    'patient_id' => $patient?->id,
                    'action_url' => route('doctor.forms.index'),
                    'related_model_type' => Form::class,
                    'related_model_id' => $form->id,
                ]
            )
        );

        return $form;
    }
}

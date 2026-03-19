<?php

namespace App\Services;

use App\Models\DoctorForms;
use App\Models\Form;
use SoapBox\Formatter\Formatter;
use Symfony\Component\Yaml\Yaml;

class FormService
{
    public function store($input)
    {
        $doctor = auth()->user()->doctor;

        // ✅ Get or create the DoctorForms record
        $doctorForms = DoctorForms::firstOrCreate(
            ['doctor_id' => $doctor->id],
            ['form' => '']
        );

        // ✅ Parse YAML if exists, otherwise create empty structure
        $array = [];
        if (! empty($doctorForms->form)) {
            $formatter = Formatter::make($doctorForms->form, Formatter::YAML);
            $array = $formatter->toArray();
        }

        // ✅ Ensure $array is an array
        if (! is_array($array)) {
            $array = [];
        }

        // ✅ Create new form key (example: based on title)
        $formKey = $input['title'] ?? 'form_'.uniqid();

        // ✅ Add new form record under that key
        $array[$formKey] = [
            'forms_title' => $input['title'] ?? '',
            'forms_destination' => $input['forms_destination'] ?? null,
            'gender' => $input['gender'] ?? '',
            'age' => $input['age'] ?? '',
        ];

        // ✅ Convert back to YAML
        $formatter = Formatter::make($array, Formatter::ARR);
        $data['form'] = $formatter->toYaml();

        // ✅ Update database
        $doctorForms->update($data);
    }

    public function formSubmit($input)
    {
        // ✅ Step 1: Validate required inputs
        if (! isset($input['title'], $input['patient_id'])) {
            return back()->with('errror', 'form not submitted.');
        }

        // ✅ Step 2: Build form structure properly
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

        // ✅ Step 3: Convert array → YAML
        $yaml = Formatter::make($formContent, Formatter::ARR)->toYaml();

        // ✅ Step 4: Fetch existing doctor form template
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
                    \Log::warning('Failed to parse existing form YAML: '.$e->getMessage());
                }
            }
        }

        // ✅ Step 5: Extract content text (simple readable summary)
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

                    // ✅ Handle array values properly
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

                    $content_text .= $question['text'].': '.$answerStr."\n";

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
                $content_text .= 'Score: '.$score."\n";
            }
        }

        // ✅ Step 6: Save or update form entry
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

        return $form;
    }
}

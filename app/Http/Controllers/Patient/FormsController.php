<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\DoctorForms;
use App\Models\DoctorPatient;
use App\Models\Form;
use App\Services\FormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use SoapBox\Formatter\Formatter;
use Symfony\Component\Yaml\Yaml;

class FormsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $patientId = auth()->user()->patient->id ?? null;

        if (! $patientId) {
            abort(404, 'Patient not found');
        }

        $list_array = [];

        // Get doctors linked to patient
        $doctorPatients = DoctorPatient::with(['doctor.user'])
            ->where('patient_id', $patientId)
            ->get();

        foreach ($doctorPatients as $doctorPatient) {

            $doctor = $doctorPatient->doctor;
            if (! $doctor) {
                continue;
            }

            // Get forms for this doctor
            $doctorForms = DoctorForms::where('doctor_id', $doctor->id)->get();

            foreach ($doctorForms as $doctorForm) {

                if (! $doctorForm->form) {
                    continue;
                }

                // Parse YAML form
                $formatter = Formatter::make($doctorForm->form, Formatter::YAML);
                $formArray = $formatter->toArray();

                foreach ($formArray as $formKey => $formValue) {

                    $list_array[] = [
                        'doctor_id' => $doctor->id,
                        'label' => $doctor->user->name.' - '.$formKey,
                        'view' => route('patient.form.show', [$doctorForm->id, $formKey]),
                    ];
                }
            }
        }

        // Completed forms by patient
        $completedForms = Form::with('doctor')
            ->where('patient_id', $patientId)
            ->get();

        return Inertia::render('Patients/Forms/Index', [
            'completdForms' => $completedForms,
            'forms' => $list_array,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, FormService $obj)
    {
        $obj->formSubmit($request->all());

        return Redirect::route('patient.forms')->with('success', 'Form has been submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, string $type)
    {
        $DoctorForms = DoctorForms::findOrFail($id);
        // Parse YAML using Symfony YAML
        $array = Yaml::parse($DoctorForms->form);
        $items = [];

        // Ensure the requested type exists in YAML
        if (! isset($array[$type]) || ! is_array($array[$type])) {
            throw new \Exception("Invalid form type: {$type}");
        }

        // ✅ Capture form metadata once
        $formMeta = [
            'form_title' => $array[$type]['forms_title'] ?? null,
            'form_destination' => $array[$type]['forms_destination'] ?? null,
        ];
        // ✅ Loop through all fields
        foreach ($array[$type] as $row_k => $row_v) {
            // Skip metadata and control keys
            if (in_array($row_k, ['forms_title', 'forms_destination', 'scoring', 'gender', 'age'])) {
                continue;
            }

            // Skip invalid rows
            if (! is_array($row_v) || ! isset($row_v['input'])) {
                continue;
            }

            $form_item = [
                'name' => $row_v['name'] ?? $row_k,
                'label' => $row_v['text'] ?? ucfirst(str_replace('_', ' ', $row_k)),
                'type' => $row_v['input'],
                'required' => $row_v['required'] ?? true,
                'default_value' => $row_v['default'] ?? null,
            ];

            // Handle options for select, radio, or checkbox
            if (in_array($row_v['input'], ['checkbox', 'radio', 'select'])) {
                $options = [];

                if (! empty($row_v['options'])) {
                    $options = array_map('trim', explode(',', $row_v['options']));
                }

                if ($row_v['input'] === 'select') {
                    $form_item['select_items'] = $options;
                } else {
                    $form_item['section_items'] = $options;
                }
                $form_item['options'] = $options;
            }

            $items[$row_k] = $form_item;
        }

        // ✅ Combine metadata + questions
        $result = [
            'form_title' => $formMeta['form_title'],
            'form_destination' => $formMeta['form_destination'],
            'questions' => $items,
        ];

        $section = $result;

        $formId = Form::where('doctor_id', $DoctorForms->doctor_id)
            ->where('patient_id', auth()->user()->patient->id)
            ->where('title', $type)
            ->first()
            ->id ?? null;
        $DoctorForms['patient_id'] = auth()->user()->patient->id;

        return Inertia::render('Patients/Forms/Form', [
            'DoctorForms' => $DoctorForms,
            'formId' => $formId,
            'section' => $section,
            'type' => $type,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, string $type)
    {
        $forms = Form::with(['doctor.user', 'patient.user'])->findOrFail($id);

        // Parse the saved content (which contains the answers)
        $content = Yaml::parse($forms->content);

        // Get the specific form data based on type (title)
        $formData = $content[$type] ?? (is_array($content) ? reset($content) : []);

        $items = [];

        if (isset($formData['questions']) && is_array($formData['questions'])) {
            foreach ($formData['questions'] as $row) {
                $form_item = [
                    'name' => $row['name'] ?? null,
                    'label' => $row['text'] ?? null,
                    'type' => $row['input'] ?? null,
                    'required' => $row['required'] ?? false,
                    'value' => $row['value'] ?? null,
                ];

                // Handle options
                if (isset($row['options'])) {
                    $options = [];
                    if (is_string($row['options'])) {
                        $options = array_map('trim', explode(',', $row['options']));
                    } elseif (is_array($row['options'])) {
                        $options = $row['options'];
                    }

                    $form_item['options'] = $options;

                    // For compatibility
                    if (($row['input'] ?? '') === 'select') {
                        $form_item['select_items'] = $options;
                    } else {
                        $form_item['section_items'] = $options;
                    }
                }

                $items[] = $form_item;
            }
        }

        $result = [
            'id' => $forms->id,
            'title' => $formData['title'] ?? $type,
            'destination' => $formData['destination'] ?? null,
            'doctor_id' => $forms->doctor_id,
            'patient_id' => $forms->patient_id,
            'questions' => $items,
        ];

        return Inertia::render('Patients/Forms/EditForm', [
            'form' => $result,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Show the form for completing the specified resource.
     */
    public function formcomplete(string $id)
    {
        try {
            $form = Form::with(['doctor.user', 'patient.user'])->findOrFail($id);
            $form['content_text'] = nl2br($form->content_text);

            $doctorForms = DoctorForms::where('doctor_id', $form->doctor_id)->first();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Form not found.');
        }

        return Inertia::render('Patients/Forms/CompleteForm', [
            'form' => $form,
            'doctorForms' => $doctorForms,
        ]);
    }
}

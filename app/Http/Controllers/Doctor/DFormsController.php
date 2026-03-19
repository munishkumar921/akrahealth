<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorForms;
use App\Models\Form;
use App\Services\FormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Symfony\Component\Yaml\Yaml;

class DFormsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctorId = Auth::user()->doctor->id ?? null;
        $patientId = Auth::user()->doctor->selected_patient_id ?? null;

        // ✅ Get user-specific form configuration
        $DoctorForms = DoctorForms::where('doctor_id', $doctorId)->first();

        // Check if the record or form is empty
        if (! $DoctorForms || empty($DoctorForms->form)) {
            // Load default YAML form
            $formYaml = File::get(resource_path('forms.yaml'));

            // Create or update record
            DoctorForms::updateOrCreate(
                ['doctor_id' => $doctorId],
                ['form' => $formYaml]
            );

            $yaml = $formYaml;
        } else {
            $yaml = $DoctorForms->form;
        }

        // Parse YAML to array using Symfony YAML
        $formatterForm = Yaml::parse($yaml);

        // ✅ Render Inertia page
        // Complete Forms
        $completdForms = Form::with('doctor')
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->get();

        return Inertia::render('Doctors/Patient/Forms/Index', [
            'forms' => $DoctorForms,
            'formatterForm' => $formatterForm,
            'completdForms' => $completdForms,
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
        $obj->store($request->all());

        return redirect()->route('doctor.forms.index')->with('success', 'Form has been saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, string $type)
    {
        $type = str_replace("\n", '', $type);
        $doctorId = Auth::user()->doctor->id ?? null;
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
                    $options_arr = array_map('trim', explode(',', $row_v['options']));
                    foreach ($options_arr as $opt) {
                        $options[$opt] = $opt;
                    }
                }

                if ($row_v['input'] === 'select') {
                    $form_item['select_items'] = $options;
                } else {
                    $form_item['section_items'] = $options;
                }
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
            ->where('patient_id', Auth::user()->doctor->selected_patient_id)
            ->where('title', $type)
            ->first()
            ->id ?? null;

        return Inertia::render('Doctors/Patient/Forms/Form', [
            'DoctorForms' => $DoctorForms,
            'formId' => $formId,
            'patientId' => Auth::user()->doctor->selected_patient_id,
            'section' => $section,
            'type' => $type,
        ]);
    }

    /**
     * form edit
     */
    public function formEdit(string $id, string $type)
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

        return Inertia::render('Doctors/Patient/Forms/EditForm', [
            'form' => $result,
        ]);
    }

    /**
     * Show the form for completing the specified resource.
     */
    public function complete(string $id)
    {
        try {
            $form = Form::with(['doctor.user', 'patient.user'])->findOrFail($id);
            $form['content_text'] = nl2br($form->content_text);

            $doctorForms = DoctorForms::where('doctor_id', $form->doctor_id)->first();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Form not found.');
        }

        return Inertia::render('Doctors/Patient/Forms/CompleteForm', [
            'form' => $form,
            'doctorForms' => $doctorForms,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function formSubmit(Request $request, FormService $obj)
    {
        $obj->formSubmit($request->all());

        return redirect()->route('doctor.forms.index')->with('success', 'Form has been submitted successfully.');
    }

    public function edit(string $id)
    {
        //
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
}

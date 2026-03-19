<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResultsRequest;
use App\Models\Alert;
use App\Models\Doctor;
use App\Models\Encounter;
use App\Models\Hospital;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Test;
use App\Models\Vital;
use App\Services\EncounterService;
use App\Services\ResultsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DResultsController extends Controller
{
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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $doctor = auth()->user()->doctor;
        $patientId = $doctor->selected_patient_id ?? null;
        // 🧪 Test Results
        $results = Test::where('patient_id', $patientId)->where('doctor_id', $doctor->id)
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = '%'.$request->get('search').'%';
                $q->where('name', 'like', $keyword)
                    ->orWhere('code', 'like', 'like', $keyword)
                    ->orWhere('result', 'like', $keyword);
            })
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
        $encounter_vitals = Vital::where('patient_id', $patientId)
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = '%'.$request->get('search').'%';
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('weight', 'like', $keyword)
                        ->orWhere('height', 'like', $keyword)
                        ->orWhere('bmi', 'like', $keyword)
                        ->orWhere('temperature', 'like', $keyword)
                        ->orWhere('bp_systolic', 'like', $keyword)
                        ->orWhere('bp_diastolic', 'like', $keyword)
                        ->orWhere('pulse', 'like', $keyword)
                        ->orWhere('respirations', 'like', $keyword)
                        ->orWhere('o2_saturation', 'like', $keyword)
                        ->orWhere('vitals_other', 'like', $keyword);
                });
            })
            ->orderByDesc('vital_date')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
        // 👨‍⚕️ Doctors (with relationship)
        $doctors = Doctor::with('user:id,name')
            ->where('hospital_id', $doctor->hospital_id)
            ->get();
        // 🔹 Pending Results Alerts
        $tests_arr = [];

        $alerts = Alert::where('patient_id', $patientId)
            ->whereNull('date_complete')
            ->whereNull('why_not_complete')
            ->whereIn('alert', [
                'Laboratory results pending',
                'Radiology results pending',
                'Cardiopulmonary results pending',
            ])
            ->get();
        foreach ($alerts as $alert) {
            $order = Order::find($alert->order_id);

            if (! $order) {
                continue;
            }

            $test_desc = '';

            if (! empty($order->labs)) {
                $test_desc = $order->labs;
            } elseif (! empty($order->radiology)) {
                $test_desc = $order->radiology;
            } elseif (! empty($order->cp)) {
                $test_desc = $order->cp;
            }

            if ($test_desc !== '') {
                $tests_arr[] = [
                    'id' => $alert->id,
                    'name' => str_replace(' results pending', ': ', $alert->alert).$test_desc,
                ];
            }
        }

        // 🧭 Return to Inertia
        return Inertia::render('Doctors/Patient/Results/Index', [
            'results' => $results,
            'encounter_vitals' => $encounter_vitals,
            'doctors' => $doctors,
            'tests' => $tests_arr,
            'serach' => $request->get('search'),
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
    public function store(ResultsRequest $request, ResultsService $obj)
    {
        $obj->store($request->all());

        return back()->with('success', 'Results  has been saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Inertia::render('Doctors/Patient/Results/Show', [
            'result' => Test::with('patient.user', 'doctor.user')->findOrFail($id),
            'doctors' => Doctor::with('user:id,name')->where('hospital_id', auth()->user()->doctor->hospital_id)->get(),

        ]);

    }

    /**
     * Show chart for the specified resource.
     */
    public function resultsChart(string $id)
    {
        $test = Test::with('patient.user', 'doctor.user')->findOrFail($id);

        $tests = Test::where('name', $test->name)
            ->where('patient_id', $test->patient_id)
            ->where('doctor_id', $test->doctor_id)
            ->orderBy('time', 'asc')
            ->get();

        return Inertia::render('Doctors/Patient/Results/Chart', [
            'result' => $test,
            'tests' => $tests, // ✅ important for plotting
            'doctors' => Doctor::with('user:id,name')
                ->where('hospital_id', auth()->user()->doctor->hospital_id)
                ->get(),
        ]);
    }

    /*
    ResultReply
    */
    public function Resultreply(ResultsRequest $request, ResultsService $obj)
    {
        $obj->reply($request->all());

        return back()->with('success', 'Reply has been send successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ResultsRequest $request, ResultsService $obj, string $id)
    {
        $input = $request->all();
        $input['id'] = $id;
        $obj->store($input);

        return back()->with('success', 'Results has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $test = \App\Models\Test::findOrFail($id);
        $test->delete();

        return back()->with('success', 'Result deleted successfully.');
    }

    // In your controller
    public function encounterVitalChat(Request $request)
    {
        $type = $request->get('type'); // e.g., 'weight', 'height', etc.
        $vitals = Vital::where('patient_id', auth()->user()->doctor->selected_patient_id)
            ->select('vital_date', $type)
            ->orderBy('vital_date', 'asc')
            ->get();

        return Inertia::render('Doctors/Patient/Results/VitalChart', [
            'vitals' => $vitals,
            'type' => $type,
        ]);
    }

    public function getVitalsHistory(Request $request, Patient $patient, $vital)
    {
        $history = Encounter::where('patient_id', $patient->id)
            ->whereNotNull($vital)
            ->orderBy('vital_date', 'asc')
            ->get(['vital_date as date', $vital.' as value']);

        return response()->json($history);
    }

    protected function array_vitals()
    {
        $practice = Hospital::where('id', '=', auth()->user()->doctor->hospital_id)->first();
        $return = [
            'weight' => [
                'name' => 'Weight',
                'unit' => $practice->weight_unit,
            ],
            'height' => [
                'name' => 'Height',
                'unit' => $practice->height_unit,
            ],
            'headcircumference' => [
                'name' => 'HC',
                'unit' => $practice->hc_unit,
            ],
            'BMI' => [
                'min' => '19',
                'max' => '30',
                'name' => 'BMI',
                'unit' => 'kg/m2',
            ],
            'temp' => [
                'min' => [
                    'F' => '93',
                    'C' => '34',
                ],
                'max' => [
                    'F' => '100.4',
                    'C' => '38',
                ],
                'name' => 'Temp',
                'unit' => $practice->temp_unit,
            ],
            'bp_systolic' => [
                'min' => '80',
                'max' => '140',
                'name' => 'SBP',
                'unit' => 'mmHg',
            ],
            'bp_diastolic' => [
                'min' => '50',
                'max' => '90',
                'name' => 'DBP',
                'unit' => 'mmHg',
            ],
            'pulse' => [
                'min' => '50',
                'max' => '140',
                'name' => 'Pulse',
                'unit' => 'bpm',
            ],
            'respirations' => [
                'min' => '10',
                'max' => '35',
                'name' => 'Resp',
                'unit' => 'bpm',
            ],
            'o2_sat' => [
                'min' => '90',
                'max' => '100',
                'name' => 'O2 Sat',
                'unit' => 'percent',
            ],
        ];

        return $return;
    }
}

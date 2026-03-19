<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\EncounterRequest;
use App\Models\Alert;
use App\Models\Assessment;
use App\Models\Billing;
use App\Models\Encounter;
use App\Models\PatientIllnessHistory;
use App\Models\PatientRelate;
use App\Models\PhysicalExamination;
use App\Services\EncounterService;
use App\Traits\UploadFileTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DEncountersController extends Controller
{
    use UploadFileTrait;

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
        $keyword = $request->get('search', ''); // get keyword or empty string
        if (auth()->user()->doctor->selected_patient_id) {
            $query = Encounter::where('patient_id', auth()->user()->doctor->selected_patient_id ?? null)->with('patient.user', 'doctor.user', 'appointment');
        } else {
            $query = Encounter::where('doctor_id', auth()->user()->doctor->id)->with('patient.user', 'doctor.user', 'appointment');
        }
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                // Search in Encounter fields
                $q->where('chief_complaint', 'like', '%'.$keyword.'%');

                // Search in Patient name
                $q->orWhereHas('patient.user', function ($q2) use ($keyword) {
                    $q2->where('name', 'like', '%'.$keyword.'%');
                });

                // Search in Doctor name
                $q->orWhereHas('doctor.user', function ($q3) use ($keyword) {
                    $q3->where('name', 'like', '%'.$keyword.'%');
                });
            });
        }

        $encounters = $query->orderBy('created_at', 'DESC')->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Doctors/Patient/Encounters/Encounters', [
            'encounters' => $encounters,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data = $this->encounter->getFormData();
        $data['encounter'] = new Encounter;

        return Inertia::render('Doctors/Patient/Encounters/AddEncounter', [
            'data' => $data,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->encounter->getFormData($id);
        $data['encounter'] = Encounter::with([
            'patient.user',
            'doctor.user',
            'appointment',
            'patientIllnessHistory',
            'reviewOfSystem',
            'vital',
            'assessment',
            'plan',
            'physicalExamination',
        ])
            ->where('id', $id)->first();

        return Inertia::render('Doctors/Patient/Encounters/EditEncounter', [
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EncounterRequest $request)
    {
        $encounter = $this->encounter->upsert($request->all());

        return Redirect::route('doctor.encounters.edit', $encounter->id)
            ->with('success', 'The encounter has been saved successfully.');
    }

    public function update(EncounterRequest $request, string $id)
    {
        $encounter = $this->encounter->upsert($request->all(), $id);

        return Redirect::route('doctor.encounters.edit', $encounter->id)
            ->with('success', 'The encounter has been saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = $this->encounter->getFormData($id);
        $data['encounter'] = Encounter::with([
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
        ])->where('id', $id)->first();

        return Inertia::render('Doctors/Patient/Encounters/ViewEncounter', [
            'data' => $data,
        ]);
    }

    public function encounter_sign(Request $request)
    {
        $encounter = Encounter::find($request->id);

        if (! $encounter) {
            return back()->with('error', 'Encounter not found');
        }

        $hospitalId = auth()->user()->doctor->hospital_id;
        $patientId = auth()->user()->doctor->selected_patient_id ?? null;
        $doctorId = auth()->user()->doctor->id;

        $error_arr = [];

        // Load encounter components
        $hpi = PatientIllnessHistory::where('encounter_id', $encounter->id)->first(); // Subjective
        $pe = PhysicalExamination::where('encounter_id', $encounter->id)->first(); // Objective
        $assessment = Assessment::where('encounter_id', $encounter->id)->first();
        $billing = Billing::where('encounter_id', $encounter->id)->first();

        // Required items check
        if (! $hpi) {
            $error_arr[] = 'Subjective';
        }

        if ($encounter->encounter_template !== 'medical') {
            if (! $pe) {
                $error_arr[] = 'Objective';
            }
        }

        if (! $assessment) {
            $error_arr[] = 'Assessment';
        }
        if (! $billing) {
            $error_arr[] = 'Billing';
        }

        // If missing sections
        if (count($error_arr) > 0) {
            $error = 'Error - Missing items: '.implode(', ', $error_arr);

            return back()->with('error', $error);
        }

        // Role restriction for certain templates
        if (
            in_array($encounter->encounter_template, ['standardmedical', 'standardmedical1'])
        ) {
            return back()->with('error', 'Error - You are not allowed to sign this type of encounter!');
        }

        // SIGN THE ENCOUNTER
        $encounter->update([
            'encounter_signed' => 'Yes',
            'date_signed' => now(),
        ]);

        // Psych template additional alert creation
        if ($encounter->encounter_template == 'standardpsych') {

            $patient = PatientRelate::where('patient_id', $patientId)
                ->where('hospital_id', $hospitalId)
                ->first();

            $alert_send_message = $patient ? 'y' : 'n';

            // One year after encounter date
            $psych_date = strtotime($encounter->encounter_date_of_service.' +1 year');

            $description = 'Schedule Annual Psychiatric Evaluation Appointment for '.date('F jS, Y', $psych_date);

            Alert::create([
                'alert' => 'Annual Psychiatric Evaluation Reminder',
                'description' => $description,
                'date_active' => now(),
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
                'hospital_id' => $hospitalId,
                'message_sent' => $alert_send_message,
            ]);
        }

        return back()->with('success', 'Encounter signed successfully.');
    }

    /**
     * updatedAnnotation
     *
     * @param  mixed  $request
     * @return void
     */
    public function updatedAnnotation(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,pdf|max:10240',
        ]);

        $path = $this->uploadPublic($request->file('image'));

        return response()->json(['path' => 'storage/'.$path]);
    }

    /**
     * addEncounterAssessment
     *
     * @param  mixed  $request
     * @param  mixed  $type
     * @param  mixed  $id
     * @return void
     */
    public function addEncounterAssessment(Request $request, $type, $id, $encounter_id = null)
    {
        $icd = $id;
        $query = DB::table('assessments')->where('encounter_id', '=', $encounter_id)->first();
        $copy_arr = [];
        $message_some = '';
        if ($type == 'issue') {

            if ($id == 'pl' || $id == 'mh' || $id == 'sh') {

                $arr = [
                    'pl' => 'Problem List',
                    'mh' => 'Medical History',
                    'sh' => 'Surgical History',
                ];

                $issues = DB::table('issues')->where('type', '=', $arr[$id])
                    ->where('issue_date_inactive', '=', '0000-00-00 00:00:00')->get();

                if ($issues->count()) {
                    foreach ($issues as $issue) {
                        $issue_arr = explode('[', $issue->issue);

                        if (count($issue_arr) == 0) {

                            $message_some = '  Some conditions were not copied due to incorrect format.';
                        } else {
                            $copy_arr[] = [
                                'desc' => rtrim($issue_arr[0]),
                                'icd' => str_replace(']', '', $issue_arr[0]),
                            ];
                        }
                    }
                } else {
                    return 'Error - '.$arr[$id].' is empty.';
                }
            } else {
                $issue = DB::table('issues')->where('issue_id', '=', $id)->first();

                $issue_arr = explode('[', $issue->issue);

                if (count($issue_arr) == 0) {

                    return 'Error - Condition was not copied due to incorrect format.';
                } else {
                    $copy_arr[] = [
                        'desc' => rtrim($issue_arr[0]),
                        'icd' => str_replace(']', '', $issue_arr[0]),
                    ];
                }
            }
        } else {

            $file = File::get(resource_path().'/icd10cm_order_2017.txt');
            $arr = preg_split('/\\r\\n|\\r|\\n/', $file);

            foreach ($arr as $row) {
                $icd10 = rtrim(substr($row, 6, 7));
                if (strlen($icd10) !== 3) {
                    $icd10 = substr_replace($icd10, '.', 3, 0);
                }
                $preicd[$icd10] = [
                    'icd10' => $icd10,
                    'desc' => substr($row, 77),
                    'type' => substr($row, 14, 1),
                ];
            }

            $copy_arr[] = [
                'desc' => $id,
                'icd' => $id,
            ];
        }

        $id = '1';
        if ($query) {
            for ($i = 1; $i <= 12; $i++) {
                $item = 'assessment_'.$i;
                if ($query->{$item} == '') {
                    break;
                }
            }
            $id = $i;
        }
        foreach ($copy_arr as $copy_item) {

            $query1 = Assessment::where('encounter_id', '=', $encounter_id)->first();
            if ($id <= 12) {
                $data1 = [
                    'assessment_'.$id => $copy_item['desc'],
                    'assessment_icd'.$id => $copy_item['icd'],
                ];
            } else {
                if ($query1) {
                    $other = $query1->assessment_other."\n".$copy_item['desc'];
                } else {
                    $other = $copy_item['desc'];
                }
                $data1['assessment_other'] = $other;
            }
            if ($query1) {
                Assessment::where('encounter_id', '=', $encounter_id)->update($data1);
                $this->audit('Update', 'Assessment');
            } else {

                $encounter = Encounter::where('id', $encounter_id)->first();
                $data1['encounter_id'] = $encounter_id;
                $data1['icd'] = $icd;
                $data1['patient_id'] = $encounter->patient_id;
                $data1['doctor_id'] = auth()->user()->doctor->id;
                $data1['encounter_provider'] = auth()->user()->name;
                Assessment::updateOrCreate(
                    [
                        'encounter_id' => $encounter_id,
                    ],
                    $data1
                );
                $this->audit('Add', 'Assessment');
            }
            $id++;
        }

        return (new EncounterService)->getAssessmentCodes($encounter_id);
    }

    /**
     * deleteEncounterAssessment
     *
     * @param  mixed  $request
     * @param  mixed  $id
     * @param  mixed  $encounter_id
     * @return void
     */
    public function deleteEncounterAssessment(Request $request, $id, $encounter_id = null)
    {
        $data = [
            'assessment_'.$id => '',
            'assessment_icd'.$id => '',
        ];

        Assessment::where('encounter_id', '=', $encounter_id)->update($data);
        $query = Assessment::where('encounter_id', '=', $encounter_id)->first();
        $start = $id + 1;
        $cur_id = $id;
        for ($i = $start; $i <= 12; $i++) {
            $item = 'assessment_'.$i;
            $item1 = 'assessment_icd'.$i;
            if ($query->{$item} !== '') {
                $data1 = [
                    'assessment_'.$cur_id => $query->{$item},
                    'assessment_icd'.$cur_id => $query->{$item1},
                ];
                Assessment::where('encounter_id', '=', $encounter_id)->update($data1);
                $this->audit('Update', 'Assessment');
            } else {
                $data2 = [
                    'assessment_'.$cur_id => '',
                    'assessment_icd'.$cur_id => '',
                ];
                Assessment::where('encounter_id', '=', $encounter_id)->update($data2);
                $this->audit('Update', 'Assessment');
            }
            $cur_id++;
        }

        return (new EncounterService)->getAssessmentCodes($encounter_id);
    }

    /**
     * updateEncounterAssessment
     *
     * @param  mixed  $request
     * @param  mixed  $id
     * @return void
     */
    public function updateEncounterAssessment(Request $request, $id)
    {
        $encounter_id = $request->post('encounter_id');
        $data = [
            'assessment_'.$id => $request->input('assessment'),
            'assessment_icd'.$id => $request->input('icd'),
        ];

        Assessment::where('encounter_id', $encounter_id)->update($data);
        $this->audit('Update', 'Assessment');

        return (new EncounterService)->getAssessmentCodes($encounter_id);
    }
}

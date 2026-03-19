<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use SoapBox\Formatter\Formatter;

class TemplateController extends Controller
{
    /**
     * Build the replacement map for template placeholder substitution
     */
    protected function buildTemplateReplacementMap(?Patient $patient): array
    {
        $replace_arr = [];

        if (! $patient) {
            return $replace_arr;
        }

        // Patient name placeholders
        $replace_arr['{{patient_name}}'] = $patient->name ?? '';
        $replace_arr['{{first_name}}'] = $patient->first_name ?? '';
        $replace_arr['{{last_name}}'] = $patient->last_name ?? '';

        // Patient demographics
        $replace_arr['{{dob}}'] = $patient->dob ?? '';
        $replace_arr['{{age}}'] = $patient->dob ? \Carbon\Carbon::parse($patient->dob)->age.' years' : '';
        $replace_arr['{{gender}}'] = $patient->sex ?? '';
        $replace_arr['{{blood_group}}'] = $patient->blood_group ?? '';
        $replace_arr['{{height}}'] = $patient->height_cm ? $patient->height_cm.' cm' : '';
        $replace_arr['{{weight}}'] = $patient->weight_kg ? $patient->weight_kg.' kg' : '';

        // Address placeholders
        if ($patient->address) {
            $replace_arr['{{address_1}}'] = $patient->address->address_1 ?? '';
            $replace_arr['{{address_2}}'] = $patient->address->address_2 ?? '';
            $replace_arr['{{city}}'] = $patient->address->city ?? '';
            $replace_arr['{{state}}'] = $patient->address->state ?? '';
            $replace_arr['{{zip}}'] = $patient->address->zip ?? '';
            $replace_arr['{{country}}'] = $patient->address->country ?? '';
        }

        // Contact placeholders
        $replace_arr['{{email}}'] = $patient->email ?? '';
        $replace_arr['{{mobile}}'] = $patient->mobile ?? '';

        // Emergency contact placeholders
        $replace_arr['{{emergency_contact_name}}'] = $patient->emergency_contact_name ?? '';
        $replace_arr['{{emergency_contact_phone}}'] = $patient->emergency_contact_phone ?? '';
        $replace_arr['{{emergency_contact_relationship}}'] = $patient->emergency_contact_relationship ?? '';

        // Insurance placeholders
        $replace_arr['{{insurance_provider}}'] = $patient->insurance_provider ?? '';
        $replace_arr['{{insurance_policy_number}}'] = $patient->insurance_policy_number ?? '';
        $replace_arr['{{insurance_group_number}}'] = $patient->insurance_group_number ?? '';

        // Guardian placeholders
        $replace_arr['{{guardian_name}}'] = $patient->guardian_name ?? '';
        $replace_arr['{{guardian_phone}}'] = $patient->guardian_phone ?? '';
        $replace_arr['{{guardian_relationship}}'] = $patient->guardian_relationship ?? '';

        return $replace_arr;
    }

    /**
     * getTemplate
     *
     * @param  mixed  $request
     * @return void
     */
    public function getTemplate(Request $request)
    {
        $selected_patient = Patient::with(['user', 'address'])
            ->where('id', auth()->user()->doctor->selected_patient_id ?? null)
            ->first();
        $gender = $selected_patient?->gender ?? 'm';

        $patientAgeInDays = 10000;

        $data['response'] = 'false';
        $user = DB::table('users')->where('id', '=', auth()->user()->id)->first();
        if ($user->template == null || $user->template == '') {
            $data1['template'] = File::get(resource_path().'/template.yaml');
            DB::table('users')->where('id', '=', auth()->user()->id)->update($data1);
            $yaml = $data1['template'];
        } else {
            $yaml = $user->template;
        }
        $formatter = Formatter::make($yaml, Formatter::YAML);

        $array = $formatter->toArray();
        if (! isset($array[$request->input('id')])) {
            $array[$request->input('id')] = [];
            $formatter1 = Formatter::make($array, Formatter::ARR);
            $user_data['template'] = $formatter1->toYaml();
            DB::table('users')->where('id', '=', auth()->user()->id)->update($user_data);
            $user = DB::table('users')->where('id', '=', auth()->user()->id)->first();
            $yaml = $user->template;
            $formatter = Formatter::make($yaml, Formatter::YAML);
            $array = $formatter->toArray();
        }

        // Build replacement map for placeholder substitution
        $replace_arr = $this->buildTemplateReplacementMap($selected_patient);
        if (is_array($array[$request->input('id')])) {
            // $data['response'] = 'li';
            foreach ($array[$request->input('id')] as $key => $value) {
                if ($request->has('template_group')) {
                    if ($request->input('template_group') == $key) {
                        foreach ($value as $row_k => $row_v) {
                            if ($row_k !== 'gender' && $row_k !== 'age') {
                                $normal = null;
                                $input = null;
                                $options = null;
                                $orders = null;
                                $age = null;
                                $gender = null;
                                $proceed = true;
                                if (isset($row_v['normal'])) {
                                    $normal = $row_v['normal'];
                                }
                                if (isset($row_v['input'])) {
                                    $input = $row_v['input'];
                                }
                                if (isset($row_v['options'])) {
                                    $options = $row_v['options'];
                                }
                                if (isset($row_v['gender'])) {
                                    $gender = $row_v['gender'];
                                }
                                if (isset($row_v['age'])) {
                                    $age = $row_v['age'];
                                }
                                if (isset($row_v['orders'])) {
                                    $orders = $row_v['orders'];
                                }
                                if ($gender) {
                                    if ($gender == 'male') {
                                        if ($gender == 'f' || $gender == 'u') {
                                            $proceed = false;
                                        }
                                    } elseif ($gender == 'female') {
                                        if ($gender == 'm' || $gender == 'u') {
                                            $proceed = false;
                                        }
                                    } else {
                                        if ($gender == 'm' || $gender == 'f') {
                                            $proceed = false;
                                        }
                                    }
                                }
                                if ($patientAgeInDays) {
                                    $agealldays = $patientAgeInDays;
                                    if ($agealldays <= 6574.5) {
                                        if ($age == 'adult') {
                                            $proceed = false;
                                        }
                                    } else {
                                        if ($age == 'child') {
                                            $proceed = false;
                                        }
                                    }
                                }
                                if ($proceed == true) {
                                    $data['message'][] = [
                                        'value' => str_replace(array_keys($replace_arr), $replace_arr, $row_v['text']),
                                        'normal' => $normal,
                                        'input' => $input,
                                        'options' => $options,
                                        'orders' => $orders,
                                        'gender' => $gender,
                                        'age' => $age,
                                        'group' => $request->input('template_group'),
                                        'id' => $row_k,
                                        'category' => $request->input('id'),
                                    ];
                                }
                            }
                        }
                    }
                } else {
                    $proceed = true;
                    $age = null;
                    $gender = null;
                    if (isset($value['gender'])) {
                        $gender = $value['gender'];
                    }
                    if (isset($value['age'])) {
                        $age = $value['age'];
                    }
                    if ($gender) {
                        if ($gender == 'male') {
                            if ($gender == 'f' || $gender == 'u') {
                                $proceed = false;
                            }
                        } elseif ($gender == 'female') {
                            if ($gender == 'm' || $gender == 'u') {
                                $proceed = false;
                            }
                        } else {
                            if ($gender == 'm' || $gender == 'f') {
                                $proceed = false;
                            }
                        }
                    }
                    if ($patientAgeInDays) {
                        $agealldays = $patientAgeInDays;
                        if ($agealldays <= 6574.5) {
                            if ($age == 'adult') {
                                $proceed = false;
                            }
                        } else {
                            if ($age == 'child') {
                                $proceed = false;
                            }
                        }
                    }
                    if ($proceed == true) {
                        $data['message'][] = [
                            'category' => $request->input('id'),
                            'value' => $key,
                        ];
                    }
                }
            }
        }
        if (isset($data['message'])) {
            $data['response'] = 'li';
        }

        return $data;
    }

    /**
     * updateTemplate
     *
     * @param  mixed  $request
     * @return void
     */
    public function updateTemplate(Request $request)
    {
        $user = DB::table('users')->where('id', '=', auth()->user()->id)->first();
        $formatter = Formatter::make($user->template, Formatter::YAML);
        $array = $formatter->toArray();
        $arr['response'] = 'yes';
        if ($request->input('template_edit_type') == 'item') {

            $new['text'] = $request->input('template_text');
            if ($request->input('template_gender') !== '') {
                $new['gender'] = $request->input('template_gender');
            }
            if ($request->input('template_age') !== '') {
                $new['age'] = $request->input('template_age');
            }
            if ($request->input('template_input_type') !== '') {
                $new['input'] = $request->input('template_input_type');
            }
            if ($request->input('template_options') !== '') {
                $new['options'] = trim($request->input('template_options'), ',');
            }
            if ($request->input('template_options_orders_facility') !== '') {
                $orders['facility'] = $request->input('template_options_orders_facility');
                $orders['orders_code'] = $request->input('template_options_orders_orders_code');
                $orders['cpt'] = trim($request->input('template_options_orders_cpt'), ',');
                $orders['loinc'] = trim($request->input('template_options_orders_loinc'), ',');
                $orders['results_code'] = trim($request->input('template_options_orders_results_code'), ',');
                $new['orders'] = $orders;
            }
            if ($request->input('id') == 'new') {

                Log::info([
                    'new' => $new,
                ]);
                $array[$request->input('category')][$request->input('group_name')][] = $new;
                $arr['message'] = 'Template added';
            } else {
                $array[$request->input('category')][$request->input('group_name')][$request->input('id')] = $new;
                $arr['message'] = 'Template updated';
            }
        } else {

            if (isset($array[$request->input('category')][$request->input('template_text')])) {

                if ($request->input('group_name') == '') {
                    $arr['response'] = 'no';
                    $arr['message'] = 'Error: Group name already exists';
                } else {
                    if ($request->input('template_gender') !== '') {
                        $array[$request->input('category')][$request->input('template_text')]['gender'] = $request->input('template_gender');
                    }
                    if ($request->input('template_age') !== '') {
                        $array[$request->input('category')][$request->input('template_text')]['age'] = $request->input('template_age');
                    }
                    $arr['message'] = 'Template group updated';
                }
            } else {
                if ($request->input('group_name') == '') {
                    if (! is_array($array[$request->input('category')])) {
                        $array[$request->input('category')] = [];
                    }
                    $array[$request->input('category')][$request->input('template_text')] = [];
                    if ($request->input('template_gender') !== '') {
                        $array[$request->input('category')][$request->input('template_text')]['gender'] = $request->input('template_gender');
                    }
                    if ($request->input('template_age') !== '') {
                        $array[$request->input('category')][$request->input('template_text')]['age'] = $request->input('template_age');
                    }
                    $arr['message'] = 'Template group added';
                } else {
                    $array[$request->input('category')][$request->input('template_text')] = $array[$request->input('category')][$request->input('group_name')];
                    if ($request->input('template_gender') !== '') {
                        $array[$request->input('category')][$request->input('template_text')]['gender'] = $request->input('template_gender');
                    }
                    if ($request->input('template_age') !== '') {
                        $array[$request->input('category')][$request->input('template_text')]['age'] = $request->input('template_age');
                    }
                    unset($array[$request->input('category')][$request->input('group_name')]);
                    $arr['message'] = 'Template group updated';
                }
            }
        }
        if ($arr['response'] == 'yes') {

            $formatter1 = Formatter::make($array, Formatter::ARR);
            $data['template'] = $formatter1->toYaml();
            DB::table('users')->where('id', '=', auth()->user()->id)->update($data);
        }

        return $arr;
    }

    /**
     * templateData
     *
     * @param  mixed  $request
     * @return void
     */
    public function templateData(Request $request)
    {
        $user = DB::table('users')->where('id', '=', auth()->user()->id)->first();
        $formatter = Formatter::make($user->template, Formatter::YAML);
        $array = $formatter->toArray();
        $data = [];
        foreach ($array[$request->input('category')][$request->input('group_name')] as $row) {
            if (isset($row['normal'])) {
                if ($row['normal'] == true) {
                    $data[] = $row['text'];
                }
            }
        }

        return $data;
    }

    /**
     * template_normal_change
     *
     * @param  mixed  $request
     * @return void
     */
    public function template_normal_change(Request $request)
    {
        $user = DB::table('users')->where('id', '=', auth()->user()->id)->first();
        $formatter = Formatter::make($user->template, Formatter::YAML);
        $array = $formatter->toArray();
        if ($request->input('template_normal_item') == 'y') {
            unset($array[$request->input('category')][$request->input('group_name')][$request->input('id')]['normal']);
            $message = 'Template unset as normal';
        } else {
            $array[$request->input('category')][$request->input('group_name')][$request->input('id')]['normal'] = true;
            $message = 'Template set as normal';
        }
        $formatter1 = Formatter::make($array, Formatter::ARR);
        $data['template'] = $formatter1->toYaml();
        DB::table('users')->where('id', '=', auth()->user()->id)->update($data);

        return $message;
    }

    /**
     * deleteTemplate
     *
     * @param  mixed  $request
     * @return void
     */
    public function deleteTemplate(Request $request)
    {
        $user = DB::table('users')->where('id', '=', auth()->user()->id)->first();
        $formatter = Formatter::make($user->template, Formatter::YAML);
        $array = $formatter->toArray();
        if ($request->input('template_edit_type') == 'item') {
            unset($array[$request->input('category')][$request->input('group_name')][$request->input('id')]);
            $message = 'Template deleted';
        } else {
            unset($array[$request->input('category')][$request->input('group_name')]);
            $message = 'Template group deleted';
        }
        $formatter1 = Formatter::make($array, Formatter::ARR);
        $data['template'] = $formatter1->toYaml();
        DB::table('users')->where('id', '=', auth()->user()->id)->update($data);

        return $message;
    }

    /**
     * searchICDSpecific
     *
     * @param  mixed  $request
     * @return void
     */
    public function searchICDSpecific(Request $request)
    {
        $data = [];
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
        $result = [];
        $result = Arr::where($preicd, function ($value, $key) use ($request) {
            if (stripos($value['icd10'], $request->input('icd')) !== false) {
                return true;
            }
        });
        if ($result) {
            foreach ($result as $row) {
                if ($row['type'] == '1') {
                    $records = $row['desc'].' ['.$row['icd10'].']';
                    $data[] = [
                        'id' => $row['icd10'],
                        'label' => $records,
                        'value' => $records,
                        'href' => route('doctor.add.encounter.assessment', ['icd', $row['icd10']]),
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * ICDSearch
     *
     * @param  mixed  $request
     * @param  mixed  $assessment
     * @return void
     */
    public function ICDSearch(Request $request, $assessment = false)
    {
        $q = strtolower(trim($request->input('search_icd', '')));
        if ($q === '') {
            return;
        }

        $hospitalId = auth()->user()?->doctor?->hospital_id;
        $practice = Cache::remember(
            "practice_{$hospitalId}",
            3600,
            fn () => DB::table('hospitals')->where('id', $hospitalId)->first()
        );

        $data = ['response' => 'false', 'message' => []];

        // -------------------------------------------------------------------------
        // ICD-9 SEARCH (small table, no change)
        // -------------------------------------------------------------------------
        if (isset($practice->icd) && $practice->icd == '9') {
            $pos = array_filter(explode(',', $q));
            $query = DB::table('icd9s')->where(function ($qb) use ($q, $pos) {
                if (count($pos) <= 1) {
                    $qb->where('icd9_description', 'like', "%$q%")
                        ->orWhere('icd9', 'like', "%$q%");
                } else {
                    foreach ($pos as $p) {
                        $qb->where('icd9_description', 'like', "%$p%");
                    }
                }
            })->limit(50)->get();

            if ($query->isNotEmpty()) {
                $data['response'] = $assessment ? 'div' : 'li';
                foreach ($query as $r) {
                    $records = "{$r->icd9_description} [{$r->icd9}]";
                    $item = [
                        'label' => $records,
                        'value' => $records,
                    ];
                    if ($assessment) {
                        $item['id'] = $r->icd9;
                        $item['href'] = route('doctor.add.encounter.assessment', ['icd', $records]);
                    }
                    $data['message'][] = $item;
                }
            }

            return $data;
        }

        // -------------------------------------------------------------------------
        // ICD-10 SEARCH
        // -------------------------------------------------------------------------
        $pos = array_filter(explode(' ', $q));

        // ✅ 1. ICD10DATA (external or AjaxSearchController)
        $icd10data = (new AjaxSearchController)->icd10data(implode('+', $pos));
        foreach ($icd10data as $r) {
            $data['message'][] = [
                'id' => $r['code'],
                'label' => $r['desc'],
                'value' => $r['desc'],
                'category' => 'ICD10Data',
                'category_id' => 'icd10data_result',
                'icd10type' => '1',
                'href' => $assessment
                    ? route('doctor.add.encounter.assessment', ['icd', $r['code']])
                    : null,
            ];
        }

        // ✅ 2. COMMON ICD CODES (YAML → cache)
        $commonArr = Cache::rememberForever('common_icd_list', function () {
            $common = File::get(resource_path('common_icd.yaml'));
            $fmt = Formatter::make($common, Formatter::YAML);
            $pre = $fmt->toArray();

            $defaults = ['Family Practice', 'Internal Medicine', 'Obstetrics & Gynaecology', 'Primary Care', 'Pediatrics'];
            $merged = [];

            foreach ($defaults as $d) {
                foreach ($pre[$d] ?? [] as $item) {
                    if (! collect($merged)->pluck('code')->contains($item['code'])) {
                        $merged[] = $item;
                    }
                }
                unset($pre[$d]);
            }

            return ['merged' => $merged, 'specialties' => $pre];
        });

        $common_arr = $commonArr['merged'];
        $common_pre = $commonArr['specialties'];

        if (Session::get('group_id') == '2') {
            $user = Cache::remember(
                'provider_'.Session::get('user_id'),
                3600,
                fn () => DB::table('providers')->where('id', Session::get('user_id'))->first()
            );
            $specialty = $user->specialty ?? '';
            $closest = $this->closest_match($specialty, array_keys($common_pre));
            foreach ($common_pre[array_keys($common_pre)[$closest]] ?? [] as $extra) {
                if (! collect($common_arr)->pluck('code')->contains($extra['code'])) {
                    $common_arr[] = $extra;
                }
            }
        }

        // 🔍 search common ICDs
        $common_result = collect($common_arr)->filter(function ($v) use ($q, $pos) {
            $desc = strtolower($v['desc']);
            $code = strtolower($v['code']);
            if (count($pos) === 1) {
                return str_contains($desc, $q) || str_contains($code, $q);
            }
            foreach ($pos as $term) {
                if (str_contains($desc, strtolower(trim($term)))) {
                    return true;
                }
            }

            return false;
        })->take(50);

        foreach ($common_result as $r) {
            $label = "{$r['desc']} [{$r['code']}]";
            $item = [
                'id' => $r['code'],
                'label' => $label,
                'value' => $label,
                'category' => 'Common Library',
                'category_id' => 'common_icd_result',
                'icd10type' => '1',
            ];
            if ($assessment) {
                $item['href'] = route('doctor.add.encounter.assessment', ['icd', $r['code']]);
            }
            $data['message'][] = $item;
        }

        // ✅ 3. UNIVERSAL ICD-10 (cached parsed .txt)
        $preicd = Cache::rememberForever('icd10cm_list', function () {
            $file = resource_path('icd10cm_order_2017.txt');
            $h = fopen($file, 'r');
            $res = [];
            while (($line = fgets($h)) !== false) {
                $icd = rtrim(substr($line, 6, 7));
                if (strlen($icd) !== 3) {
                    $icd = substr_replace($icd, '.', 3, 0);
                }
                $res[$icd] = [
                    'icd10' => strtolower($icd),
                    'desc' => strtolower(trim(substr($line, 77))),
                    'type' => substr($line, 14, 1),
                ];
            }
            fclose($h);

            return $res;
        });

        $result = collect($preicd)->filter(function ($i) use ($q, $pos) {
            if (count($pos) === 1) {
                return str_contains($i['desc'], $q) || str_contains($i['icd10'], $q);
            }
            foreach ($pos as $term) {
                if (str_contains($i['desc'], strtolower(trim($term)))) {
                    return true;
                }
            }

            return false;
        })->take(50);

        foreach ($result as $r) {
            $label = ucfirst($r['desc'])." [{$r['icd10']}]";
            $item = [
                'id' => $r['icd10'],
                'label' => $label,
                'value' => $label,
                'icd10type' => $r['type'],
                'category' => 'Universal Library',
                'category_id' => 'universal_icd_result',
            ];
            if ($assessment) {
                $item['href'] = $r['type'] == '0'
                    ? '#'
                    : route('doctor.add.encounter.assessment', ['icd', $r['icd10']]);
            }
            $data['message'][] = $item;
        }

        $data['response'] = $data['message'] ? ($assessment ? 'div' : 'li') : 'false';

        return $data;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CPTRelate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use KubAT\PhpSimple\HtmlDomParser as Htmldom;
use Maatwebsite\Excel\Facades\Excel;
use SoapBox\Formatter\Formatter;

class AjaxSearchController extends Controller
{
    /**
     * search
     *
     * @param  mixed  $request
     * @return void
     */
    public function search(Request $request)
    {

        $search = $request->get('search');

        $type = $request->get('type');
        $data = [];

        if ($search && $type === 'order') {
            $path = resource_path('LOINC.csv');

            $results = Excel::toArray([], $path)[0];
            $result = [];

            $pos = explode(' ', $search);

            if (count($pos) == 1) {

                $result = Arr::where($results, function ($value) use ($search) {

                    return stripos($value[2], $search) !== false;
                });
            } else {
                // Multiple keywords
                $result = Arr::where($results, function ($value) use ($pos) {
                    return $this->striposa($value[2], $pos) !== false;
                });
            }

            if (count($result) > 0) {
                foreach ($result as $row) {

                    $label = $row[2].' ['.$row[1].']';
                    $data[] = [
                        'id' => $row['1'],
                        'label' => $label,
                        'value' => $label,
                    ];
                }
            }

            return $data;
        }

        if ($search && $type === 'search_rx') {

            $url = 'http://rxnav.nlm.nih.gov/REST/Prescribe/drugs.json?name='.$search;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            $json = curl_exec($ch);
            curl_close($ch);
            $rxnorm = json_decode($json, true);
            $result = [];
            $i = 0;
            if (isset($rxnorm['drugGroup']['conceptGroup'])) {
                foreach ($rxnorm['drugGroup']['conceptGroup'] as $rxnorm_row1) {
                    if ($rxnorm_row1['tty'] == 'SBD' || $rxnorm_row1['tty'] == 'SCD') {
                        if (isset($rxnorm_row1['conceptProperties'])) {
                            foreach ($rxnorm_row1['conceptProperties'] as $item) {
                                $result[$i]['rxcui'] = $item['rxcui'];
                                $result[$i]['name'] = $item['name'];
                                if ($rxnorm_row1['tty'] == 'SBD') {
                                    $result[$i]['category'] = 'Brand';
                                } else {
                                    $result[$i]['category'] = 'Generic';
                                }
                                $i++;
                            }
                        }
                    }
                }
                uasort($result, function ($a, $b) {
                    return ($a['name'] < $b['name']) ? -1 : (($a['name'] > $b['name']) ? 1 : 0);
                });
            }
            if (isset($result[0])) {
                foreach ($result as $row) {
                    $arr = explode(' / ', $row['name']);
                    $units = ['MG', 'MG/ML', 'MCG'];
                    $dosage_arr = [];
                    $unit_arr = [];
                    foreach ($arr as $row1) {
                        $arr = explode(' ', $row1);
                        foreach ($units as $unit) {
                            $key = array_search($unit, $arr);
                            if ($key) {
                                $key1 = $key - 1;
                                $dosage_arr[] = $arr[$key1];
                                $unit_arr[] = $arr[$key];
                            }
                        }
                    }
                    $data[] = [
                        'id' => $row['rxcui'],
                        'label' => $row['name'],
                        'value' => $row['name'],
                        'badge' => $row['category'],
                        'dosage' => implode(';', $dosage_arr),
                        'unit' => implode(';', $unit_arr),
                        'rxcui' => $row['rxcui'],
                    ];
                }
            } else {
                $data = ['message' => 'No data found'];
            }

            return $data;
        }
        if ($search && $type === 'icd10') {

            $assessment = false;
            $q = $search;
            $pos = explode(' ', $q);
            $preicd = [];
            $data = [];
            $file = File::get(public_path().'/icd10cm_order_2017.txt');
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
            if (count($pos) == 1) {
                $result = array_filter($preicd, function ($value) use ($q) {
                    if (stripos($value['desc'], $q) !== false) {
                        return true;
                    }
                });
                $result1 = array_filter($preicd, function ($value) use ($q) {
                    if (stripos($value['icd10'], $q) !== false) {
                        return true;
                    }
                });
                $result = array_merge($result, $result1);
            } else {
                $result = array_filter($preicd, function ($value) use ($pos) {
                    if ($this->striposa($value['desc'], $pos) !== false) {
                        return true;
                    }
                });
            }
            if ($result) {

                if ($assessment == true) {
                    $data['response'] = 'div';
                }
                foreach ($result as $row) {
                    $records = $row['desc'].' ['.$row['icd10'].']';
                    if ($assessment == true) {

                        if ($row['type'] == '0') {
                        }
                        $data[] = [
                            'id' => $row['icd10'],
                            'label' => $records,
                            'value' => $records,
                            'icd10type' => $row['type'],
                            'category' => 'Universal Library',
                            'category_id' => 'universal_icd_result',
                        ];
                    } else {
                        $data[] = [
                            'id' => $row['icd10'],
                            'label' => $records,
                            'value' => $records,
                            'icd10type' => $row['type'],
                            'category' => 'Universal Library',
                            'category_id' => 'universal_icd_result',
                        ];
                    }
                }
            } else {
                $data = ['message' => 'No data found'];
            }

            return $data;
        }
    }

    /**
     * icd10data
     *
     * @param  mixed  $icd10q
     * @return void
     */
    public function icd10data($icd10q)
    {
        $url = 'http://www.icd10data.com/Search.aspx?search='.$icd10q.'&codeBook=ICD10CM';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        $result = curl_exec($ch);
        curl_close($ch);

        if (! $result) {
            return [];
        }
        $html = new Htmldom($result);
        $data = [];

        if (! isset($html)) {
            return [];
        }

        // Extract ICD-10 codes and descriptions
        foreach ($html->find('.search-results-item') as $item) {
            $code = trim($item->find('.search-results-code', 0)->plaintext ?? '');
            $desc = trim($item->find('.search-results-description', 0)->plaintext ?? '');

            if ($code && $desc) {
                $data[] = [
                    'code' => $code,
                    'desc' => $desc,
                ];
            }
        }

        // Handle pagination (only fetch first 3 pages)
        $pagination = $html->find('ul.pagination', 0);
        if ($pagination) {
            $i = 1;
            foreach ($pagination->find('li a') as $page_icd) {
                if ($i < 3) {
                    $data = $this->icd10data_get($i, $data, $icd10q);
                }
                $i++;
            }
        }

        return $data;
    }

    /**
     * icd10data_get
     *
     * @param  mixed  $page
     * @param  mixed  $data
     * @param  mixed  $icd10q
     * @return void
     */
    protected function icd10data_get($page, $data, $icd10q)
    {

        $url = 'http://www.icd10data.com/Search.aspx?search='.$icd10q.'&codeBook=ICD10CM'.'&page='.$page;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        $result = curl_exec($ch);
        $html = new Htmldom($result);
        if (isset($html)) {
            foreach ($html->find('div.SearchResultItem') as $link) {
                $status1 = $link->find('img.img2', 0);
                if (isset($status1->src)) {
                    $status = $status1->src;
                    if ($status == '/images/bullet_triangle_green.png') {
                        $code1 = $link->find('span.identifier', 0);
                        $code = $code1->innertext;
                        $desc1 = $link->find('div.SearchResultDescription', 0);
                        $desc = $desc1->plaintext;
                        $common_records = $desc.' ['.$code.']';
                        $data[] = [
                            'code' => $code,
                            'desc' => $common_records,
                        ];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * searchCPT
     *
     * @param  mixed  $request
     * @return void
     */
    public function searchCPT(Request $request)
    {
        $q = trim(strtolower($request->input('search_cpt', '')));
        if ($q === '') {
            return;
        }

        $hospitalId = auth()->user()?->doctor?->hospital_id;
        $data = ['response' => 'false', 'message' => []];

        if ($q === '***') {
            $favorites = CPTRelate::select('cpt', 'cpt_description', 'cpt_charge', 'unit')
                ->where('favorite', 1)
                ->where('hospital_id', $hospitalId)
                ->get();

            if ($favorites->isNotEmpty()) {
                $data['response'] = 'li';
                $data['message'] = $favorites->map(function ($row) {
                    return [
                        'label' => "{$row->cpt_description} [{$row->cpt}]",
                        'value' => $row->cpt,
                        'charge' => $row->cpt_charge,
                        'unit' => $row->unit ?? '1',
                        'category' => 'Favorites',
                        'category_id' => 'favorite_cpt_result',
                    ];
                })->all();
            }

            return $data;
        }

        $query = CPTRelate::select('cpt', 'cpt_description', 'cpt_charge', 'unit')
            ->where('hospital_id', $hospitalId)
            ->where(function ($qBuilder) use ($q) {
                $parts = array_filter(explode(',', $q));
                if (count($parts) <= 1) {
                    $qBuilder->where('cpt_description', 'LIKE', "%{$q}%")
                        ->orWhere('cpt', 'LIKE', "%{$q}%");
                } else {
                    foreach ($parts as $term) {
                        $qBuilder->where('cpt_description', 'LIKE', "%{$term}%");
                    }
                    $qBuilder->orWhere('cpt', 'LIKE', "%{$q}%");
                }
            })
            ->limit(30)
            ->get();

        foreach ($query as $row) {
            $data['message'][] = [
                'label' => "{$row->cpt_description} [{$row->cpt}]",
                'value' => $row->cpt,
                'charge' => $row->cpt_charge,
                'unit' => $row->unit ?? '1',
                'category' => 'Practice CPT Database',
                'category_id' => 'practice_cpt_result',
            ];
        }

        $precpt = Cache::rememberForever('cpt_fast_list', function () {
            $file = resource_path('CPT.txt');
            $handle = fopen($file, 'r');
            $data = [];

            if ($handle) {
                while (($line = fgetcsv($handle, 0, "\t")) !== false) {
                    $data[] = [
                        'cpt' => $line[0] ?? '',
                        'desc' => $line[3] ?? '',
                    ];
                }
                fclose($handle);
            }

            return array_map(function ($item) {
                return [
                    'cpt' => strtolower($item['cpt']),
                    'desc' => strtolower($item['desc']),
                ];
            }, $data);
        });

        $results = [];
        $pos1 = array_filter(explode(',', $q));

        foreach ($precpt as $item) {
            $found = false;
            if (count($pos1) === 1) {
                $found = (str_contains($item['desc'], $q) || str_contains($item['cpt'], $q));
            } else {
                foreach ($pos1 as $term) {
                    if (str_contains($item['desc'], trim($term))) {
                        $found = true;
                        break;
                    }
                }
            }

            if ($found) {
                $results[] = $item;
                if (count($results) >= 50) {
                    break;
                }
            }
        }

        foreach ($results as $row) {
            if (! collect($data['message'])->pluck('value')->contains($row['cpt'])) {
                $data['message'][] = [
                    'label' => ucfirst($row['desc'])." [{$row['cpt']}]",
                    'value' => $row['cpt'],
                    'charge' => '',
                    'unit' => '1',
                    'category' => 'Universal CPT Database',
                    'category_id' => 'universal_cpt_result',
                ];
            }
        }

        $data['response'] = $data['message'] ? 'li' : 'false';

        return $data;
    }

    /**
     * search_supplement
     *
     * @param  mixed  $request
     * @param  mixed  $order
     * @return void
     */
    public function searchSupplement(Request $request, $order)
    {
        $q = strtolower($request->input('search_supplement'));
        if (! $q) {
            return;
        }
        $data['response'] = 'false';
        $query = DB::table('supplement_inventories')
            ->where('sup_description', 'LIKE', "%$q%")
            ->where('quantity', '>', '0')
            ->where('hospital_id', '=', auth()->user()->doctor->hospital_id)
            ->select('sup_description', 'quantity', 'cpt', 'charge', 'sup_strength', 'id')
            ->distinct()
            ->get();
        if ($query->count()) {
            $data['message'] = [];
            $data['response'] = 'li';
            foreach ($query as $row) {
                if ($order == 'Y') {
                    if (strpos($row->sup_strength, ' ') === false) {
                        $dosage_array[0] = $row->sup_strength;
                        $dosage_array[1] = '';
                    } else {
                        $dosage_array = explode(' ', $row->sup_strength);
                    }
                    $label = $row->sup_description.', Quantity left: '.$row->quantity1;
                    $data['message'][] = [
                        'label' => $label,
                        'value' => $row->sup_description,
                        'category' => 'Supplements Inventory',
                        'quantity' => $row->quantity1,
                        'dosage' => $dosage_array[0],
                        'dosage_unit' => $dosage_array[1],
                        'id' => $row->id,
                    ];
                } else {
                    if (strpos($row->sup_strength, ' ') === false) {
                        $dosage_array[0] = $row->sup_strength;
                        $dosage_array[1] = '';
                    } else {
                        $dosage_array = explode(' ', $row->sup_strength);
                    }
                    $data['message'][] = [
                        'label' => $row->sup_description,
                        'value' => $row->sup_description,
                        'category' => 'Supplements Inventory',
                        'dosage' => $dosage_array[0],
                        'dosage_unit' => $dosage_array[1],
                        'id' => $row->id,
                    ];
                }
            }
        }
        $query0 = DB::table('supplement_lists')
            ->where('sup_supplement', 'LIKE', "%$q%")
            ->select('sup_supplement', 'sup_dosage', 'sup_dosage_unit')
            ->distinct()
            ->get();
        if ($query0->count()) {
            if (! isset($data['message'])) {
                $data['message'] = [];
                $data['response'] = 'li';
            }
            foreach ($query0 as $row0) {
                if ($order == 'Y') {
                    $label0 = $row0->sup_supplement.', Dosage: '.$row0->sup_dosage.' '.$row0->sup_dosage_unit;
                    $data['message'][] = [
                        'label' => $label0,
                        'value' => $row0->sup_supplement,
                        'category' => 'Previously Prescribed',
                        'quantity' => '',
                        'dosage' => $row0->sup_dosage,
                        'dosage_unit' => $row0->sup_dosage_unit,
                        'id' => '',
                    ];
                } else {
                    $data['message'][] = [
                        'label' => $row0->sup_supplement,
                        'value' => $row0->sup_supplement,
                        'category' => '',
                    ];
                }
            }
        }
        $yaml = File::get(resource_path().'/supplements.yaml');
        $formatter = Formatter::make($yaml, Formatter::YAML);
        $arr = $formatter->toArray();
        $result1 = Arr::where($arr, function ($value, $key) use ($q) {
            if (stripos($value, $q) !== false) {
                return true;
            }
        });
        if ($result1) {
            $data['response'] = 'li';
            foreach ($result1 as $row1) {
                if ($order == 'Y') {
                    $data['message'][] = [
                        'label' => $row1,
                        'value' => $row1,
                        'category' => 'Supplement Database',
                        'quantity' => '',
                        'dosage' => '',
                        'dosage_unit' => '',
                        'id' => '',
                    ];
                } else {
                    $data['message'][] = [
                        'label' => $row1,
                        'value' => $row1,
                        'category' => '',
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * searchLoinc
     *
     * @param  mixed  $request
     * @return void
     */
    public function searchLoinc(Request $request)
    {
        $q = strtolower(trim($request->input('search', '')));
        if ($q === '') {
            return response()->json(['response' => 'false', 'message' => []]);
        }

        $data = ['response' => 'false', 'message' => []];
        $pos = explode(' ', $q);

        $rows = Cache::rememberForever('loinc_parsed_rows', function () {
            $file = resource_path('LOINC.csv');

            if (! File::exists($file)) {
                return [];
            }

            $handle = fopen($file, 'r');
            $headers = fgetcsv($handle) ?: [];

            // Normalize headers (e.g. "Loinc #" → "loinc_#")
            $headers = array_map(fn ($h) => strtolower(str_replace(' ', '_', trim($h ?? ''))), $headers);

            $rows = [];
            while (($row = fgetcsv($handle)) !== false) {
                // Ensure row has same length as headers
                $assoc = array_combine($headers, $row + array_fill(0, count($headers), null));
                if (! empty(array_filter($assoc))) {
                    $assoc['loinc_#'] = strtolower($assoc['loinc_#'] ?? '');
                    $assoc['long_common_name'] = strtolower($assoc['long_common_name'] ?? '');
                    $rows[] = $assoc;
                }
            }

            fclose($handle);

            return $rows;
        });

        if (empty($rows)) {
            return response()->json($data);
        }

        // Filter logic
        if (count($pos) === 1) {
            $result = array_filter($rows, fn ($item) => str_contains($item['long_common_name'], $q));
        } else {
            $result = array_filter($rows, function ($item) use ($pos) {
                foreach ($pos as $term) {
                    if (str_contains($item['long_common_name'], strtolower(trim($term)))) {
                        return true;
                    }
                }

                return false;
            });
        }

        // Prepare response
        if (! empty($result)) {
            $data['response'] = 'li';
            foreach (array_slice($result, 0, 50) as $row) {
                $loinc = strtoupper($row['loinc_#'] ?? '');
                $desc = ucfirst($row['long_common_name'] ?? '');
                $label = "$desc [$loinc]";

                $data['message'][] = [
                    'id' => $loinc,
                    'label' => $label,
                    'value' => $label,
                ];
            }
        }

        return response()->json($data);
    }

    /**
     * search_imaging
     *
     * @param  mixed  $request
     * @return void
     */
    public function searchImaging(Request $request)
    {
        $q = strtolower($request->input('search'));
        if (! $q) {
            return;
        }
        $data['response'] = 'false';
        $data['message'] = [];
        if (($handle = fopen(resource_path().'/imaging.csv', 'r')) !== false) {
            while (($data1 = fgetcsv($handle, 0, "\t")) !== false) {
                if ($data1[0] != '') {
                    $pre[] = [
                        'code' => $data1[0],
                        'desc' => $data1[1],
                    ];
                }
            }
            fclose($handle);
        }
        $pos = explode(' ', $q);
        if (count($pos) == 1) {
            $result = Arr::where($pre, function ($value, $key) use ($q) {
                if (stripos($value['desc'], $q) !== false) {
                    return true;
                }
            });
        } else {
            $result = Arr::where($pre, function ($value, $key) use ($pos) {
                if ($this->striposa($value['desc'], $pos) !== false) {
                    return true;
                }
            });
        }
        if ($result) {
            $data['response'] = 'li';
            foreach ($result as $row) {
                $data['message'][] = [
                    'label' => $row['desc'],
                    'value' => $row['desc'].' ['.$row['code'].']',
                ];
            }
        }

        return $data;
    }

    public function searchRx(Request $request)
    {
        $search = $request->get('search');
        $data = [];
        if ($search) {

            $url = 'http://rxnav.nlm.nih.gov/REST/Prescribe/drugs.json?name='.$search;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            $json = curl_exec($ch);
            curl_close($ch);
            $rxnorm = json_decode($json, true);
            $result = [];
            $i = 0;
            if (isset($rxnorm['drugGroup']['conceptGroup'])) {
                foreach ($rxnorm['drugGroup']['conceptGroup'] as $rxnorm_row1) {
                    if ($rxnorm_row1['tty'] == 'SBD' || $rxnorm_row1['tty'] == 'SCD') {
                        if (isset($rxnorm_row1['conceptProperties'])) {
                            foreach ($rxnorm_row1['conceptProperties'] as $item) {
                                $result[$i]['rxcui'] = $item['rxcui'];
                                $result[$i]['name'] = $item['name'];
                                if ($rxnorm_row1['tty'] == 'SBD') {
                                    $result[$i]['category'] = 'Brand';
                                } else {
                                    $result[$i]['category'] = 'Generic';
                                }
                                $i++;
                            }
                        }
                    }
                }
                uasort($result, function ($a, $b) {
                    return ($a['name'] < $b['name']) ? -1 : (($a['name'] > $b['name']) ? 1 : 0);
                });
            }
            if (isset($result[0])) {
                foreach ($result as $row) {
                    $arr = explode(' / ', $row['name']);
                    $units = ['MG', 'MG/ML', 'MCG'];
                    $dosage_arr = [];
                    $unit_arr = [];
                    foreach ($arr as $row1) {
                        $arr = explode(' ', $row1);
                        foreach ($units as $unit) {
                            $key = array_search($unit, $arr);
                            if ($key) {
                                $key1 = $key - 1;
                                $dosage_arr[] = $arr[$key1];
                                $unit_arr[] = $arr[$key];
                            }
                        }
                    }
                    $data[] = [
                        'id' => $row['rxcui'],
                        'label' => $row['name'],
                        'value' => $row['name'],
                        'badge' => $row['category'],
                        'dosage' => implode(';', $dosage_arr),
                        'unit' => implode(';', $unit_arr),
                        'rxcui' => $row['rxcui'],
                    ];
                }
            } else {
                $data = ['message' => 'No data found'];
            }

            return $data;
        }
    }

    /**
     * searchImmunization
     *
     * @param  mixed  $request
     * @return void
     */
    public function searchImmunization(Request $request)
    {
        $q = strtolower(trim($request->input('search_immunization')));

        if (empty($q)) {
            return response()->json([
                'response' => 'false',
                'message' => [],
            ]);
        }

        $data = [
            'response' => 'false',
            'message' => [],
        ];

        $keywords = array_map('trim', explode(',', $q));

        // Read cvx.txt using Excel::toArray
        $path = resource_path('cvx.txt');
        $rows = Excel::toArray([], $path)[0];

        $pre = [];

        foreach ($rows as $row) {
            $status = isset($row[4]) ? trim($row[4]) : '';

            if ($status === 'Active') {
                $cvx = isset($row[0]) ? trim($row[0]) : '';
                $shortDesc = isset($row[1]) ? trim($row[1]) : '';
                $longDesc = isset($row[2]) ? trim($row[2]) : '';

                if (! empty($cvx)) {
                    $pre[] = [
                        'cvx' => $cvx,
                        'short_desc' => $shortDesc,
                        'long_desc' => ucfirst($longDesc),
                    ];
                }
            }
        }

        // Search Logic
        if (count($keywords) === 1) {

            $result = array_filter($pre, function ($value) use ($q) {
                return stripos($value['cvx'], $q) !== false ||
                       stripos($value['long_desc'], $q) !== false;
            });

        } else {

            $result = array_filter($pre, function ($value) use ($keywords) {
                foreach ($keywords as $word) {
                    if (stripos($value['long_desc'], $word) === false) {
                        return false;
                    }
                }

                return true;
            });
        }

        if (! empty($result)) {
            $data['response'] = 'li';

            $addedCvx = [];

            foreach ($result as $row) {
                if (! in_array($row['cvx'], $addedCvx)) {
                    $data['message'][] = [
                        'label' => $row['short_desc'],
                        'value' => $row['long_desc'],
                        'cvx' => $row['cvx'],
                    ];

                    $addedCvx[] = $row['cvx'];
                }
            }
        }

        return response()->json($data);
    }
}

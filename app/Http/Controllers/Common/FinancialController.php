<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\BillingCore;
use App\Models\Encounter;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Schedule;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FinancialController extends Controller
{
    use CommonTrait;

    public function bills_to_submit(Request $request)
    {
        $keyword = $request->get('keyword');

        // Build QUERY BUILDER (do NOT call ->get() yet)
        $query = Encounter::with('patient.user')
            ->where('bill_submitted', '!=', 'Done')
            ->where('addendum', 'n')
            ->where('hospital_id', auth()->user()->doctor->hospital_id)
            ->orderBy('encounter_date_of_service', 'desc');

        // SEARCH FILTER
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('patient.user.name', 'like', "%$keyword%")
                    ->orWhere('patient.user.name', 'like', "%$keyword%")
                    ->orWhere('encounters.encounter_date_of_service', 'like', "%$keyword%");
            });
        }

        // FETCH RESULTS AFTER FILTERING
        $encounters = $query->paginate();
        // PROCESS BILLING TOTALS
        $bills = [];
        foreach ($encounters as $row) {
            $bills[] = [
                'id' => $row->id,
                'date_of_service' => $row->encounter_date_of_service,
                'patient_name' => $row->patient->user->name,
                'chief_complaint' => $row->chief_complaint,
                'encounter_signed' => $row->encounter_signed,
                'batch_type' => $row->batch_type,
                'encounter_id' => $row->id,
                'patient_id' => $row->patient_id,
                'hospital_id' => $row->hospital_id,
            ];
        }
        $encounters->setCollection(collect($bills));

        return Inertia::render('Common/Financial/BillsToSubmit', [
            'bills' => $encounters,
            'keyword' => request()->get('keyword', ''),
        ]);
    }

    public function processed_bills(Request $request)
    {
        $keyword = $request->get('keyword');

        // Build QUERY BUILDER (do NOT call ->get() yet)
        $query = Encounter::with('patient.user')
            ->where('bill_submitted', 'Done')
            ->where('addendum', 'n')
            ->where('hospital_id', auth()->user()->doctor->hospital_id)
            ->orderBy('encounter_date_of_service', 'desc');

        // SEARCH FILTER
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('patient.user.name', 'like', "%$keyword%")
                    ->orWhere('patient.user.name', 'like', "%$keyword%")
                    ->orWhere('encounters.encounter_date_of_service', 'like', "%$keyword%");
            });
        }

        // FETCH RESULTS AFTER FILTERING
        $encounters = $query->paginate();
        // PROCESS BILLING TOTALS
        $bills = [];
        foreach ($encounters as $row) {

            $billing = DB::table('billing_cores')->where('encounter_id', $row->id)->get();

            $charges = $billing->sum(function ($b) {
                return $b->cpt_charge * $b->unit;
            });

            $payments = $billing->sum('payment');

            $balance = $charges - $payments;
            $bills[] = [
                'id' => $row->id,
                'date_of_service' => $row->encounter_date_of_service,
                'patient_name' => $row->patient->user->name,
                'chief_complaint' => $row->chief_complaint,
                'charges' => number_format($charges, 2),
                'total_balance' => number_format($balance, 2),
                'date_processed' => $row->updated_at->format('Y-m-d'),
                'encounter_id' => $row->id,
                'patient_id' => $row->patient_id,
                'hospital_id' => $row->hospital_id,
            ];
        }

        $encounters->setCollection(collect($bills));

        return Inertia::render('Common/Financial/ProcessedBills', [
            'bills' => $encounters,
            'keyword' => $keyword,
        ]);
    }

    public function outstanding_balances()
    {
        $patients = Patient::with(['user'])->get();

        $result = [];

        foreach ($patients as $patient) {

            // Notes (safe handling even if null)
            $notes = DB::table('patient_notes')
                ->where('patient_id', $patient->id)
                ->where('hospital_id', auth()->user()->doctor->hospital_id)
                ->first();

            $billingNotes = $notes->billing_notes ?? '';

            // All encounters
            $encounters = Encounter::with(['patient.user'])
                ->where('patient_id', $patient->id)
                ->where('addendum', 'n')
                ->where('doctor_id', auth()->user()->id)
                ->get();

            $balanceEncounter = 0;

            foreach ($encounters as $enc) {
                $billing = BillingCore::where('patient_id', $patient->id)
                    ->where('encounter_id', $enc->id)
                    ->get();

                $charge = $billing->sum(function ($b) {
                    return $b->cpt_charge * $b->unit;
                });

                $payment = $billing->sum('payment');

                $balanceEncounter += ($charge - $payment);
            }

            // Other billing (encounter_id=0 cases)
            $otherBilling = BillingCore::where('patient_id', $patient->id)
                ->where('encounter_id', 0)
                ->where('payment', 0)
                ->get();

            $balanceOther = 0;

            foreach ($otherBilling as $ob) {

                $payments = BillingCore::where('other_billing_id', $ob->other_billing_id)
                    ->sum('payment');

                $charge = $ob->cpt_charge * $ob->unit;

                $balanceOther += ($charge - $payments);
            }

            $totalBalance = $balanceEncounter + $balanceOther;

            // Only include if has balance or billing note
            if ($totalBalance >= 0.01 || $billingNotes !== '') {
                $result[] = [
                    'patient_id' => $patient->id,
                    'patient_name' => $patient->user->name,
                    'balance' => $totalBalance,
                    'billing_notes' => $billingNotes,
                ];
            }
        }

        return Inertia::render('Common/Financial/OutstandingBalances', [
            'bills' => $result,
            'keyword' => request()->get('keyword', ''),
        ]);
    }

    public function monthly_financial_report()
    {
        $query = DB::table('encounters')
            ->select(DB::raw("DATE_FORMAT(encounter_date_of_service, '%Y-%m') as month"), DB::raw('COUNT(*) as patients_seen'))
            ->where('addendum', 'n')
            ->where('doctor_id', auth()->user()->id)
            ->where('hospital_id', auth()->user()->doctor->hospital_id)
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        $result = [];

        if ($query->count()) {
            foreach ($query as $row_obj) {
                // Initialize row array
                $row = [];
                $row['patients_seen'] = $row_obj->patients_seen;
                $row['month'] = $row_obj->month;

                // Extract year + month
                [$year, $month] = explode('-', $row_obj->month);

                // Initialize counters
                $row['total_billed'] = 0;
                $row['total_payments'] = 0;
                $row['dnka'] = 0;
                $row['lmc'] = 0;

                // 1. Get encounter IDs for the month
                $encounters = DB::table('encounters')
                    ->select('id')
                    ->whereYear('encounter_date_of_service', '=', $year)
                    ->whereMonth('encounter_date_of_service', '=', $month)
                    ->where('addendum', 'n')
                    ->where('doctor_id', auth()->user()->id)
                    ->where('hospital_id', auth()->user()->doctor->hospital_id)
                    ->get();

                // 2. Compute billed + payments
                foreach ($encounters as $enc) {
                    $billing = BillingCore::where('patient_id', $enc->patient_id ?? '0')
                        ->where('encounter_id', $enc->id)
                        ->get();

                    $charge = 0;
                    $payment = 0;

                    foreach ($billing as $b) {
                        if ($b->payment_type !== 'Write-Off') {
                            $charge += $b->cpt_charge * $b->unit;
                            $payment += $b->payment;
                        }
                    }

                    $row['total_billed'] += $charge;
                    $row['total_payments'] += $payment;
                }

                // 3. Count DNKA + LMC
                $schedule = Schedule::select('schedule_status')
                    ->join('doctors', 'doctors.id', '=', 'doctor_id')
                    ->whereYear('start_date', $year)
                    ->whereMonth('start_date', $month)
                    ->where('hospital_id', auth()->user()->doctor->hospital_id)
                    ->get();

                foreach ($schedule as $sch) {
                    if ($sch->status === 'DNKA') {
                        $row['dnka'] += 1;
                    }
                    if ($sch->status === 'LMC') {
                        $row['lmc'] += 1;
                    }
                }
                // Push to result
                $result[] = $row;
            }
        }

        return Inertia::render('Common/Financial/MonthlyFinancialReport', [
            'bills' => $result,
            'keyword' => request()->get('search', ''),
        ]);
    }

    public function yearly_financial_report(Request $request)
    {
        $query = DB::table('encounters')
            ->select(DB::raw('YEAR(encounter_date_of_service) as year, COUNT(*) as patients_seen'))
            ->where('addendum', 'n')
            ->where('hospital_id', auth()->user()->doctor->hospital_id)
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        if ($query->count()) {
            foreach ($query as $row_obj) {

                $row = [];
                $row['patients_seen'] = $row_obj->patients_seen;
                $row['year'] = $row_obj->year;
                $row['total_billed'] = 0;
                $row['total_payments'] = 0;
                $row['dnka'] = 0;
                $row['lmc'] = 0;

                // Get all encounters for that year
                $query1a = DB::table('encounters')
                    ->select('id')
                    ->where('doctor_id', auth()->user()->id)
                    ->whereYear('encounter_date_of_service', $row['year'])
                    ->where('addendum', 'n')
                    ->where('hospital_id', auth()->user()->doctor->hospital_id)
                    ->get();

                foreach ($query1a as $row1) {
                    $query2 = BillingCore::where('encounter_id', $row1->id)->get();

                    if ($query2->count()) {
                        $charge = 0;
                        $payment = 0;

                        foreach ($query2 as $row2) {
                            if ($row2->payment_type != 'Write-Off') {
                                $charge += $row2->cpt_charge * $row2->unit;
                                $payment += $row2->payment;
                            }
                        }

                        $row['total_billed'] += $charge;
                        $row['total_payments'] += $payment;
                    }
                }

                // Count DNKA / LMC for that year
                $query1b = Schedule::join('doctors', 'doctors.id', '=', 'doctor_id')
                    ->whereYear(DB::raw('FROM_UNIXTIME(start_date)'), '=', $row['year'])
                    ->where('doctors.hospital_id', auth()->user()->doctor->hospital_id)
                    ->get();

                foreach ($query1b as $row3) {
                    if ($row3->status === 'DNKA') {
                        $row['dnka'] += 1;
                    }
                    if ($row3->status === 'LMC') {
                        $row['lmc'] += 1;
                    }
                }

                $result[] = $row;
            }
        }

        return Inertia::render('Common/Financial/YearlyFinancialReport', [
            'bills' => $result,
            'keyword' => request()->get('search', ''),
        ]);
    }

    public function financial_patient(Request $request, $type, $id) {}

    public function financial_resubmit($id)
    {
        $row = Billing::where('encounter_id', '=', $id)->first();

        if ($row) {
            if ($row->insurance_id_1 == '0' || $row->insurance_id_1 == '') {
                return redirect()->back()->with('error', 'No insurance was assigned, cannot be resubmitted.');
            } else {
                $data['bill_submitted'] = 'No';
                Encounter::where('id', '=', $id)->update($data);
            }
        }

        return redirect()->back()->with('success', 'Financial resubmission successful.');
    }

    public function financial_insurance(Request $request)
    {
        $date = $request->input('date');

        // $result = [];

        // $query = Encounter::with(['billing.insurance'])
        //     ->where('addendum', 'n')
        //     ->where('hospital_id', auth()->user()->doctor->hospital_id);

        // // Apply date filter BEFORE get()
        // if ($date) {
        //     $id_arr = explode("-", $date);

        //     if (count($id_arr) > 1) {
        //         // Month-Year
        //         $query->whereYear('encounter_date_of_service', $id_arr[0])
        //             ->whereMonth('encounter_date_of_service', $id_arr[1]);
        //     } else {
        //         // Year only
        //         $query->whereYear('encounter_date_of_service', $date);
        //     }
        // }

        // // Fetch final data
        // $encounters = $query->get();

        // // Group by insurance plan
        // $grouped = $encounters->groupBy(function ($encounter) {
        //     return optional(optional($encounter->billing)->insurance)->plan_name ?? 'No Insurance';
        // });

        // // Format output
        // foreach ($grouped as $planName => $items) {
        //     $result[] = [
        //         'insurance'     => $planName,
        //         'patients_seen' => $items->unique('patient_id')->count(),
        //     ];
        // }

        $result = [];
        $return = '';
        $query = DB::table(DB::raw('billings as t1'))
            ->leftJoin(DB::raw('insurances as t2'), 't1.insurance_id_1', '=', 't2.id')
            ->leftJoin(DB::raw('encounters as t3'), 't1.encounter_id', '=', 't3.id')
            ->select(DB::raw('t2.plan_name as insuranceplan, COUNT(*) as patient_id'));
        $id_arr = explode('-', $date);

        if (count($id_arr) > 1) {
            $query->where(DB::raw('YEAR(t3.encounter_date_of_service)'), '=', $id_arr[0])->where(DB::raw('MONTH(t3.encounter_date_of_service)'), '=', $id_arr[1]);
            $data['panel_header'] = 'Monthly Insurance Data';
        } else {
            $query->where(DB::raw('YEAR(t3.encounter_date_of_service)'), '=', $date);
            $data['panel_header'] = 'Yearly Insurance Data';
        }
        $query->where('t3.addendum', '=', 'n')
            ->where('t3.hospital_id', '=', auth()->user()->doctor->hospital_id)
            ->groupBy('insuranceplan');
        $query_result = $query->get();
        if ($query_result->count()) {
            foreach ($query_result as $query_row) {
                if (is_null($query_row->insuranceplan)) {
                    $query_row->insuranceplan = 'Cash Only';
                }
                $result[] = [
                    'insurance' => $query_row->insuranceplan,
                    'patients_seen' => collect($query_row)->unique('patient_id')->count(),
                ];
            }
        }

        return Inertia::render('Common/Financial/FinancialInsurance', [
            'insurance' => $result,
            'keyword' => request()->get('search', ''),
        ]);
    }

    public function custom_report_payment(Request $request)
    {
        $hospitalId = auth()->user()->doctor->hospital_id;

        // Get distinct payment types
        $variables = BillingCore::where('hospital_id', $hospitalId)
            ->whereNotNull('payment_type')
            ->distinct()
            ->orderBy('payment_type', 'asc')
            ->pluck('payment_type')
            ->toArray();

        // Get distinct year values from dos_f
        $years = [];

        $dates = BillingCore::where('hospital_id', $hospitalId)
            ->distinct()
            ->pluck('dos_f')
            ->toArray();

        foreach ($dates as $date) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $year = substr($date, 0, 4);
                $years[$year] = $year;
            }

            // If format is mm/dd/yyyy or dd/mm/yyyy (optional support)
            elseif (str_contains($date, '/')) {
                $parts = explode('/', $date);
                if (! empty($parts[2])) {
                    $years[$parts[2]] = $parts[2];
                }
            }
        }

        krsort($years); // sort years descending

        $data = [
            'variables' => array_values($variables),
            'years' => array_values($years),
        ];

        return Inertia::render('Common/Financial/CustomPayment', [
            'data' => $data,
        ]);
    }

    public function custom_report_procedure(Request $request)
    {
        $hospitalId = auth()->user()->doctor->hospital_id;

        // Get distinct payment types
        $variables = BillingCore::where('hospital_id', $hospitalId)
            ->whereNotNull('cpt')
            ->distinct()
            ->orderBy('cpt', 'asc')
            ->pluck('cpt')
            ->toArray();
        // Get distinct year values from dos_f
        $years = [];

        $dates = BillingCore::where('hospital_id', $hospitalId)
            ->distinct()
            ->pluck('dos_f')
            ->toArray();

        foreach ($dates as $date) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $year = substr($date, 0, 4);
                $years[$year] = $year;
            }

            // If format is mm/dd/yyyy or dd/mm/yyyy (optional support)
            elseif (str_contains($date, '/')) {
                $parts = explode('/', $date);
                if (! empty($parts[2])) {
                    $years[$parts[2]] = $parts[2];
                }
            }
        }

        krsort($years); // sort years descending

        $data = [
            'variables' => array_values($variables),
            'years' => array_values($years),
        ];

        return Inertia::render('Common/Financial/CustomProcedureCode', [
            'data' => $data,
        ]);
    }

    public function financial_queue(Request $request)
    {
        $request->validate([
            'variables' => 'nullable|array',
            'variables.*' => 'string',
            'type' => 'nullable|string|in:payment_type,cpt',
            'year' => 'nullable|array',
            'year.*' => 'digits:4',
            'option' => 'nullable|string',
        ]);

        $result = [];
        // Base query
        $query_text1 = BillingCore::where('hospital_id', auth()->user()->doctor->hospital_id);
        // Inputs
        $variables_array = $request->input('variables', []);
        $type = $request->input('type');
        $year_array = $request->input('year', []);
        // Filter by variables
        if (! empty($variables_array) && ! empty($type)) {
            $query_text1->whereIn($type, $variables_array);
        }
        // Filter by year
        if (! empty($year_array)) {
            $query_text1->where(function ($q) use ($year_array) {
                foreach ($year_array as $year) {
                    $q->orWhere('dos_f', 'LIKE', "%$year%");
                }
            });
        }

        // Fetch Data
        $query = $query_text1->get();
        // Format Results
        foreach ($query as $row) {

            $patient = Patient::with('user')->where('id', $row->patient_id)->first();
            $type1 = ($type == 'payment_type')
                ? $row->payment_type
                : 'CPT Code: '.$row->cpt;

            $amount = ($type == 'payment_type')
                ? $row->payment
                : $row->cpt_charge;

            $result[] = [
                'id' => $row->id,
                'dos_f' => $row->dos_f,
                'patient_name' => $patient->patient->name ?? $patient->user->name ?? $patient->first_name.' '.$patient->last_name,
                'amount' => $amount,
                'amount_type' => $type1,
                'variables' => $variables_array,
                'type' => $type,
                'year' => $year_array,
            ];
        }
        if ($request->input('option') == 'print') {
            return $result;
        }

        return Inertia::render('Common/Financial/financialQueue', [
            'data' => $result ?? '',
        ]);
    }

    protected function page_intro($title, $hospital_id, $result)
    {
        $hospital = Hospital::find($hospital_id);

        return [
            'practiceName' => $hospital?->name,
            'practiceInfo' => $this->getPracticeInfo($hospital),
            'practiceLogo' => $this->getPracticeLogo($hospital),
            'title' => $title,
            'tableData' => $result, // renamed field
        ];
    }

    public function download(Request $request)
    {
        $result = $request->input('data');
        if (! file_exists(public_path('temp'))) {
            mkdir(public_path('temp'), 0777, true);
        }

        $file_path = public_path('temp/'.time().'_'.auth()->user()->id.'_financialquery.pdf');

        $pageData = $this->page_intro('Financial Query Results', auth()->user()->doctor->hospital_id, $result);
        $html = view('pdf.intro', $pageData)->render();
        $this->generate_pdf($html, $file_path);

        return response()->download($file_path)->deleteFileAfterSend(true);
    }
}

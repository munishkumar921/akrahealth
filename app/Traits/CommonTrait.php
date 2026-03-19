<?php

namespace App\Traits;

use App\Models\Allergy;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Insurance;
use App\Models\Lab;
use App\Models\Order;
use App\Models\Patient;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

trait CommonTrait
{
    protected function goodrx_drug_search($rx)
    {
        $rx = rtrim($rx, ',');
        $result = $this->goodrx($rx, 'drug-search');
        $drug = $rx;
        if ($result['success'] == true) {
            $drug = current($result['data']['candidates']);
        }

        return $drug;
    }

    protected function goodrx_information($rx, $dose)
    {
        $rx1 = explode(',', $rx);
        $rx_array = explode(' ', $rx1[0]);
        $dose_array = explode('/', $dose);
        $link = '';
        $result = $this->goodrx($rx_array[0], 'drug-info');
        $type_arr = [
            'chewable tablet' => 'chewable',
            'tablet' => 'tablet',
            'capsule' => 'capsule',
            'bottle of oral suspension' => 'suspension',
        ];
        if ($result['success'] == true) {
            $key = false;
            foreach ($rx_array as $item) {
                $key = array_search(strtolower($item), $type_arr);
                if ($key !== false) {
                    break;
                }
            }
            if ($key !== false) {
                if (isset($result['data']['drugs'][$key][$dose_array[0]])) {
                    $link = $result['data']['drugs'][$key][$dose_array[0]];
                }
            }
        }

        return $link;
    }

    /*.
    print page
    */

    protected function page_order($id, $patientId)
    {
        $order = Order::find($id);
        if (! $order) {
            return [];
        }

        $patient = Patient::with(['address', 'user'])->find($patientId);
        if (! $patient) {
            return [];
        }

        $doctor = Doctor::find($order->doctor_id);
        $hospital = $doctor?->hospital ?? Hospital::find(auth()->user()->doctor->hospital_id);

        $data = [];
        if ($order->labs != '') {
            $data['title'] = 'LABORATORY ORDER';
            $data['title1'] = 'LABORATORY PROVIDER';
            $data['title2'] = 'ORDER';
            $data['dx'] = nl2br($order->labs_icd);
            $data['text'] = nl2br($order->labs).'<br><br>'.nl2br($order->labs_obtained);
        }
        if ($order->radiology != '') {
            $data['title'] = 'IMAGING ORDER';
            $data['title1'] = 'IMAGING PROVIDER';
            $data['title2'] = 'ORDER';
            $data['dx'] = nl2br($order->radiology_icd);
            $data['text'] = nl2br($order->radiology);
        }
        if ($order->orders_cp != '') {
            $data['title'] = 'CARDIOPULMONARY ORDER';
            $data['title1'] = 'CARDIOPULMONARY PROVIDER';
            $data['title2'] = 'ORDER';
            $data['dx'] = nl2br($order->cp_icd);
            $data['text'] = nl2br($order->cp);
        }
        if ($order->referrals != '') {
            $data['title'] = 'REFERRAL/GENERAL ORDERS';
            $data['title1'] = 'REFERRAL PROVIDER';
            $data['title2'] = 'DETAILS';
            $data['dx'] = nl2br($order->referrals_icd);
            $data['text'] = nl2br($order->referrals);
        }
        $data['address'] = Lab::where('id', '=', $order->lab_id)->first();

        $data['patientInfo'] = $patient;

        // Fixed: Use strtotime instead of human_to_unix
        $data['dob'] = date('m/d/Y', strtotime($patient->user->dob));
        $data['sex'] = $patient->user->sex;

        $data['practiceName'] = $hospital?->name;
        $data['website'] = $hospital?->website;
        $data['practiceInfo'] = $this->getPracticeInfo($hospital);
        $data['practiceLogo'] = $this->getPracticeLogo($hospital);
        $data['rxicon'] = $this->getRxIcon();

        // This line was duplicated, removed the duplicate
        // $data['dob'] = date('m/d/Y', strtotime($patient->dob));

        $data['orders_date'] = date('m/d/Y', strtotime($order->created_at));
        $data['insuranceInfo'] = nl2br($order->insurance->insurance_plan_name ?? '');
        // $data['allergyInfo'] = $this->getAllergyInfo($patientId);
        $data['signature'] = $this->signature($order->doctor_id);
        $data['top'] = 'Physician Order';
        if ($order->referrals != '') { // Fixed: changed $data['orders'] to $order
            $data['top'] = 'Physician Referral';
        }

        return $data;
    }

    protected function getPracticeInfo($hospital)
    {
        if (! $hospital) {
            return '';
        }
        $address = $hospital->street_address1 ?? '';
        if ($hospital->street_address2) {
            $address .= ' '.$hospital->street_address2;
        }
        $address .= '<br />'.($hospital->city ?? '').', '.($hospital->state ?? '').' '.($hospital->zip ?? '');
        $address .= '<br />Phone: '.($hospital->phone ?? '');

        return $address;
    }

    protected function getPracticeLogo($hospital)
    {
        if (! $hospital || empty($hospital->practice_logo)) {
            return '<br><br><br><br><br>';
        }

        $filePath = public_path($hospital->practice_logo);
        if (file_exists($filePath)) {
            $url = asset($hospital->practice_logo);

            return "<img src=\"{$url}\" height=\"80\" alt=\"Practice Logo\" />";
        }

        return '<br><br><br><br><br>';
    }

    protected function getRxIcon()
    {
        $rxiconUrl = asset('assets/images/rxicon.png');

        return '<img src="'.$rxiconUrl.'" width="30" height="30" border="0" alt="Rx" />';
    }

    protected function getInsuranceInfo($patientId)
    {
        $insurances = Insurance::where('patient_id', $patientId)->get();
        if ($insurances->isEmpty()) {
            return '';
        }

        $info = '';
        foreach ($insurances as $row) {
            $info .= "{$row->insurance_plan_name}; ID: {$row->insurance_id_num}; Group: {$row->insurance_group}; "
                   ."{$row->insurance_insu_lastname}, {$row->insurance_insu_firstname}<br><br>";
        }

        return $info;
    }

    protected function getAllergyInfo($patientId)
    {
        $allergies = Allergy::where('patient_id', $patientId)->whereNull('date_inactive')->get();

        if ($allergies->isEmpty()) {
            return 'No known allergies.';
        }

        $html = '<ul>';
        foreach ($allergies as $row) {
            $html .= "<li>{$row->allergies_medicine}</li>";
        }
        $html .= '</ul>';

        return $html;
    }

    protected function signature($id)
    {
        $user = User::find(auth()->id());
        if (! $user) {
            return '<br><br><br><br><br><br><br>Unknown Provider';
        }

        $doctor = Doctor::where('id', $id)->first();
        $signature = '<br><br><br><br><br><br><br>'.e($user->displayname ?? 'Unknown Provider');

        if ($doctor && ! empty($doctor->doctor_signature)) {
            $signaturePath = public_path(ltrim($doctor->doctor_signature, '/'));

            if (file_exists($signaturePath)) {
                $imgData = base64_encode(file_get_contents($signaturePath));
                $imgSrc = 'data:image/png;base64,'.$imgData;
                $signature = '<img src="'.$imgSrc.'" height="50" border="0" /><br>'.e($user->displayname ?? 'Unknown Provider');
            }
        }

        return $signature;
    }

    protected function page_intro($title, $hospital_id)
    {
        $practice = Hospital::where('id', '=', $hospital_id)->first();
        $data['practiceName'] = $practice->practice_name;
        // $data['LinkedIn'] = property_exists($practice, 'LinkedIn') ? $practice->LinkedIn : '';
        $data['practiceInfo'] = $practice->street_address1;
        if ($practice->street_address2 !== '') {
            $data['practiceInfo'] .= ', '.$practice->street_address2;
        }
        $data['practiceInfo'] .= '<br />';
        $data['practiceInfo'] .= $practice->city.', '.$practice->state.' '.$practice->zip.'<br />';
        $data['practiceInfo'] .= 'Phone: '.$practice->phone.'<br />';
        $data['practiceLogo'] = $this->getPracticeLogo($practice);
        $data['title'] = $title;

        return view('pdf.intro', $data);
    }

    public function generate_pdf($html, $file_path = null)
    {

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true);

        $dompdf = $pdf->getDomPDF();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('chroot', public_path());
        $pdf->setBasePath(public_path());

        // Add footer BEFORE rendering
        $canvas = $dompdf->get_canvas();
        $canvasWidth = $canvas->get_width();
        $canvasHeight = $canvas->get_height();
        $footerY = $canvasHeight - 100;

        $footer_text_1 = 'CONFIDENTIALITY NOTICE: The information contained in this document or facsimile transmission is intended for the recipient named above...';
        $footer_text_2 = 'This document was generated by AKRA TELEHEALTH.';

        $font = 'Helvetica';
        $font_size = 8;
        $line_spacing = 12;

        // Add page numbers
        $canvas->page_text(
            $canvasWidth - 100,
            $canvasHeight - 30,
            'Page {PAGE_NUM} of {PAGE_COUNT}',
            'Helvetica',
            8,
            [0, 0, 0]
        );

        $dompdf->render();

        // Now add custom footer text
        $paragraphY = $footerY + 18;
        foreach (explode("\n", wordwrap($footer_text_1, 150)) as $line) {
            $canvas->text(($canvasWidth - $canvas->get_text_width($line, $font, $font_size)) / 2, $paragraphY, $line, $font, $font_size);
            $paragraphY += $line_spacing;
        }

        $canvas->text(
            ($canvasWidth - $canvas->get_text_width($footer_text_2, $font, $font_size)) / 2,
            $paragraphY + 5,
            $footer_text_2,
            $font,
            $font_size
        );

        // Save after all content is added
        file_put_contents($file_path, $dompdf->output());
    }

    protected function array_vitals()
    {
        $practice = DB::table('practiceinfo')->where('practice_id', '=', Session::get('practice_id'))->first();
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

    protected function array_vitals1()
    {
        $return = [
            'wt_percentile' => 'Weight to Age Percentile',
            'ht_percentile' => 'Height to Age Percentile',
            'wt_ht_percentile' => 'Weight to Height Percentile',
            'hc_percentile' => 'Head Circumference to Age Percentile',
            'bmi_percentile' => 'BMI to Age Percentile',
        ];

        return $return;
    }

    protected function array_plan()
    {
        $return = [
            'plan' => 'Recommendations',
            'followup' => 'Followup',
            'goals' => 'Goals/Measures',
            'tp' => 'Treatment Plan Notes',
            'duration' => 'Counseling and face-to-face time consists of more than 50 percent of the visit.  Total face-to-face time is ',
        ];

        return $return;
    }

    public function billing_save_common($encounter_id, $billing_core) {}
}

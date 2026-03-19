<?php

namespace App\Traits;

trait SMSTrait
{
    /**
     * sendOTP
     *
     * @param  mixed  $mobile
     * @return void
     */
    public function sendOTP($mobile)
    {
        $otp = rand(1000, 9999);

        return $otp;
    }

    /**
     * sendSMS
     *
     * @param  mixed  $mobile
     * @param  mixed  $template
     * @param  mixed  $payload
     * @return void
     */
    public function sendSMS($mobile, $template, $payload)
    {
        return true;
    }

    /**
     * smsTemplate
     *
     * @param  mixed  $name
     */
    public function smsTemplate($name): string
    {
        $templates = [
            'Order_Out_for_Delivery' => '68a5a504ded062424f118b74',
            'Refund_Issued' => '68a5a4deb30a5f29582a5305',
            'Patient_OTP_Login' => '68a5a4b4461e7349cf6bf344',
            'Delivery_OTP' => '68a5a48d7d5a6622f7497483',
            'KYC_Rejected' => '68a5a471bd1ebe20434f89f3',
            'Subscription_Payment_Received' => '68a5a4292093fa32a92b1575',
            'Appointment_Booked' => '68a5a4292093fa32a92b1575',
            'Prescription_Shared' => '68a5a3ea6a329429fb686273',
            'Follow-Up_Reminder' => '68a5a3ce23c45422ba175343',
            'Emergency_Case_Follow-up' => '68a5a3a1a98e185d5f7274e3',
            'Payment_Link' => '68a5a3725c85ca1e4d252aa4',
            'Reschedule_Confirmation' => '68a5a3312307f317ac4d9662',
            'KYC_Approved' => '68a5a2f147b2c617fd2db4b4',
            'Subscription_Expired' => '68a5a2d1d4500f20da50b253',
            'Lab_Test_Scheduled' => '68a5a2ba2afaea4c1f7126a2',
            'Missed_Delivery_Attempt' => '68a5a2884952c153f31c67d3',
            'Delivery_Arrived' => '68a5a26822da657b3e4f23c2',
            'Partner_OTP_Login' => '68a5a23cbc9e3e241f240c92',
            'Sample_Pickup_Failed' => '68a5a21ef0eca84c397bb682',
            'Payment_Received' => '68a5a1e13c3104634e3ed262',
            'Subscription_Reminder' => '68a5a1a609c204631a15b9c2',
            'Delivery_OTP_for_Customer' => '68a5a0e96ea9ac5e7b702a42',
            'Appointment_Reminder' => '68a5a0ba9e6d8d200f396d22',
            'New_Task_Assigned' => '68a5a07d68042166116f3802',
            'Appointment_Cancelled' => '68a5a062461abb66b32c4ec2',
            'Sample_Collection_On_the_Way' => '68a5a0304346a066b04d19e6',
            'Lab_Report_Ready' => '68a5a0150cd0810b03702383',
            'Pickup_Scheduled' => 'Pickup_Scheduled',
            'Medicine_Order_Placed' => '68a59fcd7e1179346f64e332',
            'Patient_OTP_Login' => '68a59f990f57c55fac10e543',
            'Sample_Collection_Reminder' => '68a59f7061d7dd297000689a',
        ];

        return $templates[$name] ?? '';
    }
}

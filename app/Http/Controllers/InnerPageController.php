<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Inertia\Inertia;
use Inertia\Response;

class InnerPageController extends Controller
{
    /**
     * emr
     */
    public function emr(): Response
    {
        return Inertia::render('Inner/Emr');
    }

    public function ai(): Response
    {
        return Inertia::render('Inner/Ai');
    }

    public function dental(): Response
    {
        return Inertia::render('Inner/Dental');
    }

    public function EmrIntegration(): Response
    {
        return Inertia::render('Inner/EmrIntegration');
    }

    public function ApiIntegration(): Response
    {
        return Inertia::render('Inner/ApiIntegration');
    }

    public function StandardIntegrations(): Response
    {
        return Inertia::render('Inner/StandardIntegrations');
    }

    public function BillingIntegrations(): Response
    {
        return Inertia::render('Inner/BillingIntegrations');
    }

    public function MentalHealth(): Response
    {
        return Inertia::render('Inner/MentalHealth');
    }

    public function PracticeManagement(): Response
    {
        return Inertia::render('Inner/PracticeManagement');
    }

    public function ClinicalManagement(): Response
    {
        return Inertia::render('Inner/ClinicalManagement');
    }

    public function MedicalBilling(): Response
    {
        return Inertia::render('Inner/MedicalBilling');
    }

    public function LabImaging(): Response
    {
        return Inertia::render('Inner/LabImaging');
    }

    public function PatientPortal(): Response
    {
        return Inertia::render('Inner/PatientPortal');
    }

    public function Erx(): Response
    {
        return Inertia::render('Inner/Erx');
    }

    public function Telemedicine(): Response
    {
        return Inertia::render('Inner/Telemedicine');
    }

    public function HireUs(): Response
    {
        return Inertia::render('Inner/HireUs');
    }

    public function DoctorOnCall(): Response
    {
        return Inertia::render('Inner/DoctorOnCall');
    }

    public function ClinicManagement(): Response
    {
        return Inertia::render('Inner/ClinicManagement');
    }

    public function PatientManagement(): Response
    {
        return Inertia::render('Inner/PatientManagement');
    }

    public function MedicationAdministrationRecords(): Response
    {
        return Inertia::render('Inner/MedicationAdministrationRecords');
    }

    public function MobileIV(): Response
    {
        return Inertia::render('Inner/MobileIV');
    }

    public function NursesOnCall(): Response
    {
        return Inertia::render('Inner/NursesOnCall');
    }

    /**
     * integration
     */
    public function integration(): Response
    {
        return Inertia::render('Inner/Integration');
    }

    /**
     * security
     */
    public function security(): Response
    {
        return Inertia::render('Inner/Security');
    }

    /**
     * pharmacyApp
     */
    public function pharmacyApp(): Response
    {
        return Inertia::render('Inner/PharmacyApp');
    }

    /**
     * emrApp
     */
    public function emrApp(): Response
    {
        return Inertia::render('Inner/EmrApp');
    }

    /**
     * pricing
     */
    public function pricing(): Response
    {
        // Get user's location (you can use a service like GeoIP or get from user profile)
        $currency = $this->getUserLocation();

        // Get all active plans
        $plans = SubscriptionPlan::where('status', true)
            ->where('currency', $currency['currency'] ?? 'USD')
            ->get();

        return Inertia::render('Inner/Pricing', [
            'plans' => $plans,
        ]);
    }

    public function enquireDemo(): Response
    {
        return Inertia::render('Inner/EnquireDemo');
    }

    /**
     * contactus
     */
    public function contactUs(): Response
    {
        return Inertia::render('Inner/ContactUs');
    }

    public function outreachProgram(): Response
    {
        return Inertia::render('Inner/OutreachProgram');
    }

    public function Training(): Response
    {
        return Inertia::render('Inner/Training');
    }

    public function UseCases(): Response
    {
        return Inertia::render('Inner/UseCases');
    }

    public function Prescription(): Response
    {
        return Inertia::render('Inner/Prescription');
    }

    public function aiPatientIntake(): Response
    {
        return Inertia::render('Inner/AiPatientIntake');
    }

    public function aiMedicalScribe(): Response
    {
        return Inertia::render('Inner/AiMedicalScribe');
    }
}

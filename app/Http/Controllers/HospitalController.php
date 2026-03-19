<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Services\HospitalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HospitalController extends Controller
{
    protected $hospitalService;

    public function __construct(HospitalService $hospitalService)
    {
        $this->hospitalService = $hospitalService;
    }

    /**
     * Store a newly created hospital from modal
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'street_address1' => 'nullable|string',
            'street_address2' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'website' => 'nullable|url',
            'primary_contact' => 'nullable|string|max:100',
            'npi' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:20',
            'about' => 'nullable|string',
            'weight_unit' => 'nullable|in:kg,lbs',
            'height_unit' => 'nullable|in:cm,in',
            'temp_unit' => 'nullable|in:c,f',
            'timings' => 'nullable|array',
            'timings.*.day_of_week' => 'required_with:timings|string',
            'timings.*.is_closed' => 'boolean',
            'timings.*.open_time' => 'nullable|date_format:H:i',
            'timings.*.close_time' => 'nullable|date_format:H:i',
        ]);

        try {
            $this->hospitalService->saveHospital($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Hospital created successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create hospital. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get data needed for the add hospital modal
     */
    public function getModalData()
    {
        $states = Schema::hasTable('states') ? DB::table('states')->orderBy('name')->get() : collect();
        $countries = Schema::hasTable('countries') ? DB::table('countries')->orderBy('name')->get() : collect();

        return response()->json([
            'states' => $states,
            'countries' => $countries,
            'weight_units' => [
                ['value' => 'kg', 'label' => 'Kilograms (kg)'],
                ['value' => 'lbs', 'label' => 'Pounds (lbs)'],
            ],
            'height_units' => [
                ['value' => 'cm', 'label' => 'Centimeters (cm)'],
                ['value' => 'in', 'label' => 'Inches (in)'],
            ],
            'temp_units' => [
                ['value' => 'c', 'label' => 'Celsius (°C)'],
                ['value' => 'f', 'label' => 'Fahrenheit (°F)'],
            ],
            'days_of_week' => [
                ['value' => 'monday', 'label' => 'Monday'],
                ['value' => 'tuesday', 'label' => 'Tuesday'],
                ['value' => 'wednesday', 'label' => 'Wednesday'],
                ['value' => 'thursday', 'label' => 'Thursday'],
                ['value' => 'friday', 'label' => 'Friday'],
                ['value' => 'saturday', 'label' => 'Saturday'],
                ['value' => 'sunday', 'label' => 'Sunday'],
            ],
        ]);
    }

    /**
     * Update an existing hospital
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'street_address1' => 'nullable|string',
            'street_address2' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'website' => 'nullable|url',
            'primary_contact' => 'nullable|string|max:100',
            'npi' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:20',
            'about' => 'nullable|string',
            'weight_unit' => 'nullable|in:kg,lbs',
            'height_unit' => 'nullable|in:cm,in',
            'temp_unit' => 'nullable|in:c,f',
            'timings' => 'nullable|array',
            'timings.*.day_of_week' => 'required_with:timings|string',
            'timings.*.is_closed' => 'boolean',
            'timings.*.open_time' => 'nullable|date_format:H:i',
            'timings.*.close_time' => 'nullable|date_format:H:i',
        ]);

        try {
            $input = $request->all();
            $input['id'] = $id; // Set the ID for update

            // Reuse the same upsert logic used for create
            $this->hospitalService->saveHospital($input);

            return response()->json([
                'success' => true,
                'message' => 'Hospital updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update hospital. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

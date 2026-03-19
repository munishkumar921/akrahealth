<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VaccinesRequest;
use App\Models\Hospital;
use App\Models\Vaccine;
use App\Models\VaccineTemperature;
use App\Services\VaccineService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminVaccineController extends Controller
{
    protected $vaccineService;

    public function __construct(VaccineService $vaccineService)
    {
        $this->vaccineService = $vaccineService;
    }

    /**
     * Search vaccines via AJAX
     */
    public function search(Request $request)
    {
        $q = trim($request->input('search_vaccine', ''));

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

        $query = Vaccine::where('hospital_id', auth()->user()->hospital->id);

        if (count($keywords) === 1) {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('immunization', 'LIKE', "%{$q}%")
                    ->orWhere('brand', 'LIKE', "%{$q}%")
                    ->orWhere('manufacturer', 'LIKE', "%{$q}%")
                    ->orWhere('lot', 'LIKE', "%{$q}%")
                    ->orWhere('cpt', 'LIKE', "%{$q}%")
                    ->orWhere('code', 'LIKE', "%{$q}%");
            });
        } else {
            $query->where(function ($qBuilder) use ($keywords) {
                foreach ($keywords as $word) {
                    $qBuilder->where(function ($qInner) use ($word) {
                        $qInner->where('immunization', 'LIKE', "%{$word}%")
                            ->orWhere('brand', 'LIKE', "%{$word}%")
                            ->orWhere('manufacturer', 'LIKE', "%{$word}%")
                            ->orWhere('lot', 'LIKE', "%{$word}%")
                            ->orWhere('cpt', 'LIKE', "%{$word}%")
                            ->orWhere('code', 'LIKE', "%{$word}%");
                    });
                }
            });
        }

        $vaccines = $query->limit(30)->get();

        if ($vaccines->isNotEmpty()) {
            $data['response'] = 'li';
            foreach ($vaccines as $vaccine) {
                $data['message'][] = [
                    'id' => $vaccine->id,
                    'label' => $vaccine->immunization.' ['.$vaccine->brand.']',
                    'value' => $vaccine->immunization,
                    'immunization' => $vaccine->immunization,
                    'brand' => $vaccine->brand,
                    'manufacturer' => $vaccine->manufacturer,
                    'lot' => $vaccine->lot,
                    'expiration_date' => $vaccine->expiration_date,
                    'cpt' => $vaccine->cpt,
                    'code' => $vaccine->code,
                    'quantity' => $vaccine->quantity,
                    'date_purchase' => $vaccine->date_purchase,
                ];
            }
        }

        return response()->json($data);
    }

    /**
     * Display a listing of vaccines.
     */
    public function index(Request $request)
    {
        $vaccines = Vaccine::where('hospital_id', auth()->user()->hospital->id)->when($request->search, function ($query, $search) {
            $query->where('immunization', 'like', "%{$search}%")
                ->orWhere('manufacturer', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%");
        })->orderBy('created_at', 'desc')->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Admin/Inventory/Vaccines', [
            'vaccines' => $vaccines,
            'keyword' => $request->get('keyword') ?? '',
        ]);
    }

    /**
     * Store a newly created vaccine.
     */
    public function store(VaccinesRequest $request)
    {
        $input = $request->all();

        $this->vaccineService->upsert($input);

        return redirect()->back()->with('success', 'Vaccine added successfully.');
    }

    /**
     * Remove the specified vaccine.
     */
    public function destroy(string $id)
    {
        $vaccine = Vaccine::findOrFail($id);
        $vaccine->delete();

        return redirect()->back()->with('success', 'Vaccine deleted successfully.');
    }

    public function temperatureIndex(Request $request)
    {
        // Get the authenticated user's hospital
        $hospitalId = auth()->user()->id;
        $hospital = Hospital::where('user_id', $hospitalId)->first();

        $temperatures = VaccineTemperature::where('hospital_id', auth()->user()->hospital->id)->when($request->search, function ($query, $search) {
            $query->where('temperature', 'like', "%{$search}%");
        })
            ->where('hospital_id', $hospital->id)
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        return Inertia::render('Admin/Inventory/VaccineTemperatures', [
            'temperatures' => $temperatures,
            'keyword' => $request->get('keyword') ?? '',
        ]);
    }

    public function temperatureStore(Request $request)
    {
        // Validate input before calling the service
        $validated = $request->validate([
            'id' => 'nullable',
            'temperature' => 'required|numeric',
            'date' => 'required|date',
            'time' => 'nullable',
            'action' => 'nullable',
        ]);

        $this->vaccineService->upsertTemperature($validated);

        return redirect()->back()->with('success', 'Vaccine temperature added successfully.');
    }

    public function temperatureDestroy(string $id)
    {
        $temperature = VaccineTemperature::findOrFail($id);

        $temperature->delete();

        return redirect()->back()->with('success', 'Vaccine temperature deleted successfully.');
    }
}

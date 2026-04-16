<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VaccinesRequest;
use App\Models\Hospital;
use App\Models\Vaccine;
use App\Models\VaccineTemperature;
use App\Services\VaccineService;
use Carbon\Carbon;
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
                    'label' => $vaccine->immunization . ' [' . $vaccine->brand . ']',
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
        $keyword = $request->get('keyword', '');
        $stockStatus = $request->get('stock_status', '');
        $expiryStatus = $request->get('expiry_status', '');
        $today = Carbon::today();
        $expiringSoonDate = $today->copy()->addDays(30);

        $vaccines = Vaccine::where('hospital_id', auth()->user()->hospital->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($vaccine) use ($today, $expiringSoonDate) {
                $expirationDate = $vaccine->getRawOriginal('expiration')
                    ? Carbon::parse($vaccine->getRawOriginal('expiration'))
                    : null;

                $stockStatus = (int) $vaccine->quantity > 0 ? 'in_stock' : 'out_of_stock';
                $expiryStatus = 'valid';

                if ($expirationDate && $expirationDate->lt($today)) {
                    $expiryStatus = 'expired';
                } elseif ($expirationDate && $expirationDate->between($today, $expiringSoonDate)) {
                    $expiryStatus = 'expiring_soon';
                }

                return [
                    'id' => $vaccine->id,
                    'date_purchase' => $vaccine->date_purchase,
                    'immunization' => $vaccine->immunization,
                    'brand' => $vaccine->brand,
                    'lot' => $vaccine->lot,
                    'manufacturer' => $vaccine->manufacturer,
                    'expiration' => $vaccine->expiration,
                    'cpt' => $vaccine->cpt,
                    'code' => $vaccine->code,
                    'quantity' => $vaccine->quantity,
                    'stock_status' => $stockStatus,
                    'stock_status_label' => $stockStatus === 'in_stock' ? 'In Stock' : 'Out of Stock',
                    'expiry_status' => $expiryStatus,
                    'expiry_status_label' => match ($expiryStatus) {
                        'expired' => 'Expired',
                        'expiring_soon' => 'Expiring Soon',
                        default => 'Valid',
                    },
                ];
            });

        if ($stockStatus) {
            $vaccines = $vaccines->filter(fn ($vaccine) => $vaccine['stock_status'] === $stockStatus)->values();
        }

        if ($expiryStatus) {
            $vaccines = $vaccines->filter(fn ($vaccine) => $vaccine['expiry_status'] === $expiryStatus)->values();
        }

        if ($keyword) {
            $needle = str($keyword)->lower()->value();
            $vaccines = $vaccines->filter(function ($vaccine) use ($needle) {
                $haystack = str(implode(' ', array_filter([
                    $vaccine['date_purchase'] ?? null,
                    $vaccine['immunization'] ?? null,
                    $vaccine['brand'] ?? null,
                    $vaccine['lot'] ?? null,
                    $vaccine['manufacturer'] ?? null,
                    $vaccine['expiration'] ?? null,
                    $vaccine['cpt'] ?? null,
                    $vaccine['code'] ?? null,
                    $vaccine['quantity'] ?? null,
                    $vaccine['stock_status_label'] ?? null,
                    $vaccine['expiry_status_label'] ?? null,
                ])))->lower()->value();

                return str($haystack)->contains($needle);
            })->values();
        }

        $perPage = (int) $request->input('per_page', paginateLimit());
        $currentPage = $request->integer('page', 1);
        $vaccines = new \Illuminate\Pagination\LengthAwarePaginator(
            $vaccines->forPage($currentPage, $perPage)->values(),
            $vaccines->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Admin/Inventory/Vaccines', [
            'vaccines' => $vaccines,
            'filters' => [
                'keyword' => $keyword,
                'stock_status' => $stockStatus,
                'expiry_status' => $expiryStatus,
            ],
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
        $keyword = $request->get('keyword', '');
        $action = $request->get('action', '');
        $baseQuery = VaccineTemperature::where('hospital_id', auth()->user()->hospital->id)
            ->where('hospital_id', $hospital->id);

        $actionOptions = (clone $baseQuery)
            ->whereNotNull('action')
            ->distinct()
            ->pluck('action')
            ->filter()
            ->values();

        $temperatures = (clone $baseQuery)->orderByDesc('created_at')->get()->map(function ($temperature) {
            return [
                'id' => $temperature->id,
                'date' => $temperature->date,
                'time' => $temperature->time,
                'temperature' => $temperature->temperature,
                'action' => $temperature->action,
            ];
        });

        if ($action) {
            $temperatures = $temperatures
                ->filter(fn ($temperature) => strcasecmp((string) $temperature['action'], (string) $action) === 0)
                ->values();
        }

        if ($keyword) {
            $needle = str($keyword)->lower()->value();
            $temperatures = $temperatures->filter(function ($temperature) use ($needle) {
                $haystack = str(implode(' ', array_filter([
                    $temperature['date'] ?? null,
                    $temperature['time'] ?? null,
                    $temperature['temperature'] ?? null,
                    $temperature['action'] ?? null,
                ])))->lower()->value();

                return str($haystack)->contains($needle);
            })->values();
        }

        $perPage = (int) $request->input('per_page', paginateLimit());
        $currentPage = $request->integer('page', 1);
        $temperatures = new \Illuminate\Pagination\LengthAwarePaginator(
            $temperatures->forPage($currentPage, $perPage)->values(),
            $temperatures->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Admin/Inventory/VaccineTemperatures', [
            'temperatures' => $temperatures,
            'filters' => [
                'keyword' => $keyword,
                'action' => $action,
            ],
            'actionOptions' => $actionOptions,
        ]);
    }

    public function temperatureStore(Request $request)
    {
        // Validate input before calling the service
        $validated = $request->validate([
            'id' => 'nullable',
            'temperature' => 'required|numeric',
            'date' => 'required|date',
            'time' => 'required',
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

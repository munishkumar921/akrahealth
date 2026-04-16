<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplementRequest;
use App\Models\Supplement;
use App\Services\SupplementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminSupplementController extends Controller
{
    protected $supplementService;

    public function __construct(SupplementService $supplementService)
    {
        $this->supplementService = $supplementService;
    }

    /**
     * Display a listing of supplements.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $stockStatus = $request->get('stock_status', '');
        $expiryStatus = $request->get('expiry_status', '');
        $today = Carbon::today();
        $expiringSoonDate = $today->copy()->addDays(30);

        $supplements = Supplement::where('hospital_id', auth()->user()->doctor->hospital_id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($supplement) use ($today, $expiringSoonDate) {
                $expirationDate = $supplement->getRawOriginal('expiration')
                    ? Carbon::parse($supplement->getRawOriginal('expiration'))
                    : null;

                $stockStatus = (int) $supplement->quantity > 0 ? 'in_stock' : 'out_of_stock';
                $expiryStatus = 'valid';

                if ($expirationDate && $expirationDate->lt($today)) {
                    $expiryStatus = 'expired';
                } elseif ($expirationDate && $expirationDate->between($today, $expiringSoonDate)) {
                    $expiryStatus = 'expiring_soon';
                }

                return [
                    'id' => $supplement->id,
                    'purchase_date' => Carbon::parse($supplement->purchase_date)->format('d M, Y'),
                    'description' => $supplement->description,
                    'strength' => $supplement->strength,
                    'manufacturer' => $supplement->manufacturer,
                    'expiration' => Carbon::parse($supplement->expiration)->format('d M, Y'),
                    'cpt' => $supplement->cpt,
                    'charge' => $supplement->charge,
                    'quantity' => $supplement->quantity,
                    'sup_lot' => $supplement->sup_lot,
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
            $supplements = $supplements->filter(fn ($supplement) => $supplement['stock_status'] === $stockStatus)->values();
        }

        if ($expiryStatus) {
            $supplements = $supplements->filter(fn ($supplement) => $supplement['expiry_status'] === $expiryStatus)->values();
        }

        if ($keyword) {
            $needle = str($keyword)->lower()->value();
            $supplements = $supplements->filter(function ($supplement) use ($needle) {
                $haystack = str(implode(' ', array_filter([
                    $supplement['purchase_date'] ?? null,
                    $supplement['description'] ?? null,
                    $supplement['strength'] ?? null,
                    $supplement['manufacturer'] ?? null,
                    $supplement['expiration'] ?? null,
                    $supplement['cpt'] ?? null,
                    $supplement['charge'] ?? null,
                    $supplement['quantity'] ?? null,
                    $supplement['sup_lot'] ?? null,
                    $supplement['stock_status_label'] ?? null,
                    $supplement['expiry_status_label'] ?? null,
                ])))->lower()->value();

                return str($haystack)->contains($needle);
            })->values();
        }

        $perPage = (int) $request->input('per_page', paginateLimit());
        $currentPage = $request->integer('page', 1);
        $supplements = new \Illuminate\Pagination\LengthAwarePaginator(
            $supplements->forPage($currentPage, $perPage)->values(),
            $supplements->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Admin/Inventory/Supplements', [
            'supplements' => $supplements,
            'filters' => [
                'keyword' => $keyword,
                'stock_status' => $stockStatus,
                'expiry_status' => $expiryStatus,
            ],
        ]);
    }

    /**
     * Store a newly created supplement.
     */
    public function store(SupplementRequest $request)
    {
        $input = $request->all();

        $this->supplementService->upsert($input);

        return redirect()->route('admin.supplements.index')->with('success', 'Supplement added successfully.');
    }

    /**
     * Remove the specified supplement.
     */
    public function destroy(string $id)
    {
        $this->supplementService->destroy($id);

        return redirect()->route('admin.supplements.index')->with('success', 'Supplement deleted successfully.');
    }
}

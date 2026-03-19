<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplementRequest;
use App\Models\Supplement;
use App\Services\SupplementService;
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
        $supplements = Supplement::when($request->search, function ($query, $search) {
            $query->where('description', 'like', "%{$search}%")
                ->orWhere('manufacturer', 'like', "%{$search}%");
        })->orderBy('created_at', 'desc')->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Admin/Inventory/Supplements', [
            'supplements' => $supplements,
            'keyword' => $request->get('keyword') ?? '',
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

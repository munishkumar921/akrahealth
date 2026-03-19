<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HospitalRequest;
use App\Models\Hospital;
use App\Models\HospitalTiming;
use App\Services\HospitalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AHospitalController extends Controller
{
    /**
     * Display a listing of hospitals (practice locations).
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $hospitals = Hospital::orderBy('name', 'asc')->where('user_id', auth()->user()->id);
        if ($request->has('search')) {
            $keyword = $request->get('search');
            $hospitals = $hospitals->where('name', 'Like', '%'.$keyword.'%')
                ->orWhere('address', 'Like', '%'.$keyword.'%')
                ->orWhere('city', 'Like', '%'.$keyword.'%')
                ->orWhere('state', 'Like', '%'.$keyword.'%')
                ->orWhere('zip_code', 'Like', '%'.$keyword.'%');
        }
        $hospitals = $hospitals->paginate(request('per_page', paginateLimit()))->withQueryString();

        return inertia('Admin/Practice/Index', [
            'hospitals' => $hospitals,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Create a new hospital.
     */
    public function create()
    {
        $states = Schema::hasTable('states') ? DB::table('states')->orderBy('name')->get() : collect();
        $countries = Schema::hasTable('countries') ? DB::table('countries')->orderBy('name')->get() : collect();
        $hospitals = Hospital::whereNull('main_branch_id')->get();

        return inertia('Admin/Practice/Create', [
            'states' => $states,
            'countries' => $countries,
            'hospitals' => $hospitals,
        ]);
    }

    /**
     * Store a newly created hospital in storage.
     */
    public function store(HospitalRequest $request, HospitalService $obj)
    {
        $input = $request->all();
        if ($input['main_branch_id'] == null) {
            $user = auth()->user();
        } else {
            $user = null;
        }
        $obj->saveHospital($input, $user);

        return back()->with('success', 'Practice created successfully.');
    }

    /**
     * Display the specified hospital with timings.
     */
    public function show(string $id)
    {
        $hospital = Hospital::with('timings')->findOrFail($id);

        return response()->json($hospital);
    }

    /**
     * Remove the specified hospital from storage.
     */
    public function destroy(string $id)
    {
        Hospital::where('id', $id)->delete();

        return Redirect::back()->with('success', 'Practice deleted successfully.');
    }

    /**
     * Branch list a particular practice.
     */
    public function branchList()
    {
        $hospital = Hospital::where('user_id', auth()->user()->id)->first();
        $branches = Hospital::where('main_branch_id', $hospital->id)->get();

        return inertia('Admin/Practice/BranchList', [
            'hospital' => $hospital,
            'branches' => $branches,
        ]);
    }

    public function hospitalTiming(Request $request)
    {
        $userId = auth()->id();

        // Get user's hospital
        $hospital = Hospital::where('user_id', $userId)->first();

        // Handle null hospital - return early with empty data
        if (! $hospital) {
            return Inertia::render('Admin/HospitalTimeing/Index', [
                'hospitalTime' => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'per_page' => 10, 'total' => 0],
                'keyword' => '',
            ]);
        }

        // Initialize keyword with default empty string
        $keyword = '';

        // Handle GET request - show the page with existing data
        $hospitalTime = HospitalTiming::with('hospital')->where('hospital_id', $hospital->id);
        if ($request->has('search')) {
            $keyword = $request->get('search');
            $hospitalTime = $hospitalTime->where(function ($query) use ($keyword) {
                $query->where('day_of_week', 'Like', '%'.$keyword.'%')
                    ->orWhere('open_time', 'Like', '%'.$keyword.'%')
                    ->orWhere('close_time', 'Like', '%'.$keyword.'%');
            });

        }
        $hospitalTime = $hospitalTime->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Admin/HospitalTimeing/Index', [
            'hospitalTime' => $hospitalTime,
            'keyword' => $keyword,
        ]);
    }

    public function hospitalTimingStore(Request $request)
    {

        $userId = auth()->id();

        // Get user's hospital
        $hospital = Hospital::where('user_id', $userId)->first();

        if (! $hospital) {
            return back()->with('error', 'Hospital not found.');
        }

        $validated = $request->validate([
            'weekends' => 'nullable|boolean',
            'time_zone' => 'nullable|string',
            'default_open_time' => 'nullable|string',
            'default_close_time' => 'nullable|string',
            'day_of_week' => 'required_with:timings|string',
            'open_time' => 'nullable',
            'close_time' => 'nullable',
        ]);

        // Convert time values to 24-hour format for MySQL TIME column
        $convertTime = function ($time) {
            if (empty($time)) {
                return null;
            }
            try {
                return \Carbon\Carbon::createFromFormat('h:i:s A', $time)->format('H:i:s');
            } catch (\Exception $e) {
                return $time; // Return as is if parsing fails
            }
        };
        HospitalTiming::updateOrCreate(
            ['id' => $request->input('id') ?? null],  // Condition
            [
                'hospital_id' => $hospital->id,
                'weekends' => $validated['weekends'] ?? null,
                'time_zone' => $validated['time_zone'] ?? null,
                'default_open_time' => $convertTime($validated['default_open_time']),
                'default_close_time' => $convertTime($validated['default_close_time']),
                'day_of_week' => $validated['day_of_week'] ?? null,
                'open_time' => $convertTime($validated['open_time']),
                'close_time' => $convertTime($validated['close_time']),
            ]
        );

        return back()->with('success', 'Schedule updated successfully.');
    }

    public function hospitalTimingDestroy(string $id)
    {
        // Find the hospital timing record
        $hospitalTiming = HospitalTiming::find($id);

        // Verify it exists
        if (! $hospitalTiming) {
            return back()->with('error', 'Schedule not found.');
        }

        // Verify the hospital belongs to the current user
        $userHospital = Hospital::where('user_id', auth()->id())->first();
        if (! $userHospital || $hospitalTiming->hospital_id !== $userHospital->id) {
            return back()->with('error', 'Unauthorized to delete this schedule.');
        }

        $hospitalTiming->delete();

        return back()->with('success', 'Schedule deleted successfully.');
    }

    public function getBranches()
    {
        $user = auth()->user();

        // Find the main hospital for this user
        $hospital = Hospital::where('user_id', $user->id)->first();

        // If no hospital is found, return an empty array
        if (! $hospital) {
            return response()->json([]);
        }

        // Return the main hospital plus all its branches
        $branches = Hospital::where('id', $hospital->id)
            ->orWhere('main_branch_id', $hospital->id)
            ->orderBy('name')
            ->get();

        return response()->json($branches);
    }
}

<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Models\LaboratoryTest;
use App\Models\Order;
use App\Models\user;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LabController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $labid = auth()->user()->lab->id ?? null;
        $labs = Order::with('patient', 'doctor.hospital')->where('lab_id', $labid);
        $keyword = null;
        if ($request->has('keyword')) {

            $keyword = $request->get('keyword');
            $labs = $labs->where('lab', 'Like', '%'.$keyword.'%');
        }
        $labs = $labs->orderBy('id', 'DESC')->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Lab/LaboratoryList', [
            'labs' => $labs,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $lab = User::with('address')->where('id', $id)->first();
        $test = LaboratoryTest::where('user_id', $lab->id)->get();

        return Inertia::render('Lab/LaboratoryDetail', [
            'lab' => $lab,
            'tests' => $test,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Dashboard
     */
    public function dashboard(Request $request)
    {
        $labId = auth()->user()->lab->id ?? null;
        $keyword = $request->get('keyword');

        // Total Tests
        $totalTests = \App\Models\LaboratoryTest::where('user_id', $labId)->count();

        // Total Patients (unique patients from orders)
        $totalPatients = \App\Models\Order::where('lab_id', $labId)
            ->distinct('patient_id')
            ->count('patient_id');

        // Recent Orders with search
        $recentOrdersQuery = \App\Models\Order::with('patient', 'doctor')
            ->where('lab_id', $labId);

        if ($keyword) {
            $recentOrdersQuery->where(function ($q) use ($keyword) {
                $q->where('lab', 'Like', '%'.$keyword.'%')
                    ->orWhereHas('patient', function ($pq) use ($keyword) {
                        $pq->where('name', 'Like', '%'.$keyword.'%');
                    })
                    ->orWhereHas('doctor', function ($dq) use ($keyword) {
                        $dq->where('name', 'Like', '%'.$keyword.'%');
                    });
            });
        }

        $recentOrders = $recentOrdersQuery->orderBy('created_at', 'DESC')->paginate(10);

        return Inertia::render('Lab/Dashboard', [
            'totalTests' => $totalTests,
            'totalPatients' => $totalPatients,
            'recentOrders' => $recentOrders,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Lab Tests
     */
    public function tests(Request $request)
    {
        $labId = auth()->user()->lab->id ?? null;
        $tests = LaboratoryTest::with('user')->where('user_id', $labId);
        $keyword = null;

        if ($request->has('keyword')) {
            $keyword = $request->get('keyword');
            $tests = $tests->where('test_name', 'Like', '%'.$keyword.'%');
        }

        $tests = $tests->orderBy('id', 'DESC')->paginate($this->paginate);

        return Inertia::render('Lab/LabTests', [
            'tests' => $tests,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Lab Reports
     */
    public function reports(Request $request)
    {
        $labId = auth()->user()->lab->id ?? null;
        $reports = Order::with('patient', 'doctor')
            ->where('lab_id', $labId)
            ->select('orders.*');

        $keyword = null;
        if ($request->has('keyword')) {
            $keyword = $request->get('keyword');
            $reports = $reports->where('lab', 'Like', '%'.$keyword.'%');
        }

        $reports = $reports->orderBy('id', 'DESC')->paginate($this->paginate);

        return Inertia::render('Lab/LabReports', [
            'reports' => $reports,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Transactions
     */
    public function transactions(Request $request)
    {
        $labId = auth()->user()->lab->id ?? null;
        $transactions = Order::with('patient', 'doctor')
            ->where('lab_id', $labId)
            ->select('orders.*');

        $keyword = null;
        if ($request->has('keyword')) {
            $keyword = $request->get('keyword');
            $transactions = $transactions->where('lab', 'Like', '%'.$keyword.'%');
        }

        $transactions = $transactions->orderBy('id', 'DESC')->paginate($this->paginate);

        return Inertia::render('Lab/Transactions', [
            'transactions' => $transactions,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Messages
     */
    public function messages(Request $request)
    {
        return Inertia::render('Lab/Messages', [
            // Add messages data as needed
        ]);
    }
}

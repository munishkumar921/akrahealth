<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\PharmacyOrder;
use App\Models\Prescription;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PharmacyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $pharmacy = Pharmacy::where('user_id', $user->id)->first();

        if (! $pharmacy) {
            return Inertia::render('Pharmacy/PharmacyList', [
                'pharmacy' => null,
                'keyword' => '',
            ]);
        }

        $pharmacies = Pharmacy::with('user')
            ->where('id', $pharmacy->id)
            ->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Pharmacy/PharmacyList', [
            'pharmacies' => $pharmacies,
            'keyword' => '',
        ]);
    }

    /**
     * Dashboard
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $pharmacy = Pharmacy::where('user_id', $user->id)->first();

        if (! $pharmacy) {
            return Inertia::render('Pharmacy/Dashboard', [
                'dashboardData' => null,
            ]);
        }

        // Get total orders count
        $totalOrders = PharmacyOrder::where('pharmacy_id', $pharmacy->id)->count();

        // Get pending orders count
        $pendingOrders = PharmacyOrder::where('pharmacy_id', $pharmacy->id)
            ->whereIn('status', ['pending', 'received'])
            ->count();

        // Get completed orders count
        $completedOrders = PharmacyOrder::where('pharmacy_id', $pharmacy->id)
            ->whereIn('status', ['completed', 'dispensed', 'ready'])
            ->count();

        // Get today's prescriptions/orders
        $todayOrders = PharmacyOrder::where('pharmacy_id', $pharmacy->id)
            ->whereDate('created_at', today())
            ->count();

        // Get total medicines/drugs
        $totalMedicines = Drug::where('user_id', $user->id)->count();

        // Calculate total revenue from completed orders
        $totalRevenue = PharmacyOrder::where('pharmacy_id', $pharmacy->id)
            ->whereIn('status', ['completed', 'dispensed', 'ready'])
            ->sum('total_amount');

        // Get recent orders with prescriptions (last 5)
        $recentOrders = PharmacyOrder::with('patient.user', 'doctor.user')
            ->where('pharmacy_id', $pharmacy->id)
            ->orderBy('id', 'DESC')
            ->take(5)
            ->get();

        // Get prescription statistics
        $prescriptionsPending = PharmacyOrder::where('pharmacy_id', $pharmacy->id)
            ->whereIn('status', ['pending', 'received', 'processing'])
            ->count();

        $prescriptionsDispensed = PharmacyOrder::where('pharmacy_id', $pharmacy->id)
            ->whereIn('status', ['dispensed', 'completed', 'ready'])
            ->count();

        // Get recent medicines for display
        $recentMedicines = Drug::where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->take(5)
            ->get();

        return Inertia::render('Pharmacy/Dashboard', [
            'dashboardData' => [
                'totalOrders' => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'completedOrders' => $completedOrders,
                'todayOrders' => $todayOrders,
                'totalMedicines' => $totalMedicines,
                'totalRevenue' => $totalRevenue ?? 0,
                'recentOrders' => $recentOrders,
                'prescriptionsPending' => $prescriptionsPending,
                'prescriptionsDispensed' => $prescriptionsDispensed,
                'recentMedicines' => $recentMedicines,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'opening_time' => 'nullable',
            'closing_time' => 'nullable',
            'about' => 'nullable',
        ]);

        $user = auth()->user();

        $pharmacy = Pharmacy::where('user_id', $user->id)->first();

        if (! $pharmacy) {
            $pharmacy = new Pharmacy;
            $pharmacy->user_id = $user->id;
        }

        $pharmacy->name = $validated['name'];
        $pharmacy->license_number = $validated['license_number'];
        $pharmacy->contact_person = $validated['contact_person'];
        $pharmacy->mobile = $validated['mobile'];
        $pharmacy->email = $validated['email'];
        $pharmacy->address = $validated['address'] ?? null;
        $pharmacy->city = $validated['city'] ?? null;
        $pharmacy->pincode = $validated['pincode'] ?? null;
        $pharmacy->state = $validated['state'] ?? null;
        $pharmacy->country = $validated['country'] ?? null;
        $pharmacy->opening_time = $validated['opening_time'] ?? null;
        $pharmacy->closing_time = $validated['closing_time'] ?? null;
        $pharmacy->about = $validated['about'] ?? null;
        $pharmacy->is_active = true;
        $pharmacy->is_verified = $pharmacy->is_verified ?? false;

        $pharmacy->save();

        return redirect()->route('pharmacy.pharmacies.index')->with('success', 'Pharmacy saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $pharmacy = Pharmacy::with('user')->where('user_id', $user->id)->first();

        if (! $pharmacy) {
            return redirect()->route('pharmacy.pharmacies.index')->with('error', 'Pharmacy not found.');
        }

        $drugs = Drug::where('user_id', $user->id)->orderBy('id', 'DESC')->paginate($this->paginate);

        return Inertia::render('Pharmacy/PharmacyDetail', [
            'pharmacy' => $pharmacy,
            'drugs' => $drugs,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function dugsDetail($id)
    {
        $drug = Drug::where('id', $id)->first();
        $pharmacyDrugs = Drug::where('user_id', $drug->user_id)->orderby('id', 'DESC')->get();

        return Inertia::render('Pharmacy/PharmacyDrugsDetail', [
            'drug' => $drug,
            'pharmacyDrugs' => $pharmacyDrugs,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pharmacy = Pharmacy::with('user')->where('id', $id)->first();

        return Inertia::render('Pharmacy/PharmacyDetail', [
            'pharmacy' => $pharmacy,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'opening_time' => 'nullable',
            'closing_time' => 'nullable',
            'about' => 'nullable',
        ]);

        $user = auth()->user();
        $pharmacy = Pharmacy::where('user_id', $user->id)->where('id', $id)->first();

        if (! $pharmacy) {
            return redirect()->route('pharmacy.pharmacies.index')->with('error', 'Pharmacy not found.');
        }

        $pharmacy->name = $validated['name'];
        $pharmacy->license_number = $validated['license_number'];
        $pharmacy->contact_person = $validated['contact_person'];
        $pharmacy->mobile = $validated['mobile'];
        $pharmacy->email = $validated['email'];
        $pharmacy->address = $validated['address'] ?? null;
        $pharmacy->city = $validated['city'] ?? null;
        $pharmacy->pincode = $validated['pincode'] ?? null;
        $pharmacy->state = $validated['state'] ?? null;
        $pharmacy->country = $validated['country'] ?? null;
        $pharmacy->opening_time = $validated['opening_time'] ?? null;
        $pharmacy->closing_time = $validated['closing_time'] ?? null;
        $pharmacy->about = $validated['about'] ?? null;

        $pharmacy->save();

        return redirect()->route('pharmacy.pharmacies.index')->with('success', 'Pharmacy updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        $pharmacy = Pharmacy::where('user_id', $user->id)->where('id', $id)->first();

        if ($pharmacy) {
            $pharmacy->delete();

            return redirect()->route('pharmacy.pharmacies.index')->with('success', 'Pharmacy deleted successfully.');
        }

        return redirect()->route('pharmacy.pharmacies.index')->with('error', 'Pharmacy not found.');
    }

    /**
     * Medicines - Display pharmacy medicines/drugs list
     */
    public function medicines(Request $request)
    {
        $user = auth()->user();
        $keyword = $request->get('keyword');

        $drugs = Drug::where('user_id', $user->id);

        if ($keyword) {
            $drugs = $drugs->where(function ($q) use ($keyword) {
                $q->where('id', 'Like', '%'.$keyword.'%')
                    ->orWhere('name', 'Like', '%'.$keyword.'%')
                    ->orWhere('generic_name', 'Like', '%'.$keyword.'%')
                    ->orWhere('brand_name', 'Like', '%'.$keyword.'%');
            });
        }

        $drugs = $drugs->orderBy('id', 'DESC')->paginate($this->paginate);

        return Inertia::render('Pharmacy/PharmacyMedicines', [
            'medicines' => $drugs,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Orders - Display pharmacy orders (PharmacyOrder model)
     */
    public function orders(Request $request)
    {
        $user = auth()->user();
        $pharmacy = Pharmacy::where('user_id', $user->id)->first();

        if (! $pharmacy) {
            return Inertia::render('Pharmacy/PharmacyOrders', [
                'orders' => [],
                'keyword' => '',
            ]);
        }

        $keyword = $request->get('keyword');

        $orders = PharmacyOrder::with('patient.user', 'doctor.user', 'appointment')
            ->where('pharmacy_id', $pharmacy->id);

        if ($keyword) {
            $orders = $orders->where(function ($q) use ($keyword) {
                $q->where('id', 'Like', '%'.$keyword.'%')
                    ->orWhereHas('patient', function ($pq) use ($keyword) {
                        $pq->where('name', 'Like', '%'.$keyword.'%');
                    })
                    ->orWhereHas('doctor.user', function ($dq) use ($keyword) {
                        $dq->where('name', 'Like', '%'.$keyword.'%');
                    });
            });
        }

        $orders = $orders->orderBy('id', 'DESC')->paginate($this->paginate);

        return Inertia::render('Pharmacy/PharmacyOrders', [
            'orders' => $orders,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Reports - Display pharmacy reports (PharmacyOrder model - fulfilled orders)
     */
    public function reports(Request $request)
    {
        $user = auth()->user();
        $pharmacy = Pharmacy::where('user_id', $user->id)->first();

        if (! $pharmacy) {
            return Inertia::render('Pharmacy/PharmacyReports', [
                'reports' => [],
                'keyword' => '',
            ]);
        }

        $keyword = $request->get('keyword');

        $reports = PharmacyOrder::with('patient.user', 'doctor.user', 'appointment')
            ->where('pharmacy_id', $pharmacy->id)
            ->whereIn('status', ['completed', 'dispensed', 'ready']);

        if ($keyword) {
            $reports = $reports->where(function ($q) use ($keyword) {
                $q->where('id', 'Like', '%'.$keyword.'%')
                    ->orWhereHas('patient', function ($pq) use ($keyword) {
                        $pq->where('name', 'Like', '%'.$keyword.'%');
                    })
                    ->orWhereHas('doctor.user', function ($dq) use ($keyword) {
                        $dq->where('name', 'Like', '%'.$keyword.'%');
                    });
            });
        }

        $reports = $reports->orderBy('id', 'DESC')->paginate($this->paginate);

        return Inertia::render('Pharmacy/PharmacyReports', [
            'reports' => $reports,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Transactions - Display pharmacy transactions with payment info (PharmacyOrder model)
     */
    public function transactions(Request $request)
    {
        $user = auth()->user();
        $pharmacy = Pharmacy::where('user_id', $user->id)->first();

        if (! $pharmacy) {
            return Inertia::render('Pharmacy/PharmacyTransactions', [
                'transactions' => [],
                'keyword' => '',
            ]);
        }

        $keyword = $request->get('keyword');
        $paymentType = $request->get('payment_type');

        $transactions = Transaction::with('patient.user', 'pharmacyOrder', 'labOrder')
            ->where(function ($query) use ($pharmacy) {
                $query->whereHas('pharmacyOrder', function ($q) use ($pharmacy) {
                    $q->where('pharmacy_id', $pharmacy->id);
                })
                    ->orWhereHas('labOrder', function ($q) use ($pharmacy) {
                        $q->where('pharmacy_id', $pharmacy->id);
                    });
            });

        if ($keyword) {
            $transactions = $transactions->where(function ($q) use ($keyword) {
                $q->where('id', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('transaction_id', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('payment_type', 'LIKE', '%'.$keyword.'%')
                    ->orWhereHas('patient', function ($pq) use ($keyword) {
                        $pq->where('name', 'LIKE', '%'.$keyword.'%');
                    });
            });
        }

        if ($paymentType) {
            $transactions = $transactions->where('payment_type', $paymentType);
        }

        $transactions = $transactions->orderBy('id', 'DESC')->paginate($this->paginate);

        return Inertia::render('Pharmacy/PharmacyTransactions', [
            'transactions' => $transactions,
            'keyword' => $keyword,
            'payment_type' => $paymentType,
        ]);
    }

    /**
     * Prescriptions - Display pharmacy prescriptions (Prescription model)
     */
    public function prescriptions(Request $request)
    {
        $user = auth()->user();
        $pharmacy = Pharmacy::where('user_id', $user->id)->first();

        if (! $pharmacy) {
            return Inertia::render('Pharmacy/PharmacyPrescriptions', [
                'prescriptions' => [],
                'keyword' => '',
            ]);
        }

        $keyword = $request->get('keyword');
        $status = $request->get('status');

        $prescriptions = Prescription::with('patient.user', 'doctor.user', 'encounter')
            ->where('pharmacy_id', $pharmacy->id);

        if ($keyword) {
            $prescriptions = $prescriptions->where(function ($q) use ($keyword) {
                $q->where('id', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('medication', 'LIKE', '%'.$keyword.'%')
                    ->orWhereHas('patient', function ($pq) use ($keyword) {
                        $pq->where('name', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orWhereHas('doctor.user', function ($dq) use ($keyword) {
                        $dq->where('name', 'LIKE', '%'.$keyword.'%');
                    });
            });
        }

        if ($status) {
            $prescriptions = $prescriptions->where('prescription', $status);
        }

        $prescriptions = $prescriptions->orderBy('id', 'DESC')->paginate($this->paginate);

        return Inertia::render('Pharmacy/PharmacyPrescriptions', [
            'prescriptions' => $prescriptions,
            'keyword' => $keyword,
            'status' => $status,
        ]);
    }

    /**
     * Store a new transaction
     */
    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'lab_order_id' => 'nullable|uuid',
            'pharmacy_order_id' => 'nullable|uuid',
            'patient_id' => 'required|uuid',
            'payment_type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'status' => 'nullable|string|max:20',
            'transaction_id' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();

        $transaction = new Transaction;
        $transaction->user_id = $user->id;
        $transaction->lab_order_id = $validated['lab_order_id'] ?? null;
        $transaction->pharmacy_order_id = $validated['pharmacy_order_id'] ?? null;
        $transaction->patient_id = $validated['patient_id'];
        $transaction->payment_type = $validated['payment_type'];
        $transaction->amount = $validated['amount'];
        $transaction->currency = $validated['currency'] ?? 'USD';
        $transaction->status = $validated['status'] ?? 'pending';
        $transaction->transaction_id = $validated['transaction_id'] ?? null;
        $transaction->payment_method = $validated['payment_method'] ?? null;
        $transaction->notes = $validated['notes'] ?? null;

        $transaction->save();

        return redirect()->back()->with('success', 'Transaction recorded successfully.');
    }
}

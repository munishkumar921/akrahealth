<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\InsuranceRequest;
use App\Models\Country;
use App\Models\Insurance;
use App\Models\State;
use App\Services\InsuranceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class DInsuranceController extends Controller
{
    public function index(Request $request)
    {
        $selectedPatientId = auth()->user()->doctor?->selected_patient_id ?? null;
        $keyword = trim((string) $request->input('keyword', ''));
        $state = trim((string) $request->input('state', ''));
        $hasCommentColumn = Schema::hasColumn('insurances', 'comment');

        $insurances = Insurance::with('address')
            ->where('patient_id', $selectedPatientId)
            ->when($keyword !== '', function ($insurances) use ($keyword, $hasCommentColumn) {
                $insurances->where(function ($query) use ($keyword, $hasCommentColumn) {
                    $query->where('plan_name', 'like', "%{$keyword}%")
                        ->orWhere('insurance_company', 'like', "%{$keyword}%")
                        ->orWhereHas('address', function ($q) use ($keyword) {
                            $q->where('address_1', 'like', "%{$keyword}%")
                                ->orWhere('city', 'like', "%{$keyword}%")
                                ->orWhere('state', 'like', "%{$keyword}%")
                                ->orWhere('phone', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->orWhere('zip', 'like', "%{$keyword}%");
                        });

                    if ($hasCommentColumn) {
                        $query->orWhere('comment', 'like', "%{$keyword}%");
                    }
                });
            })
            ->when($state !== '', function ($insurances) use ($state) {
                $insurances->whereHas('address', function ($query) use ($state) {
                    $query->where('state', $state);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();

        $insurances->getCollection()->transform(function (Insurance $insurance) {
            return [
                'id' => $insurance->id,
                'plan_name' => $insurance->plan_name,
                'insurance_company' => $insurance->insurance_company,
                'address_1' => $insurance->address?->address_1,
                'city' => $insurance->address?->city,
                'state' => $insurance->address?->state,
                'zip' => $insurance->address?->zip,
                'phone' => $insurance->address?->phone,
                'email' => $insurance->address?->email,
                'comment' => $insurance->comment,
            ];
        });

        return Inertia::render('Doctors/Patient/Insurance', [
            'insurances' => $insurances,
            'states' => State::get(),
            'countries' => Country::get(),
            'filters' => [
                'keyword' => $keyword,
                'state' => $state,
            ],
        ]);
    }

    public function store(InsuranceRequest $request, InsuranceService $obj)
    {
        $insurance = $obj->store($request->all());

        return redirect()->back()->with('success', 'Insurance added successfully.');
    }
}

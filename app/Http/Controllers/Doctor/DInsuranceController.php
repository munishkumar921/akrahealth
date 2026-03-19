<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\InsuranceRequest;
use App\Models\Country;
use App\Models\Insurance;
use App\Models\State;
use App\Services\InsuranceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DInsuranceController extends Controller
{
    public function index(Request $request)
    {
        $insurances = Insurance::with('address');
        if ($request->has('keyword')) {
            $keyword = $request->get('keyword');
            $insurances->where(function ($query) use ($keyword) {
                $query->where('plan_name', 'like', "%{$keyword}%")
                    ->orWhere('insurance_company', 'like', "%{$keyword}%")
                    ->orWhereHas('address', function ($q) use ($keyword) {
                        $q->where('address_1', 'like', "%{$keyword}%")
                            ->orWhere('city', 'like', "%{$keyword}%")
                            ->orWhere('state', 'like', "%{$keyword}%")
                            ->orWhere('zip', 'like', "%{$keyword}%");
                    });
            });
        }
        $insurances = $insurances->paginate(request('per_page', paginateLimit()))->withQueryString();

        return Inertia::render('Doctors/Patient/Insurance', [
            'insurances' => $insurances,
            'states' => State::get(),
            'countries' => Country::get(),
            'keyword' => $request->get('keyword', ''),
        ]);
    }

    public function store(InsuranceRequest $request, InsuranceService $obj)
    {
        $insurance = $obj->store($request->all());

        return redirect()->back()->with('success', 'Insurance added successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class PageController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user()->fresh();

        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('home');
        }
        $roleRoutes = [
            'SuperAdmin' => 'superAdmin.dashboard',
            'Admin' => 'admin.dashboard',
            'Doctor' => 'doctor.dashboard',
            'Patient' => 'patient.dashboard',
            'Virtual Assistant' => 'assistant.dashboard',
            'Lab' => 'lab.dashboard',
            'Pharmacy' => 'pharmacy.dashboard',
            'Biller' => 'biller.dashboard',
        ];
        // 🔥 Priority: switched role first
        if ($switched = session('switched_role')) {
            return redirect()->route($roleRoutes[$switched])->with('success', 'Welcome back '.$user->name);
        }

        // Normal role redirect
        foreach ($roleRoutes as $role => $route) {
            if ($user->hasRole($role)) {

                return redirect()->route($route)->with('success', 'Welcome back '.$user->name);
            }
        }
    }

    /**
     * Switch role between Admin and Doctor
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    // Example in your controller/store
    public function switchRole(Request $request, $role)
    {
        // Validate allowed roles
        $allowedRoles = ['Doctor', 'Patient']; // Add other roles as needed
        if (! in_array($role, $allowedRoles)) {
            abort(403);
        }

        // Store the switched role in session
        $request->session()->put('switched_role', $role);

        return redirect()->back()->with('success', "Switched to $role mode");
    }

    public function resetRole(Request $request)
    {
        $request->session()->forget('switched_role');

        return redirect()->back()->with('success', 'Role reset to original');
    }

    /**
     * user Profile
     */
    public function userProfile()
    {
        if (auth()->user()->role_id == 3) {
            return Redirect::route('doctors.show', Auth::user()->id);
        } elseif (auth()->user()->role_id == 4) {
            return Redirect::route('patients.show', Auth::user()->id);
        } elseif (auth()->user()->role_id == 6) {
            return Redirect::route('lab.show', Auth::user()->id);
        } elseif (auth()->user()->role_id == 7) {
            return Redirect::route('pharmacy.show', Auth::user()->id);
        } elseif (auth()->user()->role_id == 5) {
            return Redirect::route('virtualAssistant.show', Auth::user()->id);
        }
    }

    /**
     * home
     */
    public function home(): Response
    {
        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    /**
     * aboutUs
     */
    public function aboutUs(): Response
    {
        return Inertia::render('AboutUs');
    }

    /**
     * privacyPolicy
     */
    public function privacyPolicy(): Response
    {
        return Inertia::render('PrivacyPolicy');
    }

    /**
     * termsConditions
     */
    public function termsConditions(): Response
    {
        return Inertia::render('TermsOfService');
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type');
        $data = [];

        if ($search && $type === 'order') {
            $path = resource_path('LOINC.csv');

            $results = Excel::toArray([], $path)[0];
            $result = [];

            $pos = explode(' ', $search);

            if (count($pos) == 1) {

                $result = Arr::where($results, function ($value) use ($search) {

                    return stripos($value[2], $search) !== false;
                });
            } else {
                // Multiple keywords
                $result = Arr::where($results, function ($value) use ($pos) {
                    return $this->advanceSearch($value[2], $pos) !== false;
                });
            }

            if (count($result) > 0) {
                foreach ($result as $row) {

                    $label = $row[2].' ['.$row[1].']';
                    $data[] = [
                        'id' => $row['1'],
                        'label' => $label,
                        'value' => $label,
                    ];
                }
            }

            return $data;
        }

        if ($search && $type === 'search_rx') {

            $url = 'http://rxnav.nlm.nih.gov/REST/Prescribe/drugs.json?name='.$search;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            $json = curl_exec($ch);
            curl_close($ch);
            $rxnorm = json_decode($json, true);
            $result = [];
            $i = 0;
            if (isset($rxnorm['drugGroup']['conceptGroup'])) {
                foreach ($rxnorm['drugGroup']['conceptGroup'] as $rxnorm_row1) {
                    if ($rxnorm_row1['tty'] == 'SBD' || $rxnorm_row1['tty'] == 'SCD') {
                        if (isset($rxnorm_row1['conceptProperties'])) {
                            foreach ($rxnorm_row1['conceptProperties'] as $item) {
                                $result[$i]['rxcui'] = $item['rxcui'];
                                $result[$i]['name'] = $item['name'];
                                if ($rxnorm_row1['tty'] == 'SBD') {
                                    $result[$i]['category'] = 'Brand';
                                } else {
                                    $result[$i]['category'] = 'Generic';
                                }
                                $i++;
                            }
                        }
                    }
                }
                uasort($result, function ($a, $b) {
                    return ($a['name'] < $b['name']) ? -1 : (($a['name'] > $b['name']) ? 1 : 0);
                });
            }
            if (isset($result[0])) {
                foreach ($result as $row) {
                    $arr = explode(' / ', $row['name']);
                    $units = ['MG', 'MG/ML', 'MCG'];
                    $dosage_arr = [];
                    $unit_arr = [];
                    foreach ($arr as $row1) {
                        $arr = explode(' ', $row1);
                        foreach ($units as $unit) {
                            $key = array_search($unit, $arr);
                            if ($key) {
                                $key1 = $key - 1;
                                $dosage_arr[] = $arr[$key1];
                                $unit_arr[] = $arr[$key];
                            }
                        }
                    }
                    $data[] = [
                        'id' => $row['rxcui'],
                        'label' => $row['name'],
                        'value' => $row['name'],
                        'badge' => $row['category'],
                        'dosage' => implode(';', $dosage_arr),
                        'unit' => implode(';', $unit_arr),
                        'rxcui' => $row['rxcui'],
                    ];
                }
            }

            return $data;
        }
    }

    // Helper function to search for multiple keywords in a string
    public function advanceSearch($haystack, $words)
    {
        foreach ($words as $word) {
            if (stripos($haystack, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * ApiLogin
     */
    public function ApiLogin(): Response
    {
        return Inertia::render('Auth/ApiLogin');
    }

    /**
     * cancellationRefundPolicy
     */
    public function cancellationRefundPolicy(): Response
    {
        return Inertia::render('CancellationRefundPolicy');
    }

    /**
     * test
     */
    public function test(): Response
    {
        return Inertia::render('Test');
    }

    /**
     * faq
     */
    public function faq(): Response
    {
        return Inertia::render('Faq');
    }
}

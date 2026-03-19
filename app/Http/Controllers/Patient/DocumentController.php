<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $patientId = auth()->user()->patient->id ?? null;

        $documentsQuery = Document::with('doctor')->where('patient_id', $patientId)
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('type', 'like', '%'.$request->search.'%');
            });

        $documents = $documentsQuery->get();

        // Group documents by type for tabs
        $documentsByType = [
            'laboratory' => [],
            'imaging' => [],
            'cardiopulmonary' => [],
            'endoscopy' => [],
            'referrals' => [],
            'past-records' => [],
            'other-forms' => [],
            'letters' => [],
            'education' => [],
            'ccdas' => [],
            'ccrs' => [],
        ];

        foreach ($documents as $doc) {
            $type = strtolower($doc->type ?? '');

            // Map document types to tab keys
            if (isset($documentsByType[$type])) {
                $documentsByType[$type][] = $doc;
            } else {
                // Default to 'other-forms' for unknown types
                $documentsByType['other-forms'][] = $doc;
            }
        }

        return Inertia::render('Patients/Documents', [
            'documents' => $documentsByType,
            'keyword' => $request->search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}

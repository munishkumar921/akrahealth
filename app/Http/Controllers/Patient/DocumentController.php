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
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));

        $documentsQuery = Document::with('doctor')->where('patient_id', $patientId)
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where('type', 'like', '%'.$keyword.'%');
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
            'filters' => [
                'keyword' => $keyword,
            ],
        ]);
    }
}

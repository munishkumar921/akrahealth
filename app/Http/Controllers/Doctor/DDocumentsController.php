<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DDocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Doctors/Patient/Documents/index', [

        ]);
    }

    /**
     * Get documents by category/type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDocumentsByCategory(Request $request, DocumentService $documentService, ?string $type = null)
    {
        try {
            $user = auth()->user();
            $doctor = $user->doctor ?? null;
            $patientId = $doctor?->selected_patient_id ?? $request->input('patient_id');

            if (! $patientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No patient selected',
                ], 400);
            }

            $documents = $documentService->getDocumentsByPatient($patientId, $type);

            // Transform documents for frontend
            $formattedDocuments = $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->description ?? basename($doc->url ?? ''),
                    'date' => $doc->date,
                    'text' => $doc->description ?? '',
                    'type' => $doc->type,
                    'from' => $doc->from,
                    'url' => $doc->url,
                    'created_at' => $doc->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedDocuments,
                'total' => $formattedDocuments->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch documents: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Generate Letter
     */
    public function generateLetter() {}

    /**
     * Store generated letter
     */
    public function storeLetter(Request $request)
    {
        $documentService = new DocumentService;
        $documentService->storeLetter($request);

        return Redirect::route('doctor.documents.index')->with('success', 'Letter uploaded successfully.');
    }

    /**
     * Get available letter templates
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLetterTemplates(DocumentService $documentService)
    {
        try {
            $templates = $documentService->getLetterTemplates();

            return response()->json([
                'success' => true,
                'data' => $templates,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get patient information for letter generation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPatientInfo(Request $request, DocumentService $documentService)
    {
        try {
            $patientId = $request->input('patient_id');

            if (! $patientId) {
                $user = auth()->user();
                $doctor = $user->doctor ?? null;
                $patientId = $doctor?->selected_patient_id ?? null;
            }

            if (! $patientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No patient selected',
                ], 400);
            }

            $patientInfo = $documentService->getPatientInfo($patientId);

            return response()->json([
                'success' => true,
                'data' => $patientInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch patient info: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get doctor information for letter generation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDoctorInfo(DocumentService $documentService)
    {
        try {
            $doctorInfo = $documentService->getDoctorInfo();

            return response()->json([
                'success' => true,
                'data' => $doctorInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch doctor info: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get encounter information for letter generation (last encounter date)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEncounterInfo(Request $request)
    {
        try {
            $user = auth()->user();
            $doctor = $user->doctor ?? null;
            $patientId = $doctor?->selected_patient_id ?? $request->input('patient_id');

            if (! $patientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No patient selected',
                ], 400);
            }

            // Get the most recent encounter with non-empty eid
            $encounter = DB::table('encounters')
                ->where('patient_id', $patientId)
                ->where('eid', '!=', '')
                ->orderBy('eid', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'encounter_date' => $encounter->encounter_DOS ?? null,
                    'eid' => $encounter->eid ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch encounter info: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF preview for a letter
     *
     * @return \Illuminate\Http\Response
     */
    public function previewLetter(Request $request, DocumentService $documentService)
    {
        try {
            $validated = $request->validate([
                'letterType' => 'required|string',
                'subject' => 'required|string',
                'to' => 'required|string',
                'body' => 'required|string',
                'patient_id' => 'nullable|string',
            ]);

            $template = $documentService->getTemplateByType($validated['letterType']);
            $templateType = $template['template'] ?? 'default';

            $pdfContent = $documentService->generatePdfContent($validated, $templateType);

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="letter-preview.pdf"');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download letter as PDF
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadLetterPdf(Request $request, DocumentService $documentService)
    {
        try {
            $validated = $request->validate([
                'letterType' => 'required|string',
                'subject' => 'required|string',
                'to' => 'required|string',
                'body' => 'required|string',
                'patient_id' => 'nullable|string',
            ]);

            $template = $documentService->getTemplateByType($validated['letterType']);
            $templateType = $template['template'] ?? 'default';

            $pdfContent = $documentService->generatePdfContent($validated, $templateType);

            $fileName = 'letter_'.time().'_'.preg_replace('/\s+/', '_', $validated['subject']).'.pdf';

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $documentService = new DocumentService;
        $documentService->store($request);

        return Redirect::route('doctor.documents.index')->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function uploadCcdaView(DocumentService $documentService, string $id, string $type)
    {
        $data = $documentService->GetUploadCcdaView($id, $type);

        return Inertia::render('Doctors/Patient/Documents/UploadCcda', ['data' => $data]);
    }

    public function setCcdaData(Request $request, DocumentService $documentService)
    {
        return $documentService->setCcdaData($request);
    }

    /**
     * Bulk import all records from a C-CDA document
     */
    public function bulkImportCcda(Request $request, DocumentService $documentService)
    {
        return $documentService->bulkImportCcda($request);
    }

    /**
     * Compare CCDA records with existing database records
     */
    public function compareCcdaRecords(string $id, string $type, DocumentService $documentService)
    {
        $comparison = $documentService->compareCcdaRecords($id, $type);

        return Inertia::render('Doctors/Patient/Documents/UploadCcda', [
            'data' => $comparison,
            'document' => \App\Models\Document::findOrFail($id),
            'mode' => 'comparison',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $document = Document::findOrFail($id);

            // Delete associated file if exists
            if ($document->url) {
                $filePath = str_replace('storage/', '', $document->url);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            // Delete the document record
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Serve a letter PDF file
     *
     * @return \Illuminate\Http\Response
     */
    public function serveLetter(Request $request, string $filename)
    {
        $filePath = storage_path('app/public/letters/'.$filename);

        if (! file_exists($filePath)) {
            abort(404, 'Letter file not found');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}

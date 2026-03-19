<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\TMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DPatientMessageController extends Controller
{
    protected TMessageService $messageService;

    public function __construct(TMessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $doctor = Auth::user()->doctor;

        $messages = $this->messageService->list($request);
        $patients = $this->messageService->getPatients($doctor);
        $doctors = $this->messageService->getDoctors($doctor);

        return Inertia::render('Doctors/Patient/Messages/Index', [
            'messages' => $messages,
            'keyword' => $request->get('search'),
            'patients' => $patients,
            'doctors' => $doctors,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $doctor = Auth::user()->doctor;
        $patients = $this->messageService->getPatients($doctor);

        return Inertia::render('Doctors/Patient/Messages/Create', [
            'patients' => $patients,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'date' => 'required|date',
            'messages_dos' => 'nullable|date',
            'to' => 'nullable|string',
            'from' => 'nullable|string',
            'messages_signed' => 'nullable|string',
        ]);

        try {
            $this->messageService->createMessage($validated);

            $status = ($request->submit_type ?? null) === 'draft' ? 'Draft' : 'Sent';

            return redirect()
                ->route('doctor.t_messages.index')
                ->with('success', $status === 'Draft' ? 'Message saved as draft.' : 'Message sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $message = $this->messageService->getMessage($id);

        return Inertia::render('Doctors/Patient/Messages/Show', [
            'message' => $message,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $message = $this->messageService->getMessage($id);
        $doctor = Auth::user()->doctor;
        $patients = $this->messageService->getPatients($doctor);

        return Inertia::render('Doctors/Patient/Messages/Edit', [
            'message' => $message,
            'patients' => $patients,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'date' => 'required|date',
            'messages_dos' => 'nullable|date',
            'to' => 'nullable|string',
            'from' => 'nullable|string',
            'messages_signed' => 'nullable|string',
        ]);

        try {
            $this->messageService->updateMessage($validated, $id);

            return redirect()->route('doctor.t_messages.index')
                ->with('success', 'Message updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->messageService->deleteMessage($id);

            return redirect()->route('doctor.t_messages.index')
                ->with('success', 'Message deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete message: '.$e->getMessage());
        }
    }
}

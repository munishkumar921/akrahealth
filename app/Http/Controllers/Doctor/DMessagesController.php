<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorMessageRequest;
use App\Models\Message;
use App\Models\Patient;
use App\Services\MessageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DMessagesController extends Controller
{
    protected MessageService $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $keyword = trim((string) $request->input('keyword', $request->input('search', '')));
        $status = trim((string) $request->input('status', ''));
        $patientId = trim((string) $request->input('patient_id', ''));

        $messages = Message::with(['doctor.user', 'patient.user'])
            ->where('hospital_id', $user->doctor->hospital_id)
            ->where(function ($query) use ($user) {
                $query->where('to', $user->id)
                    ->orWhere('from', $user->id);
            })
            ->when($patientId !== '', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($q2) use ($keyword) {
                    $q2->where('subject', 'like', "%{$keyword}%")
                        ->orWhere('message', 'like', "%{$keyword}%")
                        ->orWhere('date', 'like', "%{$keyword}%")
                        ->orWhere('status', 'like', "%{$keyword}%")
                        ->orWhereHas('doctor.user', function ($q3) use ($keyword) {
                            $q3->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('patient.user', function ($q3) use ($keyword) {
                            $q3->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('patient', function ($q3) use ($keyword) {
                            $q3->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->orWhere('mobile', 'like', "%{$keyword}%");
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        $messages->getCollection()->transform(function (Message $message) {
            return [
                'id' => $message->id,
                'date' => Carbon::parse($message->date)->format('d M, Y'),
                'patient_name' => $message->patient?->name ?? $message->patient?->user?->name ?? 'N/A',
                'doctor_name' => $message->doctor?->user?->name ?? 'N/A',
                'subject' => $message->subject,
                'message' => $message->message,
                'status' => $message->status ?? 'N/A',
            ];
        });

        $doctor = Auth::user()->doctor;
        $doctors = $this->messageService->getDoctors($doctor);
        $patients = $this->messageService->getPatients($doctor);

        return Inertia::render('Doctors/Messages/Index', [
            'messages' => $messages,
            'doctors' => $doctors,
            'patients' => $patients,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
                'patient_id' => $patientId,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $doctor = Auth::user()->doctor;
        $patients = $this->messageService->getPatients($doctor);

        return Inertia::render('Doctors/Messages', [
            'patients' => $patients,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DoctorMessageRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->messageService->createMessage($validated);

            $status = ($request->submit_type ?? null) === 'draft' ? 'Draft' : 'Sent';

            return redirect()
                ->route('doctor.messages.index')
                ->with('success', $status === 'Draft' ? 'Message saved as draft.' : 'Message sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $message = $this->messageService->getMessage($id);

        return Inertia::render('Doctors/Messages/Show', [
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

        return Inertia::render('Doctors/Messages/Edit', [
            'message' => $message,
            'patients' => $patients,
            'row' => $message,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DoctorMessageRequest $request, string $id)
    {
        $validated = $request->validated();

        try {
            $this->messageService->updateMessage($validated, $id);

            $message = Message::find($id);
            $status = $message?->status ?? 'Sent';

            return redirect()->route('doctor.messages.index')
                ->with('success', $status === 'Draft' ? 'Message saved as draft.' : 'Message updated and sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->messageService->deleteMessage($id);

            return redirect()->route('doctor.messages.index')
                ->with('success', 'Message deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete message: ' . $e->getMessage());
        }
    }

    /**
     * Get messages for a specific patient
     */
    public function patientMessages($patientId)
    {
        $messages = $this->messageService->getPatientMessages($patientId);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(string $id)
    {
        $this->messageService->markAsRead($id);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread message count
     */
    public function unreadCount()
    {
        $count = $this->messageService->getUnreadCount();

        return response()->json(['count' => $count]);
    }
}

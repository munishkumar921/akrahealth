<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;

class ChatsController extends Controller
{
    protected $chatService;

    /**
     * __construct
     *
     * @param  mixed $chatService
     * @return void
     */
    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get or create conversation
     */
    public function getConversation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $conversation = $this->chatService->getOrCreateConversation(
            auth()->id(),
            $request->user_id
        );

        return $conversation;
    }

    /**
     * Get messages
     */
    public function messages($conversationId)
    {
        return $this->chatService->getMessages($conversationId);
    }

    /**
     * Send message
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'nullable|string',
            'type' => 'nullable|string',
            'file' => 'nullable|string',
            'local_time' => 'required|string'
        ]);

        $message = $this->chatService->sendMessage(
            $conversationId,
            $request->message,
            $request->type ?? 'text',
            $request->local_time,
            $request->file,
        );

        return response()->json($message);
    }

    /**
     * Mark as read
     */
    public function markAsRead($conversationId)
    {
        $this->chatService->markAsRead($conversationId);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Conversation list
     */
    public function conversations()
    {
        return $this->chatService->getUserConversations(auth()->id());
    }

    /**
     * conversationID
     *
     * @param  mixed $request
     * @param  mixed $userID
     * @return void
     */
    public function conversationID(Request $request, $userID)
    {
        $request->merge([
            'user_id' => $userID,
        ]);

        request()->session()->put('chat_partner_id', $userID);

        return $this->getConversation($request);
    }

    /**
     * index
     *
     * @param  mixed $request
     * @return void
     */
    public function index(Request $request)
    {
        $authUser = $request->user();
        $search = trim((string) $request->get('search', ''));

        $baseQuery = ChatMessage::query()
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->where(function ($query) use ($authUser) {
                $query->where('sender_id', $authUser->id)
                    ->orWhere('receiver_id', $authUser->id);
            });

        $user = auth()->user();
        $users = collect();

        /* Doctor Role */
        if ($user->hasRole('Doctor')) {

            $doctor = $user->doctor;
            $hospitalId = $doctor->hospital_id;

            $doctorUsers = User::whereHas('doctor', function ($q) use ($hospitalId) {
                $q->where('hospital_id', $hospitalId);
            })
                ->select(
                    'users.id',
                    DB::raw("CONCAT(users.name, ' (Doctor)') as name"),
                    'users.email'
                )
                ->distinct()
                ->get();

            $adminUsers = User::whereHas('hospital', function ($q) use ($hospitalId) {
                $q->where('id', $hospitalId);
            })
                ->select('users.id', 'users.name', 'users.email')
                ->get();

            $patientUsers = User::select(
                'users.id',
                DB::raw("CONCAT(users.name, ' (Patient)') as name"),
                'users.email'
            )
                ->join('patients', 'patients.user_id', '=', 'users.id')
                ->join('appointments', 'appointments.patient_id', '=', 'patients.id')
                ->join('doctors', 'doctors.id', '=', 'appointments.doctor_id')
                ->where('doctors.hospital_id', $hospitalId)
                ->distinct()
                ->get();

            $users = $users
                ->merge($doctorUsers)
                ->merge($adminUsers)
                ->merge($patientUsers);
        }

        /* Admin role */
        if ($user->hasRole('Admin')) {

            $hospitalId = $user->hospital->id;

            $doctorUsers = User::whereHas('doctor', function ($q) use ($hospitalId) {
                $q->where('hospital_id', $hospitalId);
            })
                ->select(
                    'users.id',
                    DB::raw("CONCAT(users.name, ' (Doctor)') as name"),
                    'users.email'
                )->get();

            $patientUsers = User::select(
                'users.id',
                DB::raw("CONCAT(users.name, ' (Patient)') as name"),
                'users.email'
            )
                ->join('patients', 'patients.user_id', '=', 'users.id')
                ->join('appointments', 'appointments.patient_id', '=', 'patients.id')
                ->join('doctors', 'doctors.id', '=', 'appointments.doctor_id')
                ->where('doctors.hospital_id', $hospitalId)
                ->distinct()
                ->get();

            $users = $users
                ->merge($doctorUsers)
                ->merge($patientUsers);
        }

        $users = $users->unique('id')->values();
        $users = $users->where('id', '!=', auth()->id());

        /* Patient role */
        if ($user->hasRole('Patient')) {

            $patient = $user->patient;
            $doctorUsers = User::whereHas('doctor.appointments', function ($q) use ($patient) {
                $q->where('patient_id', $patient->id);
            })->get();

            $users = $users->merge($doctorUsers);
        }

        if ($request->session()->has('chat_partner_id')) {
            $chatPartnerId = $request->session()->get('chat_partner_id');
        } else {
            $chatPartnerId = $users[0]?->id ?? null;
        }
        $request->merge([
            'user_id' => $chatPartnerId,
        ]);
        $conversation = $this->getConversation($request);

        return Inertia::render('Common/Chat/Index', [
            'users' => $users->values(),
            'filters' => [
                'search' => $search,
            ],
            'chat_partner_id' => $chatPartnerId,
            'conversation' => $conversation,
        ]);
    }

    public function store(Request $request)
    {
        $authUser = $request->user();

        $validated = $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:5000', 'required_without:file'],
            'file' => ['nullable', 'image', 'max:5120', 'required_without:message'],
        ]);

        if ($validated['receiver_id'] === $authUser->id) {
            return back()->with('error', 'You cannot send a message to yourself.');
        }

        $receiver = User::query()->with(['doctor:id,user_id', 'patient:id,user_id'])->findOrFail($validated['receiver_id']);
        $authUser->loadMissing(['doctor:id,user_id', 'patient:id,user_id']);

        $doctorId = $authUser->doctor?->id ?? $receiver->doctor?->id;
        $patientId = $authUser->patient?->id ?? $receiver->patient?->id;

        $participants = [$authUser->id, $receiver->id];
        sort($participants);
        $thread = implode('_', $participants);
        $filePath = null;
        if ($request->hasFile('file')) {
            $storedPath = $request->file('file')->store('chat-files', 'public');
            $filePath = '/storage/' . $storedPath;
        }

        ChatMessage::create([
            'doctor_id' => $doctorId,
            'patient_id' => $patientId,
            'sender_id' => $authUser->id,
            'receiver_id' => $receiver->id,
            'thread' => $thread,
            'message' => $validated['message'] ?? null,
            'file' => $filePath,
            'is_read' => false,
        ]);

        return back()->with('success', 'Message sent successfully.');
    }

    public function markRead(Request $request, ChatMessage $chat)
    {
        if ($chat->receiver_id !== $request->user()->id) {
            abort(403, 'You can only mark your received messages as read.');
        }

        if (! $chat->is_read) {
            $chat->update(['is_read' => true]);
        }

        return back();
    }
}

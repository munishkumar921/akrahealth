<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SAdminEmailNotificationController extends Controller
{
    public function massnotification(Request $request)
    {
        return Inertia::render('SAdmin/emailnotification/MassNotification', $this->buildNotificationPageData($request, 'all'));
    }

    public function systemnotification(Request $request)
    {
        return Inertia::render('SAdmin/emailnotification/SystemNotification', $this->buildNotificationPageData($request, 'system'));
    }

    public function massmailnotification(Request $request)
    {
        return Inertia::render('SAdmin/emailnotification/MassMailNotification', $this->buildNotificationPageData($request, 'email'));
    }

    public function destroyNotification(string $id)
    {
        Notification::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Notification deleted successfully.');
    }

    public function markAllAsRead(Request $request)
    {
        $scope = (string) $request->input('scope', 'all');
        $query = $this->applyScope(Notification::query(), $scope);

        $query->whereNull('read_at')->update([
            'read_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Notifications marked as read.');
    }

    public function deleteAll(Request $request)
    {
        $scope = (string) $request->input('scope', 'all');
        $query = $this->applyScope(Notification::query(), $scope);

        $query->delete();

        return redirect()->back()->with('success', 'Notifications deleted successfully.');
    }

    private function buildNotificationPageData(Request $request, string $scope): array
    {
        $filters = [
            'keyword' => trim((string) $request->input('keyword', '')),
            'type' => trim((string) $request->input('type', '')),
            'channel' => trim((string) $request->input('channel', '')),
            'status' => trim((string) $request->input('status', '')),
            'recipient_role' => trim((string) $request->input('recipient_role', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $query = $this->applyFilters(
            $this->applyScope(Notification::query(), $scope),
            $filters
        );

        $sort = (string) $request->input('sort', 'created_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $this->applySorting($query, $sort, $direction);

        $notifications = $query
            ->paginate((int) $request->input('per_page', paginateLimit()))
            ->withQueryString();

        $notifications->setCollection(
            $this->transformNotifications($notifications->getCollection())
        );

        $metricsQuery = $this->applyFilters(
            $this->applyScope(Notification::query(), $scope),
            $filters
        );

        $typeOptions = $this->applyScope(Notification::query(), $scope)
            ->select(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) as type"))
            ->whereRaw("JSON_EXTRACT(data, '$.type') IS NOT NULL")
            ->distinct()
            ->pluck('type')
            ->filter()
            ->values();

        $channelOptions = $this->applyScope(Notification::query(), $scope)
            ->select(DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.channel')), 'in_app') as channel"))
            ->distinct()
            ->pluck('channel')
            ->filter()
            ->values();

        $recipientRoleOptions = $this->applyScope(Notification::query(), $scope)
            ->select(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.recipient_role')) as recipient_role"))
            ->whereRaw("JSON_EXTRACT(data, '$.recipient_role') IS NOT NULL")
            ->distinct()
            ->pluck('recipient_role')
            ->filter()
            ->values();

        return [
            'notifications' => $notifications,
            'filters' => $filters,
            'metrics' => [
                'total' => (clone $metricsQuery)->count(),
                'unread' => (clone $metricsQuery)->whereNull('read_at')->count(),
                'read' => (clone $metricsQuery)->whereNotNull('read_at')->count(),
                'unique_recipients' => (clone $metricsQuery)->distinct('notifiable_id')->count('notifiable_id'),
            ],
            'typeOptions' => $typeOptions,
            'channelOptions' => $channelOptions,
            'recipientRoleOptions' => $recipientRoleOptions,
        ];
    }

    private function applyScope($query, string $scope)
    {
        return match ($scope) {
            'system' => $query->where(function ($innerQuery) {
                $innerQuery
                    ->where('type', 'App\\Notifications\\SystemDatabaseNotification')
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.channel')) = 'in_app'")
                    ->orWhereRaw("JSON_EXTRACT(data, '$.channel') IS NULL");
            }),
            'email' => $query->where(function ($innerQuery) {
                $innerQuery
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.channel')) = 'email'")
                    ->orWhere('type', 'like', '%Mail%')
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data, '$.type'))) LIKE '%email%'");
            }),
            default => $query,
        };
    }

    private function applyFilters($query, array $filters)
    {
        return $query
            ->when($filters['keyword'] !== '', function ($innerQuery) use ($filters) {
                $keyword = $filters['keyword'];
                $innerQuery->where(function ($searchQuery) use ($keyword) {
                    $searchQuery
                        ->where('type', 'like', "%{$keyword}%")
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.title')) LIKE ?", ["%{$keyword}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.message')) LIKE ?", ["%{$keyword}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) LIKE ?", ["%{$keyword}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.channel')) LIKE ?", ["%{$keyword}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.recipient_role')) LIKE ?", ["%{$keyword}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.patient_name')) LIKE ?", ["%{$keyword}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.doctor_name')) LIKE ?", ["%{$keyword}%"]);
                });
            })
            ->when($filters['type'] !== '', fn ($innerQuery) => $innerQuery->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) = ?", [$filters['type']]))
            ->when($filters['channel'] !== '', function ($innerQuery) use ($filters) {
                if ($filters['channel'] === 'in_app') {
                    $innerQuery->where(function ($channelQuery) {
                        $channelQuery
                            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.channel')) = 'in_app'")
                            ->orWhereRaw("JSON_EXTRACT(data, '$.channel') IS NULL");
                    });

                    return;
                }

                $innerQuery->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.channel')) = ?", [$filters['channel']]);
            })
            ->when($filters['status'] === 'read', fn ($innerQuery) => $innerQuery->whereNotNull('read_at'))
            ->when($filters['status'] === 'unread', fn ($innerQuery) => $innerQuery->whereNull('read_at'))
            ->when($filters['recipient_role'] !== '', fn ($innerQuery) => $innerQuery->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.recipient_role')) = ?", [$filters['recipient_role']]))
            ->when($filters['date_from'] !== '', fn ($innerQuery) => $innerQuery->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($innerQuery) => $innerQuery->whereDate('created_at', '<=', $filters['date_to']));
    }

    private function applySorting($query, string $sort, string $direction): void
    {
        match ($sort) {
            'title' => $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.title')) {$direction}"),
            'notification_type' => $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) {$direction}"),
            'channel' => $query->orderByRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.channel')), 'in_app') {$direction}"),
            'recipient_role' => $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.recipient_role')) {$direction}"),
            'read_at' => $query->orderBy('read_at', $direction),
            default => $query->orderBy('created_at', $direction),
        };
    }

    private function transformNotifications($notifications)
    {
        $userIds = $notifications
            ->where('notifiable_type', User::class)
            ->pluck('notifiable_id')
            ->filter()
            ->unique()
            ->values();

        $doctorIds = $notifications
            ->where('notifiable_type', Doctor::class)
            ->pluck('notifiable_id')
            ->filter()
            ->unique()
            ->values();

        $patientIds = $notifications
            ->where('notifiable_type', Patient::class)
            ->pluck('notifiable_id')
            ->filter()
            ->unique()
            ->values();

        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        $doctors = Doctor::with('user')->whereIn('id', $doctorIds)->get()->keyBy('id');
        $patients = Patient::with('user')->whereIn('id', $patientIds)->get()->keyBy('id');

        return $notifications->map(function ($notification) use ($users, $doctors, $patients) {
            $data = is_array($notification->data)
                ? $notification->data
                : json_decode($notification->data ?? '{}', true);

            if (! is_array($data)) {
                $data = [];
            }

            $recipientName = null;
            $recipientEmail = null;

            if ($notification->notifiable_type === User::class) {
                $user = $users->get($notification->notifiable_id);
                $recipientName = $user?->name;
                $recipientEmail = $user?->email;
            } elseif ($notification->notifiable_type === Doctor::class) {
                $doctor = $doctors->get($notification->notifiable_id);
                $recipientName = $doctor?->user?->name ?? $data['doctor_name'] ?? 'Doctor';
                $recipientEmail = $doctor?->user?->email;
            } elseif ($notification->notifiable_type === Patient::class) {
                $patient = $patients->get($notification->notifiable_id);
                $recipientName = $patient?->user?->name ?? $data['patient_name'] ?? 'Patient';
                $recipientEmail = $patient?->user?->email;
            }

            return [
                'id' => $notification->id,
                'title' => $data['title'] ?? $data['subject'] ?? 'Notification',
                'message' => $data['message'] ?? $data['notification'] ?? 'No message available.',
                'notification_type' => $data['type'] ?? class_basename((string) $notification->type),
                'channel' => $data['channel'] ?? 'in_app',
                'recipient_role' => $data['recipient_role'] ?? $data['for_role'] ?? class_basename((string) $notification->notifiable_type),
                'recipient_name' => $recipientName ?? $data['patient_name'] ?? $data['doctor_name'] ?? 'Unknown recipient',
                'recipient_email' => $recipientEmail,
                'status' => $notification->read_at ? 'Read' : 'Unread',
                'read_at' => $notification->read_at?->format('d M, Y h:i A'),
                'created_at' => $notification->created_at?->format('d M, Y h:i A'),
                'action_url' => $data['action_url'] ?? null,
                'raw_type' => $notification->type,
            ];
        });
    }
}

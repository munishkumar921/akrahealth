<?php

namespace App\Services;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Create a new audit log entry.
     */
    public function create(array $data): Audit
    {
        $user = auth()->user();

        $auditData = [
            'user_id' => $data['user_id'] ?? ($user ? $user->id : null),
            'admin_id' => $data['admin_id'] ?? ($user ? $user->id : null),
            'hospital_id' => $data['hospital_id'] ?? $this->resolveHospitalId(),
            'user_type' => $data['user_type'] ?? ($user ? $user->getRoleNames()->first() : null),
            'module' => $data['module'] ?? null,
            'action' => $data['action'] ?? null,
            'description' => $data['description'] ?? null,
            'ip_address' => $data['ip_address'] ?? Request::ip(),
            'user_agent' => $data['user_agent'] ?? Request::userAgent(),
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'query' => $data['query'] ?? null,
            'encounter_location' => $data['encounter_location'] ?? null,
        ];

        return Audit::create($auditData);
    }

    /**
     * Log a create action.
     */
    public function logCreate(string $module, Model $model, ?string $description = null): Audit
    {
        return $this->create([
            'module' => $module,
            'action' => 'create',
            'description' => $description ?? __('audit.created', ['model' => class_basename($model)]),
            'new_values' => $model->toArray(),
        ]);
    }

    /**
     * Build the common tenant-scoped audit query.
     */
    protected function buildScopedQuery(array $filters = []): Builder
    {
        $hospitalId = $this->resolveHospitalId();

        $query = Audit::with(['user', 'admin']);

        if (! $hospitalId) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('hospital_id', $hospitalId)
            ->when(! empty($filters['keyword']), function ($q) use ($filters) {
                $q->where(function ($subQuery) use ($filters) {
                    $subQuery->where('description', 'like', '%'.$filters['keyword'].'%')
                        ->orWhere('module', 'like', '%'.$filters['keyword'].'%')
                        ->orWhere('action', 'like', '%'.$filters['keyword'].'%')
                        ->orWhereHas('user', function ($userQuery) use ($filters) {
                            $userQuery->where('name', 'like', '%'.$filters['keyword'].'%')
                                ->orWhere('email', 'like', '%'.$filters['keyword'].'%');
                        })
                        ->orWhereHas('admin', function ($adminQuery) use ($filters) {
                            $adminQuery->where('name', 'like', '%'.$filters['keyword'].'%')
                                ->orWhere('email', 'like', '%'.$filters['keyword'].'%');
                        });
                });
            })
            ->when(! empty($filters['module']), function ($q) use ($filters) {
                $q->where('module', $filters['module']);
            })
            ->when(! empty($filters['action']), function ($q) use ($filters) {
                $q->where('action', $filters['action']);
            })
            ->when(! empty($filters['user_id']), function ($q) use ($filters) {
                $q->where('user_id', $filters['user_id']);
            })
            ->when(! empty($filters['admin_id']), function ($q) use ($filters) {
                $q->where('admin_id', $filters['admin_id']);
            })
            ->when(! empty($filters['date_from']), function ($q) use ($filters) {
                $q->whereDate('created_at', '>=', $filters['date_from']);
            })
            ->when(! empty($filters['date_to']), function ($q) use ($filters) {
                $q->whereDate('created_at', '<=', $filters['date_to']);
            });
    }

    /**
     * Resolve the active hospital context for the authenticated user.
     */
    protected function resolveHospitalId(): ?string
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return $user->hospital?->id ?? $user->hospital_id ?? null;
    }

    /**
     * Log an update action.
     */
    public function logUpdate(string $module, Model $oldModel, Model $newModel, ?string $description = null): Audit
    {
        $changes = [];
        foreach ($newModel->getAttributes() as $key => $value) {
            if (isset($oldModel->getAttributes()[$key]) && $oldModel->getAttributes()[$key] !== $value) {
                $changes[$key] = [
                    'old' => $oldModel->getAttributes()[$key],
                    'new' => $value,
                ];
            }
        }

        return $this->create([
            'module' => $module,
            'action' => 'update',
            'description' => $description ?? __('audit.updated', ['model' => class_basename($newModel)]),
            'old_values' => $oldModel->toArray(),
            'new_values' => $newModel->toArray(),
        ]);
    }

    /**
     * Log a delete action.
     */
    public function logDelete(string $module, Model $model, ?string $description = null): Audit
    {
        return $this->create([
            'module' => $module,
            'action' => 'delete',
            'description' => $description ?? __('audit.deleted', ['model' => class_basename($model)]),
            'old_values' => $model->toArray(),
        ]);
    }

    /**
     * Log a view action.
     */
    public function logView(string $module, ?Model $model = null, ?string $description = null): Audit
    {
        return $this->create([
            'module' => $module,
            'action' => 'view',
            'description' => $description ?? __('audit.viewed', ['model' => class_basename($model ?? new \stdClass)]),
            'new_values' => $model ? $model->toArray() : null,
        ]);
    }

    /**
     * Log a custom action.
     */
    public function logCustom(
        string $module,
        string $action,
        ?string $description = null,
        ?array $newValues = null,
        ?array $oldValues = null
    ): Audit {
        return $this->create([
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'new_values' => $newValues,
            'old_values' => $oldValues,
        ]);
    }

    /**
     * Get audit logs with filtering and pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLogs(array $filters = [], int $perPage = 15)
    {
        return $this->buildScopedQuery($filters)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get all filtered audit logs for export.
     */
    public function getLogsForExport(array $filters = []): Collection
    {
        return $this->buildScopedQuery($filters)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find a single scoped audit log.
     */
    public function findScopedLog(string $id): Audit
    {
        return $this->buildScopedQuery()
            ->whereKey($id)
            ->firstOrFail();
    }

    /**
     * Get audit logs for a specific model.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLogsForModel(string $module, string $modelId)
    {
        return $this->buildScopedQuery()
            ->where('module', $module)
            ->where(function ($query) use ($modelId) {
                $query->where('new_values', 'like', '%"id":"'.$modelId.'"%')
                    ->orWhere('old_values', 'like', '%"id":"'.$modelId.'"%')
                    ->orWhere('new_values', 'like', '%"id":'.$modelId.'%')
                    ->orWhere('old_values', 'like', '%"id":'.$modelId.'%');
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get available modules for filtering.
     */
    public function getModules(): array
    {
        return $this->buildScopedQuery()
            ->select('module')
            ->distinct()
            ->pluck('module')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get available actions for filtering.
     */
    public function getActions(): array
    {
        return $this->buildScopedQuery()
            ->select('action')
            ->distinct()
            ->pluck('action')
            ->filter()
            ->values()
            ->toArray();
    }
}

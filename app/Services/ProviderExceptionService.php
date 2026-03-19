<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\ProviderException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProviderExceptionService extends BaseService
{
    protected string $auditModule = 'ProviderException';

    /**
     * Get a list of provider exceptions with filtering, sorting and pagination.
     */
    public function getList(array $filters): LengthAwarePaginator
    {
        $hospitalId = $filters['hospital_id'] ?? null;
        $keyword = $filters['keyword'] ?? null;
        $perPage = $filters['per_page'] ?? 10;
        $sort = $filters['sort'] ?? 'exception_date';
        $direction = $filters['direction'] ?? 'desc';

        $query = ProviderException::with(['doctor.user', 'hospital', 'doctor'])
            ->when($hospitalId, function (Builder $q) use ($hospitalId) {
                $q->where('hospital_id', $hospitalId);
            })
            ->when($keyword, function (Builder $q) use ($keyword) {
                $q->where(function (Builder $subQuery) use ($keyword) {
                    $subQuery->whereHas('doctor.user', function (Builder $userQuery) use ($keyword) {
                        $userQuery->where('name', 'LIKE', '%'.$keyword.'%');
                    })
                        ->orWhere('title', 'LIKE', '%'.$keyword.'%')
                        ->orWhere('reason', 'LIKE', '%'.$keyword.'%');
                });
            });

        // Apply sorting
        if ($sort === 'doctor_name') {
            $query->join('doctors', 'provider_exceptions.doctor_id', '=', 'doctors.id')
                ->join('users', 'doctors.user_id', '=', 'users.id')
                ->orderBy('users.name', $direction)
                ->select('provider_exceptions.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Transform provider exception data for API/frontend response.
     */
    public function transform(ProviderException $exception): array
    {
        return [
            'id' => $exception->id,
            'exception_date' => $exception->exception_date,
            'start_time' => $exception->start_time,
            'end_time' => $exception->end_time,
            'title' => $exception->title,
            'reason' => $exception->reason,
            'doctor_id' => $exception->doctor_id,
            'doctor_name' => $exception->doctor?->user?->name ?? 'N/A',
            'hospital_id' => $exception->hospital_id,
            'hospital_name' => $exception->hospital?->name ?? 'N/A',
            'is_active' => $exception->is_active,
            'created_by' => $exception->created_by,
            'created_at' => $exception->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $exception->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get doctors for dropdown selection.
     */
    public function getDoctors(?string $hospitalId = null): array
    {
        return Doctor::with(['specialities', 'user'])
            ->when($hospitalId, function (Builder $q) use ($hospitalId) {
                $q->where('hospital_id', $hospitalId);
            })
            ->where('is_active', true)
            ->get()
            ->map(fn (Doctor $doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'first_name' => $doctor->user?->first_name,
                'last_name' => $doctor->user?->last_name,
                'specialities' => $doctor->specialities->pluck('name')->toArray(),
            ])
            ->toArray();
    }

    /**
     * Create a new provider exception.
     */
    public function store(array $data): ProviderException
    {
        // Determine if this is an update or create operation
        $isUpdate = ! empty($data['id']) && ProviderException::where('id', $data['id'])->exists();

        // Convert time format from 12-hour (h:i A) to 24-hour (H:i:s) for database storage
        $startTime = $this->convertTo24HourFormat($data['start_time']);
        $endTime = $this->convertTo24HourFormat($data['end_time']);

        $exception = ProviderException::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'exception_date' => $data['exception_date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'title' => $data['title'],
                'reason' => $data['reason'] ?? null,
                'doctor_id' => $data['doctor_id'],
                'hospital_id' => $data['hospital_id'],
                'is_active' => $data['is_active'] ?? true,
                'created_by' => $data['created_by'] ?? auth()->id(),
                'updated_by' => $data['updated_by'] ?? auth()->id(),
            ]
        );

        // Correctly log based on whether it was an update or create
        if ($isUpdate) {
            $this->logUpdate($exception, $exception, 'Provider exception updated');
        } else {
            $this->logCreate($exception, 'Provider exception created');
        }

        return $exception;
    }

    /**
     * Convert time from 12-hour format to 24-hour format for database storage.
     */
    private function convertTo24HourFormat(string $time): string
    {
        // If already in correct format (H:i:s), return as is
        if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time)) {
            return $time;
        }

        // Convert from 12-hour format (h:i A) to 24-hour format
        return \Carbon\Carbon::createFromFormat('h:i A', $time)->format('H:i:s');
    }

    /**
     * Delete a provider exception.
     */
    public function delete(string $id): bool
    {
        $exception = ProviderException::findOrFail($id);

        $this->logDelete($exception, 'Provider exception deleted');

        return $exception->delete();
    }

    /**
     * Toggle the active status of a provider exception.
     */
    public function toggleStatus(string $id, bool $isActive): ProviderException
    {
        $exception = ProviderException::findOrFail($id);
        $oldData = $exception->toArray();

        $exception->update([
            'is_active' => $isActive,
            'updated_by' => auth()->id(),
        ]);

        $this->logUpdate(
            (object) $oldData,
            $exception,
            'Provider exception status toggled to: '.($isActive ? 'active' : 'inactive')
        );

        return $exception;
    }

    /**
     * Get a single provider exception by ID.
     */
    public function getById(string $id): ?ProviderException
    {
        return ProviderException::with(['doctor', 'hospital'])->findOrFail($id);
    }

    /**
     * Get hospital ID from the authenticated admin user.
     */
    public function getHospitalIdFromAdmin(): ?string
    {
        $hospital = Hospital::where('user_id', auth()->user()->id)->first();

        return $hospital?->id;
    }

    /**
     * Get hospital ID from doctor.
     */
    public function getHospitalIdFromDoctor(string $doctorId): ?string
    {
        $doctor = Doctor::findOrFail($doctorId);

        return $doctor->hospital_id;
    }
}

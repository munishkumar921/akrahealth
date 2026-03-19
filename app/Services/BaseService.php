<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

/**
 * BaseService
 *
 * All service classes should extend this base service to get
 * built-in audit logging capabilities.
 *
 * Usage:
 *   class PatientService extends BaseService
 *   {
 *       protected $auditModule = 'Patient';
 *
 *       public function savePatient(array $data)
 *       {
 *           // Your service logic here
 *           // Audit will automatically log create/update/delete based on method called
 *       }
 *   }
 */
abstract class BaseService
{
    /**
     * The module name for audit logging.
     * Override this in child services.
     */
    protected string $auditModule = 'System';

    /**
     * The audit service instance.
     */
    protected ?AuditService $auditService = null;

    /**
     * Get the audit service instance.
     */
    protected function getAuditService(): AuditService
    {
        if ($this->auditService === null) {
            $this->auditService = app(AuditService::class);
        }

        return $this->auditService;
    }

    /**
     * Log a create action.
     */
    protected function logCreate(object $model, ?string $description = null): void
    {
        $this->getAuditService()->logCreate($this->auditModule, $model, $description);
    }

    /**
     * Log an update action.
     */
    protected function logUpdate(object $oldModel, object $newModel, ?string $description = null): void
    {
        $this->getAuditService()->logUpdate($this->auditModule, $oldModel, $newModel, $description);
    }

    /**
     * Log a delete action.
     */
    protected function logDelete(object $model, ?string $description = null): void
    {
        $this->getAuditService()->logDelete($this->auditModule, $model, $description);
    }

    /**
     * Log a view action.
     */
    protected function logView(object $model, ?string $description = null): void
    {
        $this->getAuditService()->logView($this->auditModule, $model, $description);
    }

    /**
     * Log a custom action.
     */
    protected function logCustom(string $action, ?string $description = null, ?array $newValues = null, ?array $oldValues = null): void
    {
        $this->getAuditService()->logCustom($this->auditModule, $action, $description, $newValues, $oldValues);
    }

    /**
     * Get the current user for audit logging.
     */
    protected function getCurrentUser(): ?\App\Models\User
    {
        return Auth::user();
    }

    /**
     * Get current user ID.
     */
    protected function getCurrentUserId(): ?int
    {
        return Auth::id();
    }

    /**
     * Get current user role.
     */
    protected function getCurrentUserRole(): ?string
    {
        $user = $this->getCurrentUser();

        return $user ? $user->getRoleNames()->first() : null;
    }
}

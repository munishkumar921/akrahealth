<?php

namespace App\Traits;

use App\Services\AuditService;
use Illuminate\Support\Facades\Log;
use Throwable;

trait LangHelper
{
    /**
     * lang
     *
     * @param  mixed  $files
     */
    public function lang(array $files = []): array
    {
        $labels = [];
        if (! empty($files)) {
            foreach ($files as $file) {
                try {
                    $labels = array_merge($labels, __($file));
                } catch (Throwable $th) {
                    Log::emergency([
                        'file' => 'Language trait',
                        'message' => $file,
                    ]);
                }
            }
        }

        try {
            $labels = array_merge($labels, __('common'));
        } catch (Throwable $th) {
            Log::emergency([
                'file' => 'Language trait',
                'message' => 'common',
            ]);
        }

        return $labels;
    }

    /**
     * audit
     *
     * Log an audit action
     *
     * @param  string  $action  The action type (create, update, delete, view, etc.)
     * @param  string  $module  The module name (patients, doctors, admins, etc.)
     * @param  string|null  $description  Custom description
     * @param  array|null  $newValues  New values for create/update
     * @param  array|null  $oldValues  Old values for update/delete
     * @return void
     */
    public function audit($action, $module, ?string $description = null, ?array $newValues = null, ?array $oldValues = null)
    {
        try {
            $auditService = new AuditService;
            $auditService->logCustom(
                $module,
                $action,
                $description,
                $newValues,
                $oldValues
            );
        } catch (\Throwable $th) {
            Log::error('Audit logging failed: '.$th->getMessage(), [
                'action' => $action,
                'module' => $module,
                'exception' => $th,
            ]);
        }
    }

    /**
     * Log a create action
     *
     * @param  string  $module
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function auditCreate($module, $model, ?string $description = null)
    {
        try {
            $auditService = new AuditService;
            $auditService->logCreate($module, $model, $description);
        } catch (\Throwable $th) {
            Log::error('Audit create logging failed: '.$th->getMessage(), [
                'module' => $module,
                'exception' => $th,
            ]);
        }
    }

    /**
     * Log an update action
     *
     * @param  string  $module
     * @param  \Illuminate\Database\Eloquent\Model  $oldModel
     * @param  \Illuminate\Database\Eloquent\Model  $newModel
     * @return void
     */
    public function auditUpdate($module, $oldModel, $newModel, ?string $description = null)
    {
        try {
            $auditService = new AuditService;
            $auditService->logUpdate($module, $oldModel, $newModel, $description);
        } catch (\Throwable $th) {
            Log::error('Audit update logging failed: '.$th->getMessage(), [
                'module' => $module,
                'exception' => $th,
            ]);
        }
    }

    /**
     * Log a delete action
     *
     * @param  string  $module
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function auditDelete($module, $model, ?string $description = null)
    {
        try {
            $auditService = new AuditService;
            $auditService->logDelete($module, $model, $description);
        } catch (\Throwable $th) {
            Log::error('Audit delete logging failed: '.$th->getMessage(), [
                'module' => $module,
                'exception' => $th,
            ]);
        }
    }

    /**
     * Log a view action
     *
     * @param  string  $module
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return void
     */
    public function auditView($module, $model = null, ?string $description = null)
    {
        try {
            $auditService = new AuditService;
            $auditService->logView($module, $model, $description);
        } catch (\Throwable $th) {
            Log::error('Audit view logging failed: '.$th->getMessage(), [
                'module' => $module,
                'exception' => $th,
            ]);
        }
    }
}

<?php

namespace App\Traits;

use App\Services\AuditService;

/**
 * Auditable Trait
 *
 * Automatically logs model changes using Eloquent Observers.
 * Apply this trait to any model that needs automatic audit logging.
 *
 * Usage:
 *   use App\Traits\Auditable;
 *
 *   class Patient extends Model
 *   {
 *       use Auditable;
 *       // Optional: specify custom audit configuration
 *       protected $auditEnabled = true;
 *       protected $auditActions = ['create', 'update', 'delete'];
 *   }
 */
trait Auditable
{
    /**
     * Boot the auditable trait.
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            if (static::shouldAudit('create')) {
                app(AuditService::class)->logCreate(
                    static::getAuditModule(),
                    $model,
                    static::getAuditDescription('created', $model)
                );
            }
        });

        static::updated(function ($model) {
            if (static::shouldAudit('update')) {
                $oldModel = $model->getOriginal();
                $newModel = $model->fresh();

                app(AuditService::class)->logUpdate(
                    static::getAuditModule(),
                    $oldModel,
                    $newModel,
                    static::getAuditDescription('updated', $model)
                );
            }
        });

        static::deleted(function ($model) {
            if (static::shouldAudit('delete')) {
                app(AuditService::class)->logDelete(
                    static::getAuditModule(),
                    $model,
                    static::getAuditDescription('deleted', $model)
                );
            }
        });
    }

    /**
     * Get the module name for audit logging.
     */
    public static function getAuditModule(): string
    {
        return class_basename(static::class);
    }

    /**
     * Get custom audit description.
     */
    public static function getAuditDescription(string $action, $model): string
    {
        $modelName = class_basename(static::class);
        $modelNameLower = strtolower($modelName);

        // Try to get a name identifier from the model
        $nameIdentifier = '';
        if (method_exists($model, 'getNameIdentifier')) {
            $nameIdentifier = $model->getNameIdentifier();
        } elseif (isset($model->name)) {
            $nameIdentifier = $model->name;
        } elseif (isset($model->title)) {
            $nameIdentifier = $model->title;
        } elseif (isset($model->displayname)) {
            $nameIdentifier = $model->displayname;
        } elseif (isset($model->user) && isset($model->user->name)) {
            $nameIdentifier = $model->user->name;
        } elseif (isset($model->id)) {
            $nameIdentifier = 'ID: '.$model->id;
        }

        switch ($action) {
            case 'created':
                return $modelName." '".$nameIdentifier."' was created";
            case 'updated':
                return $modelName." '".$nameIdentifier."' was updated";
            case 'deleted':
                return $modelName." '".$nameIdentifier."' was deleted";
            default:
                return $modelName.' was '.$action.'d';
        }
    }

    /**
     * Determine if auditing is enabled for this model.
     */
    public static function shouldAudit(string $action): bool
    {
        // Check if auditing is disabled
        if (isset(static::$auditEnabled) && static::$auditEnabled === false) {
            return false;
        }

        // Check if specific actions are allowed
        if (isset(static::$auditActions) && ! in_array($action, static::$auditActions)) {
            return false;
        }

        return true;
    }

    /**
     * Get the audit events to listen for.
     */
    public function getAuditEvents(): array
    {
        return ['created', 'updated', 'deleted'];
    }

    /**
     * Get custom old values for audit.
     */
    public function getAuditOldValues(): ?array
    {
        return null;
    }

    /**
     * Get custom new values for audit.
     */
    public function getAuditNewValues(): ?array
    {
        return null;
    }
}

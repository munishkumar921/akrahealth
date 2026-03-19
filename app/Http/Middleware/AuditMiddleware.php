<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only audit if request is successful
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->auditRequest($request);
        }

        return $response;
    }

    /**
     * Audit the current request.
     */
    protected function auditRequest(Request $request): void
    {
        try {
            // Skip auditing for certain routes
            $skipRoutes = [
                'admin.log',
                'admin.dashboard',
                'admin.profile',
                'admin.messages',
                'admin.callLogs',
                'admin.notification',
                'admin.subscription',
                'api.roles',
            ];

            $routeName = $request->route()?->getName();
            if (! $routeName) {
                return;
            }

            foreach ($skipRoutes as $skipRoute) {
                if (str_starts_with($routeName, $skipRoute)) {
                    return;
                }
            }

            // Skip AJAX requests for auto-auditing
            if ($request->ajax() || $request->wantsJson()) {
                return;
            }

            // Determine module from route name
            $module = $this->determineModule($routeName);

            // Determine action from HTTP method
            $action = $this->determineAction($request);

            // Create audit log
            $auditService = new AuditService;
            $auditService->create([
                'module' => $module,
                'action' => $action,
                'description' => "{$action} on {$module}",
                'new_values' => $request->except(['password', 'token', '_token']),
                'query' => http_build_query($request->query()),
            ]);
        } catch (\Throwable $th) {
            // Don't break the request if audit fails
            report($th);
        }
    }

    /**
     * Determine the module from route name.
     */
    protected function determineModule(string $routeName): string
    {
        // Extract module from route name like 'admin.patients.store' -> 'patients'
        $parts = explode('.', $routeName);
        if (count($parts) >= 2) {
            return $parts[1];
        }

        return 'general';
    }

    /**
     * Determine action from HTTP method.
     */
    protected function determineAction(Request $request): string
    {
        return match ($request->method()) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            'GET' => 'view',
            default => 'unknown',
        };
    }
}

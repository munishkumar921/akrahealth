<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AuditLogsExport;
use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AAuditController extends Controller
{
    protected $auditService;

    public $paginate = 15;

    /**
     * __construct
     *
     * @param  mixed  $auditService
     * @return void
     */
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Display a listing of the audit logs.
     */
    public function index(Request $request)
    {
        $filters = [
            'keyword' => $request->get('keyword', ''),
            'module' => $request->get('module', ''),
            'action' => $request->get('action', ''),
            'user_id' => $request->get('user_id', ''),
            'admin_id' => $request->get('admin_id', ''),
            'date_from' => $request->get('date_from', ''),
            'date_to' => $request->get('date_to', ''),
        ];

        $auditLogs = $this->auditService->getLogs($filters, $this->paginate);

        // Transform for frontend
        $auditLogs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'user' => $log->user ? $log->user->name : ($log->user_type ?? 'System'),
                'user_email' => $log->user ? $log->user->email : null,
                'admin' => $log->admin ? $log->admin->name : null,
                'admin_email' => $log->admin ? $log->admin->email : null,
                'module' => $log->module,
                'module_label' => $log->module_label,
                'action' => $log->action,
                'formatted_action' => $log->formatted_action,
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'formatted_date' => $log->created_at->format('d M, Y h:i A'),
                'has_changes' => ! empty($log->old_values) || ! empty($log->new_values),
            ];
        });

        // Get modules and actions for filters
        $modules = $this->auditService->getModules();
        $actions = $this->auditService->getActions();

        return Inertia::render('Admin/Audit/Index', [
            'auditLogs' => $auditLogs,
            'filters' => $filters,
            'modules' => $modules,
            'actions' => $actions,
        ]);
    }

    /**
     * Display the specified audit log.
     */
    public function show($id)
    {
        $auditLog = Audit::with(['user', 'admin'])->findOrFail($id);

        $data = [
            'id' => $auditLog->id,
            'user' => $auditLog->user ? $auditLog->user->name : ($auditLog->user_type ?? 'System'),
            'user_email' => $auditLog->user ? $auditLog->user->email : null,
            'admin' => $auditLog->admin ? $auditLog->admin->name : null,
            'admin_email' => $auditLog->admin ? $auditLog->admin->email : null,
            'module' => $auditLog->module,
            'module_label' => $auditLog->module_label,
            'action' => $auditLog->action,
            'formatted_action' => $auditLog->formatted_action,
            'description' => $auditLog->description,
            'ip_address' => $auditLog->ip_address,
            'user_agent' => $auditLog->user_agent,
            'query' => $auditLog->query,
            'old_values' => $auditLog->old_values,
            'new_values' => $auditLog->new_values,
            'created_at' => $auditLog->created_at->format('Y-m-d H:i:s'),
            'formatted_date' => $auditLog->created_at->format('d M, Y h:i A'),
        ];

        return Inertia::render('Admin/Audit/Show', [
            'auditLog' => $data,
        ]);
    }

    /**
     * Export audit logs to CSV.
     */
    public function exportCsv(Request $request)
    {
        $filters = [
            'keyword' => $request->get('keyword', ''),
            'module' => $request->get('module', ''),
            'action' => $request->get('action', ''),
            'user_id' => $request->get('user_id', ''),
            'admin_id' => $request->get('admin_id', ''),
            'date_from' => $request->get('date_from', ''),
            'date_to' => $request->get('date_to', ''),
        ];

        $fileName = 'audit-logs-'.date('Y-m-d').'.csv';

        return Excel::download(new AuditLogsExport($filters), $fileName);
    }

    /**
     * Export audit logs to PDF.
     */
    public function exportPdf(Request $request)
    {
        $filters = [
            'keyword' => $request->get('keyword', ''),
            'module' => $request->get('module', ''),
            'action' => $request->get('action', ''),
            'user_id' => $request->get('user_id', ''),
            'admin_id' => $request->get('admin_id', ''),
            'date_from' => $request->get('date_from', ''),
            'date_to' => $request->get('date_to', ''),
        ];

        $auditLogs = $this->auditService->getLogs($filters, 100);

        $data = [
            'auditLogs' => $auditLogs,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'filters' => $filters,
        ];

        $pdf = Pdf::loadView('pdf.audit-logs', $data);

        return $pdf->download('audit-logs-'.date('Y-m-d').'.pdf');
    }
}

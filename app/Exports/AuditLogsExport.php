<?php

namespace App\Exports;

use App\Services\AuditService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AuditLogsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $auditService = new AuditService;

        return $auditService->getLogs($this->filters, 1000);
    }

    /**
     * @param  mixed  $row
     */
    public function map($row): array
    {
        return [
            'ID' => $row->id,
            'User' => $row->user ? $row->user->name : ($row->user_type ?? 'System'),
            'User Email' => $row->user ? $row->user->email : '',
            'Admin' => $row->admin ? $row->admin->name : '',
            'Admin Email' => $row->admin ? $row->admin->email : '',
            'Module' => $row->module ?? '',
            'Action' => $row->action ?? '',
            'Description' => $row->description ?? '',
            'IP Address' => $row->ip_address ?? '',
            'User Agent' => $row->user_agent ?? '',
            'Created At' => $row->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'User Email',
            'Admin',
            'Admin Email',
            'Module',
            'Action',
            'Description',
            'IP Address',
            'User Agent',
            'Created At',
        ];
    }
}

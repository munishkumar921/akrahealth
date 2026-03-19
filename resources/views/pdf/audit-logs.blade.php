<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Logs Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .meta {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .action-create { color: #28a745; }
        .action-update { color: #ffc107; }
        .action-delete { color: #dc3545; }
        .action-view { color: #17a2b8; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #999;
        }
        .filters {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }
        .filters strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Audit Logs Report</h1>
        <div class="meta">
            Generated at: {{ $generated_at }}
        </div>
    </div>

    @if(!empty($filters) && count(array_filter($filters)) > 0)
    <div class="filters">
        <strong>Applied Filters:</strong>
        @if(!empty($filters['keyword']))
            Keyword: "{{ $filters['keyword'] }}" |
        @endif
        @if(!empty($filters['module']))
            Module: "{{ $filters['module'] }}" |
        @endif
        @if(!empty($filters['action']))
            Action: "{{ $filters['action'] }}" |
        @endif
        @if(!empty($filters['date_from']))
            From: "{{ $filters['date_from'] }}" |
        @endif
        @if(!empty($filters['date_to']))
            To: "{{ $filters['date_to'] }}"
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 12%;">User</th>
                <th style="width: 12%;">Admin</th>
                <th style="width: 10%;">Module</th>
                <th style="width: 8%;">Action</th>
                <th style="width: 25%;">Description</th>
                <th style="width: 10%;">IP Address</th>
                <th style="width: 12%;">Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($auditLogs as $log)
            <tr>
                <td>{{ substr($log->id, 0, 8) }}</td>
                <td>
                    {{ $log->user ? $log->user->name : ($log->user_type ?? 'System') }}
                </td>
                <td>
                    {{ $log->admin ? $log->admin->name : '-' }}
                </td>
                <td>{{ $log->module ?? '-' }}</td>
                <td class="action-{{ $log->action }}">
                    {{ ucfirst($log->action ?? '-') }}
                </td>
                <td>{{ $log->description ?? '-' }}</td>
                <td>{{ $log->ip_address ?? '-' }}</td>
                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Total Records: {{ $auditLogs->total() }} | Page {{ $auditLogs->currentPage() }} of {{ $auditLogs->lastPage() }}
    </div>
</body>
</html>


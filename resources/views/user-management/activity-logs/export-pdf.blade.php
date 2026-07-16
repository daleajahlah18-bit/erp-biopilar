<!DOCTYPE html>
<html>
<head>
    <title>Audit Trail</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2 class="text-center">Enterprise Audit Trail</h2>
    <p>Generated on: {{ date('d M Y H:i:s') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>User</th>
                <th>Module</th>
                <th>Action</th>
                <th>Description</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                <td>{{ $log->causer ? $log->causer->name : 'System' }}</td>
                <td>{{ $log->log_name }}</td>
                <td>{{ ucfirst($log->event) }}</td>
                <td>{{ $log->description }}</td>
                <td>{{ $log->ip_address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

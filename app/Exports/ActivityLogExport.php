<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActivityLogExport implements FromCollection, WithHeadings, WithMapping
{
    protected $logs;

    public function __construct($logs)
    {
        $this->logs = $logs;
    }

    public function collection()
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return [
            'Date & Time (WIB)',
            'User',
            'Module',
            'Action',
            'Description',
            'IP Address',
            'Browser'
        ];
    }

    public function map($log): array
    {
        return [
            $log->created_at->format('d M Y H:i'),
            $log->causer ? $log->causer->name : 'System',
            $log->log_name,
            ucfirst($log->event),
            $log->description,
            $log->ip_address,
            $log->user_agent
        ];
    }
}

<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('Activity Logs.visible');

        $query = ActivityLog::with('causer')->latest();

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhereHas('causer', function($qCauser) use ($search) {
                      $qCauser->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('module')) {
            $query->where('log_name', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Export handling
        if ($request->has('export')) {
            if ($request->export === 'pdf') {
                $logs = $query->get();
                $pdf = Pdf::loadView('user-management.activity-logs.export-pdf', compact('logs'))
                          ->setPaper('a4', 'landscape');
                return $pdf->download('Audit_Trail_' . date('Y-m-d') . '.pdf');
            } elseif ($request->export === 'excel') {
                $logs = $query->get();
                $filename = 'Audit_Trail_' . date('Y-m-d') . '.csv';
                
                $headers = [
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$filename",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                ];

                $callback = function() use($logs) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, ['Date & Time (WIB)', 'User', 'Module', 'Action', 'Description', 'IP Address', 'Browser']);

                    foreach ($logs as $log) {
                        fputcsv($file, [
                            $log->created_at->format('d M Y H:i'),
                            $log->causer ? $log->causer->name : 'System',
                            $log->log_name,
                            ucfirst($log->event),
                            $log->description,
                            $log->ip_address,
                            $log->user_agent
                        ]);
                    }

                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }
        }

        $logs = $query->sortable()->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();
        $modules = ActivityLog::select('log_name')->distinct()->whereNotNull('log_name')->pluck('log_name');
        $actions = ActivityLog::select('event')->distinct()->whereNotNull('event')->pluck('event');

        return view('user-management.activity-logs.index', compact('logs', 'users', 'modules', 'actions'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.activity-logs.index', [
            'logs' => $logs,
        ]);
    }
}

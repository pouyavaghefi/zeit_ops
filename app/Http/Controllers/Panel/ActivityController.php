<?php

namespace App\Http\Controllers\Panel;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityController extends DashboardController
{
    public function index()
    {
        $notifications = ActivityLog::where('user_id', Auth::id())
            ->latest()
            ->paginate(20); // this returns a paginator

        return view('panel.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request)
    {
        ActivityLog::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'success']);
    }
}

<?php
namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait RecordsActivity
{
    protected function recordActivity(string $activity)
    {
        if (!Auth::check()) return;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => $activity,
        ]);
    }
}

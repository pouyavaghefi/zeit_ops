<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $notifications = ActivityLog::where('user_id', Auth::id())
                ->latest()
                    ->take(5)
                    ->get();

                $view->with('notifications', $notifications);
            }
        });
    }
}

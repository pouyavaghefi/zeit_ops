<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends PanelController
{
    public function showDashboard()
    {
        return view('panel.dashboard.index');
    }
}

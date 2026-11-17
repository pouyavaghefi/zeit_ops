<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveMembership
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $hasMembership = DB::table('membership_plan_registered_users')
            ->where('user_id', $request->user()->id)
            ->where('status', 1)
            ->exists();

        if (!$hasMembership) {
            return redirect()
                ->route('panel.dashboard')
                ->with('error', 'An active membership is required.');
        }

        return $next($request);
    }
}

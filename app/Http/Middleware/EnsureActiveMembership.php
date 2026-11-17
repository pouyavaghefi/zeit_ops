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
        if (!$request->user()) {
            abort(401);
        }

        $hasActiveMembership = DB::table(
            'membership_plan_registered_users as registrations'
        )
            ->join(
                'membership_plans as plans',
                'registrations.membership_plan_id',
                '=',
                'plans.id'
            )
            ->where('registrations.user_id', $request->user()->id)
            ->where('registrations.status', 1)
            ->where('plans.is_active', true)
            ->where(function ($query) {
                $query->whereNull('registrations.starts_at')
                    ->orWhere('registrations.starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('registrations.expires_at')
                    ->orWhere('registrations.expires_at', '>', now());
            })
            ->exists();

        if (!$hasActiveMembership) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An active ZeitOps membership is required.',
                ], 403);
            }

            return redirect()
                ->route('panel.dashboard')
                ->with(
                    'error',
                    'An active membership is required to create websites.'
                );
        }

        return $next($request);
    }
}

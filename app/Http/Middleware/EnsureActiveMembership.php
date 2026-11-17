
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
        if (!auth()->check()) {
            abort(401);
        }

        $hasMembership = DB::table(
            'membership_plan_registered_users'
        )
            ->where('user_id', auth()->id())
            ->where('status', 1)
            ->exists();

        if (!$hasMembership) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An active membership is required.',
                ], 403);
            }

            return redirect()
                ->route('panel.dashboard')
                ->with(
                    'error',
                    'You need an active ZeitOps membership to create websites.'
                );
        }

        return $next($request);
    }
}

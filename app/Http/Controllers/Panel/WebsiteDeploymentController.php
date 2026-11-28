<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Jobs\DeployWebsiteJob;
use App\Models\Website;
use App\Models\WebsiteDeployment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class WebsiteDeploymentController extends Controller
{
    public function start(Request $request, int $website): JsonResponse
    {
        $data = $request->validate(['run_migrations' => ['required', 'boolean']]);
        $site = Website::where('user_id', $request->user()->id)->findOrFail($website);

        if ($site->stack !== 'lamp' || $site->framework !== 'laravel' || (string) ($site->php_version ?: '8.3') !== '8.3') {
            return response()->json(['message' => 'Deployments currently support Laravel on LAMP/PHP 8.3.'], 422);
        }
        if (!$site->repository_url) {
            return response()->json(['message' => 'Configure a public GitHub repository before deployment.'], 422);
        }
        $run = DB::transaction(function () use ($site, $data) {
            $locked = Website::whereKey($site->id)->lockForUpdate()->firstOrFail();
            $pending = WebsiteDeployment::where('website_id', $locked->id)
                ->whereIn('status', ['queued', 'running'])->first();
            if ($pending) {
                return [false, $pending];
            }
            $run = WebsiteDeployment::create([
                'website_id' => $locked->id,
                'status' => 'queued',
                'stage' => 'queued',
                'run_migrations' => (bool) $data['run_migrations'],
            ]);
            return [true, $run];
        });
        [$shouldDispatch, $deployment] = $run;
        if ($shouldDispatch) {
            DeployWebsiteJob::dispatch($site->id, $deployment->id);
        }
        return response()->json($this->payload($deployment->fresh()), $shouldDispatch ? 202 : 200);
    }

    public function status(Request $request, int $website): JsonResponse
    {
        Website::where('user_id', $request->user()->id)->findOrFail($website);
        $runs = WebsiteDeployment::where('website_id', $website)->latest('id')->limit(10)->get();
        return response()->json([
            'latest' => $runs->first() ? $this->payload($runs->first()) : null,
            'history' => $runs->map(fn (WebsiteDeployment $run) => $this->payload($run))->values(),
        ]);
    }

    private function payload(WebsiteDeployment $run): array
    {
        return [
            'id' => $run->id,
            'status' => $run->status,
            'stage' => $run->stage,
            'run_migrations' => $run->run_migrations,
            'commit' => $run->commit_sha,
            'error' => $run->error,
            'started_at' => $run->started_at?->toIso8601String(),
            'finished_at' => $run->finished_at?->toIso8601String(),
        ];
    }
}

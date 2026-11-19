
<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Jobs\ProvisionWebsiteJob;
use App\Models\Website;
use App\Models\WebsiteProvisioningRun;
use App\Services\Provisioning\LampRecipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WebsiteProvisioningController extends Controller
{
    public function start(
        Request $request,
        int $website
    ): JsonResponse {
        $request->validate([
            'confirmed' => ['required', 'accepted'],
        ]);

        if (!config('services.zeitops.provisioning_enabled', false)) {
            return response()->json([
                'message' => 'VPS installation is disabled by the administrator.',
            ], 503);
        }

        $website = Website::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($website);

        if (!LampRecipe::supported(
            (string) $website->stack,
            (string) $website->runtime_version
        )) {
            return response()->json([
                'message' => 'Automated installation currently supports only LAMP with PHP 8.3 on Ubuntu 24.04.',
            ], 422);
        }

        $serverId = (string) $website->server_id;

        if (!Str::isUuid($serverId)) {
            return response()->json([
                'message' => 'Invalid registered server.',
            ], 422);
        }

        $metadataPath = storage_path(
            "app/private/zeitops/servers/{$serverId}/server.json"
        );

        try {
            $server = json_decode(
                File::get($metadataPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registered server metadata is unavailable.',
            ], 422);
        }

        if (
            !is_array($server) ||
            ($server['id'] ?? null) !== $serverId ||
            (string) ($server['user_id'] ?? '') !==
            (string) $request->user()->id ||
            ($server['status'] ?? null) !== 'connected'
        ) {
            return response()->json([
                'message' => 'You must own a connected server to provision this website.',
            ], 422);
        }

        $result = DB::transaction(function () use ($website) {

            // Lock this website during run creation.
            Website::query()
                ->whereKey($website->id)
                ->lockForUpdate()
                ->firstOrFail();

            $latest = WebsiteProvisioningRun::query()
                ->where('website_id', $website->id)
                ->latest('id')
                ->first();

            if ($latest && in_array(
                    $latest->status,
                    ['queued', 'running', 'completed'],
                    true
                )) {
                return [
                    'run' => $latest,
                    'dispatch' => false,
                ];
            }

            $run = WebsiteProvisioningRun::create([
                'website_id' => $website->id,
                'server_id' => $website->server_id,
                'status' => 'queued',
                'percentage' => 0,
                'current_operation' => 'Waiting for provisioning worker',
                'message' => 'Provisioning request queued.',
                'tasks' => LampRecipe::initialTasks(),
                'logs' => ['Provisioning request accepted.'],
            ]);

            return [
                'run' => $run,
                'dispatch' => true,
            ];
        });

        $run = $result['run'];

        if ($result['dispatch']) {
            ProvisionWebsiteJob::dispatch($run->id);
        }

        return response()->json(
            $this->formatRun($run->fresh()),
            $result['dispatch'] ? 202 : 200
        );
    }

    public function status(
        Request $request,
        int $website
    ): JsonResponse {
        $website = Website::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($website);

        $run = WebsiteProvisioningRun::query()
            ->where('website_id', $website->id)
            ->latest('id')
            ->first();

        if (!$run) {
            return response()->json([
                'status' => 'idle',
                'percentage' => 0,
                'current_operation' => 'Waiting to start',
                'completed_tasks' => 0,
                'total_tasks' => count(LampRecipe::initialTasks()),
                'message' => 'Server preparation has not started.',
                'tasks' => LampRecipe::initialTasks(),
                'logs' => [],
            ]);
        }

        return response()->json(
            $this->formatRun($run)
        );
    }

    private function formatRun(
        WebsiteProvisioningRun $run
    ): array {
        $tasks = $run->tasks ?? [];

        $completed = count(array_filter(
            $tasks,
            fn ($task) =>
                ($task['status'] ?? null) === 'completed'
        ));

        return [
            'run_id' => $run->id,
            'status' => $run->status,
            'percentage' => (int) $run->percentage,
            'current_operation' => $run->current_operation,
            'completed_tasks' => $completed,
            'total_tasks' => count($tasks),
            'message' => $run->message,
            'error' => $run->error,
            'tasks' => $tasks,
            'logs' => $run->logs ?? [],
            'started_at' => $run->started_at?->toIso8601String(),
            'finished_at' => $run->finished_at?->toIso8601String(),
        ];
    }
}

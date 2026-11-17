<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\JsonResponse;

class WebsiteProvisioningController extends Controller
{
    /**
     * Return the provisioning status for a website.
     */
    public function status(int $website): JsonResponse
    {
        $website = Website::query()
            ->where('user_id', auth()->id())
            ->findOrFail($website);

        return response()->json([
            'status' => 'idle',
            'percentage' => 0,
            'current_operation' => 'Waiting to start',
            'completed_tasks' => 0,
            'total_tasks' => 6,
            'message' => 'Server preparation has not started.',
            'tasks' => [
                [
                    'id' => 'inspect',
                    'name' => 'Inspect operating system and permissions',
                    'status' => 'pending',
                    'detail' => 'Not started',
                ],
                [
                    'id' => 'packages',
                    'name' => 'Update package information',
                    'status' => 'pending',
                    'detail' => 'Not started',
                ],
                [
                    'id' => 'webserver',
                    'name' => 'Install web server',
                    'status' => 'pending',
                    'detail' => 'Not started',
                ],
                [
                    'id' => 'runtime',
                    'name' => 'Install application runtime',
                    'status' => 'pending',
                    'detail' => 'Not started',
                ],
                [
                    'id' => 'dependencies',
                    'name' => 'Install stack dependencies',
                    'status' => 'pending',
                    'detail' => 'Not started',
                ],
                [
                    'id' => 'verify',
                    'name' => 'Verify installed components',
                    'status' => 'pending',
                    'detail' => 'Not started',
                ],
            ],
            'logs' => [],
        ]);
    }

    /**
     * Start provisioning.
     *
     * Not implemented yet.
     */
    public function start(int $website): JsonResponse
    {
        $website = Website::query()
            ->where('user_id', auth()->id())
            ->findOrFail($website);

        return response()->json([
            'message' => 'The provisioning engine is not implemented yet.',
        ], 501);
    }
}

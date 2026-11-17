<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WebsiteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET /panel/websites/create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $servers = collect();

        $basePath = storage_path(
            'app/private/zeitops/servers'
        );

        if (File::isDirectory($basePath)) {
            foreach (File::directories($basePath) as $directory) {
                $metadataPath = $directory . '/server.json';

                if (!File::isFile($metadataPath)) {
                    continue;
                }

                try {
                    $server = json_decode(
                        File::get($metadataPath),
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    );
                } catch (\Throwable $e) {
                    report($e);
                    continue;
                }

                if (!is_array($server)) {
                    continue;
                }

                if (
                    (string) ($server['user_id'] ?? '') !==
                    (string) auth()->id()
                ) {
                    continue;
                }

                if (($server['status'] ?? null) !== 'connected') {
                    continue;
                }

                $serverId = basename($directory);

                if (
                    !Str::isUuid($serverId) ||
                    ($server['id'] ?? null) !== $serverId
                ) {
                    continue;
                }

                $servers->push([
                    'id' => $serverId,
                    'name' => $server['name']
                        ?? $server['server_name']
                            ?? 'Server ' . substr($serverId, 0, 8),

                    'ip' => $server['ip'] ?? null,
                    'port' => $server['port'] ?? 22,
                ]);
            }
        }

        return view('panel.websites.create', [
            'servers' => $servers,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | POST /panel/websites
    |--------------------------------------------------------------------------
    */


    public function store(Request $request)
    {
        // Supported stack configurations.
        // Keep this in sync with the frontend configuration.

        $stacks = [
            'lamp' => [
                'frameworks' => [
                    'laravel', 'symfony', 'wordpress', 'php'
                ],
                'versions' => ['8.3', '8.4'],
                'php' => true,
            ],

            'lemp' => [
                'frameworks' => [
                    'laravel', 'symfony', 'wordpress', 'php'
                ],
                'versions' => ['8.3', '8.4'],
                'php' => true,
            ],

            'mern' => [
                'frameworks' => [
                    'react-express', 'next-express'
                ],
                'versions' => ['20', '22', '24'],
                'php' => false,
            ],

            'mean' => [
                'frameworks' => [
                    'angular-express'
                ],
                'versions' => ['20', '22', '24'],
                'php' => false,
            ],

            'python' => [
                'frameworks' => [
                    'django', 'flask', 'fastapi'
                ],
                'versions' => ['3.11', '3.12', '3.13'],
                'php' => false,
            ],

            'nodejs' => [
                'frameworks' => [
                    'express', 'nestjs', 'nextjs'
                ],
                'versions' => ['20', '22', '24'],
                'php' => false,
            ],
        ];

        $validated = $request->validate([
            'server_id' => [
                'required',
                'uuid',
            ],

            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],

            'domain' => [
                'required',
                'string',
                'max:253',
                'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i',
            ],

            'stack' => [
                'required',
                \Illuminate\Validation\Rule::in(array_keys($stacks)),
            ],

            'framework' => [
                'required',
                'string',
                'max:40',
            ],

            'runtime_version' => [
                'required',
                'string',
                'max:20',
            ],

            'repository_url' => [
                'nullable',
                'string',
                'max:500',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $validHttps = preg_match(
                        '~^https://(?:github\.com|gitlab\.com)/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+(?:\.git)?$~',
                        $value
                    );

                    $validSsh = preg_match(
                        '~^git@(?:github\.com|gitlab\.com):[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+(?:\.git)?$~',
                        $value
                    );

                    if (!$validHttps && !$validSsh) {
                        $fail('Enter a valid GitHub or GitLab repository URL.');
                    }
                },
            ],

            'repository_branch' => [
                'required',
                'string',
                'max:120',
                'regex:/^(?!-)(?!.*\.\.)(?!.*@\{)(?!.*\/\/)(?!.*[\/.]$)[A-Za-z0-9_][A-Za-z0-9._\/-]*$/',
            ],
        ]);

        // Validate stack/framework/runtime compatibility.

        $selectedStack = $stacks[$validated['stack']];

        if (!in_array(
            $validated['framework'],
            $selectedStack['frameworks'],
            true
        )) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'framework' => 'The selected framework is not supported by this stack.',
            ]);
        }

        if (!in_array(
            $validated['runtime_version'],
            $selectedStack['versions'],
            true
        )) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'runtime_version' => 'The selected runtime version is not supported.',
            ]);
        }

        // Confirm the selected VPS belongs to this user.

        $serverId = $validated['server_id'];

        $metadataPath = storage_path(
            "app/private/zeitops/servers/{$serverId}/server.json"
        );

        if (!\Illuminate\Support\Facades\File::isFile($metadataPath)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'server_id' => 'The selected server does not exist.',
            ]);
        }

        try {
            $server = json_decode(
                \Illuminate\Support\Facades\File::get($metadataPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            report($e);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'server_id' => 'Unable to read the selected server.',
            ]);
        }

        if (
            !is_array($server) ||
            ($server['id'] ?? null) !== $serverId ||
            (string) ($server['user_id'] ?? '') !== (string) auth()->id()
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'server_id' => 'You do not have permission to use this server.',
            ]);
        }

        if (($server['status'] ?? null) !== 'connected') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'server_id' => 'The selected server must be connected.',
            ]);
        }

        $domain = strtolower(
            rtrim(trim($validated['domain']), '.')
        );

        if (\App\Models\Website::where('server_id', $serverId)
            ->where('domain', $domain)
            ->exists()) {

            throw \Illuminate\Validation\ValidationException::withMessages([
                'domain' => 'This domain is already registered on the selected server.',
            ]);
        }

        // A PHP version is relevant only for LAMP/LEMP.

        $phpVersion = $selectedStack['php']
            ? $validated['runtime_version']
            : null;

        // Document root depends on the selected framework.
        // Actual remote paths are generated during provisioning.

        $documentRoot = match ($validated['framework']) {
            'laravel', 'symfony', 'php' => 'public',
            'wordpress' => '.',
            'react-express', 'angular-express' => 'dist',
            'next-express', 'nextjs' => '.next',
            'django', 'flask', 'fastapi',
            'express', 'nestjs' => '.',
            default => '.',
        };

        $website = \App\Models\Website::create([
            'user_id' => auth()->id(),
            'server_id' => $serverId,
            'name' => trim($validated['name']),
            'domain' => $domain,
            'stack' => $validated['stack'],
            'framework' => $validated['framework'],
            'runtime_version' => $validated['runtime_version'],
            'php_version' => $phpVersion,
            'repository_url' => $validated['repository_url'] ?? null,
            'repository_branch' => $validated['repository_branch'],
            'document_root' => $documentRoot,
            'status' => 'pending_provisioning',
        ]);

        return redirect()
            ->route('panel.websites.create')
            ->with(
                'success',
                "Website {$website->domain} has been created. Deployment is pending."
            );
    }

}

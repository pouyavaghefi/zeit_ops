
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
        $validated = $request->validate([
            'server_id' => ['required', 'uuid'],

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
                Rule::in(['lamp']),
            ],

            'framework' => [
                'required',
                Rule::in(['laravel']),
            ],

            'php_version' => [
                'required',
                Rule::in(['8.3', '8.4']),
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
                        $fail(
                            'Use a valid GitHub or GitLab repository URL.'
                        );
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

        /*
        |--------------------------------------------------------------------------
        | Verify server UUID and ownership
        |--------------------------------------------------------------------------
        */

        $serverId = $validated['server_id'];

        $basePath = storage_path(
            'app/private/zeitops/servers'
        );

        $metadataPath = $basePath
            . DIRECTORY_SEPARATOR
            . $serverId
            . DIRECTORY_SEPARATOR
            . 'server.json';

        if (!File::isFile($metadataPath)) {
            throw ValidationException::withMessages([
                'server_id' => 'The selected server does not exist.',
            ]);
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

            throw ValidationException::withMessages([
                'server_id' => 'Unable to load the selected server.',
            ]);
        }

        if (
            !is_array($server) ||
            ($server['id'] ?? null) !== $serverId ||
            (string) ($server['user_id'] ?? '') !==
            (string) auth()->id()
        ) {
            throw ValidationException::withMessages([
                'server_id' => 'You cannot use the selected server.',
            ]);
        }

        if (($server['status'] ?? null) !== 'connected') {
            throw ValidationException::withMessages([
                'server_id' => 'The server must be connected first.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create website configuration
        |--------------------------------------------------------------------------
        */

        $domain = strtolower(
            rtrim(trim($validated['domain']), '.')
        );

        $exists = Website::query()
            ->where('server_id', $serverId)
            ->where('domain', $domain)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'domain' => 'This domain already exists on the server.',
            ]);
        }

        $website = Website::create([
            'user_id' => auth()->id(),
            'server_id' => $serverId,

            'name' => trim($validated['name']),
            'domain' => $domain,

            'stack' => 'lamp',
            'framework' => 'laravel',
            'php_version' => $validated['php_version'],

            'repository_url' => $validated['repository_url'] ?? null,

            'repository_branch' => $validated['repository_branch'],

            'document_root' => 'public',
            'status' => 'pending_provisioning',
        ]);

        return redirect()
            ->route('panel.websites.create')
            ->with(
                'success',
                "Website {$website->domain} created successfully. Server provisioning is the next step."
            );
    }
}

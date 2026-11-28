<?php

namespace App\Services\Dns;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/** A zone-scoped Cloudflare client. No user-supplied URLs are ever requested. */
final class CloudflareDns
{
    private const API = 'https://api.cloudflare.com/client/v4';

    private function call(string $token, string $method, string $path, array $data = []): array
    {
        $request = Http::acceptJson()->withToken($token)->timeout(12)->connectTimeout(5);
        $response = $request->send($method, self::API.$path, $method === 'GET'
            ? ['query' => $data]
            : ['json' => $data]);

        $payload = $response->json();
        if (!$response->successful() || !is_array($payload) || ($payload['success'] ?? false) !== true) {
            // Avoid leaking tokens or arbitrary upstream error content to the UI/logs.
            throw new RuntimeException('Cloudflare DNS request failed (HTTP '.$response->status().'). Verify your zone ID, token permissions, and record values.');
        }
        return $payload;
    }

    public function inspectZone(string $token, string $id): array
    {
        return (array) ($this->call($token, 'GET', '/zones/'.$id)['result'] ?? []);
    }

    public function records(string $token, string $zoneId, int $page): array
    {
        $response = $this->call($token, 'GET', '/zones/'.$zoneId.'/dns_records', [
            'page' => $page, 'per_page' => 100, 'order' => 'name', 'direction' => 'asc',
        ]);
        return [
            'items' => array_values((array) ($response['result'] ?? [])),
            'pages' => max(1, (int) ($response['result_info']['total_pages'] ?? 1)),
        ];
    }

    public function create(string $token, string $zoneId, array $record): void
    {
        $this->call($token, 'POST', '/zones/'.$zoneId.'/dns_records', $record);
    }

    public function update(string $token, string $zoneId, string $id, array $record): void
    {
        $this->call($token, 'PATCH', '/zones/'.$zoneId.'/dns_records/'.$id, $record);
    }

    public function delete(string $token, string $zoneId, string $id): void
    {
        $this->call($token, 'DELETE', '/zones/'.$zoneId.'/dns_records/'.$id);
    }

    public function record(string $token, string $zoneId, string $id): array
    {
        return (array) ($this->call($token, 'GET', '/zones/'.$zoneId.'/dns_records/'.$id)['result'] ?? []);
    }
}

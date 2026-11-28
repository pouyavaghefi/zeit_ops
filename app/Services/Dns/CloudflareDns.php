<?php

namespace App\Services\Dns;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Creates individual rules without replacing unrelated Cloudflare redirects. */
final class CloudflareRedirects
{
    private const API = 'https://api.cloudflare.com/client/v4/zones/';
    private const PHASE = 'http_request_dynamic_redirect';

    private function call(string $token, string $method, string $url, array $data = [], bool $allowMissing = false): ?array
    {
        $response = Http::acceptJson()->withToken($token)->connectTimeout(6)->timeout(20)
            ->send($method, $url, $method === 'GET' || $method === 'DELETE' ? [] : ['json' => $data]);
        if ($allowMissing && $response->status() === 404) return null;
        $payload = $response->json();
        if (!$response->successful() || !is_array($payload) || ($payload['success'] ?? false) !== true) {
            // Avoid leaking tokens or arbitrary provider response bodies.
            throw new RuntimeException('Cloudflare Redirect Rules API failed (HTTP '.$response->status().'). Check Rulesets Edit permission and your plan limits.');
        }
        return (array) ($payload['result'] ?? []);
    }

    private function entrypoint(string $token, string $zoneId): ?array
    {
        return $this->call($token, 'GET', self::API.$zoneId.'/rulesets/phases/'.self::PHASE.'/entrypoint', [], true);
    }

    public function create(
        string $token, string $zoneId, string $host, string $path,
        string $target, int $status, bool $preserve, int $websiteId
    ): array {
        $base = self::API.$zoneId.'/rulesets';
        $entrypoint = $this->entrypoint($token, $zoneId);
        // Both components are validated by the controller before reaching this point.
        $rule = [
            'action' => 'redirect',
            'expression' => '(http.host eq '.json_encode($host, JSON_UNESCAPED_SLASHES).' and http.request.uri.path eq '.json_encode($path, JSON_UNESCAPED_SLASHES).')',
            'description' => 'ZeitOps website '.$websiteId.' redirect',
            'enabled' => true,
            'action_parameters' => [
                'from_value' => [
                    'status_code' => $status,
                    'target_url' => ['value' => $target],
                    'preserve_query_string' => $preserve,
                ],
            ],
        ];
        if ($entrypoint) {
            $rulesetId = (string) ($entrypoint['id'] ?? '');
            if (!$rulesetId) throw new RuntimeException('Cloudflare ruleset has no ID.');
            $updated = $this->call($token, 'POST', $base.'/'.$rulesetId.'/rules', $rule);
            // Cloudflare returns the updated ruleset; identify only the rule just added.
            $rules = (array) ($updated['rules'] ?? []);
            $matches = array_values(array_filter($rules, fn ($item) =>
                ($item['expression'] ?? null) === $rule['expression'] &&
                ($item['action_parameters']['from_value']['target_url']['value'] ?? null) === $target
            ));
            if (count($matches) !== 1 || empty($matches[0]['id'])) {
                throw new RuntimeException('Redirect was submitted but its Cloudflare rule ID could not be verified. Inspect provider rules before retrying.');
            }
            return [$rulesetId, (string) $matches[0]['id']];
        }
        // Create the phase entrypoint ruleset if the zone has none.
        $created = $this->call($token, 'POST', $base, [
            'name' => 'ZeitOps redirects', 'kind' => 'zone', 'phase' => self::PHASE, 'rules' => [$rule],
        ]);
        $rulesetId = (string) ($created['id'] ?? '');
        $ruleId = (string) ($created['rules'][0]['id'] ?? '');
        if (!$rulesetId || !$ruleId) throw new RuntimeException('Redirect created but Cloudflare IDs were missing. Inspect provider rules before retrying.');
        return [$rulesetId, $ruleId];
    }

    public function delete(string $token, string $zoneId, string $rulesetId, string $ruleId): void
    {
        // Deletes one managed rule, leaving all other Cloudflare rules untouched.
        $this->call($token, 'DELETE', self::API.$zoneId.'/rulesets/'.$rulesetId.'/rules/'.$ruleId);
    }
}

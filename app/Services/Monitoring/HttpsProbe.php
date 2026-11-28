<?php

namespace App\Services\Monitoring;

use App\Models\Website;
use RuntimeException;

/** HTTPS-only, pinned-IP probe. Never follow redirects or access an internal address. */
final class HttpsProbe
{
    public function check(Website $website, int $expectedStatus = 200): array
    {
        $host = strtolower(trim((string) $website->domain, '. '));
        if (strlen($host) > 253 || !preg_match('/\A(?=.{1,253}\z)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}\z/D', $host)) {
            return [false, null, null, 'Invalid registered domain'];
        }
        if (!extension_loaded('curl')) {
            return [false, null, null, 'PHP cURL extension missing'];
        }
        $ips = gethostbynamel($host);
        if (!is_array($ips) || $ips === []) {
            return [false, null, null, 'DNS lookup failed'];
        }
        // Reject all answers if even one resolves to a private, reserved or ambiguous address.
        foreach ($ips as $ip) {
            if (!$this->isPublicIpv4($ip)) {
                return [false, null, null, 'DNS points to an unsafe address'];
            }
        }
        $ip = $ips[0];
        $handle = curl_init();
        if ($handle === false) {
            return [false, null, null, 'Unable to initialize HTTPS probe'];
        }
        $received = 0;
        $options = [
            CURLOPT_URL => 'https://' . $host . '/',
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'ZeitOps-Uptime/1.0',
            CURLOPT_HTTPHEADER => ['Accept: text/html,application/json;q=0.8,*/*;q=0.5'],
            CURLOPT_RESOLVE => [$host . ':443:' . $ip],
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_PROXY => '',
            CURLOPT_NOPROXY => '*',
            CURLOPT_WRITEFUNCTION => static function ($ch, string $body) use (&$received): int {
                $received += strlen($body);
                return $received > 1048576 ? 0 : strlen($body);
            },
        ];
        // Prevent environmental curl configuration from selecting another protocol.
        if (defined('CURLOPT_PROTOCOLS_STR')) {
            $options[CURLOPT_PROTOCOLS_STR] = 'https';
        } else {
            $options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
        }
        curl_setopt_array($handle, $options);
        $start = microtime(true);
        $ok = curl_exec($handle);
        $code = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $duration = (int) round((microtime(true) - $start) * 1000);
        $error = $ok === false ? curl_errno($handle) : 0;
        curl_close($handle);

        if ($ok === false) {
            return [false, $code ?: null, $duration, $error === CURLE_WRITE_ERROR && $received > 1048576 ? 'Response exceeded size limit' : 'HTTPS request failed'];
        }
        return [$code === $expectedStatus, $code ?: null, $duration,
            $code === $expectedStatus ? null : 'Unexpected HTTP status'];
    }

    private function isPublicIpv4(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return false;
        $n = ip2long($ip);
        if ($n === false) return false;
        $n = (int) sprintf('%u', $n);
        // Include RFC1918, loopback, link-local, carrier NAT, benchmark, TEST-NET and multicast.
        foreach ([
                     ['0.0.0.0', 8], ['10.0.0.0', 8], ['100.64.0.0', 10],
                     ['127.0.0.0', 8], ['169.254.0.0', 16], ['172.16.0.0', 12],
                     ['192.0.0.0', 24], ['192.0.2.0', 24], ['192.168.0.0', 16],
                     ['198.18.0.0', 15], ['198.51.100.0', 24], ['203.0.113.0', 24],
                     ['224.0.0.0', 4], ['240.0.0.0', 4],
                 ] as [$network, $bits]) {
            $mask = ($bits === 0) ? 0 : (0xffffffff << (32 - $bits)) & 0xffffffff;
            if (($n & $mask) === ((int) sprintf('%u', ip2long($network)) & $mask)) return false;
        }
        return true;
    }
}

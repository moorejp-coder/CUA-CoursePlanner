<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DetectAttackPatterns
{
    /** Routes that carry free-form text — skip pattern scanning there. */
    private const SKIP_PATHS = ['api/chat', 'api/upload'];

    /** SQL injection patterns: specific syntax combos, not bare keywords. */
    private const SQL_PATTERNS = [
        '/UNION\s+(?:ALL\s+)?SELECT/i',
        '/;\s*(?:DROP|DELETE\s+FROM|INSERT\s+INTO|UPDATE\s+\w+\s+SET|CREATE|ALTER|TRUNCATE)/i',
        "/'\s*(?:OR|AND)\s+'?[\d']/i",
        '/\bEXEC(?:UTE)?\s*\(/i',
        '/\bxp_\w+/i',
    ];

    /** XSS and HTML/script injection patterns. */
    private const INJECT_PATTERNS = [
        '/<\s*script[\s>\/]/i',
        '/javascript\s*:/i',
        '/\bon\w{2,}\s*=/i',
        '/<\s*(?:iframe|object|embed|link|base)[\s>\/]/i',
        '/vbscript\s*:/i',
        '/data\s*:\s*text\/html/i',
    ];

    private const MAX_FIELD_BYTES = 10_000;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') || $request->isMethod('HEAD') || $this->isExcluded($request)) {
            return $next($request);
        }

        $inputs = $request->except([
            '_token', '_method',
            'password', 'password_confirmation', 'current_password',
        ]);

        foreach ($this->flatten($inputs) as $field => $value) {
            if (! is_string($value)) {
                continue;
            }

            if (strlen($value) > self::MAX_FIELD_BYTES) {
                $this->flag($request, $field, 'oversized_input');
            }

            if (str_contains($value, "\x00")) {
                $this->flag($request, $field, 'null_byte');
            }

            foreach (self::SQL_PATTERNS as $pattern) {
                if (preg_match($pattern, $value)) {
                    $this->flag($request, $field, 'sql_injection');
                }
            }

            foreach (self::INJECT_PATTERNS as $pattern) {
                if (preg_match($pattern, $value)) {
                    $this->flag($request, $field, 'xss_attempt');
                }
            }
        }

        return $next($request);
    }

    private function isExcluded(Request $request): bool
    {
        foreach (self::SKIP_PATHS as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, mixed> */
    private function flatten(array $inputs, string $prefix = ''): array
    {
        $flat = [];
        foreach ($inputs as $key => $value) {
            $fullKey = $prefix !== '' ? "{$prefix}.{$key}" : (string) $key;
            if (is_array($value)) {
                $flat = array_merge($flat, $this->flatten($value, $fullKey));
            } else {
                $flat[$fullKey] = $value;
            }
        }

        return $flat;
    }

    private function flag(Request $request, string $field, string $type): never
    {
        Log::warning('Abuse pattern blocked', [
            'type' => $type,
            'field' => $field,
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'url' => $request->fullUrl(),
        ]);

        abort(422, 'Invalid input detected.');
    }
}

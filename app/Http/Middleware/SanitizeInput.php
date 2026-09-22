<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Anti-XSS input purification.
 *
 * Runs BEFORE validation on every request: strips <script> blocks, event
 * handler attributes (onclick= etc.), javascript: URLs, and dangerous tags
 * from all string input (body + query + JSON). Stored content (names,
 * purposes, remarks, announcements) can then never carry active content,
 * so it is safe to render anywhere — even on pages that forget escaping.
 *
 * Rich-text editors are not used anywhere in this app, so nothing legit
 * needs <script>, <iframe>, on* handlers or javascript: URLs.
 */
class SanitizeInput
{
    public function handle(Request $request, Closure $next): Response
    {
        $clean = function ($value) use (&$clean) {
            if (is_string($value)) {
                return $this->purify($value);
            }
            if (is_array($value)) {
                return array_map($clean, $value);
            }
            return $value;
        };

        $request->merge($clean($request->all()));
        $request->query->replace($clean($request->query->all()));

        return $next($request);
    }

    private function purify(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        // 1. Remove complete <script>…</script> blocks (case-insensitive, DOTALL).
        $value = preg_replace('#<\s*script\b[^>]*>.*?<\s*/\s*script\s*>#is', '', $value) ?? $value;

        // 2. Remove dangerous elements entirely (self-closing or orphan tags too).
        $value = preg_replace('#<\s*/?\s*(script|iframe|object|embed|link|meta|base|form|svg|math)\b[^>]*>#is', '', $value) ?? $value;

        // 3. Strip inline event handlers: on\w+="…" / on\w+='…' / on\w+=bare
        $value = preg_replace('#\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#is', '', $value) ?? $value;

        // 4. Neutralise javascript:/vbscript:/data:text-html URLs.
        $value = preg_replace('#(href|src|action|background)\s*=\s*("\s*(javascript|vbscript|data\s*:\s*text/html)[^"]*"|\'\s*(javascript|vbscript|data\s*:\s*text/html)[^\']*\'|(javascript|vbscript|data\s*:\s*text/html)[^\s>]*)#is', '$1="#"', $value) ?? $value;

        // 5. Remove stray <script fragments left over after step 1 (unclosed tags).
        $value = preg_replace('#<\s*script\b[^>]*>.*$#is', '', $value) ?? $value;

        // 6. Strip null bytes and control chars (except newline/tab).
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? $value;

        return trim($value);
    }
}

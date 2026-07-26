<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Very small "shared secret" guard for the outbound integration APIs.
 *
 * Each partner ERP module (Finance, Inventory, etc.) is issued its own key
 * in config/partners.php / .env, and must send it on every request as:
 *
 *      X-API-KEY: <key>
 *
 * This is intentionally simple (no OAuth server, no Sanctum tokens) because
 * the goal of this integration exercise is for every group to be able to
 * stand up a working, authenticated REST endpoint quickly. It can be
 * swapped for Sanctum/OAuth later without changing the controllers.
 */
class VerifyPartnerApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = $request->header('X-API-KEY');
        $validKeys = array_filter(config('partners.api_keys', []));

        if (! $providedKey || ! in_array($providedKey, $validKeys, true)) {
            return response()->json([
                'message' => 'Unauthorized. Missing or invalid X-API-KEY header.',
            ], 401);
        }

        return $next($request);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->header('X-API-Key');
        if (! is_string($token) || ! str_starts_with($token, 'km_')) return response()->json(['success' => false, 'message' => 'A valid API key is required.', 'code' => 'UNAUTHENTICATED'], 401);
        $key = ApiKey::query()->where('key_hash', hash('sha256', $token))->whereNull('revoked_at')->with('user')->first();
        if (! $key) return response()->json(['success' => false, 'message' => 'A valid API key is required.', 'code' => 'UNAUTHENTICATED'], 401);
        $key->forceFill(['last_used_at' => now()])->save();
        $request->setUserResolver(fn () => $key->user);
        return $next($request);
    }
}

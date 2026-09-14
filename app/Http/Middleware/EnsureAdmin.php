<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() && hash_equals((string) config('services.konekti.admin_email'), (string) $request->user()->email), 403);
        return $next($request);
    }
}

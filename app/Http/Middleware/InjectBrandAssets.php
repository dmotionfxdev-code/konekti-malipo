<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InjectBrandAssets
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) return $response;
        $content = $response->getContent();
        if (is_string($content) && ! str_contains($content, 'favicon.svg')) {
            $response->setContent(str_replace('</head>', '<link rel="icon" type="image/svg+xml" href="/favicon.svg"><link rel="manifest" href="/site.webmanifest"><meta name="theme-color" content="#0d4037"></head>', $content));
        }
        return $response;
    }
}

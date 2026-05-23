<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePublicResponses
{
    protected array $publicPaths = [
        '/',
        'about',
        'contact',
        'blog',
        'search',
        'categories',
        'category',
        'genre',
        'books',
        'feed',
        'rss.xsl',
        'robots.txt',
        'sitemap.xml',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->isPublicGet($request)) {
            return $response;
        }

        if ($request->is('feed') || $request->is('rss.xsl')) {
            $response->headers->set('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400');

            return $response;
        }

        if ($request->is('robots.txt') || $request->is('sitemap.xml')) {
            return $response;
        }

        if ($request->is('build/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');

            return $response;
        }

        if ($request->header('X-Inertia')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');

            return $response;
        }

        $response->headers->set('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');

        return $response;
    }

    protected function isPublicGet(Request $request): bool
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return false;
        }

        if ($request->user()) {
            return false;
        }

        $path = trim($request->path(), '/');

        foreach ($this->publicPaths as $publicPath) {
            if ($path === $publicPath || str_starts_with($path, $publicPath.'/')) {
                return true;
            }
        }

        if (str_starts_with($path, 'build/')) {
            return true;
        }

        return false;
    }
}

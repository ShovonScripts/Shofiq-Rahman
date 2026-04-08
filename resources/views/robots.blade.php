{{-- resources/views/robots.blade.php --}}

@php
    $site = rtrim(config('app.url'), '/');
@endphp

@if (app()->environment('production'))
User-agent: *
Allow: /

# Optional: keep auth/admin pages out of indexes
Disallow: /dashboard/
Disallow: /login
Disallow: /register
Disallow: /password
Disallow: /profile

Sitemap: {{ $site }}/sitemap.xml
@else
# Non-production environments: block all crawling
User-agent: *
Disallow: /
@endif

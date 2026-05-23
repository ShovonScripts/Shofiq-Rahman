<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- CSRF for forms/AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Preconnect to self for faster asset loading --}}
  <link rel="preconnect" href="{{ url('/') }}">

  {{-- Basic SEO defaults (can be overridden by @inertiaHead from pages) --}}
  @php
    use App\Models\Setting;
    $siteName     = Setting::get('site_name', config('app.name'));
    $tagline      = Setting::get('tagline', '');
    $metaDesc     = Setting::get('meta_description', $tagline ?: $siteName);
    $favicon      = Setting::get('site_favicon_url');
    $faviconUrl   = $favicon && ! str_starts_with($favicon, 'http') ? asset($favicon) : $favicon;
    $canonicalUrl = url()->current();
  @endphp
  <meta name="description" content="{{ e($metaDesc) }}">
  <link rel="canonical" href="{{ $canonicalUrl }}">

  {{-- Discoverable feed & sitemap (SEO) --}}
  <link rel="alternate" type="application/rss+xml" title="{{ $siteName }} RSS" href="{{ url('/feed') }}">
  <link rel="sitemap"   type="application/xml"      title="Sitemap"         href="{{ url('/sitemap.xml') }}">

  {{-- Favicon: Settings > site_favicon_url if set, else default --}}
  @if ($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
  @else
    <link rel="icon" href="{{ url('/favicon.ico') }}">
  @endif

  {{-- Theme color for mobile address bar tinting --}}
  <meta name="theme-color" content="#0f172a">

  {{-- Ziggy: exposes window.Ziggy so route() works client-side.
       Keep this BEFORE Vite so the plugin can pick it up. --}}
  @routes

  {{-- Vite entry (loads JS/CSS; includes Inertia/Vue app) --}}
  @vite('resources/js/app.js')

  {{-- Inertia-managed head tags (title, meta, etc.) from Vue pages --}}
  @inertiaHead
</head>
<body class="font-sans antialiased">
  <noscript>
    <div style="padding:1rem; background:#fee2e2; color:#7f1d1d; text-align:center;">
      This site requires JavaScript to run. Please enable it in your browser.
    </div>
  </noscript>

  @inertia
</body>
</html>

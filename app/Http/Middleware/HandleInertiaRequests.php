<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default with Inertia.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // ✅ Single cache call instead of 7 separate reads
        $s = Setting::allCached();

        $siteName = $s['site_name'] ?? config('app.name');
        $tagline = $s['tagline'] ?? '';
        $metaDesc = $s['meta_description'] ?? 'বাংলা লেখকদের জন্য সহজ ব্লগিং প্ল্যাটফর্ম।';
        $metaImg = $s['meta_image'] ?? null;
        $metaKeywords = $s['meta_keywords'] ?? '';
        $metaTitle = $s['meta_title'] ?? $siteName;
        $logoUrl = $s['site_logo_url'] ?? null;
        $faviconUrl = $s['site_favicon_url'] ?? null;

        // Convert relative brand paths to absolute URLs (not affected by env change)
        $logoUrl = $logoUrl && ! str_starts_with($logoUrl, 'http') ? asset($logoUrl) : $logoUrl;
        $faviconUrl = $faviconUrl && ! str_starts_with($faviconUrl, 'http') ? asset($faviconUrl) : $faviconUrl;
        $gaId = $s['google_analytics_id'] ?? '';
        $gtmId = $s['google_tag_manager_id'] ?? '';
        $gsc = $s['google_site_verification'] ?? '';
        $bing = $s['bing_site_verification'] ?? '';
        $yandex = $s['yandex_site_verification'] ?? '';
        $fbPixel = $s['facebook_pixel_id'] ?? '';
        $fbAppId = $s['facebook_app_id'] ?? '';
        $twitterHandle = $s['twitter_handle'] ?? '';
        $canonical = $s['canonical_url'] ?? '';

        $socialRaw = $s['social_links'] ?? null;
        $social = $socialRaw ? json_decode($socialRaw, true) : [
            'facebook' => null,
            'twitter' => null,
            'youtube' => null,
        ];
        if (! is_array($social)) {
            $social = ['facebook' => null, 'twitter' => null, 'youtube' => null];
        }

        return array_merge(parent::share($request), [
            // ✅ Logged-in user info with role
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                ] : null,
            ],

            // ✅ App-wide info
            'app' => [
                'name' => config('app.name', 'Writers'),
                'url' => config('app.url', url('/')),
            ],

            // ✅ Global SEO meta
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDesc,
                'image' => $metaImg,
                'keywords' => $metaKeywords,
            ],

            // ✅ SEO (all raw settings for the frontend)
            'seo' => [
                'meta_title' => $metaTitle,
                'meta_description' => $metaDesc,
                'meta_image' => $metaImg,
                'meta_keywords' => $metaKeywords,
                'google_analytics_id' => $gaId,
                'google_tag_manager_id' => $gtmId,
                'google_site_verification' => $gsc,
                'bing_site_verification' => $bing,
                'yandex_site_verification' => $yandex,
                'facebook_pixel_id' => $fbPixel,
                'facebook_app_id' => $fbAppId,
                'twitter_handle' => $twitterHandle,
            ],

            // ✅ Settings accessible in frontend
            'settings' => [
                'site_name' => $siteName,
                'tagline' => $tagline,
                'social_links' => $social,
            ],

            // ✅ Brand (for Navbar/Footer etc.)
            'brand' => [
                'name' => $siteName,
                'logo' => $logoUrl,
                'favicon' => $faviconUrl,
            ],

            // ✅ Flash messages
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}

<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Setting;

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
        // ✅ Frequently used settings
        $siteName = Setting::get('site_name', config('app.name'));
        $tagline  = Setting::get('tagline', '');
        $metaDesc = Setting::get('meta_description', 'বাংলা লেখকদের জন্য সহজ ব্লগিং প্ল্যাটফর্ম।');
        $metaImg  = Setting::get('meta_image', null);

        // ✅ Branding
        $logoUrl    = Setting::get('site_logo_url', null);
        $faviconUrl = Setting::get('site_favicon_url', null);

        // ✅ Social links (always keep 3 keys)
        $social = Setting::getJson('social_links', [
            'facebook' => null,
            'twitter'  => null,
            'youtube'  => null,
        ]);

        return array_merge(parent::share($request), [
            // ✅ Logged-in user info with role
            'auth' => [
                'user' => $request->user() ? [
                    'id'    => $request->user()->id,
                    'name'  => $request->user()->name,
                    'email' => $request->user()->email,
                    'role'  => $request->user()->role,
                ] : null,
            ],

            // ✅ App-wide info
            'app' => [
                'name' => config('app.name', 'Writers'),
                'url'  => config('app.url', url('/')),
            ],

            // ✅ Global SEO meta
            'meta' => [
                'title'       => $siteName,
                'description' => $metaDesc,
                'image'       => $metaImg,
            ],

            // ✅ Settings accessible in frontend (now includes social_links)
            'settings' => [
                'site_name'     => $siteName,
                'tagline'       => $tagline,
                'social_links'  => $social,
            ],

            // ✅ Brand (for Navbar/Footer etc.)
            'brand' => [
                'name'    => $siteName,
                'logo'    => $logoUrl,
                'favicon' => $faviconUrl,
            ],

            // ✅ Flash messages
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class]);
    }

    /**
     * Show settings edit form
     */
    public function edit()
    {
        $social = Setting::getJson('social_links', [
            'facebook' => null,
            'twitter'  => null,
            'youtube'  => null,
        ]);

        return Inertia::render('Dashboard/Settings/Edit', [
            'form' => [
                'site_name'         => Setting::get('site_name', config('app.name')),
                'tagline'           => Setting::get('tagline', ''),
                'rss_title'         => Setting::get('rss_title', 'Site RSS'),
                'comment_policy'    => Setting::get('comment_policy', ''),
                'mail_from_name'    => Setting::get('mail_from_name', config('mail.from.name')),
                'mail_from_address' => Setting::get('mail_from_address', config('mail.from.address')),
                'social_links'      => $social,
                // previews
                'site_logo_url'     => Setting::get('site_logo_url', null),
                'site_favicon_url'  => Setting::get('site_favicon_url', null),
            ],
        ]);
    }

    /**
     * Update settings (text + social + files)
     */
    public function update(Request $request)
    {
        // --- Normalize social URLs so users can type without scheme (e.g., facebook.com/you) ---
        $links = (array) $request->input('social_links', []);
        $normalize = function ($url) {
            if (!$url) return null;
            $u = trim((string) $url);
            // allow "@handle" for YouTube new handles? keep as plain url requirement, but normalize scheme
            if (!preg_match('~^https?://~i', $u)) {
                $u = 'https://' . $u;
            }
            return $u;
        };
        $request->merge([
            'social_links' => [
                'facebook' => $normalize(data_get($links, 'facebook')),
                'twitter'  => $normalize(data_get($links, 'twitter')),
                'youtube'  => $normalize(data_get($links, 'youtube')),
            ],
        ]);
        // ---------------------------------------------------------------------------------------

        $data = $request->validate([
            'site_name'               => 'required|string|max:120',
            'tagline'                 => 'nullable|string|max:200',
            'rss_title'               => 'required|string|max:160',
            'comment_policy'          => 'nullable|string|max:5000',
            'mail_from_name'          => 'required|string|max:120',
            'mail_from_address'       => 'required|email|max:190',

            'social_links.facebook'   => 'nullable|url',
            'social_links.twitter'    => 'nullable|url',
            'social_links.youtube'    => 'nullable|url',

            // Files
            'logo'                    => 'nullable|file|mimetypes:image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon|mimes:png,jpg,jpeg,webp,svg,ico|max:2048',
            'favicon'                 => 'nullable|file|mimetypes:image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon|mimes:png,jpg,jpeg,webp,svg,ico|max:1024',
        ]);

        // === Text settings ===
        Setting::set('site_name', $data['site_name']);
        Setting::set('tagline', $data['tagline'] ?? '');
        Setting::set('rss_title', $data['rss_title']);
        Setting::set('comment_policy', $data['comment_policy'] ?? '');
        Setting::set('mail_from_name', $data['mail_from_name']);
        Setting::set('mail_from_address', $data['mail_from_address']);

        // Save social as JSON with all 3 keys always present
        $socialJson = [
            'facebook' => data_get($data, 'social_links.facebook'),
            'twitter'  => data_get($data, 'social_links.twitter'),
            'youtube'  => data_get($data, 'social_links.youtube'),
        ];
        Setting::setJson('social_links', $socialJson);

        // === File uploads (public/brand) ===
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext  = $file->getClientOriginalExtension();
            $name = 'logo_' . time() . '.' . $ext;

            $file->move(public_path('brand'), $name); // save to public/brand
            $url = asset('brand/' . $name);           // public URL

            Setting::set('site_logo_url', $url);
            Log::info('Site logo uploaded', ['url' => $url]);
        }

        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $ext  = $file->getClientOriginalExtension();
            $name = 'favicon_' . time() . '.' . $ext;

            $file->move(public_path('brand'), $name);
            $url = asset('brand/' . $name);

            Setting::set('site_favicon_url', $url);
            Log::info('Site favicon uploaded', ['url' => $url]);
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    /**
     * Send a test email using current settings
     */
    public function sendTestEmail(Request $request)
    {
        $data = $request->validate(['to' => 'required|email']);

        $fromName  = Setting::get('mail_from_name', config('mail.from.name'));
        $fromEmail = Setting::get('mail_from_address', config('mail.from.address'));

        // runtime override for this request only
        config([
            'mail.from.address' => $fromEmail,
            'mail.from.name'    => $fromName,
        ]);

        try {
            Mail::raw('Test email from your site settings. All good!', function ($m) use ($data, $fromName, $fromEmail) {
                $m->to($data['to'])
                  ->subject('Test email ✅')
                  ->from($fromEmail, $fromName);
            });

            return back()->with('success', 'Test email sent to ' . $data['to']);
        } catch (\Throwable $e) {
            Log::error('Test mail failed: ' . $e->getMessage());
            return back()->with('error', 'Test email failed: ' . $e->getMessage());
        }
    }
}

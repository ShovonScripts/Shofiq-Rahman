<?php

namespace App\Http\Controllers;

use App\Models\BookLinkClick;
use App\Models\BookPurchaseLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookLinkController extends Controller
{
    /**
     * Public redirect for purchase links.
     * Route: GET /go/book-link/{link}
     * Implicit model binding: {link} => BookPurchaseLink $link
     */
    public function go(Request $request, BookPurchaseLink $link): RedirectResponse
    {
        // 1) Get raw URL safely
        $raw = (string) ($link->url ?? '');
        $url = trim($raw);

        // 2) Ensure scheme (http/https only)
        if ($url !== '') {
            $lower = Str::lower($url);
            if (! Str::startsWith($lower, ['http://', 'https://'])) {
                // avoid accidental double slashes after scheme
                $url = 'https://'.ltrim($url);
            }
        }

        // 3) Validate final URL (must be proper http/https with host)
        if (! $this->isValidHttpUrl($url)) {
            // Fallback → book details page if available, else home
            $book = $link->book()->select('slug', 'title')->first();

            return $book?->slug
                ? redirect()->route('books.show', ['slug' => $book->slug])
                    ->with('error', 'Invalid purchase link. Please try another seller.')
                : redirect('/')->with('error', 'Invalid purchase link.');
        }

        // 4) Best-effort click log (won’t break redirect if it fails)
        $this->logClickIfPossible($request, $link);

        // 5) Safe external redirect
        return redirect()->away($url);
    }

    /**
     * Strict http/https URL validator.
     */
    private function isValidHttpUrl(?string $u): bool
    {
        if (! is_string($u) || $u === '') {
            return false;
        }
        if (! filter_var($u, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($u);
        if (! isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        $scheme = Str::lower((string) $parts['scheme']);

        return in_array($scheme, ['http', 'https'], true);
    }

    /** Click logging is best-effort and must never prevent a valid redirect. */
    private function logClickIfPossible(Request $request, BookPurchaseLink $link): void
    {
        try {
            BookLinkClick::create([
                'book_id' => $link->book_id,
                'book_link_id' => $link->id,
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'referer' => substr((string) $request->headers->get('referer'), 0, 1000),
                'utm_source' => substr((string) $request->query('utm_source', ''), 0, 100),
                'utm_medium' => substr((string) $request->query('utm_medium', ''), 0, 100),
                'utm_campaign' => substr((string) $request->query('utm_campaign', ''), 0, 150),
            ]);
        } catch (\Throwable $e) {
            logger()->warning('Failed to log book link click', [
                'link_id' => $link->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

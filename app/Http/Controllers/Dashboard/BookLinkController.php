<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookLinkController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class]);
    }

    /**
     * POST /dashboard/books/{book}/links
     */
    public function store(Book $book, Request $request)
    {
        $this->authorize('update', $book);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'max:1000'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $maxOrder = $book->purchaseLinks()->max('sort_order');
        $nextOrder = (int) ($maxOrder ?? 0) + 1;

        DB::transaction(function () use ($book, $data, $nextOrder) {
            $link = $book->purchaseLinks()->create([
                'label' => $data['label'],
                'url' => $data['url'],
                'is_primary' => (bool) ($data['is_primary'] ?? false),
                'sort_order' => $nextOrder,
            ]);

            if (! empty($data['is_primary'])) {
                $book->purchaseLinks()
                    ->where('id', '!=', $link->id)
                    ->update(['is_primary' => false]);
            }
        });

        return back()->with('success', 'Link added.');
    }

    /**
     * PUT /dashboard/books/{book}/links/{link}
     */
    public function update(Book $book, BookLink $link, Request $request)
    {
        $this->authorize('update', $book);

        if ((int) $link->book_id !== (int) $book->id) {
            abort(404);
        }

        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'max:1000'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($book, $link, $data) {
            $link->update([
                'label' => $data['label'],
                'url' => $data['url'],
                'is_primary' => (bool) ($data['is_primary'] ?? $link->is_primary),
            ]);

            if (! empty($data['is_primary'])) {
                $book->purchaseLinks()
                    ->where('id', '!=', $link->id)
                    ->update(['is_primary' => false]);
            }
        });

        return back()->with('success', 'Link updated.');
    }

    /**
     * DELETE /dashboard/books/{book}/links/{link}
     */
    public function destroy(Book $book, BookLink $link)
    {
        $this->authorize('update', $book);

        if ((int) $link->book_id !== (int) $book->id) {
            abort(404);
        }

        $link->delete();

        // Re-normalize sort_order
        $book->purchaseLinks()
            ->orderBy('sort_order')
            ->get()
            ->values()
            ->each(function (BookLink $item, $idx) {
                $item->update(['sort_order' => $idx + 1]);
            });

        return back()->with('success', 'Link removed.');
    }

    /**
     * PATCH /dashboard/books/{book}/links/{link}/primary
     */
    public function makePrimary(Book $book, BookLink $link)
    {
        $this->authorize('update', $book);

        if ((int) $link->book_id !== (int) $book->id) {
            abort(404);
        }

        DB::transaction(function () use ($book, $link) {
            $book->purchaseLinks()->update(['is_primary' => false]);
            $link->update(['is_primary' => true]);
        });

        return back()->with('success', 'Primary link set.');
    }

    /**
     * PATCH /dashboard/books/{book}/links/{link}/sort/{dir}
     * dir: up|down
     */
    public function bump(Book $book, BookLink $link, string $dir)
    {
        $this->authorize('update', $book);

        if ((int) $link->book_id !== (int) $book->id) {
            abort(404);
        }

        $dir = $dir === 'up' ? 'up' : 'down';

        $links = $book->purchaseLinks()->orderBy('sort_order')->get()->values();

        $index = $links->search(fn ($l) => $l->id === $link->id);
        if ($index === false) {
            return back()->with('error', 'Link not found in list.');
        }

        $swapWith = $dir === 'up' ? $index - 1 : $index + 1;
        if ($swapWith < 0 || $swapWith >= $links->count()) {
            return back();
        }

        $a = $links[$index];
        $b = $links[$swapWith];

        DB::transaction(function () use ($a, $b) {
            $tmp = $a->sort_order;
            /** @var BookLink $a */
            $a->update(['sort_order' => $b->sort_order]);
            /** @var BookLink $b */
            $b->update(['sort_order' => $tmp]);
        });

        return back()->with('success', 'Order updated.');
    }
}

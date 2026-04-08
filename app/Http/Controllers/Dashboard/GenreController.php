<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GenreController extends Controller
{
    /**
     * Display a paginated list of genres.
     */
    public function index()
    {
        $genres = Genre::orderBy('name')
            ->select('id', 'name', 'slug', 'created_at')
            ->paginate(20);

        return Inertia::render('Dashboard/Genres/Index', [
            'genres' => $genres,
        ]);
    }

    /**
     * Show the form for creating a new genre.
     */
    public function create()
    {
        return Inertia::render('Dashboard/Genres/Create');
    }

    /**
     * Store a newly created genre in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', 'unique:genres,slug'],
        ]);

        // 🔹 Generate unique slug if not provided
        if (empty($data['slug'])) {
            $base = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', $data['name']), '-') ?: Str::random(6);
            $slug = $base;
            $i = 1;

            while (Genre::where('slug', $slug)->exists()) {
                $slug = $base . '-' . Str::random(4) . ($i > 1 ? $i : '');
                $i++;
            }

            $data['slug'] = $slug;
        }

        Genre::create($data);

        return redirect()
            ->route('dashboard.genres.index')
            ->with('success', '✅ Genre created successfully.');
    }

    /**
     * Show the form for editing the specified genre.
     */
    public function edit(Genre $genre)
    {
        return Inertia::render('Dashboard/Genres/Edit', [
            'genre' => [
                'id'   => $genre->id,
                'name' => $genre->name,
                'slug' => $genre->slug,
            ],
        ]);
    }

    /**
     * Update the specified genre in storage.
     */
    public function update(Request $request, Genre $genre)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:160', 'unique:genres,slug,' . $genre->id],
        ]);

        $genre->update($data);

        return redirect()
            ->route('dashboard.genres.index')
            ->with('success', '✅ Genre updated successfully.');
    }

    /**
     * Remove the specified genre from storage.
     */
    public function destroy(Genre $genre)
    {
        $genre->delete();

        return redirect()
            ->route('dashboard.genres.index')
            ->with('success', '🗑️ Genre deleted successfully.');
    }
}

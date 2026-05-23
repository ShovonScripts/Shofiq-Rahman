<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\EnsureAdmin::class]);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $categories = Category::query()
            ->when($q, fn ($sql) => $sql->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard/Categories/Index', [
            'filters' => ['q' => $q],
            'items' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/Categories/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120|unique:categories,name',
            'slug' => 'nullable|string|max:160|unique:categories,slug',
            'description' => 'nullable|string|max:1000',
        ]);

        $slug = $data['slug'] ?: Str::slug($data['name']);
        // ensure unique
        $slugBase = $slug;
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$slugBase}-{$i}";
            $i++;
        }

        Category::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);

        Cache::forget(CacheKeys::allCategories());
        Cache::forget(CacheKeys::categoriesWithPostCount());

        return redirect()->route('dashboard.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Dashboard/Categories/Edit', [
            'item' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ],
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120|unique:categories,name,'.$category->id,
            'slug' => 'nullable|string|max:160|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $slug = $data['slug'] ?: Str::slug($data['name']);
        // ensure unique (skip self)
        $slugBase = $slug;
        $i = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = "{$slugBase}-{$i}";
            $i++;
        }

        $category->update([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);

        Cache::forget(CacheKeys::allCategories());
        Cache::forget(CacheKeys::categoriesWithPostCount());

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        Cache::forget(CacheKeys::allCategories());
        Cache::forget(CacheKeys::categoriesWithPostCount());

        return back()->with('success', 'Category deleted.');
    }
}

<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Model;

class TermSyncService
{
    public function ensureCategoriesExistAndReturnIds(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        $ids = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $category = Category::firstOrCreate(
                ['name' => $name],
                ['slug' => (new SlugService())->generateUniqueSlug(new Category(), $name)]
            );
            $ids[] = $category->id;
        }

        return $ids;
    }

    public function ensureGenresExistAndReturnIds(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        $ids = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $genre = Genre::firstOrCreate(
                ['name' => $name],
                ['slug' => (new SlugService())->generateUniqueSlug(new Genre(), $name)]
            );
            $ids[] = $genre->id;
        }

        return $ids;
    }

    public function syncCategories(Model $model, string $csv, string $relation = 'categories'): void
    {
        $names = $this->parseCsv($csv);
        $ids = $this->ensureCategoriesExistAndReturnIds($names);
        $model->{$relation}()->sync($ids);
    }

    public function syncGenres(Model $model, string $csv, string $relation = 'genres'): void
    {
        $names = $this->parseCsv($csv);
        $ids = $this->ensureGenresExistAndReturnIds($names);
        $model->{$relation}()->sync($ids);
    }

    public function parseCsv(string $csv): array
    {
        return array_filter(
            array_map('trim', preg_split('/[,\x{FF0C}\x{060C};|]+/u', $csv)),
            fn ($v) => $v !== ''
        );
    }

    public function toCsv(array $ids, $source, string $nameField = 'name'): string
    {
        return collect($ids)
            ->map(fn ($id) => $source->firstWhere('id', $id)?->{$nameField})
            ->filter()
            ->implode(', ');
    }
}

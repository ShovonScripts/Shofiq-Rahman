<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugService
{
    public function generateUniqueSlug(Model $model, string $base, string $column = 'slug', int $maxLength = 200): string
    {
        $slug = Str::slug($base);
        $slug = Str::limit($slug, $maxLength, '');

        $original = $slug;
        $i = 0;

        while ($model::where($column, $slug)->exists()) {
            $i++;
            $suffix = '-' . Str::random(4) . ($i > 1 ? $i : '');
            $slug = Str::limit($original, $maxLength - strlen($suffix), '') . $suffix;
        }

        return $slug;
    }
}

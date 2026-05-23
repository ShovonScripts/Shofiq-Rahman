<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorEvent extends Model
{
    protected $fillable = [
        'author_profile_id', 'happened_at', 'headline', 'description_md', 'image_path', 'location', 'sort_order', 'is_published',
    ];

    protected $casts = [
        'happened_at' => 'date',
        'is_published' => 'boolean',
    ];

    public function profile()
    {
        return $this->belongsTo(AuthorProfile::class, 'author_profile_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return storage_url($this->image_path);
    }
}

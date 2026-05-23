<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthorProfile extends Model
{
    protected $fillable = [
        'name', 'pen_name', 'date_of_birth', 'location',
        'email_public', 'website', 'bio_md', 'achievements_summary_md',
        'avatar_path', 'cover_path', 'social_links', 'is_active',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_active' => 'boolean',
        'date_of_birth' => 'date',
    ];

    public function achievements(): HasMany
    {
        return $this->hasMany(AuthorAchievement::class)->orderBy('sort_order')->orderBy('id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AuthorEvent::class)->orderBy('sort_order')->orderByDesc('happened_at')->orderBy('id', 'desc');
    }

    // Convenience accessor for absolute URLs to images (if using public disk)
    public function getAvatarUrlAttribute(): ?string
    {
        return storage_url($this->avatar_path);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return storage_url($this->cover_path);
    }
}

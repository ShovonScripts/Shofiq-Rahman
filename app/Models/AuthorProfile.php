<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorProfile extends Model
{
    protected $fillable = [
        'name','pen_name','date_of_birth','location',
        'email_public','website','bio_md','achievements_summary_md',
        'avatar_path','cover_path','social_links','is_active'
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_active' => 'boolean',
        'date_of_birth' => 'date',
    ];

    public function achievements() {
        return $this->hasMany(AuthorAchievement::class)->orderBy('sort_order')->orderBy('id');
    }

    public function events() {
        return $this->hasMany(AuthorEvent::class)->orderBy('sort_order')->orderByDesc('happened_at')->orderBy('id','desc');
    }

    // Convenience accessor for absolute URLs to images (if using public disk)
    public function getAvatarUrlAttribute(): ?string {
        return $this->avatar_path ? url('storage/'.$this->avatar_path) : null;
    }

    public function getCoverUrlAttribute(): ?string {
        return $this->cover_path ? url('storage/'.$this->cover_path) : null;
    }
}

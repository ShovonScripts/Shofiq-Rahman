<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    BelongsToMany,
    HasMany,
    HasOne,
    MorphMany
};
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'subtitle',
        'description',
        'cover',
        'isbn',
        'language',
        'pages',
        'release_date',
        'status',
        'published_at',
        'meta_json',
    ];

    protected $casts = [
        'user_id'      => 'integer',
        'pages'        => 'integer',
        'release_date' => 'date',
        'published_at' => 'datetime',
        'meta_json'    => 'array',
        'status'       => 'string',
    ];

    protected $appends = ['cover_url'];

    /* -----------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------ */

    /** ✍️ Author (owner) */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** 🔗 Purchase links (no default order here; keep it neutral) */
    public function purchaseLinks(): HasMany
    {
        return $this->hasMany(BookLink::class, 'book_id');
    }

    /** 🔁 Legacy alias ($book->links()) */
    public function links(): HasMany
    {
        return $this->purchaseLinks();
    }

    /** 🔗 Convenience: links ordered (sort_order → position → id) */
    public function linksOrdered(): HasMany
    {
        return $this->hasMany(BookLink::class, 'book_id')->ordered();
    }

    /** ⭐ Convenience: the primary link if one is marked */
    public function primaryLink(): HasOne
    {
        return $this->hasOne(BookLink::class, 'book_id')
            ->where('is_primary', true)
            ->orderByRaw('COALESCE(sort_order, position, 0) ASC, id ASC');
    }

    /** 📈 Link clicks (aggregated at book level) */
    public function clicks(): HasMany
    {
        // FK defaults to book_id (matches your schema)
        return $this->hasMany(BookLinkClick::class);
    }

    /** 🎭 Genres */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    /** 📚 Categories */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'book_category');
    }

    /** 💬 Comments (newest first via ->latest() on relation) */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    /** ❤️ Likes */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /* -----------------------------------------------------------------
     | Accessors
     |------------------------------------------------------------------ */

    public function getCoverUrlAttribute(): ?string
    {
        $cover = $this->cover;

        if (!$cover) {
            return null;
        }

        // If it's already a full URL, return as-is
        if (filter_var($cover, FILTER_VALIDATE_URL)) {
            return $cover;
        }

        // Normalize local storage path
        $path = ltrim($cover, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8); // drop 'storage/' prefix
        }

        // Prefer public disk if file exists
        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path); // /storage/...
        }

        // Fallback to asset() so older uploads still resolve
        return asset('storage/' . $path);
    }

    /* -----------------------------------------------------------------
     | Scopes
     |------------------------------------------------------------------ */

    /** Only published & already visible */
    public function scopePublished($q)
    {
        return $q
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** Recent first by creation time */
    public function scopeRecent($q)
    {
        return $q->orderByDesc('created_at');
    }

    /** Drafts only */
    public function scopeDraft($q)
    {
        return $q->where('status', 'draft');
    }
}

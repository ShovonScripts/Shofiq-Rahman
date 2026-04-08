<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeBanner extends Model
{
    protected $fillable = [
        'image',
        'title',
        'link', 
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the full image URL with proper storage path handling
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Check if the image path is already a full URL (for seeded data or external URLs)
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        // Handle both absolute paths and relative storage paths
        $imagePath = $this->image;
        
        // Remove 'storage/' prefix if it exists to avoid duplication
        if (str_starts_with($imagePath, 'storage/')) {
            $imagePath = substr($imagePath, 8); // Remove 'storage/' prefix
        }

        return Storage::disk('public')->exists($imagePath) 
            ? Storage::disk('public')->url($imagePath)
            : asset('storage/' . $imagePath);
    }

    /**
     * Scope for active banners
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the image path for storage operations
     */
    public function getImagePathAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Remove 'storage/' prefix if it exists
        if (str_starts_with($this->image, 'storage/')) {
            return substr($this->image, 8);
        }

        return $this->image;
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Set default sort order when creating a new banner
        static::creating(function ($banner) {
            if (is_null($banner->sort_order)) {
                $banner->sort_order = static::max('sort_order') + 1;
            }
        });

        // Reorder remaining banners when deleting
        static::deleting(function ($banner) {
            // If you want to reorder the remaining banners after deletion
            // You can implement this logic here if needed
        });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookLink extends Model
{
    use HasFactory;

    protected $table = 'book_links';

    protected $fillable = [
        'book_id',
        'label',
        'url',
        'price_label',
        'is_primary',
        'position',   // পুরোনো প্রোজেক্টে থাকতে পারে
        'sort_order', // নতুন স্ট্যান্ডার্ড কলাম
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(BookLinkClick::class, 'book_purchase_link_id');
    }

    /** এক জায়গায় অর্ডারিং—Fallback: sort_order → position → 0 */
    public function scopeOrdered($q)
    {
        return $q->orderByRaw('COALESCE(sort_order, position, 0) ASC, id ASC');
    }

    public function scopePrimary($q)
    {
        return $q->where('is_primary', true);
    }
}

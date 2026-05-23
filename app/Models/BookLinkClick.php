<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookLinkClick extends Model
{
    use HasFactory;

    protected $table = 'book_link_clicks';

    protected $fillable = [
        'book_id',
        'book_link_id',   // ✅ match DB column name
        'ip',
        'user_agent',
        'referer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function purchaseLink(): BelongsTo
    {
        // ✅ FK uses book_link_id
        return $this->belongsTo(BookLink::class, 'book_link_id');
    }

    // Backward alias for older code
    public function link(): BelongsTo
    {
        return $this->purchaseLink();
    }
}

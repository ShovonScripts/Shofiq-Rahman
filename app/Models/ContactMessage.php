<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',           // ✅ Added
        'subject',
        'message',
        'status',
        'ip_address',      // ✅ Added
        'replied_at',
        'reply_subject',
        'reply_body',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',  // ✅ Added
        'updated_at' => 'datetime',  // ✅ Added
    ];

    // ✅ Updated scope to handle both 'new' and 'unread' statuses
    public function scopeUnread($query)
    {
        return $query->whereIn('status', ['new', 'unread']);
    }

    // ✅ Additional helpful scopes
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeReplied($query)
    {
        return $query->where('status', 'replied');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}

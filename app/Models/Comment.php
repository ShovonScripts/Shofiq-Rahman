<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'user_id',
        'commentable_type',
        'commentable_id',
        'body',
        'is_approved',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
            'deleted_at'  => 'datetime',
        ];
    }

    /* -----------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------*/

    /**
     * Polymorphic relation to Post or Book (or any other model).
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * The user who wrote the comment.
     * Kept as `user()` because many controllers/eager-loads use this name.
     */
    public function user()
    {
        // comments.user_id → users.id
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias of user() for readability in some views/controllers.
     */
    public function author()
    {
        return $this->user();
    }

    /* -----------------------------------------------------------------
     | Scopes
     |------------------------------------------------------------------*/

    /**
     * Only approved comments.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Only unapproved/pending comments.
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }
}

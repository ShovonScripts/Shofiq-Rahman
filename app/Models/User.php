<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\User
 *
 * @property string $name
 * @property string $email
 * @property string $role One of: admin|author|reader
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 *
 * // Helper methods (for IDEs like Intelephense)
 *
 * @method bool isAdmin()
 * @method bool isAuthor()
 * @method bool isReader()
 * @method bool isActive()
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** ----------------------------------------------------------------
     * Role constants (use these instead of hard-coded strings)
     * --------------------------------------------------------------- */
    public const ROLE_ADMIN = 'admin';

    public const ROLE_AUTHOR = 'author';

    public const ROLE_READER = 'reader';

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Attributes hidden from arrays / JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting (classic property form = works across L9/L10/L11).
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /* -----------------------------------------------------------------
     | Relations
     |------------------------------------------------------------------*/
    public function posts()
    {
        // Explicit FQN to avoid missing import issues
        return $this->hasMany(\App\Models\Post::class);
    }

    /* -----------------------------------------------------------------
     | Mutators / Normalizers
     |------------------------------------------------------------------*/
    /**
     * Normalize role values to lowercase strings.
     */
    public function setRoleAttribute($value): void
    {
        $this->attributes['role'] = is_string($value) ? strtolower($value) : $value;
    }

    /* -----------------------------------------------------------------
     | Role helpers
     |------------------------------------------------------------------*/
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isAuthor(): bool
    {
        // Authors and Admins both pass the "author" gate in your middleware
        return in_array($this->role, [self::ROLE_AUTHOR, self::ROLE_ADMIN], true);
    }

    public function isReader(): bool
    {
        return $this->role === self::ROLE_READER;
    }

    /**
     * Active flag helper (pairs with your is_active column).
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /* -----------------------------------------------------------------
     | Scopes
     |------------------------------------------------------------------*/
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeAuthors($query)
    {
        return $query->whereIn('role', [self::ROLE_AUTHOR, self::ROLE_ADMIN]);
    }

    public function scopeReaders($query)
    {
        return $query->where('role', self::ROLE_READER);
    }
}

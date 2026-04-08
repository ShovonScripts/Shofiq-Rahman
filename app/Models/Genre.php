<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'slug'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * 📚 Books relationship (many-to-many)
     */
    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_genre');
    }

    /**
     * 📰 Posts relationship (many-to-many)
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_genre');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorAchievement extends Model
{
    protected $fillable = [
        'author_profile_id','title','year','description_md','icon','sort_order'
    ];

    public function profile() {
        return $this->belongsTo(AuthorProfile::class, 'author_profile_id');
    }
}

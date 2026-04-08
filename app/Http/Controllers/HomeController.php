<?php

// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;

use App\Models\HomeBanner;
use App\Models\Post;
use App\Models\Book;
use App\Models\Setting;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $banners = HomeBanner::where('is_active', true)
            ->orderBy('sort_order')->take(5)->get()
            ->map(fn($b)=>[
                'id'=>$b->id,
                'title'=>$b->title,
                'link'=>$b->link,
                'image_url'=>$b->image_url,
            ]);

        $posts = Post::published()
            ->select('id','title','slug','excerpt','cover','published_at')
            ->recent()->take(5)->get()
            ->map(fn($p)=>[
                'id'=>$p->id,
                'title'=>$p->title,
                'slug'=>$p->slug,
                'excerpt'=>$p->excerpt,
                'cover_url'=>$p->cover_url,
                'date'=>optional($p->published_at)->toDateString(),
            ]);

        $books = Book::published()
            ->select('id','title','slug','subtitle','cover','published_at')
            ->recent()->take(5)->get()
            ->map(fn($b)=>[
                'id'=>$b->id,
                'title'=>$b->title,
                'slug'=>$b->slug,
                'subtitle'=>$b->subtitle,
                'cover_url'=>$b->cover_url,
            ]);

        $author = [
            'name'  => Setting::get('home_author_name', 'Author'),
            'bio'   => Setting::get('home_author_bio_md', ''),
            'photo' => Setting::get('home_author_photo') ? asset('storage/'.Setting::get('home_author_photo')) : null,
        ];

        return Inertia::render('Home', [
            'banners'=>$banners,
            'latest_posts'=>$posts,
            'latest_books'=>$books,
            'author'=>$author,
        ]);
    }
}


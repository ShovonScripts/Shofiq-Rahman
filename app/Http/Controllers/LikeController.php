<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:post,book',
            'id' => 'required|integer',
        ]);

        $userId = $request->user()->id;
        $model = $data['type'] === 'post'
            ? Post::published()->findOrFail($data['id'])
            : Book::published()->findOrFail($data['id']);

        $existing = $model->likes()->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $model->likes()->create(['user_id' => $userId]);
            $liked = true;
        }

        // ছোট JSON রেসপন্স (Inertia partial reloadেও ব্যবহার করতে পারো)
        return response()->json([
            'liked' => $liked,
            'count' => $model->likes()->count(),
        ]);
    }
}

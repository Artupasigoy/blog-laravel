<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Menampilkan halaman detail postingan (Single Post).
     *
     * @param string $slug Slug postingan
     */
    public function index($slug)
    {
        // Query Eager Loading Kompleks:
        // 1. Ambil relasi kategori, user, tags.
        // 2. Ambil komentar & balasan (hanya yang statusnya aktif/approved).
        // 3. Hitung jumlah tags dan komentar aktif.
        $post = Post::with(["category", "user", "tags", "comments.user", "comments.replies.user"])->with("comments.replies", function ($q) {
            $q->where("status", true); // Balasan aktif
        })->with("comments", function ($q) {
            $q->where("status", true)->where("parent_id", null); // Komentar induk aktif
        })->withCount([
                    "tags",
                    "comments" => function ($q) {
                        $q->where("status", true);
                    }
                ])->where("status", true)->where("slug", $slug)->first(); // Pastikan post aktif

        if ($post) {
            // Increment view counter
            $post->views += 1;
            $post->save();

            $str = Str::class;
            return view("frontend.post.index", compact("post", "str"));
        }
        return abort(404);
    }
}
